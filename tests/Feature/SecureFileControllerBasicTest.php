<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Content;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\Enrollment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class SecureFileControllerBasicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Set up storage disks
        Storage::fake('protected');
        
        // Create roles
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Admin']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Teacher']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'Student']);
    }

    public function test_unauthenticated_user_receives_redirect_to_login()
    {
        // Create a simple content item
        $content = Content::factory()->create([
            'type' => 'pdf',
            'file_path' => 'test.pdf',
            'storage_disk' => 'protected'
        ]);
        
        $response = $this->get(route('secure-files.download-content', $content));
        
        // Laravel's auth middleware redirects unauthenticated users
        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_user_without_role_receives_403_error()
    {
        $user = User::factory()->create();
        // No role assigned
        
        $content = Content::factory()->create([
            'type' => 'pdf',
            'file_path' => 'test.pdf',
            'storage_disk' => 'protected'
        ]);
        
        $this->actingAs($user);
        $response = $this->get(route('secure-files.download-content', $content));
        
        $response->assertStatus(403);
        $response->assertJson([
            'error' => 'Access denied',
            'error_code' => 'INSUFFICIENT_ROLE'
        ]);
    }

    public function test_non_file_content_returns_400_error()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $content = Content::factory()->create([
            'type' => 'text', // Not a file type
            'file_path' => null
        ]);
        
        $this->actingAs($admin);
        $response = $this->get(route('secure-files.download-content', $content));
        
        $response->assertStatus(400);
        $response->assertJson([
            'error' => 'Content is not a file',
            'error_code' => 'NOT_A_FILE'
        ]);
    }

    public function test_content_without_file_path_returns_404_error()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $content = Content::factory()->create([
            'type' => 'pdf',
            'file_path' => null // No file path
        ]);
        
        $this->actingAs($admin);
        $response = $this->get(route('secure-files.download-content', $content));
        
        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'Content has no associated file',
            'error_code' => 'NO_FILE_ASSOCIATED'
        ]);
    }

    public function test_missing_file_returns_404_error()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $content = Content::factory()->create([
            'type' => 'pdf',
            'file_path' => 'missing-file.pdf',
            'storage_disk' => 'protected'
        ]);
        
        $this->actingAs($admin);
        $response = $this->get(route('secure-files.download-content', $content));
        
        $response->assertStatus(404);
        $response->assertJson([
            'error' => 'File not found',
            'error_code' => 'FILE_NOT_FOUND'
        ]);
    }

    public function test_admin_can_download_existing_file()
    {
        $admin = User::factory()->create();
        $admin->assignRole('Admin');
        
        $content = Content::factory()->create([
            'type' => 'pdf',
            'file_path' => 'test-document.pdf',
            'storage_disk' => 'protected',
            'original_filename' => 'test-document.pdf'
        ]);
        
        // Create the actual file
        Storage::disk('protected')->put($content->file_path, 'PDF content');
        
        $this->actingAs($admin);
        $response = $this->get(route('secure-files.download-content', $content));
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertHeader('Content-Disposition', 'inline; filename="test-document.pdf"');
        $this->assertEquals('PDF content', $response->getContent());
    }
}