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
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate form data
    $errors = [];
    
    if (empty($username)) {
        $errors[] = 'Username is required.';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required.';
    }
    
    // If no errors, login user
    if (empty($errors)) {
        $result = loginUser($username, $password);
        
        if ($result['status']) {
            // Set remember me cookie if selected
            if ($remember) {
                // Set cookie for 30 days
                setcookie('remember_user', $_SESSION['user_id'], time() + (86400 * 30), '/');
            }
            
            // Redirect to appropriate dashboard
            if ($result['role'] === 'teacher') {
                redirect('admin.php');
            } else {
                redirect('student.php');
            }
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
            <h2>Login</h2>
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
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
        <div class="card-footer text-center">
            <p class="mb-0">Don't have an account? <a href="register.php">Register</a></p>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
