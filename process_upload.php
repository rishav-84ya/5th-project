<?php
session_start();
require '../db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_SESSION['user_id'])) {
        die("Error: User not logged in.");
    }

    $user_id = $_SESSION['user_id'];
    $title = htmlspecialchars($_POST['title']);
    $description = htmlspecialchars($_POST['description']);
    $university_id = (int)$_POST['university_id'];
    $department_id = (int)$_POST['department'];
    $course_id = $_POST['course'];
    $subject_id = $_POST['subject_id'];
    $semester = (int)$_POST['semester'];
    
    // Set the upload directory
    $target_dir = "uploads/";
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $upload_date = date('Y-m-d H:i:s');
    $success_count = 0;
    $error_messages = [];
    $allowed_extensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'];

    // Define the maximum file size in bytes (10MB)
    $max_file_size = 10 * 1024 * 1024; 

    // Generate a unique ID for this entire upload session.
    $upload_group_id = uniqid('upload_', true);

    // --- Logic to get or create a Course ---
    if ($course_id === 'new') {
        $new_course_name = trim($_POST['new_course']);
        if (empty($new_course_name)) {
            $error_messages[] = "Error: New course name cannot be empty.";
        } else {
            // First, check if the course already exists anywhere in the database.
            $stmt_check = $conn->prepare("SELECT id FROM courses WHERE name = ?");
            $stmt_check->bind_param("s", $new_course_name);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            if ($result_check->num_rows > 0) {
                // If it exists, use its ID.
                $course_id = $result_check->fetch_assoc()['id'];
            } else {
                // If it doesn't exist, create it.
                $stmt_course = $conn->prepare("INSERT INTO courses (name, department_id, university_id) VALUES (?, ?, ?)");
                $stmt_course->bind_param("sii", $new_course_name, $department_id, $university_id);
                if (!$stmt_course->execute()) {
                    $error_messages[] = "Error creating new course: " . $stmt_course->error;
                }
                $course_id = $conn->insert_id;
                $stmt_course->close();
            }
            $stmt_check->close();
        }
    }

    // --- Logic to get or create a Subject ---
    if ($subject_id === 'new') {
        $new_subject_name = trim($_POST['new_subject']);
        if (empty($new_subject_name)) {
            $error_messages[] = "Error: New subject name cannot be empty.";
        } else {
            // First, check if the subject already exists.
            $stmt_check = $conn->prepare("SELECT id FROM subjects WHERE name = ? AND course_id = ?");
            $stmt_check->bind_param("si", $new_subject_name, $course_id);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();

            if ($result_check->num_rows > 0) {
                // If it exists, use its ID.
                $subject_id = $result_check->fetch_assoc()['id'];
            } else {
                // If it doesn't exist, create it.
                $stmt_subject = $conn->prepare("INSERT INTO subjects (name, course_id, department_id) VALUES (?, ?, ?)");
                $stmt_subject->bind_param("sii", $new_subject_name, $course_id, $department_id);
                if (!$stmt_subject->execute()) {
                    $error_messages[] = "Error creating new subject: " . $stmt_subject->error;
                }
                $subject_id = $conn->insert_id;
                $stmt_subject->close();
            }
            $stmt_check->close();
        }
    }

    // Check if files were uploaded and loop through them
    if (isset($_FILES['files']) && !empty($_FILES['files']['name'][0])) {
        $file_count = count($_FILES['files']['name']);
        
        for ($i = 0; $i < $file_count && $i < 2; $i++) { // Limit to 2 files
            $file_name = $_FILES['files']['name'][$i];
            $file_tmp = $_FILES['files']['tmp_name'][$i];
            $file_error = $_FILES['files']['error'][$i];
            $file_size = $_FILES['files']['size'][$i];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if ($file_error !== UPLOAD_ERR_OK) {
                $error_messages[] = "Error uploading file '{$file_name}': " . $file_error;
                continue;
            }
            
            // Check file size
            if ($file_size > $max_file_size) {
                $error_messages[] = "File '{$file_name}' is too large. Maximum size is 10MB.";
                continue;
            }

            if (!in_array($file_ext, $allowed_extensions)) {
                $error_messages[] = "File '{$file_name}' has an invalid type. Only PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, TXT are allowed.";
                continue;
            }

            $new_file_name = uniqid('material_', true) . '.' . $file_ext;
            $target_file = $target_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $target_file)) {
                $stmt_insert = $conn->prepare("INSERT INTO materials (user_id, title, description, file_path, upload_date, university_id, department_id, course_id, subject_id, semester, upload_group_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt_insert->bind_param("issssiisiss", $user_id, $title, $description, $target_file, $upload_date, $university_id, $department_id, $course_id, $subject_id, $semester, $upload_group_id);

                if ($stmt_insert->execute()) {
                    $success_count++;
                } else {
                    $error_messages[] = "Database error for file '{$file_name}': " . $stmt_insert->error;
                }
                $stmt_insert->close();
            } else {
                $error_messages[] = "Failed to move uploaded file '{$file_name}'. Check directory permissions.";
            }
        }
    } else {
        $error_messages[] = "Please select at least one file.";
    }

    $conn->close();

    if ($success_count > 0) {
        $success_message = "Successfully uploaded {$success_count} file(s).";
        if (!empty($error_messages)) {
            $success_message .= " Some files failed: " . implode(" ", $error_messages);
        }
        header("Location: upload.php?success=" . urlencode($success_message));
        exit();
    } else {
        $error_message = "All uploads failed: " . implode(" ", $error_messages);
        header("Location: upload.php?error=" . urlencode($error_message));
        exit();
    }
}
?>