<?php
header('Content-Type: application/json');
require '../db_connect.php';

$department_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($department_id > 0) {
    $stmt = $conn->prepare("SELECT id, name FROM courses WHERE department_id = ? ORDER BY name ASC");
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($result);
    $stmt->close();
} else {
    echo json_encode([]);
}

$conn->close();
?>