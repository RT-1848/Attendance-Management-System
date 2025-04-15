/**
 * Dark Mode Toggle Functionality
 * This file handles the dark mode feature of the application
 */

// DOM ready function
document.addEventListener('DOMContentLoaded', function() {
    // Get the dark mode toggle button
    const darkModeToggle = document.getElementById('dark-mode-toggle');
    
    // Get the dark mode stylesheet
    const darkModeStylesheet = document.getElementById('dark-mode-css');
    
    // Dark mode toggle event
    darkModeToggle.addEventListener('click', function() {
        // Check if dark mode is currently enabled
        if (darkModeStylesheet.disabled) {
            // Enable dark mode
            darkModeStylesheet.disabled = false;
            document.body.classList.add('dark-mode');
            
            // Update toggle button
            darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
            
            // Save preference to localStorage
            localStorage.setItem('darkMode', 'enabled');
        } else {
            // Disable dark mode
            darkModeStylesheet.disabled = true;
            document.body.classList.remove('dark-mode');
            
            // Update toggle button
            darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
            
            // Save preference to localStorage
            localStorage.setItem('darkMode', 'disabled');
        }
    });
    
    // Initialize based on saved preference
    if (localStorage.getItem('darkMode') === 'enabled') {
        darkModeStylesheet.disabled = false;
        document.body.classList.add('dark-mode');
        darkModeToggle.innerHTML = '<i class="fas fa-sun"></i>';
    } else {
        darkModeStylesheet.disabled = true;
        document.body.classList.remove('dark-mode');
        darkModeToggle.innerHTML = '<i class="fas fa-moon"></i>';
    }
});
