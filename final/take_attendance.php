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
$today = date('Y-m-d');

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
$query = "SELECT e.enrollment_id, u.user_id, u.full_name 
          FROM enrollments e 
          JOIN users u ON e.student_id = u.user_id 
          WHERE e.class_id = $classId 
          ORDER BY u.full_name";
$result = $db->query($query);
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Check if attendance for today already exists
$attendanceExists = [];
foreach ($students as $student) {
    $enrollmentId = $student['enrollment_id'];
    $query = "SELECT * FROM attendance WHERE enrollment_id = $enrollmentId AND attendance_date = '$today'";
    $result = $db->query($query);
    if ($result->num_rows > 0) {
        $attendanceRecord = $result->fetch_assoc();
        $attendanceExists[$enrollmentId] = $attendanceRecord['status'];
    } else {
        $attendanceExists[$enrollmentId] = 'none';
    }
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST['attendance'] as $enrollmentId => $status) {
        $enrollmentId = (int)$enrollmentId;
        
        // Check if attendance record already exists for this enrollment on this date
        $query = "SELECT * FROM attendance WHERE enrollment_id = $enrollmentId AND attendance_date = '$today'";
        $result = $db->query($query);
        
        if ($result->num_rows > 0) {
            // Update existing record
            $query = "UPDATE attendance 
                      SET status = '$status', marked_by = 'teacher', timestamp = NOW() 
                      WHERE enrollment_id = $enrollmentId AND attendance_date = '$today'";
        } else {
            // Insert new record
            $query = "INSERT INTO attendance (enrollment_id, attendance_date, status, marked_by) 
                      VALUES ($enrollmentId, '$today', '$status', 'teacher')";
        }
        
        $db->query($query);
    }
    
    $success = "Attendance recorded successfully!";
    
    // Update the attendance status array
    foreach ($_POST['attendance'] as $enrollmentId => $status) {
        $attendanceExists[$enrollmentId] = $status;
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h1>Take Attendance</h1>
            <a href="class_details.php?id=<?php echo $classId; ?>" class="btn btn-outline-secondary">Back to Class</a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo $class['class_name']; ?> (<?php echo $class['class_code']; ?>) - <?php echo date('F j, Y'); ?></h5>
                </div>
                <div class="card-body">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (count($students) > 0): ?>
                        <form method="POST" action="">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Attendance Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td><?php echo $student['full_name']; ?></td>
                                                <td>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="attendance[<?php echo $student['enrollment_id']; ?>]" 
                                                               id="present-<?php echo $student['enrollment_id']; ?>" value="present"
                                                               <?php echo ($attendanceExists[$student['enrollment_id']] === 'present') ? 'checked' : ''; ?>>
                                                        <label class="form-check-label text-success" for="present-<?php echo $student['enrollment_id']; ?>">Present</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="attendance[<?php echo $student['enrollment_id']; ?>]" 
                                                               id="late-<?php echo $student['enrollment_id']; ?>" value="late"
                                                               <?php echo ($attendanceExists[$student['enrollment_id']] === 'late') ? 'checked' : ''; ?>>
                                                        <label class="form-check-label text-warning" for="late-<?php echo $student['enrollment_id']; ?>">Late</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" name="attendance[<?php echo $student['enrollment_id']; ?>]" 
                                                               id="absent-<?php echo $student['enrollment_id']; ?>" value="absent"
                                                               <?php echo ($attendanceExists[$student['enrollment_id']] === 'absent' || $attendanceExists[$student['enrollment_id']] === 'none') ? 'checked' : ''; ?>>
                                                        <label class="form-check-label text-danger" for="absent-<?php echo $student['enrollment_id']; ?>">Absent</label>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="d-grid gap-2 col-md-6 mx-auto mt-3">
                                <button type="submit" class="btn btn-primary">Save Attendance</button>
                                <a href="class_details.php?id=<?php echo $classId; ?>" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
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
