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
$db = new Database();

// Get class details
$query = "SELECT * FROM classes WHERE class_id = $classId AND teacher_id = $teacherId";
$result = $db->query($query);

// If class not found or doesn't belong to this teacher, redirect to dashboard
if ($result->num_rows === 0) {
    redirect('admin.php');
}

$class = $result->fetch_assoc();

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classCode = sanitize($_POST['class_code']);
    $className = sanitize($_POST['class_name']);
    $semester = sanitize($_POST['semester']);
    
    // Validate input
    $errors = [];
    
    if (empty($classCode)) {
        $errors[] = "Class code is required.";
    }
    
    if (empty($className)) {
        $errors[] = "Class name is required.";
    }
    
    if (empty($semester)) {
        $errors[] = "Semester is required.";
    }
    
    // Check if class code already exists (if changed)
    if ($classCode !== $class['class_code']) {
        $query = "SELECT * FROM classes WHERE class_code = '$classCode' AND class_id != $classId";
        $result = $db->query($query);
        
        if ($result->num_rows > 0) {
            $errors[] = "Class code already exists. Please choose another one.";
        }
    }
    
    // If no errors, update the class
    if (empty($errors)) {
        $query = "UPDATE classes 
                  SET class_code = '$classCode', class_name = '$className', semester = '$semester' 
                  WHERE class_id = $classId AND teacher_id = $teacherId";
        $result = $db->query($query);
        
        if ($result) {
            $success = "Class updated successfully!";
            
            // Update class variable to show the changes
            $class['class_code'] = $classCode;
            $class['class_name'] = $className;
            $class['semester'] = $semester;
            
            // Redirect after a brief delay
            header("refresh:2;url=class_details.php?id=$classId");
        } else {
            $errors[] = "Failed to update class. Please try again.";
        }
    }
}

// Include header
include 'includes/header.php';
?>

<div class="container py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="mb-0">Edit Class</h3>
                    <a href="class_details.php?id=<?php echo $classId; ?>" class="btn btn-outline-secondary btn-sm">Back to Class</a>
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
                    
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="class_code" class="form-label">Class Code</label>
                            <input type="text" class="form-control" id="class_code" name="class_code" value="<?php echo $class['class_code']; ?>" required>
                            <small class="form-text text-muted">A unique code to identify your class (e.g., department code and number).</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="class_name" class="form-label">Class Name</label>
                            <input type="text" class="form-control" id="class_name" name="class_name" value="<?php echo $class['class_name']; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="semester" class="form-label">Semester</label>
                            <input type="text" class="form-control" id="semester" name="semester" value="<?php echo $class['semester']; ?>" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Update Class</button>
                            <a href="class_details.php?id=<?php echo $classId; ?>" class="btn btn-outline-secondary">Cancel</a>
                        </div>
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
