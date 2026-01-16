<?php
session_start();
require '../db_connect.php';

// --- Get User ID from URL ---
$user_id_to_view = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$current_user_id = $_SESSION['user_id'] ?? null;

if ($user_id_to_view === 0) {
    header("Location: ../home/homepage.php");
    exit();
}

// --- File Viewing / Download Logic ---
if (isset($_GET['file'])) {
    $filename_from_db = urldecode($_GET['file']);
    $file_to_open = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . $filename_from_db;

    if (!file_exists($file_to_open)) {
        die("File not found: " . htmlspecialchars($filename_from_db));
    }

    $ext = strtolower(pathinfo($file_to_open, PATHINFO_EXTENSION));
    $mime = 'application/octet-stream';
    if ($ext === 'pdf') $mime = 'application/pdf';
    else if ($ext === 'docx') $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    else if ($ext === 'pptx') $mime = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';

    if (isset($_GET['download'])) {
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_to_open) . '"');
    } else {
        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . basename($file_to_open) . '"');
    }
    header('Content-Length: ' . filesize($file_to_open));
    readfile($file_to_open);
    exit;
}

// --- Fetch User Details ---
$stmt = $conn->prepare("
    SELECT 
        u.full_name, u.gmail, u.user_type, u.roll_number, u.branch, u.year, u.contact,
        uni.name AS university_name, 
        dept.name AS department_name
    FROM users u
    LEFT JOIN universities uni ON u.university_id = uni.id
    LEFT JOIN departments dept ON u.department_id = dept.id
    WHERE u.id = ?
");
$stmt->bind_param("i", $user_id_to_view);
$stmt->execute();
$user_details = $stmt->get_result()->fetch_assoc();
$stmt->close();
$user_type = $user_details['user_type'] ?? 'student';

// --- Fetch User's Uploaded Materials (CORRECTED QUERY) ---
$user_materials = [];
$stmt_m = $conn->prepare("
    SELECT 
        m.id, m.title, m.description, m.file_path, m.upload_date,
        c.name AS course_name,
        d.name AS department_name
    FROM materials m
    LEFT JOIN courses c ON m.course_id = c.id
    LEFT JOIN departments d ON m.department_id = d.id
    WHERE m.user_id = ?
    ORDER BY m.upload_date DESC
");

if ($stmt_m) {
    $stmt_m->bind_param("i", $user_id_to_view);
    $stmt_m->execute();
    $user_materials = $stmt_m->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_m->close();
} else {
    echo "Database Error: " . $conn->error;
}

// --- Fetch Favorite Universities ---
$favorites = [];
if ($current_user_id) {
    $fav_stmt = $conn->prepare("
        SELECT u.id, u.name 
        FROM user_favorites uf
        JOIN universities u ON uf.university_id = u.id
        WHERE uf.user_id = ?
        ORDER BY u.name ASC
    ");
    $fav_stmt->bind_param("i", $current_user_id);
    $fav_stmt->execute();
    $fav_result = $fav_stmt->get_result();
    while ($row = $fav_result->fetch_assoc()) {
        $favorites[] = $row;
    }
    $fav_stmt->close();
}

$conn->close();

function get_file_icon($file_path) {
    $ext = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
    switch($ext) {
        case 'pdf': return 'fa-file-pdf text-red-500';
        case 'doc': case 'docx': return 'fa-file-word text-blue-500';
        case 'ppt': case 'pptx': return 'fa-file-powerpoint text-orange-500';
        default: return 'fa-file text-gray-500';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user_details['full_name'] ?? 'User') ?>'s Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50">

<header class="bg-white p-4 shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto flex justify-between items-center">
        <div class="flex items-center space-x-2 text-2xl font-bold text-blue-600">
            <i class="fas fa-user-circle"></i>
            <span><?= htmlspecialchars($user_details['full_name'] ?? 'User') ?>'s Profile</span>
        </div>
        <nav class="space-x-4">
            <a href="../home/homepage.php" class="hover:text-blue-600 transition">Home</a>
            <a href="director.php" class="hover:text-blue-600 transition">Directory</a>
            <a href="../upload/uploads/lib.php" class="hover:text-blue-600 transition">Library</a>
            <?php if ($user_type === 'teacher' && $current_user_id === $user_id_to_view): ?>
            <a href="../upload/upload.php" class="hover:text-blue-600 transition">Upload</a>
            <?php endif; ?>
            <?php if ($current_user_id): ?>
            <a href="../login/logout.php" class="bg-red-500 px-4 py-2 rounded-lg text-white hover:bg-red-600 transition shadow-md">Logout</a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="max-w-7xl mx-auto p-6 space-y-8">

    <section class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
        <div class="flex items-center space-x-6">
            <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center text-blue-600 text-3xl">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <h2 class="text-3xl font-bold text-gray-800"><?= htmlspecialchars($user_details['full_name'] ?? 'User') ?></h2>
                <p class="text-gray-500"><?= htmlspecialchars($user_details['gmail'] ?? 'N/A') ?></p>
            </div>
        </div>
        <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8 text-gray-600">
            <div class="flex items-center"><i class="fas fa-university text-blue-500 mr-3"></i><span>**University:** <?= htmlspecialchars($user_details['university_name'] ?? 'N/A') ?></span></div>
            <div class="flex items-center"><i class="fas fa-sitemap text-blue-500 mr-3"></i><span>**Department:** <?= htmlspecialchars($user_details['department_name'] ?? 'N/A') ?></span></div>
            <div class="flex items-center"><i class="fas fa-id-card-alt text-blue-500 mr-3"></i><span>**Role:** <?= ucfirst(htmlspecialchars($user_type)) ?></span></div>
            <?php if ($user_type === 'student'): ?>
                <div class="flex items-center"><i class="fas fa-calendar-alt text-blue-500 mr-3"></i><span>**Year:** <?= htmlspecialchars($user_details['year'] ?? 'N/A') ?></span></div>
                <div class="flex items-center"><i class="fas fa-code-branch text-blue-500 mr-3"></i><span>**Branch:** <?= htmlspecialchars($user_details['branch'] ?? 'N/A') ?></span></div>
                <div class="flex items-center"><i class="fas fa-hashtag text-blue-500 mr-3"></i><span>**Roll Number:** <?= htmlspecialchars($user_details['roll_number'] ?? 'N/A') ?></span></div>
            <?php endif; ?>
            <div class="flex items-center"><i class="fas fa-phone text-blue-500 mr-3"></i><span>**Contact:** <?= htmlspecialchars($user_details['contact'] ?? 'N/A') ?></span></div>
        </div>
    </section>

    <section class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Uploaded Materials</h2>
        <?php if ($user_materials): ?>
            <ul class="space-y-4">
                <?php foreach ($user_materials as $mat): ?>
                    <li class="bg-gray-100 p-4 rounded-xl shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center">
                        <div class="flex items-center space-x-4">
                            <i class="fas fa-2x <?= get_file_icon($mat['file_path']) ?>"></i>
                            <div>
                                <h3 class="font-semibold text-lg"><?= htmlspecialchars($mat['title']) ?></h3>
                                <p class="text-sm text-gray-500">
                                    **Department:** <?= htmlspecialchars($mat['department_name'] ?? 'N/A') ?> | 
                                    **Uploaded on:** <?= date('Y-m-d', strtotime($mat['upload_date'])) ?>
                                </p>
                            </div>
                        </div>
                        <div class="flex space-x-3 mt-4 md:mt-0">
                            <?php if ($current_user_id !== $user_id_to_view): ?>
                            <a href="../upload/uploads/toggle_favorite.php?material_id=<?= $mat['id'] ?>" class="bg-yellow-400 text-gray-800 py-2 px-4 rounded-lg hover:bg-yellow-500 transition flex items-center">
                                <i class="fas fa-star mr-2"></i> Favorite
                            </a>
                            <?php endif; ?>
                            <a href="?file=<?= urlencode($mat['file_path']) ?>" target="_blank" class="bg-gray-300 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-400 transition flex items-center">
                                <i class="fas fa-eye mr-2"></i> View
                            </a>
                            <a href="?file=<?= urlencode($mat['file_path']) ?>&download=1" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition flex items-center">
                                <i class="fas fa-download mr-2"></i> Download
                            </a>
                            <?php if ($current_user_id === $user_id_to_view): ?>
                            <a href="../upload/uploads/delete_material.php?id=<?= $mat['id'] ?>" onclick="return confirm('Are you sure?')" class="bg-red-500 text-white py-2 px-4 rounded-lg hover:bg-red-600 transition flex items-center">
                                <i class="fas fa-trash-alt mr-2"></i> Delete
                            </a>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="text-gray-500 italic text-center py-6">This user has not uploaded any materials yet.</p>
        <?php endif; ?>
    </section>

    <?php if ($current_user_id): ?>
    <section class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
        <h2 class="text-2xl font-bold mb-6 text-gray-800">Favorite Universities</h2>
        <?php if ($favorites): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <?php foreach ($favorites as $fav): ?>
                    <a href="../upload/uploads/lib.php?uni_id=<?= $fav['id'] ?>" class="bg-gray-100 p-4 rounded-lg shadow-sm hover:shadow-md transition duration-200 hover:bg-gray-200 flex items-center space-x-3">
                        <i class="fas fa-university text-blue-500"></i>
                        <span class="font-semibold text-gray-700"><?= htmlspecialchars($fav['name']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-gray-500 italic text-center py-6">You haven't marked any favorites yet.</p>
        <?php endif; ?>
    </section>
    <?php endif; ?>

</main>
</body>
</html>