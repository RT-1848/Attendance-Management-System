<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Logout user
logoutUser();

// Clear remember me cookie if exists
if (isset($_COOKIE['remember_user'])) {
    setcookie('remember_user', '', time() - 3600, '/');
}

// Set alert message
$_SESSION['alert'] = [
    'type' => 'success',
    'message' => 'You have been logged out successfully.'
];

// Redirect to login page
redirect('login.php');
?>
