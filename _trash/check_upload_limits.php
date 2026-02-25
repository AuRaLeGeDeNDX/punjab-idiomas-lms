<?php

echo "=== PHP Upload Configuration ===\n\n";

echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . " seconds\n";
echo "max_input_time: " . ini_get('max_input_time') . " seconds\n";

echo "\n=== Converted to Bytes ===\n\n";

function convertToBytes($value) {
    $value = trim($value);
    $last = strtolower($value[strlen($value)-1]);
    $value = (int) $value;
    
    switch($last) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }
    
    return $value;
}

$uploadMax = convertToBytes(ini_get('upload_max_filesize'));
$postMax = convertToBytes(ini_get('post_max_size'));

echo "upload_max_filesize: " . number_format($uploadMax) . " bytes (" . round($uploadMax / 1024 / 1024, 2) . " MB)\n";
echo "post_max_size: " . number_format($postMax) . " bytes (" . round($postMax / 1024 / 1024, 2) . " MB)\n";

echo "\n=== Laravel Validation ===\n\n";
echo "Laravel validation rule: max:51200 (50 MB)\n";
echo "Your file size: 34 MB\n";

if ($uploadMax < (34 * 1024 * 1024)) {
    echo "\n⚠️  WARNING: upload_max_filesize is TOO SMALL for 34MB file!\n";
    echo "You need to increase it to at least 50M in php.ini\n";
}

if ($postMax < (34 * 1024 * 1024)) {
    echo "\n⚠️  WARNING: post_max_size is TOO SMALL for 34MB file!\n";
    echo "You need to increase it to at least 50M in php.ini\n";
}

echo "\n=== Recommendation ===\n\n";
echo "To fix this issue, update your php.ini file with:\n";
echo "upload_max_filesize = 50M\n";
echo "post_max_size = 50M\n";
echo "memory_limit = 256M\n";
echo "max_execution_time = 300\n";
