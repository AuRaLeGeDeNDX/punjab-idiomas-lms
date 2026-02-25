<?php

$phpIniPath = 'C:\php\php.ini';

if (!file_exists($phpIniPath)) {
    echo "ERROR: php.ini file not found at: $phpIniPath\n";
    exit(1);
}

// Read the current php.ini
$content = file_get_contents($phpIniPath);

// Backup the original file
$backupPath = $phpIniPath . '.backup_' . date('Y-m-d_H-i-s');
file_put_contents($backupPath, $content);
echo "✓ Backup created: $backupPath\n\n";

// Update upload_max_filesize
if (preg_match('/^upload_max_filesize\s*=\s*.+$/m', $content)) {
    $content = preg_replace('/^upload_max_filesize\s*=\s*.+$/m', 'upload_max_filesize = 500M', $content);
    echo "✓ Updated upload_max_filesize to 500M\n";
} else {
    $content .= "\nupload_max_filesize = 500M\n";
    echo "✓ Added upload_max_filesize = 500M\n";
}

// Update post_max_size
if (preg_match('/^post_max_size\s*=\s*.+$/m', $content)) {
    $content = preg_replace('/^post_max_size\s*=\s*.+$/m', 'post_max_size = 500M', $content);
    echo "✓ Updated post_max_size to 500M\n";
} else {
    $content .= "post_max_size = 500M\n";
    echo "✓ Added post_max_size = 500M\n";
}

// Update memory_limit
if (preg_match('/^memory_limit\s*=\s*.+$/m', $content)) {
    $content = preg_replace('/^memory_limit\s*=\s*.+$/m', 'memory_limit = 512M', $content);
    echo "✓ Updated memory_limit to 512M\n";
} else {
    $content .= "\nmemory_limit = 512M\n";
    echo "✓ Added memory_limit = 512M\n";
}

// Update max_execution_time
if (preg_match('/^max_execution_time\s*=\s*.+$/m', $content)) {
    $content = preg_replace('/^max_execution_time\s*=\s*.+$/m', 'max_execution_time = 300', $content);
    echo "✓ Updated max_execution_time to 300\n";
} else {
    $content .= "max_execution_time = 300\n";
    echo "✓ Added max_execution_time = 300\n";
}

// Update max_input_time
if (preg_match('/^max_input_time\s*=\s*.+$/m', $content)) {
    $content = preg_replace('/^max_input_time\s*=\s*.+$/m', 'max_input_time = 300', $content);
    echo "✓ Updated max_input_time to 300\n";
} else {
    $content .= "max_input_time = 300\n";
    echo "✓ Added max_input_time = 300\n";
}

// Write the updated content
file_put_contents($phpIniPath, $content);

echo "\n✅ php.ini updated successfully!\n";
echo "\n⚠️  IMPORTANT: You must restart your Laravel server for changes to take effect.\n";
echo "   1. Stop the current server (Ctrl+C)\n";
echo "   2. Run: php artisan serve\n";
