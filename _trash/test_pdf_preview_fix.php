<?php
/**
 * Quick Test for PDF Preview Fix
 * 
 * Run: php test_pdf_preview_fix.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PDF Preview Fix Test ===\n\n";

// Test 1: Check storage disk configuration
echo "Test 1: Storage Disk Configuration\n";
$protectedConfig = config('filesystems.disks.protected');

if ($protectedConfig['root'] === storage_path('app/protected')) {
    echo "✅ Protected disk root is correct: " . $protectedConfig['root'] . "\n";
} else {
    echo "❌ Protected disk root is WRONG: " . $protectedConfig['root'] . "\n";
    echo "   Expected: " . storage_path('app/protected') . "\n";
}

if (($protectedConfig['visibility'] ?? 'public') === 'private') {
    echo "✅ Protected disk visibility is correct: private\n";
} else {
    echo "❌ Protected disk visibility is WRONG: " . ($protectedConfig['visibility'] ?? 'not set') . "\n";
}

echo "\n";

// Test 2: Find a PDF content
echo "Test 2: PDF Content Check\n";
$pdfContent = \App\Models\Content::where('type', 'pdf')
    ->where('is_active', true)
    ->first();

if (!$pdfContent) {
    echo "⚠️  No PDF content found. Please upload a PDF first.\n";
    echo "\nTests completed with warnings.\n";
    exit(0);
}

echo "✅ Found PDF content (ID: {$pdfContent->id})\n";
echo "   Title: {$pdfContent->title}\n";
echo "   File Path: {$pdfContent->file_path}\n";
echo "   Storage Disk: " . ($pdfContent->storage_disk ?? 'not set') . "\n\n";

// Test 3: Check file existence
echo "Test 3: File Existence Check\n";
$disksToCheck = ['protected', 'private', 'public'];
$fileFound = false;
$foundOnDisk = null;

foreach ($disksToCheck as $disk) {
    try {
        if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($pdfContent->file_path)) {
            echo "✅ File found on '{$disk}' disk\n";
            $fileFound = true;
            $foundOnDisk = $disk;
            break;
        }
    } catch (\Exception $e) {
        echo "⚠️  Error checking '{$disk}' disk: " . $e->getMessage() . "\n";
    }
}

if (!$fileFound) {
    echo "❌ File NOT found on any disk!\n";
    echo "   This will cause 404 errors.\n";
    echo "   Run: php artisan repair:file-paths\n\n";
} else {
    echo "\n";
}

// Test 4: URL Generation
echo "Test 4: URL Generation\n";
try {
    $pdfUrl = $pdfContent->getSecurePdfUrl();
    if ($pdfUrl) {
        echo "✅ PDF URL generated successfully\n";
        echo "   URL: {$pdfUrl}\n";
    } else {
        echo "❌ PDF URL generation returned null\n";
    }
} catch (\Exception $e) {
    echo "❌ PDF URL generation failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Test 5: Route Check
echo "Test 5: Route Registration\n";
try {
    $route = route('secure.pdf.stream', ['content' => $pdfContent->id]);
    echo "✅ Route 'secure.pdf.stream' is registered\n";
    echo "   URL: {$route}\n";
} catch (\Exception $e) {
    echo "❌ Route 'secure.pdf.stream' NOT registered\n";
    echo "   Error: " . $e->getMessage() . "\n";
}

echo "\n";

// Summary
echo "=== Test Summary ===\n";
$allPassed = true;

if ($protectedConfig['root'] !== storage_path('app/protected')) {
    echo "❌ Storage disk configuration needs fixing\n";
    $allPassed = false;
}

if (!$fileFound) {
    echo "❌ PDF file not found - run file repair\n";
    $allPassed = false;
}

if ($allPassed && $pdfContent) {
    echo "✅ All tests passed!\n";
    echo "\nNext steps:\n";
    echo "1. Refresh your browser (Ctrl+Shift+R)\n";
    echo "2. Navigate to a subpage with PDF content\n";
    echo "3. PDF should display in iframe\n";
    echo "\nIf PDF still doesn't show:\n";
    echo "- Check browser console (F12) for errors\n";
    echo "- Check Network tab for 404/403 errors\n";
    echo "- Verify you're logged in as a student enrolled in the course\n";
} else {
    echo "\n⚠️  Some tests failed. Fix the issues above before testing in browser.\n";
}

echo "\n=== Test Complete ===\n";
