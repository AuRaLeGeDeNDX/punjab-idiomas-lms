<?php
$logFile = storage_path('logs/laravel.log');
if (file_exists($logFile)) {
    // Read the file backwards or just read all and filter
    // For 100MB file, reading all is bad.
    // Use tail approach via seeking
    $handle = fopen($logFile, "r");
    $pos = -2; // Start from end
    $lines = [];
    $limit = 200; // Find 200 matching lines
    $found = 0;
    
    // Quick and dirty: read last 1MB
    fseek($handle, -1024 * 1024, SEEK_END);
    $text = fread($handle, 1024 * 1024);
    $allLines = explode("\n", $text);
    
    $matches = [];
    foreach ($allLines as $line) {
        if (strpos($line, '.ERROR') !== false || strpos($line, 'exception') !== false) {
            $matches[] = $line;
        }
    }
    
    echo implode("\n", array_slice($matches, -20)); // Return last 20 errors
}
