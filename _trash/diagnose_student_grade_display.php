<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Grade;
use App\Models\User;

echo "=== Student Grade Display Diagnostic ===\n\n";

// Get the assignment from the URL
$assignmentId = 2;
$assignment = Assignment::with(['course', 'module', 'subpage'])->find($assignmentId);

if (!$assignment) {
    echo "❌ Assignment not found with ID: {$assignmentId}\n";
    exit(1);
}

echo "Assignment: {$assignment->title}\n";
echo "Course: {$assignment->course->title}\n";
echo "Module: {$assignment->module->title}\n";
echo "Subpage: {$assignment->subpage->title}\n\n";

// Get all submissions for this assignment
$submissions = Submission::where('assignment_id', $assignmentId)
    ->with(['user', 'grade'])
    ->get();

echo "=== Submissions for this assignment ===\n";
echo "Total submissions: " . $submissions->count() . "\n\n";

foreach ($submissions as $submission) {
    echo "Student: {$submission->user->name} (ID: {$submission->user->id})\n";
    echo "Submission ID: {$submission->id}\n";
    echo "Submitted at: {$submission->submitted_at}\n";
    echo "Status: {$submission->status}\n";
    
    if ($submission->grade) {
        echo "Grade exists: YES\n";
        echo "  Grade ID: {$submission->grade->id}\n";
        echo "  Score: {$submission->grade->score}\n";
        echo "  Is Published: " . ($submission->grade->is_published ? 'YES' : 'NO') . "\n";
        echo "  Published at: " . ($submission->grade->published_at ? $submission->grade->published_at : 'Not published') . "\n";
        echo "  Feedback: " . ($submission->grade->feedback ? substr($submission->grade->feedback, 0, 50) . '...' : 'None') . "\n";
    } else {
        echo "Grade exists: NO\n";
    }
    echo "\n";
}

// Test the submissionFor method
echo "=== Testing submissionFor method ===\n";
$student = User::whereHas('submissions', function($q) use ($assignmentId) {
    $q->where('assignment_id', $assignmentId);
})->first();

if ($student) {
    echo "Testing with student: {$student->name} (ID: {$student->id})\n";
    $submission = $assignment->submissionFor($student);
    
    if ($submission) {
        echo "✓ Submission found via submissionFor()\n";
        echo "  Submission ID: {$submission->id}\n";
        
        if ($submission->grade) {
            echo "  ✓ Grade loaded via submissionFor()\n";
            echo "    Grade ID: {$submission->grade->id}\n";
            echo "    Score: {$submission->grade->score}\n";
            echo "    Is Published: " . ($submission->grade->is_published ? 'YES' : 'NO') . "\n";
        } else {
            echo "  ❌ Grade NOT loaded via submissionFor()\n";
        }
    } else {
        echo "❌ Submission not found via submissionFor()\n";
    }
} else {
    echo "No student found with submissions for this assignment\n";
}

echo "\n=== Direct Grade Query ===\n";
$grades = Grade::whereHas('submission', function($q) use ($assignmentId) {
    $q->where('assignment_id', $assignmentId);
})->with('submission.user')->get();

echo "Total grades: " . $grades->count() . "\n\n";

foreach ($grades as $grade) {
    echo "Grade ID: {$grade->id}\n";
    echo "Student: {$grade->submission->user->name}\n";
    echo "Score: {$grade->score}\n";
    echo "Is Published: " . ($grade->is_published ? 'YES' : 'NO') . "\n";
    echo "Published at: " . ($grade->published_at ? $grade->published_at : 'Not published') . "\n";
    echo "\n";
}

echo "=== Diagnostic Complete ===\n";
