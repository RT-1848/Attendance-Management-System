<?php
// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header('Location: index.php?page=login');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: index.php?page=dashboard');
    exit();
}

$classId = mysqli_real_escape_string($conn, $_GET['id']);
$studentId = $_SESSION['user_id'];

// Check if the student is enrolled in the class
$query = "SELECT c.class_name, c.class_code FROM classes c
          JOIN class_enrollments ce ON c.id = ce.class_id
          WHERE ce.class_id = $classId AND ce.student_id = $studentId";
$result = mysqli_query($conn, $query);
$class = mysqli_fetch_assoc($result);

if (!$class) {
    header('Location: index.php?page=dashboard');
    exit();
}

// Get total attendance days for this class
$query = "SELECT COUNT(DISTINCT date) as total_days FROM attendance_codes WHERE class_id = $classId";
$result = mysqli_query($conn, $query);
$totalDays = mysqli_fetch_assoc($result)['total_days'];

// Get the student's attendance record for this class
$query = "SELECT DISTINCT ac.date 
          FROM attendance_records ar
          JOIN attendance_codes ac ON ar.attendance_code_id = ac.id
          WHERE ar.class_id = $classId AND ar.student_id = $studentId
          ORDER BY ac.date DESC";
$result = mysqli_query($conn, $query);
$attendanceDates = mysqli_fetch_all($result, MYSQLI_ASSOC);
$presentDays = count($attendanceDates);

// Calculate attendance percentage
$percentage = $totalDays > 0 ? ($presentDays / $totalDays) * 100 : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Your Attendance - <?php echo htmlspecialchars($class['class_name']); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="class-details-student">
            <h2><?php echo htmlspecialchars($class['class_name']); ?> - Attendance Overview</h2>
            <p><strong>Class Code:</strong> <?php echo htmlspecialchars($class['class_code']); ?></p>

            <div class="attendance-summary">
                <p><strong>Total Days:</strong> <?php echo $totalDays; ?></p>
                <p><strong>Days Attended:</strong> <?php echo $presentDays; ?></p>
                <p><strong>Attendance Percentage:</strong> <?php echo number_format($percentage, 1); ?>%</p>
            </div>

            <div class="attendance-dates">
                <h3>Dates You Attended</h3>
                <?php if ($presentDays > 0): ?>
                    <ul>
                        <?php foreach ($attendanceDates as $record): ?>
                            <li><?php echo date('F j, Y', strtotime($record['date'])); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>No attendance recorded yet.</p>
                <?php endif; ?>
            </div>

            <p><a href="index.php?page=dashboard" class="btn">Back to Dashboard</a></p>
        </div>
    </div>
</body>
</html>