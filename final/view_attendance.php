<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    redirect('login.php');
}

// Check if class ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    redirect('student.php');
}

$classId = (int)$_GET['id'];
$studentId = $_SESSION['user_id'];
$db = new Database();

// Check if student is enrolled in the class
$query = "SELECT e.enrollment_id, c.class_name, c.class_code, c.semester, u.full_name as teacher_name
          FROM enrollments e 
          JOIN classes c ON e.class_id = c.class_id 
          JOIN users u ON c.teacher_id = u.user_id
          WHERE e.student_id = $studentId AND e.class_id = $classId";
$result = $db->query($query);

if ($result->num_rows === 0) {
    // Not enrolled in this class
    redirect('student.php');
}

$enrollment = $result->fetch_assoc();
$enrollmentId = $enrollment['enrollment_id'];

// Get date filters
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // Default to first day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Default to today

// Get attendance records
$query = "SELECT * FROM attendance 
          WHERE enrollment_id = $enrollmentId 
          AND attendance_date BETWEEN '$startDate' AND '$endDate' 
          ORDER BY attendance_date DESC";
$result = $db->query($query);
$attendanceRecords = [];
while ($row = $result->fetch_assoc()) {
    $attendanceRecords[] = $row;
}

// Calculate statistics
$totalDays = count($attendanceRecords);
$presentDays = 0;
$lateDays = 0;
$absentDays = 0;

foreach ($attendanceRecords as $record) {
    if ($record['status'] === 'present') {
        $presentDays++;
    } elseif ($record['status'] === 'late') {
        $lateDays++;
    } elseif ($record['status'] === 'absent') {
        $absentDays++;
    }
}

$attendanceRate = ($totalDays > 0) ? round((($presentDays + $lateDays) / $totalDays) * 100) : 0;

// Include header
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h1>Attendance Records</h1>
            <a href="student.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo $enrollment['class_name']; ?> (<?php echo $enrollment['class_code']; ?>)</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Teacher:</strong> <?php echo $enrollment['teacher_name']; ?></p>
                            <p><strong>Semester:</strong> <?php echo $enrollment['semester']; ?></p>
                        </div>
                        <div class="col-md-6">
                            <form method="GET" action="" class="row g-3">
                                <input type="hidden" name="id" value="<?php echo $classId; ?>">
                                <div class="col-md-5">
                                    <label for="start_date" class="form-label">Start Date</label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                                </div>
                                <div class="col-md-5">
                                    <label for="end_date" class="form-label">End Date</label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Attendance Summary</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-primary"><?php echo $totalDays; ?></h3>
                                    <p class="mb-0">Total Days</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-success"><?php echo $presentDays; ?></h3>
                                    <p class="mb-0">Present</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-warning"><?php echo $lateDays; ?></h3>
                                    <p class="mb-0">Late</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h3 class="text-danger"><?php echo $absentDays; ?></h3>
                                    <p class="mb-0">Absent</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5>Attendance Rate: <?php echo $attendanceRate; ?>%</h5>
                        <div class="progress">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $attendanceRate; ?>%" 
                                 aria-valuenow="<?php echo $attendanceRate; ?>" aria-valuemin="0" aria-valuemax="100">
                                <?php echo $attendanceRate; ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Attendance Details</h5>
                </div>
                <div class="card-body">
                    <?php if (count($attendanceRecords) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Marked By</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attendanceRecords as $record): ?>
                                        <tr>
                                            <td><?php echo date('F j, Y (l)', strtotime($record['attendance_date'])); ?></td>
                                            <td>
                                                <span class="badge <?php 
                                                    echo ($record['status'] === 'present') ? 'bg-success' : 
                                                        (($record['status'] === 'late') ? 'bg-warning' : 'bg-danger'); 
                                                ?>">
                                                    <?php echo ucfirst($record['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo ucfirst($record['marked_by']); ?></td>
                                            <td><?php echo date('h:i A', strtotime($record['timestamp'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No attendance records found for the selected date range.
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
