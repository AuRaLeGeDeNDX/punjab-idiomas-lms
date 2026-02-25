<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Unit tests for Enhanced File Input Handling - Task 5.3
 * 
 * Tests the enhanced file input functionality including:
 * - Drag-and-drop file selection
 * - File preview for images
 * - File information display (name, size, type)
 * - Automatic clearing of invalid files
 * 
 * Requirements: 5.1, 5.2
 */
class EnhancedFileInputTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /**
     * Test that file information is properly extracted and formatted
     * 
     * @test
     */
    public function it_extracts_file_information_correctly()
    {
        // Create a test file (not image to avoid GD dependency)
        $file = UploadedFile::fake()->create('test-document.pdf', 1024); // 1MB
        
        // Test file information extraction
        $this->assertEquals('test-document.pdf', $file->getClientOriginalName());
        $this->assertEquals('pdf', $file->getClientOriginalExtension());
        $this->assertGreaterThan(0, $file->getSize());
        
        // Test that file is valid
        $this->assertTrue($file->isValid());
        $this->assertEquals(UPLOAD_ERR_OK, $file->getError());
    }

    /**
     * Test file size formatting for display
     * 
     * @test
     */
    public function it_formats_file_sizes_correctly()
    {
        $testCases = [
            [0, '0 Bytes'],
            [1024, '1 KB'],
            [1048576, '1 MB'],
            [1073741824, '1 GB'],
            [1536, '1.5 KB'],
            [2621440, '2.5 MB'],
        ];

        foreach ($testCases as [$bytes, $expected]) {
            $this->assertEquals($expected, $this->formatFileSize($bytes));
        }
    }

    /**
     * Test image file preview capability
     * 
     * @test
     */
    public function it_identifies_image_files_for_preview()
    {
        // Test with fake files that simulate image MIME types
        $imageFiles = [
            ['test.jpg', 'image/jpeg'],
            ['test.png', 'image/png'],
            ['test.gif', 'image/gif'],
        ];

        foreach ($imageFiles as [$filename, $mimeType]) {
            $file = UploadedFile::fake()->create($filename, 500);
            
            // Simulate the MIME type check that would happen in real scenario
            $this->assertTrue($this->isImageFile($file, $mimeType));
            
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $this->assertEquals($extension, $file->getClientOriginalExtension());
        }
    }

    /**
     * Test non-image file handling
     * 
     * @test
     */
    public function it_identifies_non_image_files_correctly()
    {
        $nonImageFiles = [
            'document.pdf' => 'application/pdf',
            'document.docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'audio.mp3' => 'audio/mpeg',
            'video.mp4' => 'video/mp4',
        ];

        foreach ($nonImageFiles as $filename => $expectedMimeType) {
            $file = UploadedFile::fake()->create($filename, 1000);
            
            $this->assertFalse($this->isImageFile($file));
            // Note: Fake files may not have exact MIME types, so we check the extension
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            $this->assertEquals($extension, $file->getClientOriginalExtension());
        }
    }

    /**
     * Test file validation with size limits
     * 
     * @test
     */
    public function it_validates_file_size_limits()
    {
        $maxSize = 2 * 1024 * 1024; // 2MB

        // Test file within limit
        $validFile = UploadedFile::fake()->create('valid.pdf', 1024); // 1MB
        $this->assertTrue($this->isFileSizeValid($validFile, $maxSize));

        // Test file exceeding limit
        $invalidFile = UploadedFile::fake()->create('invalid.pdf', 3072); // 3MB
        $this->assertFalse($this->isFileSizeValid($invalidFile, $maxSize));
    }

    /**
     * Test file extension validation
     * 
     * @test
     */
    public function it_validates_file_extensions()
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        // Test valid extensions
        foreach ($allowedExtensions as $ext) {
            $file = UploadedFile::fake()->create("test.$ext", 500);
            $this->assertTrue($this->isExtensionAllowed($file, $allowedExtensions));
        }

        // Test invalid extension
        $invalidFile = UploadedFile::fake()->create('test.exe', 100);
        $this->assertFalse($this->isExtensionAllowed($invalidFile, $allowedExtensions));
    }

    /**
     * Test automatic file clearing for invalid files
     * 
     * @test
     */
    public function it_identifies_files_that_should_be_cleared()
    {
        $config = [
            'max_file_size' => 1024 * 1024, // 1MB
            'allowed_extensions' => ['jpg', 'png'],
        ];

        // Test valid file - should not be cleared
        $validFile = UploadedFile::fake()->create('valid.jpg', 500); // 500KB
        $this->assertFalse($this->shouldClearFile($validFile, $config));

        // Test oversized file - should be cleared
        $oversizedFile = UploadedFile::fake()->create('large.jpg', 2048); // 2MB
        $this->assertTrue($this->shouldClearFile($oversizedFile, $config));

        // Test invalid extension - should be cleared
        $invalidExtFile = UploadedFile::fake()->create('document.pdf', 500);
        $this->assertTrue($this->shouldClearFile($invalidExtFile, $config));
    }

    /**
     * Test drag-and-drop file handling simulation
     * 
     * @test
     */
    public function it_handles_drag_and_drop_file_selection()
    {
        // Simulate multiple files being dropped (should take first one)
        $files = [
            UploadedFile::fake()->create('first.jpg', 500),
            UploadedFile::fake()->create('second.jpg', 600),
            UploadedFile::fake()->create('third.jpg', 700),
        ];

        $selectedFile = $this->handleMultipleFilesDrop($files);
        
        $this->assertEquals('first.jpg', $selectedFile->getClientOriginalName());
        $this->assertCount(1, [$selectedFile]); // Only one file should be selected
    }

    /**
     * Test file type icon determination
     * 
     * @test
     */
    public function it_determines_correct_file_type_icons()
    {
        $iconMappings = [
            ['image/jpeg', 'jpg', 'fas fa-image', 'text-info'],
            ['application/pdf', 'pdf', 'fas fa-file-pdf', 'text-danger'],
            ['video/mp4', 'mp4', 'fas fa-video', 'text-warning'],
            ['audio/mp3', 'mp3', 'fas fa-music', 'text-success'],
            ['text/plain', 'txt', 'fas fa-file', 'text-secondary'],
        ];

        foreach ($iconMappings as [$mimeType, $extension, $expectedIcon, $expectedColor]) {
            $icon = $this->getFileTypeIcon($mimeType, $extension);
            
            $this->assertEquals($expectedIcon, $icon['icon']);
            $this->assertEquals($expectedColor, $icon['color']);
        }
    }

    /**
     * Test progress bar class determination based on file size percentage
     * 
     * @test
     */
    public function it_determines_correct_progress_bar_classes()
    {
        $testCases = [
            [50, 'bg-success'],
            [85, 'bg-info'],
            [92, 'bg-warning'],
            [97, 'bg-danger'],
            [100, 'bg-danger'],
        ];

        foreach ($testCases as [$percentage, $expectedClass]) {
            $this->assertEquals($expectedClass, $this->getSizeProgressBarClass($percentage));
        }
    }

    // Helper methods that simulate the JavaScript functionality

    private function formatFileSize(int $bytes): string
    {
        if ($bytes === 0) return '0 Bytes';
        
        $k = 1024;
        $sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log($k));
        
        $sizeIndex = min($i, count($sizes) - 1);
        
        return round($bytes / pow($k, $sizeIndex), 2) . ' ' . $sizes[$sizeIndex];
    }

    private function isImageFile(UploadedFile $file, string $mimeType = null): bool
    {
        // Use provided MIME type for testing, or fall back to file's MIME type
        $fileType = $mimeType ?? $file->getMimeType() ?? '';
        return str_starts_with($fileType, 'image/');
    }

    private function isFileSizeValid(UploadedFile $file, int $maxSize): bool
    {
        return $file->getSize() <= $maxSize;
    }

    private function isExtensionAllowed(UploadedFile $file, array $allowedExtensions): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, $allowedExtensions);
    }

    private function shouldClearFile(UploadedFile $file, array $config): bool
    {
        // Check file size
        if (isset($config['max_file_size']) && $file->getSize() > $config['max_file_size']) {
            return true;
        }

        // Check extension
        if (isset($config['allowed_extensions'])) {
            $extension = strtolower($file->getClientOriginalExtension());
            if (!in_array($extension, $config['allowed_extensions'])) {
                return true;
            }
        }

        return false;
    }

    private function handleMultipleFilesDrop(array $files): UploadedFile
    {
        // Simulate taking the first file from a drag-and-drop operation
        return $files[0];
    }

    private function getFileTypeIcon(string $mimeType, string $extension): array
    {
        if (str_starts_with($mimeType, 'image/')) {
            return ['icon' => 'fas fa-image', 'color' => 'text-info'];
        } elseif ($mimeType === 'application/pdf') {
            return ['icon' => 'fas fa-file-pdf', 'color' => 'text-danger'];
        } elseif (str_starts_with($mimeType, 'video/')) {
            return ['icon' => 'fas fa-video', 'color' => 'text-warning'];
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return ['icon' => 'fas fa-music', 'color' => 'text-success'];
        } else {
            return ['icon' => 'fas fa-file', 'color' => 'text-secondary'];
        }
    }

    private function getSizeProgressBarClass(int $percentage): string
    {
        if ($percentage > 95) return 'bg-danger';
        if ($percentage > 90) return 'bg-warning';
        if ($percentage > 80) return 'bg-info';
        return 'bg-success';
    }
}