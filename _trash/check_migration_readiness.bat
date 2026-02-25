@echo off
REM Migration Readiness Check Script
REM Verifies all prerequisites before migration

echo ========================================
echo MySQL Migration Readiness Check
echo ========================================
echo.

set READY=1

echo [1/5] Checking MySQL installation...
mysql --version >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo ✓ MySQL is installed
    mysql --version
) else (
    echo ✗ MySQL is NOT installed
    echo   Install MySQL from: https://dev.mysql.com/downloads/
    set READY=0
)
echo.

echo [2/5] Checking MySQL service status...
sc query MySQL80 | find "RUNNING" >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo ✓ MySQL service is running
) else (
    echo ✗ MySQL service is NOT running
    echo   Start with: net start MySQL80
    set READY=0
)
echo.

echo [3/5] Checking .env configuration...
findstr /C:"DB_CONNECTION=mysql" .env >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo ✓ .env configured for MySQL
    findstr /B "DB_" .env
) else (
    echo ✗ .env NOT configured for MySQL
    echo   Expected: DB_CONNECTION=mysql
    set READY=0
)
echo.

echo [4/5] Checking PHP installation...
php --version >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    echo ✓ PHP is installed
    php --version | findstr /C:"PHP"
) else (
    echo ✗ PHP is NOT installed
    set READY=0
)
echo.

echo [5/5] Checking Laravel installation...
if exist "artisan" (
    echo ✓ Laravel project detected
    php artisan --version
) else (
    echo ✗ Laravel project NOT found
    echo   Run this script from project root
    set READY=0
)
echo.

echo ========================================
echo Readiness Summary
echo ========================================
echo.

if %READY% EQU 1 (
    echo ✓ ALL CHECKS PASSED
    echo.
    echo You are ready to migrate!
    echo.
    echo Next steps:
    echo   1. Run: setup_mysql.bat
    echo   2. Run: migrate_to_mysql.bat
    echo.
    echo See MIGRATION_STATUS.md for detailed instructions.
) else (
    echo ✗ SOME CHECKS FAILED
    echo.
    echo Please fix the issues above before migrating.
    echo.
    echo Need help? See MIGRATION_STATUS.md
)
echo.

echo ========================================
echo Current Database Status
echo ========================================
echo.

if exist "database\database.sqlite" (
    echo Current: SQLite database exists
    echo Location: database\database.sqlite
    for %%A in ("database\database.sqlite") do echo Size: %%~zA bytes
) else (
    echo Warning: SQLite database not found
)
echo.

echo Target: MySQL database (punjabidiomas_lms)
echo Status: Not yet created
echo.

pause
