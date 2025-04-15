<?php
require_once 'db.php';
global $db;

/**
 * Sanitize input data
 * @param string $data - Data to sanitize
 * @return string - Sanitized data
 */
function sanitize($data) {
    global $db;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $db->escapeString($data);
    return $data;
}

/**
 * Redirect to a URL
 * @param string $url - URL to redirect to
 */
function redirect($url) {
    header("Location: $url");
    exit();
}

/**
 * Check if user is logged in
 * @return boolean - True if logged in, false otherwise
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if user is a teacher
 * @return boolean - True if teacher, false otherwise
 */
function isTeacher() {
    return isLoggedIn() && $_SESSION['role'] === 'teacher';
}

/**
 * Check if user is a student
 * @return boolean - True if student, false otherwise
 */
function isStudent() {
    return isLoggedIn() && $_SESSION['role'] === 'student';
}

/**
 * Generate a random string (for attendance codes)
 * @param int $length - Length of the string
 * @return string - Random string
 */
function generateRandomString($length = 6) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

/**
 * Format date for display
 * @param string $date - Date in Y-m-d format
 * @return string - Formatted date (e.g., April 10, 2025)
 */
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

/**
 * Calculate attendance percentage
 * @param int $present - Number of days present
 * @param int $total - Total number of days
 * @return float - Attendance percentage
 */
function calculateAttendancePercentage($present, $total) {
    if ($total == 0) {
        return 0;
    }
    return round(($present / $total) * 100, 2);
}

/**
 * Get user details by ID
 * @param int $userId - User ID
 * @return array|null - User details or null if not found
 */
function getUserById($userId) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM users WHERE user_id = ?", "i", [$userId]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Get class details by ID
 * @param int $classId - Class ID
 * @return array|null - Class details or null if not found
 */
function getClassById($classId) {
    global $db;
    
    $stmt = $db->prepare("SELECT * FROM classes WHERE class_id = ?", "i", [$classId]);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Check if a student is enrolled in a class
 * @param int $studentId - Student ID
 * @param int $classId - Class ID
 * @return boolean - True if enrolled, false otherwise
 */
function isStudentEnrolled($studentId, $classId) {
    global $db;
    
    $stmt = $db->prepare(
        "SELECT * FROM enrollments WHERE student_id = ? AND class_id = ?", 
        "ii", 
        [$studentId, $classId]
    );
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

/**
 * Generate PDF report (for the unique feature)
 * @param string $html - HTML content for the PDF
 * @param string $filename - Filename for the PDF
 */
function generatePDF($html, $filename) {
    // Placeholder for PDF generation logic
    // In a real implementation, you would use a library like TCPDF or FPDF
    // Example with TCPDF:
    // require_once('tcpdf/tcpdf.php');
    // $pdf = new TCPDF();
    // $pdf->AddPage();
    // $pdf->writeHTML($html);
    // $pdf->Output($filename, 'D');
}
?>
