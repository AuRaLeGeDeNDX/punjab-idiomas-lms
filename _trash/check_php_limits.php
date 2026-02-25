<?php

echo "PHP Upload Configuration:\n";
echo "========================\n\n";

echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "max_input_time: " . ini_get('max_input_time') . "\n";
echo "file_uploads: " . (ini_get('file_uploads') ? 'On' : 'Off') . "\n";
echo "upload_tmp_dir: " . (ini_get('upload_tmp_dir') ?: sys_get_temp_dir()) . "\n";

echo "\nConverted to bytes:\n";
echo "upload_max_filesize: " . convertToBytes(ini_get('upload_max_filesize')) . " bytes\n";
echo "post_max_size: " . convertToBytes(ini_get('post_max_size')) . " bytes\n";

echo "\nTest file size: 4125197 bytes (3.93 MB)\n";
echo "Will fit in upload_max_filesize: " . (4125197 < convertToBytes(ini_get('upload_max_filesize')) ? 'YES' : 'NO') . "\n";
echo "Will fit in post_max_size: " . (4125197 < convertToBytes(ini_get('post_max_size')) ? 'YES' : 'NO') . "\n";

function convertToBytes($size) {
    $size = trim($size);
    $last = strtolower($size[strlen($size) - 1]);
    $size = (int) $size;
    
    switch ($last) {
        case 'g':
            $size *= 1024;
        case 'm':
            $size *= 1024;
        case 'k':
            $size *= 1024;
    }
    
    return $size;
}
