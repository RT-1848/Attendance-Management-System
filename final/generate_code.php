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

// Get class details
$db = new Database();
$query = "SELECT * FROM classes WHERE class_id = $classId AND teacher_id = $teacherId";
$result = $db->query($query);

// If class not found or doesn't belong to this teacher, redirect to dashboard
if ($result->num_rows === 0) {
    redirect('admin.php');
}

$class = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $expiryHours = $_POST['expiry_hours'] ?? 1;
    $attendanceDate = date('Y-m-d');
    
    // Generate a random 6-character code
    $attendanceCode = generateRandomString(6);
    
    // Set expiry time
    $expiryTime = date('Y-m-d H:i:s', strtotime("+$expiryHours hours"));
    
    // Check if a code already exists for this class and date
    $query = "SELECT * FROM attendance_codes 
              WHERE class_id = $classId AND attendance_date = '$attendanceDate'";
    $result = $db->query($query);
    
    if ($result->num_rows > 0) {
        // Update existing code
        $query = "UPDATE attendance_codes 
                  SET attendance_code = '$attendanceCode', expiry_time = '$expiryTime' 
                  WHERE class_id = $classId AND attendance_date = '$attendanceDate'";
        $db->query($query);
        $message = "Attendance code updated successfully!";
    } else {
        // Insert new code
        $query = "INSERT INTO attendance_codes (class_id, attendance_code, attendance_date, expiry_time) 
                  VALUES ($classId, '$attendanceCode', '$attendanceDate', '$expiryTime')";
        $db->query($query);
        $message = "Attendance code generated successfully!";
    }
    
    $codeGenerated = true;
}

// Include header
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <h1>Generate Attendance Code</h1>
            <a href="class_details.php?id=<?php echo $classId; ?>" class="btn btn-outline-secondary">Back to Class</a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6 mx-auto">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><?php echo $class['class_name']; ?> (<?php echo $class['class_code']; ?>)</h5>
                </div>
                <div class="card-body">
                    <?php if (isset($codeGenerated) && isset($message)): ?>
                        <div class="alert alert-success text-center">
                            <?php echo $message; ?>
                        </div>
                        
                        <div class="text-center mb-4">
                            <h2 class="display-4"><?php echo $attendanceCode; ?></h2>
                            <p class="text-muted">This code will expire at <?php echo date('h:i A', strtotime($expiryTime)); ?> (<?php echo $expiryHours; ?> hour<?php echo $expiryHours > 1 ? 's' : ''; ?>)</p>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button onclick="window.print()" class="btn btn-secondary">Print Code</button>
                            <a href="generate_code.php?id=<?php echo $classId; ?>" class="btn btn-primary">Generate New Code</a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="expiry_hours" class="form-label">Code Validity (hours)</label>
                                <select class="form-select" id="expiry_hours" name="expiry_hours">
                                    <option value="1">1 hour</option>
                                    <option value="2">2 hours</option>
                                    <option value="3">3 hours</option>
                                    <option value="6">6 hours</option>
                                    <option value="12">12 hours</option>
                                    <option value="24">24 hours</option>
                                </select>
                                <small class="form-text text-muted">Select how long the attendance code should be valid.</small>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">Generate Code</button>
                                <a href="class_details.php?id=<?php echo $classId; ?>" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                        
                        <div class="alert alert-info mt-3">
                            <h5>How It Works</h5>
                            <ol>
                                <li>Generate a unique attendance code for today's class.</li>
                                <li>Share the code with your students during class.</li>
                                <li>Students enter the code on their accounts to mark their attendance.</li>
                                <li>The code will automatically expire after the set duration.</li>
                            </ol>
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
