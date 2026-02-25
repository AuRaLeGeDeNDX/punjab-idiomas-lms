<?php
$file = 'storage/logs/laravel.log';
$size = filesize($file);
$read = min($size, 51200); // Read last 50KB
$handle = fopen($file, 'r');
fseek($handle, -$read, SEEK_END);
echo fread($handle, $read);
fclose($handle);
