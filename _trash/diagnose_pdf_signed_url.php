<?php

/**
 * Web-Based Diagnostic Tool for PDF Signed URL Generation and Validation
 * 
 * This script tests the signed URL generation and validation process
 * to identify issues causing 403 Forbidden errors in PDF streaming.
 * 
 * Requirements tested: 1.1, 1.2, 7.1, 7.2, 7.3
 * 
 * Usage: Access via browser at /diagnose_pdf_signed_url.php?content_id=X
 */

// Bootstrap Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Storage;
use App\Models\Content;
use App\Services\SecurePdfService;

// Get content ID from query parameter
$contentId = $_GET['content_id'] ?? null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PDF Signed URL Diagnostic Tool</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #f5f5f5;
            padding: 20px;
            line-height: 1.6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        h2 {
            color: #555;
            margin-top: 30px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e0e0e0;
            font-size: 20px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .success {
            color: #28a745;
            padding: 10px;
            background: #d4edda;
            border-left: 4px solid #28a745;
            margin: 10px 0;
            border-radius: 4px;
        }
        
        .error {
            color: #dc3545;
            padding: 10px;
            background: #f8d7da;
            border-left: 4px solid #dc3545;
            margin: 10px 0;
            border-radius: 4px;
        }
        
        .warning {
            color: #856404;
            padding: 10px;
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            margin: 10px 0;
            border-radius: 4px;
        }
        
        .info {
            color: #004085;
            padding: 10px;
            background: #cce5ff;
            border-left: 4px solid #007bff;
            margin: 10px 0;
            border-radius: 4px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
            background: white;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
        }
        
        .url-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin: 10px 0;
            border: 1px solid #dee2e6;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-success {
            background: #28a745;
            color: white;
        }
        
        .badge-error {
            background: #dc3545;
            color: white;
        }
        
        .badge-warning {
            background: #ffc107;
            color: #333;
        }
        
        .badge-info {
            background: #17a2b8;
            color: white;
        }
        
        ul {
            margin: 10px 0;
            padding-left: 30px;
        }
        
        li {
            margin: 5px 0;
        }
        
        .summary-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .summary-box h3 {
            margin-bottom: 15px;
            color: #333;
        }
        
        .form-group {
            margin: 20px 0;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        input[type="number"] {
            width: 200px;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        
        button {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }
        
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 PDF Signed URL Diagnostic Tool</h1>
        <p class="subtitle">Testing signed URL generation and validation for PDF streaming (Requirements 1.1, 1.2, 7.1, 7.2, 7.3)</p>
        
        <?php if (!$contentId): ?>
            <div class="info">
                <strong>ℹ️ No content ID specified</strong>
                <p>Please provide a content ID to test, or the first PDF content will be used.</p>
            </div>
            
            <div class="form-group">
                <label for="content_id">Content ID:</label>
                <input type="number" id="content_id" name="content_id" placeholder="Enter content ID">
                <button onclick="window.location.href='?content_id=' + document.getElementById('content_id').value">Test Content</button>
            </div>
        <?php endif; ?>
        
        <?php
        // Step 1: Find content
        echo '<h2>Step 1: Finding Test Content</h2>';
        
        if ($contentId) {
            $content = Content::find($contentId);
            if (!$content) {
                echo '<div class="error"><strong>✗ Error:</strong> Content with ID ' . htmlspecialchars($contentId) . ' not found</div>';
                exit;
            }
            echo '<div class="success"><strong>✓ Success:</strong> Using specified content ID: ' . htmlspecialchars($contentId) . '</div>';
        } else {
            $content = Content::where('type', 'pdf')->first();
            if (!$content) {
                echo '<div class="error"><strong>✗ Error:</strong> No PDF content found in database</div>';
                echo '<div class="info">Please create a PDF content entry or specify a content ID</div>';
                exit;
            }
            echo '<div class="success"><strong>✓ Success:</strong> Using first PDF content found: ID ' . $content->id . '</div>';
        }
        
        echo '<table>';
        echo '<tr><th>Property</th><th>Value</th></tr>';
        echo '<tr><td>ID</td><td>' . $content->id . '</td></tr>';
        echo '<tr><td>Title</td><td>' . htmlspecialchars($content->title) . '</td></tr>';
        echo '<tr><td>Type</td><td>' . htmlspecialchars($content->type) . '</td></tr>';
        echo '<tr><td>File Path</td><td>' . htmlspecialchars($content->file_path) . '</td></tr>';
        echo '<tr><td>Storage Disk</td><td>' . htmlspecialchars($content->storage_disk ?? 'protected') . '</td></tr>';
        echo '</table>';
        
        // Step 2: Test signed URL generation
        echo '<h2>Step 2: Testing Signed URL Generation</h2>';
        
        $pdfService = new SecurePdfService();
        
        try {
            $signedUrl = $pdfService->generateSecureUrl($content, 5);
            echo '<div class="success"><strong>✓ Success:</strong> Signed URL generated successfully</div>';
            
            echo '<div class="info"><strong>Generated URL:</strong></div>';
            echo '<div class="url-box">' . htmlspecialchars($signedUrl) . '</div>';
            
            // Parse URL components
            $parsedUrl = parse_url($signedUrl);
            parse_str($parsedUrl['query'] ?? '', $queryParams);
            
            echo '<table>';
            echo '<tr><th>Component</th><th>Value</th></tr>';
            echo '<tr><td>Scheme</td><td>' . ($parsedUrl['scheme'] ?? 'N/A') . '</td></tr>';
            echo '<tr><td>Host</td><td>' . ($parsedUrl['host'] ?? 'N/A') . '</td></tr>';
            echo '<tr><td>Path</td><td>' . ($parsedUrl['path'] ?? 'N/A') . '</td></tr>';
            echo '</table>';
            
            echo '<table>';
            echo '<tr><th>Query Parameter</th><th>Value</th></tr>';
            foreach ($queryParams as $key => $value) {
                if ($key === 'signature') {
                    echo '<tr><td>' . htmlspecialchars($key) . '</td><td>' . htmlspecialchars(substr($value, 0, 30)) . '... (truncated)</td></tr>';
                } else {
                    echo '<tr><td>' . htmlspecialchars($key) . '</td><td>' . htmlspecialchars($value) . '</td></tr>';
                }
            }
            echo '</table>';
            
            // Validate URL structure
            echo '<h3>URL Validation:</h3>';
            echo '<table>';
            echo '<tr><th>Check</th><th>Status</th><th>Result</th></tr>';
            
            if (isset($parsedUrl['scheme']) && in_array($parsedUrl['scheme'], ['http', 'https'])) {
                echo '<tr><td>Valid scheme</td><td><span class="badge badge-success">✓ PASS</span></td><td>' . $parsedUrl['scheme'] . '</td></tr>';
            } else {
                echo '<tr><td>Valid scheme</td><td><span class="badge badge-error">✗ FAIL</span></td><td>Invalid or missing</td></tr>';
            }
            
            if (isset($parsedUrl['host'])) {
                echo '<tr><td>Has host (absolute URL)</td><td><span class="badge badge-success">✓ PASS</span></td><td>' . $parsedUrl['host'] . '</td></tr>';
            } else {
                echo '<tr><td>Has host (absolute URL)</td><td><span class="badge badge-error">✗ FAIL</span></td><td>Missing host</td></tr>';
            }
            
            if (isset($queryParams['signature'])) {
                echo '<tr><td>Has signature parameter</td><td><span class="badge badge-success">✓ PASS</span></td><td>Present</td></tr>';
            } else {
                echo '<tr><td>Has signature parameter</td><td><span class="badge badge-error">✗ FAIL</span></td><td>Missing</td></tr>';
            }
            
            if (isset($queryParams['expires'])) {
                $expiresAt = (int)$queryParams['expires'];
                $now = time();
                $timeUntilExpiry = $expiresAt - $now;
                $minutes = round($timeUntilExpiry / 60, 2);
                
                echo '<tr><td>Has expires parameter</td><td><span class="badge badge-success">✓ PASS</span></td><td>' . date('Y-m-d H:i:s', $expiresAt) . '</td></tr>';
                echo '<tr><td>Time until expiry</td><td><span class="badge badge-info">ℹ INFO</span></td><td>' . $timeUntilExpiry . 's (' . $minutes . ' min)</td></tr>';
                
                if ($timeUntilExpiry >= 300) {
                    echo '<tr><td>Expiration >= 5 minutes (Req 7.1)</td><td><span class="badge badge-success">✓ PASS</span></td><td>Valid</td></tr>';
                } else {
                    echo '<tr><td>Expiration >= 5 minutes (Req 7.1)</td><td><span class="badge badge-warning">⚠ WARNING</span></td><td>Less than 5 minutes</td></tr>';
                }
            } else {
                echo '<tr><td>Has expires parameter</td><td><span class="badge badge-error">✗ FAIL</span></td><td>Missing</td></tr>';
            }
            
            echo '</table>';
            
        } catch (\Exception $e) {
            echo '<div class="error"><strong>✗ Error:</strong> Failed to generate signed URL: ' . htmlspecialchars($e->getMessage()) . '</div>';
            exit;
        }
        
        // Step 3: Test signature validation
        echo '<h2>Step 3: Testing Signature Validation</h2>';
        
        try {
            // Create a mock request with the signed URL
            $request = Illuminate\Http\Request::create($signedUrl, 'GET');
            
            if ($request->hasValidSignature()) {
                echo '<div class="success"><strong>✓ Success:</strong> Signature validation PASSED</div>';
                echo '<div class="info">The signed URL is valid and should work with PDF.js</div>';
            } else {
                echo '<div class="error"><strong>✗ Error:</strong> Signature validation FAILED</div>';
                echo '<div class="warning">This is likely the cause of 403 errors</div>';
                
                echo '<h3>Diagnosing validation failure:</h3>';
                echo '<table>';
                echo '<tr><th>Check</th><th>Status</th><th>Result</th></tr>';
                
                if (!$request->query('signature')) {
                    echo '<tr><td>Signature parameter</td><td><span class="badge badge-error">✗ FAIL</span></td><td>Missing</td></tr>';
                } else {
                    echo '<tr><td>Signature parameter</td><td><span class="badge badge-success">✓ PASS</span></td><td>Present</td></tr>';
                }
                
                if (!$request->query('expires')) {
                    echo '<tr><td>Expires parameter</td><td><span class="badge badge-error">✗ FAIL</span></td><td>Missing</td></tr>';
                } else {
                    $expires = (int)$request->query('expires');
                    $now = time();
                    
                    if ($expires < $now) {
                        echo '<tr><td>Expires parameter</td><td><span class="badge badge-error">✗ FAIL</span></td><td>Expired</td></tr>';
                    } else {
                        echo '<tr><td>Expires parameter</td><td><span class="badge badge-success">✓ PASS</span></td><td>Valid</td></tr>';
                    }
                }
                
                $originalUrl = $signedUrl;
                $decodedUrl = urldecode($signedUrl);
                
                if ($originalUrl !== $decodedUrl) {
                    echo '<tr><td>URL encoding</td><td><span class="badge badge-warning">⚠ WARNING</span></td><td>URL appears to be encoded</td></tr>';
                } else {
                    echo '<tr><td>URL encoding</td><td><span class="badge badge-success">✓ PASS</span></td><td>Correct</td></tr>';
                }
                
                echo '</table>';
            }
            
        } catch (\Exception $e) {
            echo '<div class="error"><strong>✗ Error:</strong> Failed to validate signature: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        
        // Step 4: Test route configuration
        echo '<h2>Step 4: Testing Route Configuration</h2>';
        
        try {
            $routeName = 'secure.pdf.stream';
            $route = app('router')->getRoutes()->getByName($routeName);
            
            if ($route) {
                echo '<div class="success"><strong>✓ Success:</strong> Route \'' . $routeName . '\' exists</div>';
                
                $middleware = $route->middleware();
                
                echo '<table>';
                echo '<tr><th>Property</th><th>Value</th></tr>';
                echo '<tr><td>URI</td><td>' . $route->uri() . '</td></tr>';
                echo '<tr><td>Methods</td><td>' . implode(', ', $route->methods()) . '</td></tr>';
                echo '<tr><td>Action</td><td>' . $route->getActionName() . '</td></tr>';
                echo '<tr><td>Middleware</td><td>' . (empty($middleware) ? 'None' : implode(', ', $middleware)) . '</td></tr>';
                echo '</table>';
                
                if (in_array('signed', $middleware)) {
                    echo '<div class="success"><strong>✓ Success:</strong> \'signed\' middleware is applied</div>';
                } else {
                    echo '<div class="error"><strong>✗ Error:</strong> \'signed\' middleware is NOT applied</div>';
                    echo '<div class="warning">This will cause validation to fail</div>';
                }
                
                $conflictingMiddleware = ['auth', 'csrf'];
                foreach ($conflictingMiddleware as $mw) {
                    if (in_array($mw, $middleware)) {
                        echo '<div class="warning"><strong>⚠ Warning:</strong> \'' . $mw . '\' middleware is applied (may cause issues)</div>';
                    }
                }
                
            } else {
                echo '<div class="error"><strong>✗ Error:</strong> Route \'' . $routeName . '\' not found</div>';
            }
            
        } catch (\Exception $e) {
            echo '<div class="error"><strong>✗ Error:</strong> Failed to check route configuration: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        
        // Step 5: Test file access
        echo '<h2>Step 5: Testing File Access</h2>';
        
        try {
            $storageDisk = $content->storage_disk ?? 'protected';
            $disk = Storage::disk($storageDisk);
            
            if ($disk->exists($content->file_path)) {
                echo '<div class="success"><strong>✓ Success:</strong> File exists on disk \'' . $storageDisk . '\'</div>';
                
                $fileSize = $disk->size($content->file_path);
                $lastModified = $disk->lastModified($content->file_path);
                
                echo '<table>';
                echo '<tr><th>Property</th><th>Value</th></tr>';
                echo '<tr><td>Size</td><td>' . number_format($fileSize) . ' bytes (' . round($fileSize / 1024 / 1024, 2) . ' MB)</td></tr>';
                echo '<tr><td>Last Modified</td><td>' . date('Y-m-d H:i:s', $lastModified) . '</td></tr>';
                echo '<tr><td>Full Path</td><td>' . htmlspecialchars($disk->path($content->file_path)) . '</td></tr>';
                echo '</table>';
                
                $fullPath = $disk->path($content->file_path);
                if (is_readable($fullPath)) {
                    echo '<div class="success"><strong>✓ Success:</strong> File is readable</div>';
                } else {
                    echo '<div class="error"><strong>✗ Error:</strong> File exists but is not readable (permission issue)</div>';
                }
                
            } else {
                echo '<div class="error"><strong>✗ Error:</strong> File does not exist on disk \'' . $storageDisk . '\'</div>';
            }
            
        } catch (\Exception $e) {
            echo '<div class="error"><strong>✗ Error:</strong> Failed to check file access: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        
        // Summary
        echo '<h2>Summary and Recommendations</h2>';
        
        $issues = [];
        $warnings = [];
        
        $parsedUrl = parse_url($signedUrl);
        parse_str($parsedUrl['query'] ?? '', $queryParams);
        
        if (!isset($parsedUrl['host'])) {
            $issues[] = "Signed URL is not absolute (missing host)";
        }
        
        if (!isset($queryParams['signature'])) {
            $issues[] = "Signed URL is missing signature parameter";
        }
        
        if (!isset($queryParams['expires'])) {
            $issues[] = "Signed URL is missing expires parameter";
        } else {
            $expiresAt = (int)$queryParams['expires'];
            $timeUntilExpiry = $expiresAt - time();
            if ($timeUntilExpiry < 300) {
                $warnings[] = "Expiration time is less than 5 minutes";
            }
        }
        
        $request = Illuminate\Http\Request::create($signedUrl, 'GET');
        if (!$request->hasValidSignature()) {
            $issues[] = "Signature validation fails for generated URL";
        }
        
        $route = app('router')->getRoutes()->getByName('secure.pdf.stream');
        if (!$route) {
            $issues[] = "Route 'secure.pdf.stream' not found";
        } else {
            $middleware = $route->middleware();
            if (!in_array('signed', $middleware)) {
                $issues[] = "Route is missing 'signed' middleware";
            }
        }
        
        if (empty($issues) && empty($warnings)) {
            echo '<div class="success">';
            echo '<h3>✓ No issues found!</h3>';
            echo '<p>The signed URL system appears to be working correctly.</p>';
            echo '<p>If you\'re still experiencing 403 errors, check:</p>';
            echo '<ul>';
            echo '<li>Browser console for JavaScript errors</li>';
            echo '<li>Network tab for actual request/response details</li>';
            echo '<li>Laravel logs for detailed error messages</li>';
            echo '</ul>';
            echo '</div>';
        } else {
            if (!empty($issues)) {
                echo '<div class="error">';
                echo '<h3>✗ Critical Issues Found:</h3>';
                echo '<ul>';
                foreach ($issues as $issue) {
                    echo '<li>' . htmlspecialchars($issue) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            
            if (!empty($warnings)) {
                echo '<div class="warning">';
                echo '<h3>⚠ Warnings:</h3>';
                echo '<ul>';
                foreach ($warnings as $warning) {
                    echo '<li>' . htmlspecialchars($warning) . '</li>';
                }
                echo '</ul>';
                echo '</div>';
            }
            
            echo '<div class="info">';
            echo '<h3>Recommendations:</h3>';
            echo '<ul>';
            
            if (!isset($parsedUrl['host'])) {
                echo '<li>Ensure APP_URL is set correctly in .env file</li>';
                echo '<li>Use URL::temporarySignedRoute() instead of route() helper</li>';
                echo '<li>Force absolute URLs in config/app.php</li>';
            }
            
            if (!$request->hasValidSignature()) {
                echo '<li>Check that APP_KEY is set and consistent</li>';
                echo '<li>Verify URL encoding is not breaking the signature</li>';
                echo '<li>Ensure the URL is not being modified before validation</li>';
            }
            
            if ($route && !in_array('signed', $route->middleware())) {
                echo '<li>Add \'signed\' middleware to the route in routes/web.php</li>';
                echo '<li>Ensure middleware is applied correctly</li>';
            }
            
            echo '</ul>';
            echo '</div>';
        }
        
        echo '<div class="info">';
        echo '<h3>Additional Resources:</h3>';
        echo '<ul>';
        echo '<li>Laravel logs: <code>storage/logs/laravel.log</code></li>';
        echo '<li>Requirements: <code>.kiro/specs/pdf-stream-403-fix/requirements.md</code></li>';
        echo '<li>Design: <code>.kiro/specs/pdf-stream-403-fix/design.md</code></li>';
        echo '</ul>';
        echo '</div>';
        ?>
    </div>
</body>
</html>
