<?php
session_start();
require '../../db_connect.php';

// --- Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login/login1.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// --- Get filters from URL ---
$university_id = isset($_GET['uni_id']) ? (int)$_GET['uni_id'] : null;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sem_filter = isset($_GET['semester']) && $_GET['semester'] != '' ? (int)$_GET['semester'] : null;
$course_filter = isset($_GET['course']) && $_GET['course'] != '' ? (int)$_GET['course'] : null;
$subject_filter = isset($_GET['subject']) && $_GET['subject'] != '' ? (int)$_GET['subject'] : null;

// --- Build the SQL query for all materials ---
$sql = "
    SELECT 
        m.id, m.title, m.description, m.file_path, m.upload_date, m.semester,
        c.name AS course_name,
        d.name AS department_name,
        s.name AS subject_name,
        u.full_name AS uploader_name,
        uni.name AS university_name,
        mf.id AS favorite_id
    FROM materials m
    LEFT JOIN courses c ON m.course_id = c.id
    LEFT JOIN departments d ON m.department_id = d.id
    LEFT JOIN subjects s ON m.subject_id = s.id
    LEFT JOIN users u ON m.user_id = u.id
    LEFT JOIN universities uni ON m.university_id = uni.id
    LEFT JOIN material_favorites mf ON mf.material_id = m.id AND mf.user_id = ?
    WHERE 1=1
";
$params = [$user_id];
$types = 'i';

if ($university_id) {
    $sql .= " AND m.university_id = ?";
    $params[] = $university_id;
    $types .= 'i';
}
if ($sem_filter) {
    $sql .= " AND m.semester = ?";
    $params[] = $sem_filter;
    $types .= 'i';
}
if ($course_filter) {
    $sql .= " AND m.course_id = ?";
    $params[] = $course_filter;
    $types .= 'i';
}
if ($subject_filter) {
    $sql .= " AND m.subject_id = ?";
    $params[] = $subject_filter;
    $types .= 'i';
}
if ($search) {
    $sql .= " AND (m.title LIKE ? OR m.description LIKE ?)";
    $search_param = '%' . $search . '%';
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$sql .= " ORDER BY m.upload_date DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die('SQL Prepare Error: ' . $conn->error);
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$materials_result = $stmt->get_result();

// --- Fetch data for dropdowns to maintain state ---
$universities = $conn->query("SELECT id, name FROM universities ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$courses = $conn->query("SELECT id, name FROM courses ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
$subjects = $conn->query("SELECT id, name FROM subjects ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Material Library</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Poppins', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen">
    <header class="bg-white shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex justify-between items-center">
            <div class="flex items-center space-x-2 text-xl font-bold text-blue-600">
                <i class="fas fa-book-open"></i>
                <span>Material Library</span>
            </div>
            <nav class="flex items-center space-x-6 text-gray-600 font-medium">
                                <a href="../../home/mainhome.php" class="hover:text-blue-600 transition-colors">Home</a>

                <a href="../../home/dashboard.php" class="hover:text-blue-600 transition-colors">Dashboard</a>
                
                <a href="../../home/homepage.php" class="hover:text-blue-600 transition-colors">Hall room</a>
                <?php if ($user_type === 'teacher'): ?>
                    <a href="../../upload/upload.php" class="hover:text-blue-600 transition-colors">Upload</a>
                <?php endif; ?>
                <a href="../../login/logout.php" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600 transition-colors">Logout</a>
            </nav>
        </div>
    </header>

    <main class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl font-extrabold text-gray-900 text-center mb-8">Material Library</h1>

        <div class="bg-white p-6 rounded-xl shadow-lg mb-8">
            <form action="lib.php" method="GET" class="space-y-4 md:space-y-0 md:flex md:space-x-4">
                <div class="flex-1">
                    <label for="search" class="sr-only">Search</label>
                    <input type="text" name="search" id="search" placeholder="Search by title or description..." value="<?= htmlspecialchars($search); ?>" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex-1">
                    <label for="uni_id" class="sr-only">University</label>
                    <select name="uni_id" id="uni_id" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Universities</option>
                        <?php foreach ($universities as $uni): ?>
                            <option value="<?= htmlspecialchars($uni['id']); ?>" <?= ($university_id == $uni['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($uni['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-1">
                    <label for="course" class="sr-only">Course</label>
                    <select name="course" id="course" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Courses</option>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?= htmlspecialchars($course['id']); ?>" <?= ($course_filter == $course['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($course['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-1">
                    <label for="subject" class="sr-only">Subject</label>
                    <select name="subject" id="subject" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Subjects</option>
                        <?php foreach ($subjects as $subject): ?>
                            <option value="<?= htmlspecialchars($subject['id']); ?>" <?= ($subject_filter == $subject['id']) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($subject['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-1">
                    <label for="semester" class="sr-only">Semester</label>
                    <select name="semester" id="semester" class="w-full border border-gray-300 rounded-lg p-3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Semesters</option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i; ?>" <?= ($sem_filter == $i) ? 'selected' : ''; ?>>Semester <?= $i; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow-md w-full md:w-auto">Filter</button>
            </form>
        </div>

        <?php if ($materials_result->num_rows > 0): ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($mat = $materials_result->fetch_assoc()): ?>
                    <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-2xl transition duration-300">
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">
                            <?= htmlspecialchars($mat['title']) ?>
                        </h3>
                        <p class="text-gray-600 text-sm mb-4">
                            <?= nl2br(htmlspecialchars($mat['description'])) ?>
                        </p>
                        <ul class="text-gray-500 text-sm space-y-1">
                            <li><strong>University:</strong> <?= htmlspecialchars($mat['university_name'] ?? 'N/A') ?></li>
                            <li><strong>Department:</strong> <?= htmlspecialchars($mat['department_name'] ?? 'N/A') ?></li>
                            <li><strong>Course:</strong> <?= htmlspecialchars($mat['course_name'] ?? 'N/A') ?></li>
                            <li><strong>Subject:</strong> <?= htmlspecialchars($mat['subject_name'] ?? 'N/A') ?></li>
                            <li><strong>Semester:</strong> <?= htmlspecialchars($mat['semester'] ?? 'N/A') ?></li>
                            <li><strong>Uploaded by:</strong> <?= htmlspecialchars($mat['uploader_name'] ?? 'N/A') ?></li>
                            <li><strong>Uploaded on:</strong> <?= date('Y-m-d', strtotime($mat['upload_date'])) ?></li>
                        </ul>

                        <div class="flex space-x-3 mt-4">
                            <a href="toggle_favorite.php?material_id=<?= $mat['id'] ?>" class="py-2 px-4 rounded-lg transition <?= $mat['favorite_id'] ? 'bg-yellow-500 text-white hover:bg-yellow-600' : 'bg-yellow-400 text-gray-800 hover:bg-yellow-500' ?>">
                                <i class="fas fa-star mr-2"></i> <?= $mat['favorite_id'] ? 'Favorited' : 'Favorite' ?>
                            </a>

                            <a href="download.php?file=<?= urlencode($mat['file_path']) ?>" target="_blank" class="bg-gray-300 text-gray-800 py-2 px-4 rounded-lg hover:bg-gray-400 transition">
    <i class="fas fa-eye mr-2"></i> View
</a>
                            </a>
                            <a href="download.php?file=<?= urlencode($mat['file_path']) ?>&download=1" class="bg-blue-500 text-white py-2 px-4 rounded-lg hover:bg-blue-600 transition">
    <i class="fas fa-download mr-2"></i> Download
</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <p class="text-center text-gray-500 text-lg mt-10">No materials found matching your criteria.</p>
        <?php endif; ?>
    </main>
</body>
</html>