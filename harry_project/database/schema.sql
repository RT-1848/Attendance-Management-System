-- Create database
CREATE DATABASE IF NOT EXISTS attendance_system;
USE attendance_system;

-- Users table for both teachers and students
CREATE TABLE IF NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL, -- Will store hashed passwords
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('teacher', 'student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Classes table
CREATE TABLE IF NOT EXISTS classes (
    class_id INT AUTO_INCREMENT PRIMARY KEY,
    class_code VARCHAR(20) NOT NULL UNIQUE, -- e.g., CSCI4410
    class_name VARCHAR(100) NOT NULL,
    teacher_id INT NOT NULL,
    semester VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Enrollments table (maps students to classes)
CREATE TABLE IF NOT EXISTS enrollments (
    enrollment_id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    student_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY (class_id, student_id) -- Prevent duplicate enrollments
);

-- Attendance codes table
CREATE TABLE IF NOT EXISTS attendance_codes (
    code_id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    attendance_code VARCHAR(10) NOT NULL,
    attendance_date DATE NOT NULL,
    expiry_time TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
    UNIQUE KEY (class_id, attendance_date) -- One code per class per day
);

-- Attendance records table
CREATE TABLE IF NOT EXISTS attendance (
    attendance_id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent', 'late') DEFAULT 'absent',
    marked_by ENUM('teacher', 'student', 'system') NOT NULL,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(enrollment_id) ON DELETE CASCADE,
    UNIQUE KEY (enrollment_id, attendance_date) -- One attendance record per student per day per class
);

-- Insert sample data (optional)
-- Sample teacher
INSERT INTO users (username, password, email, full_name, role) 
VALUES ('teacher1', '$2y$10$qCEj4.uP0p9C1nsO1VKVZOzGwm5s3YEHhUJZgqsGJ4T0QZGpMBGOW', 'teacher1@example.com', 'Professor Smith', 'teacher');
-- The password is 'password123' hashed with bcrypt

-- Sample students
INSERT INTO users (username, password, email, full_name, role) 
VALUES 
('student1', '$2y$10$qCEj4.uP0p9C1nsO1VKVZOzGwm5s3YEHhUJZgqsGJ4T0QZGpMBGOW', 'student1@example.com', 'John Doe', 'student'),
('student2', '$2y$10$qCEj4.uP0p9C1nsO1VKVZOzGwm5s3YEHhUJZgqsGJ4T0QZGpMBGOW', 'student2@example.com', 'Jane Smith', 'student');

-- Sample class
INSERT INTO classes (class_code, class_name, teacher_id, semester) 
VALUES ('CSCI4410', 'Web Programming', 1, 'Spring 2025');

-- Sample enrollments
INSERT INTO enrollments (class_id, student_id) 
VALUES (1, 2), (1, 3);

-- Sample attendance code
INSERT INTO attendance_codes (class_id, attendance_code, attendance_date, expiry_time) 
VALUES (1, 'ABC123', '2025-04-10', '2025-04-10 23:59:59');

-- Sample attendance records
INSERT INTO attendance (enrollment_id, attendance_date, status, marked_by) 
VALUES 
(1, '2025-04-10', 'present', 'student'),
(2, '2025-04-10', 'present', 'teacher');
