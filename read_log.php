<?php

$logFile = 'storage/logs/laravel.log';

if (!file_exists($logFile)) {
    echo "Log file not found\n";
    exit(1);
}

// Read last 200 lines
$lines = file($logFile);
$lastLines = array_slice($lines, -200);

// Filter for relevant lines
$relevantLines = [];
foreach ($lastLines as $line) {
    if (stripos($line, 'File upload') !== false || 
        stripos($line, 'ContentBlock') !== false || 
        stripos($line, 'correlation_id') !== false ||
        stripos($line, 'Empty or invalid file path') !== false ||
        stripos($line, 'does not exist or is not readable') !== false) {
        $relevantLines[] = $line;
    }
}

if (empty($relevantLines)) {
    echo "No relevant log entries found in last 200 lines\n";
    echo "\nShowing last 20 lines instead:\n";
    echo implode('', array_slice($lines, -20));
} else {
    echo "Found " . count($relevantLines) . " relevant log entries:\n\n";
    echo implode('', $relevantLines);
}
