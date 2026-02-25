<?php

/**
 * Diagnostic Script: Module API Authorization
 * 
 * This script helps diagnose 403 Forbidden errors when accessing module API endpoints.
 * It checks user authentication, roles, and course authorization.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\Gate;

echo "=== Module API Authorization Diagnostic ===\n\n";

// Get the authenticated user (simulating the logged-in admin)
echo "1. Checking Authentication:\n";
echo "   - Please enter the user ID to test: ";
$userId = trim(fgets(STDIN));

$user = User::find($userId);

if (!$user) {
    echo "   ❌ User not found with ID: {$userId}\n";
    exit(1);
}

echo "   ✓ User found: {$user->name} (ID: {$user->id})\n";
echo "   - Email: {$user->email}\n\n";

// Check user roles
echo "2. Checking User Roles:\n";
$roles = $user->getRoleNames();
echo "   - Roles: " . ($roles->isEmpty() ? 'None' : $roles->implode(', ')) . "\n";

if ($roles->isEmpty()) {
    echo "   ❌ User has no roles assigned!\n";
    echo "   - This is likely the cause of the 403 error.\n";
    echo "   - Please assign a role to this user (Admin, Teacher, or Student).\n\n";
} else {
    echo "   ✓ User has roles assigned\n\n";
}

// Check specific role methods
echo "3. Checking Role Methods:\n";
echo "   - hasRole('Admin'): " . ($user->hasRole('Admin') ? '✓ Yes' : '❌ No') . "\n";
echo "   - hasRole('Teacher'): " . ($user->hasRole('Teacher') ? '✓ Yes' : '❌ No') . "\n";
echo "   - hasRole('Student'): " . ($user->hasRole('Student') ? '✓ Yes' : '❌ No') . "\n\n";

// Check course authorization
echo "4. Checking Course Authorization:\n";
echo "   - Please enter the course ID to test: ";
$courseId = trim(fgets(STDIN));

$course = Course::find($courseId);

if (!$course) {
    echo "   ❌ Course not found with ID: {$courseId}\n";
    exit(1);
}

echo "   ✓ Course found: {$course->title} (ID: {$course->id})\n";
echo "   - Teacher ID: {$course->teacher_id}\n";
echo "   - Is Published: " . ($course->is_published ? 'Yes' : 'No') . "\n\n";

// Check authorization using Gate
echo "5. Checking Gate Authorization:\n";

try {
    // Simulate the user being authenticated
    auth()->login($user);
    
    // Check 'view' permission
    $canView = Gate::forUser($user)->allows('view', $course);
    echo "   - Can view course: " . ($canView ? '✓ Yes' : '❌ No') . "\n";
    
    // Check 'update' permission (used by module API)
    $canUpdate = Gate::forUser($user)->allows('update', $course);
    echo "   - Can update course: " . ($canUpdate ? '✓ Yes' : '❌ No') . "\n";
    
    // Check 'manageModules' permission
    $canManageModules = Gate::forUser($user)->allows('manageModules', $course);
    echo "   - Can manage modules: " . ($canManageModules ? '✓ Yes' : '❌ No') . "\n\n";
    
    if (!$canUpdate) {
        echo "6. Diagnosis:\n";
        echo "   ❌ User cannot update this course!\n";
        echo "   - This is why the API returns 403 Forbidden.\n\n";
        
        echo "   Possible causes:\n";
        if ($roles->isEmpty()) {
            echo "   1. User has no roles assigned (most likely)\n";
        } elseif (!$user->hasRole('Admin') && $course->teacher_id !== $user->id) {
            echo "   1. User is not an Admin\n";
            echo "   2. User is not the teacher of this course (teacher_id mismatch)\n";
        }
        
        echo "\n   Solutions:\n";
        if ($roles->isEmpty()) {
            echo "   - Assign the 'Admin' role to this user\n";
            echo "   - Command: php artisan tinker\n";
            echo "   - Then run: User::find({$userId})->assignRole('Admin')\n";
        } elseif (!$user->hasRole('Admin')) {
            echo "   - Either assign the 'Admin' role to this user\n";
            echo "   - Or update the course's teacher_id to {$user->id}\n";
        }
    } else {
        echo "6. Diagnosis:\n";
        echo "   ✓ User CAN update this course!\n";
        echo "   - Authorization should work correctly.\n";
        echo "   - If you're still getting 403 errors, check:\n";
        echo "     1. Session/cookie issues\n";
        echo "     2. CSRF token issues\n";
        echo "     3. Middleware configuration\n";
    }
    
} catch (\Exception $e) {
    echo "   ❌ Error checking authorization: " . $e->getMessage() . "\n";
}

echo "\n=== Diagnostic Complete ===\n";
