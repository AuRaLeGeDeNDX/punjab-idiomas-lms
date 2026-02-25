<?php
/**
 * Test PDF Streaming
 * 
 * This script tests the PDF streaming functionality by:
 * 1. Generating a signed URL for content ID 7
 * 2. Making a request to that URL
 * 3. Checking the response
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PDF Streaming Test ===\n\n";

// Get content
$contentId = $argv[1] ?? 7;
$content = \App\Models\Content::find($contentId);

if (!$content) {
    echo "❌ Content not found: {$contentId}\n";
    exit(1);
}

echo "Content ID: {$content->id}\n";
echo "Title: {$content->title}\n";
echo "Type: {$content->type}\n";
echo "File Path: {$content->file_path}\n";
echo "Storage Disk: {$content->storage_disk}\n\n";

// Check if file exists
$disk = \Illuminate\Support\Facades\Storage::disk($content->storage_disk ?? 'protected');
$fileExists = $disk->exists($content->file_path);

echo "File exists on disk: " . ($fileExists ? "✅ YES" : "❌ NO") . "\n";

if ($fileExists) {
    $path = $disk->path($content->file_path);
    $fileSize = filesize($path);
    echo "File size: " . number_format($fileSize) . " bytes (" . round($fileSize / 1024 / 1024, 2) . " MB)\n";
    
    // Check if it's a valid PDF
    $handle = fopen($path, 'rb');
    $header = fread($handle, 8);
    fclose($handle);
    
    $isPdf = str_starts_with($header, '%PDF');
    echo "Valid PDF header: " . ($isPdf ? "✅ YES" : "❌ NO") . "\n";
    echo "Header: " . bin2hex($header) . "\n";
}

echo "\n";

// Generate signed URL
$service = app(\App\Services\SecurePdfService::class);
$signedUrl = $service->generateSecureUrl($content, 10);

echo "Signed URL generated:\n";
echo $signedUrl . "\n\n";

// Parse URL
$urlParts = parse_url($signedUrl);
parse_str($urlParts['query'] ?? '', $queryParams);

echo "URL Components:\n";
echo "- Path: {$urlParts['path']}\n";
echo "- Expires: " . ($queryParams['expires'] ?? 'N/A') . "\n";
echo "- Signature: " . (isset($queryParams['signature']) ? substr($queryParams['signature'], 0, 16) . '...' : 'N/A') . "\n";

if (isset($queryParams['expires'])) {
    $expiresAt = \Carbon\Carbon::createFromTimestamp($queryParams['expires']);
    $now = \Carbon\Carbon::now();
    $diff = $now->diffInMinutes($expiresAt, false);
    
    echo "- Expires at: " . $expiresAt->toDateTimeString() . "\n";
    echo "- Current time: " . $now->toDateTimeString() . "\n";
    echo "- Time until expiry: {$diff} minutes\n";
    echo "- URL is: " . ($diff > 0 ? "✅ VALID" : "❌ EXPIRED") . "\n";
}

echo "\n";

// Test the URL with a simulated request
echo "Testing URL access...\n";

try {
    // Create a request
    $request = \Illuminate\Http\Request::create($signedUrl, 'GET');
    
    // Check if signature is valid
    if ($request->hasValidSignature()) {
        echo "✅ Signature is VALID\n";
    } else {
        echo "❌ Signature is INVALID\n";
    }
    
} catch (\Exception $e) {
    echo "❌ Error testing URL: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
