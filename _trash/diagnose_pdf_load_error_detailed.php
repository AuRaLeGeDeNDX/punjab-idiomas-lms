<?php
/**
 * Detailed PDF Load Error Diagnostic Tool
 * 
 * This script performs comprehensive checks on PDF content to identify
 * why PDF.js fails to load the document.
 */

require __DIR__.'/vendor/autoload.php';

use Illuminate\Support\Facades\Storage;
use App\Models\Content;

// Bootstrap Laravel
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== PDF Load Error Diagnostic Tool ===\n\n";

// Get content ID from command line or use default
$contentId = $argv[1] ?? 7;

echo "Checking Content ID: {$contentId}\n";
echo str_repeat("=", 80) . "\n\n";

// Find the content
$content = Content::find($contentId);

if (!$content) {
    echo "❌ ERROR: Content with ID {$contentId} not found!\n";
    exit(1);
}

echo "✓ Content found: {$content->title}\n";
echo "  Type: {$content->type}\n";
echo "  File Path: {$content->file_path}\n";
echo "  Storage Disk: {$content->storage_disk}\n\n";

// Check if file exists
$disk = Storage::disk($content->storage_disk ?? 'protected');
$filePath = $content->file_path;

if (!$disk->exists($filePath)) {
    echo "❌ ERROR: File does not exist on disk '{$content->storage_disk}'!\n";
    echo "  Trying other disks...\n";
    
    $disksToTry = ['protected', 'private', 'public'];
    $found = false;
    
    foreach ($disksToTry as $tryDisk) {
        if (Storage::disk($tryDisk)->exists($filePath)) {
            echo "  ✓ Found on disk: {$tryDisk}\n";
            $disk = Storage::disk($tryDisk);
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo "  ❌ File not found on any disk!\n";
        exit(1);
    }
}

$fullPath = $disk->path($filePath);
echo "✓ File exists at: {$fullPath}\n\n";

// Get file info
$fileSize = filesize($fullPath);
$fileSizeMB = round($fileSize / 1024 / 1024, 2);

echo "FILE INFORMATION:\n";
echo "  Size: {$fileSize} bytes ({$fileSizeMB} MB)\n";
echo "  Readable: " . (is_readable($fullPath) ? "Yes" : "No") . "\n";
echo "  MIME Type: " . mime_content_type($fullPath) . "\n\n";

// Read first 1024 bytes to check PDF header
$handle = fopen($fullPath, 'rb');
$header = fread($handle, 1024);
fclose($handle);

echo "PDF HEADER CHECK:\n";
$pdfMagic = substr($header, 0, 5);
echo "  First 5 bytes: " . bin2hex($pdfMagic) . " (" . $pdfMagic . ")\n";

if (strpos($header, '%PDF-') === 0) {
    echo "  ✓ Valid PDF header found\n";
    
    // Extract PDF version
    preg_match('/%PDF-(\d+\.\d+)/', $header, $matches);
    if (isset($matches[1])) {
        echo "  PDF Version: {$matches[1]}\n";
    }
} else {
    echo "  ❌ INVALID PDF HEADER!\n";
    echo "  Expected: %PDF-\n";
    echo "  Got: " . substr($header, 0, 20) . "\n";
    echo "\n  This is NOT a valid PDF file!\n";
    exit(1);
}

// Check for PDF trailer
$handle = fopen($fullPath, 'rb');
fseek($handle, -1024, SEEK_END);
$trailer = fread($handle, 1024);
fclose($handle);

echo "\nPDF TRAILER CHECK:\n";
if (strpos($trailer, '%%EOF') !== false) {
    echo "  ✓ Valid PDF trailer (%%EOF) found\n";
} else {
    echo "  ⚠️  WARNING: PDF trailer (%%EOF) not found in last 1024 bytes\n";
    echo "  This may indicate a truncated or corrupted PDF\n";
}

// Check for common PDF corruption indicators
echo "\nCORRUPTION CHECKS:\n";

// Check for HTML content (common issue when server returns error page)
if (strpos($header, '<html') !== false || strpos($header, '<!DOCTYPE') !== false) {
    echo "  ❌ CRITICAL: File contains HTML content!\n";
    echo "  The server is returning an HTML error page instead of PDF data.\n";
    echo "  First 200 bytes:\n";
    echo "  " . substr($header, 0, 200) . "\n";
    exit(1);
}

// Check for JSON content
if (strpos($header, '{') === 0 || strpos($header, '[') === 0) {
    echo "  ❌ CRITICAL: File contains JSON content!\n";
    echo "  The server is returning JSON instead of PDF data.\n";
    echo "  Content: " . substr($header, 0, 200) . "\n";
    exit(1);
}

// Check for null bytes (corruption indicator)
$nullCount = substr_count($header, "\0");
if ($nullCount > 100) {
    echo "  ⚠️  WARNING: High number of null bytes ({$nullCount}) in header\n";
    echo "  This may indicate corruption\n";
} else {
    echo "  ✓ No excessive null bytes\n";
}

// Check file size limits
echo "\nFILE SIZE ANALYSIS:\n";
if ($fileSizeMB > 50) {
    echo "  ⚠️  WARNING: File is very large ({$fileSizeMB} MB)\n";
    echo "  PDF.js may struggle with files over 50MB\n";
    echo "  Recommendation: Compress or split the PDF\n";
} elseif ($fileSizeMB > 20) {
    echo "  ⚠️  CAUTION: File is large ({$fileSizeMB} MB)\n";
    echo "  PDF.js may be slow to load this file\n";
    echo "  Consider enabling progressive loading\n";
} else {
    echo "  ✓ File size is reasonable ({$fileSizeMB} MB)\n";
}

// Try to parse PDF structure
echo "\nPDF STRUCTURE ANALYSIS:\n";

// Count pages (rough estimate)
$pageCount = substr_count(file_get_contents($fullPath), '/Type /Page');
echo "  Estimated pages: ~{$pageCount}\n";

// Check for encryption
if (strpos($header, '/Encrypt') !== false) {
    echo "  ⚠️  WARNING: PDF appears to be encrypted\n";
    echo "  PDF.js may not be able to open encrypted PDFs\n";
} else {
    echo "  ✓ PDF does not appear to be encrypted\n";
}

// Check for linearization (fast web view)
if (strpos($header, '/Linearized') !== false) {
    echo "  ✓ PDF is linearized (optimized for web viewing)\n";
} else {
    echo "  ℹ️  PDF is not linearized (may be slower to load)\n";
}

// Generate test URL
echo "\n" . str_repeat("=", 80) . "\n";
echo "TESTING RECOMMENDATIONS:\n\n";

echo "1. Test PDF with online validator:\n";
echo "   https://www.pdf-online.com/osa/validate.aspx\n\n";

echo "2. Test direct file access:\n";
echo "   File path: {$fullPath}\n";
echo "   Try opening directly in browser or PDF reader\n\n";

echo "3. Generate signed URL and test:\n";
$app = app();
$pdfService = $app->make(\App\Services\SecurePdfService::class);
$signedUrl = $pdfService->generateSecureUrl($content, 10);
echo "   Signed URL: {$signedUrl}\n";
echo "   Test in browser: curl -I \"{$signedUrl}\"\n\n";

echo "4. Check Content-Type header:\n";
echo "   Expected: application/pdf\n";
echo "   Test: curl -I \"{$signedUrl}\" | grep Content-Type\n\n";

// Summary
echo str_repeat("=", 80) . "\n";
echo "DIAGNOSTIC SUMMARY:\n\n";

$issues = [];
$warnings = [];

if (strpos($header, '%PDF-') !== 0) {
    $issues[] = "Invalid PDF header - file is not a valid PDF";
}

if (strpos($trailer, '%%EOF') === false) {
    $warnings[] = "PDF trailer not found - may be truncated";
}

if ($fileSizeMB > 50) {
    $warnings[] = "File size exceeds 50MB - may cause browser memory issues";
}

if (strpos($header, '/Encrypt') !== false) {
    $warnings[] = "PDF is encrypted - PDF.js may not support it";
}

if (empty($issues) && empty($warnings)) {
    echo "✓ No critical issues found!\n";
    echo "  The PDF file appears to be valid.\n";
    echo "  The issue may be with:\n";
    echo "  - Server response headers (Content-Type)\n";
    echo "  - CORS configuration\n";
    echo "  - PDF.js configuration\n";
    echo "  - Browser memory limits\n";
} else {
    if (!empty($issues)) {
        echo "❌ CRITICAL ISSUES:\n";
        foreach ($issues as $issue) {
            echo "  - {$issue}\n";
        }
        echo "\n";
    }
    
    if (!empty($warnings)) {
        echo "⚠️  WARNINGS:\n";
        foreach ($warnings as $warning) {
            echo "  - {$warning}\n";
        }
    }
}

echo "\n" . str_repeat("=", 80) . "\n";
echo "Diagnostic complete!\n";
