# Attendance Tracker

A web-based attendance tracking system that allows teachers to create classes and generate attendance codes, while students can join classes and mark their attendance using these codes.

## Features

- User authentication (teachers and students)
- Class creation and management
- Class code generation for student enrollment
- Daily attendance code generation
- Attendance tracking and reporting
- Secure password handling
- Responsive design

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Modern web browser

## Installation

1. Clone the repository to your web server directory:
   ```bash
   git clone [repository-url]
   ```

2. Create a MySQL database and import the database structure:
   ```bash
   mysql -u username -p database_name < database.sql
   ```

3. Configure the database connection in `config/database.php`:
   ```php
   $host = 'localhost';
   $dbname = 'attendance_tracker';
   $username = 'your_username';
   $password = 'your_password';
   ```

4. Ensure your web server has write permissions for the project directory.

5. Access the application through your web browser:
   ```
   http://localhost/attendance-tracker
   ```

## Usage

### For Teachers

1. Register an account as a teacher
2. Log in to your account
3. Create classes and get class codes
4. Share class codes with students
5. Generate daily attendance codes
6. View attendance records

### For Students

1. Register an account as a student
2. Log in to your account
3. Join classes using class codes
4. Mark attendance using daily codes
5. View your attendance history

## Security Features

- Password hashing using PHP's password_hash()
- Prepared statements to prevent SQL injection
- Session management
- Input validation and sanitization
- Role-based access control

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a new Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details. 