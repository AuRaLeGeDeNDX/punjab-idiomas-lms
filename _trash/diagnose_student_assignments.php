<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Assignment;
use App\Models\User;
use App\Models\Enrollment;

echo "=== Student Assignment Visibility Diagnostic ===\n\n";

// Get all assignments
$assignments = Assignment::with(['course', 'module', 'subpage'])->get();

echo "Total Assignments in Database: " . $assignments->count() . "\n\n";

foreach ($assignments as $assignment) {
    echo "Assignment ID: {$assignment->id}\n";
    echo "Title: {$assignment->title}\n";
    echo "Course: {$assignment->course->title}\n";
    echo "Module: {$assignment->module->title}\n";
    echo "Subpage: {$assignment->subpage->title}\n";
    echo "Is Published: " . ($assignment->is_published ? 'YES' : 'NO') . "\n";
    echo "Is Active: " . ($assignment->is_active ? 'YES' : 'NO') . "\n";
    echo "Published At: " . ($assignment->published_at ? $assignment->published_at->format('Y-m-d H:i:s') : 'NULL') . "\n";
    echo "Scheduled Publish At: " . ($assignment->scheduled_publish_at ? $assignment->scheduled_publish_at->format('Y-m-d H:i:s') : 'NULL') . "\n";
    echo "Due Date: " . ($assignment->due_date ? $assignment->due_date->format('Y-m-d H:i:s') : 'NULL') . "\n";
    echo "---\n\n";
}

// Get students
$students = User::whereHas('roles', function($q) {
    $q->where('name', 'Student');
})->get();

echo "\nTotal Students: " . $students->count() . "\n\n";

foreach ($students as $student) {
    echo "Student: {$student->name} (ID: {$student->id})\n";
    
    // Get enrollments
    $enrollments = Enrollment::where('user_id', $student->id)
        ->where('status', 'active')
        ->with('course')
        ->get();
    
    echo "Active Enrollments: " . $enrollments->count() . "\n";
    foreach ($enrollments as $enrollment) {
        echo "  - {$enrollment->course->title} (Course ID: {$enrollment->course_id})\n";
    }
    
    // Get assignments visible to this student
    $visibleAssignments = Assignment::whereHas('course.enrollments', function ($query) use ($student) {
        $query->where('user_id', $student->id)->where('status', 'active');
    })
    ->where('is_published', true)
    ->get();
    
    echo "Visible Published Assignments: " . $visibleAssignments->count() . "\n";
    foreach ($visibleAssignments as $assignment) {
        echo "  - {$assignment->title} (ID: {$assignment->id})\n";
    }
    
    echo "---\n\n";
}

echo "\n=== Checking Assignment Query Logic ===\n\n";

// Test the exact query from the controller
$testStudent = $students->first();
if ($testStudent) {
    echo "Testing with student: {$testStudent->name}\n\n";
    
    $testAssignments = Assignment::whereHas('course.enrollments', function ($query) use ($testStudent) {
        $query->where('user_id', $testStudent->id)->where('status', 'active');
    })
    ->where('is_published', true)
    ->with([
        'course',
        'module',
        'subpage',
        'submissions' => function ($query) use ($testStudent) {
            $query->where('user_id', $testStudent->id);
        }
    ])
    ->orderBy('due_date', 'asc')
    ->get();
    
    echo "Query Result Count: " . $testAssignments->count() . "\n";
    foreach ($testAssignments as $assignment) {
        echo "  - {$assignment->title} (Course: {$assignment->course->title})\n";
    }
}

echo "\n=== Diagnostic Complete ===\n";
