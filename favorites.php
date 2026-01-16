<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login1.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$page_title = "My Favorites";

// Fetch favorited materials
$sql = "
    SELECT 
        m.id, m.title, m.description, m.file_path, m.upload_date,
        s.name AS subject_name,
        c.name AS course_name,
        d.name AS department_name,
        u.full_name AS uploader_name,
        uni.name AS university_name
    FROM material_favorites mf
    JOIN materials m ON mf.material_id = m.id
    LEFT JOIN subjects s ON m.subject_id = s.id
    LEFT JOIN courses c ON m.course_id = c.id
    LEFT JOIN departments d ON m.department_id = d.id
    LEFT JOIN users u ON m.user_id = u.id
    LEFT JOIN universities uni ON m.university_id = uni.id
    WHERE mf.user_id = ?
    ORDER BY mf.favorited_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$favorites_result = $stmt->get_result();

$grouped_materials = [];
while ($mat = $favorites_result->fetch_assoc()) {
    $group_key = $mat['id']; // Each favorited material is unique in this view
    if (!isset($grouped_materials[$group_key])) {
        $grouped_materials[$group_key] = [
            'group_info' => $mat,
            'files' => []
        ];
    }
    $grouped_materials[$group_key]['files'][] = $mat;
}

$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <header class="bg-white shadow-md p-4 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <h1 class="text-3xl font-extrabold text-gray-800">EDU-SHARE</h1>
            <nav class="space-x-4">
                <a href="homepage.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-150">Home</a>
                <a href="lib.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-150">Library</a>
                <a href="dashboard.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-150">My Dashboard</a>
                <a href="favorites.php" class="text-blue-600 font-medium transition duration-150">Favorites</a>
                <a href="../upload/upload.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-150">Upload</a>
                <a href="../login/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">Logout</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-extrabold text-gray-900 text-center mb-8"><?= $page_title ?></h1>

        <?php if (empty($grouped_materials)): ?>
            <p class="text-center text-gray-500 mt-10">You have not favorited any materials yet.</p>
        <?php else: ?>
            <?php foreach ($grouped_materials as $group):
                $main_mat = $group['group_info'];
                $files = $group['files'];
            ?>
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500 mb-6">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-xl font-semibold text-gray-800"><?= htmlspecialchars($main_mat['title']) ?></h3>
                        <button class="favorite-btn text-xl transition" data-material-id="<?= $main_mat['id'] ?>">
                            <i class="fas text-red-500 fa-heart"></i>
                        </button>
                    </div>
                    <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($main_mat['description']) ?></p>
                    <ul class="text-gray-600 text-xs space-y-1">
                        <li><strong>University:</strong> <?= htmlspecialchars($main_mat['university_name'] ?? 'N/A') ?></li>
                        <li><strong>Department:</strong> <?= htmlspecialchars($main_mat['department_name'] ?? 'N/A') ?></li>
                        <li><strong>Course:</strong> <?= htmlspecialchars($main_mat['course_name'] ?? 'N/A') ?></li>
                        <li><strong>Subject:</strong> <?= htmlspecialchars($main_mat['subject_name'] ?? 'N/A') ?></li>
                        <li><strong>Uploaded by:</strong> <?= htmlspecialchars($main_mat['uploader_name'] ?? 'N/A') ?></li>
                        <li><strong>Uploaded on:</strong> <?= date('Y-m-d', strtotime($main_mat['upload_date'])) ?></li>
                    </ul>
                    <div class="mt-4 border-t border-gray-200 pt-4 space-y-2">
                        <p class="font-semibold text-sm text-gray-700">Attached Files:</p>
                        <?php foreach ($files as $file): ?>
                            <div class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200">
                                <span class="text-sm font-medium text-gray-800">
                                    <i class="fas fa-file-alt mr-2"></i> <?= htmlspecialchars(basename($file['file_path'])) ?>
                                </span>
                                <div class="flex space-x-2 text-sm">
                                    <a href="../profile/download.php?file=<?= urlencode($file['file_path']) ?>" target="_blank" class="bg-gray-300 text-gray-800 py-1 px-3 rounded-lg hover:bg-gray-400 transition">
                                        <i class="fas fa-eye mr-2"></i> View
                                    </a>
                                    <a href="../profile/download.php?file=<?= urlencode($file['file_path']) ?>&download=1" class="bg-blue-500 text-white py-1 px-3 rounded-lg hover:bg-blue-600 transition">
                                        <i class="fas fa-download mr-2"></i> Download
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.favorite-btn').forEach(button => {
                button.addEventListener('click', async (event) => {
                    const materialId = event.currentTarget.dataset.materialId;
                    const icon = event.currentTarget.querySelector('i');

                    try {
                        const response = await fetch('../profile/toggle_favorite.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ material_id: materialId })
                        });
                        const data = await response.json();

                        if (data.status === 'success') {
                            if (data.action === 'added') {
                                icon.classList.remove('far', 'text-gray-400');
                                icon.classList.add('fas', 'text-red-500');
                            } else {
                                icon.classList.remove('fas', 'text-red-500');
                                icon.classList.add('far', 'text-gray-400');
                            }
                        } else {
                            console.error('Failed to update favorite status:', data.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    }
                });
            });
        });
    </script>
</body>
</html>