<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=================================================\n";
echo "Video Playback Diagnostic Tool\n";
echo "=================================================\n\n";

// Get all video content
$videos = \App\Models\Content::where('type', 'video')->get();

if ($videos->isEmpty()) {
    echo "❌ No videos found in database\n";
    echo "\nPlease upload a video first through the content builder.\n";
    exit(1);
}

echo "Found " . $videos->count() . " video(s) in database:\n\n";

foreach ($videos as $video) {
    echo "---------------------------------------------------\n";
    echo "Video ID: {$video->id}\n";
    echo "Title: {$video->title}\n";
    echo "File Path: {$video->file_path}\n";
    echo "Storage Disk: {$video->storage_disk}\n";
    echo "MIME Type: {$video->mime_type}\n";
    echo "Is Active: " . ($video->is_active ? 'Yes' : 'No') . "\n";
    echo "External URL: " . ($video->external_url ?: 'None') . "\n";
    
    // Check if file exists
    if ($video->file_path) {
        $disk = $video->storage_disk ?? 'public';
        
        echo "\nFile Existence Check:\n";
        
        // Check on recorded disk
        try {
            $exists = \Storage::disk($disk)->exists($video->file_path);
            echo "  - On '{$disk}' disk: " . ($exists ? '✅ EXISTS' : '❌ NOT FOUND') . "\n";
            
            if ($exists) {
                $size = \Storage::disk($disk)->size($video->file_path);
                echo "  - File size: " . number_format($size / 1024 / 1024, 2) . " MB\n";
            }
        } catch (\Exception $e) {
            echo "  - Error checking '{$disk}' disk: " . $e->getMessage() . "\n";
        }
        
        // Check on private disk
        try {
            $exists = \Storage::disk('private')->exists($video->file_path);
            echo "  - On 'private' disk: " . ($exists ? '✅ EXISTS' : '❌ NOT FOUND') . "\n";
        } catch (\Exception $e) {
            echo "  - Error checking 'private' disk: " . $e->getMessage() . "\n";
        }
        
        // Check on public disk
        try {
            $exists = \Storage::disk('public')->exists($video->file_path);
            echo "  - On 'public' disk: " . ($exists ? '✅ EXISTS' : '❌ NOT FOUND') . "\n";
        } catch (\Exception $e) {
            echo "  - Error checking 'public' disk: " . $e->getMessage() . "\n";
        }
    }
    
    // Test URL generation
    echo "\nURL Generation Test:\n";
    
    try {
        $displayUrl = $video->getDisplayContent();
        echo "  - getDisplayContent(): " . ($displayUrl ?: 'NULL') . "\n";
        
        if ($displayUrl) {
            // Check if it's a signed URL
            if (strpos($displayUrl, 'signature=') !== false) {
                echo "  - URL Type: ✅ Signed URL (secure)\n";
            } elseif (strpos($displayUrl, 'http') === 0) {
                echo "  - URL Type: ⚠️  Direct URL (not secure)\n";
            } else {
                echo "  - URL Type: ❓ Unknown\n";
            }
        }
    } catch (\Exception $e) {
        echo "  - Error: " . $e->getMessage() . "\n";
    }
    
    try {
        $secureUrl = $video->getSecureVideoUrl();
        echo "  - getSecureVideoUrl(): " . ($secureUrl ?: 'NULL') . "\n";
    } catch (\Exception $e) {
        echo "  - Error: " . $e->getMessage() . "\n";
    }
    
    // Check course relationship
    echo "\nCourse Relationship Check:\n";
    try {
        $subpage = $video->subpage;
        if ($subpage) {
            echo "  - Subpage: ✅ {$subpage->title} (ID: {$subpage->id})\n";
            
            $module = $subpage->module;
            if ($module) {
                echo "  - Module: ✅ {$module->title} (ID: {$module->id})\n";
                
                $course = $module->course;
                if ($course) {
                    echo "  - Course: ✅ {$course->title} (ID: {$course->id})\n";
                    echo "  - Teacher ID: {$course->teacher_id}\n";
                } else {
                    echo "  - Course: ❌ NOT FOUND\n";
                }
            } else {
                echo "  - Module: ❌ NOT FOUND\n";
            }
        } else {
            echo "  - Subpage: ❌ NOT FOUND\n";
        }
    } catch (\Exception $e) {
        echo "  - Error: " . $e->getMessage() . "\n";
    }
    
    echo "\n";
}

echo "=================================================\n";
echo "Diagnostic Complete\n";
echo "=================================================\n";
