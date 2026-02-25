<?php

/**
 * Diagnostic script for admin settings optimize error
 * 
 * This script helps identify why the optimize command is failing
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Admin Settings Optimize Diagnostic ===\n\n";

// Check 1: Test config:cache
echo "1. Testing config:cache...\n";
try {
    Artisan::call('config:cache');
    echo "   ✓ config:cache succeeded\n";
} catch (Exception $e) {
    echo "   ✗ config:cache failed: " . $e->getMessage() . "\n";
}

// Check 2: Test route:cache
echo "\n2. Testing route:cache...\n";
try {
    Artisan::call('route:cache');
    echo "   ✓ route:cache succeeded\n";
} catch (Exception $e) {
    echo "   ✗ route:cache failed: " . $e->getMessage() . "\n";
    echo "   This is likely because routes contain closures\n";
}

// Check 3: Test view:cache
echo "\n3. Testing view:cache...\n";
try {
    Artisan::call('view:cache');
    echo "   ✓ view:cache succeeded\n";
} catch (Exception $e) {
    echo "   ✗ view:cache failed: " . $e->getMessage() . "\n";
}

// Check 4: Check for closure-based routes
echo "\n4. Checking for closure-based routes...\n";
$routesFile = file_get_contents(__DIR__ . '/routes/web.php');
if (preg_match('/Route::\w+\([\'"].*?[\'"],\s*function\s*\(/i', $routesFile)) {
    echo "   ⚠ Found closure-based routes in routes/web.php\n";
    echo "   Closure routes cannot be cached with route:cache\n";
    echo "   Consider converting closures to controller methods\n";
} else {
    echo "   ✓ No closure-based routes detected\n";
}

// Check 5: Check cache directories
echo "\n5. Checking cache directories...\n";
$cacheDirectories = [
    'bootstrap/cache' => __DIR__ . '/bootstrap/cache',
    'storage/framework/cache' => __DIR__ . '/storage/framework/cache',
    'storage/framework/views' => __DIR__ . '/storage/framework/views',
];

foreach ($cacheDirectories as $name => $path) {
    if (is_dir($path) && is_writable($path)) {
        echo "   ✓ $name is writable\n";
    } else {
        echo "   ✗ $name is not writable or doesn't exist\n";
    }
}

// Check 6: Check Laravel log for recent errors
echo "\n6. Checking recent Laravel logs...\n";
$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentLines = array_slice($lines, -50); // Last 50 lines
    
    $errorFound = false;
    foreach ($recentLines as $line) {
        if (stripos($line, 'optimize') !== false || stripos($line, 'route:cache') !== false) {
            echo "   " . trim($line) . "\n";
            $errorFound = true;
        }
    }
    
    if (!$errorFound) {
        echo "   No recent optimize-related errors found in log\n";
    }
} else {
    echo "   ✗ Log file not found\n";
}

echo "\n=== Diagnostic Complete ===\n";
echo "\nRecommendation:\n";
echo "The optimize command likely fails because routes/web.php contains closure-based routes.\n";
echo "Laravel's route:cache command cannot cache routes that use closures.\n";
echo "\nSolution: Convert closure routes to controller methods or skip route caching.\n";
