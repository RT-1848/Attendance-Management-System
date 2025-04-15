// Dark Mode Toggle Functionality

document.addEventListener('DOMContentLoaded', function() {
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    const darkModeCss = document.getElementById('dark-mode-css');
    
    // Check if dark mode is enabled in localStorage
    const isDarkMode = localStorage.getItem('darkMode') === 'enabled';
    
    // Set initial state based on localStorage
    if (isDarkMode) {
        darkModeCss.disabled = false;
        document.body.classList.add('dark-mode');
        darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    } else {
        darkModeCss.disabled = true;
        document.body.classList.remove('dark-mode');
        darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
    }
    
    // Toggle dark mode on button click
    darkModeToggle.addEventListener('click', function() {
        if (darkModeCss.disabled) {
            // Enable dark mode
            darkModeCss.disabled = false;
            document.body.classList.add('dark-mode');
            localStorage.setItem('darkMode', 'enabled');
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        } else {
            // Disable dark mode
            darkModeCss.disabled = true;
            document.body.classList.remove('dark-mode');
            localStorage.setItem('darkMode', 'disabled');
            darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
        }
    });
});

// Auto-hide alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alertElements = document.querySelectorAll('.alert:not(.alert-permanent)');
    
    alertElements.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Initialize tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Initialize popovers
document.addEventListener('DOMContentLoaded', function() {
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});

// Confirm delete actions
document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('.delete-confirm');
    
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                event.preventDefault();
            }
        });
    });
});

// Print functionality
document.addEventListener('DOMContentLoaded', function() {
    const printButtons = document.querySelectorAll('.print-button');
    
    printButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            window.print();
        });
    });
});
