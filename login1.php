<?php
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

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: profile/profile.php");

    exit();
}

$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"] ?? '';
    $password_input = $_POST["password"] ?? '';

    if (empty($email) || empty($password_input)) {
        $error_message = "Please enter both email and password.";
    } else {
        // Query to find user by email
        $stmt = $conn->prepare("SELECT id, full_name, user_type, password FROM users WHERE gmail = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password_input, $row['password'])) {
                // Successful login: Set session variables
                session_regenerate_id(true);
                $_SESSION['user_id'] = $row['id'];
                // FIX: Explicitly set user_type and full_name in session
                $_SESSION['user_type'] = $row['user_type']; 
                $_SESSION['full_name'] = $row['full_name'];

                // Redirect to profile dashboard
                header("Location: ../home/dashboard.php");
             
                exit();
            } else {
                $error_message = "Invalid password.";
            }
        } else {
            $error_message = "No account found with that email.";
        }
        $stmt->close();
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f4f7f6; }
        .form-box { max-width: 400px; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="form-box bg-white p-8 rounded-xl shadow-2xl w-full">
        <h2 class="text-3xl font-extrabold text-gray-800 text-center mb-6">Welcome Back</h2>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4" role="alert">
                Registration successful! Please log in.
            </div>
        <?php endif; ?>

        <form action="login1.php" method="post" class="space-y-4">
            
            <input type="email" name="email" placeholder="Email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            <input type="password" name="password" placeholder="Password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-extrabold py-3 rounded-xl shadow-lg hover:shadow-xl transition duration-300">
                Log In
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-4">
            Don't have an account? 
            <a href="../register/register.php" class="text-green-600 hover:text-green-800 font-semibold">Register here</a>
        </p>
    </div>
</body>
</html>
