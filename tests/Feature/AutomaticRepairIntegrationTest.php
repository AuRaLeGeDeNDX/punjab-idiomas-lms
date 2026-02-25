<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Content;
use App\Models\User;
use App\Services\RepairTriggerService;
use App\Services\FileRepairService;
use App\Services\FileStorageDiagnosticService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

/**
 * AutomaticRepairIntegrationTest verifies the integration of automatic repair triggers.
 * 
 * Requirements: 7.1, 7.2
 */
class AutomaticRepairIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected RepairTriggerService $triggerService;
    protected FileRepairService $repairService;
    protected FileStorageDiagnosticService $diagnosticService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles and permissions for tests
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        
        $this->triggerService = app(RepairTriggerService::class);
        $this->repairService = app(FileRepairService::class);
        $this->diagnosticService = app(FileStorageDiagnosticService::class);
        
        // Clear any cached repair throttles
        Cache::flush();
    }

    /** @test */
    public function it_can_trigger_automatic_repair_on_inconsistency()
    {
        // Create a test content record with file
        $content = Content::factory()->create([
            'type' => 'image',
            'file_path' => 'test-files/test-image.jpg',
            'storage_disk' => 'public',
            'file_size' => 1024,
        ]);
        
        // Create the actual file
        Storage::disk('public')->put('test-files/test-image.jpg', 'test content');
        
        // Trigger repair for a simulated inconsistency
        $result = $this->triggerService->triggerRepairOnInconsistency(
            $content,
            'test_inconsistency',
            ['test_context' => 'integration_test']
        );
        
        $this->assertTrue($result, 'Automatic repair should be triggered successfully');
    }

    /** @test */
    public function it_respects_repair_throttling()
    {
        $content = Content::factory()->create([
            'type' => 'image',
            'file_path' => 'test-files/test-image.jpg',
            'storage_disk' => 'public',
        ]);
        
        Storage::disk('public')->put('test-files/test-image.jpg', 'test content');
        
        // First repair should succeed
        $result1 = $this->triggerService->triggerRepairOnInconsistency(
            $content,
            'test_inconsistency'
        );
        $this->assertTrue($result1);
        
        // Second repair should be throttled
        $result2 = $this->triggerService->triggerRepairOnInconsistency(
            $content,
            'test_inconsistency'
        );
        $this->assertFalse($result2, 'Second repair should be throttled');
    }

    /** @test */
    public function it_can_schedule_batch_repair()
    {
        // Create multiple content records
        $contents = Content::factory()->count(3)->create([
            'type' => 'image',
            'storage_disk' => 'public',
        ]);
        
        $contentIds = $contents->pluck('id')->toArray();
        
        $jobId = $this->triggerService->scheduleBatchRepair(
            $contentIds,
            'test_batch_repair'
        );
        
        $this->assertIsString($jobId);
        $this->assertNotEmpty($jobId);
    }

    /** @test */
    public function it_can_monitor_repair_operations()
    {
        $stats = $this->triggerService->monitorRepairOperations();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('hourly_repairs', $stats);
        $this->assertArrayHasKey('throttled_content', $stats);
        $this->assertArrayHasKey('failed_repairs_last_hour', $stats);
        $this->assertArrayHasKey('batch_jobs_active', $stats);
    }

    /** @test */
    public function admin_can_access_file_repair_dashboard()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $response = $this->actingAs($admin)
            ->get('/admin/file-repair');
        
        $response->assertStatus(200);
        $response->assertViewIs('admin.file-repair.index');
    }

    /** @test */
    public function admin_can_diagnose_content()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $content = Content::factory()->create([
            'type' => 'image',
            'file_path' => 'test-files/test-image.jpg',
            'storage_disk' => 'public',
        ]);
        
        Storage::disk('public')->put('test-files/test-image.jpg', 'test content');
        
        $response = $this->actingAs($admin)
            ->postJson("/admin/file-repair/diagnose/{$content->id}");
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'correlation_id',
            'content_id',
            'diagnostic' => [
                'file_exists',
                'has_inconsistencies',
                'actual_location',
                'inconsistencies',
                'recommendations',
            ],
        ]);
    }

    /** @test */
    public function admin_can_repair_content()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $content = Content::factory()->create([
            'type' => 'image',
            'file_path' => 'test-files/test-image.jpg',
            'storage_disk' => 'public',
        ]);
        
        Storage::disk('public')->put('test-files/test-image.jpg', 'test content');
        
        $response = $this->actingAs($admin)
            ->postJson("/admin/file-repair/repair/{$content->id}");
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'correlation_id',
            'content_id',
            'repair_result' => [
                'action',
                'description',
                'has_changes',
                'changes',
            ],
        ]);
    }

    /** @test */
    public function admin_can_start_batch_repair()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        // Create test content
        Content::factory()->count(3)->create([
            'type' => 'image',
            'storage_disk' => 'public',
        ]);
        
        $response = $this->actingAs($admin)
            ->postJson('/admin/file-repair/batch', [
                'content_types' => ['image'],
                'limit' => 5,
                'dry_run' => true,
            ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'correlation_id',
            'dry_run',
            'batch_result' => [
                'total_processed',
                'successful_repairs',
                'failed_repairs',
                'success_rate',
            ],
        ]);
    }

    /** @test */
    public function non_admin_cannot_access_file_repair()
    {
        $user = User::factory()->create();
        $user->assignRole('Student');
        
        $response = $this->actingAs($user)
            ->get('/admin/file-repair');
        
        $response->assertStatus(403);
    }

    protected function tearDown(): void
    {
        // Clean up test files
        Storage::disk('public')->deleteDirectory('test-files');
        
        parent::tearDown();
    }
}