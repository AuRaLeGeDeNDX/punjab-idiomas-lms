<?php
/**
 * PDF 204 Error Diagnostic Script
 * 
 * This script helps diagnose why PDFs are returning 204 (No Content) errors.
 * Run with: php test_pdf_204_fix.php
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PDF 204 Error Diagnostic ===\n\n";

// Test content ID (change this to your failing PDF)
$contentId = 6;

echo "Testing Content ID: $contentId\n";
echo str_repeat("-", 50) . "\n\n";

// 1. Check if content exists
echo "1. Checking if content exists...\n";
$content = \App\Models\Content::find($contentId);

if (!$content) {
    echo "   ❌ ERROR: Content not found!\n";
    exit(1);
}

echo "   ✓ Content found: {$content->title}\n";
echo "   - Type: {$content->type}\n";
echo "   - Storage Disk: " . ($content->storage_disk ?? 'protected') . "\n";
echo "   - File Path: {$content->file_path}\n\n";

// 2. Check if file exists
echo "2. Checking if file exists in storage...\n";
$storageDisk = $content->storage_disk ?? 'protected';
$disk = \Storage::disk($storageDisk);

if (!$disk->exists($content->file_path)) {
    echo "   ❌ ERROR: File not found on disk '$storageDisk'!\n";
    echo "   Trying other disks...\n";
    
    $found = false;
    foreach (['protected', 'private', 'public'] as $tryDisk) {
        if (\Storage::disk($tryDisk)->exists($content->file_path)) {
            echo "   ✓ Found on disk: $tryDisk\n";
            $disk = \Storage::disk($tryDisk);
            $storageDisk = $tryDisk;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo "   ❌ ERROR: File not found on any disk!\n";
        exit(1);
    }
} else {
    echo "   ✓ File exists on disk '$storageDisk'\n";
}

$fileSize = $disk->size($content->file_path);
echo "   - File Size: " . number_format($fileSize) . " bytes (" . round($fileSize / 1024 / 1024, 2) . " MB)\n\n";

// 3. Check configuration
echo "3. Checking configuration...\n";
$requireSignedUrls = config('secure-pdf.require_signed_urls', true);
echo "   - Require Signed URLs: " . ($requireSignedUrls ? 'YES' : 'NO') . "\n";
echo "   - Token Expiration: " . config('secure-pdf.token_expiration_minutes', 60) . " minutes\n";
echo "   - APP_KEY set: " . (config('app.key') ? 'YES' : 'NO') . "\n\n";

// 4. Generate signed URL
echo "4. Generating signed URL...\n";
$service = app(\App\Services\SecurePdfService::class);

try {
    $url = $service->generateSecureUrl($content, 10);
    echo "   ✓ URL generated successfully\n";
    echo "   - URL: $url\n\n";
    
    // Parse URL
    $parts = parse_url($url);
    parse_str($parts['query'] ?? '', $query);
    
    echo "5. Analyzing signed URL...\n";
    echo "   - Has signature: " . (isset($query['signature']) ? 'YES' : 'NO') . "\n";
    echo "   - Has expires: " . (isset($query['expires']) ? 'YES' : 'NO') . "\n";
    
    if (isset($query['expires'])) {
        $expiresAt = (int)$query['expires'];
        $currentTime = now()->timestamp;
        $timeUntilExpiry = $expiresAt - $currentTime;
        
        echo "   - Expires at: " . date('Y-m-d H:i:s', $expiresAt) . "\n";
        echo "   - Current time: " . date('Y-m-d H:i:s', $currentTime) . "\n";
        echo "   - Time until expiry: $timeUntilExpiry seconds (" . round($timeUntilExpiry / 60, 1) . " minutes)\n";
        
        if ($timeUntilExpiry < 0) {
            echo "   ❌ WARNING: URL already expired!\n";
        } elseif ($timeUntilExpiry < 60) {
            echo "   ⚠️  WARNING: URL expires in less than 1 minute!\n";
        } else {
            echo "   ✓ URL expiration looks good\n";
        }
    }
    echo "\n";
    
} catch (\Exception $e) {
    echo "   ❌ ERROR generating URL: " . $e->getMessage() . "\n\n";
    exit(1);
}

// 6. Test signature validation
echo "6. Testing signature validation...\n";
$request = \Illuminate\Http\Request::create($url);

if ($request->hasValidSignature()) {
    echo "   ✓ Signature validation PASSED\n";
} else {
    echo "   ❌ Signature validation FAILED\n";
    echo "   This is why you're getting 204 errors!\n";
}
echo "\n";

// 7. Recommendations
echo "=== Recommendations ===\n\n";

if (!$requireSignedUrls) {
    echo "⚠️  Signed URL validation is DISABLED\n";
    echo "   This is OK for development but MUST be enabled in production.\n";
    echo "   Set SECURE_PDF_REQUIRE_SIGNED_URLS=true in .env\n\n";
}

if (!$request->hasValidSignature() && $requireSignedUrls) {
    echo "❌ ISSUE FOUND: Signature validation is failing\n\n";
    echo "Possible causes:\n";
    echo "1. Server time is not synchronized\n";
    echo "   - Check: date\n";
    echo "   - Fix: Sync server time with NTP\n\n";
    echo "2. APP_KEY changed after URL generation\n";
    echo "   - Check: APP_KEY in .env\n";
    echo "   - Fix: Don't change APP_KEY or regenerate URLs\n\n";
    echo "3. Timezone configuration mismatch\n";
    echo "   - Check: config/app.php timezone setting\n";
    echo "   - Fix: Ensure timezone matches server timezone\n\n";
    echo "TEMPORARY FIX (development only):\n";
    echo "Add to .env: SECURE_PDF_REQUIRE_SIGNED_URLS=false\n";
    echo "Then run: php artisan config:clear\n\n";
}

if ($request->hasValidSignature() || !$requireSignedUrls) {
    echo "✅ Configuration looks good!\n\n";
    echo "If you're still getting 204 errors, check:\n";
    echo "1. Laravel logs: tail -f storage/logs/laravel.log\n";
    echo "2. Browser console for JavaScript errors\n";
    echo "3. Network tab in DevTools for actual response\n\n";
}

echo "=== Test Complete ===\n";
