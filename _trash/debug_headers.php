<?php

require_once 'vendor/autoload.php';

// Test file type detection for PDF
$extension = 'pdf';
echo "Extension: $extension\n";

// Test isDocumentFile logic
$documentTypes = [
    'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
    'txt', 'rtf', 'odt', 'ods', 'odp',
    'html', 'htm', 'xml', 'json', 'csv'
];

$downloadableTypes = [
    // Archive formats
    'zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz',
    // Executable formats
    'exe', 'msi', 'dmg', 'pkg', 'deb', 'rpm',
    // Office documents (force download for security)
    'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
    // Text documents (except those that can be viewed inline)
    'rtf', 'odt', 'ods', 'odp',
    // Other potentially dangerous formats
    'bat', 'cmd', 'sh', 'ps1'
];

$imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico'];
$mediaTypes = ['mp4', 'avi', 'mov', 'wmv', 'flv', 'webm', 'mp3', 'wav', 'ogg', 'aac', 'flac'];

echo "isImage: " . (in_array($extension, $imageTypes) ? 'true' : 'false') . "\n";
echo "isMedia: " . (in_array($extension, $mediaTypes) ? 'true' : 'false') . "\n";
echo "isDocument: " . (in_array($extension, $documentTypes) ? 'true' : 'false') . "\n";
echo "isDownloadable: " . (in_array($extension, $downloadableTypes) ? 'true' : 'false') . "\n";

// Test the logic flow
if (in_array($extension, $imageTypes)) {
    echo "Would apply: IMAGE headers\n";
} elseif (in_array($extension, $mediaTypes)) {
    echo "Would apply: MEDIA headers\n";
} elseif (in_array($extension, $documentTypes)) {
    echo "Would apply: DOCUMENT headers\n";
} elseif (in_array($extension, $downloadableTypes)) {
    echo "Would apply: DOWNLOADABLE headers\n";
} else {
    echo "Would apply: DEFAULT SECURE headers\n";
}