<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

echo "=== Login Diagnostics ===\n\n";

// Check users count
$userCount = User::count();
echo "Total users in database: $userCount\n\n";

if ($userCount === 0) {
    echo "ERROR: No users found in database!\n";
    echo "You need to create a user first.\n";
    exit;
}

// Get first few users
$users = User::take(5)->get();

echo "Users in database:\n";
echo str_repeat("-", 80) . "\n";
printf("%-5s %-30s %-30s %-15s\n", "ID", "Name", "Email", "Has Password");
echo str_repeat("-", 80) . "\n";

foreach ($users as $user) {
    $hasPassword = !empty($user->password) ? 'Yes' : 'NO - PROBLEM!';
    printf("%-5s %-30s %-30s %-15s\n", 
        $user->id, 
        substr($user->name, 0, 30), 
        substr($user->email, 0, 30), 
        $hasPassword
    );
}

echo "\n";

// Check if passwords are hashed properly
$firstUser = User::first();
if ($firstUser) {
    echo "First user details:\n";
    echo "  Email: {$firstUser->email}\n";
    echo "  Name: {$firstUser->name}\n";
    echo "  Password hash: " . substr($firstUser->password, 0, 20) . "...\n";
    echo "  Password hash length: " . strlen($firstUser->password) . " characters\n";
    
    // Check if it's a bcrypt hash
    if (strlen($firstUser->password) === 60 && substr($firstUser->password, 0, 4) === '$2y$') {
        echo "  ✓ Password appears to be properly hashed (bcrypt)\n";
    } else {
        echo "  ✗ WARNING: Password doesn't look like a bcrypt hash!\n";
    }
    
    echo "\n";
    
    // Test password verification
    echo "Testing password verification:\n";
    $testPasswords = ['password', 'Password123', 'admin', 'admin123', '12345678'];
    
    foreach ($testPasswords as $testPass) {
        $matches = Hash::check($testPass, $firstUser->password);
        echo "  Testing '$testPass': " . ($matches ? "✓ MATCHES" : "✗ No match") . "\n";
    }
}

echo "\n";

// Check authentication configuration
echo "Authentication Configuration:\n";
echo "  Guard: " . config('auth.defaults.guard') . "\n";
echo "  Provider: " . config('auth.defaults.passwords') . "\n";
echo "  User Model: " . config('auth.providers.users.model') . "\n";
echo "  Bcrypt Rounds: " . config('hashing.bcrypt.rounds') . "\n";

echo "\n=== End Diagnostics ===\n";
