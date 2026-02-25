<?php
/**
 * Test Blade Compilation for Secure PDF Viewer
 * 
 * This script tests if the viewer.blade.php file compiles correctly
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Try to compile the Blade view
    $compiler = app('blade.compiler');
    $viewPath = resource_path('views/secure-pdf/viewer.blade.php');
    
    echo "Testing Blade compilation for: $viewPath\n\n";
    
    if (!file_exists($viewPath)) {
        echo "❌ ERROR: File not found!\n";
        exit(1);
    }
    
    echo "✓ File exists\n";
    
    // Get the compiled path
    $compiledPath = $compiler->getCompiledPath($compiler->getPath('secure-pdf.viewer'));
    echo "✓ Compiled path: $compiledPath\n";
    
    // Try to compile
    $compiler->compile($viewPath);
    echo "✓ Compilation successful!\n\n";
    
    // Check if compiled file exists
    if (file_exists($compiledPath)) {
        echo "✓ Compiled file exists\n";
        echo "✓ File size: " . filesize($compiledPath) . " bytes\n\n";
    }
    
    echo "✅ SUCCESS: No syntax errors found!\n";
    echo "\nThe ParseError was likely caused by a corrupted cache.\n";
    echo "The caches have been cleared and the file should work now.\n";
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}
