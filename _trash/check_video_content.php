<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Checking for video content in database...\n\n";

$videos = App\Models\Content::where('type', 'video')->get(['id', 'title', 'file_path', 'storage_disk', 'is_active']);

if ($videos->isEmpty()) {
    echo "No video content found in database.\n";
} else {
    echo "Found " . $videos->count() . " video content records:\n\n";
    foreach ($videos as $video) {
        echo "ID: {$video->id}\n";
        echo "Title: {$video->title}\n";
        echo "File Path: {$video->file_path}\n";
        echo "Storage Disk: {$video->storage_disk}\n";
        echo "Is Active: " . ($video->is_active ? 'Yes' : 'No') . "\n";
        
        // Check if file exists
        if ($video->file_path && $video->storage_disk) {
            $exists = \Illuminate\Support\Facades\Storage::disk($video->storage_disk)->exists($video->file_path);
            echo "File Exists: " . ($exists ? 'Yes' : 'No') . "\n";
            
            if ($exists) {
                $fullPath = \Illuminate\Support\Facades\Storage::disk($video->storage_disk)->path($video->file_path);
                echo "Full Path: {$fullPath}\n";
            }
        }
        
        echo "\n" . str_repeat('-', 50) . "\n\n";
    }
}

echo "\nChecking Content ID 37 specifically...\n";
$content37 = App\Models\Content::find(37);
if ($content37) {
    echo "Content ID 37 EXISTS\n";
    echo "Type: {$content37->type}\n";
    echo "Title: {$content37->title}\n";
} else {
    echo "Content ID 37 NOT FOUND\n";
}
