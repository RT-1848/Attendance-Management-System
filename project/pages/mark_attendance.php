<?php
//page where the student can mark their attendence
// check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    //if not redirect to the login
    header('Location: index.php?page=login');
    exit();
}

if (!isset($_GET['class_id'])) {
    //if the class id is not set redirect to the dashboard page might not exist
    header('Location: index.php?page=dashboard');
    exit();
}

//getting class id and student id
$classId = htmlspecialchars($_GET['class_id']);
$studentId = $_SESSION['user_id'];

// checking if student is enrolled in the class by retreiving the class row from classes and retreiving a row from enrollments where the class and student id match
$query = "SELECT c.* FROM classes c
          JOIN class_enrollments ce ON c.id = ce.class_id
          WHERE c.id = $classId AND ce.student_id = $studentId";
$result = mysqli_query($conn, $query);
$class = mysqli_fetch_assoc($result);

//if the student isn't enrolled in the class redirect to the dashboard
if (!$class) {
    header('Location: index.php?page=dashboard');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //getting the attendence code from the form
    $attendanceCode = htmlspecialchars($_POST['attendance_code']);
    //getting toady's date from system
    $today = date('Y-m-d');
    
    try {
        // check if code is valid for today by seeuing if the code is found in a row with the class id and today's date
        $query = "SELECT id FROM attendance_codes 
                 WHERE class_id = $classId AND code = '$attendanceCode' AND date = '$today'";
        $result = mysqli_query($conn, $query);
        
        // if valid
        if (mysqli_num_rows($result) > 0) {
            $attendanceCodeRecord = mysqli_fetch_assoc($result);
            $attendanceCodeId = $attendanceCodeRecord['id'];
            
            // check if already marked attendance by checking attendence records table to see if there is a row with that code for that class
            $query = "SELECT id FROM attendance_records 
                     WHERE student_id = $studentId AND class_id = $classId AND attendance_code_id = $attendanceCodeId";
            $result = mysqli_query($conn, $query);
            
            //if not
            if (mysqli_num_rows($result) === 0) {
                // mark attendance by inserting a new attendence record
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
                <!-- Form to get the attendence code -->
                <div class="form-group">
                    <label for="attendance_code">Attendance Code</label>
                    <input type="text" id="attendance_code" name="attendance_code" required>
                </div>
                <button type="submit">Mark Attendance</button>
            </form>
            <!-- Link to redirect to the dashboard -->
            <p><a href="index.php?page=dashboard">Back to Dashboard</a></p>
        </div>
    </div>
</body>
<footer>
    <p>&copy MTSU CSCI 4410 | All Rights Reserved</p>
    <strong>Youssef Botros | Harry He | Khalid Khalel | Henry Ngo | Ryan Thieu | Marcos Wofford</strong>
</footer>
</html> 