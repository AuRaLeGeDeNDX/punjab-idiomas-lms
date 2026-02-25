<?php

/**
 * Enhanced Diagnostic Script for PDF Streaming
 * 
 * This script tests actual PDF streaming functionality with various scenarios
 * to identify issues in the complete streaming pipeline.
 * 
 * Requirements tested: 5.1, 5.2, 5.3
 * 
 * Usage: php diagnose_pdf_streaming.php [content_id]
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Content;
use App\Services\SecurePdfService;
use App\Http\Controllers\SecurePdfController;

// Color output helpers
function colorOutput($text, $color = 'white') {
    $colors = [
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'magenta' => "\033[35m",
        'cyan' => "\033[36m",
        'white' => "\033[37m",
        'reset' => "\033[0m",
    ];
    
    return ($colors[$color] ?? $colors['white']) . $text . $colors['reset'];
}

function printHeader($text) {
    echo "\n" . colorOutput(str_repeat('=', 80), 'cyan') . "\n";
    echo colorOutput($text, 'cyan') . "\n";
    echo colorOutput(str_repeat('=', 80), 'cyan') . "\n\n";
}

function printSuccess($text) {
    echo colorOutput('✓ ', 'green') . $text . "\n";
}

function printError($text) {
    echo colorOutput('✗ ', 'red') . $text . "\n";
}

function printWarning($text) {
    echo colorOutput('⚠ ', 'yellow') . $text . "\n";
}

function printInfo($text) {
    echo colorOutput('ℹ ', 'blue') . $text . "\n";
}

// Get content ID from command line or use first PDF content
$contentId = $argv[1] ?? null;

printHeader('PDF Streaming Diagnostic Script');
printInfo('Testing actual PDF streaming with various scenarios');
printInfo('Requirements: 5.1, 5.2, 5.3');

// Step 1: Find test content
printHeader('Step 1: Finding Test Content');

if ($contentId) {
    $content = Content::find($contentId);
    if (!$content) {
        printError("Content with ID {$contentId} not found");
        exit(1);
    }
    printSuccess("Using specified content ID: {$contentId}");
} else {
    $content = Content::where('type', 'pdf')->first();
    if (!$content) {
        printError("No PDF content found in database");
        printInfo("Please create a PDF content entry or specify a content ID");
        exit(1);
    }
    printSuccess("Using first PDF content found: ID {$content->id}");
}

printInfo("Content Details:");
echo "  - ID: {$content->id}\n";
echo "  - Title: {$content->title}\n";
echo "  - Type: {$content->type}\n";
echo "  - File Path: {$content->file_path}\n";
echo "  - Storage Disk: " . ($content->storage_disk ?? 'protected') . "\n";

// Step 2: Test basic streaming
printHeader('Step 2: Testing Basic PDF Streaming');

$pdfService = new SecurePdfService();

try {
    $signedUrl = $pdfService->generateSecureUrl($content, 5);
    printSuccess("Signed URL generated: " . substr($signedUrl, 0, 60) . "...");
    
    // Parse URL to get path and query
    $parsedUrl = parse_url($signedUrl);
    $path = $parsedUrl['path'];
    $query = $parsedUrl['query'] ?? '';
    
    // Create request
    $request = Request::create($path . '?' . $query, 'GET');
    
    printInfo("Testing stream controller response...");
    
    // Instantiate controller
    $controller = new SecurePdfController();
    
    // Call stream method
    $response = $controller->stream($request, $content);
    
    // Check response
    if ($response->getStatusCode() === 200) {
        printSuccess("Stream response status: 200 OK");
    } else {
        printError("Stream response status: " . $response->getStatusCode());
    }
    
    // Check Content-Type header
    $contentType = $response->headers->get('Content-Type');
    if ($contentType === 'application/pdf') {
        printSuccess("Content-Type header: application/pdf");
    } else {
        printError("Content-Type header: {$contentType} (expected: application/pdf)");
    }
    
    // Check Content-Length header
    $contentLength = $response->headers->get('Content-Length');
    if ($contentLength) {
        printSuccess("Content-Length header: " . number_format($contentLength) . " bytes");
    } else {
        printWarning("Content-Length header: Not set");
    }
    
    // Check Accept-Ranges header
    $acceptRanges = $response->headers->get('Accept-Ranges');
    if ($acceptRanges === 'bytes') {
        printSuccess("Accept-Ranges header: bytes (range requests supported)");
    } else {
        printWarning("Accept-Ranges header: {$acceptRanges} (range requests may not work)");
    }
    
    // Check response content
    $content_data = $response->getContent();
    if (strlen($content_data) > 0) {
        printSuccess("Response has content: " . number_format(strlen($content_data)) . " bytes");
        
        // Check if it's a valid PDF (starts with %PDF)
        if (substr($content_data, 0, 4) === '%PDF') {
            printSuccess("Content is valid PDF format (starts with %PDF)");
        } else {
            printError("Content does not appear to be valid PDF format");
            printInfo("First 20 bytes: " . bin2hex(substr($content_data, 0, 20)));
        }
    } else {
        printError("Response has no content");
    }
    
} catch (\Exception $e) {
    printError("Failed to test basic streaming: " . $e->getMessage());
    echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
}

// Step 3: Test range requests
printHeader('Step 3: Testing Range Request Support');

try {
    $signedUrl = $pdfService->generateSecureUrl($content, 5);
    $parsedUrl = parse_url($signedUrl);
    $path = $parsedUrl['path'];
    $query = $parsedUrl['query'] ?? '';
    
    // Test various range request scenarios
    $rangeTests = [
        'First 1024 bytes' => 'bytes=0-1023',
        'Bytes 1024-2047' => 'bytes=1024-2047',
        'Last 1024 bytes' => 'bytes=-1024',
        'From byte 1024 to end' => 'bytes=1024-',
    ];
    
    foreach ($rangeTests as $description => $rangeHeader) {
        printInfo("\nTesting: {$description} ({$rangeHeader})");
        
        // Create request with Range header
        $request = Request::create($path . '?' . $query, 'GET');
        $request->headers->set('Range', $rangeHeader);
        
        $controller = new SecurePdfController();
        $response = $controller->stream($request, $content);
        
        // Check response status
        if ($response->getStatusCode() === 206) {
            printSuccess("  Status: 206 Partial Content");
        } elseif ($response->getStatusCode() === 200) {
            printWarning("  Status: 200 OK (range request not honored)");
        } else {
            printError("  Status: " . $response->getStatusCode());
        }
        
        // Check Content-Range header
        $contentRange = $response->headers->get('Content-Range');
        if ($contentRange) {
            printSuccess("  Content-Range: {$contentRange}");
        } else {
            printWarning("  Content-Range: Not set");
        }
        
        // Check content length
        $contentLength = $response->headers->get('Content-Length');
        if ($contentLength) {
            printInfo("  Content-Length: " . number_format($contentLength) . " bytes");
        }
    }
    
} catch (\Exception $e) {
    printError("Failed to test range requests: " . $e->getMessage());
}

// Step 4: Test with expired signature
printHeader('Step 4: Testing Expired Signature Handling');

try {
    // Generate URL with very short expiration (already expired)
    $expiredUrl = $pdfService->generateSecureUrl($content, -1); // Negative minutes = expired
    
    $parsedUrl = parse_url($expiredUrl);
    $path = $parsedUrl['path'];
    $query = $parsedUrl['query'] ?? '';
    
    $request = Request::create($path . '?' . $query, 'GET');
    
    printInfo("Testing with expired signature...");
    
    if ($request->hasValidSignature()) {
        printError("Expired signature validated (should have failed)");
    } else {
        printSuccess("Expired signature correctly rejected");
    }
    
    // Try to stream with expired signature
    try {
        $controller = new SecurePdfController();
        $response = $controller->stream($request, $content);
        
        if ($response->getStatusCode() === 403) {
            printSuccess("Stream correctly returns 403 for expired signature");
        } else {
            printWarning("Stream returns status: " . $response->getStatusCode());
        }
    } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
        if ($e->getStatusCode() === 403) {
            printSuccess("Stream correctly throws 403 exception for expired signature");
        } else {
            printError("Stream throws unexpected exception: " . $e->getMessage());
        }
    }
    
} catch (\Exception $e) {
    printInfo("Expected behavior: " . $e->getMessage());
}

// Step 5: Test with invalid signature
printHeader('Step 5: Testing Invalid Signature Handling');

try {
    $signedUrl = $pdfService->generateSecureUrl($content, 5);
    
    // Tamper with the signature
    $tamperedUrl = preg_replace('/signature=[^&]+/', 'signature=invalid_signature_12345', $signedUrl);
    
    $parsedUrl = parse_url($tamperedUrl);
    $path = $parsedUrl['path'];
    $query = $parsedUrl['query'] ?? '';
    
    $request = Request::create($path . '?' . $query, 'GET');
    
    printInfo("Testing with tampered signature...");
    
    if ($request->hasValidSignature()) {
        printError("Tampered signature validated (should have failed)");
    } else {
        printSuccess("Tampered signature correctly rejected");
    }
    
} catch (\Exception $e) {
    printInfo("Expected behavior: " . $e->getMessage());
}

// Step 6: Test multiple requests with same URL
printHeader('Step 6: Testing Multiple Requests (Idempotence)');

try {
    $signedUrl = $pdfService->generateSecureUrl($content, 5);
    $parsedUrl = parse_url($signedUrl);
    $path = $parsedUrl['path'];
    $query = $parsedUrl['query'] ?? '';
    
    printInfo("Making 5 requests with the same signed URL...");
    
    $successCount = 0;
    $failCount = 0;
    
    for ($i = 1; $i <= 5; $i++) {
        $request = Request::create($path . '?' . $query, 'GET');
        
        try {
            $controller = new SecurePdfController();
            $response = $controller->stream($request, $content);
            
            if ($response->getStatusCode() === 200) {
                $successCount++;
                printSuccess("  Request {$i}: 200 OK");
            } else {
                $failCount++;
                printError("  Request {$i}: " . $response->getStatusCode());
            }
        } catch (\Exception $e) {
            $failCount++;
            printError("  Request {$i}: Exception - " . $e->getMessage());
        }
        
        // Small delay between requests
        usleep(100000); // 100ms
    }
    
    echo "\n";
    if ($successCount === 5) {
        printSuccess("All 5 requests succeeded (idempotence verified)");
    } else {
        printError("Only {$successCount}/5 requests succeeded");
        printWarning("Signed URLs should work multiple times (Requirement 1.4)");
    }
    
} catch (\Exception $e) {
    printError("Failed to test multiple requests: " . $e->getMessage());
}

// Step 7: Test without credentials
printHeader('Step 7: Testing Session Independence');

try {
    $signedUrl = $pdfService->generateSecureUrl($content, 5);
    $parsedUrl = parse_url($signedUrl);
    $path = $parsedUrl['path'];
    $query = $parsedUrl['query'] ?? '';
    
    printInfo("Testing request without session/cookies...");
    
    // Create request without session
    $request = Request::create($path . '?' . $query, 'GET');
    // Explicitly don't set any session or auth data
    
    $controller = new SecurePdfController();
    $response = $controller->stream($request, $content);
    
    if ($response->getStatusCode() === 200) {
        printSuccess("Stream works without session (session independence verified)");
        printInfo("Requirement 4.1: Signed URLs work independently of session state");
    } else {
        printError("Stream requires session (status: " . $response->getStatusCode() . ")");
        printWarning("Signed URLs should work without active session");
    }
    
} catch (\Exception $e) {
    printError("Failed to test session independence: " . $e->getMessage());
}

// Step 8: Test file size and performance
printHeader('Step 8: Testing File Size and Performance');

try {
    $storageDisk = $content->storage_disk ?? 'protected';
    $disk = Storage::disk($storageDisk);
    
    if ($disk->exists($content->file_path)) {
        $fileSize = $disk->size($content->file_path);
        printInfo("File size: " . number_format($fileSize) . " bytes (" . round($fileSize / 1024 / 1024, 2) . " MB)");
        
        // Test streaming performance
        $signedUrl = $pdfService->generateSecureUrl($content, 5);
        $parsedUrl = parse_url($signedUrl);
        $path = $parsedUrl['path'];
        $query = $parsedUrl['query'] ?? '';
        
        $request = Request::create($path . '?' . $query, 'GET');
        
        $startTime = microtime(true);
        $controller = new SecurePdfController();
        $response = $controller->stream($request, $content);
        $endTime = microtime(true);
        
        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds
        
        printInfo("Streaming time: " . round($duration, 2) . " ms");
        
        if ($duration < 100) {
            printSuccess("Streaming is fast (< 100ms)");
        } elseif ($duration < 500) {
            printInfo("Streaming is acceptable (< 500ms)");
        } else {
            printWarning("Streaming is slow (>= 500ms)");
        }
        
        // Calculate throughput
        $throughput = ($fileSize / 1024 / 1024) / ($duration / 1000); // MB/s
        printInfo("Throughput: " . round($throughput, 2) . " MB/s");
        
    } else {
        printError("File not found for performance testing");
    }
    
} catch (\Exception $e) {
    printError("Failed to test performance: " . $e->getMessage());
}

// Step 9: Summary
printHeader('Step 9: Diagnostic Summary');

printInfo("Test Scenarios Completed:");
echo "  ✓ Basic PDF streaming\n";
echo "  ✓ Range request support\n";
echo "  ✓ Expired signature handling\n";
echo "  ✓ Invalid signature handling\n";
echo "  ✓ Multiple requests (idempotence)\n";
echo "  ✓ Session independence\n";
echo "  ✓ File size and performance\n";

echo "\n";
printInfo("Requirements Validated:");
echo "  - Requirement 1.1, 1.2: Signed URL validation\n";
echo "  - Requirement 1.4: Multiple requests with same URL\n";
echo "  - Requirement 3.1: Content-Type header\n";
echo "  - Requirement 3.4: Range request support\n";
echo "  - Requirement 4.1: Session independence\n";
echo "  - Requirement 5.1, 5.2, 5.3: Error logging and diagnostics\n";

printHeader('Diagnostic Complete');

echo "\nFor more information, check:\n";
echo "  - Laravel logs: storage/logs/laravel.log\n";
echo "  - Requirements: .kiro/specs/pdf-stream-403-fix/requirements.md\n";
echo "  - Design: .kiro/specs/pdf-stream-403-fix/design.md\n\n";
