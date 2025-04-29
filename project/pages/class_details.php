<?php
//page where the teacher can see the details for the class
// check if user is logged in and is a teacher
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    //if not redirect back to login
    header('Location: index.php?page=login');
    exit();
}

if (!isset($_GET['id'])) {
    //if the class id can't be retreived go back to dashboard
    header('Location: index.php?page=dashboard');
    exit();
}

//retreiving class id
$classId = htmlspecialchars($_GET['id']);
//getting the user id from the session
$teacherId = $_SESSION['user_id'];

// get all the columns from the classes table for that class and that teacher
$query = "SELECT * FROM classes WHERE id = $classId AND teacher_id = $teacherId";
$result = mysqli_query($conn, $query);
$class = mysqli_fetch_assoc($result);

//if info can't be found redirect to the dashboard class may no longer exist
if (!$class) {
    header('Location: index.php?page=dashboard');
    exit();
}

// generate new attendance code
if (isset($_POST['generate_code'])) {
    //getting today's date from system
    $today = date('Y-m-d');
    //generating the code
    $code = generateAttendanceCode();
    
    try {
        // check if code already exists for today by checking if an attendence code for that class for that day
        $query = "SELECT id FROM attendance_codes WHERE class_id = $classId AND date = '$today'";
        $result = mysqli_query($conn, $query);
        
        //if no code exists for that class for that day
        if (mysqli_num_rows($result) === 0) {
            //insert the new code to the attendence codes table
            $query = "INSERT INTO attendance_codes (class_id, code, date) VALUES ($classId, '$code', '$today')";
            //if successfully inserted generate a message with the code
            if (mysqli_query($conn, $query)) {
                $success = "New attendance code generated: " . $code;
            } else {
                //otherwise the code couldn't be generated
                $error = "Failed to generate attendance code. Please try again.";
            }
        } else {
            //other wise the code already exists and should be displayed without generating a new code
            $error2 = 'Today\'s Code Has Already Been Generated And Is: ';
        }
    } catch (Exception $e) {
        $error = "Failed to generate attendance code. Please try again.";
    }
}

// get total number of attendance codes generated for this class to calculate the attendence percentage
$query = "SELECT COUNT(DISTINCT date) as total_days FROM attendance_codes WHERE class_id = $classId";
$result = mysqli_query($conn, $query);
$totalDays = mysqli_fetch_assoc($result)['total_days'];

// get enrolled students with their attendance records by getting their ID, name, all their attendance dates as a single string and the number of days they were present which is a count of distinct dates in the attendance records table. It filters by class ID, joins attendance info, and sorts the final list by last name.
//
$query = "SELECT u.id, u.first_name, u.last_name,
          GROUP_CONCAT(ac.date ORDER BY ac.date DESC SEPARATOR ', ') as attendance_dates,
          COUNT(DISTINCT ac.date) as present_days
          FROM users u
          JOIN class_enrollments ce ON u.id = ce.student_id
          LEFT JOIN attendance_records ar ON u.id = ar.student_id AND ar.class_id = $classId
          LEFT JOIN attendance_codes ac ON ar.attendance_code_id = ac.id
          WHERE ce.class_id = $classId
          GROUP BY u.id, u.first_name, u.last_name
          ORDER BY u.last_name";
$result = mysqli_query($conn, $query);
$students = mysqli_fetch_all($result, MYSQLI_ASSOC);

// prepare the data for the chart to be used in the chart.js to generate the chart
$chartData = [];
foreach ($students as $student) {
    //calculating the attendence percentages
    $percentage = $totalDays > 0 ? ($student['present_days'] / $totalDays) * 100 : 0;
    //adding info to chart data as an assoc array
    $chartData[] = [
        'student' => $student['last_name'] .", ".$student['first_name'] ,
        'percentage' => $percentage
    ];
}

//same generate code function as for the class code
function generateAttendanceCode() {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $code;
}

// check if we should show percentages based on the current view
$showPercentages = isset($_GET['view']) && $_GET['view'] === 'percentage';
// check if we should show chart based on the current view
$showChart = isset($_GET['view']) && $_GET['view'] === 'chart';
//default view is detailed view
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
            <h2>Class Details - <?php echo htmlspecialchars($class['class_name']); ?></h2>
            <div class="class-code">Class Code: <?php echo htmlspecialchars($class['class_code']); ?></div>
            
            <?php
                //if code aleady exists for today it should still be displayed if the button is pressed by retreving it from the table
                if (isset($error2)) {
                    $today = date('Y-m-d');
                    $query = "SELECT code FROM attendance_codes WHERE class_id = $classId AND date = '$today'";
                    $result = mysqli_query($conn, $query);
                    $row = mysqli_fetch_assoc($result);  // fetch as an associative array
                    $code = $row['code'];
                    echo '<div class="success">' . $error2 . $code .'</div>';
                } elseif (isset($success)) {
                    echo '<div class="success">' . $success . '</div>';
                } elseif (isset($error)) {
                    echo '<div class="error">' . $error . '</div>';
                }
            ?>
            
            <!-- Button to generate attendence -->
            <div class="attendance-section">
                <h3>Generate Attendance Code</h3>
                <form method="POST" action="">
                    <button type="submit" name="generate_code" id="generateCodeButton" >Generate Today's Code</button>
                </form>

            </div>
            
            <!-- If current view is show chart -->
            <?php if ($showChart): ?>
                <div class="chart-container">
                    <!-- send the chart data to attendence-chart.js as a json_enocode and display it in a canvas block -->
                    <canvas id="attendanceChart" data-chart='<?php echo json_encode($chartData); ?>'></canvas>
                </div>
                <script src="assets/js/attendance-chart.js"></script>
            <?php else: ?>
                <!-- other wise instead of a canvas a table is used for the student attendence info -->
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
                        <td><?php echo htmlspecialchars($student['first_name']. " " . $student['last_name']); ?></td>
                        <td>
                            <?php if ($showPercentages): ?>
                                <?php
                                //if the current view is the percentage view display the percentage of attendence calculated earlier in the php
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
                                    //other wise display each day the student attended which was retreived from the attendence records table earlier
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
                <!-- Links to change the current view so the chart, percent, or details can be shown by redirecting to the class_details page with diffrent view values -->
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

            
            <p><a href="index.php?page=dashboard" id="backToDashboardLink">Back to Dashboard</a></p>
        </div>
    </div>
</body>
<footer>
    <p>&copy MTSU CSCI 4410 | All Rights Reserved</p>
    <strong>Youssef Botros | Harry He | Khalid Khalel | Henry Ngo | Ryan Thieu | Marcos Wofford</strong>
</footer>
</html> 