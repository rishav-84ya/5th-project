<?php
session_start();
require '../db_connect.php';

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login1.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- Fetch User's University and Department ID ---
$user_uni_id = null;
$user_dept_id = null;
$stmt_user = $conn->prepare("SELECT university_id, department_id FROM users WHERE id = ?");
if ($stmt_user) {
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    if ($row = $result_user->fetch_assoc()) {
        $user_uni_id = $row['university_id'];
        $user_dept_id = $row['department_id'];
    }
    $stmt_user->close();
}

// --- Fetch all departments (to populate the dropdown) ---
$departments = [];
$sql_dept = "SELECT id, name FROM departments ORDER BY name ASC";
$dept_result = $conn->query($sql_dept);
if ($dept_result) {
    $departments = $dept_result->fetch_all(MYSQLI_ASSOC);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Materials</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
        .hidden { display: none; }
    </style>
</head>
<body class="bg-gray-100 flex flex-col items-center justify-center min-h-screen">

<header class="bg-white shadow-md p-4 w-full sticky top-0 z-10">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
        <h1 class="text-3xl font-extrabold text-gray-800">EDU-SHARE</h1>
       <nav class="space-x-4">
    <a href="../home/homepage.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-150">Home</a>
    <a href="../home/lib.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-150">Library</a>
    <a href="../home/dashboard.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-150">My Dashboard</a>
    <a href="upload.php" class="text-blue-600 font-medium transition duration-150">Upload</a>
    <a href="../login/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">Logout</a>
</nav>

    </div>
</header>

<div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-2xl border border-gray-200 mt-12 mb-12">
    <h1 class="text-3xl font-bold mb-6 text-gray-800 text-center">Upload New Material</h1>
    <p class="text-center text-gray-600 mb-8">You can upload up to 2 files at once (max 10MB per file).</p>

    <form action="process_upload.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="university_id" value="<?= htmlspecialchars($user_uni_id); ?>">

        <div class="mb-4">
            <label for="title" class="block text-gray-700 font-semibold mb-2">Title:</label>
            <input type="text" name="title" id="title" required class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="mb-4">
            <label for="description" class="block text-gray-700 font-semibold mb-2">Description:</label>
            <textarea name="description" id="description" rows="4" class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
        </div>

        <div class="mb-4">
            <label for="department" class="block text-gray-700 font-semibold mb-2">Department:</label>
            <select name="department" id="department" required class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Department</option>
                <?php foreach ($departments as $dept): ?>
                    <option value="<?= htmlspecialchars($dept['id']); ?>" <?= ($user_dept_id == $dept['id']) ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($dept['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-4">
            <label for="course" class="block text-gray-700 font-semibold mb-2">Course:</label>
            <select name="course" id="course" required class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Course</option>
                <option value="new">Create New Course...</option>
            </select>
            <input type="text" name="new_course" id="new_course" placeholder="Enter new course name" class="w-full border border-gray-300 rounded-lg p-2 mt-2 focus:outline-none focus:ring-2 focus:ring-blue-500 hidden">
        </div>

        <div class="mb-4">
            <label for="subject" class="block text-gray-700 font-semibold mb-2">Subject:</label>
            <select name="subject_id" id="subject" required class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Subject</option>
                <option value="new">Create New Subject...</option>
            </select>
            <input type="text" name="new_subject" id="new_subject" placeholder="Enter new subject name" class="w-full border border-gray-300 rounded-lg p-2 mt-2 focus:outline-none focus:ring-2 focus:ring-blue-500 hidden">
        </div>

        <div class="mb-4">
            <label for="semester" class="block text-gray-700 font-semibold mb-2">Semester:</label>
            <select name="semester" id="semester" required class="w-full border border-gray-300 rounded-lg p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Semester</option>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?= $i; ?>">Semester <?= $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <div class="mb-6">
            <label for="files" class="block text-gray-700 font-semibold mb-2">Select Files to Upload (Max 2, 10MB each):</label>
            <input type="file" name="files[]" id="files" multiple required class="w-full text-gray-700 p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow-md">Upload Material</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const departmentSelect = document.getElementById('department');
        const courseSelect = document.getElementById('course');
        const newCourseInput = document.getElementById('new_course');
        const subjectSelect = document.getElementById('subject');
        const newSubjectInput = document.getElementById('new_subject');

        function fetchCourses(departmentId) {
            if (!departmentId) {
                courseSelect.innerHTML = '<option value="">Select Course</option><option value="new">Create New Course...</option>';
                return;
            }
            
            fetch(`../ajax/get_courses.php?id=${departmentId}`)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">Select Course</option>';
                    data.forEach(course => {
                        options += `<option value="${course.id}">${course.name}</option>`;
                    });
                    options += '<option value="new">Create New Course...</option>';
                    courseSelect.innerHTML = options;
                });
        }

        function fetchSubjects(courseId) {
            if (!courseId || courseId === 'new') {
                subjectSelect.innerHTML = '<option value="">Select Subject</option><option value="new">Create New Subject...</option>';
                newSubjectInput.classList.remove('hidden');
                return;
            }

            fetch(`../ajax/get_subjects.php?id=${courseId}`)
                .then(response => response.json())
                .then(data => {
                    let options = '<option value="">Select Subject</option>';
                    data.forEach(subject => {
                        options += `<option value="${subject.id}">${subject.name}</option>`;
                    });
                    options += '<option value="new">Create New Subject...</option>';
                    subjectSelect.innerHTML = options;
                });
        }

        departmentSelect.addEventListener('change', (e) => {
            fetchCourses(e.target.value);
            newCourseInput.classList.add('hidden');
            newCourseInput.required = false;
            subjectSelect.innerHTML = '<option value="">Select Subject</option><option value="new">Create New Subject...</option>';
            newSubjectInput.classList.add('hidden');
            newSubjectInput.required = false;
        });

        courseSelect.addEventListener('change', (e) => {
            const selectedValue = e.target.value;
            if (selectedValue === 'new') {
                newCourseInput.classList.remove('hidden');
                newCourseInput.required = true;
                newSubjectInput.classList.remove('hidden');
                newSubjectInput.required = true;
            } else {
                newCourseInput.classList.add('hidden');
                newCourseInput.required = false;
                fetchSubjects(selectedValue);
            }
        });
        
        subjectSelect.addEventListener('change', (e) => {
            const selectedValue = e.target.value;
            if (selectedValue === 'new') {
                newSubjectInput.classList.remove('hidden');
                newSubjectInput.required = true;
            } else {
                newSubjectInput.classList.add('hidden');
                newSubjectInput.required = false;
            }
        });

        // Initial load for pre-selected department
        if (departmentSelect.value) {
            fetchCourses(departmentSelect.value);
        }
    });
</script>

</body>
</html>