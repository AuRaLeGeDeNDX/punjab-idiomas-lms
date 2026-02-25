@echo off
REM SQLite to MySQL Migration Setup Script
REM Institute LMS - Database Setup

echo ========================================
echo Institute LMS - MySQL Database Setup
echo ========================================
echo.

echo Creating MySQL database and user...
echo Please enter your MySQL root password when prompted.
echo.

mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS punjabidiomas_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER IF NOT EXISTS 'punjabidiomas.user'@'localhost' IDENTIFIED BY 'StrongPassword123!'; GRANT ALL PRIVILEGES ON punjabidiomas_lms.* TO 'punjabidiomas.user'@'localhost'; FLUSH PRIVILEGES;"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ✓ Database 'punjabidiomas_lms' created successfully!
    echo ✓ User 'punjabidiomas.user' created with full privileges!
    echo.
    echo Verifying database...
    mysql -u root -p -e "USE punjabidiomas_lms; SELECT @@character_set_database, @@collation_database;"
    echo.
    echo ========================================
    echo Database setup complete!
    echo ========================================
    echo.
    echo Next steps:
    echo 1. Run: migrate_to_mysql.bat
    echo    (This will run migrations and seed the database)
    echo.
) else (
    echo.
    echo ✗ Failed to create database.
    echo Please check your MySQL installation and credentials.
    echo.
)

pause
