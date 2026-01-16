<?php
header('Content-Type: application/json');
require '../db_connect.php';

$university_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($university_id > 0) {
    $stmt = $conn->prepare("SELECT id, name FROM departments WHERE university_id = ? ORDER BY name ASC");
    $stmt->bind_param("i", $university_id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    echo json_encode($result);
    $stmt->close();
} else {
    echo json_encode([]);
}

$conn->close();
?>