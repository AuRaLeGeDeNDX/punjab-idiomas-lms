<?php
/**
 * Punjab Idiomas Rebranding Verification Script
 * 
 * This script performs automated checks to verify the rebranding implementation.
 * It checks for asset files, configuration values, and scans for old branding text.
 * 
 * Usage: php verify_rebranding_implementation.php
 */

echo "\n";
echo "============================================================================\n";
echo "Punjab Idiomas Rebranding Verification Script\n";
echo "============================================================================\n";
echo "\n";

$errors = [];
$warnings = [];
$passed = 0;
$failed = 0;

// ============================================================================
// Test 1: Check Asset Files Exist
// ============================================================================
echo "[Test 1] Checking asset files...\n";

$requiredAssets = [
    'public/images/punjab-idiomas-logo.png' => 'Main logo',
    'public/images/punjab-idiomas-logo-mobile.png' => 'Mobile logo',
    'public/images/punjab-idiomas-logo-small.png' => 'Small logo',
    'public/favicon.ico' => 'Main favicon',
    'public/favicons/favicon-16x16.png' => 'Favicon 16x16',
    'public/favicons/favicon-32x32.png' => 'Favicon 32x32',
    'public/favicons/favicon-48x48.png' => 'Favicon 48x48',
];

foreach ($requiredAssets as $path => $description) {
    if (file_exists($path)) {
        echo "  ✓ {$description}: {$path}\n";
        $passed++;
    } else {
        echo "  ✗ {$description}: {$path} NOT FOUND\n";
        $errors[] = "Missing asset file: {$path}";
        $failed++;
    }
}

echo "\n";

// ============================================================================
// Test 2: Check Configuration Files
// ============================================================================
echo "[Test 2] Checking configuration files...\n";

// Check .env file
if (file_exists('.env')) {
    $envContent = file_get_contents('.env');
    if (strpos($envContent, 'APP_NAME="Punjab Idiomas"') !== false || 
        strpos($envContent, "APP_NAME='Punjab Idiomas'") !== false ||
        strpos($envContent, 'APP_NAME=Punjab Idiomas') !== false) {
        echo "  ✓ .env file contains APP_NAME=\"Punjab Idiomas\"\n";
        $passed++;
    } else {
        echo "  ✗ .env file does not contain correct APP_NAME\n";
        $errors[] = ".env file missing or incorrect APP_NAME";
        $failed++;
    }
} else {
    echo "  ✗ .env file not found\n";
    $errors[] = ".env file not found";
    $failed++;
}

// Check config/app.php
if (file_exists('config/app.php')) {
    $configContent = file_get_contents('config/app.php');
    if (strpos($configContent, "'Punjab Idiomas'") !== false || 
        strpos($configContent, '"Punjab Idiomas"') !== false) {
        echo "  ✓ config/app.php contains 'Punjab Idiomas'\n";
        $passed++;
    } else {
        echo "  ✗ config/app.php does not contain 'Punjab Idiomas'\n";
        $errors[] = "config/app.php missing 'Punjab Idiomas'";
        $failed++;
    }
} else {
    echo "  ✗ config/app.php not found\n";
    $errors[] = "config/app.php not found";
    $failed++;
}

echo "\n";

// ============================================================================
// Test 3: Check Main Layout File
// ============================================================================
echo "[Test 3] Checking main layout file...\n";

$layoutFile = 'resources/views/layouts/app.blade.php';
if (file_exists($layoutFile)) {
    $layoutContent = file_get_contents($layoutFile);
    
    // Check for logo image
    if (strpos($layoutContent, 'punjab-idiomas-logo') !== false) {
        echo "  ✓ Layout contains Punjab Idiomas logo reference\n";
        $passed++;
    } else {
        echo "  ✗ Layout does not contain Punjab Idiomas logo reference\n";
        $errors[] = "Layout missing Punjab Idiomas logo";
        $failed++;
    }
    
    // Check for old graduation cap icon
    if (strpos($layoutContent, 'fa-graduation-cap') !== false) {
        echo "  ⚠ Layout still contains fa-graduation-cap icon\n";
        $warnings[] = "Layout contains old graduation cap icon";
    } else {
        echo "  ✓ Layout does not contain old graduation cap icon\n";
        $passed++;
    }
    
    // Check for old branding text
    if (strpos($layoutContent, 'Institute LMS') !== false) {
        echo "  ✗ Layout still contains 'Institute LMS' text\n";
        $errors[] = "Layout contains old 'Institute LMS' text";
        $failed++;
    } else {
        echo "  ✓ Layout does not contain 'Institute LMS' text\n";
        $passed++;
    }
} else {
    echo "  ✗ Layout file not found: {$layoutFile}\n";
    $errors[] = "Layout file not found";
    $failed++;
}

echo "\n";

// ============================================================================
// Test 4: Check Login Page
// ============================================================================
echo "[Test 4] Checking login page...\n";

$loginFile = 'resources/views/auth/login.blade.php';
if (file_exists($loginFile)) {
    $loginContent = file_get_contents($loginFile);
    
    // Check for Punjab Idiomas branding
    if (strpos($loginContent, 'Punjab Idiomas') !== false) {
        echo "  ✓ Login page contains 'Punjab Idiomas' text\n";
        $passed++;
    } else {
        echo "  ✗ Login page does not contain 'Punjab Idiomas' text\n";
        $errors[] = "Login page missing 'Punjab Idiomas' text";
        $failed++;
    }
    
    // Check for old branding text
    if (strpos($loginContent, 'Institute LMS') !== false) {
        echo "  ✗ Login page still contains 'Institute LMS' text\n";
        $errors[] = "Login page contains old 'Institute LMS' text";
        $failed++;
    } else {
        echo "  ✓ Login page does not contain 'Institute LMS' text\n";
        $passed++;
    }
} else {
    echo "  ✗ Login file not found: {$loginFile}\n";
    $errors[] = "Login file not found";
    $failed++;
}

echo "\n";

// ============================================================================
// Test 5: Check Welcome Page
// ============================================================================
echo "[Test 5] Checking welcome page...\n";

$welcomeFile = 'resources/views/welcome.blade.php';
if (file_exists($welcomeFile)) {
    $welcomeContent = file_get_contents($welcomeFile);
    
    // Check for old branding text
    if (strpos($welcomeContent, 'Institute LMS') !== false) {
        echo "  ✗ Welcome page still contains 'Institute LMS' text\n";
        $errors[] = "Welcome page contains old 'Institute LMS' text";
        $failed++;
    } else {
        echo "  ✓ Welcome page does not contain 'Institute LMS' text\n";
        $passed++;
    }
} else {
    echo "  ✗ Welcome file not found: {$welcomeFile}\n";
    $errors[] = "Welcome file not found";
    $failed++;
}

echo "\n";

// ============================================================================
// Test 6: Scan All Blade Files for Old Branding
// ============================================================================
echo "[Test 6] Scanning all Blade files for old branding...\n";

$bladeFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator('resources/views', RecursiveDirectoryIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $bladeFiles[] = $file->getPathname();
    }
}

$filesWithOldBranding = [];
foreach ($bladeFiles as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'Institute LMS') !== false) {
        $filesWithOldBranding[] = $file;
    }
}

if (empty($filesWithOldBranding)) {
    echo "  ✓ No Blade files contain 'Institute LMS' text\n";
    $passed++;
} else {
    echo "  ✗ Found " . count($filesWithOldBranding) . " Blade files with 'Institute LMS' text:\n";
    foreach ($filesWithOldBranding as $file) {
        echo "    - {$file}\n";
    }
    $errors[] = "Some Blade files still contain 'Institute LMS' text";
    $failed++;
}

echo "\n";

// ============================================================================
// Test 7: Check Cache Clearing Scripts
// ============================================================================
echo "[Test 7] Checking cache clearing scripts...\n";

$cacheScripts = [
    'scripts/clear-rebranding-cache.bat' => 'Windows cache clearing script',
    'scripts/clear-rebranding-cache.sh' => 'Linux cache clearing script',
];

foreach ($cacheScripts as $path => $description) {
    if (file_exists($path)) {
        echo "  ✓ {$description}: {$path}\n";
        $passed++;
    } else {
        echo "  ✗ {$description}: {$path} NOT FOUND\n";
        $errors[] = "Missing cache script: {$path}";
        $failed++;
    }
}

echo "\n";

// ============================================================================
// Summary
// ============================================================================
echo "============================================================================\n";
echo "Verification Summary\n";
echo "============================================================================\n";
echo "\n";
echo "Tests Passed: {$passed}\n";
echo "Tests Failed: {$failed}\n";
echo "Warnings: " . count($warnings) . "\n";
echo "\n";

if (!empty($errors)) {
    echo "ERRORS FOUND:\n";
    foreach ($errors as $i => $error) {
        echo "  " . ($i + 1) . ". {$error}\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "WARNINGS:\n";
    foreach ($warnings as $i => $warning) {
        echo "  " . ($i + 1) . ". {$warning}\n";
    }
    echo "\n";
}

if ($failed === 0) {
    echo "✓ ALL AUTOMATED CHECKS PASSED!\n";
    echo "\n";
    echo "Next Steps:\n";
    echo "1. Run the cache clearing script: scripts/clear-rebranding-cache.bat\n";
    echo "2. Perform manual testing using MANUAL_TESTING_GUIDE_punjab_idiomas.md\n";
    echo "3. Test on multiple devices and browsers\n";
    echo "4. Verify with different user roles (Admin, Teacher, Student)\n";
    echo "\n";
    exit(0);
} else {
    echo "✗ SOME CHECKS FAILED - Please review and fix the errors above.\n";
    echo "\n";
    exit(1);
}
