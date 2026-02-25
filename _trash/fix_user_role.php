<?php

/**
 * Quick Fix Script: Assign Admin Role to User
 * 
 * This script assigns the Admin role to a user to fix 403 authorization errors.
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Spatie\Permission\Models\Role;

echo "=== Fix User Role - Assign Admin ===\n\n";

// Get user ID
echo "Enter the user ID to assign Admin role: ";
$userId = trim(fgets(STDIN));

$user = User::find($userId);

if (!$user) {
    echo "❌ User not found with ID: {$userId}\n";
    exit(1);
}

echo "✓ User found: {$user->name} (ID: {$user->id})\n";
echo "  Email: {$user->email}\n\n";

// Check current roles
$currentRoles = $user->getRoleNames();
echo "Current roles: " . ($currentRoles->isEmpty() ? 'None' : $currentRoles->implode(', ')) . "\n\n";

// Check if Admin role exists
$adminRole = Role::where('name', 'Admin')->first();

if (!$adminRole) {
    echo "❌ Admin role does not exist in the database!\n";
    echo "Creating Admin role...\n";
    $adminRole = Role::create(['name' => 'Admin']);
    echo "✓ Admin role created\n\n";
}

// Assign Admin role
if ($user->hasRole('Admin')) {
    echo "✓ User already has Admin role\n";
} else {
    echo "Assigning Admin role to user...\n";
    $user->assignRole('Admin');
    echo "✓ Admin role assigned successfully!\n\n";
}

// Verify
$user->refresh();
$newRoles = $user->getRoleNames();
echo "Updated roles: " . $newRoles->implode(', ') . "\n\n";

// Clear cache
echo "Clearing cache...\n";
Artisan::call('cache:clear');
Artisan::call('config:clear');
echo "✓ Cache cleared\n\n";

echo "=== Fix Complete ===\n";
echo "The user now has Admin role and should be able to access module API endpoints.\n";
echo "Please refresh your browser to apply the changes.\n";
