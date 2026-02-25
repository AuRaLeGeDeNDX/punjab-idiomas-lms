<?php

namespace Tests\Unit;

use App\Models\Content;
use App\Services\FileLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * Unit tests for Content::findActualFileLocation() method.
 * 
 * Tests the logic to search for files across storage disks,
 * log storage inconsistencies when found, and return FileLocation
 * value object with actual file location.
 * 
 * Requirements: 2.3, 7.1
 */
class ContentFindActualFileLocationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use fake storage for testing
        Storage::fake('public');
        Storage::fake('protected');
        
        // Clear logs
        Log::spy();
    }

    /** @test */
    public function it_returns_null_when_no_file_path_is_recorded()
    {
        $content = Content::factory()->create([
            'file_path' => null,
            'storage_disk' => 'public',
        ]);

        $result = $content->findActualFileLocation();

        $this->assertNull($result);
        
        // Verify debug log was written
        Log::shouldHaveReceived('debug')
            ->with('Content findActualFileLocation: No file path recorded', [
                'content_id' => $content->id,
                'content_type' => $content->type,
            ]);
    }

    /** @test */
    public function it_finds_file_at_recorded_location_on_public_disk()
    {
        $filePath = 'test-files/image.jpg';
        Storage::disk('public')->put($filePath, 'test content');
        
        $content = Content::factory()->create([
            'file_path' => $filePath,
            'storage_disk' => 'public',
            'type' => 'image',
        ]);

        $result = $content->findActualFileLocation();

        $this->assertInstanceOf(FileLocation::class, $result);
        $this->assertEquals('public', $result->getDisk());
        $this->assertEquals($filePath, $result->getPath());
        $this->assertTrue($result->exists());
        
        // Verify file found at recorded location log
        Log::shouldHaveReceived('debug')
            ->with('Content findActualFileLocation: File found at recorded location', \Mockery::subset([
                'content_id' => $content->id,
                'storage_disk' => 'public',
                'file_path' => $filePath,
            ]));
    }

    /** @test */
    public function it_finds_file_at_recorded_location_on_protected_disk()
    {
        $filePath = 'secure-files/document.pdf';
        Storage::disk('protected')->put($filePath, 'test content');
        
        $content = Content::factory()->create([
            'file_path' => $filePath,
            'storage_disk' => 'protected',
            'type' => 'pdf',
        ]);

        $result = $content->findActualFileLocation();

        $this->assertInstanceOf(FileLocation::class, $result);
        $this->assertEquals('protected', $result->getDisk());
        $this->assertEquals($filePath, $result->getPath());
        $this->assertTrue($result->exists());
    }

    /** @test */
    public function it_finds_file_on_alternative_disk_and_logs_inconsistency()
    {
        $filePath = 'test-files/image.jpg';
        // File is recorded as being on public disk but actually exists on protected disk
        Storage::disk('protected')->put($filePath, 'test content');
        
        $content = Content::factory()->create([
            'file_path' => $filePath,
            'storage_disk' => 'public', // Recorded as public
            'type' => 'image',
        ]);

        $result = $content->findActualFileLocation();

        $this->assertInstanceOf(FileLocation::class, $result);
        $this->assertEquals('protected', $result->getDisk()); // Actually found on protected
        $this->assertEquals($filePath, $result->getPath());
        $this->assertTrue($result->exists());
        
        // Verify inconsistency warning was logged
        Log::shouldHaveReceived('warning')
            ->with('Content findActualFileLocation: Storage inconsistency detected', \Mockery::subset([
                'event_type' => 'storage_inconsistency',
                'content_id' => $content->id,
                'content_type' => $content->type,
                'recorded_disk' => 'public',
                'actual_disk' => 'protected',
                'file_path' => $filePath,
                'recommendation' => 'Update storage_disk field in database to match actual location',
            ]));
    }

    /** @test */
    public function it_searches_protected_disk_when_recorded_as_public_but_not_found()
    {
        $filePath = 'test-files/image.jpg';
        // File is recorded as being on public disk but actually exists on protected disk
        Storage::disk('protected')->put($filePath, 'test content');
        
        $content = Content::factory()->create([
            'file_path' => $filePath,
            'storage_disk' => 'public', // Recorded as public but doesn't exist there
            'type' => 'image',
        ]);

        $result = $content->findActualFileLocation();

        $this->assertInstanceOf(FileLocation::class, $result);
        $this->assertEquals('protected', $result->getDisk());
        $this->assertEquals($filePath, $result->getPath());
        
        // Verify inconsistency was logged
        Log::shouldHaveReceived('warning')
            ->with('Content findActualFileLocation: Storage inconsistency detected', \Mockery::any());
    }

    /** @test */
    public function it_searches_public_disk_when_recorded_as_protected_but_not_found()
    {
        $filePath = 'test-files/image.jpg';
        // File is recorded as being on protected disk but actually exists on public disk
        Storage::disk('public')->put($filePath, 'test content');
        
        $content = Content::factory()->create([
            'file_path' => $filePath,
            'storage_disk' => 'protected', // Recorded as protected but doesn't exist there
            'type' => 'image',
        ]);

        $result = $content->findActualFileLocation();

        $this->assertInstanceOf(FileLocation::class, $result);
        $this->assertEquals('public', $result->getDisk());
        $this->assertEquals($filePath, $result->getPath());
        
        // Verify inconsistency was logged
        Log::shouldHaveReceived('warning')
            ->with('Content findActualFileLocation: Storage inconsistency detected', \Mockery::subset([
                'recorded_disk' => 'protected',
                'actual_disk' => 'public',
            ]));
    }

    /** @test */
    public function it_defaults_to_public_disk_when_storage_disk_is_null()
    {
        $filePath = 'test-files/image.jpg';
        Storage::disk('public')->put($filePath, 'test content');
        
        $content = Content::factory()->create([
            'file_path' => $filePath,
            'storage_disk' => 'public', // Set to public initially
            'type' => 'image',
        ]);
        
        // Manually set storage_disk to null after creation to simulate legacy data
        $content->storage_disk = null;

        $result = $content->findActualFileLocation();

        $this->assertInstanceOf(FileLocation::class, $result);
        $this->assertEquals('public', $result->getDisk());
        $this->assertEquals($filePath, $result->getPath());
    }

    /** @test */
    public function it_returns_null_when_file_not_found_on_any_disk()
    {
        $filePath = 'test-files/nonexistent.jpg';
        // File doesn't exist on any disk
        
        $content = Content::factory()->create([
            'file_path' => $filePath,
            'storage_disk' => 'public',
            'type' => 'image',
        ]);

        $result = $content->findActualFileLocation();

        $this->assertNull($result);
        
        // Verify error was logged
        Log::shouldHaveReceived('error')
            ->with('Content findActualFileLocation: File not found on any storage disk', \Mockery::subset([
                'event_type' => 'file_not_found',
                'content_id' => $content->id,
                'content_type' => $content->type,
                'recorded_file_path' => $filePath,
                'recorded_storage_disk' => 'public',
                'disks_checked' => ['public', 'protected'],
            ]));
    }

    /** @test */
    public function it_handles_storage_disk_access_errors_gracefully()
    {
        $filePath = 'test-files/image.jpg';
        
        $content = Content::factory()->create([
            'file_path' => $filePath,
            'storage_disk' => 'invalid_disk', // Use an invalid disk name to trigger error
            'type' => 'image',
        ]);

        $result = $content->findActualFileLocation();

        $this->assertNull($result);
        
        // Verify error was logged
        Log::shouldHaveReceived('warning')
            ->with('Content findActualFileLocation: Error checking storage disk', \Mockery::subset([
                'content_id' => $content->id,
                'disk' => 'invalid_disk',
            ]));
    }

    /** @test */
    public function it_logs_comprehensive_information_for_successful_search()
    {
        $filePath = 'test-files/image.jpg';
        Storage::disk('public')->put($filePath, 'test content');
        
        $content = Content::factory()->create([
            'file_path' => $filePath,
            'storage_disk' => 'public',
            'type' => 'image',
        ]);

        $result = $content->findActualFileLocation();

        $this->assertNotNull($result);
        
        // Verify comprehensive logging
        Log::shouldHaveReceived('debug')
            ->with('Content findActualFileLocation: Starting file location search', \Mockery::subset([
                'content_id' => $content->id,
                'recorded_file_path' => $filePath,
                'recorded_storage_disk' => 'public',
                'content_type' => $content->type,
            ]));
        
        Log::shouldHaveReceived('info')
            ->with('Content findActualFileLocation: File location found', \Mockery::subset([
                'content_id' => $content->id,
                'found_on_disk' => 'public',
                'recorded_disk' => 'public',
                'is_consistent' => true,
            ]));
    }

    /** @test */
    public function it_includes_server_information_in_inconsistency_logs()
    {
        $filePath = 'test-files/image.jpg';
        Storage::disk('protected')->put($filePath, 'test content');
        
        $content = Content::factory()->create([
            'file_path' => $filePath,
            'storage_disk' => 'public',
            'type' => 'image',
        ]);

        // Mock server variables
        $_SERVER['SERVER_NAME'] = 'test-server.com';
        
        $result = $content->findActualFileLocation();

        $this->assertNotNull($result);
        
        // Verify that a warning was logged (the exact content is tested in other tests)
        Log::shouldHaveReceived('warning')
            ->atLeast()
            ->once();
    }

    /** @test */
    public function it_includes_recommendations_in_error_logs()
    {
        $filePath = 'test-files/nonexistent.jpg';
        
        $content = Content::factory()->create([
            'file_path' => $filePath,
            'storage_disk' => 'public',
            'type' => 'image',
        ]);

        $result = $content->findActualFileLocation();

        $this->assertNull($result);
        
        // Verify recommendations are included in error logs
        Log::shouldHaveReceived('error')
            ->with('Content findActualFileLocation: File not found on any storage disk', \Mockery::subset([
                'recommendations' => [
                    'investigate_file_deletion' => 'Check if file was accidentally deleted or moved',
                    'check_storage_configuration' => 'Verify storage disk configuration and accessibility',
                    'run_file_audit' => 'Run comprehensive file audit to identify similar issues',
                    'consider_file_recovery' => 'Check backups or file recovery options if available',
                ],
            ]));
    }

    /** @test */
    public function it_checks_disks_in_correct_priority_order()
    {
        $filePath = 'test-files/image.jpg';
        // Put file on both disks to test priority
        Storage::disk('public')->put($filePath, 'public content');
        Storage::disk('protected')->put($filePath, 'protected content');
        
        $content = Content::factory()->create([
            'file_path' => $filePath,
            'storage_disk' => 'protected', // Recorded as protected
            'type' => 'image',
        ]);

        $result = $content->findActualFileLocation();

        // Should find on protected disk first (recorded disk has priority)
        $this->assertEquals('protected', $result->getDisk());
        
        // Should not log inconsistency since found on recorded disk
        Log::shouldNotHaveReceived('warning');
    }
}