<?php
// Check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header('Location: index.php?page=login');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: index.php?page=dashboard');
    exit();
}

$classId = mysqli_real_escape_string($conn, $_GET['id']);
$teacherId = $_SESSION['user_id'];

// Get class details
$query = "SELECT * FROM classes WHERE id = $classId AND teacher_id = $teacherId";
$result = mysqli_query($conn, $query);
$class = mysqli_fetch_assoc($result);

if (!$class) {
    header('Location: index.php?page=dashboard');
    exit();
}

// Generate new attendance code
if (isset($_POST['generate_code'])) {
    $today = date('Y-m-d');
    $code = generateAttendanceCode();
    
    try {
        // Check if code already exists for today
        $query = "SELECT id FROM attendance_codes WHERE class_id = $classId AND date = '$today'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) === 0) {
            $query = "INSERT INTO attendance_codes (class_id, code, date) VALUES ($classId, '$code', '$today')";
            if (mysqli_query($conn, $query)) {
                $success = "New attendance code generated: " . $code;
            } else {
                $error = "Failed to generate attendance code. Please try again.";
            }
        } else {
            $error = "Attendance code already generated for today.";
        }
    } catch (Exception $e) {
        $error = "Failed to generate attendance code. Please try again.";
    }
}

// Get total number of attendance codes generated for this class
$query = "SELECT COUNT(DISTINCT date) as total_days FROM attendance_codes WHERE class_id = $classId";
$result = mysqli_query($conn, $query);
$totalDays = mysqli_fetch_assoc($result)['total_days'];

// Get enrolled students with their attendance records
$query = "SELECT u.id, u.username, 
          GROUP_CONCAT(DISTINCT ac.date ORDER BY ac.date DESC) as attendance_dates,
          COUNT(DISTINCT ac.date) as present_days
          FROM users u
          JOIN class_enrollments ce ON u.id = ce.student_id
          LEFT JOIN attendance_records ar ON u.id = ar.student_id AND ar.class_id = $classId
          LEFT JOIN attendance_codes ac ON ar.attendance_code_id = ac.id
          WHERE ce.class_id = $classId
          GROUP BY u.id, u.username
          ORDER BY u.username";
$result = mysqli_query($conn, $query);
$students = mysqli_fetch_all($result, MYSQLI_ASSOC);

// Prepare data for the chart
$chartData = [];
foreach ($students as $student) {
    $percentage = $totalDays > 0 ? ($student['present_days'] / $totalDays) * 100 : 0;
    $chartData[] = [
        'student' => $student['username'],
        'percentage' => $percentage
    ];
}

function generateAttendanceCode() {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

// Check if we should show percentages
$showPercentages = isset($_GET['view']) && $_GET['view'] === 'percentage';
// Check if we should show chart
$showChart = isset($_GET['view']) && $_GET['view'] === 'chart';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Class Details - Attendance Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <div class="class-details">
            <h2><?php echo htmlspecialchars($class['class_name']); ?></h2>
            <div class="class-code">Class Code: <?php echo htmlspecialchars($class['class_code']); ?></div>
            
            <?php if (isset($error)): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <div class="attendance-section">
                <h3>Generate Attendance Code</h3>
                <form method="POST" action="">
                    <button type="submit" name="generate_code">Generate Today's Code</button>
                </form>
            </div>
            
            <div class="view-toggle">
                <a href="index.php?page=class_details&id=<?php echo $classId; ?>&view=details" 
                   class="toggle-button <?php echo !$showPercentages && !$showChart ? 'active' : ''; ?>">
                    Show Detailed View
                </a>
                <a href="index.php?page=class_details&id=<?php echo $classId; ?>&view=percentage" 
                   class="toggle-button <?php echo $showPercentages ? 'active' : ''; ?>">
                    Show Percentage View
                </a>
                <a href="index.php?page=class_details&id=<?php echo $classId; ?>&view=chart" 
                   class="toggle-button <?php echo $showChart ? 'active' : ''; ?>">
                    Show Chart View
                </a>
            </div>
            
            <?php if ($showChart): ?>
                <div class="chart-container">
                    <canvas id="attendanceChart" data-chart='<?php echo json_encode($chartData); ?>'></canvas>
                </div>
                <script src="assets/js/attendance-chart.js"></script>
            <?php else: ?>
                <div class="students-section">
                    <h3>Enrolled Students</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Student Name</th>
                                <th>
                                    <?php if ($showPercentages): ?>
                                        Attendance Percentage
                                    <?php else: ?>
                                        Attendance Dates
                                    <?php endif; ?>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($student['username']); ?></td>
                                    <td>
                                        <?php if ($showPercentages): ?>
                                            <?php
                                            if ($totalDays > 0) {
                                                $percentage = ($student['present_days'] / $totalDays) * 100;
                                                echo number_format($percentage, 1) . '%';
                                            } else {
                                                echo 'No attendance recorded';
                                            }
                                            ?>
                                        <?php else: ?>
                                            <?php 
                                            if (!empty($student['attendance_dates'])) {
                                                $dates = explode(',', $student['attendance_dates']);
                                                foreach ($dates as $date) {
                                                    echo date('F j, Y', strtotime($date)) . '<br>';
                                                }
                                            } else {
                                                echo 'No attendance recorded';
                                            }
                                            ?>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
            
            <p><a href="index.php?page=dashboard">Back to Dashboard</a></p>
        </div>
    </div>
</body>
</html> 