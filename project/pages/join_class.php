<?php
//page where a student can join a new class
// check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    //if not redirect to the login pafe
    header('Location: index.php?page=login');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //getting the class code from the form input
    $classCode = htmlspecialchars($_POST['class_code']);
    
    try {
        // check if class code exists by retreiving the row with the matching code
        $query = "SELECT id FROM classes WHERE class_code = '$classCode'";
        $result = mysqli_query($conn, $query);
        
        //if such a row exists
        if (mysqli_num_rows($result) > 0) {
            $class = mysqli_fetch_assoc($result);
            //get the id
            $classId = $class['id'];
            
            // check if the student is already enrolled by checking if there is a row in class_enrollments where the student id and class id exists
            $query = "SELECT id FROM class_enrollments WHERE class_id = $classId AND student_id = {$_SESSION['user_id']}";
            $result = mysqli_query($conn, $query);
            
            if (mysqli_num_rows($result) === 0) {
                // If the students isn't already enrolled enroll them by inserting the info to the class_enrollments table
                $query = "INSERT INTO class_enrollments (class_id, student_id) VALUES ($classId, {$_SESSION['user_id']})";
                if (mysqli_query($conn, $query)) {
                    $success = "Successfully joined the class!";
                } else {
                    $error = "Failed to join class. Please try again.";
                }
            } else {
                $error = "You are already enrolled in this class.";
            }
        } else {
            $error = "Invalid class code.";
        }
    } catch (Exception $e) {
        $error = "Failed to join class. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Class - Attendance Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div id="joinClassPage">
        <h1 id="joinClassTitle">Join Class</h1>
        
        <div id="joinClassLayout">
            <div id="joinClassForm">
                <?php if (isset($error)): ?>
                    <div class="error"><?php echo $error; ?></div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="success"><?php echo $success; ?></div>
                <?php endif; ?>
                <!-- Form to get the class code -->
                <form method="POST" action="">
                    <div id="formGroup">
                        <label for="class_code">Class Code</label>
                        <input type="text" id="class_code" name="class_code" required>
                    </div>
                    <button id="joinClassBtn" type="submit">Join Class</button>
                </form>
                <!-- Link to redirect to the dashboard -->
                <p><a id="backToDashboardLink" href="index.php?page=dashboard">Back to Dashboard</a></p>
            </div>
        </div>
    </div>
</body>
</html> 