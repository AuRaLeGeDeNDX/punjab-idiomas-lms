<?php
/**
 * Quick PDF Header Check
 * Checks if the PDF file has valid header and footer
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Content;
use Illuminate\Support\Facades\Storage;

$contentId = $argv[1] ?? 7;

$content = Content::find($contentId);
if (!$content) {
    echo "Content not found!\n";
    exit(1);
}

$disk = Storage::disk($content->storage_disk ?? 'public');
$path = $disk->path($content->file_path);

if (!file_exists($path)) {
    echo "File not found: {$path}\n";
    exit(1);
}

$fileSize = filesize($path);
echo "File: {$path}\n";
echo "Size: " . number_format($fileSize) . " bytes (" . round($fileSize/1024/1024, 2) . " MB)\n\n";

// Read first 100 bytes
$handle = fopen($path, 'rb');
$header = fread($handle, 100);
fclose($handle);

echo "First 100 bytes (hex):\n";
echo bin2hex($header) . "\n\n";

echo "First 100 bytes (text):\n";
echo $header . "\n\n";

// Check PDF header
if (substr($header, 0, 5) === '%PDF-') {
    echo "✓ Valid PDF header found\n";
    preg_match('/%PDF-(\d+\.\d+)/', $header, $matches);
    echo "  PDF Version: " . ($matches[1] ?? 'unknown') . "\n";
} else {
    echo "❌ INVALID PDF HEADER!\n";
    echo "  Expected: %PDF-\n";
    echo "  Got: " . substr($header, 0, 10) . "\n";
}

// Read last 100 bytes
$handle = fopen($path, 'rb');
fseek($handle, -100, SEEK_END);
$footer = fread($handle, 100);
fclose($handle);

echo "\nLast 100 bytes (text):\n";
echo $footer . "\n\n";

// Check PDF footer
if (strpos($footer, '%%EOF') !== false) {
    echo "✓ Valid PDF footer (%%EOF) found\n";
} else {
    echo "⚠️  PDF footer (%%EOF) not found in last 100 bytes\n";
}

// Check for HTML/JSON content
if (strpos($header, '<html') !== false || strpos($header, '<!DOCTYPE') !== false) {
    echo "\n❌ CRITICAL: File contains HTML!\n";
    exit(1);
}

if (strpos($header, '{') === 0) {
    echo "\n❌ CRITICAL: File contains JSON!\n";
    exit(1);
}

echo "\n✓ File appears to be a valid PDF\n";
