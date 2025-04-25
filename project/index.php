<?php
session_start();
require_once 'config/database.php';

// basic routing to the about page at orgin
$page = isset($_GET['page']) ? $_GET['page'] : 'about';

// check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : null;

// redirect to the about if the user isn't logged in and isn't on the register or about page initially
if (!$isLoggedIn && $page !== 'login' && $page !== 'register' && $page !== 'about') {
    header('Location: index.php?page=about');
    exit();
}

// the path for all the pages
$pageFile = 'pages/' . $page . '.php';
if (file_exists($pageFile)) {
    include $pageFile;
} else {
    include 'pages/404.php';
}
?> 