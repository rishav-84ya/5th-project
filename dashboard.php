<?php
session_start();
require '../db_connect.php';

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login1.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$is_teacher = ($_SESSION['user_type'] === 'teacher');

// --- Fetch User Info without contact_number ---
$stmt_user = $conn->prepare("SELECT u.full_name, u.gmail, u.user_type, uni.name AS university_name, d.name AS department_name FROM users u LEFT JOIN universities uni ON u.university_id = uni.id LEFT JOIN departments d ON u.department_id = d.id WHERE u.id=?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$user_info = $stmt_user->get_result()->fetch_assoc();
$stmt_user->close();

// --- Fetch All Uploaded Materials by this User ---
$sql_materials = "
    SELECT 
        m.id, m.title, m.description, m.file_path, m.upload_date, m.semester,
        c.name AS course_name,
        d.name AS department_name,
        s.name AS subject_name,
        u.full_name AS uploader_name,
        uni.name AS university_name
    FROM materials m
    LEFT JOIN courses c ON m.course_id = c.id
    LEFT JOIN departments d ON m.department_id = d.id
    LEFT JOIN subjects s ON m.subject_id = s.id
    LEFT JOIN users u ON m.user_id = u.id
    LEFT JOIN universities uni ON m.university_id = uni.id
    WHERE m.user_id = ?
    ORDER BY m.upload_date DESC";
$stmt_materials = $conn->prepare($sql_materials);
$stmt_materials->bind_param("i", $user_id);
$stmt_materials->execute();
$uploaded_materials = $stmt_materials->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_materials->close();

// --- Fetch Favorite Universities ---
$sql_favorites = "
    SELECT u.id, u.name
    FROM university_favorites uf
    JOIN universities u ON uf.university_id = u.id
    WHERE uf.user_id = ?
    ORDER BY u.name ASC
";
$stmt_favorites = $conn->prepare($sql_favorites);
$stmt_favorites->bind_param("i", $user_id);
$stmt_favorites->execute();
$favorites = $stmt_favorites->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_favorites->close();

// --- Fetch Favorited Materials ---
$sql_fav_materials = "
    SELECT 
        m.id, m.title, m.description, m.file_path, m.upload_date,
        s.name AS subject_name,
        uni.name AS university_name
    FROM material_favorites mf
    JOIN materials m ON mf.material_id = m.id
    LEFT JOIN subjects s ON m.subject_id = s.id
    LEFT JOIN universities uni ON m.university_id = uni.id
    WHERE mf.user_id = ?
    ORDER BY mf.id DESC
";
$stmt_fav_materials = $conn->prepare($sql_fav_materials);
$stmt_fav_materials->bind_param("i", $user_id);
$stmt_fav_materials->execute();
$favorited_materials = $stmt_fav_materials->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_fav_materials->close();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <header class="bg-white shadow-md p-4 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <h1 class="text-3xl font-extrabold text-gray-800">EDU-SHARE</h1>
            <nav class="space-x-4">
                <a href="mainhome.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-150">Home</a>
                <a href="../upload/uploads/lib.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-150">Library</a>
                <a href="homepage.php" class="text-blue-600 font-medium transition duration-150">Hall Room</a>
                <a href="../login/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">Logout</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12">
            <h1 class="text-4xl font-extrabold text-gray-900 text-center md:text-left">My Dashboard</h1>
            <a href="../upload/upload.php" class="mt-4 md:mt-0 bg-blue-600 text-white font-semibold py-2 px-6 rounded-full shadow-lg hover:bg-blue-700 transition duration-300 transform hover:scale-105">
                <i class="fas fa-plus-circle mr-2"></i> Upload New Material
            </a>
        </div>

        <section class="bg-white p-8 rounded-xl shadow-lg border border-gray-200 mb-8">
            <h2 class="text-3xl font-bold mb-6 text-gray-800 flex items-center">
                <i class="fas fa-user-circle text-blue-500 mr-4 text-4xl"></i>
                Profile Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-gray-700">
                <div class="p-4 bg-gray-100 rounded-lg shadow-sm">
                    <h3 class="font-semibold text-sm text-gray-500 uppercase tracking-wide">Full Name</h3>
                    <p class="text-lg font-medium"><?= htmlspecialchars($user_info['full_name'] ?? 'N/A') ?></p>
                </div>
                <div class="p-4 bg-gray-100 rounded-lg shadow-sm">
                    <h3 class="font-semibold text-sm text-gray-500 uppercase tracking-wide">Email Address</h3>
                    <p class="text-lg font-medium"><?= htmlspecialchars($user_info['gmail'] ?? 'N/A') ?></p>
                </div>
                <div class="p-4 bg-gray-100 rounded-lg shadow-sm">
                    <h3 class="font-semibold text-sm text-gray-500 uppercase tracking-wide">Account Type</h3>
                    <p class="text-lg font-medium"><?= htmlspecialchars(ucfirst($user_info['user_type'] ?? 'N/A')) ?></p>
                </div>
                <div class="p-4 bg-gray-100 rounded-lg shadow-sm">
                    <h3 class="font-semibold text-sm text-gray-500 uppercase tracking-wide">University</h3>
                    <p class="text-lg font-medium"><?= htmlspecialchars($user_info['university_name'] ?? 'N/A') ?></p>
                </div>
                <div class="p-4 bg-gray-100 rounded-lg shadow-sm">
                    <h3 class="font-semibold text-sm text-gray-500 uppercase tracking-wide">Department</h3>
                    <p class="text-lg font-medium"><?= htmlspecialchars($user_info['department_name'] ?? 'N/A') ?></p>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <section class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
                <h2 class="text-3xl font-bold mb-6 text-gray-800 flex items-center">
                    <i class="fas fa-upload text-blue-500 mr-4 text-3xl"></i>
                    My Uploaded Materials
                </h2>
                <?php if (!empty($uploaded_materials)): ?>
                    <div class="grid grid-cols-1 gap-6">
                        <?php foreach ($uploaded_materials as $material): ?>
                            <div class="bg-gray-100 p-5 rounded-lg border border-gray-200 hover:shadow-md transition">
                                <h3 class="font-semibold text-lg text-gray-800 mb-1"><?= htmlspecialchars($material['title']) ?></h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    <span class="font-medium">Subject:</span> <?= htmlspecialchars($material['subject_name'] ?? 'N/A') ?><br>
                                    <span class="font-medium">Department:</span> <?= htmlspecialchars($material['department_name'] ?? 'N/A') ?><br>
                                    <span class="font-medium">University:</span> <?= htmlspecialchars($material['university_name'] ?? 'N/A') ?><br>
                                    <span class="font-medium">Uploaded on:</span> <?= date('F j, Y', strtotime($material['upload_date'])) ?>
                                </p>
                                <div class="flex space-x-2 mt-2">
                                    <a href="../upload/uploads/<?= urlencode($material['file_path']) ?>" target="_blank" class="bg-gray-300 text-gray-800 py-1 px-3 rounded-lg hover:bg-gray-400 transition text-sm">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                    <a href="../upload/uploads/<?= urlencode($material['file_path']) ?>" download class="bg-blue-500 text-white py-1 px-3 rounded-lg hover:bg-blue-600 transition text-sm">
                                        <i class="fas fa-download mr-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 italic text-center py-6">You have not uploaded any materials yet.</p>
                <?php endif; ?>
            </section>

            <section class="bg-white p-8 rounded-xl shadow-lg border border-gray-200">
                <h2 class="text-3xl font-bold mb-6 text-gray-800 flex items-center">
                    <i class="fas fa-star text-yellow-400 mr-4 text-3xl"></i>
                    My Favorite Materials
                </h2>
                <?php if (!empty($favorited_materials)): ?>
                    <div class="grid grid-cols-1 gap-6">
                        <?php foreach ($favorited_materials as $fav_mat): ?>
                            <div class="bg-gray-100 p-5 rounded-lg border border-gray-200 hover:shadow-md transition">
                                <h3 class="font-semibold text-lg text-gray-800 mb-1"><?= htmlspecialchars($fav_mat['title']) ?></h3>
                                <p class="text-sm text-gray-600 mb-2">
                                    <span class="font-medium">Subject:</span> <?= htmlspecialchars($fav_mat['subject_name'] ?? 'N/A') ?><br>
                                    <span class="font-medium">University:</span> <?= htmlspecialchars($fav_mat['university_name'] ?? 'N/A') ?>
                                </p>
                                <div class="flex space-x-2 mt-2">
                                    <a href="../upload/uploads/<?= urlencode($fav_mat['file_path']) ?>" target="_blank" class="bg-gray-300 text-gray-800 py-1 px-3 rounded-lg hover:bg-gray-400 transition text-sm">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </a>
                                    <a href="../upload/uploads/<?= urlencode($fav_mat['file_path']) ?>" download class="bg-blue-500 text-white py-1 px-3 rounded-lg hover:bg-blue-600 transition text-sm">
                                        <i class="fas fa-download mr-1"></i> Download
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-gray-500 italic text-center py-6">You have not favorited any materials yet.</p>
                <?php endif; ?>
            </section>
        </div>

        <section class="bg-white p-8 rounded-xl shadow-lg border border-gray-200 mt-8">
            <h2 class="text-3xl font-bold mb-6 text-gray-800 flex items-center">
                <i class="fas fa-university text-blue-500 mr-4 text-3xl"></i>
                Favorite Universities
            </h2>
            <?php if (!empty($favorites)): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($favorites as $fav): ?>
                        <a href="lib.php?uni_id=<?= $fav['id'] ?>" class="bg-gray-100 p-4 rounded-lg shadow-sm hover:shadow-md transition duration-200 hover:bg-gray-200 flex items-center space-x-4">
                            <i class="fas fa-university text-blue-500 text-xl"></i>
                            <span class="font-semibold text-gray-700"><?= htmlspecialchars($fav['name']) ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500 italic text-center py-6">You haven't favorited any universities yet.</p>
            <?php endif; ?>
        </section>

    </main>

</body>
</html>