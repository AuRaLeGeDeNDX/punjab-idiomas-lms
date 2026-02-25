<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Content;
use App\Models\User;

echo "=== PDF Access Diagnostic Tool ===\n\n";

// Get content ID from command line or use default
$contentId = $argv[1] ?? 6;
$userId = $argv[2] ?? 1;

echo "Checking Content ID: {$contentId}\n";
echo "User ID: {$userId}\n\n";

// Load content
$content = Content::find($contentId);

if (!$content) {
    echo "ERROR: Content not found!\n";
    exit(1);
}

echo "Content Details:\n";
echo "  - ID: {$content->id}\n";
echo "  - Title: {$content->title}\n";
echo "  - Type: {$content->type}\n";
echo "  - Subpage ID: {$content->subpage_id}\n";
echo "  - Is Active: " . ($content->is_active ? 'Yes' : 'No') . "\n";
echo "  - File Path: {$content->file_path}\n";
echo "  - Storage Disk: {$content->storage_disk}\n\n";

// Check subpage
$subpage = $content->subpage;
if (!$subpage) {
    echo "ERROR: No subpage found for this content!\n";
    echo "  - Content has subpage_id: {$content->subpage_id}\n";
    echo "  - But subpage relationship returned null\n";
    exit(1);
}

echo "Subpage Details:\n";
echo "  - ID: {$subpage->id}\n";
echo "  - Title: {$subpage->title}\n";
echo "  - Module ID: {$subpage->module_id}\n\n";

// Check module
$module = $subpage->module;
if (!$module) {
    echo "ERROR: No module found for this subpage!\n";
    echo "  - Subpage has module_id: {$subpage->module_id}\n";
    echo "  - But module relationship returned null\n";
    exit(1);
}

echo "Module Details:\n";
echo "  - ID: {$module->id}\n";
echo "  - Title: {$module->title}\n";
echo "  - Course ID: {$module->course_id}\n\n";

// Check course
$course = $module->course;
if (!$course) {
    echo "ERROR: No course found for this module!\n";
    echo "  - Module has course_id: {$module->course_id}\n";
    echo "  - But course relationship returned null\n";
    exit(1);
}

echo "Course Details:\n";
echo "  - ID: {$course->id}\n";
echo "  - Title: {$course->title}\n";
echo "  - Teacher ID: {$course->teacher_id}\n\n";

// Check user
$user = User::find($userId);
if (!$user) {
    echo "ERROR: User not found!\n";
    exit(1);
}

echo "User Details:\n";
echo "  - ID: {$user->id}\n";
echo "  - Name: {$user->name}\n";
echo "  - Email: {$user->email}\n\n";

// Check roles
$roles = $user->roles->pluck('name')->toArray();
echo "User Roles: " . implode(', ', $roles) . "\n";
$isAdmin = $user->hasRole('Admin');
echo "  - Is Admin: " . ($isAdmin ? 'Yes' : 'No') . "\n";
$isTeacher = $course->teacher_id === $user->id;
echo "  - Is Teacher of this course: " . ($isTeacher ? 'Yes' : 'No') . "\n\n";

// Check enrollment
$enrollment = $user->enrollments()
    ->where('course_id', $course->id)
    ->where('status', 'active')
    ->first();

echo "Enrollment Status:\n";
if ($enrollment) {
    echo "  - Enrolled: Yes\n";
    echo "  - Enrollment ID: {$enrollment->id}\n";
    echo "  - Status: {$enrollment->status}\n";
} else {
    echo "  - Enrolled: No\n";
    
    // Check if there's any enrollment (even inactive)
    $anyEnrollment = $user->enrollments()
        ->where('course_id', $course->id)
        ->first();
    
    if ($anyEnrollment) {
        echo "  - Found inactive enrollment with status: {$anyEnrollment->status}\n";
    }
}

echo "\n";

// Final permission check
$canView = $isAdmin || $isTeacher || ($enrollment && $enrollment->status === 'active');
echo "=== FINAL RESULT ===\n";
echo "Can View PDF: " . ($canView ? 'YES' : 'NO') . "\n";

if (!$canView) {
    echo "\nREASON: ";
    if (!$isAdmin) echo "Not an admin. ";
    if (!$isTeacher) echo "Not the course teacher. ";
    if (!$enrollment) {
        echo "Not enrolled in the course.";
    } elseif ($enrollment->status !== 'active') {
        echo "Enrollment status is '{$enrollment->status}' (not active).";
    }
    echo "\n";
}

echo "\n";
