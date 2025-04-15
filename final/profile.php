<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$db = new Database();

// Get user details
$query = "SELECT * FROM users WHERE user_id = $userId";
$result = $db->query($query);
$user = $result->fetch_assoc();

// Process profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $fullName = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    
    // Validate input
    $errors = [];
    
    if (empty($fullName)) {
        $errors[] = "Full name is required.";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format.";
    }
    
    // Check if email already exists (if changed)
    if ($email !== $user['email']) {
        $query = "SELECT * FROM users WHERE email = '$email' AND user_id != $userId";
        $result = $db->query($query);
        
        if ($result->num_rows > 0) {
            $errors[] = "Email already exists. Please choose another one.";
        }
    }
    
    // If no errors, update profile
    if (empty($errors)) {
        $query = "UPDATE users SET full_name = '$fullName', email = '$email' WHERE user_id = $userId";
        $result = $db->query($query);
        
        if ($result) {
            // Update session variables
            $_SESSION['full_name'] = $fullName;
            $_SESSION['email'] = $email;
            
            $profileSuccess = "Profile updated successfully!";
        } else {
            $errors[] = "Failed to update profile. Please try again.";
        }
    }
}

// Process password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validate input
    $passwordErrors = [];
    
    if (empty($currentPassword)) {
        $passwordErrors[] = "Current password is required.";
    }
    
    if (empty($newPassword)) {
        $passwordErrors[] = "New password is required.";
    } elseif (strlen($newPassword) < 8) {
        $passwordErrors[] = "New password must be at least 8 characters long.";
    }
    
    if ($newPassword !== $confirmPassword) {
        $passwordErrors[] = "New passwords do not match.";
    }
    
    // If no errors, change password
    if (empty($passwordErrors)) {
        $result = changePassword($userId, $currentPassword, $newPassword);
        
        if ($result['status']) {
            $passwordSuccess = $result['message'];
        } else {
            $passwordErrors[] = $result['message'];
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>My Profile</h1>
            <p>Manage your account settings</p>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Profile Information</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($profileSuccess)): ?>
                        <div class="alert alert-success">
                            <?php echo $profileSuccess; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" value="<?php echo $user['username']; ?>" disabled>
                            <small class="form-text text-muted">Username cannot be changed.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo $user['full_name']; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo $user['email']; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="role" class="form-label">Role</label>
                            <input type="text" class="form-control" id="role" value="<?php echo ucfirst($user['role']); ?>" disabled>
                            <small class="form-text text-muted">Role cannot be changed.</small>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="update_profile" class="btn btn-primary">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Change Password</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($passwordErrors) && !empty($passwordErrors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($passwordErrors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($passwordSuccess)): ?>
                        <div class="alert alert-success">
                            <?php echo $passwordSuccess; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                            <small class="form-text text-muted">Password must be at least 8 characters long.</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Account Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Account Created:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                    <p><strong>Last Updated:</strong> <?php echo date('F j, Y', strtotime($user['updated_at'])); ?></p>
                    
                    <div class="d-grid gap-2">
                        <?php if (isTeacher()): ?>
                            <a href="admin.php" class="btn btn-outline-primary">Back to Dashboard</a>
                        <?php else: ?>
                            <a href="student.php" class="btn btn-outline-primary">Back to Dashboard</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
