<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

$startTime = microtime(true);

define('LARAVEL_START', $startTime);

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$response = $app->handleRequest(Request::capture());

$executionTime = microtime(true) - $startTime;

file_put_contents(
    __DIR__.'/../storage/logs/performance.log',
    "Execution time: " . $executionTime . " seconds\n",
    FILE_APPEND
);

return $response;
