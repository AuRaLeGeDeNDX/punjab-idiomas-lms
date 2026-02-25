<?php
/**
 * PDF Preview Diagnostic Script
 * 
 * This script helps diagnose why PDFs are not displaying inline.
 * Run this from the command line: php diagnose_pdf_preview.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PDF Preview Diagnostic ===\n\n";

// Find a PDF content item
$pdfContent = \App\Models\Content::where('type', 'pdf')
    ->where('is_active', true)
    ->first();

if (!$pdfContent) {
    echo "❌ No PDF content found in database\n";
    echo "Please upload a PDF file first.\n";
    exit(1);
}

echo "✅ Found PDF content:\n";
echo "   ID: {$pdfContent->id}\n";
echo "   Title: {$pdfContent->title}\n";
echo "   File Path: {$pdfContent->file_path}\n";
echo "   Storage Disk: " . ($pdfContent->storage_disk ?? 'not set') . "\n";
echo "   MIME Type: {$pdfContent->mime_type}\n\n";

// Check if file exists
echo "=== File Existence Check ===\n";
$disksToCheck = ['protected', 'private', 'public'];
$fileFound = false;

foreach ($disksToCheck as $disk) {
    try {
        if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($pdfContent->file_path)) {
            echo "✅ File found on '{$disk}' disk\n";
            $fileSize = \Illuminate\Support\Facades\Storage::disk($disk)->size($pdfContent->file_path);
            echo "   Size: " . number_format($fileSize / 1024, 2) . " KB\n";
            $fileFound = true;
        } else {
            echo "❌ File NOT found on '{$disk}' disk\n";
        }
    } catch (\Exception $e) {
        echo "⚠️  Error checking '{$disk}' disk: " . $e->getMessage() . "\n";
    }
}

if (!$fileFound) {
    echo "\n❌ CRITICAL: File not found on any storage disk!\n";
    exit(1);
}

echo "\n=== URL Generation Test ===\n";

// Test getDisplayContent()
try {
    $displayUrl = $pdfContent->getDisplayContent();
    echo "✅ getDisplayContent() returned: {$displayUrl}\n";
} catch (\Exception $e) {
    echo "❌ getDisplayContent() failed: " . $e->getMessage() . "\n";
}

// Test getSecurePdfUrl()
try {
    $securePdfUrl = $pdfContent->getSecurePdfUrl();
    echo "✅ getSecurePdfUrl() returned: {$securePdfUrl}\n";
} catch (\Exception $e) {
    echo "❌ getSecurePdfUrl() failed: " . $e->getMessage() . "\n";
}

// Test getSignedUrl()
try {
    $signedUrl = $pdfContent->getSignedUrl();
    echo "✅ getSignedUrl() returned: " . ($signedUrl ?? 'NULL') . "\n";
} catch (\Exception $e) {
    echo "❌ getSignedUrl() failed: " . $e->getMessage() . "\n";
}

echo "\n=== Route Check ===\n";

// Check if routes are registered
$routes = [
    'secure.pdf.stream' => 'PDF streaming route',
    'secure-files.download-content' => 'Secure file download route',
];

foreach ($routes as $routeName => $description) {
    try {
        $url = route($routeName, ['content' => $pdfContent->id]);
        echo "✅ {$description} ({$routeName}): {$url}\n";
    } catch (\Exception $e) {
        echo "❌ {$description} ({$routeName}): NOT REGISTERED\n";
    }
}

echo "\n=== Storage Configuration ===\n";
$storageConfig = config('filesystems.disks');

foreach (['protected', 'private', 'public'] as $disk) {
    if (isset($storageConfig[$disk])) {
        echo "✅ '{$disk}' disk configured:\n";
        echo "   Driver: " . ($storageConfig[$disk]['driver'] ?? 'not set') . "\n";
        echo "   Root: " . ($storageConfig[$disk]['root'] ?? 'not set') . "\n";
    } else {
        echo "❌ '{$disk}' disk NOT configured\n";
    }
}

echo "\n=== Recommendations ===\n";

if (!$fileFound) {
    echo "1. File is missing - run file repair command\n";
    echo "   php artisan repair:file-paths\n\n";
}

if (empty($displayUrl)) {
    echo "2. URL generation failed - check route registration\n";
    echo "   php artisan route:list | findstr secure\n\n";
}

echo "3. Test the PDF URL in browser:\n";
if (!empty($securePdfUrl)) {
    echo "   {$securePdfUrl}\n\n";
}

echo "4. Check browser console for errors (F12)\n\n";

echo "5. Verify X-Frame-Options header allows iframe:\n";
echo "   Should be 'SAMEORIGIN' not 'DENY'\n\n";

echo "=== Diagnostic Complete ===\n";
