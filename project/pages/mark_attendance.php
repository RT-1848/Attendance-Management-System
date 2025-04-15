<?php
// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php?page=login');
    exit();
}

if (!isset($_GET['class_id'])) {
    header('Location: index.php?page=dashboard');
    exit();
}

$classId = mysqli_real_escape_string($conn, $_GET['class_id']);
$studentId = $_SESSION['user_id'];

// Check if student is enrolled in the class
$query = "SELECT c.* FROM classes c
          JOIN class_enrollments ce ON c.id = ce.class_id
          WHERE c.id = $classId AND ce.student_id = $studentId";
$result = mysqli_query($conn, $query);
$class = mysqli_fetch_assoc($result);

if (!$class) {
    header('Location: index.php?page=dashboard');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $attendanceCode = mysqli_real_escape_string($conn, $_POST['attendance_code']);
    $today = date('Y-m-d');
    
    try {
        // Check if code is valid for today
        $query = "SELECT id FROM attendance_codes 
                 WHERE class_id = $classId AND code = '$attendanceCode' AND date = '$today'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $attendanceCodeRecord = mysqli_fetch_assoc($result);
            $attendanceCodeId = $attendanceCodeRecord['id'];
            
            // Check if already marked attendance
            $query = "SELECT id FROM attendance_records 
                     WHERE student_id = $studentId AND class_id = $classId AND attendance_code_id = $attendanceCodeId";
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) === 0) {
                // Mark attendance
                $query = "INSERT INTO attendance_records 
                         (student_id, class_id, attendance_code_id) 
                         VALUES ($studentId, $classId, $attendanceCodeId)";
                if (mysqli_query($conn, $query)) {
                    $success = "Attendance marked successfully!";
                } else {
                    $error = "Failed to mark attendance. Please try again.";
                }
            } else {
                $error = "You have already marked attendance for today.";
            }
        } else {
            $error = "Invalid attendance code.";
        }
    } catch (Exception $e) {
        $error = "Failed to mark attendance. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - Attendance Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="mark-attendance-form">
            <h2>Mark Attendance for <?php echo htmlspecialchars($class['class_name']); ?></h2>
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="POST" action="">
                <div class="form-group">
                    <label for="attendance_code">Attendance Code</label>
                    <input type="text" id="attendance_code" name="attendance_code" required>
                </div>
                <button type="submit">Mark Attendance</button>
            </form>
            <p><a href="index.php?page=dashboard">Back to Dashboard</a></p>
        </div>
    </div>
</body>
</html> 