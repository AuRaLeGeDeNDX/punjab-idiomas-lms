<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Publish Course ===\n\n";

$courseId = $argv[1] ?? 1;

$course = \App\Models\Course::find($courseId);

if (!$course) {
    echo "❌ Course with ID {$courseId} not found\n";
    exit(1);
}

echo "Course: {$course->title}\n";
echo "Current status: " . ($course->is_published ? 'Published' : 'Unpublished') . "\n\n";

if ($course->is_published) {
    echo "✓ Course is already published\n";
} else {
    $course->is_published = true;
    $course->save();
    echo "✓ Course has been published successfully!\n";
}

echo "\nYou can now access it at: http://127.0.0.1:8000/student/courses/{$courseId}\n";
