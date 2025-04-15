<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    redirect('login.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = $_POST['attendance_code'];
    $studentId = $_SESSION['user_id'];
    $message = '';
    $messageType = '';
    
    // Check if code exists and is valid
    $db = new Database();
    $code = $db->escapeString($code);
    $query = "SELECT ac.*, c.class_id FROM attendance_codes ac 
              JOIN classes c ON ac.class_id = c.class_id 
              WHERE ac.attendance_code = '$code' 
              AND ac.expiry_time > NOW()";
    $result = $db->query($query);
    
    if ($result->num_rows === 1) {
        $codeData = $result->fetch_assoc();
        $classId = $codeData['class_id'];
        $attendanceDate = $codeData['attendance_date'];
        
        // Check if student is enrolled in the class
        $query = "SELECT enrollment_id FROM enrollments WHERE student_id = $studentId AND class_id = $classId";
        $enrollmentResult = $db->query($query);
        
        if ($enrollmentResult->num_rows === 1) {
            $enrollmentId = $enrollmentResult->fetch_assoc()['enrollment_id'];
            
            // Check if attendance is already marked
            $query = "SELECT * FROM attendance WHERE enrollment_id = $enrollmentId AND attendance_date = '$attendanceDate'";
            $attendanceResult = $db->query($query);
            
            if ($attendanceResult->num_rows === 0) {
                // Mark attendance
                $query = "INSERT INTO attendance (enrollment_id, attendance_date, status, marked_by) 
                          VALUES ($enrollmentId, '$attendanceDate', 'present', 'student')";
                $db->query($query);
                $message = "Your attendance has been marked successfully!";
                $messageType = "success";
            } else {
                $message = "Your attendance for this class has already been marked!";
                $messageType = "warning";
            }
        } else {
            $message = "You are not enrolled in this class!";
            $messageType = "danger";
        }
    } else {
        $message = "Invalid or expired attendance code!";
        $messageType = "danger";
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h3 class="mb-0">Enter Attendance Code</h3>
                </div>
                <div class="card-body">
                    <?php if (isset($message) && $message): ?>
                        <div class="alert alert-<?php echo $messageType; ?>" role="alert">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="form-group mb-3">
                            <label for="attendance_code">Attendance Code</label>
                            <input type="text" class="form-control" id="attendance_code" name="attendance_code" required>
                            <small class="form-text text-muted">Enter the code provided by your teacher to mark your attendance.</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">Submit</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
