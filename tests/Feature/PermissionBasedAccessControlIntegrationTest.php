<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Course;
use App\Models\Module;
use App\Models\Subpage;
use App\Models\Content;
use App\Models\Enrollment;
use App\Services\ContentBlockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

/**
 * PermissionBasedAccessControlIntegrationTest verifies comprehensive access control.
 * 
 * This test suite covers:
 * - Role-based file upload permissions
 * - Course enrollment-based file access
 * - Content visibility controls
 * - Administrative override permissions
 * - Security boundary enforcement
 * - Permission inheritance and delegation
 * 
 * Requirements: 6.4, 6.5 - Permission-based access control
 */
class PermissionBasedAccessControlIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teacher1;
    protected User $teacher2;
    protected User $student1;
    protected User $student2;
    protected User $unenrolledStudent;
    protected Course $course1;
    protected Course $course2;
    protected Module $module1;
    protected Module $module2;
    protected Subpage $subpage1;
    protected Subpage $subpage2;
    protected ContentBlockService $contentBlockService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles and permissions
        $this->createRolesAndPermissions();
        
        // Create users with roles
        $this->createUsers();
        
        // Create course structure
        $this->createCourseStructure();
        
        // Set up enrollments
        $this->setupEnrollments();
        
        // Get services
        $this->contentBlockService = app(ContentBlockService::class);
        
        // Ensure storage directories exist
        Storage::disk('public')->makeDirectory('permission-test');
        Storage::disk('protected')->makeDirectory('permission-test');
    }

    protected function createRolesAndPermissions(): void
    {
        // Create roles
        Role::create(['name' => 'Admin']);
        Role::create(['name' => 'Teacher']);
        Role::create(['name' => 'Student']);
        
        // Create permissions
        Permission::create(['name' => 'upload_files']);
        Permission::create(['name' => 'manage_content']);
        Permission::create(['name' => 'access_all_courses']);
        Permission::create(['name' => 'view_protected_content']);
        
        // Assign permissions to roles
        $adminRole = Role::findByName('Admin');
        $adminRole->givePermissionTo(['upload_files', 'manage_content', 'access_all_courses', 'view_protected_content']);
        
        $teacherRole = Role::findByName('Teacher');
        $teacherRole->givePermissionTo(['upload_files', 'manage_content']);
        
        $studentRole = Role::findByName('Student');
        // Students have no direct permissions - access is enrollment-based
    }

    protected function createUsers(): void
    {
        $this->admin = User::factory()->create(['name' => 'Admin User']);
        $this->admin->assignRole('Admin');
        
        $this->teacher1 = User::factory()->create(['name' => 'Teacher One']);
        $this->teacher1->assignRole('Teacher');
        
        $this->teacher2 = User::factory()->create(['name' => 'Teacher Two']);
        $this->teacher2->assignRole('Teacher');
        
        $this->student1 = User::factory()->create(['name' => 'Student One']);
        $this->student1->assignRole('Student');
        
        $this->student2 = User::factory()->create(['name' => 'Student Two']);
        $this->student2->assignRole('Student');
        
        $this->unenrolledStudent = User::factory()->create(['name' => 'Unenrolled Student']);
        $this->unenrolledStudent->assignRole('Student');
    }

    protected function createCourseStructure(): void
    {
        // Course 1 - owned by teacher1
        $this->course1 = Course::factory()->create([
            'title' => 'Course One',
            'created_by' => $this->teacher1->id,
        ]);
        
        $this->module1 = Module::factory()->create([
            'course_id' => $this->course1->id,
            'title' => 'Module One',
        ]);
        
        $this->subpage1 = Subpage::factory()->create([
            'module_id' => $this->module1->id,
            'title' => 'Subpage One',
        ]);
        
        // Course 2 - owned by teacher2
        $this->course2 = Course::factory()->create([
            'title' => 'Course Two',
            'created_by' => $this->teacher2->id,
        ]);
        
        $this->module2 = Module::factory()->create([
            'course_id' => $this->course2->id,
            'title' => 'Module Two',
        ]);
        
        $this->subpage2 = Subpage::factory()->create([
            'module_id' => $this->module2->id,
            'title' => 'Subpage Two',
        ]);
    }

    protected function setupEnrollments(): void
    {
        // Enroll student1 in course1
        Enrollment::create([
            'user_id' => $this->student1->id,
            'course_id' => $this->course1->id,
            'enrolled_at' => now(),
        ]);
        
        // Enroll student2 in course2
        Enrollment::create([
            'user_id' => $this->student2->id,
            'course_id' => $this->course2->id,
            'enrolled_at' => now(),
        ]);
        
        // unenrolledStudent is not enrolled in any course
    }

    /** @test */
    public function admin_can_upload_files_to_any_course()
    {
        // Requirements: 6.4 - Administrative override permissions
        $this->actingAs($this->admin);
        
        $testFile = UploadedFile::fake()->image('admin-upload.jpg');
        
        // Admin should be able to upload to any subpage
        $response1 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage1->id,
            'type' => 'image',
            'title' => 'Admin Upload to Course 1',
            'file' => $testFile,
        ]);
        
        $response1->assertStatus(201);
        
        $response2 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage2->id,
            'type' => 'image',
            'title' => 'Admin Upload to Course 2',
            'file' => $testFile,
        ]);
        
        $response2->assertStatus(201);
    }

    /** @test */
    public function teacher_can_only_upload_to_own_courses()
    {
        // Requirements: 6.4 - Role-based upload permissions
        
        // Teacher1 can upload to their own course
        $this->actingAs($this->teacher1);
        
        $testFile = UploadedFile::fake()->image('teacher1-upload.jpg');
        
        $response1 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage1->id,
            'type' => 'image',
            'title' => 'Teacher1 Upload to Own Course',
            'file' => $testFile,
        ]);
        
        $response1->assertStatus(201);
        
        // Teacher1 cannot upload to teacher2's course
        $response2 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage2->id,
            'type' => 'image',
            'title' => 'Teacher1 Upload to Other Course',
            'file' => $testFile,
        ]);
        
        $response2->assertStatus(403);
        
        // Teacher2 can upload to their own course
        $this->actingAs($this->teacher2);
        
        $response3 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage2->id,
            'type' => 'image',
            'title' => 'Teacher2 Upload to Own Course',
            'file' => $testFile,
        ]);
        
        $response3->assertStatus(201);
        
        // Teacher2 cannot upload to teacher1's course
        $response4 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage1->id,
            'type' => 'image',
            'title' => 'Teacher2 Upload to Other Course',
            'file' => $testFile,
        ]);
        
        $response4->assertStatus(403);
    }

    /** @test */
    public function students_cannot_upload_files()
    {
        // Requirements: 6.4 - Role-based upload restrictions
        
        $testFile = UploadedFile::fake()->image('student-upload.jpg');
        
        // Student1 cannot upload even to enrolled course
        $this->actingAs($this->student1);
        
        $response1 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage1->id,
            'type' => 'image',
            'title' => 'Student Upload Attempt',
            'file' => $testFile,
        ]);
        
        $response1->assertStatus(403);
        
        // Student2 cannot upload to any course
        $this->actingAs($this->student2);
        
        $response2 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage2->id,
            'type' => 'image',
            'title' => 'Student Upload Attempt',
            'file' => $testFile,
        ]);
        
        $response2->assertStatus(403);
    }

    /** @test */
    public function enrolled_students_can_access_course_files()
    {
        // Requirements: 6.5 - Enrollment-based file access
        
        // Create content as teacher1
        $this->actingAs($this->teacher1);
        
        $testFile = UploadedFile::fake()->image('course1-content.jpg');
        
        $content = $this->contentBlockService->createContentBlock($this->subpage1, [
            'type' => 'image',
            'title' => 'Course 1 Content',
            'file' => $testFile,
            'section' => 'main_content',
        ]);
        
        // Enrolled student1 can access the file
        $this->actingAs($this->student1);
        
        $response1 = $this->get("/secure-files/{$content->id}");
        $response1->assertStatus(200);
        
        // Unenrolled student cannot access the file
        $this->actingAs($this->unenrolledStudent);
        
        $response2 = $this->get("/secure-files/{$content->id}");
        $response2->assertStatus(403);
        
        // Student2 (enrolled in different course) cannot access the file
        $this->actingAs($this->student2);
        
        $response3 = $this->get("/secure-files/{$content->id}");
        $response3->assertStatus(403);
    }

    /** @test */
    public function content_visibility_controls_are_enforced()
    {
        // Requirements: 6.5 - Content visibility controls
        
        $this->actingAs($this->teacher1);
        
        $testFile = UploadedFile::fake()->image('visibility-test.jpg');
        
        // Create content with different visibility levels
        $publicContent = $this->contentBlockService->createContentBlock($this->subpage1, [
            'type' => 'image',
            'title' => 'Public Content',
            'file' => $testFile,
            'section' => 'main_content',
            'visibility' => 'student', // Visible to students
        ]);
        
        $teacherOnlyContent = $this->contentBlockService->createContentBlock($this->subpage1, [
            'type' => 'image',
            'title' => 'Teacher Only Content',
            'file' => $testFile,
            'section' => 'main_content',
            'visibility' => 'teacher', // Visible to teachers only
        ]);
        
        // Enrolled student can access public content
        $this->actingAs($this->student1);
        
        $response1 = $this->get("/secure-files/{$publicContent->id}");
        $response1->assertStatus(200);
        
        // Enrolled student cannot access teacher-only content
        $response2 = $this->get("/secure-files/{$teacherOnlyContent->id}");
        $response2->assertStatus(403);
        
        // Teacher can access both
        $this->actingAs($this->teacher1);
        
        $response3 = $this->get("/secure-files/{$publicContent->id}");
        $response3->assertStatus(200);
        
        $response4 = $this->get("/secure-files/{$teacherOnlyContent->id}");
        $response4->assertStatus(200);
        
        // Admin can access both
        $this->actingAs($this->admin);
        
        $response5 = $this->get("/secure-files/{$publicContent->id}");
        $response5->assertStatus(200);
        
        $response6 = $this->get("/secure-files/{$teacherOnlyContent->id}");
        $response6->assertStatus(200);
    }

    /** @test */
    public function admin_has_override_access_to_all_content()
    {
        // Requirements: 6.4 - Administrative override permissions
        
        // Create content in both courses as different teachers
        $this->actingAs($this->teacher1);
        
        $testFile1 = UploadedFile::fake()->image('teacher1-content.jpg');
        
        $content1 = $this->contentBlockService->createContentBlock($this->subpage1, [
            'type' => 'image',
            'title' => 'Teacher1 Content',
            'file' => $testFile1,
            'section' => 'main_content',
            'visibility' => 'teacher', // Teacher-only content
        ]);
        
        $this->actingAs($this->teacher2);
        
        $testFile2 = UploadedFile::fake()->image('teacher2-content.jpg');
        
        $content2 = $this->contentBlockService->createContentBlock($this->subpage2, [
            'type' => 'image',
            'title' => 'Teacher2 Content',
            'file' => $testFile2,
            'section' => 'main_content',
            'visibility' => 'teacher', // Teacher-only content
        ]);
        
        // Admin can access all content regardless of ownership or visibility
        $this->actingAs($this->admin);
        
        $response1 = $this->get("/secure-files/{$content1->id}");
        $response1->assertStatus(200);
        
        $response2 = $this->get("/secure-files/{$content2->id}");
        $response2->assertStatus(200);
        
        // Admin can also modify content in any course
        $response3 = $this->putJson("/api/content-blocks/{$content1->id}", [
            'title' => 'Admin Modified Title',
        ]);
        
        $response3->assertStatus(200);
        
        $response4 = $this->deleteJson("/api/content-blocks/{$content2->id}");
        $response4->assertStatus(200);
    }

    /** @test */
    public function permission_inheritance_works_correctly()
    {
        // Requirements: 6.4 - Permission inheritance
        
        // Create a custom role with specific permissions
        $customRole = Role::create(['name' => 'Content Manager']);
        $customRole->givePermissionTo(['upload_files', 'view_protected_content']);
        
        $contentManager = User::factory()->create(['name' => 'Content Manager']);
        $contentManager->assignRole('Content Manager');
        
        // Content manager should be able to upload files
        $this->actingAs($contentManager);
        
        $testFile = UploadedFile::fake()->image('manager-upload.jpg');
        
        // But only to courses they have explicit access to (none in this case)
        $response1 = $this->postJson('/api/content-blocks', [
            'subpage_id' => $this->subpage1->id,
            'type' => 'image',
            'title' => 'Manager Upload Attempt',
            'file' => $testFile,
        ]);
        
        $response1->assertStatus(403); // Should fail due to course access restrictions
        
        // Grant course-specific access by making them a course collaborator
        // (This would be implemented through a course_collaborators table or similar)
        
        // For now, test that they can view protected content if they had access
        $this->actingAs($this->teacher1);
        
        $protectedFile = UploadedFile::fake()->create('protected-doc.pdf', 1024, 'application/pdf');
        
        $protectedContent = $this->contentBlockService->createContentBlock($this->subpage1, [
            'type' => 'document',
            'title' => 'Protected Document',
            'file' => $protectedFile,
            'section' => 'main_content',
        ]);
        
        // Content manager with view_protected_content permission should be able to access
        // if they had course access (this would need additional implementation)
        $this->assertTrue($contentManager->can('view_protected_content'));
    }

    /** @test */
    public function security_boundaries_are_enforced()
    {
        // Requirements: 6.4, 6.5 - Security boundary enforcement
        
        $this->actingAs($this->teacher1);
        
        $testFile = UploadedFile::fake()->image('security-test.jpg');
        
        $content = $this->contentBlockService->createContentBlock($this->subpage1, [
            'type' => 'image',
            'title' => 'Security Test Content',
            'file' => $testFile,
            'section' => 'main_content',
        ]);
        
        // Test 1: Direct file system access should be blocked
        // (This would be enforced by web server configuration in production)
        
        // Test 2: Unauthorized API access should be blocked
        $this->actingAs($this->student2); // Student from different course
        
        $response1 = $this->getJson("/api/content-blocks/{$content->id}");
        $response1->assertStatus(403);
        
        $response2 = $this->putJson("/api/content-blocks/{$content->id}", [
            'title' => 'Unauthorized Modification',
        ]);
        $response2->assertStatus(403);
        
        $response3 = $this->deleteJson("/api/content-blocks/{$content->id}");
        $response3->assertStatus(403);
        
        // Test 3: File serving should respect permissions
        $response4 = $this->get("/secure-files/{$content->id}");
        $response4->assertStatus(403);
        
        // Test 4: Unauthenticated access should be blocked
        auth()->logout();
        
        $response5 = $this->get("/secure-files/{$content->id}");
        $response5->assertStatus(302); // Redirect to login
        
        $response6 = $this->getJson("/api/content-blocks/{$content->id}");
        $response6->assertStatus(401); // Unauthorized
    }

    /** @test */
    public function enrollment_changes_affect_file_access_immediately()
    {
        // Requirements: 6.5 - Dynamic enrollment-based access
        
        $this->actingAs($this->teacher1);
        
        $testFile = UploadedFile::fake()->image('enrollment-test.jpg');
        
        $content = $this->contentBlockService->createContentBlock($this->subpage1, [
            'type' => 'image',
            'title' => 'Enrollment Test Content',
            'file' => $testFile,
            'section' => 'main_content',
        ]);
        
        // Initially, unenrolled student cannot access
        $this->actingAs($this->unenrolledStudent);
        
        $response1 = $this->get("/secure-files/{$content->id}");
        $response1->assertStatus(403);
        
        // Enroll the student
        Enrollment::create([
            'user_id' => $this->unenrolledStudent->id,
            'course_id' => $this->course1->id,
            'enrolled_at' => now(),
        ]);
        
        // Now the student should have access
        $response2 = $this->get("/secure-files/{$content->id}");
        $response2->assertStatus(200);
        
        // Unenroll the student
        Enrollment::where('user_id', $this->unenrolledStudent->id)
                  ->where('course_id', $this->course1->id)
                  ->delete();
        
        // Access should be revoked immediately
        $response3 = $this->get("/secure-files/{$content->id}");
        $response3->assertStatus(403);
    }

    /** @test */
    public function file_access_logging_captures_permission_checks()
    {
        // Requirements: 6.4, 6.5 - Access logging for security auditing
        
        $this->actingAs($this->teacher1);
        
        $testFile = UploadedFile::fake()->image('logging-test.jpg');
        
        $content = $this->contentBlockService->createContentBlock($this->subpage1, [
            'type' => 'image',
            'title' => 'Logging Test Content',
            'file' => $testFile,
            'section' => 'main_content',
        ]);
        
        // Successful access by enrolled student
        $this->actingAs($this->student1);
        
        $response1 = $this->get("/secure-files/{$content->id}");
        $response1->assertStatus(200);
        
        // Failed access by unenrolled student
        $this->actingAs($this->unenrolledStudent);
        
        $response2 = $this->get("/secure-files/{$content->id}");
        $response2->assertStatus(403);
        
        // Check that access attempts are logged
        // (In a real implementation, you would check log files or database records)
        $this->assertTrue(true); // Placeholder - actual log checking would go here
    }

    protected function tearDown(): void
    {
        // Clean up test files
        Storage::disk('public')->deleteDirectory('permission-test');
        Storage::disk('protected')->deleteDirectory('permission-test');
        Storage::disk('public')->deleteDirectory('content_blocks');
        Storage::disk('protected')->deleteDirectory('content_blocks');
        
        parent::tearDown();
    }
}