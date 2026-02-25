<?php

// Simple debug script to check video content
require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

header('Content-Type: text/html; charset=utf-8');

echo "<h1>Video Debug Information</h1>";
echo "<style>body { font-family: monospace; } table { border-collapse: collapse; } td, th { border: 1px solid #ddd; padding: 8px; text-align: left; } .error { color: red; } .success { color: green; }</style>";

// Get content ID from URL
$contentId = $_GET['id'] ?? 37;

echo "<h2>Checking Content ID: {$contentId}</h2>";

// Check if content exists
$content = \App\Models\Content::find($contentId);

if (!$content) {
    echo "<p class='error'>❌ Content with ID {$contentId} NOT FOUND in database</p>";
    
    // Show all video content
    $videos = \App\Models\Content::where('type', 'video')->get();
    echo "<h3>Available Videos:</h3>";
    if ($videos->isEmpty()) {
        echo "<p class='error'>No videos found in database</p>";
    } else {
        echo "<table>";
        echo "<tr><th>ID</th><th>Title</th><th>File Path</th><th>Storage Disk</th><th>Active</th></tr>";
        foreach ($videos as $v) {
            echo "<tr>";
            echo "<td><a href='?id={$v->id}'>{$v->id}</a></td>";
            echo "<td>{$v->title}</td>";
            echo "<td>{$v->file_path}</td>";
            echo "<td>{$v->storage_disk}</td>";
            echo "<td>" . ($v->is_active ? 'Yes' : 'No') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    exit;
}

echo "<p class='success'>✅ Content found in database</p>";

echo "<h3>Content Details:</h3>";
echo "<table>";
echo "<tr><th>Property</th><th>Value</th></tr>";
echo "<tr><td>ID</td><td>{$content->id}</td></tr>";
echo "<tr><td>Title</td><td>{$content->title}</td></tr>";
echo "<tr><td>Type</td><td>{$content->type}</td></tr>";
echo "<tr><td>File Path</td><td>{$content->file_path}</td></tr>";
echo "<tr><td>Storage Disk</td><td>{$content->storage_disk}</td></tr>";
echo "<tr><td>MIME Type</td><td>{$content->mime_type}</td></tr>";
echo "<tr><td>Is Active</td><td>" . ($content->is_active ? 'Yes' : 'No') . "</td></tr>";
echo "<tr><td>External URL</td><td>" . ($content->external_url ?: 'None') . "</td></tr>";
echo "</table>";

// Check file existence
echo "<h3>File Existence Check:</h3>";
if ($content->file_path) {
    $disk = $content->storage_disk ?? 'public';
    
    echo "<table>";
    echo "<tr><th>Disk</th><th>Exists</th><th>Size</th></tr>";
    
    // Check private disk
    try {
        $exists = \Storage::disk('private')->exists($content->file_path);
        $size = $exists ? \Storage::disk('private')->size($content->file_path) : 0;
        echo "<tr><td>private</td><td>" . ($exists ? '✅ YES' : '❌ NO') . "</td><td>" . ($exists ? number_format($size / 1024 / 1024, 2) . ' MB' : '-') . "</td></tr>";
    } catch (\Exception $e) {
        echo "<tr><td>private</td><td class='error'>Error: {$e->getMessage()}</td><td>-</td></tr>";
    }
    
    // Check public disk
    try {
        $exists = \Storage::disk('public')->exists($content->file_path);
        $size = $exists ? \Storage::disk('public')->size($content->file_path) : 0;
        echo "<tr><td>public</td><td>" . ($exists ? '✅ YES' : '❌ NO') . "</td><td>" . ($exists ? number_format($size / 1024 / 1024, 2) . ' MB' : '-') . "</td></tr>";
    } catch (\Exception $e) {
        echo "<tr><td>public</td><td class='error'>Error: {$e->getMessage()}</td><td>-</td></tr>";
    }
    
    // Check recorded disk
    if ($disk !== 'private' && $disk !== 'public') {
        try {
            $exists = \Storage::disk($disk)->exists($content->file_path);
            $size = $exists ? \Storage::disk($disk)->size($content->file_path) : 0;
            echo "<tr><td>{$disk}</td><td>" . ($exists ? '✅ YES' : '❌ NO') . "</td><td>" . ($exists ? number_format($size / 1024 / 1024, 2) . ' MB' : '-') . "</td></tr>";
        } catch (\Exception $e) {
            echo "<tr><td>{$disk}</td><td class='error'>Error: {$e->getMessage()}</td><td>-</td></tr>";
        }
    }
    
    echo "</table>";
} else {
    echo "<p class='error'>❌ No file path recorded</p>";
}

// Test URL generation
echo "<h3>URL Generation Test:</h3>";
try {
    $url = $content->getSecureVideoUrl();
    if ($url) {
        echo "<p class='success'>✅ Secure URL generated successfully</p>";
        echo "<p><strong>URL:</strong> <a href='{$url}' target='_blank'>{$url}</a></p>";
    } else {
        echo "<p class='error'>❌ getSecureVideoUrl() returned NULL</p>";
    }
} catch (\Exception $e) {
    echo "<p class='error'>❌ Error generating URL: {$e->getMessage()}</p>";
}

// Check course relationship
echo "<h3>Course Relationship:</h3>";
try {
    $subpage = $content->subpage;
    if ($subpage) {
        echo "<p class='success'>✅ Subpage: {$subpage->title} (ID: {$subpage->id})</p>";
        
        $module = $subpage->module;
        if ($module) {
            echo "<p class='success'>✅ Module: {$module->title} (ID: {$module->id})</p>";
            
            $course = $module->course;
            if ($course) {
                echo "<p class='success'>✅ Course: {$course->title} (ID: {$course->id})</p>";
                echo "<p>Teacher ID: {$course->teacher_id}</p>";
            } else {
                echo "<p class='error'>❌ Course not found</p>";
            }
        } else {
            echo "<p class='error'>❌ Module not found</p>";
        }
    } else {
        echo "<p class='error'>❌ Subpage not found</p>";
    }
} catch (\Exception $e) {
    echo "<p class='error'>❌ Error: {$e->getMessage()}</p>";
}

echo "<hr>";
echo "<p><a href='?id={$contentId}'>Refresh</a> | <a href='debug_video.php'>Show all videos</a></p>";
