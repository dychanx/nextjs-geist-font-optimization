<?php
require_once __DIR__ . '/../classes/UserManager.php';
require_once __DIR__ . '/../classes/DashboardGenerator.php';

session_start();

// Check if user is logged in
$userManager = new UserManager();
if (!$userManager->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Check session timeout
if (!$userManager->checkSessionTimeout()) {
    header('Location: login.php');
    exit;
}

$dashboard = new DashboardGenerator();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - CRUD Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <span class="text-2xl font-bold text-gray-800">Admin Panel</span>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="index.php" 
                           class="border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <?php if ($_SESSION['user_level'] === 'admin'): ?>
                        <a href="users.php" 
                           class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Users
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="hidden sm:ml-6 sm:flex sm:items-center">
                    <div class="ml-3 relative">
                        <div class="flex items-center space-x-4">
                            <span class="text-gray-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <a href="logout.php" 
                               class="text-gray-700 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium">
                                Logout
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <!-- Welcome Section -->
            <div class="px-4 py-5 sm:px-6">
                <h1 class="text-2xl font-bold text-gray-900">
                    Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!
                </h1>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">
                    Here's an overview of your database tables and records.
                </p>
            </div>

            <!-- Dashboard Content -->
            <?php 
            // Display any flash messages
            if (isset($_SESSION['flash_message'])) {
                echo '<div class="mb-4 px-4 sm:px-6">';
                echo '<div class="rounded-md bg-green-50 p-4">';
                echo '<div class="flex">';
                echo '<div class="flex-shrink-0">';
                echo '<svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">';
                echo '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>';
                echo '</svg>';
                echo '</div>';
                echo '<div class="ml-3">';
                echo '<p class="text-sm font-medium text-green-800">' . htmlspecialchars($_SESSION['flash_message']) . '</p>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
                unset($_SESSION['flash_message']);
            }

            // Generate and display the dashboard content
            echo $dashboard->generateDashboard();
            
            // Generate and display system statistics
            echo $dashboard->generateSystemStats();
            ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white shadow mt-8">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center">
                <div class="text-gray-500 text-sm">
                    &copy; <?php echo date('Y'); ?> CRUD Dashboard. All rights reserved.
                </div>
                <div class="text-gray-500 text-sm">
                    Version 1.0.0
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
