<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$user = User::find(1);
if ($user) {
    echo "User: " . $user->name . "\n";
    echo "Email: " . $user->email . "\n";
    $roles = $user->getRoleNames();
    echo "Roles: " . ($roles->isEmpty() ? 'NONE - THIS IS THE PROBLEM!' : $roles->implode(', ')) . "\n";
    echo "\nhasRole('Admin'): " . ($user->hasRole('Admin') ? 'YES' : 'NO') . "\n";
} else {
    echo "User not found\n";
}

echo "\n--- Role Assignments ---\n";
$assignments = DB::table('model_has_roles')->where('model_id', 1)->get();
if ($assignments->isEmpty()) {
    echo "NO ROLES ASSIGNED TO USER 1\n";
    echo "\nTO FIX: Run this command:\n";
    echo "php artisan tinker\n";
    echo "Then: User::find(1)->assignRole('Admin')\n";
} else {
    foreach ($assignments as $a) {
        echo "Role ID: {$a->role_id}\n";
    }
}
