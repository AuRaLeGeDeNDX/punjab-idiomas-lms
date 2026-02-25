#!/bin/bash

# ================================================
# DEPLOYMENT SCRIPT FOR VPS
# ================================================
# This script prepares the Laravel application for production deployment
# Run this on the VPS server after cloning the repository

set -e

echo "=========================================="
echo "Starting VPS Deployment Process"
echo "=========================================="

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo -e "${YELLOW}Warning: This script should ideally be run as root or with sudo${NC}"
fi

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_ROOT"

# Step 1: Remove debug and test files
echo -e "\n${YELLOW}Step 1: Removing debug and test files...${NC}"

# Remove check_*.php files
rm -f check_assignment_enum.php
rm -f check_legacy_file_paths.php
rm -f check_pdf_content.php
rm -f check_pdf_header.php
rm -f check_php_limits.php
rm -f check_roles_simple.php
rm -f check_upload_limits.php
rm -f check_user_roles.php
rm -f check_video_content.php

# Remove debug_*.php and debug_*.html files
rm -f debug_content_builder.html
rm -f debug_content_creation.php
rm -f debug_controller_logic.php
rm -f debug_headers.php
rm -f debug_media.php
rm -f debug_module_creation.html
rm -f debug_services.php

# Remove diagnose_*.php files
rm -f diagnose_admin_pdf_access.php
rm -f diagnose_grade_loading.php
rm -f diagnose_login.php
rm -f diagnose_module_api_auth.php
rm -f diagnose_optimize_error.php
rm -f diagnose_pdf_access.php
rm -f diagnose_pdf_load_error.php
rm -f diagnose_pdf_load_error_detailed.php
rm -f diagnose_pdf_preview.php
rm -f diagnose_pdf_streaming.php
rm -f diagnose_signed_url.php
rm -f diagnose_student_assignments.php
rm -f diagnose_student_course_route.php
rm -f diagnose_student_grade_display.php
rm -f diagnose_submission_file_download.php
rm -f diagnose_video_issue.php
rm -f diagnose_video_playback.php
rm -f diagnose_video_upload.php

# Remove fix_*.php files
rm -f fix_pdf_storage_disk.php
rm -f fix_user_role.php

# Remove publish_*.php files
rm -f publish_course.php
rm -f publish_grade.php

# Remove test_*.php and test_*.html files
rm -f test_all_routes.php
rm -f test_api_auth.php
rm -f test_assignment_categorization.php
rm -f test_blade_compilation.php
rm -f test_debug.php
rm -f test_file_upload_debug.php
rm -f test_image_upload.html
rm -f test_pdf_204_fix.php
rm -f test_pdf_direct_load.html
rm -f test_pdf_iframe_headers.php
rm -f test_pdf_preview_fix.php
rm -f test_pdf_signed_url.php
rm -f test_pdf_stream.php
rm -f test_pdf_url_generation.php
rm -f test_pdf_validity.php
rm -f test_pdf_viewer_access.php
rm -f test_pdfjs_direct.html
rm -f test_stream_response.php
rm -f test_student_assignment_view.php
rm -f test_video_playback.html
rm -f test_video_upload_fix.php
rm -f test_video_url.html

# Remove test-*.html files
rm -f test-alert-component.html
rm -f test-anti-download-protections.html
rm -f test-button-click.html
rm -f test-button-component.html
rm -f test-card-component.html
rm -f test-content-builder.html
rm -f test-design-system.html
rm -f test-enhanced-errors.html
rm -f test-enhanced-file-input.html
rm -f test-file-input-fix.html
rm -f test-file-selection-debug.html
rm -f test-file-upload-validator.html
rm -f test-form-component.html
rm -f test-modal-component.html
rm -f test-navigation-component.html
rm -f test-responsive-pdf-viewer.html
rm -f test-table-component.html
rm -f test-toolbar-styling.html
rm -f test-upload-progress.html

# Remove verify_*.php files
rm -f verify_branding_assets.php
rm -f verify_logo_update.php
rm -f verify_mysql_migration.bat
rm -f verify_rebranding_implementation.php
rm -f verify_secure_media_implementation.php
rm -f verify_secure_video_implementation.php
rm -f verify_submission_file_download.php

# Remove other diagnostic/temporary files
rm -f grep_log.php
rm -f read_log.php
rm -f read_log_tail.php
rm -f tail_log.php
rm -f update_php_ini.php
rm -f get_signed_url.php
rm -f test-vite-design-system.blade.php
rm -f modern-dashboard-preview.html
rm -f test_output.txt
rm -f cookies.txt

# Step 2: Remove Windows batch files
echo -e "${YELLOW}Step 2: Removing Windows batch files...${NC}"
rm -f check_migration_readiness.bat
rm -f clear_cache.bat
rm -f migrate_to_mysql.bat
rm -f restart_server.bat
rm -f setup_mysql.bat

# Step 3: Remove unnecessary directories
echo -e "${YELLOW}Step 3: Cleaning up unnecessary directories...${NC}"
rm -rf .kiro/
rm -rf react-lms/
rm -f .phpunit.result.cache
rm -f temp_*.txt
rm -f setup_mysql_database.sql

echo -e "${GREEN}✓ Debug files removed${NC}"

# Step 4: Update environment file for production
echo -e "\n${YELLOW}Step 4: Setting up environment...${NC}"
if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${GREEN}✓ Created .env from .env.example${NC}"
    fi
fi

# Step 5: Install composer dependencies
echo -e "\n${YELLOW}Step 5: Installing PHP dependencies...${NC}"
if command -v composer &> /dev/null; then
    composer install --optimize-autoloader --no-dev
    echo -e "${GREEN}✓ Composer dependencies installed${NC}"
else
    echo -e "${RED}✗ Composer not found. Please install composer first.${NC}"
fi

# Step 6: Generate application key
echo -e "\n${YELLOW}Step 6: Generating application key...${NC}"
if [ -z "$APP_KEY" ] || [ "$APP_KEY" == "base64:" ]; then
    php artisan key:generate
    echo -e "${GREEN}✓ Application key generated${NC}"
else
    echo -e "${GREEN}✓ Application key already exists${NC}"
fi

# Step 7: Set up file permissions
echo -e "\n${YELLOW}Step 7: Setting file permissions...${NC}"
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;
chmod -R 775 storage bootstrap/cache
chmod 640 .env
echo -e "${GREEN}✓ File permissions set${NC}"

# Step 8: Clear all caches
echo -e "\n${YELLOW}Step 8: Clearing caches...${NC}"
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo -e "${GREEN}✓ Caches cleared${NC}"

# Step 9: Cache configuration for production
echo -e "\n${YELLOW}Step 9: Caching configuration for production...${NC}"
php artisan config:cache
php artisan route:cache
echo -e "${GREEN}✓ Configuration cached${NC}"

# Step 10: Build frontend assets
echo -e "\n${YELLOW}Step 10: Building frontend assets...${NC}"
if command -v npm &> /dev/null; then
    npm ci
    npm run build
    echo -e "${GREEN}✓ Frontend assets built${NC}"
else
    echo -e "${RED}✗ npm not found. Please install Node.js.${NC}"
fi

# Step 11: Create storage link
echo -e "\n${YELLOW}Step 11: Creating storage link...${NC}"
php artisan storage:link
echo -e "${GREEN}✓ Storage link created${NC}"

# Step 12: Summary
echo -e "\n${GREEN}=========================================="
echo "✓ Deployment preparation complete!"
echo "=========================================="

echo -e "\n${YELLOW}Next Steps:${NC}"
echo "1. Update .env file with production credentials:"
echo "   - APP_ENV=production"
echo "   - APP_DEBUG=false"
echo "   - APP_URL=your-domain.com"
echo "   - DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD"
echo "   - MAIL settings"
echo ""
echo "2. Run migrations: php artisan migrate --force"
echo ""
echo "3. Configure your web server (Apache/Nginx):"
echo "   - Set document root to 'public/' directory"
echo "   - Enable mod_rewrite (Apache) or configure equivalent (Nginx)"
echo "   - Set proper headers"
echo ""
echo "4. Set up HTTPS/SSL certificate"
echo ""
echo "5. Configure log rotation and monitoring"
echo ""
echo "6. Test the application: php artisan serve"
echo ""
