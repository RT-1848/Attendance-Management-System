<?php
//This is the page where the user logs into their existing account
//since a password is being sent the post method is used for security
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //collecting the username and password securly
    $username = htmlspecialchars( $_POST['username']);
    $password = $_POST['password'];

    //getting user information from users table and storing it in result
    $query = "SELECT id, username, password, role FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    
    //checking if the information could be retreived
    if ($result && mysqli_num_rows($result) > 0) {
        //if so store is as an associative array
        $user = mysqli_fetch_assoc($result);
        //checking if the password input matches the hashed password stored in the database
        if (password_verify($password, $user['password'])) {
            //if so set session variables to their counter parts from the table
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            //change the browser header to the dashboard page
            header('Location: index.php?page=dashboard');
            exit();
        }
    }
    //other wise report an error
    $error = "Invalid username or password";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Attendance Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="login-form">
            <!-- header for the login page -->
            <h2>Login</h2>
            <!-- checking if the error variable is set indecating that there is an error. If there is print it. -->
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <!-- form to get the username and password both of which are required -->
            <form method="POST" action="">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                    <!-- adding a picture to toggle password display -->
                    <img src=
                    "https://media.geeksforgeeks.org/wp-content/uploads/20210917145551/eye.png"
                     width="5%"
                     height="5%"
                     style=
                         "display: inline; 
                         margin-left: 93%;
                         transform: translateY(-30px);
                         vertical-align: middle;
                         cursor: pointer"
                     id="togglePassword">
                </div>
                <button type="submit">Login</button>
            <!-- Redirect link at the bottom that takes user to the register page to register an account if they don't have one -->
            <p>Don't have an account? <a href="index.php?page=register">Register here</a></p>
        </div>
    </div>
    <script>
        // get the password input and toggle button
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        
        // click event listener to toggle password
        togglePassword.addEventListener('click', function() {
            // if it is a password change to text and vice versa
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // change the eye icon
            this.src = type === 'password' 
                ? 'https://media.geeksforgeeks.org/wp-content/uploads/20210917145551/eye.png'
                : 'https://media.geeksforgeeks.org/wp-content/uploads/20210917150049/eyeslash.png';
        });
    </script>
</body>
<footer>
    <p>&copy MTSU CSCI 4410 | All Rights Reserved</p>
    <strong>Youssef Botros | Harry He | Khalid Khalel | Henry Ngo | Ryan Thieu | Marcos Wofford</strong>
</footer>
</html> 