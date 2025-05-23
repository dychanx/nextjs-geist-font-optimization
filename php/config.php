<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'crud_dashboard');

// Application settings
define('BASE_URL', 'http://localhost:8000/php/public/');
define('SESSION_TIMEOUT', 3600); // 1 hour

// Error logging
ini_set('log_errors', 'On');
ini_set('error_log', __DIR__ . '/logs/app.log');

// Security settings
ini_set('display_errors', 'Off');
error_reporting(E_ALL);

// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
