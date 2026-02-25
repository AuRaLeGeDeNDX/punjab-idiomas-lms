@echo off
REM SQLite to MySQL Migration Execution Script
REM Institute LMS - Complete Migration Process

echo ========================================
echo Institute LMS - SQLite to MySQL Migration
echo ========================================
echo.

echo STEP 1: Clearing Laravel caches...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
echo ✓ Caches cleared
echo.

echo STEP 2: Testing MySQL connection...
php artisan db:show
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ✗ MySQL connection failed!
    echo Please ensure:
    echo   1. MySQL is running
    echo   2. Database 'punjabidiomas_lms' exists
    echo   3. User 'punjabidiomas.user' has proper privileges
    echo   4. .env credentials are correct
    echo.
    echo Run setup_mysql.bat first if you haven't already.
    pause
    exit /b 1
)
echo ✓ MySQL connection successful
echo.

echo STEP 3: Running migrations fresh...
echo WARNING: This will drop all existing tables!
echo.
set /p CONFIRM="Continue? (yes/no): "
if /i not "%CONFIRM%"=="yes" (
    echo Migration cancelled.
    pause
    exit /b 0
)

php artisan migrate:fresh --force
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ✗ Migration failed!
    echo Check the error messages above.
    pause
    exit /b 1
)
echo ✓ Migrations completed successfully
echo.

echo STEP 4: Seeding database...
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=TestUserSeeder
echo ✓ Database seeded
echo.

echo STEP 5: Verifying database structure...
php artisan db:show
echo.

echo ========================================
echo Migration Complete!
echo ========================================
echo.
echo Next steps:
echo 1. Test login functionality
echo 2. Test course creation
echo 3. Test file uploads
echo 4. Run test suite: php artisan test
echo.
echo Database: MySQL (punjabidiomas_lms)
echo User: punjabidiomas.user
echo Status: Ready for use
echo.

pause
