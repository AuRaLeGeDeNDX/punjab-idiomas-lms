<?php

namespace Tests\Unit\PropertyTests;

use Tests\TestCase;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;

/**
 * Property-Based Tests for File Normalization
 * 
 * Feature: complete-assignment-system
 * Tests Properties 1-3 from the design document
 * 
 * These tests verify universal properties that should hold true
 * across all valid inputs and scenarios for file storage normalization.
 */
class FileNormalizationPropertiesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles if they don't exist
        Role::firstOrCreate(['name' => 'teacher']);
        Role::firstOrCreate(['name' => 'student']);
        Role::firstOrCreate(['name' => 'admin']);
        
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
     * Property 1: File Upload Creates Database Record
     * 
     * For any file uploaded by a student, a corresponding record should be created
     * in the submission_files table with accurate metadata (file_name, file_path,
     * file_size, mime_type, uploaded_at).
     * 
     * **Validates: Requirements 1.2**
     */
    public function test_property_1_file_upload_creates_database_record(): void
    {
        // Feature: complete-assignment-system, Property 1: File Upload Creates Database Record
        
        // Test with various file types and sizes
        $testCases = [
            ['name' => 'document.pdf', 'size' => 1024000, 'mime' => 'application/pdf'],
            ['name' => 'image.jpg', 'size' => 512000, 'mime' => 'image/jpeg'],
            ['name' => 'text.txt', 'size' => 2048, 'mime' => 'text/plain'],
            ['name' => 'presentation.pptx', 'size' => 2048000, 'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation'],
            ['name' => 'spreadsheet.xlsx', 'size' => 768000, 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'],
        ];

        foreach ($testCases as $iteration => $fileData) {
            // Create test data
            $teacher = $this->createUserWithRole('teacher');
            $student = $this->createUserWithRole('student');
            $course = Course::factory()->create(['teacher_id' => $teacher->id]);
            $assignment = Assignment::factory()->create([
                'course_id' => $course->id,
                'is_published' => true,
            ]);

            $submission = Submission::factory()->create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
            ]);

            // Create a file record (simulating file upload)
            $uploadedAt = now();
            $filePath = "assignments/{$submission->id}/{$fileData['name']}";
            
            $submissionFile = SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_name' => $fileData['name'],
                'file_path' => $filePath,
                'file_size' => $fileData['size'],
                'mime_type' => $fileData['mime'],
                'uploaded_at' => $uploadedAt,
            ]);

            // Verify database record was created
            $this->assertDatabaseHas('submission_files', [
                'submission_id' => $submission->id,
                'file_name' => $fileData['name'],
                'file_path' => $filePath,
                'file_size' => $fileData['size'],
                'mime_type' => $fileData['mime'],
            ]);

            // Verify all metadata is accurate
            $this->assertEquals($submission->id, $submissionFile->submission_id,
                "Iteration {$iteration}: submission_id should match");
            $this->assertEquals($fileData['name'], $submissionFile->file_name,
                "Iteration {$iteration}: file_name should match");
            $this->assertEquals($filePath, $submissionFile->file_path,
                "Iteration {$iteration}: file_path should match");
            $this->assertEquals($fileData['size'], $submissionFile->file_size,
                "Iteration {$iteration}: file_size should match");
            $this->assertEquals($fileData['mime'], $submissionFile->mime_type,
                "Iteration {$iteration}: mime_type should match");
            $this->assertNotNull($submissionFile->uploaded_at,
                "Iteration {$iteration}: uploaded_at should not be null");

            // Verify relationship works
            $this->assertInstanceOf(Submission::class, $submissionFile->submission,
                "Iteration {$iteration}: should have submission relationship");
            $this->assertEquals($submission->id, $submissionFile->submission->id,
                "Iteration {$iteration}: submission relationship should be correct");
        }
    }

    /**
     * Property 1 (Extended): Multiple Files Per Submission
     * 
     * Test that multiple files can be uploaded to a single submission,
     * each creating its own database record with accurate metadata.
     */
    public function test_property_1_multiple_files_per_submission(): void
    {
        // Feature: complete-assignment-system, Property 1: File Upload Creates Database Record
        
        $testCases = [
            ['fileCount' => 2],
            ['fileCount' => 3],
            ['fileCount' => 5],
        ];

        foreach ($testCases as $testCase) {
            $teacher = $this->createUserWithRole('teacher');
            $student = $this->createUserWithRole('student');
            $course = Course::factory()->create(['teacher_id' => $teacher->id]);
            $assignment = Assignment::factory()->create([
                'course_id' => $course->id,
                'is_published' => true,
            ]);

            $submission = Submission::factory()->create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
            ]);

            $fileTypes = [
                ['name' => 'file1.pdf', 'size' => 1024000, 'mime' => 'application/pdf'],
                ['name' => 'file2.jpg', 'size' => 512000, 'mime' => 'image/jpeg'],
                ['name' => 'file3.txt', 'size' => 2048, 'mime' => 'text/plain'],
                ['name' => 'file4.docx', 'size' => 768000, 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
                ['name' => 'file5.png', 'size' => 256000, 'mime' => 'image/png'],
            ];

            $uploadedFiles = [];
            for ($i = 0; $i < $testCase['fileCount']; $i++) {
                $fileData = $fileTypes[$i];
                $filePath = "assignments/{$submission->id}/{$fileData['name']}";
                
                $submissionFile = SubmissionFile::create([
                    'submission_id' => $submission->id,
                    'file_name' => $fileData['name'],
                    'file_path' => $filePath,
                    'file_size' => $fileData['size'],
                    'mime_type' => $fileData['mime'],
                    'uploaded_at' => now(),
                ]);

                $uploadedFiles[] = $submissionFile;
            }

            // Verify all files were created
            $this->assertCount($testCase['fileCount'], $uploadedFiles,
                "Should create {$testCase['fileCount']} file records");

            // Verify all files are associated with the submission
            $submission->refresh();
            $this->assertCount($testCase['fileCount'], $submission->files,
                "Submission should have {$testCase['fileCount']} files");

            // Verify each file has correct metadata
            foreach ($uploadedFiles as $index => $file) {
                $this->assertDatabaseHas('submission_files', [
                    'id' => $file->id,
                    'submission_id' => $submission->id,
                    'file_name' => $fileTypes[$index]['name'],
                ]);
            }

            // Verify total file size calculation
            $expectedTotalSize = array_sum(array_column(array_slice($fileTypes, 0, $testCase['fileCount']), 'size'));
            $actualTotalSize = $submission->getTotalFileSize();
            $this->assertEquals($expectedTotalSize, $actualTotalSize,
                "Total file size should be sum of all file sizes");
        }
    }

    /**
     * Property 2: Cascade Delete Integrity
     * 
     * For any submission with associated files, deleting the submission should
     * remove all related submission_files records, ensuring no orphaned file
     * records remain.
     * 
     * **Validates: Requirements 1.3, 9.8**
     */
    public function test_property_2_cascade_delete_integrity(): void
    {
        // Feature: complete-assignment-system, Property 2: Cascade Delete Integrity
        
        // Test with various numbers of files
        $testCases = [
            ['fileCount' => 1],
            ['fileCount' => 3],
            ['fileCount' => 5],
            ['fileCount' => 10],
        ];

        foreach ($testCases as $testCase) {
            $teacher = $this->createUserWithRole('teacher');
            $student = $this->createUserWithRole('student');
            $course = Course::factory()->create(['teacher_id' => $teacher->id]);
            $assignment = Assignment::factory()->create([
                'course_id' => $course->id,
                'is_published' => true,
            ]);

            $submission = Submission::factory()->create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
            ]);

            // Create multiple files
            $fileIds = [];
            for ($i = 0; $i < $testCase['fileCount']; $i++) {
                $submissionFile = SubmissionFile::create([
                    'submission_id' => $submission->id,
                    'file_name' => "test_file_{$i}.pdf",
                    'file_path' => "assignments/{$submission->id}/test_file_{$i}.pdf",
                    'file_size' => 1024000 + ($i * 1000),
                    'mime_type' => 'application/pdf',
                    'uploaded_at' => now(),
                ]);
                $fileIds[] = $submissionFile->id;
            }

            // Verify files exist before deletion
            $this->assertCount($testCase['fileCount'], $submission->files,
                "Should have {$testCase['fileCount']} files before deletion");

            foreach ($fileIds as $fileId) {
                $this->assertDatabaseHas('submission_files', ['id' => $fileId]);
            }

            // Delete the submission
            $submissionId = $submission->id;
            $submission->delete();

            // Verify all files were cascade deleted
            foreach ($fileIds as $fileId) {
                $this->assertDatabaseMissing('submission_files', ['id' => $fileId]);
            }

            // Verify no orphaned records remain
            $orphanedFiles = SubmissionFile::where('submission_id', $submissionId)->count();
            $this->assertEquals(0, $orphanedFiles,
                "No orphaned file records should remain after submission deletion");
        }
    }

    /**
     * Property 2 (Extended): Cascade Delete with Mixed Content
     * 
     * Test cascade delete with submissions that have both files and versions.
     */
    public function test_property_2_cascade_delete_with_versions(): void
    {
        // Feature: complete-assignment-system, Property 2: Cascade Delete Integrity
        
        $teacher = $this->createUserWithRole('teacher');
        $student = $this->createUserWithRole('student');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => 'Version 1',
        ]);

        // Create files
        $fileIds = [];
        for ($i = 0; $i < 3; $i++) {
            $submissionFile = SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_name' => "file_{$i}.pdf",
                'file_path' => "assignments/{$submission->id}/file_{$i}.pdf",
                'file_size' => 1024000,
                'mime_type' => 'application/pdf',
                'uploaded_at' => now(),
            ]);
            $fileIds[] = $submissionFile->id;
        }

        // Create versions
        $version1 = $submission->createVersion();
        $submission->content = 'Version 2';
        $submission->save();
        $version2 = $submission->createVersion();

        $versionIds = [$version1->id, $version2->id];

        // Verify everything exists
        $this->assertCount(3, $submission->files);
        $this->assertCount(2, $submission->versions);

        // Delete submission
        $submissionId = $submission->id;
        $submission->delete();

        // Verify all files were deleted
        foreach ($fileIds as $fileId) {
            $this->assertDatabaseMissing('submission_files', ['id' => $fileId]);
        }

        // Verify all versions were deleted
        foreach ($versionIds as $versionId) {
            $this->assertDatabaseMissing('submission_versions', ['id' => $versionId]);
        }

        // Verify no orphaned records
        $this->assertEquals(0, SubmissionFile::where('submission_id', $submissionId)->count());
        $this->assertEquals(0, DB::table('submission_versions')->where('submission_id', $submissionId)->count());
    }

    /**
     * Property 3: Query Efficiency (N+1 Prevention)
     * 
     * For any query retrieving submissions with files, the number of database
     * queries should remain constant regardless of the number of submissions
     * returned (eager loading verification).
     * 
     * **Validates: Requirements 1.6**
     */
    public function test_property_3_query_efficiency_n_plus_one_prevention(): void
    {
        // Feature: complete-assignment-system, Property 3: Query Efficiency (N+1 Prevention)
        
        // Test with different numbers of submissions
        $testCases = [
            ['submissionCount' => 5, 'filesPerSubmission' => 2],
            ['submissionCount' => 10, 'filesPerSubmission' => 3],
            ['submissionCount' => 20, 'filesPerSubmission' => 2],
            ['submissionCount' => 50, 'filesPerSubmission' => 1],
        ];

        foreach ($testCases as $testCase) {
            $teacher = $this->createUserWithRole('teacher');
            $course = Course::factory()->create(['teacher_id' => $teacher->id]);
            $assignment = Assignment::factory()->create([
                'course_id' => $course->id,
                'is_published' => true,
            ]);

            // Create submissions with files
            for ($i = 0; $i < $testCase['submissionCount']; $i++) {
                $student = $this->createUserWithRole('student');
                $submission = Submission::factory()->create([
                    'assignment_id' => $assignment->id,
                    'user_id' => $student->id,
                ]);

                for ($j = 0; $j < $testCase['filesPerSubmission']; $j++) {
                    SubmissionFile::create([
                        'submission_id' => $submission->id,
                        'file_name' => "file_{$i}_{$j}.pdf",
                        'file_path' => "assignments/{$submission->id}/file_{$i}_{$j}.pdf",
                        'file_size' => 1024000,
                        'mime_type' => 'application/pdf',
                        'uploaded_at' => now(),
                    ]);
                }
            }

            // Test WITHOUT eager loading (N+1 problem)
            DB::enableQueryLog();
            DB::flushQueryLog();
            
            $submissions = Submission::where('assignment_id', $assignment->id)->get();
            foreach ($submissions as $submission) {
                $fileCount = $submission->files->count(); // This triggers a query per submission
            }
            
            $queriesWithoutEagerLoading = count(DB::getQueryLog());
            DB::disableQueryLog();

            // Test WITH eager loading (no N+1 problem)
            DB::enableQueryLog();
            DB::flushQueryLog();
            
            $submissions = Submission::with('files')->where('assignment_id', $assignment->id)->get();
            foreach ($submissions as $submission) {
                $fileCount = $submission->files->count(); // This uses the eager loaded data
            }
            
            $queriesWithEagerLoading = count(DB::getQueryLog());
            DB::disableQueryLog();

            // Verify eager loading reduces queries
            $this->assertLessThan($queriesWithoutEagerLoading, $queriesWithEagerLoading,
                "Eager loading should use fewer queries than lazy loading for {$testCase['submissionCount']} submissions");

            // Verify eager loading uses constant queries (should be 2: 1 for submissions, 1 for all files)
            $this->assertEquals(2, $queriesWithEagerLoading,
                "Eager loading should use exactly 2 queries regardless of submission count");

            // Verify without eager loading scales linearly (1 for submissions + 1 per submission for files)
            $expectedQueriesWithoutEagerLoading = 1 + $testCase['submissionCount'];
            $this->assertEquals($expectedQueriesWithoutEagerLoading, $queriesWithoutEagerLoading,
                "Without eager loading, queries should scale linearly with submission count");

            // Verify data integrity (all files were loaded)
            $totalFiles = $submissions->sum(fn($s) => $s->files->count());
            $expectedTotalFiles = $testCase['submissionCount'] * $testCase['filesPerSubmission'];
            $this->assertEquals($expectedTotalFiles, $totalFiles,
                "All files should be loaded with eager loading");
        }
    }

    /**
     * Property 3 (Extended): Nested Eager Loading Efficiency
     * 
     * Test that nested eager loading (assignment -> submissions -> files)
     * also prevents N+1 queries.
     */
    public function test_property_3_nested_eager_loading_efficiency(): void
    {
        // Feature: complete-assignment-system, Property 3: Query Efficiency (N+1 Prevention)
        
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);

        // Create multiple assignments with submissions and files
        $assignmentCount = 3;
        $submissionsPerAssignment = 5;
        $filesPerSubmission = 2;

        for ($i = 0; $i < $assignmentCount; $i++) {
            $assignment = Assignment::factory()->create([
                'course_id' => $course->id,
                'is_published' => true,
                'title' => "Assignment {$i}",
            ]);

            for ($j = 0; $j < $submissionsPerAssignment; $j++) {
                $student = $this->createUserWithRole('student');
                $submission = Submission::factory()->create([
                    'assignment_id' => $assignment->id,
                    'user_id' => $student->id,
                ]);

                for ($k = 0; $k < $filesPerSubmission; $k++) {
                    SubmissionFile::create([
                        'submission_id' => $submission->id,
                        'file_name' => "file_{$i}_{$j}_{$k}.pdf",
                        'file_path' => "assignments/{$submission->id}/file_{$i}_{$j}_{$k}.pdf",
                        'file_size' => 1024000,
                        'mime_type' => 'application/pdf',
                        'uploaded_at' => now(),
                    ]);
                }
            }
        }

        // Test nested eager loading
        DB::enableQueryLog();
        DB::flushQueryLog();
        
        $assignments = Assignment::with('submissions.files')
            ->where('course_id', $course->id)
            ->get();
        
        foreach ($assignments as $assignment) {
            foreach ($assignment->submissions as $submission) {
                $fileCount = $submission->files->count();
            }
        }
        
        $queriesWithNestedEagerLoading = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Should be 3 queries: 1 for assignments, 1 for all submissions, 1 for all files
        $this->assertEquals(3, $queriesWithNestedEagerLoading,
            "Nested eager loading should use exactly 3 queries");

        // Verify all data was loaded correctly
        $totalSubmissions = $assignments->sum(fn($a) => $a->submissions->count());
        $totalFiles = $assignments->sum(fn($a) => 
            $a->submissions->sum(fn($s) => $s->files->count())
        );

        $this->assertEquals($assignmentCount * $submissionsPerAssignment, $totalSubmissions,
            "All submissions should be loaded");
        $this->assertEquals($assignmentCount * $submissionsPerAssignment * $filesPerSubmission, $totalFiles,
            "All files should be loaded");
    }

    /**
     * Property 3 (Extended): Query Efficiency with Filtering
     * 
     * Test that eager loading remains efficient even with WHERE clauses and filters.
     */
    public function test_property_3_query_efficiency_with_filtering(): void
    {
        // Feature: complete-assignment-system, Property 3: Query Efficiency (N+1 Prevention)
        
        $teacher = $this->createUserWithRole('teacher');
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        // Create submissions with different statuses
        $submissionCount = 20;
        for ($i = 0; $i < $submissionCount; $i++) {
            $student = $this->createUserWithRole('student');
            $submission = Submission::factory()->create([
                'assignment_id' => $assignment->id,
                'user_id' => $student->id,
                'status' => $i % 2 === 0 ? 'submitted' : 'graded',
            ]);

            SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_name' => "file_{$i}.pdf",
                'file_path' => "assignments/{$submission->id}/file_{$i}.pdf",
                'file_size' => 1024000,
                'mime_type' => 'application/pdf',
                'uploaded_at' => now(),
            ]);
        }

        // Test eager loading with WHERE clause
        DB::enableQueryLog();
        DB::flushQueryLog();
        
        $submissions = Submission::with('files')
            ->where('assignment_id', $assignment->id)
            ->where('status', 'submitted')
            ->get();
        
        foreach ($submissions as $submission) {
            $fileCount = $submission->files->count();
        }
        
        $queriesWithFilter = count(DB::getQueryLog());
        DB::disableQueryLog();

        // Should still be 2 queries even with filtering
        $this->assertEquals(2, $queriesWithFilter,
            "Eager loading with filtering should still use exactly 2 queries");

        // Verify correct submissions were loaded
        $this->assertEquals(10, $submissions->count(),
            "Should load only submitted submissions");

        // Verify all files were loaded for filtered submissions
        $totalFiles = $submissions->sum(fn($s) => $s->files->count());
        $this->assertEquals(10, $totalFiles,
            "All files for filtered submissions should be loaded");
    }
}
