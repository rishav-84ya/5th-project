<?php
session_start();
// This is the corrected path to the database connection file
require '../db_connect.php'; 

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login1.php");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$material_id = (int)$_GET['id'];

// 1. Get the file path from the database and verify ownership
$stmt = $conn->prepare("SELECT file_path, user_id FROM materials WHERE id = ?");
$stmt->bind_param("i", $material_id);
$stmt->execute();
$result = $stmt->get_result();
$material = $result->fetch_assoc();
$stmt->close();

if (!$material || $material['user_id'] != $user_id) {
    die("Error: Material not found or you do not have permission to delete it.");
}

$file_path = $material['file_path'];

// 2. Delete the database record first
$stmt = $conn->prepare("DELETE FROM materials WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $material_id, $user_id);
$stmt->execute();
$stmt->close();

// 3. Delete the physical file from the server
// This path is also corrected to be relative to the 'home' directory
$full_file_path = __DIR__ . '/../upload/' . $file_path;
if (file_exists($full_file_path)) {
    unlink($full_file_path);
}

$conn->close();

// Redirect back to the dashboard after successful deletion.
header("Location: dashboard.php");
exit();
?>