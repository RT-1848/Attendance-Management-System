<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in and is a teacher
if (!isLoggedIn() || !isTeacher()) {
    redirect('login.php');
}

// Check if class ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('admin.php');
}

$classId = (int)$_GET['id'];
$teacherId = $_SESSION['user_id'];

// Get class details
$db = new Database();
$query = "SELECT * FROM classes WHERE class_id = $classId AND teacher_id = $teacherId";
$result = $db->query($query);

// If class not found or doesn't belong to this teacher, redirect to dashboard
if ($result->num_rows === 0) {
    redirect('admin.php');
}

$class = $result->fetch_assoc();

// Get enrolled students
$query = "SELECT e.enrollment_id, u.user_id, u.username, u.full_name, u.email, e.enrollment_date 
          FROM enrollments e 
          JOIN users u ON e.student_id = u.user_id 
          WHERE e.class_id = $classId 
          ORDER BY u.full_name";
$result = $db->query($query);
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Get attendance statistics
$query = "SELECT 
            a.status, 
            COUNT(*) as count 
          FROM attendance a 
          JOIN enrollments e ON a.enrollment_id = e.enrollment_id 
          WHERE e.class_id = $classId 
          GROUP BY a.status";
$result = $db->query($query);
$stats = [
    'present' => 0,
    'absent' => 0,
    'late' => 0
];
while ($row = $result->fetch_assoc()) {
    $stats[$row['status']] = $row['count'];
}
$totalAttendance = $stats['present'] + $stats['absent'] + $stats['late'];
$presentPercentage = ($totalAttendance > 0) ? round(($stats['present'] / $totalAttendance) * 100) : 0;

// Include header
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h1><?php echo $class['class_name']; ?> <small class="text-muted">(<?php echo $class['class_code']; ?>)</small></h1>
            <a href="admin.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Class Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Class Code:</strong> <?php echo $class['class_code']; ?></p>
                    <p><strong>Semester:</strong> <?php echo $class['semester']; ?></p>
                    <p><strong>Created:</strong> <?php echo date('F j, Y', strtotime($class['created_at'])); ?></p>
                    <p><strong>Students Enrolled:</strong> <?php echo count($students); ?></p>
                    
                    <div class="d-grid gap-2 mt-3">
                        <a href="edit_class.php?id=<?php echo $classId; ?>" class="btn btn-primary">Edit Class</a>
                        <a href="generate_code.php?id=<?php echo $classId; ?>" class="btn btn-success">Generate Attendance Code</a>
                        <a href="take_attendance.php?id=<?php echo $classId; ?>" class="btn btn-warning">Take Attendance</a>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Attendance Statistics</h5>
                </div>
                <div class="card-body">
                    <?php if ($totalAttendance > 0): ?>
                        <div class="row mb-3">
                            <div class="col">
                                <h3 class="text-center"><?php echo $presentPercentage; ?>%</h3>
                                <p class="text-center text-muted">Overall Attendance Rate</p>
                            </div>
                        </div>
                        
                        <div class="row text-center">
                            <div class="col">
                                <h4 class="text-success"><?php echo $stats['present']; ?></h4>
                                <p>Present</p>
                            </div>
                            <div class="col">
                                <h4 class="text-warning"><?php echo $stats['late']; ?></h4>
                                <p>Late</p>
                            </div>
                            <div class="col">
                                <h4 class="text-danger"><?php echo $stats['absent']; ?></h4>
                                <p>Absent</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No attendance records available yet. Start taking attendance to see statistics here.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Enrolled Students (<?php echo count($students); ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (count($students) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Enrollment Date</th>
                                        <th>Attendance</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($students as $student): ?>
                                        <?php
                                        // Get student's attendance
                                        $enrollmentId = $student['enrollment_id'];
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
                                            <td><?php echo $student['full_name']; ?></td>
                                            <td><?php echo $student['username']; ?></td>
                                            <td><?php echo $student['email']; ?></td>
                                            <td><?php echo date('M j, Y', strtotime($student['enrollment_date'])); ?></td>
                                            <td>
                                                <div class="progress">
                                                    <div class="progress-bar <?php echo ($attendancePercentage < 60) ? 'bg-danger' : (($attendancePercentage < 80) ? 'bg-warning' : 'bg-success'); ?>" 
                                                         role="progressbar" 
                                                         style="width: <?php echo $attendancePercentage; ?>%"
                                                         aria-valuenow="<?php echo $attendancePercentage; ?>" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="100">
                                                        <?php echo $attendancePercentage; ?>%
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="student_attendance.php?enrollment=<?php echo $student['enrollment_id']; ?>" class="btn btn-info btn-sm">Attendance Records</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No students have enrolled in this class yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
