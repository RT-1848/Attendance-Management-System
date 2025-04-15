<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in and is a student
if (!isLoggedIn() || !isStudent()) {
    redirect('login.php');
}

$studentId = $_SESSION['user_id'];
$db = new Database();

// Process enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll'])) {
    $classId = (int)$_POST['class_id'];
    
    // Check if already enrolled
    $query = "SELECT * FROM enrollments WHERE student_id = $studentId AND class_id = $classId";
    $result = $db->query($query);
    
    if ($result->num_rows > 0) {
        $enrollError = "You are already enrolled in this class.";
    } else {
        // Enroll student
        $query = "INSERT INTO enrollments (student_id, class_id) VALUES ($studentId, $classId)";
        $result = $db->query($query);
        
        if ($result) {
            $enrollSuccess = "Successfully enrolled in the class!";
        } else {
            $enrollError = "Failed to enroll. Please try again.";
        }
    }
}

// Get available classes (that the student is not already enrolled in)
$query = "SELECT c.*, u.full_name as teacher_name, 
          (SELECT COUNT(*) FROM enrollments WHERE class_id = c.class_id) as student_count 
          FROM classes c 
          JOIN users u ON c.teacher_id = u.user_id 
          WHERE c.class_id NOT IN (
              SELECT class_id FROM enrollments WHERE student_id = $studentId
          )
          ORDER BY c.class_code";
$result = $db->query($query);
$availableClasses = [];
while ($row = $result->fetch_assoc()) {
    $availableClasses[] = $row;
}

// Get enrolled classes
$query = "SELECT c.*, u.full_name as teacher_name 
          FROM enrollments e 
          JOIN classes c ON e.class_id = c.class_id 
          JOIN users u ON c.teacher_id = u.user_id 
          WHERE e.student_id = $studentId";
$result = $db->query($query);
$enrolledClasses = [];
while ($row = $result->fetch_assoc()) {
    $enrolledClasses[] = $row;
}

// Include header
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h1>Browse Classes</h1>
            <a href="student.php" class="btn btn-outline-secondary">Back to Dashboard</a>
        </div>
    </div>
    
    <?php if (isset($enrollError)): ?>
        <div class="alert alert-danger">
            <?php echo $enrollError; ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($enrollSuccess)): ?>
        <div class="alert alert-success">
            <?php echo $enrollSuccess; ?>
            <a href="student.php" class="alert-link">View your enrolled classes</a>.
        </div>
    <?php endif; ?>
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Available Classes</h5>
                </div>
                <div class="card-body">
                    <?php if (count($availableClasses) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Class Code</th>
                                        <th>Class Name</th>
                                        <th>Semester</th>
                                        <th>Teacher</th>
                                        <th>Students</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($availableClasses as $class): ?>
                                        <tr>
                                            <td><?php echo $class['class_code']; ?></td>
                                            <td><?php echo $class['class_name']; ?></td>
                                            <td><?php echo $class['semester']; ?></td>
                                            <td><?php echo $class['teacher_name']; ?></td>
                                            <td><?php echo $class['student_count']; ?></td>
                                            <td>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="class_id" value="<?php echo $class['class_id']; ?>">
                                                    <button type="submit" name="enroll" class="btn btn-success btn-sm">Enroll</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No available classes found. You are enrolled in all existing classes.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">My Enrolled Classes</h5>
                </div>
                <div class="card-body">
                    <?php if (count($enrolledClasses) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Class Code</th>
                                        <th>Class Name</th>
                                        <th>Semester</th>
                                        <th>Teacher</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enrolledClasses as $class): ?>
                                        <tr>
                                            <td><?php echo $class['class_code']; ?></td>
                                            <td><?php echo $class['class_name']; ?></td>
                                            <td><?php echo $class['semester']; ?></td>
                                            <td><?php echo $class['teacher_name']; ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            You are not enrolled in any classes yet.
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
