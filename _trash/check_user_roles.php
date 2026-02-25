<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;

echo "=== Checking User Roles and Authorization ===\n\n";

// Get all users
$users = User::all();

echo "Users in database:\n";
foreach ($users as $user) {
    echo "  ID: {$user->id} | Name: {$user->name} | Email: {$user->email}\n";
    $roles = $user->getRoleNames();
    echo "  Roles: " . ($roles->isEmpty() ? 'NONE' : $roles->implode(', ')) . "\n";
    echo "  hasRole('Admin'): " . ($user->hasRole('Admin') ? 'YES' : 'NO') . "\n";
    echo "  hasRole('Teacher'): " . ($user->hasRole('Teacher') ? 'YES' : 'NO') . "\n";
    echo "  hasRole('Student'): " . ($user->hasRole('Student') ? 'YES' : 'NO') . "\n";
    echo "\n";
}

// Check courses
echo "\nCourses in database:\n";
$courses = Course::all();
foreach ($courses as $course) {
    echo "  ID: {$course->id} | Title: {$course->title} | Teacher ID: {$course->teacher_id}\n";
}

// Check role_user table
echo "\nRole assignments (model_has_roles table):\n";
$roleAssignments = DB::table('model_has_roles')->get();
if ($roleAssignments->isEmpty()) {
    echo "  NO ROLE ASSIGNMENTS FOUND!\n";
} else {
    foreach ($roleAssignments as $assignment) {
        echo "  Role ID: {$assignment->role_id} | Model: {$assignment->model_type} | Model ID: {$assignment->model_id}\n";
    }
}

// Check roles table
echo "\nRoles in database:\n";
$roles = DB::table('roles')->get();
foreach ($roles as $role) {
    echo "  ID: {$role->id} | Name: {$role->name}\n";
}

echo "\n=== Check Complete ===\n";
