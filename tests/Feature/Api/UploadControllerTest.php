<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UploadControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Ensure R2 is configured for the test
        config([
            'filesystems.disks.r2' => [
                'driver' => 's3',
                'key' => 'test-key',
                'secret' => 'test-secret',
                'region' => 'auto',
                'bucket' => 'test-bucket',
                'endpoint' => 'https://test-endpoint.cloudflare.com',
                'use_path_style_endpoint' => true,
            ]
        ]);
        
        // Mock the S3 Client
        $s3ClientMock = \Mockery::mock(\Aws\S3\S3Client::class);
        
        // Mock the command creation
        $commandMock = \Mockery::mock(\Aws\CommandInterface::class);
        $s3ClientMock->shouldReceive('getCommand')
            ->with('PutObject', \Mockery::type('array'))
            ->andReturn($commandMock);
            
        // Mock the presigned request generation
        $requestMock = \Mockery::mock(\Psr\Http\Message\RequestInterface::class);
        $requestMock->shouldReceive('getUri')
            ->andReturn('https://test-presigned-url.com/upload-path?signature=xyz');
            
        $s3ClientMock->shouldReceive('createPresignedRequest')
            ->with($commandMock, '+60 minutes')
            ->andReturn($requestMock);

        $diskMock = \Mockery::mock(\Illuminate\Contracts\Filesystem\Filesystem::class);
        $diskMock->shouldReceive('getClient')->andReturn($s3ClientMock);

        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('r2')
            ->andReturn($diskMock);
        \Illuminate\Support\Facades\Storage::shouldReceive('disk')
            ->with('public')
            ->andReturn(\Illuminate\Support\Facades\Storage::fake('public'));

        // Seed necessary roles/permissions if your app uses spatie/laravel-permission or similar
        // For simplicity, we create an admin user that can bypass Gate checks
        $this->user = \App\Models\User::factory()->create();
        $role = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
        $this->user->assignRole($role); // Ensure user has access
    }

    public function test_can_generate_presigned_url_for_content_block_upload()
    {
        $course = \App\Models\Course::factory()->create(['teacher_id' => $this->user->id]);
        $module = \App\Models\Module::factory()->create(['course_id' => $course->id]);
        $subpage = \App\Models\Subpage::factory()->create(['module_id' => $module->id]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}/content-blocks/presigned-url", [
            'topic' => 'content-blocks',
            'subpage_id' => $subpage->id,
            'filename' => 'test-video.mp4',
            'content_type' => 'video/mp4',
            'file_size' => 1024 * 1024 * 5, // 5MB
            'block_type' => 'video'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'upload_url',
                'path',
                'expires_in'
            ]
        ]);
        
        $this->assertTrue($response->json('success'));
        $this->assertEquals('https://test-presigned-url.com/upload-path?signature=xyz', $response->json('data.upload_url'));
        $this->assertStringContainsString('content-blocks/' . $course->id . '/' . $module->id . '/' . $subpage->id, $response->json('data.path'));
    }

    public function test_fails_presigned_url_generation_if_file_too_large()
    {
        $course = \App\Models\Course::factory()->create(['teacher_id' => $this->user->id]);
        $module = \App\Models\Module::factory()->create(['course_id' => $course->id]);
        $subpage = \App\Models\Subpage::factory()->create(['module_id' => $module->id]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/courses/{$course->id}/modules/{$module->id}/subpages/{$subpage->id}/content-blocks/presigned-url", [
            'topic' => 'content-blocks',
            'subpage_id' => $subpage->id,
            'filename' => 'massive-video.mp4',
            'content_type' => 'video/mp4',
            'file_size' => 1024 * 1024 * 1024, // 1GB
            'block_type' => 'video'
        ]);

        $response->assertStatus(422); // Validation error
        $response->assertJsonFragment(['success' => false]);
    }
}
