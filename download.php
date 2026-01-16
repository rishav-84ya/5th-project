<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    die("Access denied. Please log in.");
}

// Check if a file is specified
if (!isset($_GET['file'])) {
    http_response_code(400);
    die("File not specified.");
}

// Sanitize the filename to prevent directory traversal attacks
$filename = basename(urldecode($_GET['file']));
$file_path = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $filename;

// Check if the file exists and is a file
if (!file_exists($file_path) || !is_file($file_path)) {
    http_response_code(404);
    die("File not found.");
}

// Determine the file's MIME type
$mime_type = 'application/octet-stream';
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
switch ($extension) {
    case 'pdf':
        $mime_type = 'application/pdf';
        break;
    case 'doc':
    case 'docx':
        $mime_type = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
        break;
    case 'ppt':
    case 'pptx':
        $mime_type = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
        break;
    case 'xls':
    case 'xlsx':
        $mime_type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        break;
    // Add more cases for other file types if needed
}

// Set headers for download or inline view
header('Content-Description: File Transfer');
header('Content-Type: ' . $mime_type);
header('Content-Disposition: ' . (isset($_GET['download']) ? 'attachment' : 'inline') . '; filename="' . $filename . '"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . filesize($file_path));

// Read the file and send it to the browser
readfile($file_path);
exit;
?>