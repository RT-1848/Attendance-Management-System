<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';
require_once 'includes/auth.php';

// Check if user is logged in and is a teacher
if (!isLoggedIn() || !isTeacher()) {
    redirect('login.php');
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $classCode = sanitize($_POST['class_code']);
    $className = sanitize($_POST['class_name']);
    $semester = sanitize($_POST['semester']);
    $teacherId = $_SESSION['user_id'];
    
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
    
    // Check if class code already exists
    $db = new Database();
    $query = "SELECT * FROM classes WHERE class_code = '$classCode'";
    $result = $db->query($query);
    
    if ($result->num_rows > 0) {
        $errors[] = "Class code already exists. Please choose another one.";
    }
    
    // If no errors, add the class
    if (empty($errors)) {
        $query = "INSERT INTO classes (class_code, class_name, teacher_id, semester) 
                  VALUES ('$classCode', '$className', $teacherId, '$semester')";
        $result = $db->query($query);
        
        if ($result) {
            $success = "Class added successfully!";
            // Redirect to classes page after a brief delay
            header("refresh:2;url=admin.php");
        } else {
            $errors[] = "Failed to add class. Please try again.";
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
                <div class="card-header">
                    <h3 class="mb-0">Add New Class</h3>
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
                            <input type="text" class="form-control" id="class_code" name="class_code" placeholder="e.g., CSCI4410" required>
                            <small class="form-text text-muted">A unique code to identify your class (e.g., department code and number).</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="class_name" class="form-label">Class Name</label>
                            <input type="text" class="form-control" id="class_name" name="class_name" placeholder="e.g., Web Programming" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="semester" class="form-label">Semester</label>
                            <input type="text" class="form-control" id="semester" name="semester" placeholder="e.g., Spring 2025" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Add Class</button>
                            <a href="admin.php" class="btn btn-outline-secondary">Cancel</a>
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
