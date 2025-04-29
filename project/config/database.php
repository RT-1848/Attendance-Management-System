<?php
$host = 'database-1.ctuosie4ur9o.us-east-2.rds.amazonaws.com';
$dbname = 'attendance_tracker';
$username = 'admin';
$password = 'Mtsu1234';

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?> 