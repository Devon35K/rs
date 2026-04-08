-- AcadPortal Database Schema
-- Run this in your MySQL client or phpMyAdmin

-- CREATE DATABASE IF NOT EXISTS acadportal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE acadportal;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(191) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','faculty','student') NOT NULL DEFAULT 'student',
    avatar VARCHAR(255) NULL,
    google_id VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Announcements table
CREATE TABLE IF NOT EXISTS announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    audience ENUM('all','faculty','student') NOT NULL DEFAULT 'all',
    cover_image VARCHAR(255) NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Memos table
CREATE TABLE IF NOT EXISTS memos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    memo_no VARCHAR(100) NOT NULL,
    date_issued DATE NOT NULL,
    subject VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    type ENUM('internal','external') NOT NULL DEFAULT 'internal',
    file_path VARCHAR(255) NULL,
    link VARCHAR(500) NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Documents / Uploads table
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NULL,
    file_size INT NULL,
    category VARCHAR(100) NULL,
    audience ENUM('all','faculty','student') NOT NULL DEFAULT 'all',
    drive_link VARCHAR(500) NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Visit / Analytics logs
CREATE TABLE IF NOT EXISTS visit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    ip_address VARCHAR(45) NULL,
    page VARCHAR(100) NULL,
    user_agent TEXT NULL,
    visited_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Seed default admin (password: 'password' — hashed with PHP password_hash)
-- Hash below generated with: password_hash('password', PASSWORD_DEFAULT)
INSERT INTO users (name, email, password, role) VALUES
('Administrator', 'admin@school.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE password = VALUES(password);
-- Default credentials: admin@school.edu / password
-- NOTE: If login fails after fresh import, run fixpw.php to regenerate hash on this machine.