<?php
session_start();
// Configuration
$is_logged_in = isset($_SESSION['user_id']);
$user_name = $_SESSION['full_name'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EDU-SHARE - Knowledge Exchange Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #ecf0f1; }
        .hero-section { background: linear-gradient(135deg, #2c3e50, #3498db); }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <header class="bg-white shadow-lg p-4 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <h1 class="text-3xl font-extrabold text-gray-800">EDU-SHARE</h1>
            <nav class="space-x-4">
                <a href="../home/lib.php" class="text-gray-700 hover:text-blue-600 font-medium transition duration-150">Browse Materials</a>
                <?php if ($is_logged_in): ?>
                    <span class="text-gray-600 font-medium hidden sm:inline">Welcome, <?php echo htmlspecialchars($user_name); ?></span>
                    <a href="../home/dashboard.php" class="bg-green-500 hover:bg-green-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150">
                        Dashboard
                    </a>
                    <a href="../login/logout.php" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150">
                        Logout
                    </a>
                <?php else: ?>
                    <a href="../login/login1.php" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150">
                        Log In
                    </a>
                    <a href="../register/register.php" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg shadow-md transition duration-150">
                        Register
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="flex-grow">
        
        <section class="hero-section text-white text-center py-20 px-4 shadow-xl">
            <h2 class="text-5xl md:text-6xl font-black mb-4">Share Knowledge, Empower Peers</h2>
            <p class="text-xl md:text-2xl font-light max-w-3xl mx-auto mb-8">
                The centralized platform for students and teachers to exchange study materials, notes, and resources tailored to specific universities and courses.
            </p>
            <?php if (!$is_logged_in): ?>
                <a href="../login/login1.php" class="inline-block bg-yellow-400 text-gray-900 font-extrabold text-lg py-3 px-8 rounded-full shadow-lg hover:bg-yellow-300 transform hover:scale-105 transition duration-300">
                    Get Started Now
                </a>
            <?php else: ?>
                <a href="../home/dashboard.php" class="inline-block bg-yellow-400 text-gray-900 font-extrabold text-lg py-3 px-8 rounded-full shadow-lg hover:bg-yellow-300 transform hover:scale-105 transition duration-300">
                    Go to Dashboard
                </a>
            <?php endif; ?>
        </section>

        <section class="py-16 px-4">
            <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-2xl transition duration-300 text-center">
                    <div class="text-blue-600 text-4xl mb-3">📘</div>
                    <h3 class="text-xl font-bold mb-2 text-gray-800">Targeted Materials</h3>
                    <p class="text-gray-600">Find notes, exam papers, and guides specific to your university, department, and branch.</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-2xl transition duration-300 text-center">
                    <div class="text-green-600 text-4xl mb-3">👨‍🏫</div>
                    <h3 class="text-xl font-bold mb-2 text-gray-800">Teacher Contributions</h3>
                    <p class="text-gray-600">Teachers can upload verified resources, ensuring quality and relevance for students.</p>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-lg hover:shadow-2xl transition duration-300 text-center">
                    <div class="text-red-600 text-4xl mb-3">🔒</div>
                    <h3 class="text-xl font-bold mb-2 text-gray-800">Secure & Organized</h3>
                    <p class="text-gray-600">Everything is neatly organized by course and subject, making searching easy.</p>
                </div>
            </div>
        </section>

    </main>
    
    <footer class="bg-gray-800 text-white p-4 text-center">
        &copy; <?php echo date('Y'); ?> EDU-SHARE. All rights reserved.
    </footer>
</body>
</html>
