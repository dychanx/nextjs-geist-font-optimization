<?php
require_once __DIR__ . '/../classes/UserManager.php';

session_start();
$userManager = new UserManager();
$userManager->logout();

header('Location: login.php');
exit;
