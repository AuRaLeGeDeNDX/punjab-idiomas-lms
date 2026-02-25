<?php

/**
 * Video Preview Diagnostic Script
 * 
 * This script helps diagnose video preview 404 errors by checking:
 * - Video content records in database
 * - File existence in storage
 * - URL generation
 * - Route accessibility
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Video Preview Diagnostic ===\n\n";

// Get all video content
$videos = \App\Models\Content::where('type', 'video')->get();

echo "Found " . $videos->count() . " video content blocks\n\n";

foreach ($videos as $video) {
    echo "--- Video ID: {$video->id} ---\n";
    echo "Title: {$video->title}\n";
    echo "File Path: {$video->file_path}\n";
    echo "Storage Disk: {$video->storage_disk}\n";
    echo "External URL: {$video->external_url}\n";
    echo "MIME Type: {$video->mime_type}\n";
    echo "File Size: {$video->formatted_file_size}\n\n";
    
    // Check if file exists
    if ($video->file_path) {
        $disk = $video->storage_disk ?? 'public';
        $exists = \Storage::disk($disk)->exists($video->file_path);
        echo "File exists on '{$disk}' disk: " . ($exists ? 'YES' : 'NO') . "\n";
        
        // Check alternative disks
        if (!$exists) {
            echo "Checking alternative storage locations...\n";
            foreach (['public', 'protected'] as $altDisk) {
                if ($altDisk !== $disk) {
                    $altExists = \Storage::disk($altDisk)->exists($video->file_path);
                    echo "  - '{$altDisk}' disk: " . ($altExists ? 'YES' : 'NO') . "\n";
                }
            }
        }
    }
    
    // Try to generate URL
    echo "\nURL Generation:\n";
    try {
        $displayUrl = $video->getDisplayContent();
        echo "Display URL: {$displayUrl}\n";
        
        // Check if URL is accessible
        if (filter_var($displayUrl, FILTER_VALIDATE_URL)) {
            echo "URL format: Valid\n";
            
            // Parse URL to check route
            $parsedUrl = parse_url($displayUrl);
            echo "URL Path: {$parsedUrl['path']}\n";
            if (isset($parsedUrl['query'])) {
                echo "URL Query: {$parsedUrl['query']}\n";
            }
        } else {
            echo "URL format: Invalid or relative\n";
        }
    } catch (\Exception $e) {
        echo "ERROR generating URL: {$e->getMessage()}\n";
    }
    
    echo "\n" . str_repeat("-", 50) . "\n\n";
}

// Check route registration
echo "=== Route Check ===\n";
$routes = \Route::getRoutes();
$secureVideoRoute = $routes->getByName('secure.video.stream');

if ($secureVideoRoute) {
    echo "Route 'secure.video.stream' is registered\n";
    echo "URI: {$secureVideoRoute->uri()}\n";
    echo "Methods: " . implode(', ', $secureVideoRoute->methods()) . "\n";
    echo "Middleware: " . implode(', ', $secureVideoRoute->middleware()) . "\n";
} else {
    echo "ERROR: Route 'secure.video.stream' is NOT registered\n";
}

echo "\n=== Diagnostic Complete ===\n";
