<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Bootstrap Laravel
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "=== PDF Content Block Details ===\n\n";

$pdf = \App\Models\Content::where('type', 'pdf')->first();

if (!$pdf) {
    echo "No PDF content blocks found.\n";
    exit;
}

echo "ID: {$pdf->id}\n";
echo "Title: {$pdf->title}\n";
echo "Type: {$pdf->type}\n";
echo "File Path: {$pdf->file_path}\n";
echo "Storage Disk: " . ($pdf->storage_disk ?? 'null') . "\n";
echo "File Name: " . ($pdf->file_name ?? 'null') . "\n";
echo "Is Active: " . ($pdf->is_active ? 'Yes' : 'No') . "\n";
echo "\n";

// Test URL generation
echo "=== Testing URL Generation ===\n\n";

echo "1. getSecurePdfUrl():\n";
try {
    $url = $pdf->getSecurePdfUrl();
    if ($url) {
        echo "   ✓ URL: {$url}\n";
    } else {
        echo "   ✗ URL is NULL\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Exception: {$e->getMessage()}\n";
}
echo "\n";

echo "2. getDisplayContent():\n";
try {
    $content = $pdf->getDisplayContent();
    if ($content) {
        echo "   ✓ Content: {$content}\n";
        if ($content === url('/')) {
            echo "   ⚠ WARNING: This is the base URL!\n";
        }
    } else {
        echo "   ✗ Content is empty\n";
    }
} catch (\Exception $e) {
    echo "   ✗ Exception: {$e->getMessage()}\n";
}
echo "\n";

// Check file existence
echo "=== File Existence Check ===\n\n";

$disks = ['protected', 'private', 'public'];
foreach ($disks as $disk) {
    try {
        $exists = \Illuminate\Support\Facades\Storage::disk($disk)->exists($pdf->file_path);
        $status = $exists ? '✓' : '✗';
        echo "{$status} Disk '{$disk}': " . ($exists ? 'EXISTS' : 'NOT FOUND') . "\n";
        
        if ($exists) {
            $fullPath = \Illuminate\Support\Facades\Storage::disk($disk)->path($pdf->file_path);
            echo "   Path: {$fullPath}\n";
        }
    } catch (\Exception $e) {
        echo "✗ Disk '{$disk}': ERROR - {$e->getMessage()}\n";
    }
}

echo "\n=== Done ===\n";
