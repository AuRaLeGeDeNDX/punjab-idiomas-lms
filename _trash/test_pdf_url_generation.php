<?php

/**
 * Test PDF URL Generation
 * 
 * This script tests the PDF URL generation for Content Builder preview.
 * Run this from the browser while logged in as a teacher/admin.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

// Start session
session_start();

echo "<h1>PDF URL Generation Test</h1>";
echo "<hr>";

// Check if user is authenticated
if (!auth()->check()) {
    echo "<p style='color: red;'>❌ Not authenticated. Please log in first.</p>";
    echo "<p><a href='/login'>Go to Login</a></p>";
    exit;
}

echo "<p style='color: green;'>✅ Authenticated as: " . auth()->user()->name . " (" . auth()->user()->email . ")</p>";
echo "<hr>";

// Find a PDF content block
$pdfContent = \App\Models\Content::where('type', 'pdf')
    ->whereNotNull('file_path')
    ->first();

if (!$pdfContent) {
    echo "<p style='color: orange;'>⚠️ No PDF content blocks found in database.</p>";
    exit;
}

echo "<h2>PDF Content Block Found</h2>";
echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Property</th><th>Value</th></tr>";
echo "<tr><td>ID</td><td>{$pdfContent->id}</td></tr>";
echo "<tr><td>Title</td><td>{$pdfContent->title}</td></tr>";
echo "<tr><td>Type</td><td>{$pdfContent->type}</td></tr>";
echo "<tr><td>File Path</td><td>{$pdfContent->file_path}</td></tr>";
echo "<tr><td>Storage Disk</td><td>" . ($pdfContent->storage_disk ?? 'null') . "</td></tr>";
echo "<tr><td>File Name</td><td>" . ($pdfContent->file_name ?? 'null') . "</td></tr>";
echo "<tr><td>Is Active</td><td>" . ($pdfContent->is_active ? 'Yes' : 'No') . "</td></tr>";
echo "</table>";
echo "<hr>";

// Test URL generation methods
echo "<h2>URL Generation Tests</h2>";

// Test 1: getSecurePdfUrl()
echo "<h3>1. getSecurePdfUrl()</h3>";
try {
    $securePdfUrl = $pdfContent->getSecurePdfUrl();
    if ($securePdfUrl) {
        echo "<p style='color: green;'>✅ URL Generated:</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>{$securePdfUrl}</pre>";
        echo "<p><a href='{$securePdfUrl}' target='_blank'>Test URL in New Tab</a></p>";
    } else {
        echo "<p style='color: red;'>❌ URL is NULL</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Test 2: getDisplayContent()
echo "<h3>2. getDisplayContent()</h3>";
try {
    $displayContent = $pdfContent->getDisplayContent();
    if ($displayContent) {
        echo "<p style='color: green;'>✅ Display Content:</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>{$displayContent}</pre>";
        
        // Check if it's the base URL
        $baseUrl = url('/');
        if ($displayContent === $baseUrl || $displayContent === $baseUrl . '/') {
            echo "<p style='color: red;'>❌ WARNING: Display content is the base URL! This is the problem!</p>";
        } else {
            echo "<p><a href='{$displayContent}' target='_blank'>Test URL in New Tab</a></p>";
        }
    } else {
        echo "<p style='color: red;'>❌ Display Content is empty</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Test 3: Check route exists
echo "<h3>3. Route Check</h3>";
try {
    $routeExists = \Illuminate\Support\Facades\Route::has('secure.pdf.stream');
    if ($routeExists) {
        echo "<p style='color: green;'>✅ Route 'secure.pdf.stream' exists</p>";
        
        // Try to generate URL manually
        $manualUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes(10),
            ['content' => $pdfContent->id]
        );
        echo "<p>Manual URL generation:</p>";
        echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>{$manualUrl}</pre>";
        echo "<p><a href='{$manualUrl}' target='_blank'>Test Manual URL in New Tab</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Route 'secure.pdf.stream' does NOT exist</p>";
    }
} catch (\Exception $e) {
    echo "<p style='color: red;'>❌ Exception: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

// Test 4: File existence
echo "<h3>4. File Existence Check</h3>";
$storageDisk = $pdfContent->storage_disk ?? 'protected';
$disksToCheck = ['protected', 'private', 'public'];

echo "<table border='1' cellpadding='5' style='border-collapse: collapse;'>";
echo "<tr><th>Disk</th><th>Exists?</th><th>Path</th></tr>";

foreach ($disksToCheck as $disk) {
    try {
        $exists = \Illuminate\Support\Facades\Storage::disk($disk)->exists($pdfContent->file_path);
        $color = $exists ? 'green' : 'red';
        $status = $exists ? '✅ Yes' : '❌ No';
        $fullPath = \Illuminate\Support\Facades\Storage::disk($disk)->path($pdfContent->file_path);
        
        echo "<tr>";
        echo "<td>{$disk}</td>";
        echo "<td style='color: {$color};'>{$status}</td>";
        echo "<td style='font-size: 11px;'>{$fullPath}</td>";
        echo "</tr>";
    } catch (\Exception $e) {
        echo "<tr>";
        echo "<td>{$disk}</td>";
        echo "<td style='color: orange;'>⚠️ Error</td>";
        echo "<td style='font-size: 11px;'>{$e->getMessage()}</td>";
        echo "</tr>";
    }
}

echo "</table>";

echo "<hr>";
echo "<h2>Iframe Test</h2>";
echo "<p>Testing iframe with getDisplayContent() URL:</p>";

$iframeUrl = $pdfContent->getDisplayContent();
echo "<div style='border: 2px solid #ccc; padding: 10px; margin: 10px 0;'>";
echo "<p><strong>URL:</strong> <code>{$iframeUrl}</code></p>";
echo "<iframe src='{$iframeUrl}' style='width: 100%; height: 400px; border: 1px solid #999;'></iframe>";
echo "</div>";

echo "<hr>";
echo "<p><em>Check browser console (F12) for any errors</em></p>";
