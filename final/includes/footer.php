    </div>
    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-3">
        <div class="container text-center">
            <p class="mb-0">&copy; <?php echo date('Y'); ?> Attendance Management System | Web Programming Final Project</p>
        </div>
    </footer>
    
    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Chart.js for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/darkmode.js"></script>
    
    <!-- Initialize dark mode based on user preference -->
    <script>
        // Check if dark mode is enabled in localStorage
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.getElementById('dark-mode-css').disabled = false;
            document.body.classList.add('dark-mode');
        }
    </script>
</body>
</html>
