<?php

/**
 * Check for legacy file_paths in submissions table
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Submission;
use Illuminate\Support\Facades\Storage;

echo "=== Checking Legacy File Paths ===\n\n";

$submission = Submission::find(1);

if (!$submission) {
    echo "❌ Submission ID 1 not found\n";
    exit(1);
}

echo "✓ Found submission ID: {$submission->id}\n";
echo "  Student ID: {$submission->user_id}\n";
echo "  Assignment ID: {$submission->assignment_id}\n";
echo "  Status: {$submission->status}\n\n";

// Check for legacy file_paths column
echo "--- Legacy file_paths Column ---\n";
if (isset($submission->file_paths)) {
    echo "✓ file_paths column exists\n";
    
    if (is_array($submission->file_paths) && count($submission->file_paths) > 0) {
        echo "✓ Found " . count($submission->file_paths) . " file path(s):\n\n";
        
        foreach ($submission->file_paths as $index => $filePath) {
            echo "File " . ($index + 1) . ":\n";
            echo "  Path: {$filePath}\n";
            
            // Check if file exists in storage
            $exists = Storage::disk('local')->exists($filePath);
            echo "  Exists in storage: " . ($exists ? '✅ YES' : '❌ NO') . "\n";
            
            if ($exists) {
                $size = Storage::disk('local')->size($filePath);
                $mimeType = Storage::disk('local')->mimeType($filePath);
                echo "  Size: " . number_format($size) . " bytes\n";
                echo "  MIME type: {$mimeType}\n";
            }
            
            echo "\n";
        }
    } else {
        echo "⚠ file_paths is empty or not an array\n";
        echo "  Value: " . json_encode($submission->file_paths) . "\n";
    }
} else {
    echo "❌ file_paths column does not exist\n";
}

echo "\n--- Recommendation ---\n";
if (isset($submission->file_paths) && is_array($submission->file_paths) && count($submission->file_paths) > 0) {
    echo "This submission uses the LEGACY file storage system.\n";
    echo "The view should display these files using the legacy download route.\n";
    echo "\nLegacy download route:\n";
    echo "  Route: teacher.courses.modules.subpages.assignments.submissions.download\n";
    echo "  Controller: SubmissionController::downloadFile()\n";
} else {
    echo "This submission has NO files attached (neither legacy nor new system).\n";
    echo "The student may not have uploaded any files yet.\n";
}
