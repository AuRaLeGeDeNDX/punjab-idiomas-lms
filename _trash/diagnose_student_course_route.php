<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Student Course Route Diagnosis ===\n\n";

// Check if course with ID 1 exists
$course = \App\Models\Course::find(1);

if (!$course) {
    echo "❌ Course with ID 1 does NOT exist in the database\n\n";
    
    // List all courses
    $allCourses = \App\Models\Course::all();
    echo "Available courses:\n";
    foreach ($allCourses as $c) {
        echo "  - ID: {$c->id}, Title: {$c->title}, Published: " . ($c->is_published ? 'Yes' : 'No') . "\n";
    }
} else {
    echo "✓ Course with ID 1 exists\n";
    echo "  Title: {$course->title}\n";
    echo "  Published: " . ($course->is_published ? 'Yes' : 'No') . "\n";
    echo "  Teacher: {$course->teacher->name}\n\n";
    
    // Check current user
    if (auth()->check()) {
        $user = auth()->user();
        echo "Current user: {$user->name} (ID: {$user->id})\n";
        echo "Roles: " . $user->roles->pluck('name')->implode(', ') . "\n\n";
        
        // Check if user can access this course
        if (!$course->is_published && !$user->hasRole(['Teacher', 'Admin'])) {
            echo "❌ Course is not published and user is not a teacher/admin\n";
            echo "   This will result in a 404 error\n";
        } else {
            echo "✓ User should be able to access this course\n";
        }
    } else {
        echo "❌ No user is currently authenticated\n";
        echo "   Please log in first\n";
    }
}

echo "\n=== End Diagnosis ===\n";
