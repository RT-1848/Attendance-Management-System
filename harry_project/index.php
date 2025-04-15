<?php
// Include necessary files
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Redirect logged in users to their respective dashboards
if (isLoggedIn()) {
    if (isTeacher()) {
        redirect('admin.php');
    } else if (isStudent()) {
        redirect('student.php');
    }
}

// Include header
include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="container-fluid px-4 py-5 my-5 text-center">
    <h1 class="display-5 fw-bold">Attendance Management System</h1>
    <div class="col-lg-6 mx-auto">
        <p class="lead mb-4">
            A comprehensive solution for managing classroom attendance. Track attendance efficiently, generate reports, and improve student engagement.
        </p>
        <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
            <a href="login.php" class="btn btn-primary btn-lg px-4 gap-3">Login</a>
            <a href="register.php" class="btn btn-outline-secondary btn-lg px-4">Register</a>
        </div>
    </div>
</div>

<!-- Features Section -->
<div class="container px-4 py-5">
    <h2 class="pb-2 border-bottom">Key Features</h2>
    
    <div class="row g-4 py-5 row-cols-1 row-cols-lg-3">
        <div class="col d-flex align-items-start">
            <div class="icon-square bg-light text-dark flex-shrink-0 me-3">
                <i class="fas fa-qrcode fa-2x"></i>
            </div>
            <div>
                <h2>Digital Attendance</h2>
                <p>Generate unique attendance codes for each class session. Students can mark their attendance by entering the code.</p>
            </div>
        </div>
        
        <div class="col d-flex align-items-start">
            <div class="icon-square bg-light text-dark flex-shrink-0 me-3">
                <i class="fas fa-chart-pie fa-2x"></i>
            </div>
            <div>
                <h2>Data Visualization</h2>
                <p>View attendance statistics with intuitive charts and graphs. Monitor attendance trends and identify patterns.</p>
            </div>
        </div>
        
        <div class="col d-flex align-items-start">
            <div class="icon-square bg-light text-dark flex-shrink-0 me-3">
                <i class="fas fa-file-pdf fa-2x"></i>
            </div>
            <div>
                <h2>Report Generation</h2>
                <p>Generate detailed attendance reports for individual students or entire classes. Export reports in PDF format.</p>
            </div>
        </div>
    </div>
</div>

<!-- User Types Section -->
<div class="container px-4 py-5 bg-light rounded-3">
    <h2 class="pb-2 border-bottom">For Teachers & Students</h2>
    
    <div class="row g-4 py-5">
        <div class="col-md-6">
            <h3>Teachers</h3>
            <ul class="list-unstyled">
                <li><i class="fas fa-check-circle text-success me-2"></i> Create and manage classes</li>
                <li><i class="fas fa-check-circle text-success me-2"></i> Generate attendance codes</li>
                <li><i class="fas fa-check-circle text-success me-2"></i> Mark attendance manually</li>
                <li><i class="fas fa-check-circle text-success me-2"></i> View detailed attendance reports</li>
                <li><i class="fas fa-check-circle text-success me-2"></i> Generate PDF reports</li>
            </ul>
        </div>
        
        <div class="col-md-6">
            <h3>Students</h3>
            <ul class="list-unstyled">
                <li><i class="fas fa-check-circle text-success me-2"></i> Mark attendance using codes</li>
                <li><i class="fas fa-check-circle text-success me-2"></i> View personal attendance records</li>
                <li><i class="fas fa-check-circle text-success me-2"></i> Check attendance percentage</li>
                <li><i class="fas fa-check-circle text-success me-2"></i> Get notified of attendance status</li>
            </ul>
        </div>
    </div>
</div>

<?php
// Include footer
include 'includes/footer.php';
?>
