<?php

// Simple test script to debug file upload
// Access via: http://127.0.0.1:8000/test_file_upload_debug.php

echo "<h1>File Upload Debug</h1>";

echo "<h2>POST Data:</h2>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

echo "<h2>FILES Data:</h2>";
echo "<pre>";
print_r($_FILES);
echo "</pre>";

echo "<h2>Request Headers:</h2>";
echo "<pre>";
foreach (getallheaders() as $name => $value) {
    echo "$name: $value\n";
}
echo "</pre>";

echo "<h2>PHP Info:</h2>";
echo "<pre>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "file_uploads: " . (ini_get('file_uploads') ? 'On' : 'Off') . "\n";
echo "upload_tmp_dir: " . (ini_get('upload_tmp_dir') ?: sys_get_temp_dir()) . "\n";
echo "</pre>";

// Test form
?>
<h2>Test Upload Form:</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="file" accept=".mp4,.webm,.ogg,.mov">
    <input type="text" name="title" placeholder="Title" value="Test Video">
    <input type="hidden" name="type" value="video">
    <button type="submit">Upload Test</button>
</form>
