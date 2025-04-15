<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in and is a teacher
if (!isLoggedIn() || !isTeacher()) {
    redirect('login.php');
}

// Include header
include 'includes/header.php';

// Get teacher's classes
$teacherId = $_SESSION['user_id'];
$db = new Database();
$query = "SELECT * FROM classes WHERE teacher_id = $teacherId";
$result = $db->query($query);
$classes = [];
while ($row = $result->fetch_assoc()) {
    $classes[] = $row;
}
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Teacher Dashboard</h1>
            <p>Welcome, <?php echo $_SESSION['full_name']; ?>!</p>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">My Classes</h5>
                    <a href="add_class.php" class="btn btn-primary btn-sm">Add New Class</a>
                </div>
                <div class="card-body">
                    <?php if (count($classes) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Class Code</th>
                                        <th>Class Name</th>
                                        <th>Semester</th>
                                        <th>Students</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($classes as $class): ?>
                                        <?php
                                        // Get student count for this class
                                        $classId = $class['class_id'];
                                        $query = "SELECT COUNT(*) as student_count FROM enrollments WHERE class_id = $classId";
                                        $countResult = $db->query($query);
                                        $studentCount = $countResult->fetch_assoc()['student_count'];
                                        ?>
                                        <tr>
                                            <td><?php echo $class['class_code']; ?></td>
                                            <td><?php echo $class['class_name']; ?></td>
                                            <td><?php echo $class['semester']; ?></td>
                                            <td><?php echo $studentCount; ?></td>
                                            <td>
                                                <a href="class_details.php?id=<?php echo $class['class_id']; ?>" class="btn btn-info btn-sm">Details</a>
                                                <a href="generate_code.php?id=<?php echo $class['class_id']; ?>" class="btn btn-success btn-sm">Generate Code</a>
                                                <a href="take_attendance.php?id=<?php echo $class['class_id']; ?>" class="btn btn-warning btn-sm">Take Attendance</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            You haven't created any classes yet. <a href="add_class.php">Create your first class</a>.
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
                    <h5 class="mb-0">Recent Attendance</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        Attendance data will appear here once you start taking attendance for your classes.
                    </div>
                    <!-- You can add charts here later for attendance visualization -->
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
                        <a href="add_class.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-plus-circle me-2"></i> Add New Class
                        </a>
                        <a href="reports.php" class="list-group-item list-group-item-action">
                            <i class="fas fa-file-alt me-2"></i> Generate Reports
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
