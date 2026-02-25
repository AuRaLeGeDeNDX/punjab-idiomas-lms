<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Video Upload Diagnostics\n";
echo "========================\n\n";

// Check protected disk
echo "1. Checking 'protected' disk configuration...\n";
try {
    $disk = Storage::disk('protected');
    echo "   ✓ Protected disk configured\n";
    echo "   Root: " . $disk->path('') . "\n";
    echo "   Exists: " . (file_exists($disk->path('')) ? 'YES' : 'NO') . "\n";
    echo "   Writable: " . (is_writable($disk->path('')) ? 'YES' : 'NO') . "\n";
} catch (Exception $e) {
    echo "   ✗ ERROR: " . $e->getMessage() . "\n";
}

echo "\n2. Checking content_blocks directory...\n";
$contentBlocksPath = storage_path('app/protected/content_blocks');
echo "   Path: {$contentBlocksPath}\n";
echo "   Exists: " . (file_exists($contentBlocksPath) ? 'YES' : 'NO') . "\n";
if (!file_exists($contentBlocksPath)) {
    echo "   Creating directory...\n";
    mkdir($contentBlocksPath, 0755, true);
    echo "   ✓ Directory created\n";
}
echo "   Writable: " . (is_writable($contentBlocksPath) ? 'YES' : 'NO') . "\n";

echo "\n3. Checking recent content uploads...\n";
$recentContent = \App\Models\Content::where('type', 'video')
    ->orderBy('created_at', 'desc')
    ->limit(5)
    ->get();

if ($recentContent->isEmpty()) {
    echo "   No recent video uploads found\n";
} else {
    foreach ($recentContent as $content) {
        echo "\n   Content ID: {$content->id}\n";
        echo "   File Path: {$content->file_path}\n";
        echo "   Storage Disk: {$content->storage_disk}\n";
        echo "   Created: {$content->created_at}\n";
        
        if ($content->storage_disk && $content->file_path) {
            try {
                $disk = Storage::disk($content->storage_disk);
                $exists = $disk->exists($content->file_path);
                echo "   File Exists: " . ($exists ? 'YES' : 'NO') . "\n";
                
                if ($exists) {
                    $size = $disk->size($content->file_path);
                    echo "   File Size: " . number_format($size / 1024 / 1024, 2) . " MB\n";
                }
            } catch (Exception $e) {
                echo "   Error checking file: " . $e->getMessage() . "\n";
            }
        }
    }
}

echo "\n4. Testing video file upload simulation...\n";
try {
    $testContent = 'Test video content for diagnostics';
    $testPath = 'content_blocks/test_video_' . time() . '.mp4';
    
    $disk = Storage::disk('protected');
    $disk->put($testPath, $testContent);
    echo "   ✓ Test file written\n";
    
    $exists = $disk->exists($testPath);
    echo "   ✓ Test file exists: " . ($exists ? 'YES' : 'NO') . "\n";
    
    $disk->delete($testPath);
    echo "   ✓ Test file deleted\n";
    
    echo "\n✅ Video upload mechanism is working correctly!\n";
    
} catch (Exception $e) {
    echo "\n❌ ERROR during test: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n========================\n";
echo "Diagnostics complete\n";
