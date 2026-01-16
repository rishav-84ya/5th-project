<?php
header('Content-Type: application/json');
require '../db_connect.php';

$course_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($course_id > 0) {
    $stmt = $conn->prepare("SELECT id, name FROM subjects WHERE course_id = ? ORDER BY name ASC");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($result);
    $stmt->close();
} else {
    echo json_encode([]);
}

$conn->close();
?>