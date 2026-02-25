<?php
/**
 * Test PDF iframe headers
 * 
 * This script verifies that:
 * 1. SecureMediaController sets X-Frame-Options: SAMEORIGIN for PDFs
 * 2. PerformanceOptimizationMiddleware does NOT override it
 * 
 * Run: php artisan serve
 * Then visit: http://127.0.0.1:8000/test_pdf_iframe_headers.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "<h1>PDF iframe Header Test</h1>";
echo "<p>Testing X-Frame-Options header for PDF preview...</p>";

// Find a PDF content block
$content = \App\Models\Content::where('type', 'pdf')->first();

if (!$content) {
    echo "<p style='color: red;'>❌ No PDF content found in database</p>";
    exit;
}

echo "<h2>Test Content</h2>";
echo "<ul>";
echo "<li><strong>Content ID:</strong> {$content->id}</li>";
echo "<li><strong>Title:</strong> {$content->title}</li>";
echo "<li><strong>File Path:</strong> {$content->file_path}</li>";
echo "<li><strong>Storage Disk:</strong> " . ($content->storage_disk ?? 'not set') . "</li>";
echo "</ul>";

// Generate secure URL
try {
    $url = $content->getSecurePdfUrl(60);
    echo "<h2>Generated URL</h2>";
    echo "<p><code>{$url}</code></p>";
    
    // Make a request to check headers
    echo "<h2>Response Headers</h2>";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    echo "<p><strong>HTTP Status:</strong> {$httpCode}</p>";
    
    // Parse headers
    $headers = [];
    $headerLines = explode("\r\n", $response);
    foreach ($headerLines as $line) {
        if (strpos($line, ':') !== false) {
            list($key, $value) = explode(':', $line, 2);
            $headers[trim($key)] = trim($value);
        }
    }
    
    echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
    echo "<tr><th>Header</th><th>Value</th><th>Status</th></tr>";
    
    // Check X-Frame-Options
    $xFrameOptions = $headers['X-Frame-Options'] ?? 'NOT SET';
    $xFrameStatus = ($xFrameOptions === 'SAMEORIGIN') ? '✅ CORRECT' : '❌ WRONG';
    echo "<tr><td><strong>X-Frame-Options</strong></td><td>{$xFrameOptions}</td><td>{$xFrameStatus}</td></tr>";
    
    // Check Content-Type
    $contentType = $headers['Content-Type'] ?? 'NOT SET';
    $contentTypeStatus = (strpos($contentType, 'application/pdf') !== false) ? '✅ CORRECT' : '❌ WRONG';
    echo "<tr><td><strong>Content-Type</strong></td><td>{$contentType}</td><td>{$contentTypeStatus}</td></tr>";
    
    // Check Content-Disposition
    $contentDisposition = $headers['Content-Disposition'] ?? 'NOT SET';
    $dispositionStatus = (strpos($contentDisposition, 'inline') !== false) ? '✅ CORRECT' : '❌ WRONG';
    echo "<tr><td><strong>Content-Disposition</strong></td><td>{$contentDisposition}</td><td>{$dispositionStatus}</td></tr>";
    
    // Check other security headers
    $securityHeaders = [
        'X-Content-Type-Options',
        'X-XSS-Protection',
        'Referrer-Policy',
        'Cache-Control',
    ];
    
    foreach ($securityHeaders as $header) {
        $value = $headers[$header] ?? 'NOT SET';
        echo "<tr><td>{$header}</td><td>{$value}</td><td>ℹ️ INFO</td></tr>";
    }
    
    echo "</table>";
    
    // Final verdict
    echo "<h2>Final Verdict</h2>";
    if ($xFrameOptions === 'SAMEORIGIN' && strpos($contentType, 'application/pdf') !== false && strpos($contentDisposition, 'inline') !== false) {
        echo "<p style='color: green; font-size: 20px; font-weight: bold;'>✅ ALL TESTS PASSED - PDF can be displayed in iframe!</p>";
    } else {
        echo "<p style='color: red; font-size: 20px; font-weight: bold;'>❌ TESTS FAILED - PDF cannot be displayed in iframe</p>";
        echo "<ul>";
        if ($xFrameOptions !== 'SAMEORIGIN') {
            echo "<li>X-Frame-Options should be SAMEORIGIN, got: {$xFrameOptions}</li>";
        }
        if (strpos($contentType, 'application/pdf') === false) {
            echo "<li>Content-Type should be application/pdf, got: {$contentType}</li>";
        }
        if (strpos($contentDisposition, 'inline') === false) {
            echo "<li>Content-Disposition should contain 'inline', got: {$contentDisposition}</li>";
        }
        echo "</ul>";
    }
    
    // Test iframe embed
    echo "<h2>Live iframe Test</h2>";
    echo "<p>If the PDF displays below, the fix is working:</p>";
    echo "<iframe src='{$url}' width='100%' height='600px' style='border: 2px solid #ccc;'></iframe>";
    
} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Error: {$e->getMessage()}</p>";
}
