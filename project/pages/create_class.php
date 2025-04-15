<?php
// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: index.php?page=login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $className = mysqli_real_escape_string($conn, $_POST['class_name']);
    $classCode = generateClassCode();
    
    try {
        $query = "INSERT INTO classes (teacher_id, class_name, class_code) VALUES ({$_SESSION['user_id']}, '$className', '$classCode')";
        if (mysqli_query($conn, $query)) {
            header('Location: index.php?page=dashboard');
            exit();
        } else {
            $error = "Failed to create class. Please try again.";
        }
    } catch (Exception $e) {
        $error = "Failed to create class. Please try again.";
    }
}

function generateClassCode() {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Class - Attendance Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="create-class-form">
            <h2>Create New Class</h2>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="class_name">Class Name</label>
                    <input type="text" id="class_name" name="class_name" required>
                </div>
                <button type="submit">Create Class</button>
            </form>
            <p><a href="index.php?page=dashboard">Back to Dashboard</a></p>
        </div>
    </div>
</body>
</html> 