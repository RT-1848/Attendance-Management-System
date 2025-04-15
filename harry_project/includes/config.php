<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');  // Change this to your MySQL username
define('DB_PASS', '');      // Change this to your MySQL password
define('DB_NAME', 'attendance_system');

// Error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Session configuration
session_start();

// Site URL
define('BASE_URL', 'http://localhost/finalproject/');  // Adjust this to your environment

// Time zone
date_default_timezone_set('America/New_York');  // Adjust to your time zone
?>
