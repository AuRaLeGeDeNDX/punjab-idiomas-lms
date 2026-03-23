<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::find(1);
echo "User: " . $user->name . "\n";
echo "Roles: " . json_encode($user->getRoleNames()) . "\n";
echo "hasRole('admin'): " . ($user->hasRole('admin') ? 'YES' : 'NO') . "\n";
echo "hasRole('Admin'): " . ($user->hasRole('Admin') ? 'YES' : 'NO') . "\n";

echo "\nAll Users:\n";
foreach(App\Models\User::all() as $u) {
    echo $u->id . " -> " . $u->name . " -> roles: " . json_encode($u->getRoleNames()) . "\n";
}
