<?php
require_once __DIR__ . '/../classes/UserManager.php';
require_once __DIR__ . '/../classes/CrudGenerator.php';

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

// Get parameters
$action = $_GET['action'] ?? 'list';
$table = $_GET['table'] ?? '';
$id = $_GET['id'] ?? null;
$page = (int)($_GET['page'] ?? 1);

// Validate table name (prevent SQL injection)
if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
    die('Invalid table name');
}

$crud = new CrudGenerator($table);
$message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    switch ($action) {
        case 'create':
            $result = $crud->create($_POST);
            if ($result['success']) {
                $_SESSION['flash_message'] = 'Record created successfully';
                header("Location: crud.php?action=list&table=$table");
                exit;
            } else {
                $message = $result['message'];
            }
            break;

        case 'update':
            if ($id) {
                $result = $crud->update($id, $_POST);
                if ($result['success']) {
                    $_SESSION['flash_message'] = 'Record updated successfully';
                    header("Location: crud.php?action=list&table=$table");
                    exit;
                } else {
                    $message = $result['message'];
                }
            }
            break;
    }
}

// Handle delete action
if ($action === 'delete' && $id) {
    $result = $crud->delete($id);
    if ($result['success']) {
        $_SESSION['flash_message'] = 'Record deleted successfully';
        header("Location: crud.php?action=list&table=$table");
        exit;
    } else {
        $message = $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ucfirst($action) . ' ' . ucfirst($table); ?> - CRUD Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="flex-shrink-0 flex items-center">
                        <a href="index.php" class="text-2xl font-bold text-gray-800">Admin Panel</a>
                    </div>
                    <div class="hidden sm:ml-6 sm:flex sm:space-x-8">
                        <a href="index.php" 
                           class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <span class="border-blue-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <?php echo ucfirst($table); ?>
                        </span>
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
            <!-- Page Header -->
            <div class="px-4 py-5 sm:px-6 bg-white shadow rounded-lg mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
                            <?php echo ucfirst($action) . ' ' . ucfirst($table); ?>
                        </h2>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Manage your <?php echo $table; ?> records
                        </p>
                    </div>
                    <?php if ($action === 'list'): ?>
                    <div>
                        <a href="crud.php?action=create&table=<?php echo $table; ?>" 
                           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Add New Record
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Flash Messages -->
            <?php if (isset($_SESSION['flash_message'])): ?>
                <div class="rounded-md bg-green-50 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800">
                                <?php 
                                echo htmlspecialchars($_SESSION['flash_message']);
                                unset($_SESSION['flash_message']);
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($message): ?>
                <div class="rounded-md bg-red-50 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-red-800">
                                <?php echo htmlspecialchars($message); ?>
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- CRUD Content -->
            <div class="bg-white shadow rounded-lg">
                <?php
                switch ($action) {
                    case 'create':
                        echo $crud->generateCreateForm();
                        break;
                    
                    case 'edit':
                        if ($id) {
                            echo $crud->generateEditForm($id);
                        } else {
                            echo '<div class="p-4 text-red-500">No record ID provided</div>';
                        }
                        break;
                    
                    case 'list':
                    default:
                        echo $crud->generateListView($page);
                        break;
                }
                ?>
            </div>
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
