<?php
/**
 * Test PDF Validity - Check if content ID 6 PDF is valid
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Content;
use Illuminate\Support\Facades\Storage;

echo "=== PDF Validity Test for Content ID 6 ===\n\n";

// Get content
$content = Content::find(6);

if (!$content) {
    echo "❌ Content ID 6 not found\n";
    exit(1);
}

echo "Content Details:\n";
echo "- Title: {$content->title}\n";
echo "- Type: {$content->type}\n";
echo "- File Path: {$content->file_path}\n";
echo "- Storage Disk: {$content->storage_disk}\n";
echo "- File Size (DB): " . number_format($content->file_size) . " bytes\n\n";

// Check file exists
$disk = Storage::disk($content->storage_disk ?? 'protected');
$exists = $disk->exists($content->file_path);

echo "File Existence Check:\n";
echo "- Exists on disk: " . ($exists ? "✅ YES" : "❌ NO") . "\n";

if (!$exists) {
    // Try other disks
    echo "\nTrying other disks...\n";
    foreach (['protected', 'private', 'public'] as $tryDisk) {
        if (Storage::disk($tryDisk)->exists($content->file_path)) {
            echo "- Found on disk: $tryDisk ✅\n";
            $disk = Storage::disk($tryDisk);
            $exists = true;
            break;
        }
    }
}

if (!$exists) {
    echo "\n❌ File not found on any disk\n";
    exit(1);
}

// Get actual file size
$path = $disk->path($content->file_path);
$actualSize = filesize($path);

echo "- Actual file size: " . number_format($actualSize) . " bytes (" . round($actualSize / 1024 / 1024, 2) . " MB)\n";
echo "- Size matches DB: " . ($actualSize == $content->file_size ? "✅ YES" : "⚠️ NO") . "\n\n";

// Read first few bytes to check PDF header
echo "PDF Header Check:\n";
$handle = fopen($path, 'rb');
$header = fread($handle, 10);
fclose($handle);

echo "- First 10 bytes: " . bin2hex($header) . "\n";
echo "- Header string: " . substr($header, 0, 5) . "\n";
echo "- Valid PDF header: " . (substr($header, 0, 4) === '%PDF' ? "✅ YES" : "❌ NO") . "\n\n";

// Check if file is readable
echo "File Permissions:\n";
echo "- Readable: " . (is_readable($path) ? "✅ YES" : "❌ NO") . "\n";
echo "- File permissions: " . substr(sprintf('%o', fileperms($path)), -4) . "\n\n";

// Try to read last few bytes (PDF should end with %%EOF)
echo "PDF Footer Check:\n";
$handle = fopen($path, 'rb');
fseek($handle, -20, SEEK_END);
$footer = fread($handle, 20);
fclose($handle);

echo "- Last 20 bytes: " . trim($footer) . "\n";
echo "- Contains %%EOF: " . (strpos($footer, '%%EOF') !== false ? "✅ YES" : "❌ NO") . "\n\n";

// Generate signed URL and test it
echo "Signed URL Test:\n";
$service = app(\App\Services\SecurePdfService::class);
$signedUrl = $service->generateSecureUrl($content, 5);
echo "- Generated URL: $signedUrl\n";
echo "- URL is absolute: " . (strpos($signedUrl, 'http') === 0 ? "✅ YES" : "❌ NO") . "\n";
echo "- Contains signature: " . (strpos($signedUrl, 'signature=') !== false ? "✅ YES" : "❌ NO") . "\n";
echo "- Contains expires: " . (strpos($signedUrl, 'expires=') !== false ? "✅ YES" : "❌ NO") . "\n\n";

echo "=== Summary ===\n";
if (substr($header, 0, 4) === '%PDF' && strpos($footer, '%%EOF') !== false && $exists) {
    echo "✅ PDF file appears to be valid\n";
    echo "✅ File size: " . round($actualSize / 1024 / 1024, 2) . " MB\n";
    echo "\n⚠️ LARGE FILE WARNING: This is a " . round($actualSize / 1024 / 1024, 2) . " MB PDF.\n";
    echo "   PDF.js may struggle with files this large, especially on slower connections.\n";
    echo "   Consider:\n";
    echo "   1. Optimizing/compressing the PDF\n";
    echo "   2. Increasing browser memory limits\n";
    echo "   3. Testing with a smaller PDF first\n";
} else {
    echo "❌ PDF file may be corrupted or invalid\n";
}
