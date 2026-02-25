<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Grade;
use App\Models\Submission;

echo "=== Grade Publishing Script ===\n\n";

// Get submission ID 2
$submissionId = 2;
$submission = Submission::with('grade', 'user', 'assignment')->find($submissionId);

if (!$submission) {
    echo "❌ Submission $submissionId not found\n";
    exit(1);
}

echo "✓ Submission found\n";
echo "  Student: {$submission->user->name}\n";
echo "  Assignment: {$submission->assignment->title}\n\n";

if (!$submission->grade) {
    echo "❌ No grade found for this submission\n";
    exit(1);
}

$grade = $submission->grade;

echo "Grade Details:\n";
echo "  Score: {$grade->score} / {$submission->assignment->max_score}\n";
echo "  Letter Grade: {$grade->getLetterGrade()}\n";
echo "  Is Published: " . ($grade->is_published ? 'YES' : 'NO') . "\n";
echo "  Published At: " . ($grade->published_at ? $grade->published_at : 'Not published') . "\n\n";

if ($grade->is_published) {
    echo "✓ Grade is already published!\n";
} else {
    echo "Publishing grade...\n";
    $grade->publish();
    echo "✓ Grade published successfully!\n\n";
    
    // Reload to verify
    $grade->refresh();
    echo "Verification:\n";
    echo "  Is Published: " . ($grade->is_published ? 'YES' : 'NO') . "\n";
    echo "  Published At: " . ($grade->published_at ? $grade->published_at : 'Not published') . "\n";
}

echo "\n=== Complete ===\n";
