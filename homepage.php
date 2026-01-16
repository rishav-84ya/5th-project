<?php
session_start();
require '../db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login/login1.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// --- Get filters from URL (for search functionality) ---
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$university_filter = isset($_GET['university_id']) ? (int)$_GET['university_id'] : null;

// --- Fetch all registered universities with favorite status ---
$sql_universities = "
    SELECT 
        u.id, 
        u.name,
        uf.id AS favorite_id
    FROM universities u
    LEFT JOIN university_favorites uf ON uf.university_id = u.id AND uf.user_id = ?
    ORDER BY u.name ASC
";
$stmt_universities = $conn->prepare($sql_universities);
$stmt_universities->bind_param('i', $user_id);
$stmt_universities->execute();
$universities_result = $stmt_universities->get_result();
$universities = $universities_result->fetch_all(MYSQLI_ASSOC);
$stmt_universities->close();

// --- Build the SQL query for all materials with correct joins ---
$sql_materials = "
    SELECT 
        m.id, m.title, m.description, m.file_path, m.upload_date, m.semester,
        m.subject AS subject_name,
        c.name AS course_name,
        d.name AS department_name,
        u.full_name AS uploader_name,
        uni.name AS university_name,
        mf.id AS favorite_id
    FROM materials m
    LEFT JOIN courses c ON m.course_id = c.id
    LEFT JOIN departments d ON m.department_id = d.id
    LEFT JOIN users u ON m.user_id = u.id
    LEFT JOIN universities uni ON m.university_id = uni.id
    LEFT JOIN material_favorites mf ON mf.material_id = m.id AND mf.user_id = ?
    WHERE 1=1
";

$params = [$user_id];
$types = 'i';

if ($search) {
    $sql_materials .= " AND (
        m.title LIKE ? OR
        m.description LIKE ? OR
        c.name LIKE ? OR
        d.name LIKE ?
    )";
    $search_param = "%" . $search . "%";
    $params = array_merge($params, [$search_param, $search_param, $search_param, $search_param]);
    $types .= "ssss";
}

if ($university_filter) {
    $sql_materials .= " AND m.university_id = ?";
    $params[] = $university_filter;
    $types .= "i";
}

$sql_materials .= " ORDER BY m.upload_date DESC";
$stmt_materials = $conn->prepare($sql_materials);

if ($params) {
    $stmt_materials->bind_param($types, ...$params);
}
$stmt_materials->execute();
$materials_result = $stmt_materials->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage - All Materials</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-100">

    <header class="bg-white shadow-md p-4 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
            <h1 class="text-3xl font-extrabold text-gray-800">EDU-SHARE</h1>
            <nav class="space-x-4">
                <a href="dashboard.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-150">My Dashboard</a>
                <a href="favorites.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-150">Favorites</a>
                <a href="../upload/upload.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-150">Upload</a>
                <a href="../login/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600 transition">Logout</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-extrabold text-gray-900 text-center mb-8">Material Library</h1>
        
        <form action="homepage.php" method="GET" class="mb-8 flex flex-col md:flex-row items-center gap-4">
            <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Search by title, course, or department..." class="flex-1 w-full md:w-auto px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="w-full md:w-auto bg-blue-500 text-white px-6 py-2 rounded-lg hover:bg-blue-600 transition">
                <i class="fas fa-search mr-2"></i> Search
            </button>
            <?php if ($search || $university_filter): ?>
                <a href="homepage.php" class="w-full md:w-auto text-center text-gray-600 hover:text-gray-800 transition">Clear Filters</a>
            <?php endif; ?>
        </form>

        <div class="flex flex-col lg:flex-row gap-8">
            <div class="lg:w-1/4 space-y-4">
                <h2 class="text-2xl font-bold mb-4 text-gray-800">Universities</h2>
                <?php if ($universities): ?>
                    <?php foreach ($universities as $uni): ?>
                        <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-2xl transition duration-300 transform hover:-translate-y-1">
                            <div class="flex justify-between items-start">
                                <h3 class="text-xl font-bold text-gray-800"><?= htmlspecialchars($uni['name']) ?></h3>
                                <button class="uni-favorite-btn text-xl transition" data-university-id="<?= $uni['id'] ?>">
                                    <i class="<?= $uni['favorite_id'] ? 'fas text-red-500' : 'far text-gray-400' ?> fa-heart"></i>
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 mt-2">View materials for this university.</p>
                            <a href="lib.php?uni_id=<?= $uni['id']; ?>" class="mt-4 inline-block text-blue-500 hover:underline">View Materials</a>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center p-6 bg-gray-200 rounded-lg">
                        <p class="text-gray-500 italic">No universities found.</p>
                    </div>
                <?php endif; ?>
            </div>

            <div class="flex-1">
                <section class="bg-white p-6 rounded-xl shadow-lg border border-gray-200">
                    <h2 class="text-2xl font-bold mb-6 text-gray-800">Recent Materials</h2>
                    <?php if ($materials_result->num_rows > 0): ?>
                        <div class="grid grid-cols-1 gap-6">
                            <?php while($mat = $materials_result->fetch_assoc()): ?>
                                <div class="bg-gray-50 p-6 rounded-lg shadow-sm border-l-4 border-green-500 flex flex-col sm:flex-row justify-between items-start sm:items-center">
                                    <div class="flex-1">
                                        <h3 class="text-xl font-semibold text-gray-800 mb-1"><?= htmlspecialchars($mat['title']) ?></h3>
                                        <p class="text-gray-600 text-sm mb-2"><?= htmlspecialchars($mat['description']) ?></p>
                                        <ul class="text-gray-600 text-xs space-y-1">
                                            <li><strong>University:</strong> <?= htmlspecialchars($mat['university_name'] ?? 'N/A') ?></li>
                                            <li><strong>Department:</strong> <?= htmlspecialchars($mat['department_name'] ?? 'N/A') ?></li>
                                            <li><strong>Course:</strong> <?= htmlspecialchars($mat['course_name'] ?? 'N/A') ?></li>
                                            <li><strong>Subject:</strong> <?= htmlspecialchars($mat['subject_name'] ?? 'N/A') ?></li>
                                            <li><strong>Uploaded by:</strong> <?= htmlspecialchars($mat['uploader_name'] ?? 'N/A') ?></li>
                                            <li><strong>Uploaded on:</strong> <?= date('Y-m-d', strtotime($mat['upload_date'])) ?></li>
                                        </ul>
                                    </div>
                                    <div class="flex space-x-3 mt-4 sm:mt-0 text-sm flex-wrap justify-end">
                                        <button class="favorite-btn text-xl transition" data-material-id="<?= $mat['id'] ?>">
                                            <i class="<?= $mat['favorite_id'] ? 'fas text-red-500' : 'far text-gray-400' ?> fa-heart"></i>
                                        </button>
                                        <a href="../profile/download.php?file=<?= urlencode($mat['file_path']) ?>" target="_blank" class="bg-gray-300 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-400 transition">
                                            <i class="fas fa-eye mr-2"></i> View
                                        </a>
                                        <a href="../profile/download.php?file=<?= urlencode($mat['file_path']) ?>&download=1" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition">
                                            <i class="fas fa-download mr-2"></i> Download
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-center text-gray-500 mt-10">No materials found matching your criteria.</p>
                    <?php endif; ?>
                </section>
            </div>
        </div>
    </main>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Existing material favorite button logic
            document.querySelectorAll('.favorite-btn').forEach(button => {
                button.addEventListener('click', async (event) => {
                    const materialId = event.currentTarget.dataset.materialId;
                    const icon = event.currentTarget.querySelector('i');

                    try {
                        const response = await fetch('toggle_favorite.php', {
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

            // New university favorite button logic
            document.querySelectorAll('.uni-favorite-btn').forEach(button => {
                button.addEventListener('click', async (event) => {
                    const universityId = event.currentTarget.dataset.universityId;
                    const icon = event.currentTarget.querySelector('i');

                    try {
                        const response = await fetch('toggle_university_favorite.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ university_id: universityId })
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