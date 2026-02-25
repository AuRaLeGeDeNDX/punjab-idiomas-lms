<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

try {
    $contentBlockService = $app->make(\App\Services\ContentBlockService::class);
    echo "ContentBlockService resolved successfully\n";
} catch (Exception $e) {
    echo "Error resolving ContentBlockService: " . $e->getMessage() . "\n";
}

try {
    $fileUploadLogger = $app->make(\App\Services\FileUploadLogger::class);
    echo "FileUploadLogger resolved successfully\n";
} catch (Exception $e) {
    echo "Error resolving FileUploadLogger: " . $e->getMessage() . "\n";
}

try {
    $secureFileStorage = $app->make(\App\Services\SecureFileStorageService::class);
    echo "SecureFileStorageService resolved successfully\n";
} catch (Exception $e) {
    echo "Error resolving SecureFileStorageService: " . $e->getMessage() . "\n";
}