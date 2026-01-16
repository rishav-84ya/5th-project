<?php
// Start session is not needed for registration, but good practice for redirects.
session_start();

// Configuration for Database Connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "project";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/**
 * Checks if an affiliation (University or Department) exists by name. 
 * If not, creates it and returns the ID.
 */
function getOrCreateAffiliationID($conn, $tableName, $name, $parentField = null, $parentID = null) {
    $query = "SELECT id FROM $tableName WHERE name = ?";
    $params = [$name];
    $types = "s";
    
    if ($parentField && $parentID !== null) {
        $query .= " AND $parentField = ?";
        $params[] = $parentID;
        $types .= "i";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        return $row['id'];
    }

    if ($parentField && $parentID !== null) {
        $stmt_insert = $conn->prepare("INSERT INTO $tableName (name, $parentField) VALUES (?, ?)");
        $stmt_insert->bind_param("si", $name, $parentID);
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO $tableName (name) VALUES (?)");
        $stmt_insert->bind_param("s", $name);
    }
    
    if ($stmt_insert->execute()) {
        return $stmt_insert->insert_id;
    } else {
        error_log("Failed to insert into $tableName: " . $stmt_insert->error);
        return 0;
    }
}


// --- Form Submission Logic ---
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST["role"] ?? '';
    $fullname = $_POST["name"] ?? '';
    $email = $_POST["email"] ?? '';
    $password_input = $_POST["password"] ?? '';
    $university_name = $_POST["university"] ?? '';
    $department_name = $_POST["department"] ?? '';

    // Student-specific fields (without roll_number)
    $branch = ($role === "student") ? ($_POST["branch"] ?? null) : null;
    $year = ($role === "student") ? ($_POST["year"] ?? null) : null;
    
    $contact = $_POST["contact"] ?? null;
    $address = $_POST["address"] ?? null;

    if (empty($role) || empty($fullname) || empty($email) || empty($password_input)) {
        $error_message = "All required fields must be filled.";
    } else {
        try {
            $hashed_password = password_hash($password_input, PASSWORD_DEFAULT);

            $university_id = 0;
            if (!empty($university_name)) {
                $university_id = getOrCreateAffiliationID($conn, 'universities', $university_name);
            }
            
            $department_id = 0;
            if ($university_id > 0 && !empty($department_name)) {
                $department_id = getOrCreateAffiliationID($conn, 'departments', $department_name, 'university_id', $university_id);
            }

            // ✅ Removed roll_number from insertion
            $stmt = $conn->prepare(
                "INSERT INTO users (
                    full_name, gmail, password, user_type, 
                    university_id, department_id, branch, year, 
                    contact, address
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            
            $stmt->bind_param(
                "ssssiiisis", 
                $fullname, $email, $hashed_password, $role, 
                $university_id, $department_id, $branch, $year, 
                $contact, $address
            );

            if ($stmt->execute()) {
                header("Location: ../login/login1.php");
                echo "Registration successful. Please log in.";
                exit();
            } else {
                if ($conn->errno == 1062) {
                    $error_message = "Email already exists. Please login or use a different email.";
                } else {
                    $error_message = "Registration failed: " . $stmt->error;
                }
            }
            $stmt->close();
        } catch (Exception $e) {
            $error_message = "An unexpected error occurred: " . $e->getMessage();
        }
    }
}
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Registration</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; }
        .form-box { max-width: 500px; }
        .required::after { content: " *"; color: #e74c3c; margin-left: 4px; }
    </style>
    <script>
        function toggleFields() {
            const role = document.getElementById("role").value;
            const studentFields = document.getElementById("studentFields");
            
            if (role === "student") {
                studentFields.classList.remove('hidden');
                studentFields.querySelectorAll('input').forEach(input => input.required = true);
            } else {
                studentFields.classList.add('hidden');
                studentFields.querySelectorAll('input').forEach(input => input.required = false);
            }
        }
        window.onload = toggleFields;
    </script>
</head>
<body class="min-h-screen flex flex-col">
    <header class="bg-gray-800 text-white p-4 shadow-md flex justify-between items-center">
        <div class="text-2xl font-bold">EDU-SHARE</div>
        <div class="text-sm">
            <a href="../login/login1.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-200">
                Log In
            </a>
        </div>
    </header>

    <main class="flex-grow flex items-center justify-center p-4">
        <div class="form-box bg-white p-8 rounded-xl shadow-2xl w-full">
            <h2 class="text-3xl font-extrabold text-gray-800 text-center mb-6">Create Your Account</h2>
            
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="post" class="space-y-4">
                
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 required">Select Role:</label>
                    <select name="role" id="role" onchange="toggleFields()" class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-150" required>
                        <option value="">-- Select --</option>
                        <option value="student">Student</option>
                        <option value="teacher">Teacher</option>
                    </select>
                </div>

                <input type="text" name="name" placeholder="Full Name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <input type="email" name="email" placeholder="Email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <input type="password" name="password" placeholder="Password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>

                <input type="text" name="university" placeholder="University Name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                <input type="text" name="department" placeholder="Department Name" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                
                <input type="text" name="contact" placeholder="Contact Number (Optional)" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="text" name="address" placeholder="Address (Optional)" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">

                <!-- ✅ Removed Roll Number field -->
                <div id="studentFields" class="hidden space-y-4 border-t pt-4 border-gray-200">
                    <h3 class="text-lg font-bold text-gray-800">Student Details (Required for Students)</h3>
                    <input type="text" name="branch" placeholder="Branch (e.g., IT, Mechanical)" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="number" name="year" placeholder="Current Year of Study" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-extrabold py-3 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                    Register Account
                </button>
            </form>

            <p class="text-center text-sm text-gray-500 mt-4">
                Already have an account? 
                <a href="../login/login1.php" class="text-blue-600 hover:text-blue-800 font-semibold">Log In here</a>
            </p>
        </div>
    </main>
</body>
</html>
