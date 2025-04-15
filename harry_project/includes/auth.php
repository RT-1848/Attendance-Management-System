<?php
require_once 'db.php';
require_once 'functions.php';
global $db;

/**
 * Register a new user
 * @param string $username - Username
 * @param string $password - Password (will be hashed)
 * @param string $email - Email
 * @param string $fullName - Full name
 * @param string $role - Role (teacher or student)
 * @return array - Result array with status and message
 */
function registerUser($username, $password, $email, $fullName, $role) {
    global $db;
    
    // Sanitize inputs
    $username = sanitize($username);
    $email = sanitize($email);
    $fullName = sanitize($fullName);
    $role = sanitize($role);
    
    // Check if username already exists
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?", "s", [$username]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['status' => false, 'message' => 'Username already exists.'];
    }
    
    // Check if email already exists
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?", "s", [$email]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return ['status' => false, 'message' => 'Email already exists.'];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert new user
    $stmt = $db->prepare(
        "INSERT INTO users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, ?)",
        "sssss",
        [$username, $hashedPassword, $email, $fullName, $role]
    );
    
    if ($stmt->execute()) {
        return ['status' => true, 'message' => 'Registration successful.', 'user_id' => $db->getLastInsertId()];
    } else {
        return ['status' => false, 'message' => 'Registration failed.'];
    }
}

/**
 * Login a user
 * @param string $username - Username
 * @param string $password - Password
 * @return array - Result array with status and message
 */
function loginUser($username, $password) {
    global $db;
    
    // Sanitize inputs
    $username = sanitize($username);
    
    // Get user with matching username
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ?", "s", [$username]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['status' => false, 'message' => 'Invalid username or password.'];
    }
    
    $user = $result->fetch_assoc();
    
    // Verify password
    if (password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        return [
            'status' => true, 
            'message' => 'Login successful.',
            'role' => $user['role']
        ];
    } else {
        return ['status' => false, 'message' => 'Invalid username or password.'];
    }
}

/**
 * Logout user
 * @return void
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = array();
    
    // Destroy the session
    session_destroy();
}

/**
 * Change user password
 * @param int $userId - User ID
 * @param string $currentPassword - Current password
 * @param string $newPassword - New password
 * @return array - Result array with status and message
 */
function changePassword($userId, $currentPassword, $newPassword) {
    global $db;
    
    // Get user data
    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?", "i", [$userId]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['status' => false, 'message' => 'User not found.'];
    }
    
    $user = $result->fetch_assoc();
    
    // Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        return ['status' => false, 'message' => 'Current password is incorrect.'];
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password
    $stmt = $db->prepare(
        "UPDATE users SET password = ? WHERE user_id = ?",
        "si",
        [$hashedPassword, $userId]
    );
    
    if ($stmt->execute()) {
        return ['status' => true, 'message' => 'Password changed successfully.'];
    } else {
        return ['status' => false, 'message' => 'Failed to change password.'];
    }
}
?>
