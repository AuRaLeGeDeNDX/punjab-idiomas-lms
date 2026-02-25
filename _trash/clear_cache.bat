@echo off
echo Clearing Laravel caches...
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
echo.
echo All caches cleared successfully!
echo Please try submitting the assignment again.
pause
