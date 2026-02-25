<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

echo "=== Grade Loading Diagnostic ===\n\n";

// Get assignment ID 2
$assignmentId = 2;
$assignment = Assignment::find($assignmentId);

if (!$assignment) {
    echo "❌ Assignment $assignmentId not found\n";
    exit(1);
}

echo "✓ Assignment found: {$assignment->title}\n";
echo "  Course ID: {$assignment->course_id}\n";
echo "  Module ID: {$assignment->module_id}\n";
echo "  Subpage ID: {$assignment->subpage_id}\n\n";

// Get submissions for this assignment
$submissions = $assignment->submissions()->with('grade', 'user')->get();

echo "Total submissions: " . $submissions->count() . "\n\n";

foreach ($submissions as $submission) {
    echo "--- Submission ID: {$submission->id} ---\n";
    echo "  Student: {$submission->user->name} (ID: {$submission->user_id})\n";
    echo "  Submitted at: {$submission->submitted_at}\n";
    echo "  Status: {$submission->status}\n";
    
    if ($submission->grade) {
        echo "  ✓ Grade found:\n";
        echo "    - Score: {$submission->grade->score}\n";
        echo "    - Max Score: {$assignment->max_score}\n";
        echo "    - Letter Grade: {$submission->grade->getLetterGrade()}\n";
        echo "    - Is Published: " . ($submission->grade->is_published ? 'YES' : 'NO') . "\n";
        echo "    - Published At: " . ($submission->grade->published_at ? $submission->grade->published_at : 'Not published') . "\n";
        echo "    - Feedback: " . ($submission->grade->feedback ? substr($submission->grade->feedback, 0, 50) . '...' : 'None') . "\n";
    } else {
        echo "  ❌ No grade found\n";
    }
    echo "\n";
}

// Test the submissionFor method
echo "=== Testing submissionFor method ===\n";
$student = User::whereHas('enrollments', function($q) use ($assignment) {
    $q->where('course_id', $assignment->course_id);
})->first();

if ($student) {
    echo "Testing with student: {$student->name} (ID: {$student->id})\n";
    $submission = $assignment->submissionFor($student);
    
    if ($submission) {
        echo "✓ Submission found via submissionFor()\n";
        echo "  Submission ID: {$submission->id}\n";
        
        // Check if grade relationship is loaded
        if ($submission->relationLoaded('grade')) {
            echo "  ✓ Grade relationship is LOADED\n";
            if ($submission->grade) {
                echo "    - Score: {$submission->grade->score}\n";
                echo "    - Is Published: " . ($submission->grade->is_published ? 'YES' : 'NO') . "\n";
            } else {
                echo "    - No grade exists\n";
            }
        } else {
            echo "  ❌ Grade relationship is NOT LOADED (this is the problem!)\n";
        }
    } else {
        echo "❌ No submission found for this student\n";
    }
} else {
    echo "❌ No enrolled student found\n";
}

echo "\n=== Diagnostic Complete ===\n";
