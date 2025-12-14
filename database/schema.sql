-- ================================================
-- Smart Student Result Management System
-- Database Schema with Sample Data
-- ================================================

-- Drop existing tables if they exist (for clean setup)
DROP TABLE IF EXISTS queries;
DROP TABLE IF EXISTS marks;
DROP TABLE IF EXISTS subjects;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS users;

-- ================================================
-- USERS TABLE (Admin, Staff, Student)
-- ================================================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff', 'student') NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================
-- STUDENTS TABLE
-- ================================================
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    phone VARCHAR(15),
    class VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================
-- SUBJECTS TABLE
-- ================================================
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subject_code VARCHAR(20) UNIQUE NOT NULL,
    subject_name VARCHAR(100) NOT NULL,
    total_marks INT NOT NULL DEFAULT 100,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================
-- MARKS TABLE (with constraint to prevent duplicate entries)
-- ================================================
CREATE TABLE marks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT NOT NULL,
    staff_id INT NOT NULL,
    marks_obtained DECIMAL(5,2) NOT NULL,
    posted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_student_subject (student_id, subject_id),
    CHECK (marks_obtained >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================
-- QUERIES TABLE (Student complaints/queries)
-- ================================================
CREATE TABLE queries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    subject_id INT,
    query_type VARCHAR(50) NOT NULL,
    query_text TEXT NOT NULL,
    status ENUM('pending', 'reviewed', 'resolved') DEFAULT 'pending',
    admin_response TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================
-- SAMPLE DATA
-- ================================================

-- Insert Admin Users
INSERT INTO users (username, password, role, full_name, email) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Administrator', 'admin@school.com');
-- Password: password

-- Insert Staff Users
INSERT INTO users (username, password, role, full_name, email) VALUES
('staff1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'Dr. John Smith', 'john.smith@school.com'),
('staff2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'Prof. Mary Johnson', 'mary.johnson@school.com'),
('staff3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'staff', 'Dr. Robert Williams', 'robert.williams@school.com');
-- Password: password

-- Insert Student Users
INSERT INTO users (username, password, role, full_name, email) VALUES
('student1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Alice Brown', 'alice.brown@school.com'),
('student2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Bob Davis', 'bob.davis@school.com'),
('student3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Charlie Wilson', 'charlie.wilson@school.com'),
('student4', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Diana Martinez', 'diana.martinez@school.com'),
('student5', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student', 'Edward Taylor', 'edward.taylor@school.com');
-- Password: password

-- Insert Students
INSERT INTO students (student_id, full_name, email, phone, class) VALUES
('STU001', 'Alice Brown', 'alice.brown@school.com', '1234567890', 'Class 10A'),
('STU002', 'Bob Davis', 'bob.davis@school.com', '1234567891', 'Class 10A'),
('STU003', 'Charlie Wilson', 'charlie.wilson@school.com', '1234567892', 'Class 10B'),
('STU004', 'Diana Martinez', 'diana.martinez@school.com', '1234567893', 'Class 10B'),
('STU005', 'Edward Taylor', 'edward.taylor@school.com', '1234567894', 'Class 10A');

-- Insert Subjects
INSERT INTO subjects (subject_code, subject_name, total_marks) VALUES
('MATH101', 'Mathematics', 100),
('PHY101', 'Physics', 100),
('CHEM101', 'Chemistry', 100),
('BIO101', 'Biology', 100),
('ENG101', 'English', 100),
('CS101', 'Computer Science', 100);

-- Insert Sample Marks (Some staff have posted marks, some haven't)
-- Staff1 (John Smith) posts marks for Mathematics
INSERT INTO marks (student_id, subject_id, staff_id, marks_obtained) VALUES
(1, 1, 2, 85.5),
(2, 1, 2, 92.0),
(3, 1, 2, 78.5),
(4, 1, 2, 88.0),
(5, 1, 2, 95.5);

-- Staff2 (Mary Johnson) posts marks for Physics
INSERT INTO marks (student_id, subject_id, staff_id, marks_obtained) VALUES
(1, 2, 3, 82.0),
(2, 2, 3, 90.5),
(3, 2, 3, 75.0),
(4, 2, 3, 86.5),
(5, 2, 3, 93.0);

-- Staff3 (Robert Williams) posts marks for Chemistry
INSERT INTO marks (student_id, subject_id, staff_id, marks_obtained) VALUES
(1, 3, 4, 80.0),
(2, 3, 4, 88.5),
(3, 3, 4, 72.5),
(4, 3, 4, 85.0),
(5, 3, 4, 91.5);

-- Insert Sample Queries
INSERT INTO queries (student_id, subject_id, query_type, query_text, status) VALUES
(1, 1, 'Grade Discrepancy', 'I believe there might be an error in my Mathematics marks. Could you please review?', 'pending'),
(2, 2, 'Clarification', 'Can you explain how the Physics practical marks were calculated?', 'reviewed'),
(3, NULL, 'General Query', 'I would like to know about the result publication date.', 'pending');

-- ================================================
-- USEFUL VIEWS FOR STATISTICS
-- ================================================

-- View: Staff who have posted marks
CREATE OR REPLACE VIEW staff_with_marks AS
SELECT DISTINCT u.id, u.username, u.full_name, u.email
FROM users u
INNER JOIN marks m ON u.id = m.staff_id
WHERE u.role = 'staff';

-- View: Staff who haven't posted marks
CREATE OR REPLACE VIEW staff_without_marks AS
SELECT u.id, u.username, u.full_name, u.email
FROM users u
WHERE u.role = 'staff'
AND u.id NOT IN (SELECT DISTINCT staff_id FROM marks);

