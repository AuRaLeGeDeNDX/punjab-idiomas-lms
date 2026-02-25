<?php
/**
 * Logo Update Verification Script
 * 
 * This script verifies that the Punjab Idiomas logo (PunjabIdiomas.jpg) 
 * is properly referenced in all view files.
 */

echo "\n";
echo "============================================================================\n";
echo "Punjab Idiomas Logo Update Verification\n";
echo "============================================================================\n";
echo "\n";

$passed = 0;
$failed = 0;

// Check if the logo file exists
echo "[Test 1] Checking if PunjabIdiomas.jpg exists...\n";
if (file_exists('public/images/PunjabIdiomas.jpg')) {
    echo "  ✓ Logo file found: public/images/PunjabIdiomas.jpg\n";
    
    // Get file size
    $fileSize = filesize('public/images/PunjabIdiomas.jpg');
    $fileSizeKB = round($fileSize / 1024, 2);
    echo "  ✓ File size: {$fileSizeKB} KB\n";
    
    // Check if it's a valid image
    $imageInfo = @getimagesize('public/images/PunjabIdiomas.jpg');
    if ($imageInfo) {
        echo "  ✓ Valid image file\n";
        echo "  ✓ Dimensions: {$imageInfo[0]}x{$imageInfo[1]} pixels\n";
        echo "  ✓ Type: {$imageInfo['mime']}\n";
        $passed++;
    } else {
        echo "  ✗ File exists but is not a valid image\n";
        $failed++;
    }
} else {
    echo "  ✗ Logo file NOT FOUND: public/images/PunjabIdiomas.jpg\n";
    $failed++;
}

echo "\n";

// Check main layout file
echo "[Test 2] Checking main layout file (resources/views/layouts/app.blade.php)...\n";
if (file_exists('resources/views/layouts/app.blade.php')) {
    $layoutContent = file_get_contents('resources/views/layouts/app.blade.php');
    
    if (strpos($layoutContent, "asset('images/PunjabIdiomas.jpg')") !== false) {
        echo "  ✓ Layout file references PunjabIdiomas.jpg\n";
        $passed++;
    } else {
        echo "  ✗ Layout file does NOT reference PunjabIdiomas.jpg\n";
        $failed++;
    }
    
    // Check if old reference still exists
    if (strpos($layoutContent, 'punjab-idiomas-logo.png') !== false) {
        echo "  ⚠ Warning: Layout still contains reference to old logo file\n";
    }
} else {
    echo "  ✗ Layout file not found\n";
    $failed++;
}

echo "\n";

// Check login page
echo "[Test 3] Checking login page (resources/views/auth/login.blade.php)...\n";
if (file_exists('resources/views/auth/login.blade.php')) {
    $loginContent = file_get_contents('resources/views/auth/login.blade.php');
    
    if (strpos($loginContent, "asset('images/PunjabIdiomas.jpg')") !== false) {
        echo "  ✓ Login page references PunjabIdiomas.jpg\n";
        $passed++;
    } else {
        echo "  ✗ Login page does NOT reference PunjabIdiomas.jpg\n";
        $failed++;
    }
    
    // Check if old reference still exists
    if (strpos($loginContent, 'punjab-idiomas-logo.png') !== false) {
        echo "  ⚠ Warning: Login page still contains reference to old logo file\n";
    }
} else {
    echo "  ✗ Login page not found\n";
    $failed++;
}

echo "\n";

// Check welcome page
echo "[Test 4] Checking welcome page (resources/views/welcome.blade.php)...\n";
if (file_exists('resources/views/welcome.blade.php')) {
    $welcomeContent = file_get_contents('resources/views/welcome.blade.php');
    
    if (strpos($welcomeContent, "asset('images/PunjabIdiomas.jpg')") !== false) {
        echo "  ✓ Welcome page references PunjabIdiomas.jpg\n";
        $passed++;
    } else {
        echo "  ✗ Welcome page does NOT reference PunjabIdiomas.jpg\n";
        $failed++;
    }
    
    // Check if old reference still exists
    if (strpos($welcomeContent, 'punjab-idiomas-logo.png') !== false) {
        echo "  ⚠ Warning: Welcome page still contains reference to old logo file\n";
    }
} else {
    echo "  ✗ Welcome page not found\n";
    $failed++;
}

echo "\n";

// Summary
echo "============================================================================\n";
echo "Verification Summary\n";
echo "============================================================================\n";
echo "\n";
echo "Tests Passed: {$passed}\n";
echo "Tests Failed: {$failed}\n";
echo "\n";

if ($failed === 0) {
    echo "✓ ALL CHECKS PASSED!\n";
    echo "\n";
    echo "Your Punjab Idiomas logo (PunjabIdiomas.jpg) is now properly configured.\n";
    echo "\n";
    echo "Next Steps:\n";
    echo "1. Start your development server: php artisan serve\n";
    echo "2. Open your browser to: http://localhost:8000\n";
    echo "3. You should see the Punjab Idiomas logo in the top-left navbar\n";
    echo "4. Check the login page: http://localhost:8000/login\n";
    echo "5. The logo should appear on the login page as well\n";
    echo "\n";
    exit(0);
} else {
    echo "✗ SOME CHECKS FAILED - Please review the errors above.\n";
    echo "\n";
    exit(1);
}
