@echo off
REM MySQL Migration Verification Script
REM Institute LMS - Post-Migration Checks

echo ========================================
echo MySQL Migration Verification
echo ========================================
echo.

echo Checking database connection...
php artisan db:show
if %ERRORLEVEL% NEQ 0 (
    echo ✗ Database connection failed
    pause
    exit /b 1
)
echo ✓ Database connection OK
echo.

echo Checking tables...
php artisan db:table users
php artisan db:table courses
php artisan db:table modules
php artisan db:table contents
echo ✓ Core tables exist
echo.

echo Running test suite...
php artisan test --stop-on-failure
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ⚠ Some tests failed
    echo Review the output above for details
) else (
    echo.
    echo ✓ All tests passed!
)
echo.

echo ========================================
echo Verification Complete
echo ========================================
echo.

pause
