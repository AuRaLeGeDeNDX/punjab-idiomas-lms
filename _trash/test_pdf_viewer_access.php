<?php
/**
 * Test PDF Viewer Access
 * 
 * This script tests accessing the PDF viewer as an authenticated user
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== PDF Viewer Access Test ===\n\n";

// Get content
$contentId = $argv[1] ?? 7;
$content = \App\Models\Content::find($contentId);

if (!$content) {
    echo "❌ Content not found: {$contentId}\n";
    exit(1);
}

echo "Content: {$content->title} (ID: {$content->id})\n";

// Get a user (try to find an admin)
$user = \App\Models\User::whereHas('roles', function($q) {
    $q->where('name', 'Admin');
})->first();

if (!$user) {
    // Fallback to any user
    $user = \App\Models\User::first();
}

if (!$user) {
    echo "❌ No users found in database\n";
    exit(1);
}

echo "User: {$user->name} (ID: {$user->id})\n";
echo "Roles: " . $user->roles->pluck('name')->join(', ') . "\n\n";

// Check permissions
$service = app(\App\Services\SecurePdfService::class);
$canView = $service->canView($content, $user);

echo "Can view PDF: " . ($canView ? "✅ YES" : "❌ NO") . "\n\n";

if (!$canView) {
    echo "Checking relationships...\n";
    $subpage = $content->subpage;
    echo "- Subpage: " . ($subpage ? "✅ Found (ID: {$subpage->id})" : "❌ Not found") . "\n";
    
    if ($subpage) {
        $module = $subpage->module;
        echo "- Module: " . ($module ? "✅ Found (ID: {$module->id})" : "❌ Not found") . "\n";
        
        if ($module) {
            $course = $module->course;
            echo "- Course: " . ($course ? "✅ Found (ID: {$course->id})" : "❌ Not found") . "\n";
            
            if ($course) {
                $isTeacher = $course->teacher_id === $user->id;
                echo "- Is teacher: " . ($isTeacher ? "YES" : "NO") . "\n";
                
                $isEnrolled = $user->enrollments()
                    ->where('course_id', $course->id)
                    ->where('status', 'active')
                    ->exists();
                echo "- Is enrolled: " . ($isEnrolled ? "YES" : "NO") . "\n";
            }
        }
    }
    echo "\n";
}

// Generate viewer URL
$viewerUrl = $service->generateViewerUrl($content, $user);
echo "Viewer URL:\n{$viewerUrl}\n\n";

// Generate stream URL
$streamUrl = $service->generateSecureUrl($content, 10);
echo "Stream URL:\n{$streamUrl}\n\n";

echo "=== Test Complete ===\n";
echo "\nTo test in browser:\n";
echo "1. Login as: {$user->email}\n";
echo "2. Visit: {$viewerUrl}\n";
