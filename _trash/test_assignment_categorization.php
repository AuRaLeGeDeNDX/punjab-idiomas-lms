<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Assignment;
use App\Models\User;

echo "=== Assignment Categorization Test ===\n\n";

$student = User::whereHas('roles', function($q) {
    $q->where('name', 'Student');
})->first();

if (!$student) {
    echo "No student found!\n";
    exit;
}

echo "Testing with student: {$student->name}\n\n";

$assignments = Assignment::whereHas('course.enrollments', function ($query) use ($student) {
    $query->where('user_id', $student->id)->where('status', 'active');
})
->where('is_published', true)
->with([
    'course',
    'module',
    'subpage',
    'submissions' => function ($query) use ($student) {
        $query->where('user_id', $student->id);
    }
])
->orderBy('due_date', 'asc')
->get();

echo "Total Published Assignments: " . $assignments->count() . "\n\n";

foreach ($assignments as $assignment) {
    echo "Assignment: {$assignment->title}\n";
    echo "Due Date: " . ($assignment->due_date ? $assignment->due_date->format('Y-m-d H:i:s') : 'NULL') . "\n";
    echo "Has Submission: " . ($assignment->hasSubmissionFrom($student) ? 'YES' : 'NO') . "\n";
    
    // Check categorization
    $isUpcoming = $assignment->due_date && $assignment->due_date->isFuture() && !$assignment->hasSubmissionFrom($student);
    $isOverdue = $assignment->due_date && $assignment->due_date->isPast() && !$assignment->hasSubmissionFrom($student);
    $isSubmitted = $assignment->hasSubmissionFrom($student);
    
    echo "Category:\n";
    echo "  - Upcoming: " . ($isUpcoming ? 'YES' : 'NO') . "\n";
    echo "  - Overdue: " . ($isOverdue ? 'YES' : 'NO') . "\n";
    echo "  - Submitted: " . ($isSubmitted ? 'YES' : 'NO') . "\n";
    
    if (!$isUpcoming && !$isOverdue && !$isSubmitted) {
        echo "  *** NOT IN ANY CATEGORY - WILL NOT BE DISPLAYED ***\n";
    }
    
    echo "---\n\n";
}

echo "=== Test Complete ===\n";
