@echo off
REM ============================================================================
REM Clear Laravel Caches After Rebranding
REM ============================================================================
REM This script clears all Laravel caches to ensure the Punjab Idiomas
REM rebranding changes are immediately visible to users.
REM ============================================================================

echo.
echo ============================================================================
echo Clearing Laravel Caches After Rebranding
echo ============================================================================
echo.

echo [1/4] Clearing configuration cache...
php artisan config:clear
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to clear configuration cache
    exit /b 1
)
echo Configuration cache cleared successfully!
echo.

echo [2/4] Clearing view cache...
php artisan view:clear
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to clear view cache
    exit /b 1
)
echo View cache cleared successfully!
echo.

echo [3/4] Clearing route cache...
php artisan route:clear
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to clear route cache
    exit /b 1
)
echo Route cache cleared successfully!
echo.

echo [4/4] Clearing application cache...
php artisan cache:clear
if %ERRORLEVEL% NEQ 0 (
    echo ERROR: Failed to clear application cache
    exit /b 1
)
echo Application cache cleared successfully!
echo.

echo ============================================================================
echo All caches cleared successfully!
echo The Punjab Idiomas rebranding changes are now active.
echo ============================================================================
echo.

exit /b 0
