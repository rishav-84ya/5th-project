<?php
session_start();
require '../db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$university_id = $data['university_id'] ?? null;

if (!$university_id) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid university ID.']);
    exit();
}

try {
    // Check if the university is already a favorite
    $sql_check = "SELECT id FROM university_favorites WHERE user_id = ? AND university_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('ii', $user_id, $university_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // It's a favorite, so remove it
        $sql_delete = "DELETE FROM university_favorites WHERE user_id = ? AND university_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param('ii', $user_id, $university_id);
        $stmt_delete->execute();
        echo json_encode(['status' => 'success', 'action' => 'removed']);
    } else {
        // Not a favorite, so add it
        $sql_insert = "INSERT INTO university_favorites (user_id, university_id) VALUES (?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param('ii', $user_id, $university_id);
        $stmt_insert->execute();
        echo json_encode(['status' => 'success', 'action' => 'added']);
    }

    $conn->close();

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>