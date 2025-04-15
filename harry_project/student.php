<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    redirect('login.php');
}

// Include header
include 'includes/header.php';

// Get student's enrollments
$studentId = $_SESSION['user_id'];
$db = new Database();
$query = "SELECT e.enrollment_id, c.class_id, c.class_code, c.class_name, c.semester, u.full_name as teacher_name 
          FROM enrollments e 
          JOIN classes c ON e.class_id = c.class_id 
          JOIN users u ON c.teacher_id = u.user_id 
          WHERE e.student_id = $studentId";
$result = $db->query($query);
$enrollments = [];
while ($row = $result->fetch_assoc()) {
    $enrollments[] = $row;
}
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Student Dashboard</h1>
            <p>Welcome, <?php echo $_SESSION['full_name']; ?>!</p>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Classes</h5>
                    <a href="browse_classes.php" class="btn btn-primary btn-sm">Browse Classes</a>
                </div>
                <div class="card-body">
                    <?php if (count($enrollments) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Class Code</th>
                                        <th>Class Name</th>
                                        <th>Semester</th>
                                        <th>Teacher</th>
                                        <th>Attendance</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enrollments as $enrollment): ?>
                                        <?php
                                        // Get attendance percentage for this enrollment
                                        $enrollmentId = $enrollment['enrollment_id'];
                                        $query = "SELECT 
                                                    (SELECT COUNT(*) FROM attendance WHERE enrollment_id = $enrollmentId AND status = 'present') as present_count,
                                                    (SELECT COUNT(*) FROM attendance WHERE enrollment_id = $enrollmentId) as total_count";
                                        $attendanceResult = $db->query($query);
                                        $attendanceData = $attendanceResult->fetch_assoc();
                                        $attendancePercentage = ($attendanceData['total_count'] > 0) 
                                            ? round(($attendanceData['present_count'] / $attendanceData['total_count']) * 100) 
                                            : 0;
                                        ?>
                                        <tr>
                                            <td><?php echo $enrollment['class_code']; ?></td>
                                            <td><?php echo $enrollment['class_name']; ?></td>
                                            <td><?php echo $enrollment['semester']; ?></td>
                                            <td><?php echo $enrollment['teacher_name']; ?></td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $attendancePercentage; ?>%"
                                                        aria-valuenow="<?php echo $attendancePercentage; ?>" aria-valuemin="0" aria-valuemax="100">
                                                        <?php echo $attendancePercentage; ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="mark_attendance.php?id=<?php echo $enrollment['class_id']; ?>" class="btn btn-success btn-sm">Mark Attendance</a>
                                                <a href="view_attendance.php?id=<?php echo $enrollment['class_id']; ?>" class="btn btn-info btn-sm">View Records</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            You haven't enrolled in any classes yet. <a href="browse_classes.php">Browse available classes</a>.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Attendance Summary</h5>
                </div>
                <div class="card-body">
                    <?php if (count($enrollments) > 0): ?>
                        <!-- You can add charts here later for attendance visualization -->
                        <div class="alert alert-info">
                            Attendance statistics will be shown here as you mark your attendance.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            Enroll in classes to see your attendance summary.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <a href="browse_classes.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-search me-2"></i> Browse Classes
                        </a>
                        <a href="enter_code.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-qrcode me-2"></i> Enter Attendance Code
                        </a>
                        <a href="profile.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-user-edit me-2"></i> Edit Profile
                        </a>
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
