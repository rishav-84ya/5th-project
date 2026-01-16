```php
<?php
session_start();
require '../db_connect.php';

// Only allow admin/teacher
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'teacher') {
    die("Unauthorized Access");
}

// Handle delete requests
if (isset($_GET['delete']) && isset($_GET['type'])) {
    $id = intval($_GET['delete']);
    $type = $_GET['type'];

    if ($type === 'department') {
        $conn->query("DELETE FROM departments WHERE id = $id");
    } elseif ($type === 'course') {
        $conn->query("DELETE FROM courses WHERE id = $id");
    } elseif ($type === 'subject') {
        $conn->query("DELETE FROM subjects WHERE id = $id");
    }
    header("Location: manage_academics.php");
    exit();
}

// Handle updates
if (isset($_POST['update_department'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['department_name']);
    $stmt = $conn->prepare("UPDATE departments SET department_name = ? WHERE id = ?");
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
}
if (isset($_POST['update_course'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['course_name']);
    $stmt = $conn->prepare("UPDATE courses SET course_name = ? WHERE id = ?");
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
}
if (isset($_POST['update_subject'])) {
    $id = intval($_POST['id']);
    $name = trim($_POST['subject_name']);
    $stmt = $conn->prepare("UPDATE subjects SET subject_name = ? WHERE id = ?");
    $stmt->bind_param("si", $name, $id);
    $stmt->execute();
}

// Handle add requests
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_department'])) {
        $uni_id = intval($_POST['university']);
        $department_name = trim($_POST['department_name']);
        if ($uni_id && $department_name) {
            $stmt = $conn->prepare("INSERT INTO departments (department_name, university_id) VALUES (?, ?)");
            $stmt->bind_param("si", $department_name, $uni_id);
            $stmt->execute();
        }
    }

    if (isset($_POST['add_course'])) {
        $dept_id = intval($_POST['department']);
        $course_name = trim($_POST['course_name']);
        if ($dept_id && $course_name) {
            $stmt = $conn->prepare("INSERT INTO courses (course_name, department_id) VALUES (?, ?)");
            $stmt->bind_param("si", $course_name, $dept_id);
            $stmt->execute();
        }
    }

    if (isset($_POST['add_subject'])) {
        $course_id = intval($_POST['course']);
        $subject_name = trim($_POST['subject_name']);
        if ($course_id && $subject_name) {
            $stmt = $conn->prepare("INSERT INTO subjects (subject_name, course_id) VALUES (?, ?)");
            $stmt->bind_param("si", $subject_name, $course_id);
            $stmt->execute();
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Manage Academics</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6 font-sans">
    <h1 class="text-3xl font-bold mb-6">Admin Panel – Manage Departments, Courses & Subjects</h1>

    <!-- Add Department -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-xl font-semibold mb-4">Add Department</h2>
        <form method="POST" class="space-y-4">
            <select name="university" required class="border p-2 rounded w-full">
                <option value="">Select University</option>
                <?php
                $res = $conn->query("SELECT id, university_name FROM universities");
                while ($row = $res->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['university_name']}</option>";
                }
                ?>
            </select>
            <input type="text" name="department_name" placeholder="Department Name" required class="border p-2 rounded w-full">
            <button type="submit" name="add_department" class="bg-blue-600 text-white px-4 py-2 rounded">Add Department</button>
        </form>
    </div>

    <!-- Department List -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-xl font-semibold mb-4">Departments</h2>
        <table class="w-full border">
            <tr class="bg-gray-200">
                <th class="p-2 border">ID</th>
                <th class="p-2 border">Name</th>
                <th class="p-2 border">University</th>
                <th class="p-2 border">Action</th>
            </tr>
            <?php
            $res = $conn->query("SELECT d.id, d.department_name, u.university_name 
                                 FROM departments d 
                                 JOIN universities u ON d.university_id = u.id");
            while ($row = $res->fetch_assoc()) {
                echo "<tr>
                        <td class='p-2 border'>{$row['id']}</td>
                        <td class='p-2 border'>
                            <form method='POST' class='flex'>
                                <input type='hidden' name='id' value='{$row['id']}'>
                                <input type='text' name='department_name' value='{$row['department_name']}' class='border p-1 rounded w-full'>
                                <button type='submit' name='update_department' class='ml-2 bg-yellow-500 text-white px-2 rounded'>Update</button>
                            </form>
                        </td>
                        <td class='p-2 border'>{$row['university_name']}</td>
                        <td class='p-2 border'>
                            <a href='?delete={$row['id']}&type=department' class='text-red-600'>Delete</a>
                        </td>
                      </tr>";
            }
            ?>
        </table>
    </div>

    <!-- Add Course -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-xl font-semibold mb-4">Add Course</h2>
        <form method="POST" class="space-y-4">
            <select name="department" required class="border p-2 rounded w-full">
                <option value="">Select Department</option>
                <?php
                $res = $conn->query("SELECT id, department_name FROM departments");
                while ($row = $res->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['department_name']}</option>";
                }
                ?>
            </select>
            <input type="text" name="course_name" placeholder="Course Name" required class="border p-2 rounded w-full">
            <button type="submit" name="add_course" class="bg-green-600 text-white px-4 py-2 rounded">Add Course</button>
        </form>
    </div>

    <!-- Course List -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-xl font-semibold mb-4">Courses</h2>
        <table class="w-full border">
            <tr class="bg-gray-200">
                <th class="p-2 border">ID</th>
                <th class="p-2 border">Name</th>
                <th class="p-2 border">Department</th>
                <th class="p-2 border">Action</th>
            </tr>
            <?php
            $res = $conn->query("SELECT c.id, c.course_name, d.department_name 
                                 FROM courses c 
                                 JOIN departments d ON c.department_id = d.id");
            while ($row = $res->fetch_assoc()) {
                echo "<tr>
                        <td class='p-2 border'>{$row['id']}</td>
                        <td class='p-2 border'>
                            <form method='POST' class='flex'>
                                <input type='hidden' name='id' value='{$row['id']}'>
                                <input type='text' name='course_name' value='{$row['course_name']}' class='border p-1 rounded w-full'>
                                <button type='submit' name='update_course' class='ml-2 bg-yellow-500 text-white px-2 rounded'>Update</button>
                            </form>
                        </td>
                        <td class='p-2 border'>{$row['department_name']}</td>
                        <td class='p-2 border'>
                            <a href='?delete={$row['id']}&type=course' class='text-red-600'>Delete</a>
                        </td>
                      </tr>";
            }
            ?>
        </table>
    </div>

    <!-- Add Subject -->
    <div class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-xl font-semibold mb-4">Add Subject</h2>
        <form method="POST" class="space-y-4">
            <select name="course" required class="border p-2 rounded w-full">
                <option value="">Select Course</option>
                <?php
                $res = $conn->query("SELECT id, course_name FROM courses");
                while ($row = $res->fetch_assoc()) {
                    echo "<option value='{$row['id']}'>{$row['course_name']}</option>";
                }
                ?>
            </select>
            <input type="text" name="subject_name" placeholder="Subject Name" required class="border p-2 rounded w-full">
            <button type="submit" name="add_subject" class="bg-purple-600 text-white px-4 py-2 rounded">Add Subject</button>
        </form>
    </div>

    <!-- Subject List -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-4">Subjects</h2>
        <table class="w-full border">
            <tr class="bg-gray-200">
                <th class="p-2 border">ID</th>
                <th class="p-2 border">Name</th>
                <th class="p-2 border">Course</th>
                <th class="p-2 border">Action</th>
            </tr>
            <?php
            $res = $conn->query("SELECT s.id, s.subject_name, c.course_name 
                                 FROM subjects s 
                                 JOIN courses c ON s.course_id = c.id");
            while ($row = $res->fetch_assoc()) {
                echo "<tr>
                        <td class='p-2 border'>{$row['id']}</td>
                        <td class='p-2 border'>
                            <form method='POST' class='flex'>
                                <input type='hidden' name='id' value='{$row['id']}'>
                                <input type='text' name='subject_name' value='{$row['subject_name']}' class='border p-1 rounded w-full'>
                                <button type='submit' name='update_subject' class='ml-2 bg-yellow-500 text-white px-2 rounded'>Update</button>
                            </form>
                        </td>
                        <td class='p-2 border'>{$row['course_name']}</td>
                        <td class='p-2 border'>
                            <a href='?delete={$row['id']}&type=subject' class='text-red-600'>Delete</a>
                        </td>
                      </tr>";
            }
            ?>
        </table>
    </div>

</body>
</html>
```
