<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in and is a teacher
if (!isLoggedIn() || !isTeacher()) {
    redirect('login.php');
}

// Check if enrollment ID is provided
if (!isset($_GET['enrollment']) || empty($_GET['enrollment'])) {
    redirect('admin.php');
}

$enrollmentId = (int)$_GET['enrollment'];
$teacherId = $_SESSION['user_id'];
$db = new Database();

// Get enrollment and class details
$query = "SELECT e.*, c.class_id, c.class_name, c.class_code, c.semester, u.full_name as student_name, u.email as student_email
          FROM enrollments e 
          JOIN classes c ON e.class_id = c.class_id 
          JOIN users u ON e.student_id = u.user_id
          WHERE e.enrollment_id = $enrollmentId AND c.teacher_id = $teacherId";
$result = $db->query($query);

if ($result->num_rows === 0) {
    // Enrollment not found or class doesn't belong to this teacher
    redirect('admin.php');
}

$enrollment = $result->fetch_assoc();
$classId = $enrollment['class_id'];

// Get date filters
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // Default to first day of current month
$endDate = $_GET['end_date'] ?? date('Y-m-d'); // Default to today

// Process form submission for updating attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_attendance'])) {
    $date = $_POST['date'];
    $status = $_POST['status'];
    
    // Check if attendance record already exists for this date
    $query = "SELECT * FROM attendance WHERE enrollment_id = $enrollmentId AND attendance_date = '$date'";
    $result = $db->query($query);
    
    if ($result->num_rows > 0) {
        // Update existing record
        $query = "UPDATE attendance SET status = '$status', marked_by = 'teacher', timestamp = NOW() 
                  WHERE enrollment_id = $enrollmentId AND attendance_date = '$date'";
    } else {
        // Insert new record
        $query = "INSERT INTO attendance (enrollment_id, attendance_date, status, marked_by) 
                  VALUES ($enrollmentId, '$date', '$status', 'teacher')";
    }
    
    $db->query($query);
    $updateSuccess = "Attendance updated successfully!";
}

// Get attendance records
$query = "SELECT * FROM attendance 
          WHERE enrollment_id = $enrollmentId 
          AND attendance_date BETWEEN '$startDate' AND '$endDate' 
          ORDER BY attendance_date DESC";
$result = $db->query($query);
$attendanceRecords = [];
while ($row = $result->fetch_assoc()) {
    $attendanceRecords[$row['attendance_date']] = $row;
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
            <h1>Student Attendance Records</h1>
            <a href="class_details.php?id=<?php echo $classId; ?>" class="btn btn-outline-secondary">Back to Class</a>
        </div>
    </div>
    
    <?php if (isset($updateSuccess)): ?>
        <div class="alert alert-success">
            <?php echo $updateSuccess; ?>
        </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Student Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Student:</strong> <?php echo $enrollment['student_name']; ?></p>
                            <p><strong>Email:</strong> <?php echo $enrollment['student_email']; ?></p>
                            <p><strong>Enrollment Date:</strong> <?php echo date('F j, Y', strtotime($enrollment['enrollment_date'])); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Class:</strong> <?php echo $enrollment['class_name']; ?> (<?php echo $enrollment['class_code']; ?>)</p>
                            <p><strong>Semester:</strong> <?php echo $enrollment['semester']; ?></p>
                            <p><strong>Attendance Rate:</strong> <?php echo $attendanceRate; ?>%</p>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <form method="GET" action="" class="row g-3">
                                <input type="hidden" name="enrollment" value="<?php echo $enrollmentId; ?>">
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
                            <div class="progress-bar <?php echo ($attendanceRate < 60) ? 'bg-danger' : (($attendanceRate < 80) ? 'bg-warning' : 'bg-success'); ?>" 
                                 role="progressbar" 
                                 style="width: <?php echo $attendanceRate; ?>%" 
                                 aria-valuenow="<?php echo $attendanceRate; ?>" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <?php echo $attendanceRate; ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Attendance Records</h5>
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
                                        <th>Actions</th>
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
                                            <td>
                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editModal" 
                                                        data-date="<?php echo $record['attendance_date']; ?>"
                                                        data-status="<?php echo $record['status']; ?>">
                                                    Edit
                                                </button>
                                            </td>
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
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Mark Attendance</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="date" class="form-label">Date</label>
                            <input type="date" class="form-control" id="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="present">Present</option>
                                <option value="late">Late</option>
                                <option value="absent">Absent</option>
                            </select>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" name="update_attendance" class="btn btn-primary">Save Attendance</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Attendance Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Edit Attendance</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="modal_date" class="form-label">Date</label>
                        <input type="date" class="form-control" id="modal_date" name="date" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label for="modal_status" class="form-label">Status</label>
                        <select class="form-select" id="modal_status" name="status" required>
                            <option value="present">Present</option>
                            <option value="late">Late</option>
                            <option value="absent">Absent</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_attendance" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set modal values when shown
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const date = button.getAttribute('data-date');
            const status = button.getAttribute('data-status');
            
            const modalDate = document.getElementById('modal_date');
            const modalStatus = document.getElementById('modal_status');
            
            modalDate.value = date;
            modalStatus.value = status;
        });
    }
});
</script>

<?php
// Include footer
include 'includes/footer.php';
?>
