/**
 * Attendance Functions
 * This file handles the attendance code generation and verification
 */

// DOM ready function
document.addEventListener('DOMContentLoaded', function() {
    // Generate attendance code
    const generateCodeBtn = document.getElementById('generate-code-btn');
    if (generateCodeBtn) {
        generateCodeBtn.addEventListener('click', generateAttendanceCode);
    }
    
    // Submit attendance code
    const submitCodeForm = document.getElementById('attendance-code-form');
    if (submitCodeForm) {
        submitCodeForm.addEventListener('submit', verifyAttendanceCode);
    }
    
    // Copy code to clipboard
    const copyCodeBtn = document.getElementById('copy-code-btn');
    if (copyCodeBtn) {
        copyCodeBtn.addEventListener('click', copyCodeToClipboard);
    }
    
    // Mark attendance
    const attendanceCheckboxes = document.querySelectorAll('.attendance-checkbox');
    if (attendanceCheckboxes.length > 0) {
        attendanceCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateAttendanceStatus);
        });
    }
});

/**
 * Generate a random attendance code and save to database
 */
function generateAttendanceCode(e) {
    e.preventDefault();
    
    const classId = document.getElementById('class-id').value;
    
    // AJAX request to generate code
    fetch('attendance_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=generate_code&class_id=${classId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            // Show the generated code
            document.getElementById('attendance-code-display').textContent = data.code;
            document.getElementById('code-container').classList.remove('d-none');
            
            // Start the expiry countdown
            startExpiryCountdown(data.expiry);
        } else {
            // Show error
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while generating the code.');
    });
}

/**
 * Verify the attendance code submitted by a student
 */
function verifyAttendanceCode(e) {
    e.preventDefault();
    
    const codeInput = document.getElementById('attendance-code').value;
    const classId = document.getElementById('class-id').value;
    
    // AJAX request to verify code
    fetch('attendance_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=verify_code&class_id=${classId}&code=${codeInput}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            // Show success message
            document.getElementById('attendance-result').innerHTML = 
                '<div class="alert alert-success">Attendance marked successfully!</div>';
            
            // Clear the input
            document.getElementById('attendance-code').value = '';
            
            // Reload after 2 seconds
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            // Show error
            document.getElementById('attendance-result').innerHTML = 
                `<div class="alert alert-danger">Error: ${data.message}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('attendance-result').innerHTML = 
            '<div class="alert alert-danger">An error occurred while verifying the code.</div>';
    });
}

/**
 * Copy the attendance code to clipboard
 */
function copyCodeToClipboard() {
    const codeText = document.getElementById('attendance-code-display').textContent;
    
    // Create a temporary input element
    const tempInput = document.createElement('input');
    tempInput.value = codeText;
    document.body.appendChild(tempInput);
    
    // Select and copy the text
    tempInput.select();
    document.execCommand('copy');
    
    // Remove the temporary element
    document.body.removeChild(tempInput);
    
    // Show copied feedback
    const copyBtn = document.getElementById('copy-code-btn');
    const originalText = copyBtn.innerHTML;
    copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
    
    // Reset button text after 2 seconds
    setTimeout(() => {
        copyBtn.innerHTML = originalText;
    }, 2000);
}

/**
 * Start countdown timer for code expiry
 */
function startExpiryCountdown(expiryTimestamp) {
    const countdownElement = document.getElementById('code-expiry');
    
    // Update countdown every second
    const countdownInterval = setInterval(() => {
        // Get current timestamp
        const now = Math.floor(Date.now() / 1000);
        
        // Calculate remaining time
        const remainingTime = expiryTimestamp - now;
        
        if (remainingTime <= 0) {
            // Time expired
            clearInterval(countdownInterval);
            countdownElement.textContent = 'Expired';
            countdownElement.classList.remove('text-success');
            countdownElement.classList.add('text-danger');
            
            // Hide the code after expiry
            document.getElementById('code-container').classList.add('d-none');
        } else {
            // Format and display remaining time
            const minutes = Math.floor(remainingTime / 60);
            const seconds = remainingTime % 60;
            countdownElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
        }
    }, 1000);
}

/**
 * Update student attendance status manually
 */
function updateAttendanceStatus() {
    const checkbox = this;
    const enrollmentId = checkbox.dataset.enrollmentId;
    const date = checkbox.dataset.date;
    const status = checkbox.checked ? 'present' : 'absent';
    
    // AJAX request to update attendance
    fetch('attendance_ajax.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=update_status&enrollment_id=${enrollmentId}&date=${date}&status=${status}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status) {
            // Show success toast
            const toastElement = document.getElementById('attendance-toast');
            const toastBody = toastElement.querySelector('.toast-body');
            toastBody.textContent = 'Attendance updated successfully!';
            
            // Show the toast
            const toast = new bootstrap.Toast(toastElement);
            toast.show();
        } else {
            // Show error and revert checkbox
            alert('Error: ' + data.message);
            checkbox.checked = !checkbox.checked;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while updating attendance.');
        checkbox.checked = !checkbox.checked;
    });
}
