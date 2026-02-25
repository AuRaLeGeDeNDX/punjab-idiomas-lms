<?php
/**
 * Test PDF Stream Response
 * 
 * This script makes a direct HTTP request to the PDF stream endpoint
 * to see what response is actually being returned.
 */

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== PDF Stream Response Test ===\n\n";

// Get content and generate signed URL
$content = \App\Models\Content::find(6);
$service = app(\App\Services\SecurePdfService::class);
$url = $service->generateSecureUrl($content, 10);

echo "Testing URL: $url\n\n";

// Make HTTP request using Laravel's HTTP client
try {
    $response = \Illuminate\Support\Facades\Http::withoutVerifying()
        ->timeout(30)
        ->get($url);
    
    echo "Response Status: " . $response->status() . "\n";
    echo "Response Reason: " . $response->reason() . "\n\n";
    
    echo "Response Headers:\n";
    foreach ($response->headers() as $header => $values) {
        echo "  $header: " . implode(', ', $values) . "\n";
    }
    echo "\n";
    
    $contentType = $response->header('Content-Type');
    $contentLength = $response->header('Content-Length');
    
    echo "Content-Type: $contentType\n";
    echo "Content-Length: $contentLength\n\n";
    
    if ($response->status() === 204) {
        echo "❌ ERROR: Received 204 No Content!\n";
        echo "This is the problem - the server is returning 204 instead of the PDF.\n\n";
        
        echo "Response body (should be empty for 204):\n";
        echo $response->body() . "\n\n";
        
    } elseif ($response->status() === 200) {
        echo "✅ SUCCESS: Received 200 OK\n";
        
        $bodySize = strlen($response->body());
        echo "Body size: " . number_format($bodySize) . " bytes\n";
        
        // Check if it's actually a PDF
        $bodyStart = substr($response->body(), 0, 10);
        if (strpos($bodyStart, '%PDF') === 0) {
            echo "✅ Response body starts with %PDF - it's a valid PDF!\n";
        } else {
            echo "❌ Response body does NOT start with %PDF\n";
            echo "First 100 bytes: " . substr($response->body(), 0, 100) . "\n";
        }
        
    } else {
        echo "⚠️  Unexpected status code: " . $response->status() . "\n";
        echo "Response body:\n";
        echo substr($response->body(), 0, 500) . "\n";
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR making request: " . $e->getMessage() . "\n";
    echo "Exception class: " . get_class($e) . "\n";
}

echo "\n=== Test Complete ===\n";
