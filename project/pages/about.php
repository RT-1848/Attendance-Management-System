<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Attendance Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="link-button-container">
        <a href="index.php?page=login" class="link-button">Log in</a>
        <a href="index.php?page=register" class="link-button">Register</a>
    </div>
    <div class="information-container">
        <h1>Welcome to the Attendance Management System!</h1>
        <p>Effortlessly mark attendance or create classes with just a few clicks.</p>
        <p>Our system removes the hassle of manual tracking, reducing errors and improving accuracy.</p>
        <p>Generate detailed reports, review attendance history, and monitor overall patterns with ease.</p>
        <p>Designed with simplicity in mind, our user-friendly interface ensures a smooth experience for everyone.</p>
    </div>
    <div class="teacher-container">
        <h1>Teacher Features</h1>
        <p>Create and manage classes effortlessly.</p>
        <p>View real-time student attendance updates.</p>
        <p>Generate comprehensive attendance reports.</p>
        <p>Manually mark students as present from the available list.</p>
        <p>Create and distribute unique class codes for students to join.</p>
    </div>
    <div class="student-container">
        <h1>Student Features</h1>
        <p>Join classes using a unique code provided by your teacher.</p>
        <p>Access and view all your enrolled classes from a simple dashboard.</p>
        <p>Track your attendance percentage for each class in real time.</p>
    </div>
</body>
<style>
    .link-button-container {
        margin-top: 15px;
        margin-bottom: 10px;
        text-align: center;
    }

    .link-button {
        background-color: #3a86ff;
        color: white;
        border-radius: 6px;
        padding: 8px 16px;
        text-decoration: none;
    }
    .link-button:hover {
        text-decoration: none;
        background-color: #2f6cd8;
    }

    .information-container, .teacher-container, .student-container {
        margin: auto;
        margin-top: 10px;
        background-color: white;
        text-align: center;
        height: 200px;
        width: 1250px;
        border-radius: 10px;
    }
</style>