<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Bootstrap Laravel
$request = Illuminate\Http\Request::capture();
$response = $kernel->handle($request);

echo "=== Fixing PDF Storage Disk ===\n\n";

$pdf = \App\Models\Content::find(6);

if (!$pdf) {
    echo "PDF content block not found.\n";
    exit;
}

echo "Current storage_disk: " . ($pdf->storage_disk ?? 'null') . "\n";
echo "File path: {$pdf->file_path}\n\n";

// Check where file actually exists
$disks = ['protected', 'private', 'public'];
$actualDisk = null;

foreach ($disks as $disk) {
    if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($pdf->file_path)) {
        $actualDisk = $disk;
        echo "✓ File found on '{$disk}' disk\n";
        break;
    }
}

if (!$actualDisk) {
    echo "✗ File not found on any disk!\n";
    exit;
}

if ($pdf->storage_disk !== $actualDisk) {
    echo "\nUpdating storage_disk from '{$pdf->storage_disk}' to '{$actualDisk}'...\n";
    $pdf->storage_disk = $actualDisk;
    $pdf->save();
    echo "✓ Updated successfully!\n";
} else {
    echo "\n✓ Storage disk is already correct.\n";
}

echo "\n=== Done ===\n";
