<?php
//this is the page where users register or create new accounts
//post  method is used since passwords and other info is collected from users
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //storing all the information from the form to local vars
    $firstname = htmlspecialchars($_POST['firstname']);
    $lastname = htmlspecialchars($_POST['lastname']);
    $username = htmlspecialchars( $_POST['username']);
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];
    $role = htmlspecialchars( $_POST['role']);

    //conditions array for secure password
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

    //loop checking if the password is valid and meetis the conditions.
    $valid = true;
    foreach ($conditions as $key => $rule) {
        if (!preg_match($rule['pattern'], $password)) {
            $errorList[$key] = false;
            $valid = false;
        } else {
            $errorList[$key] = true;
        }
    }
    // Validate user inputs
    $error = "";
    if (empty($username) || empty($email) || empty($password) && $valid) {
        $error = "All fields are required";
    }
    else if(!$valid){
        //reporting errors if the password is not valid
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
        // Check if username or email already exists in the table
        $query = "SELECT id FROM users WHERE username = '$username' OR email = '$email'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            //if so report an error
            $error = "Username or email already exists";
        } else {
            //otherwise hash the password and insert the new user into the users table
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $query = "INSERT INTO users (first_name, last_name, username, email, password, role) VALUES ('$firstname', '$lastname', '$username', '$email', '$hashedPassword', '$role')";
            
            //error handeling if the query is successful you are redirected to the login page to login with the newly created account
            if (mysqli_query($conn, $query)) {
                header('Location: index.php?page=login');
                exit();
            } else {
                //otherwise report an error
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
            <!-- header for the register page -->
            <h2>Register</h2>
            <!-- Checking if there are any errors set and if so displaying them -->
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <!-- Form to get user input for the information -->
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
                <!-- Drop down for role selection -->
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" name="role" required>
                        <option value="teacher">Teacher</option>
                        <option value="student">Student</option>
                    </select>
                </div>
                <button type="submit">Register</button>
            </form>
            <!-- link to redirect to the login page if the user already has an account -->
            <p>Already have an account? <a href="index.php?page=login">Login here</a></p>
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