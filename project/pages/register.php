<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstname = mysqli_real_escape_string($conn, $_POST['firstname']);
    $lastname = mysqli_real_escape_string($conn, $_POST['lastname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $role = mysqli_real_escape_string($conn, $_POST['role']);

    $conditions = [
        'letter' => [
            'pattern' => '/[A-Za-z]/',
            'message' => 'Password must contain at least one letter.'
        ],
        'number' => [
            'pattern' => '/[0-9]/',
            'message' => 'Password must contain at least one number.'
        ],
        'special' => [
            'pattern' => '/[\W_]/',
            'message' => 'Password must contain at least one special character.'
        ]
    ];

    $valid = true;
    foreach ($conditions as $key => $rule) {
        if (!preg_match($rule['pattern'], $password)) {
            $errorList[$key] = false;
            $valid = false;
        } else {
            $errorList[$key] = true;
        }
    }
    // Validate input
    $error = "";
    if (empty($username) || empty($email) || empty($password) && $valid) {
        $error = "All fields are required";
    }
    else if(!$valid){
        if (!isset($errorList['letter']) || !$errorList['letter']) {
            $error .= "Password must contain at least one letter.<br>";
        }
        if (!isset($errorList['number']) || !$errorList['number']) {
            $error .= "Password must contain at least one number.<br>";
        }
        if (!isset($errorList['special']) || !$errorList['special']) {
            $error .= "Password must contain at least one special character.<br>";
        }
        if(strlen($password)<8){
            $error .= "Password must contain at least 8 characters.<br>";
        }
    } 
    else {
        // Check if username or email already exists
        $query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $error = "Username or email already exists";
        } else {
            // Create new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (first_name, last_name, username, email, password, role) VALUES ('$firstname', '$lastname', '$username', '$email', '$hashedPassword', '$role')";
            
            
            if (mysqli_query($conn, $query)) {
                header('Location: index.php?page=login');
                exit();
            } else {
                $error = "Registration failed. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Attendance Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="register-form">
            <h2>Register</h2>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="firstname">First Name</label>
                    <input type="text" id="firstname" name="firstname" required>
                </div>
                <div class="form-group">
                    <label for="lastname">Last Name</label>
                    <input type="text" id="lastname" name="lastname" required>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="teacher">Teacher</option>
                        <option value="student">Student</option>
                    </select>
                </div>
                <button type="submit">Register</button>
            </form>
            <p>Already have an account? <a href="index.php?page=login">Login here</a></p>
        </div>
    </div>
</body>
</html> 