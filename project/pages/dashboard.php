<?php
//This is the dashboard page where the user can see the classes they are in. teachers can create classes and students can add classes. Teachers can delete classes and students can drop classes.
// Check if user is logged in to ensure the page can't be accessed simply through the browser
if (!isset($_SESSION['user_id'])) {
    //if not loged in redirect to the login page
    header('Location: index.php?page=login');
    exit();
}

//getting the session variables that were stored in the login page
$userId = $_SESSION['user_id'];
$userRole = $_SESSION['role'];

// Get classes based on user role
if ($userRole === 'teacher') {
    //if ther area a teacher simply get the classes that have that teacher's ID used as a foreign key and store the infor in an assoc array
    $query = "SELECT * FROM classes WHERE teacher_id = $userId";
    $result = mysqli_query($conn, $query);
    $classes = mysqli_fetch_all($result, MYSQLI_ASSOC);
} else {
    //else if they are a student get all the information from the classes table then perform a join action with the class_enrollment table where the student id is used to enroll and the class id's match in both table
    //this essentially gets all the classes the student is enrollec in and stores them in an assoc array.
    $query = "SELECT c.* FROM classes c
              JOIN class_enrollments ce ON c.id = ce.class_id
              WHERE ce.student_id = $userId";
    $result = mysqli_query($conn, $query);
    $classes = mysqli_fetch_all($result, MYSQLI_ASSOC);
}

//getting the user info particularly their first and last name
$query = "SELECT * FROM users WHERE id = $userId";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);
$userName = $user['username'];
$firstName = $user['first_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Attendance Tracker</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="container">
        <div class="dashboard-header">
            <h1 class="dashboard-title"> 
                <img src="assets/pictures/mt-logo.png" width="50px" height="30px"> 
                <!-- Header for the dashboard with user's name -->
                <?php echo htmlspecialchars($firstName); ?>'s Courses
            </h1>
        </div>

        <!-- Navigation menue for users-->
        <div class="nav-menu">
            <?php if ($userRole === 'teacher'): ?>
                <!-- a create class link for teachers that redirects to a diffrent page -->
                <a href="index.php?page=create_class" class="nav-button">Create Class</a>
            <?php else: ?>
                <!-- a join class link for students that redirects to a difftent page-->
                <a href="index.php?page=join_class" class="nav-button">Join Class</a>
            <?php endif; ?>
            <!-- A log out button -->
            <a href="index.php?page=logout" class="nav-button">Logout</a>
        </div>

        <!-- Grid th show all the courses -->
        <div class="courses-grid">
            <!-- For each loop over the classes retreived from the sql database -->
            <?php foreach ($classes as $class): ?>
                 <?php 
                   $gradientIndex = $class['id'] % 10 + 1; //  gradient chosen based on class ID
                    $selectedGradientClass = "course-banner-gradient-" . $gradientIndex;
                ?>
                <!-- Displaying each class in a d2l style class block -->
                <div class="course-card">
                   <div class="<?php echo $selectedGradientClass; ?>"></div>
                    <div class="course-content">
                        <h3 class="course-name"><?php echo htmlspecialchars($class['class_name']); ?></h3>
                        <div class="course-code">Class Code: <?php echo htmlspecialchars($class['class_code']); ?></div>
                        <div class="course-meta">
                            <?php if ($userRole === 'teacher'): ?>
                                <!-- displaying teacher buttons: class_details link that redirect to a page with class attendence info, end class button that ends the class and removes all info from the DB -->
                                <a href="index.php?page=class_details&id=<?php echo $class['id']; ?>" class="course-link">View Details</a>
                                <form method="post">
                                <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                <button type="submit" name="end" class="course-link" style="margin-top: 2px;">End Class</button>
                                </form>
                            <?php else: ?>
                                <!-- Displaying student buttons: link for a student class details page that has the indivisual students attendence info, mark attendence button that redirects students to a page whene they can mark -->
                                <a href="index.php?page=mark_attendance&class_id=<?php echo $class['id']; ?>" class="course-link">Mark Attendance</a>
                                <a href="index.php?page=class_details_student&id=<?php echo $class['id']; ?>" class="course-link">View Details</a>
                                <form method="post">
                                <input type="hidden" name="class_id" value="<?php echo $class['id']; ?>">
                                <!-- Button to drop the class which will remove their enrollment -->
                                <button type="submit" name="drop" class="course-link" style="margin-top: 2px;">Drop Class</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
<footer>
    <p>&copy MTSU CSCI 4410 | All Rights Reserved</p>
    <strong>Youssef Botros | Harry He | Khalid Khalel | Henry Ngo | Ryan Thieu | Marcos Wofford</strong>
</footer>
</html> 
<?php
//php block to handle dropping or deleting a class
    if ($_SERVER['REQUEST_METHOD']==="POST" && isset($_POST['drop'])) {
        //if a student is dropping a class
        //retreive the class id from the hidden for associated with the drop button
        $classid = $_POST["class_id"];
        //query to unenroll the student
        $drop_query = "DELETE FROM class_enrollments WHERE class_id = $classid AND student_id = $userId";
        $result = mysqli_query($conn, $drop_query);
        //if the query was succesfull redirect the student back to the dashboard
        if ($result) {
            header("Location: index.php?page=dashboard");
            exit;
        }
        //echo"<h1>Done</h1>";
    }
    else if($_SERVER['REQUEST_METHOD']==="POST" && isset($_POST['end'])){
        //if a teacher want to end a class       
        //retreive the class id from the hidden for associated with the drop button
        $classid = $_POST["class_id"];
        //queries to unenroll all students from the class, delete all attendence record for the class, delete all the daily attendence codes generated for the clas, and delete the class itself.
        //it is important that the queries are done in this order as the id from each table entry are used as foreign keys for others so error will emerge if executed in a diffrent order
        $end_query = "DELETE FROM class_enrollments WHERE class_id = $classid";
        $result = mysqli_query($conn, $end_query);
        $end_query1 = "DELETE FROM attendance_records WHERE class_id = $classid";
        $result = mysqli_query($conn, $end_query1);
        $end_query2 = "DELETE FROM attendance_codes WHERE class_id = $classid";
        $result = mysqli_query($conn, $end_query2);
        $end_query3 = "DELETE FROM classes WHERE id = $classid";
        $result = mysqli_query($conn, $end_query3);
        //redirecting to dashboard
        if ($result) {
            header("Location: index.php?page=dashboard");
            exit;
        }
    }
?>
