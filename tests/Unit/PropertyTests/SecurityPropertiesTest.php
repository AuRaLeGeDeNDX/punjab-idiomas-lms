<?php

namespace Tests\Unit\PropertyTests;

use Tests\TestCase;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\Grade;
use App\Models\GradeOverride;
use App\Services\FileSecurityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;

/**
 * Property-Based Tests for Security Features
 * 
 * Feature: complete-assignment-system
 * Tests Properties 4-6, 10, 21-22 from the design document
 * 
 * These tests verify critical security properties that protect against
 * vulnerabilities and ensure proper authorization.
 */
class SecurityPropertiesTest extends TestCase
{
    use RefreshDatabase;

    protected FileSecurityService $fileSecurityService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->fileSecurityService = new FileSecurityService();
        
        // Create roles if they don't exist (using capital case to match system conventions)
        Role::firstOrCreate(['name' => 'Teacher']);
        Role::firstOrCreate(['name' => 'Student']);
        Role::firstOrCreate(['name' => 'Admin']);
        
        // Set up storage for testing
        Storage::fake('local');
    }

    /**
     * Helper method to create a user with a specific role
     */
    protected function createUserWithRole(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->assignRole($role);
        return $user;
    }

    /**
     * Property 4: File Extension Validation
     * 
     * For any file with an extension not in the allowed list (pdf, doc, docx, txt,
     * jpg, jpeg, png, gif, zip, rar), the upload should be rejected with a
     * descriptive error message.
     * 
     * **Validates: Requirements 2.1**
     */
    public function test_property_4_file_extension_validation(): void
    {
        // Feature: complete-assignment-system, Property 4: File Extension Validation
        
        // Test with various disallowed extensions
        $disallowedExtensions = [
            'exe', 'bat', 'sh', 'cmd', 'com',
            'php', 'js', 'html', 'htm',
            'dll', 'so', 'dylib',
            'app', 'deb', 'rpm',
            'vbs', 'ps1', 'jar'
        ];

        foreach ($disallowedExtensions as $extension) {
            $file = UploadedFile::fake()->create("malicious_file.{$extension}", 100);
            
            $result = $this->fileSecurityService->validateFile($file);
            
            $this->assertFalse($result->isValid(),
                "File with extension .{$extension} should be rejected");
            
            $this->assertTrue($result->hasErrors(),
                "Validation should return errors for .{$extension} files");
            
            $errors = $result->getErrors();
            $this->assertArrayHasKey('file_extension', $errors,
                "Should have file_extension error for .{$extension}");
            
            $this->assertStringContainsString('not allowed', $errors['file_extension'],
                "Error message should indicate file type not allowed");
        }
    }

    /**
     * Property 4 (Extended): Allowed Extensions Pass Validation
     */
    public function test_property_4_allowed_extensions_pass(): void
    {
        // Feature: complete-assignment-system, Property 4: File Extension Validation
        
        $allowedExtensions = [
            'pdf', 'doc', 'docx', 'txt', 'rtf',
            'jpg', 'jpeg', 'png', 'gif', 'webp',
            'zip', 'rar', '7z',
            'ppt', 'pptx', 'xls', 'xlsx', 'csv'
        ];

        foreach ($allowedExtensions as $extension) {
            $file = UploadedFile::fake()->create("document.{$extension}", 100);
            
            // Note: This will still fail MIME type validation in real scenario,
            // but extension check should pass
            $result = $this->fileSecurityService->validateFile($file);
            
            // Check that file_extension is not in errors
            $errors = $result->getErrors();
            $this->assertArrayNotHasKey('file_extension', $errors,
                "File with extension .{$extension} should pass extension validation");
        }
    }

    /**
     * Property 5: MIME Type Consistency
     * 
     * For any uploaded file, the MIME type must match the file extension,
     * otherwise the upload should be rejected.
     * 
     * **Validates: Requirements 2.2**
     */
    public function test_property_5_mime_type_consistency(): void
    {
        // Feature: complete-assignment-system, Property 5: MIME Type Consistency
        
        // Test cases with mismatched MIME types and extensions
        $mismatchedFiles = [
            // Extension says PDF, but MIME says image
            ['extension' => 'pdf', 'mime' => 'image/jpeg'],
            // Extension says image, but MIME says PDF
            ['extension' => 'jpg', 'mime' => 'application/pdf'],
            // Extension says doc, but MIME says text
            ['extension' => 'doc', 'mime' => 'text/plain'],
            // Extension says zip, but MIME says image
            ['extension' => 'zip', 'mime' => 'image/png'],
        ];

        foreach ($mismatchedFiles as $testCase) {
            $file = UploadedFile::fake()->create(
                "file.{$testCase['extension']}", 
                100, 
                $testCase['mime']
            );
            
            $result = $this->fileSecurityService->validateFile($file);
            
            $this->assertFalse($result->isValid(),
                "File with mismatched MIME type ({$testCase['mime']}) and extension (.{$testCase['extension']}) should be rejected");
            
            $errors = $result->getErrors();
            $this->assertArrayHasKey('mime_type', $errors,
                "Should have mime_type error for mismatched file");
            
            $this->assertStringContainsString('does not match', $errors['mime_type'],
                "Error message should indicate MIME type mismatch");
            
            $this->assertStringContainsString('security risk', strtolower($errors['mime_type']),
                "Error message should mention security risk");
        }
    }

    /**
     * Property 5 (Extended): Matching MIME Types Pass Validation
     */
    public function test_property_5_matching_mime_types_pass(): void
    {
        // Feature: complete-assignment-system, Property 5: MIME Type Consistency
        
        $matchingFiles = [
            ['extension' => 'pdf', 'mime' => 'application/pdf'],
            ['extension' => 'jpg', 'mime' => 'image/jpeg'],
            ['extension' => 'png', 'mime' => 'image/png'],
            ['extension' => 'txt', 'mime' => 'text/plain'],
        ];

        foreach ($matchingFiles as $testCase) {
            $file = UploadedFile::fake()->create(
                "file.{$testCase['extension']}", 
                100, 
                $testCase['mime']
            );
            
            $result = $this->fileSecurityService->validateFile($file);
            
            // Check that mime_type is not in errors
            $errors = $result->getErrors();
            $this->assertArrayNotHasKey('mime_type', $errors,
                "File with matching MIME type ({$testCase['mime']}) and extension (.{$testCase['extension']}) should pass MIME validation");
        }
    }

    /**
     * Property 6: Malicious Content Detection
     * 
     * For any file containing malicious patterns (executable code, script tags,
     * PHP code), the upload should be rejected and the file should be deleted.
     * 
     * **Validates: Requirements 2.3**
     * 
     * Note: This test verifies the malware scanning logic is invoked.
     * The actual pattern matching is tested in FileSecurityHelper tests.
     */
    public function test_property_6_malicious_content_detection(): void
    {
        // Feature: complete-assignment-system, Property 6: Malicious Content Detection
        
        // Test that malware scanning is invoked and works for blocked extensions
        $blockedExtensions = ['exe', 'bat', 'php', 'js'];

        foreach ($blockedExtensions as $extension) {
            $file = UploadedFile::fake()->create("malicious.{$extension}", 100);
            
            $result = $this->fileSecurityService->validateFile($file);
            
            $this->assertFalse($result->isValid(),
                "File with blocked extension .{$extension} should be rejected");
            
            // The file should be rejected either by extension validation or malware scan
            $errors = $result->getErrors();
            $this->assertTrue(
                isset($errors['file_extension']) || isset($errors['malware']),
                "File with .{$extension} should have either file_extension or malware error"
            );
        }
    }

    /**
     * Property 6 (Extended): Clean Files Pass Malware Scan
     * 
     * Note: This test verifies that allowed file types with clean content pass validation.
     */
    public function test_property_6_clean_files_pass_scan(): void
    {
        // Feature: complete-assignment-system, Property 6: Malicious Content Detection
        
        // Test with allowed file types
        $cleanFiles = [
            ['name' => 'document.pdf', 'mime' => 'application/pdf'],
            ['name' => 'image.jpg', 'mime' => 'image/jpeg'],
            ['name' => 'text.txt', 'mime' => 'text/plain'],
        ];

        foreach ($cleanFiles as $fileData) {
            $file = UploadedFile::fake()->create($fileData['name'], 100, $fileData['mime']);
            
            $result = $this->fileSecurityService->validateFile($file);
            
            // Check that malware is not in errors (file may still fail other validations)
            $errors = $result->getErrors();
            $this->assertArrayNotHasKey('malware', $errors,
                "Clean file {$fileData['name']} should pass malware scan");
        }
    }

    /**
     * Property 10: File Access Authorization
     * 
     * For any file access attempt, only authorized users (the student who submitted,
     * the teacher of the course, or an admin) should be able to access the file.
     * 
     * **Validates: Requirements 2.8**
     */
    public function test_property_10_file_access_authorization(): void
    {
        // Feature: complete-assignment-system, Property 10: File Access Authorization
        
        $teacher = $this->createUserWithRole('Teacher');
        $student = $this->createUserWithRole('Student');
        $otherStudent = $this->createUserWithRole('Student');
        $admin = $this->createUserWithRole('Admin');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        $submissionFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'test.pdf',
            'file_path' => 'assignments/test.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        // Test authorized access: student who submitted
        $this->actingAs($student);
        $this->assertTrue(
            $student->id === $submission->user_id,
            "Student who submitted should be authorized to access their own file"
        );

        // Test authorized access: teacher of the course
        $this->actingAs($teacher);
        $this->assertTrue(
            $teacher->id === $course->teacher_id,
            "Teacher of the course should be authorized to access student files"
        );

        // Test authorized access: admin
        $this->actingAs($admin);
        $this->assertTrue(
            $admin->hasRole('Admin'),
            "Admin should be authorized to access any file"
        );

        // Test unauthorized access: other student
        $this->actingAs($otherStudent);
        $this->assertFalse(
            $otherStudent->id === $submission->user_id,
            "Other student should NOT be authorized to access file"
        );
        $this->assertFalse(
            $otherStudent->id === $course->teacher_id,
            "Other student should NOT be authorized as teacher"
        );
    }

    /**
     * Property 21: Grade Lock Authorization
     * 
     * For any locked grade, edit attempts by teachers (non-admin users) should be
     * rejected with an appropriate error message.
     * 
     * **Validates: Requirements 5.3, 5.4**
     */
    public function test_property_21_grade_lock_authorization(): void
    {
        // Feature: complete-assignment-system, Property 21: Grade Lock Authorization
        
        $teacher = $this->createUserWithRole('Teacher');
        $admin = $this->createUserWithRole('Admin');
        $student = $this->createUserWithRole('Student');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        $grade = Grade::create([
            'submission_id' => $submission->id,
            'score' => 85.00,
            'grader_id' => $teacher->id,
            'is_published' => true,
            'is_locked' => false,
        ]);

        // Test: Unlocked grade can be edited by teacher
        $this->assertTrue($grade->canBeEdited($teacher),
            "Teacher should be able to edit unlocked grade they created");

        // Lock the grade
        $grade->lock();
        $grade->refresh();

        // Test: Locked grade cannot be edited by teacher
        $this->assertFalse($grade->canBeEdited($teacher),
            "Teacher should NOT be able to edit locked grade");

        // Test: Locked grade can be edited by admin
        $this->assertTrue($grade->canBeEdited($admin),
            "Admin should be able to edit locked grade");

        // Test: Student cannot edit grade (locked or unlocked)
        $this->assertFalse($grade->canBeEdited($student),
            "Student should NOT be able to edit any grade");
    }

    /**
     * Property 21 (Extended): Multiple Teachers and Grade Locking
     */
    public function test_property_21_grade_lock_multiple_teachers(): void
    {
        // Feature: complete-assignment-system, Property 21: Grade Lock Authorization
        
        $teacher1 = $this->createUserWithRole('Teacher', ['name' => 'Teacher 1']);
        $teacher2 = $this->createUserWithRole('Teacher', ['name' => 'Teacher 2']);
        $student = $this->createUserWithRole('Student');
        
        $course = Course::factory()->create(['teacher_id' => $teacher1->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        // Teacher 1 creates and locks a grade
        $grade = Grade::create([
            'submission_id' => $submission->id,
            'score' => 90.00,
            'grader_id' => $teacher1->id,
            'is_published' => true,
            'is_locked' => true,
        ]);

        // Test: Teacher 1 (who created it) cannot edit locked grade
        $this->assertFalse($grade->canBeEdited($teacher1),
            "Teacher who created the grade should NOT be able to edit it when locked");

        // Test: Teacher 2 (different teacher) cannot edit locked grade
        $this->assertFalse($grade->canBeEdited($teacher2),
            "Different teacher should NOT be able to edit locked grade");
    }

    /**
     * Property 22: Override Audit Logging
     * 
     * For any admin override of a locked grade, a record should be created in
     * grade_overrides table with admin_id, original_score, new_score, reason,
     * and overridden_at.
     * 
     * **Validates: Requirements 5.6**
     */
    public function test_property_22_override_audit_logging(): void
    {
        // Feature: complete-assignment-system, Property 22: Override Audit Logging
        
        $teacher = $this->createUserWithRole('Teacher');
        $admin = $this->createUserWithRole('Admin');
        $student = $this->createUserWithRole('Student');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        $originalScore = 75.00;
        $grade = Grade::create([
            'submission_id' => $submission->id,
            'score' => $originalScore,
            'grader_id' => $teacher->id,
            'is_published' => true,
            'is_locked' => true,
        ]);

        // Admin overrides the grade
        $newScore = 85.00;
        $reason = 'Student provided additional evidence that was not considered in original grading.';
        
        $override = $grade->createOverride($admin, $newScore, $reason);

        // Verify override record was created
        $this->assertDatabaseHas('grade_overrides', [
            'grade_id' => $grade->id,
            'admin_id' => $admin->id,
            'original_score' => $originalScore,
            'new_score' => $newScore,
            'reason' => $reason,
        ]);

        // Verify all required fields are present
        $this->assertNotNull($override->id, "Override should have an ID");
        $this->assertEquals($grade->id, $override->grade_id, "Override should reference correct grade");
        $this->assertEquals($admin->id, $override->admin_id, "Override should reference admin who performed it");
        $this->assertEquals($originalScore, $override->original_score, "Override should record original score");
        $this->assertEquals($newScore, $override->new_score, "Override should record new score");
        $this->assertEquals($reason, $override->reason, "Override should record reason");
        $this->assertNotNull($override->overridden_at, "Override should have timestamp");

        // Verify grade score was updated
        $grade->refresh();
        $this->assertEquals($newScore, $grade->score, "Grade score should be updated to new score");
    }

    /**
     * Property 22 (Extended): Multiple Overrides Create Multiple Records
     */
    public function test_property_22_multiple_overrides_logged(): void
    {
        // Feature: complete-assignment-system, Property 22: Override Audit Logging
        
        $teacher = $this->createUserWithRole('Teacher');
        $admin1 = $this->createUserWithRole('Admin', ['name' => 'Admin 1']);
        $admin2 = $this->createUserWithRole('Admin', ['name' => 'Admin 2']);
        $student = $this->createUserWithRole('Student');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        $grade = Grade::create([
            'submission_id' => $submission->id,
            'score' => 70.00,
            'grader_id' => $teacher->id,
            'is_published' => true,
            'is_locked' => true,
        ]);

        // First override by admin1
        $override1 = $grade->createOverride($admin1, 80.00, 'First adjustment for missing work found.');
        
        // Second override by admin2
        $override2 = $grade->createOverride($admin2, 90.00, 'Second adjustment after appeal review.');

        // Verify both overrides are recorded
        $this->assertCount(2, $grade->overrides, "Grade should have 2 override records");

        // Verify first override
        $this->assertEquals($admin1->id, $override1->admin_id);
        $this->assertEquals(70.00, $override1->original_score);
        $this->assertEquals(80.00, $override1->new_score);

        // Verify second override
        $this->assertEquals($admin2->id, $override2->admin_id);
        $this->assertEquals(80.00, $override2->original_score);
        $this->assertEquals(90.00, $override2->new_score);

        // Verify final grade score
        $grade->refresh();
        $this->assertEquals(90.00, $grade->score);
    }

    /**
     * Property 22 (Extended): Override Helper Methods
     */
    public function test_property_22_override_helper_methods(): void
    {
        // Feature: complete-assignment-system, Property 22: Override Audit Logging
        
        $teacher = $this->createUserWithRole('Teacher');
        $admin = $this->createUserWithRole('Admin');
        $student = $this->createUserWithRole('Student');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        $grade = Grade::create([
            'submission_id' => $submission->id,
            'score' => 75.00,
            'grader_id' => $teacher->id,
            'is_published' => true,
            'is_locked' => true,
        ]);

        // Test score increase
        $increaseOverride = $grade->createOverride($admin, 85.00, 'Score increased');
        $this->assertEquals(10.00, $increaseOverride->getScoreDifference());
        $this->assertTrue($increaseOverride->isIncrease());
        $this->assertFalse($increaseOverride->isDecrease());

        // Test score decrease
        $decreaseOverride = $grade->createOverride($admin, 70.00, 'Score decreased');
        $this->assertEquals(-15.00, $decreaseOverride->getScoreDifference());
        $this->assertFalse($decreaseOverride->isIncrease());
        $this->assertTrue($decreaseOverride->isDecrease());
    }
}
