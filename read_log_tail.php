<?php
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    $lines = file($logFile);
    $tail = array_slice($lines, -100);
    echo implode("", $tail);
} else {
    echo "Log file not found at $logFile";
}
