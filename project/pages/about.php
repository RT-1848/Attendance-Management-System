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
        <img src="assets/pictures/mt-logo.png">
        <h1 class="link-title">Attendance Management System</h1>
        <div class="link-button-group">
            <a href="index.php?page=login" class="link-button">Log in</a>
            <a href="index.php?page=register" class="link-button">Register</a>
        </div>
    </div>
    <div class="about-container">
        <div class="about-feature-card">
            <h1>✨ About Our System!</h1>
            <ul>
                <li>Effortlessly mark attendance or create classes with just a few clicks.</li>
                <li>Our system removes the hassle of manual tracking, reducing errors and improving accuracy.</li>
                <li>Review attendance history and monitor overall patterns with ease.</li>
                <li>Made to be simple and easy for both teachers and students.</li>
            </ul>
        </div>
        <div class="about-feature-card">
            <h1>👩🏻‍💻 Teacher Features</h1>
            <ul>
                <li>Create and manage classes with ease.</li>
                <li>See live updates of student attendance.</li>
                <li>View attendance summaries as charts or percentages.</li>
                <li>Generate and distribute unique class codes for students to join.</li>
                <li>Create an attendance code each day for students to mark themselves present.</li>
            </ul>
        </div>
        <div class="about-feature-card">
            <h1>☝️🤓 Student Features</h1>
            <ul>
                <li>Join classes using a unique code provided by your teacher.</li>
                <li>View all your enrolled classes from a simple dashboard.</li>
                <li>Track your attendance percentage for each class.</li>
                <li>Mark yourself as present for your class.</li>
            </ul>
        </div>
    </div>
</body>
<footer>
    <p>&copy MTSU CSCI 4410 | All Rights Reserved</p>
    <strong>Youssef Botros | Harry He | Khalid Khalel | Henry Ngo | Ryan Thieu | Marcos Wofford</strong>
</footer>
</html>

<style>
    .link-button-container {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: flex-end;
        padding: 16px 32px;
        background-color: #ffffff;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    
    .link-button-container img {
        margin-right: auto;
        width: 60px;
        height: 40px;
    }

    .link-button-group {
        display: flex;
    }

    .link-button-container .link-title {
        position: absolute;
        left: 50%;
        transform: translateX(-50%);
        font-size: 28px;
        color: #1e293b;
        margin: 0;
        color: #2563eb;
    }

    .link-button {
        background-color: #2563eb;
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        margin-left: 16px;
    }

    .link-button:hover {
        background-color: #1e40af;
        color: white;
        text-decoration: none;
    }

    .about-container {
        padding: 32px;
        margin: 0;
        width: 100%;
        display: flex;
        flex-wrap: nowrap;           
        justify-content: space-between;
        min-height: 100vh;
    }

    .about-feature-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 28px 32px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        min-height: 550px;
        width: 32%;                
    }

    .about-feature-card h1 {
        font-size: 28px;
        color: #1e293b;
        margin-bottom: 32px;
        border-bottom: 2px solid #e2e8f0;
        padding-bottom: 8px;
    }

    .about-container ul {
        list-style-type: disc;
        padding-left: 1.25rem;
        margin-bottom: 1rem;
    }

    .about-container li {
        font-size: 20px;
        margin-bottom: 10px;
        line-height: 1.6;
        color: #334155;
    }
    
    @media (max-width: 900px) {
        .link-title {
            position: static !important;
            transform: none !important;
            text-align: center;
            font-size: 22px;
            width: 100%;
        }
    
        .link-button-container {
            flex-direction: column;
            align-items: center;
            padding: 16px;
            gap: 12px;
        }

        .link-button-group {
            display: flex;
            justify-content: center;
            width: 100%;
            flex-wrap: wrap;
        }

        .link-button-container img {
            margin: 0 auto;
            display: block;
        }

        .link-button {
            font-size: 16px;
            padding: 8px 14px;
            margin-left: 5px;
        }

        .about-container {
            flex-direction: column;
            padding: 16px;
        }

        .about-feature-card {
            width: 100%;
            margin-bottom: 24px;
            height: auto;
        }

        .about-feature-card h1 {
            font-size: 24px;
            text-align: center;
        }

        .about-container li {
            font-size: 18px;
        }
    }
</style>