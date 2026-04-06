-- Corruption Control System Database Schema
-- Database: corruption_control_system

CREATE DATABASE IF NOT EXISTS corruption_control_system;
USE corruption_control_system;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    role ENUM('citizen', 'admin', 'officer', 'investigator') NOT NULL,
    department VARCHAR(100),
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Complaints table
CREATE TABLE IF NOT EXISTS complaints (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100),
    location VARCHAR(255),
    status ENUM('submitted', 'under_review', 'approved', 'rejected', 'investigation', 'closed') DEFAULT 'submitted',
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    assigned_officer_id INT,
    assigned_investigator_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_officer_id) REFERENCES users(id),
    FOREIGN KEY (assigned_investigator_id) REFERENCES users(id),
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_assigned_officer (assigned_officer_id),
    INDEX idx_created_at (created_at)
);

-- Evidence table
CREATE TABLE IF NOT EXISTS evidence (
    id INT PRIMARY KEY AUTO_INCREMENT,
    complaint_id INT NOT NULL,
    uploaded_by INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_type VARCHAR(50),
    file_size INT,
    description TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_complaint_id (complaint_id),
    INDEX idx_uploaded_by (uploaded_by)
);

-- FIR (First Information Report) table
CREATE TABLE IF NOT EXISTS fir (
    id INT PRIMARY KEY AUTO_INCREMENT,
    complaint_id INT NOT NULL UNIQUE,
    officer_id INT NOT NULL,
    fir_number VARCHAR(50) UNIQUE NOT NULL,
    fir_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    description TEXT,
    status ENUM('pending', 'filed', 'closed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (officer_id) REFERENCES users(id),
    INDEX idx_complaint_id (complaint_id),
    INDEX idx_officer_id (officer_id),
    INDEX idx_fir_number (fir_number)
);

-- Investigation Reports table
CREATE TABLE IF NOT EXISTS investigation_reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    complaint_id INT NOT NULL,
    investigator_id INT NOT NULL,
    fir_id INT,
    report_title VARCHAR(255) NOT NULL,
    report_text LONGTEXT NOT NULL,
    findings TEXT,
    recommendations TEXT,
    status ENUM('draft', 'submitted', 'approved', 'rejected') DEFAULT 'draft',
    submitted_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (investigator_id) REFERENCES users(id),
    FOREIGN KEY (fir_id) REFERENCES fir(id),
    INDEX idx_complaint_id (complaint_id),
    INDEX idx_investigator_id (investigator_id),
    INDEX idx_status (status)
);

-- Activity Log table
CREATE TABLE IF NOT EXISTS activity_log (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    complaint_id INT,
    action VARCHAR(255) NOT NULL,
    description TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_complaint_id (complaint_id),
    INDEX idx_timestamp (timestamp)
);

-- Comments/Notes table
CREATE TABLE IF NOT EXISTS comments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    complaint_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    is_internal BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id),
    INDEX idx_complaint_id (complaint_id),
    INDEX idx_user_id (user_id)
);

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info','success','warning','danger') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    link VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read)
);

-- NOTE: Use init_users.php to initialize users with correct passwords
-- Run: http://localhost/corruptioncontrolsystem/database/init_users.php
-- This will properly hash passwords and create the following users:
-- 
-- Admin: admin@corruptioncontrol.com / admin123@Ab
-- Officer: rajesh.officer@corruptioncontrol.com / admin123@Ab
-- Investigator: priya.investigator@corruptioncontrol.com / admin123@Ab
