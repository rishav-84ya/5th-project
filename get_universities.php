<?php
header('Content-Type: application/json');
require '../db_connect.php';

$sql = "SELECT id, name FROM universities ORDER BY name ASC";
$result = $conn->query($sql);
$universities = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($universities);

$conn->close();
?>