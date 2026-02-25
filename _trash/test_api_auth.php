<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Course;
use Illuminate\Support\Facades\Gate;

$user = User::find(1);
$course = Course::find(1);

echo "=== Testing Authorization ===\n\n";
echo "User: {$user->name}\n";
echo "Roles: " . $user->getRoleNames()->implode(', ') . "\n";
echo "Course: {$course->title}\n";
echo "Course Teacher ID: {$course->teacher_id}\n\n";

// Simulate authentication
auth()->login($user);

echo "Testing Gate authorization:\n";
echo "- Can view course: " . (Gate::forUser($user)->allows('view', $course) ? 'YES' : 'NO') . "\n";
echo "- Can update course: " . (Gate::forUser($user)->allows('update', $course) ? 'YES' : 'NO') . "\n";
echo "- Can manageModules: " . (Gate::forUser($user)->allows('manageModules', $course) ? 'YES' : 'NO') . "\n";

echo "\nDirect policy check:\n";
$policy = app(\App\Policies\CoursePolicy::class);
echo "- manageModules result: " . ($policy->manageModules($user, $course) ? 'TRUE' : 'FALSE') . "\n";

echo "\nUser hasRole checks:\n";
echo "- hasRole('Admin'): " . ($user->hasRole('Admin') ? 'YES' : 'NO') . "\n";
echo "- hasRole(['Teacher', 'Admin']): " . ($user->hasRole(['Teacher', 'Admin']) ? 'YES' : 'NO') . "\n";

echo "\n=== Test Complete ===\n";
