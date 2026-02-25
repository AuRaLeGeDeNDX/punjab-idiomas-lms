<?php
/**
 * Branding Assets Verification Script
 * 
 * This script checks if all required Punjab Idiomas branding assets
 * are present and accessible in the correct locations.
 * 
 * Usage: php verify_branding_assets.php
 */

echo "===========================================\n";
echo "Punjab Idiomas Branding Assets Verification\n";
echo "===========================================\n\n";

// Define required assets
$requiredAssets = [
    'Logo Files' => [
        'public/images/punjab-idiomas-logo.png' => 'Main logo (200x60px approx)',
        'public/images/punjab-idiomas-logo-mobile.png' => 'Mobile logo (40x40px)',
        'public/images/punjab-idiomas-logo-small.png' => 'Small logo (32x32px)',
    ],
    'Favicon Files' => [
        'public/favicon.ico' => 'Main favicon (32x32px ICO)',
        'public/favicons/favicon-16x16.png' => 'Favicon 16x16',
        'public/favicons/favicon-32x32.png' => 'Favicon 32x32',
        'public/favicons/favicon-48x48.png' => 'Favicon 48x48',
    ],
];

$allPresent = true;
$missingFiles = [];
$presentFiles = [];

// Check each asset
foreach ($requiredAssets as $category => $files) {
    echo "Checking {$category}:\n";
    echo str_repeat('-', 50) . "\n";
    
    foreach ($files as $path => $description) {
        $fullPath = __DIR__ . '/' . $path;
        $exists = file_exists($fullPath);
        
        if ($exists) {
            $size = filesize($fullPath);
            $sizeKB = round($size / 1024, 2);
            echo "  ✓ {$path}\n";
            echo "    Description: {$description}\n";
            echo "    Size: {$sizeKB} KB\n";
            $presentFiles[] = $path;
        } else {
            echo "  ✗ {$path}\n";
            echo "    Description: {$description}\n";
            echo "    Status: MISSING\n";
            $allPresent = false;
            $missingFiles[] = $path;
        }
        echo "\n";
    }
}

// Summary
echo "===========================================\n";
echo "Summary\n";
echo "===========================================\n";
echo "Total required assets: " . array_sum(array_map('count', $requiredAssets)) . "\n";
echo "Present: " . count($presentFiles) . "\n";
echo "Missing: " . count($missingFiles) . "\n\n";

if ($allPresent) {
    echo "✓ SUCCESS: All branding assets are present!\n\n";
    echo "Next steps:\n";
    echo "1. Verify assets are accessible via browser\n";
    echo "2. Clear Laravel caches: php artisan cache:clear\n";
    echo "3. Test logo display in the application\n";
    echo "4. Test favicon display in browser tabs\n";
    exit(0);
} else {
    echo "✗ WARNING: Some branding assets are missing!\n\n";
    echo "Missing files:\n";
    foreach ($missingFiles as $file) {
        echo "  - {$file}\n";
    }
    echo "\nAction required:\n";
    echo "1. Review BRANDING_ASSETS_SETUP_GUIDE.md\n";
    echo "2. Prepare logo and favicon files\n";
    echo "3. Upload files to the correct locations\n";
    echo "4. Run this script again to verify\n\n";
    echo "For detailed instructions, see:\n";
    echo "  - public/images/README_BRANDING_ASSETS.md\n";
    echo "  - public/favicons/README_FAVICON_ASSETS.md\n";
    exit(1);
}
