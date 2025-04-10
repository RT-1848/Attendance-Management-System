<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title> April 1st</title>
    </head>
    <body>
        <?php
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
        
        ?>
        <link rel="stylesheet" href="style.css">
		<form method="post">
            <h2>Register</h2>
            Username: <input name="username" required><br>
            Password: <input type="password" name="password" required><br>
            <button type="submit">Register</button>
		</form>
    </body>
</html>