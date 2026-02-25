<?php
// Lightweight DB diagnostics using the app bootstrap
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "DB_USERNAME: " . (env('DB_USERNAME') ?? '<null>') . PHP_EOL;
echo "DB_PASSWORD: " . ((env('DB_PASSWORD') === '') ? '<empty>' : env('DB_PASSWORD')) . PHP_EOL;

try {
    DB::connection()->getPdo();
    echo "DB Connection: SUCCESS" . PHP_EOL;
} catch (Exception $e) {
    echo "DB Connection Error: " . get_class($e) . " - " . $e->getMessage() . PHP_EOL;
}
