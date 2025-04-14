<?php
session_start();
require_once 'config/database.php';

// Basic routing
$page = isset($_GET['page']) ? $_GET['page'] : 'login';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// Redirect logic
if (!$isLoggedIn && $page !== 'login' && $page !== 'register') {
    header('Location: index.php?page=login');
    exit();
}

// Include the appropriate page
$pageFile = 'pages/' . $page . '.php';
if (file_exists($pageFile)) {
    include $pageFile;
} else {
    include 'pages/404.php';
}
?> 