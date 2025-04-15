<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in and is a teacher
if (!isLoggedIn() || !isTeacher()) {
    redirect('login.php');
}

$teacherId = $_SESSION['user_id'];
$db = new Database();

// Get teacher's classes
$query = "SELECT * FROM classes WHERE teacher_id = $teacherId ORDER BY class_code";
$result = $db->query($query);
$classes = [];
while ($row = $result->fetch_assoc()) {
    $classes[] = $row;
}

// Process report generation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classId = (int)$_POST['class_id'];
    $reportType = $_POST['report_type'];
    $startDate = $_POST['start_date'];
    $endDate = $_POST['end_date'];
    
    // Validate input
    $errors = [];
    
    if (empty($classId)) {
        $errors[] = "Please select a class.";
    }
    
    if (empty($reportType)) {
        $errors[] = "Please select a report type.";
    }
    
    if (empty($startDate)) {
        $errors[] = "Please select a start date.";
    }
    
    if (empty($endDate)) {
        $errors[] = "Please select an end date.";
    }
    
    if (strtotime($endDate) < strtotime($startDate)) {
        $errors[] = "End date cannot be earlier than start date.";
    }
    
    // If no errors, generate report
    if (empty($errors)) {
        // Get class details
        $query = "SELECT * FROM classes WHERE class_id = $classId AND teacher_id = $teacherId";
        $classResult = $db->query($query);
        $class = $classResult->fetch_assoc();
        
        // Get enrolled students
        $query = "SELECT e.enrollment_id, u.user_id, u.full_name 
                  FROM enrollments e 
                  JOIN users u ON e.student_id = u.user_id 
                  WHERE e.class_id = $classId 
                  ORDER BY u.full_name";
        $enrollmentsResult = $db->query($query);
        $students = [];
        while ($row = $enrollmentsResult->fetch_assoc()) {
            $students[$row['enrollment_id']] = $row;
        }
        
        // Get all attendance dates in range
        $query = "SELECT DISTINCT attendance_date 
                  FROM attendance a 
                  JOIN enrollments e ON a.enrollment_id = e.enrollment_id 
                  WHERE e.class_id = $classId 
                  AND attendance_date BETWEEN '$startDate' AND '$endDate' 
                  ORDER BY attendance_date";
        $datesResult = $db->query($query);
        $dates = [];
        while ($row = $datesResult->fetch_assoc()) {
            $dates[] = $row['attendance_date'];
        }
        
        // Get attendance records
        $attendanceData = [];
        foreach ($students as $enrollmentId => $student) {
            $query = "SELECT attendance_date, status 
                      FROM attendance 
                      WHERE enrollment_id = $enrollmentId 
                      AND attendance_date BETWEEN '$startDate' AND '$endDate'";
            $attendanceResult = $db->query($query);
            $studentAttendance = [];
            while ($row = $attendanceResult->fetch_assoc()) {
                $studentAttendance[$row['attendance_date']] = $row['status'];
            }
            $attendanceData[$enrollmentId] = $studentAttendance;
        }
        
        // Calculate statistics
        $studentStats = [];
        foreach ($students as $enrollmentId => $student) {
            $present = 0;
            $late = 0;
            $absent = 0;
            
            foreach ($dates as $date) {
                if (isset($attendanceData[$enrollmentId][$date])) {
                    $status = $attendanceData[$enrollmentId][$date];
                    if ($status === 'present') {
                        $present++;
                    } elseif ($status === 'late') {
                        $late++;
                    } elseif ($status === 'absent') {
                        $absent++;
                    }
                } else {
                    $absent++;
                }
            }
            
            $total = count($dates);
            $presentPercentage = ($total > 0) ? round(($present / $total) * 100) : 0;
            
            $studentStats[$enrollmentId] = [
                'present' => $present,
                'late' => $late,
                'absent' => $absent,
                'total' => $total,
                'percentage' => $presentPercentage
            ];
        }
        
        // Generate report
        $reportGenerated = true;
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>Attendance Reports</h1>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Generate Report</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($errors) && !empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="class_id" class="form-label">Select Class</label>
                            <select class="form-select" id="class_id" name="class_id" required>
                                <option value="">-- Select Class --</option>
                                <?php foreach ($classes as $class): ?>
                                    <option value="<?php echo $class['class_id']; ?>" <?php echo (isset($_POST['class_id']) && $_POST['class_id'] == $class['class_id']) ? 'selected' : ''; ?>>
                                        <?php echo $class['class_name']; ?> (<?php echo $class['class_code']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="report_type" class="form-label">Report Type</label>
                            <select class="form-select" id="report_type" name="report_type" required>
                                <option value="">-- Select Type --</option>
                                <option value="summary" <?php echo (isset($_POST['report_type']) && $_POST['report_type'] == 'summary') ? 'selected' : ''; ?>>Attendance Summary</option>
                                <option value="detailed" <?php echo (isset($_POST['report_type']) && $_POST['report_type'] == 'detailed') ? 'selected' : ''; ?>>Detailed Report</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="start_date" class="form-label">Start Date</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo $_POST['start_date'] ?? date('Y-m-01'); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="end_date" class="form-label">End Date</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo $_POST['end_date'] ?? date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Generate Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-8">
            <?php if (isset($reportGenerated) && isset($class)): ?>
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <?php echo $class['class_name']; ?> (<?php echo $class['class_code']; ?>) - 
                            <?php echo date('M j, Y', strtotime($startDate)); ?> to <?php echo date('M j, Y', strtotime($endDate)); ?>
                        </h5>
                        <button onclick="window.print()" class="btn btn-sm btn-secondary">Print Report</button>
                    </div>
                    <div class="card-body">
                        <?php if ($reportType === 'summary'): ?>
                            <!-- Summary Report -->
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Student Name</th>
                                            <th>Present</th>
                                            <th>Late</th>
                                            <th>Absent</th>
                                            <th>Attendance Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $enrollmentId => $student): ?>
                                            <tr>
                                                <td><?php echo $student['full_name']; ?></td>
                                                <td><?php echo $studentStats[$enrollmentId]['present']; ?></td>
                                                <td><?php echo $studentStats[$enrollmentId]['late']; ?></td>
                                                <td><?php echo $studentStats[$enrollmentId]['absent']; ?></td>
                                                <td>
                                                    <div class="progress">
                                                        <div class="progress-bar <?php echo ($studentStats[$enrollmentId]['percentage'] < 60) ? 'bg-danger' : (($studentStats[$enrollmentId]['percentage'] < 80) ? 'bg-warning' : 'bg-success'); ?>" 
                                                             role="progressbar" 
                                                             style="width: <?php echo $studentStats[$enrollmentId]['percentage']; ?>%"
                                                             aria-valuenow="<?php echo $studentStats[$enrollmentId]['percentage']; ?>" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="100">
                                                            <?php echo $studentStats[$enrollmentId]['percentage']; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <!-- Detailed Report -->
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Student Name</th>
                                            <?php foreach ($dates as $date): ?>
                                                <th><?php echo date('m/d', strtotime($date)); ?></th>
                                            <?php endforeach; ?>
                                            <th>Rate</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $enrollmentId => $student): ?>
                                            <tr>
                                                <td><?php echo $student['full_name']; ?></td>
                                                <?php foreach ($dates as $date): ?>
                                                    <td>
                                                        <?php if (isset($attendanceData[$enrollmentId][$date])): ?>
                                                            <?php 
                                                            $status = $attendanceData[$enrollmentId][$date];
                                                            $statusClass = '';
                                                            $statusIcon = '';
                                                            
                                                            if ($status === 'present') {
                                                                $statusClass = 'text-success';
                                                                $statusIcon = 'fas fa-check-circle';
                                                            } elseif ($status === 'late') {
                                                                $statusClass = 'text-warning';
                                                                $statusIcon = 'fas fa-exclamation-circle';
                                                            } elseif ($status === 'absent') {
                                                                $statusClass = 'text-danger';
                                                                $statusIcon = 'fas fa-times-circle';
                                                            }
                                                            ?>
                                                            <i class="<?php echo $statusIcon; ?> <?php echo $statusClass; ?>"></i>
                                                        <?php else: ?>
                                                            <i class="fas fa-times-circle text-danger"></i>
                                                        <?php endif; ?>
                                                    </td>
                                                <?php endforeach; ?>
                                                <td><?php echo $studentStats[$enrollmentId]['percentage']; ?>%</td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-3">
                                <p><i class="fas fa-check-circle text-success"></i> Present &nbsp;
                                <i class="fas fa-exclamation-circle text-warning"></i> Late &nbsp;
                                <i class="fas fa-times-circle text-danger"></i> Absent</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info">
                            Select a class and date range to generate an attendance report.
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
