<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

$result = DB::select("SHOW COLUMNS FROM assignments WHERE Field = 'assignment_type'");

echo "Current assignment_type column definition:\n";
print_r($result);

if (!empty($result)) {
    echo "\nType: " . $result[0]->Type . "\n";
}
