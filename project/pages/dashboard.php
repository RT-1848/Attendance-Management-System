<?php
// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php?page=login');
    exit();
}

$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Get classes based on user role
if ($userRole === 'teacher') {
    $query = "SELECT * FROM classes WHERE teacher_id = $userId";
    $result = mysqli_query($conn, $query);
    $classes = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    $query = "SELECT c.* FROM classes c
              JOIN class_enrollments ce ON c.id = ce.class_id
              WHERE ce.student_id = $userId";
    $result = mysqli_query($conn, $query);
    $classes = mysqli_fetch_all($result, MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Attendance Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1 class="dashboard-title">My Courses</h1>
        </div>

        <!-- Navigation menu -->
        <div class="nav-menu">
            <?php if ($userRole === 'teacher'): ?>
                <a href="index.php?page=create_class" class="nav-button">Create Class</a>
            <?php else: ?>
                <a href="index.php?page=join_class" class="nav-button">Join Class</a>
            <?php endif; ?>
            <a href="index.php?page=logout" class="nav-button">Logout</a>
        </div>

        <!-- Courses grid -->
        <div class="courses-grid">
            <?php foreach ($classes as $class): ?>
                <div class="course-card">
                    <div class="course-banner"></div>
                    <div class="course-content">
                        <h3 class="course-name"><?php echo htmlspecialchars($class['class_name']); ?></h3>
                        <div class="course-code">Class Code: <?php echo htmlspecialchars($class['class_code']); ?></div>
                        <div class="course-meta">
                            <?php if ($userRole === 'teacher'): ?>
                                <a href="index.php?page=class_details&id=<?php echo $class['id']; ?>" class="course-link">View Details</a>
                            <?php else: ?>
                                <a href="index.php?page=mark_attendance&class_id=<?php echo $class['id']; ?>" class="course-link">Mark Attendance</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html> 