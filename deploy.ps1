# ================================================
# DEPLOYMENT SCRIPT FOR VPS (Windows/PowerShell)
# ================================================
# This script prepares the Laravel application for production deployment
# Run this on the VPS server after cloning the repository

param(
    [switch]$Force = $false
)

$ErrorActionPreference = "Stop"

# Colors helper
function Write-Success {
    param([string]$Message)
    Write-Host "✓ $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "⚠ $Message" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "✗ $Message" -ForegroundColor Red
}

Write-Host "==========================================" -ForegroundColor Cyan
Write-Host "Starting VPS Deployment Process" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Cyan

$ProjectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
Set-Location $ProjectRoot

# Step 1: Remove debug and test files
Write-Host "`nStep 1: Removing debug and test files..." -ForegroundColor Yellow

$filesToRemove = @(
    # Check files
    "check_assignment_enum.php",
    "check_legacy_file_paths.php",
    "check_pdf_content.php",
    "check_pdf_header.php",
    "check_php_limits.php",
    "check_roles_simple.php",
    "check_upload_limits.php",
    "check_user_roles.php",
    "check_video_content.php",

    # Debug files
    "debug_content_builder.html",
    "debug_content_creation.php",
    "debug_controller_logic.php",
    "debug_headers.php",
    "debug_media.php",
    "debug_module_creation.html",
    "debug_services.php",

    # Diagnose files
    "diagnose_admin_pdf_access.php",
    "diagnose_grade_loading.php",
    "diagnose_login.php",
    "diagnose_module_api_auth.php",
    "diagnose_optimize_error.php",
    "diagnose_pdf_access.php",
    "diagnose_pdf_load_error.php",
    "diagnose_pdf_load_error_detailed.php",
    "diagnose_pdf_preview.php",
    "diagnose_pdf_streaming.php",
    "diagnose_signed_url.php",
    "diagnose_student_assignments.php",
    "diagnose_student_course_route.php",
    "diagnose_student_grade_display.php",
    "diagnose_submission_file_download.php",
    "diagnose_video_issue.php",
    "diagnose_video_playback.php",
    "diagnose_video_upload.php",

    # Fix files
    "fix_pdf_storage_disk.php",
    "fix_user_role.php",

    # Publish files
    "publish_course.php",
    "publish_grade.php",

    # Test files
    "test_all_routes.php",
    "test_api_auth.php",
    "test_assignment_categorization.php",
    "test_blade_compilation.php",
    "test_debug.php",
    "test_file_upload_debug.php",
    "test_image_upload.html",
    "test_pdf_204_fix.php",
    "test_pdf_direct_load.html",
    "test_pdf_iframe_headers.php",
    "test_pdf_preview_fix.php",
    "test_pdf_signed_url.php",
    "test_pdf_stream.php",
    "test_pdf_url_generation.php",
    "test_pdf_validity.php",
    "test_pdf_viewer_access.php",
    "test_pdfjs_direct.html",
    "test_stream_response.php",
    "test_student_assignment_view.php",
    "test_video_playback.html",
    "test_video_upload_fix.php",
    "test_video_url.html",

    # Test component files
    "test-alert-component.html",
    "test-anti-download-protections.html",
    "test-button-click.html",
    "test-button-component.html",
    "test-card-component.html",
    "test-content-builder.html",
    "test-design-system.html",
    "test-enhanced-errors.html",
    "test-enhanced-file-input.html",
    "test-file-input-fix.html",
    "test-file-selection-debug.html",
    "test-file-upload-validator.html",
    "test-form-component.html",
    "test-modal-component.html",
    "test-navigation-component.html",
    "test-responsive-pdf-viewer.html",
    "test-table-component.html",
    "test-toolbar-styling.html",
    "test-upload-progress.html",

    # Verify files
    "verify_branding_assets.php",
    "verify_logo_update.php",
    "verify_mysql_migration.bat",
    "verify_rebranding_implementation.php",
    "verify_secure_media_implementation.php",
    "verify_secure_video_implementation.php",
    "verify_submission_file_download.php",

    # Other files
    "grep_log.php",
    "read_log.php",
    "read_log_tail.php",
    "tail_log.php",
    "update_php_ini.php",
    "get_signed_url.php",
    "test-vite-design-system.blade.php",
    "modern-dashboard-preview.html",
    "test_output.txt",
    "cookies.txt",

    # Batch files
    "check_migration_readiness.bat",
    "clear_cache.bat",
    "migrate_to_mysql.bat",
    "restart_server.bat",
    "setup_mysql.bat",

    # Other temporary files
    "temp_scripts.txt",
    "temp_welcome.txt",
    "setup_mysql_database.sql",
    ".phpunit.result.cache"
)

$removedCount = 0
foreach ($file in $filesToRemove) {
    $filePath = Join-Path $ProjectRoot $file
    if (Test-Path $filePath) {
        Remove-Item $filePath -Force -ErrorAction SilentlyContinue
        $removedCount++
    }
}

Write-Success "Removed $removedCount debug/test files"

# Step 2: Remove unnecessary directories
Write-Host "`nStep 2: Cleaning up unnecessary directories..." -ForegroundColor Yellow

$dirsToRemove = @(".kiro", "react-lms")
foreach ($dir in $dirsToRemove) {
    $dirPath = Join-Path $ProjectRoot $dir
    if (Test-Path $dirPath) {
        Remove-Item $dirPath -Recurse -Force -ErrorAction SilentlyContinue
    }
}

Write-Success "Unnecessary directories cleaned"

# Step 3: Environment setup
Write-Host "`nStep 3: Setting up environment..." -ForegroundColor Yellow
if (!(Test-Path ".env")) {
    if (Test-Path ".env.example") {
        Copy-Item ".env.example" ".env"
        Write-Success "Created .env from .env.example"
    }
}

# Step 4: Install composer dependencies
Write-Host "`nStep 4: Installing PHP dependencies..." -ForegroundColor Yellow
$composerPath = Get-Command composer -ErrorAction SilentlyContinue
if ($composerPath) {
    & composer install --optimize-autoloader --no-dev
    Write-Success "Composer dependencies installed"
} else {
    Write-Error "Composer not found. Please install composer first."
}

# Step 5: Generate application key
Write-Host "`nStep 5: Verifying application key..." -ForegroundColor Yellow
& php artisan key:generate
Write-Success "Application key verified"

# Step 6: Set up file permissions
Write-Host "`nStep 6: Setting file permissions..." -ForegroundColor Yellow
try {
    # For Windows, use icacls instead
    & attrib -R /S /D "$ProjectRoot\*" | Out-Null
    & icacls "$ProjectRoot\storage" /grant "%USERNAME%`:(OI)(CI)F" /T | Out-Null
    & icacls "$ProjectRoot\bootstrap\cache" /grant "%USERNAME%`:(OI)(CI)F" /T | Out-Null
    Write-Success "File permissions set"
} catch {
    Write-Warning "Could not set all permissions - you may need to do this manually"
}

# Step 7: Clear caches
Write-Host "`nStep 7: Clearing caches..." -ForegroundColor Yellow
& php artisan config:clear
& php artisan cache:clear
& php artisan view:clear
& php artisan route:clear
Write-Success "Caches cleared"

# Step 8: Build frontend assets
Write-Host "`nStep 8: Building frontend assets..." -ForegroundColor Yellow
$npmPath = Get-Command npm -ErrorAction SilentlyContinue
if ($npmPath) {
    & npm ci
    & npm run build
    Write-Success "Frontend assets built"
} else {
    Write-Error "npm not found. Please install Node.js."
}

# Step 9: Create storage link
Write-Host "`nStep 9: Creating storage link..." -ForegroundColor Yellow
& php artisan storage:link
Write-Success "Storage link created"

# Step 10: Summary
Write-Host "`n==========================================" -ForegroundColor Green
Write-Host "✓ Deployment preparation complete!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Green

Write-Host "`nNext Steps:" -ForegroundColor Yellow
Write-Host "1. Update .env file with production credentials:"
Write-Host "   - APP_ENV=production"
Write-Host "   - APP_DEBUG=false"
Write-Host "   - APP_URL=your-domain.com"
Write-Host "   - DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD"
Write-Host "   - MAIL settings"
Write-Host ""
Write-Host "2. Run migrations: php artisan migrate --force"
Write-Host ""
Write-Host "3. Configure your web server (IIS/Apache/Nginx)"
Write-Host ""
Write-Host "4. Set up HTTPS/SSL certificate"
Write-Host ""
Write-Host "5. Test the application"
Write-Host ""
