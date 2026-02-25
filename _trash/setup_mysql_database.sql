-- SQLite to MySQL Migration Setup Script
-- Institute LMS Database Setup

-- Create database with proper charset and collation
CREATE DATABASE IF NOT EXISTS punjabidiomas_lms 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Create user with secure password
CREATE USER IF NOT EXISTS 'punjabidiomas.user'@'localhost' IDENTIFIED BY 'StrongPassword123!';
GRANT ALL PRIVILEGES ON punjabidiomas_lms.* TO 'punjabidiomas.user'@'localhost';
FLUSH PRIVILEGES;

-- Use the database
USE punjabidiomas_lms;

-- Verify settings
SELECT @@character_set_database, @@collation_database;
