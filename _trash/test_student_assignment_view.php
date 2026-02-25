<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Assignment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

echo "=== Testing Student Assignment View Data ===\n\n";

// Simulate the student user
$student = User::find(3); // Student User from diagnostic
if (!$student) {
    echo "❌ Student not found\n";
    exit(1);
}

echo "Student: {$student->name} (ID: {$student->id})\n\n";

// Get the assignment
$assignmentId = 2;
$assignment = Assignment::with(['course', 'module', 'subpage'])->find($assignmentId);

if (!$assignment) {
    echo "❌ Assignment not found\n";
    exit(1);
}

echo "Assignment: {$assignment->title}\n\n";

// Simulate what the controller does
echo "=== Simulating Controller Logic ===\n";
$submission = $assignment->submissionFor($student);

if ($submission) {
    echo "✓ Submission found\n";
    echo "  Submission ID: {$submission->id}\n";
    echo "  Status: {$submission->status}\n";
    
    // Check if grade relationship is loaded
    if ($submission->relationLoaded('grade')) {
        echo "  ✓ Grade relationship is loaded\n";
    } else {
        echo "  ❌ Grade relationship is NOT loaded\n";
    }
    
    // Check grade
    if ($submission->grade) {
        echo "  ✓ Grade exists\n";
        echo "    Grade ID: {$submission->grade->id}\n";
        echo "    Score: {$submission->grade->score}\n";
        echo "    Max Score: {$assignment->max_score}\n";
        echo "    Is Published: " . ($submission->grade->is_published ? 'YES' : 'NO') . "\n";
        echo "    Published At: " . ($submission->grade->published_at ? $submission->grade->published_at->format('M j, Y g:i A') : 'Not published') . "\n";
        
        // Test the view conditions
        echo "\n  === View Condition Tests ===\n";
        echo "  \$submission->grade exists: " . ($submission->grade ? 'TRUE' : 'FALSE') . "\n";
        echo "  \$submission->grade->is_published: " . ($submission->grade->is_published ? 'TRUE' : 'FALSE') . "\n";
        echo "  Condition (\$submission->grade && \$submission->grade->is_published): " . (($submission->grade && $submission->grade->is_published) ? 'TRUE' : 'FALSE') . "\n";
        
        if ($submission->grade && $submission->grade->is_published) {
            echo "\n  ✓ Grade SHOULD be displayed to student\n";
            echo "    Display: {$submission->grade->score} / {$assignment->max_score}\n";
            echo "    Letter Grade: {$submission->grade->getLetterGrade()}\n";
            if ($submission->grade->feedback) {
                echo "    Feedback: " . substr($submission->grade->feedback, 0, 50) . "...\n";
            }
        } else {
            echo "\n  ❌ Grade should NOT be displayed (not published)\n";
        }
    } else {
        echo "  ❌ No grade exists\n";
    }
} else {
    echo "❌ No submission found\n";
}

echo "\n=== Test Complete ===\n";
