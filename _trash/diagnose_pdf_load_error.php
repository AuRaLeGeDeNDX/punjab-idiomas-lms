<?php
/**
 * Diagnose PDF Loading Error
 * 
 * This script checks why the PDF isn't loading in the viewer
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Content;
use App\Services\SecurePdfService;
use Illuminate\Support\Facades\Storage;

echo "=== PDF Loading Diagnostic ===\n\n";

// Get content ID 7
$content = Content::find(7);

if (!$content) {
    echo "❌ ERROR: Content ID 7 not found!\n";
    exit(1);
}

echo "✓ Content found: {$content->title}\n";
echo "  Type: {$content->type}\n";
echo "  File path: {$content->file_path}\n";
echo "  Storage disk: {$content->storage_disk}\n";
echo "  File size: " . number_format($content->file_size) . " bytes\n\n";

// Check if file exists
$disk = Storage::disk($content->storage_disk ?? 'protected');
$fileExists = $disk->exists($content->file_path);

echo "File existence check:\n";
echo "  Disk: {$content->storage_disk}\n";
echo "  Path: {$content->file_path}\n";
echo "  Exists: " . ($fileExists ? "✓ YES" : "❌ NO") . "\n\n";

if (!$fileExists) {
    echo "Checking other disks...\n";
    $disks = ['protected', 'private', 'public'];
    foreach ($disks as $diskName) {
        $testDisk = Storage::disk($diskName);
        if ($testDisk->exists($content->file_path)) {
            echo "  ✓ Found on disk: $diskName\n";
        }
    }
    echo "\n";
}

// Test signed URL generation
echo "Testing signed URL generation:\n";
try {
    $pdfService = app(SecurePdfService::class);
    $signedUrl = $pdfService->generateSecureUrl($content, 5);
    echo "  ✓ Signed URL generated\n";
    echo "  URL: $signedUrl\n\n";
    
    // Parse the URL
    $urlParts = parse_url($signedUrl);
    echo "  URL components:\n";
    echo "    Scheme: {$urlParts['scheme']}\n";
    echo "    Host: {$urlParts['host']}\n";
    echo "    Path: {$urlParts['path']}\n";
    if (isset($urlParts['query'])) {
        echo "    Query: {$urlParts['query']}\n";
    }
    echo "\n";
    
} catch (\Exception $e) {
    echo "  ❌ ERROR: " . $e->getMessage() . "\n\n";
}

// Check CORS and access
echo "Checking stream route:\n";
$streamRoute = route('secure.pdf.stream', ['content' => $content->id]);
echo "  Stream URL: $streamRoute\n";
echo "  Expected to work with signed URL parameters\n\n";

// Check if PDF is valid
if ($fileExists) {
    echo "Checking PDF file validity:\n";
    try {
        $fullPath = $disk->path($content->file_path);
        echo "  Full path: $fullPath\n";
        
        if (file_exists($fullPath)) {
            $fileSize = filesize($fullPath);
            echo "  ✓ File accessible\n";
            echo "  Size: " . number_format($fileSize) . " bytes\n";
            
            // Read first few bytes to check PDF header
            $handle = fopen($fullPath, 'rb');
            $header = fread($handle, 8);
            fclose($handle);
            
            if (strpos($header, '%PDF') === 0) {
                echo "  ✓ Valid PDF header detected\n";
            } else {
                echo "  ❌ Invalid PDF header: " . bin2hex($header) . "\n";
            }
        } else {
            echo "  ❌ File not accessible at full path\n";
        }
    } catch (\Exception $e) {
        echo "  ❌ ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Diagnosis Complete ===\n";
echo "\nLikely issues:\n";
echo "1. PDF file doesn't exist at the specified path\n";
echo "2. Signed URL is malformed or expired\n";
echo "3. CORS headers blocking the request\n";
echo "4. PDF file is corrupted\n";
echo "5. Stream route not accessible\n";
