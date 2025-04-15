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
$query = "SELECT e.enrollment_id, c.class_name, c.class_code 
          FROM enrollments e 
          JOIN classes c ON e.class_id = c.class_id 
          WHERE e.student_id = $studentId AND e.class_id = $classId";
$result = $db->query($query);

if ($result->num_rows === 0) {
    // Not enrolled in this class
    redirect('student.php');
}

$enrollment = $result->fetch_assoc();
$enrollmentId = $enrollment['enrollment_id'];
$today = date('Y-m-d');

// Check if attendance for today already exists
$query = "SELECT * FROM attendance WHERE enrollment_id = $enrollmentId AND attendance_date = '$today'";
$result = $db->query($query);
$attendanceExists = ($result->num_rows > 0);

if ($attendanceExists) {
    $attendanceRecord = $result->fetch_assoc();
    $attendanceStatus = $attendanceRecord['status'];
    $markedBy = $attendanceRecord['marked_by'];
}

// Get latest attendance code for this class
$query = "SELECT * FROM attendance_codes 
          WHERE class_id = $classId 
          AND attendance_date = '$today' 
          AND expiry_time > NOW() 
          ORDER BY created_at DESC 
          LIMIT 1";
$result = $db->query($query);
$codeAvailable = ($result->num_rows > 0);

if ($codeAvailable) {
    $attendanceCode = $result->fetch_assoc();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = sanitize($_POST['attendance_code']);
    
    // Validate code
    $query = "SELECT * FROM attendance_codes 
              WHERE class_id = $classId 
              AND attendance_code = '$code' 
              AND attendance_date = '$today' 
              AND expiry_time > NOW()";
    $result = $db->query($query);
    
    if ($result->num_rows > 0) {
        // Valid code, mark attendance
        if ($attendanceExists) {
            $query = "UPDATE attendance 
                      SET status = 'present', marked_by = 'student', timestamp = NOW() 
                      WHERE enrollment_id = $enrollmentId AND attendance_date = '$today'";
        } else {
            $query = "INSERT INTO attendance (enrollment_id, attendance_date, status, marked_by) 
                      VALUES ($enrollmentId, '$today', 'present', 'student')";
        }
        
        $db->query($query);
        $success = "Your attendance has been marked successfully!";
        
        // Update variables for display
        $attendanceExists = true;
        $attendanceStatus = 'present';
        $markedBy = 'student';
    } else {
        $error = "Invalid attendance code. Please try again.";
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h1>Mark Attendance</h1>
            <a href="student.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo $enrollment['class_name']; ?> (<?php echo $enrollment['class_code']; ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($attendanceExists): ?>
                        <div class="alert alert-info">
                            <h5>Attendance Status: 
                                <span class="
                                    <?php 
                                    echo ($attendanceStatus === 'present') ? 'text-success' : 
                                        (($attendanceStatus === 'late') ? 'text-warning' : 'text-danger'); 
                                    ?>
                                ">
                                    <?php echo ucfirst($attendanceStatus); ?>
                                </span>
                            </h5>
                            <p>
                                You were marked <?php echo $attendanceStatus; ?> for today's class 
                                (<?php echo date('F j, Y'); ?>) by 
                                <?php echo ($markedBy === 'student') ? 'yourself' : 'your teacher'; ?>.
                            </p>
                            
                            <?php if ($attendanceStatus !== 'present' && $codeAvailable): ?>
                                <p>You can still mark yourself present by entering the attendance code below.</p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!$attendanceExists || ($attendanceStatus !== 'present' && $codeAvailable)): ?>
                        <?php if ($codeAvailable): ?>
                            <form method="POST" action="">
                                <div class="mb-3">
                                    <label for="attendance_code" class="form-label">Enter Attendance Code</label>
                                    <input type="text" class="form-control" id="attendance_code" name="attendance_code" required>
                                    <small class="form-text text-muted">Enter the code provided by your teacher to mark your attendance.</small>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">Submit Code</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                <h5>No Active Attendance Code</h5>
                                <p>There is no active attendance code for today's class. Please wait for your teacher to generate a code, or contact them if you believe this is an error.</p>
                            </div>
                        <?php endif; ?>
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
