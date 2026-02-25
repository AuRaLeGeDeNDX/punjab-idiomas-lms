<?php

namespace Tests\Feature;

use App\Models\Content;
use App\Models\Course;
use App\Models\Module;
use App\Models\PdfAccessLog;
use App\Models\Subpage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PdfAccessLogControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teacher;
    protected User $student;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Teacher']);
        Role::create(['name' => 'Student']);

        // Create users
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');

        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');

        $this->student = User::factory()->create();
        $this->student->assignRole('Student');
    }

    /** @test */
    public function admin_can_view_pdf_access_logs_index()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.pdf-access-logs.index'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.pdf-access-logs.index');
        $response->assertViewHas('logs');
        $response->assertViewHas('stats');
        $response->assertViewHas('users');
        $response->assertViewHas('contents');
    }

    /** @test */
    public function non_admin_cannot_view_pdf_access_logs()
    {
        $response = $this->actingAs($this->teacher)->get(route('admin.pdf-access-logs.index'));
        $response->assertStatus(403);

        $response = $this->actingAs($this->student)->get(route('admin.pdf-access-logs.index'));
        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_view_pdf_access_logs()
    {
        $response = $this->get(route('admin.pdf-access-logs.index'));
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_displays_paginated_logs()
    {
        // Create test data
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
        ]);

        // Create 30 access logs
        for ($i = 0; $i < 30; $i++) {
            PdfAccessLog::create([
                'user_id' => $this->student->id,
                'content_id' => $content->id,
                'session_token' => 'token_' . $i,
                'ip_address' => '127.0.0.1',
                'access_granted' => true,
                'accessed_at' => now()->subMinutes($i),
            ]);
        }

        // Request first page with 25 per page
        $response = $this->actingAs($this->admin)->get(route('admin.pdf-access-logs.index', [
            'per_page' => 25,
        ]));

        $response->assertStatus(200);
        $logs = $response->viewData('logs');
        
        // Assert pagination
        $this->assertEquals(25, $logs->count());
        $this->assertEquals(30, $logs->total());
        $this->assertEquals(2, $logs->lastPage());
    }

    /** @test */
    public function it_filters_logs_by_user()
    {
        // Create test data
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
        ]);

        $otherStudent = User::factory()->create();
        $otherStudent->assignRole('Student');

        // Create logs for different users
        PdfAccessLog::create([
            'user_id' => $this->student->id,
            'content_id' => $content->id,
            'session_token' => 'token_1',
            'ip_address' => '127.0.0.1',
            'access_granted' => true,
            'accessed_at' => now(),
        ]);

        PdfAccessLog::create([
            'user_id' => $otherStudent->id,
            'content_id' => $content->id,
            'session_token' => 'token_2',
            'ip_address' => '127.0.0.1',
            'access_granted' => true,
            'accessed_at' => now(),
        ]);

        // Filter by specific user
        $response = $this->actingAs($this->admin)->get(route('admin.pdf-access-logs.index', [
            'user_id' => $this->student->id,
        ]));

        $response->assertStatus(200);
        $logs = $response->viewData('logs');
        
        // Assert only logs for the filtered user are returned
        $this->assertEquals(1, $logs->count());
        $this->assertEquals($this->student->id, $logs->first()->user_id);
    }

    /** @test */
    public function it_filters_logs_by_content()
    {
        // Create test data
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        $content1 = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
        ]);
        
        $content2 = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
        ]);

        // Create logs for different content
        PdfAccessLog::create([
            'user_id' => $this->student->id,
            'content_id' => $content1->id,
            'session_token' => 'token_1',
            'ip_address' => '127.0.0.1',
            'access_granted' => true,
            'accessed_at' => now(),
        ]);

        PdfAccessLog::create([
            'user_id' => $this->student->id,
            'content_id' => $content2->id,
            'session_token' => 'token_2',
            'ip_address' => '127.0.0.1',
            'access_granted' => true,
            'accessed_at' => now(),
        ]);

        // Filter by specific content
        $response = $this->actingAs($this->admin)->get(route('admin.pdf-access-logs.index', [
            'content_id' => $content1->id,
        ]));

        $response->assertStatus(200);
        $logs = $response->viewData('logs');
        
        // Assert only logs for the filtered content are returned
        $this->assertEquals(1, $logs->count());
        $this->assertEquals($content1->id, $logs->first()->content_id);
    }

    /** @test */
    public function it_filters_logs_by_date_range()
    {
        // Create test data
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
        ]);

        // Create logs with different dates
        PdfAccessLog::create([
            'user_id' => $this->student->id,
            'content_id' => $content->id,
            'session_token' => 'token_1',
            'ip_address' => '127.0.0.1',
            'access_granted' => true,
            'accessed_at' => now()->subDays(5),
        ]);

        PdfAccessLog::create([
            'user_id' => $this->student->id,
            'content_id' => $content->id,
            'session_token' => 'token_2',
            'ip_address' => '127.0.0.1',
            'access_granted' => true,
            'accessed_at' => now()->subDays(2),
        ]);

        PdfAccessLog::create([
            'user_id' => $this->student->id,
            'content_id' => $content->id,
            'session_token' => 'token_3',
            'ip_address' => '127.0.0.1',
            'access_granted' => true,
            'accessed_at' => now(),
        ]);

        // Filter by date range (last 3 days)
        $response = $this->actingAs($this->admin)->get(route('admin.pdf-access-logs.index', [
            'date_from' => now()->subDays(3)->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $logs = $response->viewData('logs');
        
        // Assert only logs within date range are returned
        $this->assertEquals(2, $logs->count());
    }

    /** @test */
    public function it_filters_logs_by_access_granted_status()
    {
        // Create test data
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
        ]);

        // Create successful and failed access logs
        PdfAccessLog::create([
            'user_id' => $this->student->id,
            'content_id' => $content->id,
            'session_token' => 'token_1',
            'ip_address' => '127.0.0.1',
            'access_granted' => true,
            'accessed_at' => now(),
        ]);

        PdfAccessLog::create([
            'user_id' => $this->student->id,
            'content_id' => $content->id,
            'session_token' => 'token_2',
            'ip_address' => '127.0.0.1',
            'access_granted' => false,
            'failure_reason' => 'invalid_token',
            'accessed_at' => now(),
        ]);

        // Filter by denied access
        $response = $this->actingAs($this->admin)->get(route('admin.pdf-access-logs.index', [
            'access_granted' => '0',
        ]));

        $response->assertStatus(200);
        $logs = $response->viewData('logs');
        
        // Assert only denied access logs are returned
        $this->assertEquals(1, $logs->count());
        $this->assertFalse($logs->first()->access_granted);
    }

    /** @test */
    public function it_displays_correct_statistics()
    {
        // Create test data
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        
        $content1 = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
        ]);
        
        $content2 = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
        ]);

        $otherStudent = User::factory()->create();
        $otherStudent->assignRole('Student');

        // Create various logs
        PdfAccessLog::create([
            'user_id' => $this->student->id,
            'content_id' => $content1->id,
            'session_token' => 'token_1',
            'ip_address' => '127.0.0.1',
            'access_granted' => true,
            'accessed_at' => now(),
        ]);

        PdfAccessLog::create([
            'user_id' => $this->student->id,
            'content_id' => $content2->id,
            'session_token' => 'token_2',
            'ip_address' => '127.0.0.1',
            'access_granted' => true,
            'accessed_at' => now(),
        ]);

        PdfAccessLog::create([
            'user_id' => $otherStudent->id,
            'content_id' => $content1->id,
            'session_token' => 'token_3',
            'ip_address' => '127.0.0.1',
            'access_granted' => false,
            'failure_reason' => 'invalid_token',
            'accessed_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.pdf-access-logs.index'));

        $response->assertStatus(200);
        $stats = $response->viewData('stats');
        
        // Assert statistics are correct
        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(2, $stats['granted']);
        $this->assertEquals(1, $stats['denied']);
        $this->assertEquals(2, $stats['unique_users']);
        $this->assertEquals(2, $stats['unique_content']);
    }

    /** @test */
    public function it_loads_user_and_content_relationships()
    {
        // Create test data
        $course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $module = Module::factory()->create(['course_id' => $course->id]);
        $subpage = Subpage::factory()->create(['module_id' => $module->id]);
        $content = Content::factory()->create([
            'subpage_id' => $subpage->id,
            'type' => 'pdf',
            'title' => 'Test PDF Document',
        ]);

        PdfAccessLog::create([
            'user_id' => $this->student->id,
            'content_id' => $content->id,
            'session_token' => 'token_1',
            'ip_address' => '127.0.0.1',
            'access_granted' => true,
            'accessed_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.pdf-access-logs.index'));

        $response->assertStatus(200);
        $logs = $response->viewData('logs');
        
        // Assert relationships are loaded
        $log = $logs->first();
        $this->assertNotNull($log->user);
        $this->assertEquals($this->student->name, $log->user->name);
        $this->assertNotNull($log->content);
        $this->assertEquals('Test PDF Document', $log->content->title);
    }
}
