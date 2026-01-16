<?php
session_start();
require '../../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login1.php");
    exit();
}

if (!isset($_GET['material_id'])) {
    header("Location: lib.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$material_id = (int)$_GET['material_id'];

// Check if the material is already a favorite
$stmt = $conn->prepare("SELECT COUNT(*) FROM material_favorites WHERE user_id = ? AND material_id = ?");
$stmt->bind_param("ii", $user_id, $material_id);
$stmt->execute();
$count = $stmt->get_result()->fetch_row()[0];
$stmt->close();

if ($count > 0) {
    // It is a favorite, so remove it
    $stmt = $conn->prepare("DELETE FROM material_favorites WHERE user_id = ? AND material_id = ?");
} else {
    // Not a favorite, so add it
    $stmt = $conn->prepare("INSERT INTO material_favorites (user_id, material_id) VALUES (?, ?)");
}

$stmt->bind_param("ii", $user_id, $material_id);
$stmt->execute();
$stmt->close();
$conn->close();

// Redirect back to the library page
header("Location: lib.php");
exit();
?>