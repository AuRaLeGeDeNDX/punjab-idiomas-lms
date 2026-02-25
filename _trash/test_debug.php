<?php

require_once 'vendor/autoload.php';

// Create a mock SecureFileController to test the header building
class TestSecureFileController extends App\Http\Controllers\SecureFileController
{
    public function testBuildHeaders($mimeType, $fileSize, $fileName, $filePath)
    {
        return $this->buildSecureFileHeaders($mimeType, $fileSize, $fileName, $filePath);
    }
    
    public function testIsDocument($extension)
    {
        return $this->isDocumentFile($extension);
    }
}

// Mock the service dependency
$mockService = Mockery::mock(App\Services\SecureFileStorageService::class);
$controller = new TestSecureFileController($mockService);

echo "Testing PDF detection:\n";
echo "isDocumentFile('pdf'): " . ($controller->testIsDocument('pdf') ? 'true' : 'false') . "\n";

echo "\nTesting header building:\n";
$headers = $controller->testBuildHeaders('application/pdf', 1024, 'test.pdf', 'test-document.pdf');

echo "X-Frame-Options: " . ($headers['X-Frame-Options'] ?? 'not set') . "\n";
echo "Content-Disposition: " . ($headers['Content-Disposition'] ?? 'not set') . "\n";
echo "Content-Security-Policy: " . ($headers['Content-Security-Policy'] ?? 'not set') . "\n";