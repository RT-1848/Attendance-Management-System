<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Redirect logged in users
if (isLoggedIn()) {
    if (isTeacher()) {
        redirect('admin.php');
    } else if (isStudent()) {
        redirect('student.php');
    }
}

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $email = $_POST['email'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $role = $_POST['role'] ?? '';
    
    // Validate form data
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters long.';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    
    if (empty($full_name)) {
        $errors[] = 'Full name is required.';
    }
    
    if (empty($role) || !in_array($role, ['teacher', 'student'])) {
        $errors[] = 'Invalid role selected.';
    }
    
    // If no errors, register user
    if (empty($errors)) {
        $result = registerUser($username, $password, $email, $full_name, $role);
        
        if ($result['status']) {
            // Set success message
            $_SESSION['alert'] = [
                'type' => 'success',
                'message' => 'Registration successful! You can now log in.'
            ];
            
            // Redirect to login page
            redirect('login.php');
        } else {
            // Set error message
            $errors[] = $result['message'];
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div class="auth-form">
    <div class="card">
        <div class="card-header text-center">
            <h2>Create an Account</h2>
        </div>
        <div class="card-body">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" class="form-control" id="username" name="username" value="<?php echo $_POST['username'] ?? ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo $_POST['email'] ?? ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo $_POST['full_name'] ?? ''; ?>" required>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="form-text">Password must be at least 6 characters long.</div>
                </div>
                
                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">I am a:</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="role_student" value="student" <?php echo (isset($_POST['role']) && $_POST['role'] === 'student') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="role_student">Student</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="role" id="role_teacher" value="teacher" <?php echo (isset($_POST['role']) && $_POST['role'] === 'teacher') ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="role_teacher">Teacher</label>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Register</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center">
            <p class="mb-0">Already have an account? <a href="login.php">Login</a></p>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
