<?php
// Debug script to check media content in database
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Content;
use Illuminate\Support\Facades\Storage;

echo "=== MEDIA CONTENT DEBUG ===\n";

// Check for media content
$mediaContent = Content::whereIn('type', ['image', 'pdf', 'audio', 'video'])->get();

echo "Found " . $mediaContent->count() . " media content blocks\n\n";

foreach ($mediaContent as $content) {
    echo "--- Content ID: {$content->id} ---\n";
    echo "Type: {$content->type}\n";
    echo "Title: {$content->title}\n";
    echo "File Path: " . ($content->file_path ?? 'NULL') . "\n";
    echo "File Name: " . ($content->file_name ?? 'NULL') . "\n";
    echo "External URL: " . ($content->external_url ?? 'NULL') . "\n";
    echo "MIME Type: " . ($content->mime_type ?? 'NULL') . "\n";
    
    if ($content->file_path) {
        echo "File exists: " . (Storage::exists($content->file_path) ? 'YES' : 'NO') . "\n";
        echo "Storage disk: " . config('filesystems.default') . "\n";
        echo "Full path: " . Storage::path($content->file_path) . "\n";
        
        try {
            $signedUrl = $content->getSignedUrl();
            echo "Signed URL: " . ($signedUrl ?? 'NULL') . "\n";
        } catch (Exception $e) {
            echo "Signed URL Error: " . $e->getMessage() . "\n";
        }
        
        try {
            $displayContent = $content->getDisplayContent();
            echo "Display Content: " . $displayContent . "\n";
        } catch (Exception $e) {
            echo "Display Content Error: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
}

// Check storage configuration
echo "=== STORAGE CONFIGURATION ===\n";
echo "Default disk: " . config('filesystems.default') . "\n";
echo "Private disk path: " . config('filesystems.disks.private.root') . "\n";
echo "Public disk path: " . config('filesystems.disks.public.root') . "\n";

// Check if storage link exists
$publicPath = public_path('storage');
echo "Storage link exists: " . (is_link($publicPath) ? 'YES' : 'NO') . "\n";
if (is_link($publicPath)) {
    echo "Storage link target: " . readlink($publicPath) . "\n";
}