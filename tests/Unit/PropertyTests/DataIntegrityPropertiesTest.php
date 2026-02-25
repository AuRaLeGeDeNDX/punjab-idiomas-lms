<?php

namespace Tests\Unit\PropertyTests;

use Tests\TestCase;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\User;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\SubmissionVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;

/**
 * Property-Based Tests for Data Integrity
 * 
 * Feature: complete-assignment-system
 * Tests Properties 2, 38, 41-42 from the design document
 * 
 * These tests verify critical data integrity properties that ensure
 * data consistency, versioning accuracy, and referential integrity.
 */
class DataIntegrityPropertiesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles if they don't exist
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
        
        $teacher = $this->createUserWithRole('Teacher');
        $student = $this->createUserWithRole('Student');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        // Create submission with multiple files
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
        ]);

        $fileCount = 5;
        $fileIds = [];
        
        for ($i = 0; $i < $fileCount; $i++) {
            $file = SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_name' => "test_file_{$i}.pdf",
                'file_path' => "assignments/test_file_{$i}.pdf",
                'file_size' => 1024000 + ($i * 1000),
                'mime_type' => 'application/pdf',
                'uploaded_at' => now(),
            ]);
            $fileIds[] = $file->id;
        }

        // Verify files exist before deletion
        $this->assertEquals($fileCount, SubmissionFile::where('submission_id', $submission->id)->count(),
            "Should have {$fileCount} files before deletion");

        // Delete the submission
        $submissionId = $submission->id;
        $submission->delete();

        // Verify all related files are deleted (cascade)
        $this->assertEquals(0, SubmissionFile::where('submission_id', $submissionId)->count(),
            "All submission files should be deleted when submission is deleted");

        // Verify no orphaned file records
        foreach ($fileIds as $fileId) {
            $this->assertDatabaseMissing('submission_files', ['id' => $fileId]);
        }
    }

    /**
     * Property 2 (Extended): Cascade Delete with Versions
     */
    public function test_property_2_cascade_delete_with_versions(): void
    {
        // Feature: complete-assignment-system, Property 2: Cascade Delete Integrity
        
        $teacher = $this->createUserWithRole('Teacher');
        $student = $this->createUserWithRole('Student');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => 'Original content',
        ]);

        // Create multiple versions
        $versionCount = 3;
        $versionIds = [];
        
        for ($i = 0; $i < $versionCount; $i++) {
            $version = $submission->createVersion();
            $versionIds[] = $version->id;
        }

        // Verify versions exist
        $this->assertEquals($versionCount, SubmissionVersion::where('submission_id', $submission->id)->count(),
            "Should have {$versionCount} versions before deletion");

        // Delete the submission
        $submissionId = $submission->id;
        $submission->delete();

        // Verify all versions are deleted
        $this->assertEquals(0, SubmissionVersion::where('submission_id', $submissionId)->count(),
            "All submission versions should be deleted when submission is deleted");

        foreach ($versionIds as $versionId) {
            $this->assertDatabaseMissing('submission_versions', ['id' => $versionId]);
        }
    }

    /**
     * Property 38: Version Creation on Edit
     * 
     * For any submission edit by a student, a new version record should be created
     * before the submission is updated, preserving the previous state.
     * 
     * **Validates: Requirements 9.2**
     */
    public function test_property_38_version_creation_on_edit(): void
    {
        // Feature: complete-assignment-system, Property 38: Version Creation on Edit
        
        $teacher = $this->createUserWithRole('Teacher');
        $student = $this->createUserWithRole('Student');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $originalContent = 'This is the original submission content.';
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => $originalContent,
        ]);

        // Create initial file
        $originalFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'original.pdf',
            'file_path' => 'assignments/original.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        // Verify no versions exist initially
        $this->assertEquals(0, $submission->versions()->count(),
            "Should have no versions before first edit");

        // Simulate edit: create version before updating
        $version1 = $submission->createVersion();
        
        // Update submission
        $newContent = 'This is the updated submission content.';
        $submission->update(['content' => $newContent]);

        // Verify version was created
        $this->assertEquals(1, $submission->versions()->count(),
            "Should have 1 version after first edit");

        // Verify version contains original content
        $this->assertEquals($originalContent, $version1->content,
            "Version should preserve original content");

        // Verify version contains file snapshot
        $fileSnapshot = $version1->file_paths_snapshot;
        $this->assertIsArray($fileSnapshot, "File snapshot should be an array");
        $this->assertCount(1, $fileSnapshot, "File snapshot should contain 1 file");
        $this->assertEquals('original.pdf', $fileSnapshot[0]['file_name'],
            "File snapshot should preserve original file name");

        // Simulate second edit
        $version2 = $submission->createVersion();
        $submission->update(['content' => 'Third version content']);

        // Verify second version was created
        $this->assertEquals(2, $submission->versions()->count(),
            "Should have 2 versions after second edit");

        // Verify second version contains the updated content (not the third)
        $this->assertEquals($newContent, $version2->content,
            "Second version should preserve content from before second edit");
    }

    /**
     * Property 38 (Extended): Version Number Incrementing
     */
    public function test_property_38_version_number_incrementing(): void
    {
        // Feature: complete-assignment-system, Property 38: Version Creation on Edit
        
        $teacher = $this->createUserWithRole('Teacher');
        $student = $this->createUserWithRole('Student');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => 'Original content',
        ]);

        // Create multiple versions
        $versions = [];
        for ($i = 1; $i <= 5; $i++) {
            $version = $submission->createVersion();
            $versions[] = $version;
            $submission->update(['content' => "Content version {$i}"]);
        }

        // Verify version numbers are sequential
        foreach ($versions as $index => $version) {
            $expectedVersionNumber = $index + 1;
            $this->assertEquals($expectedVersionNumber, $version->version_number,
                "Version {$index} should have version_number {$expectedVersionNumber}");
        }

        // Verify versions are ordered correctly
        $orderedVersions = $submission->versions()->orderBy('version_number')->get();
        for ($i = 0; $i < count($orderedVersions); $i++) {
            $this->assertEquals($i + 1, $orderedVersions[$i]->version_number,
                "Ordered version at index {$i} should have version_number " . ($i + 1));
        }
    }

    /**
     * Property 41: Version Diff Accuracy
     * 
     * For any two versions of a submission, the diff should correctly identify
     * added, removed, and unchanged text content.
     * 
     * **Validates: Requirements 9.5**
     */
    public function test_property_41_version_diff_accuracy(): void
    {
        // Feature: complete-assignment-system, Property 41: Version Diff Accuracy
        
        $teacher = $this->createUserWithRole('Teacher');
        $student = $this->createUserWithRole('Student');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => "Line 1\nLine 2\nLine 3",
        ]);

        // Create first version
        $version1 = $submission->createVersion();
        
        // Update submission with changes
        $submission->update(['content' => "Line 1\nLine 2 modified\nLine 4"]);
        
        // Create second version
        $version2 = $submission->createVersion();

        // Calculate diff between versions
        $diff = $version2->getDiff($version1);

        // Verify diff structure
        $this->assertArrayHasKey('added', $diff, "Diff should have 'added' key");
        $this->assertArrayHasKey('removed', $diff, "Diff should have 'removed' key");
        $this->assertArrayHasKey('unchanged', $diff, "Diff should have 'unchanged' key");

        // Verify added lines
        $this->assertContains('Line 2 modified', $diff['added'],
            "Diff should identify 'Line 2 modified' as added");
        $this->assertContains('Line 4', $diff['added'],
            "Diff should identify 'Line 4' as added");

        // Verify removed lines
        $this->assertContains('Line 3', $diff['removed'],
            "Diff should identify 'Line 3' as removed");

        // Verify unchanged lines
        $this->assertContains('Line 1', $diff['unchanged'],
            "Diff should identify 'Line 1' as unchanged");
    }

    /**
     * Property 41 (Extended): Complex Diff Scenarios
     */
    public function test_property_41_complex_diff_scenarios(): void
    {
        // Feature: complete-assignment-system, Property 41: Version Diff Accuracy
        
        $teacher = $this->createUserWithRole('Teacher');
        $student1 = $this->createUserWithRole('Student', ['name' => 'Student 1']);
        $student2 = $this->createUserWithRole('Student', ['name' => 'Student 2']);
        $student3 = $this->createUserWithRole('Student', ['name' => 'Student 3']);
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        // Test Case 1: All lines removed
        $submission1 = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student1->id,
            'content' => "Line A\nLine B\nLine C",
        ]);
        $v1 = $submission1->createVersion();
        $submission1->update(['content' => '']);
        $v2 = $submission1->createVersion();
        
        $diff1 = $v2->getDiff($v1);
        $this->assertCount(3, $diff1['removed'], "Should identify 3 removed lines");
        // Note: Empty string creates one empty line in the diff, so we check <= 1
        $this->assertLessThanOrEqual(1, count($diff1['added']), "Should have at most 1 added line (empty)");

        // Test Case 2: All lines added
        $submission2 = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student2->id,
            'content' => '',
        ]);
        $v3 = $submission2->createVersion();
        $submission2->update(['content' => "New Line 1\nNew Line 2"]);
        $v4 = $submission2->createVersion();
        
        $diff2 = $v4->getDiff($v3);
        $this->assertCount(2, $diff2['added'], "Should identify 2 added lines");
        // Note: Empty string creates one empty line in the diff
        $this->assertLessThanOrEqual(1, count($diff2['removed']), "Should have at most 1 removed line (empty)");

        // Test Case 3: No changes
        $submission3 = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student3->id,
            'content' => "Same Line 1\nSame Line 2",
        ]);
        $v5 = $submission3->createVersion();
        $v6 = $submission3->createVersion();
        
        $diff3 = $v6->getDiff($v5);
        $this->assertCount(0, $diff3['added'], "Should have no added lines when content is same");
        $this->assertCount(0, $diff3['removed'], "Should have no removed lines when content is same");
        $this->assertCount(2, $diff3['unchanged'], "Should identify 2 unchanged lines");
    }

    /**
     * Property 42: Version File Snapshot
     * 
     * For any previous version viewed, the files displayed should match the files
     * that were attached at that specific version, not the current files.
     * 
     * **Validates: Requirements 9.6**
     */
    public function test_property_42_version_file_snapshot(): void
    {
        // Feature: complete-assignment-system, Property 42: Version File Snapshot
        
        $teacher = $this->createUserWithRole('Teacher');
        $student = $this->createUserWithRole('Student');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => 'Original content',
        ]);

        // Add initial files
        $file1 = SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'document_v1.pdf',
            'file_path' => 'assignments/document_v1.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        $file2 = SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'image_v1.jpg',
            'file_path' => 'assignments/image_v1.jpg',
            'file_size' => 512000,
            'mime_type' => 'image/jpeg',
            'uploaded_at' => now(),
        ]);

        // Create version 1 (snapshot with 2 files)
        $version1 = $submission->createVersion();
        
        // Verify version 1 snapshot
        $snapshot1 = $version1->file_paths_snapshot;
        $this->assertIsArray($snapshot1, "Version 1 snapshot should be an array");
        $this->assertCount(2, $snapshot1, "Version 1 should have snapshot of 2 files");
        
        $fileNames1 = array_column($snapshot1, 'file_name');
        $this->assertContains('document_v1.pdf', $fileNames1,
            "Version 1 snapshot should contain document_v1.pdf");
        $this->assertContains('image_v1.jpg', $fileNames1,
            "Version 1 snapshot should contain image_v1.jpg");

        // Modify files: delete one, add another
        $file2->delete();
        
        $file3 = SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'spreadsheet_v2.xlsx',
            'file_path' => 'assignments/spreadsheet_v2.xlsx',
            'file_size' => 2048000,
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'uploaded_at' => now(),
        ]);

        // Create version 2 (snapshot with different files)
        $version2 = $submission->createVersion();
        
        // Verify version 2 snapshot
        $snapshot2 = $version2->file_paths_snapshot;
        $this->assertIsArray($snapshot2, "Version 2 snapshot should be an array");
        $this->assertCount(2, $snapshot2, "Version 2 should have snapshot of 2 files");
        
        $fileNames2 = array_column($snapshot2, 'file_name');
        $this->assertContains('document_v1.pdf', $fileNames2,
            "Version 2 snapshot should contain document_v1.pdf");
        $this->assertContains('spreadsheet_v2.xlsx', $fileNames2,
            "Version 2 snapshot should contain spreadsheet_v2.xlsx");
        $this->assertNotContains('image_v1.jpg', $fileNames2,
            "Version 2 snapshot should NOT contain deleted image_v1.jpg");

        // Verify version 1 snapshot is unchanged
        $version1->refresh();
        $snapshot1After = $version1->file_paths_snapshot;
        $this->assertCount(2, $snapshot1After, "Version 1 snapshot should still have 2 files");
        $fileNames1After = array_column($snapshot1After, 'file_name');
        $this->assertContains('image_v1.jpg', $fileNames1After,
            "Version 1 snapshot should still contain image_v1.jpg even though it was deleted later");
    }

    /**
     * Property 42 (Extended): File Metadata in Snapshot
     */
    public function test_property_42_file_metadata_in_snapshot(): void
    {
        // Feature: complete-assignment-system, Property 42: Version File Snapshot
        
        $teacher = $this->createUserWithRole('Teacher');
        $student = $this->createUserWithRole('Student');
        
        $course = Course::factory()->create(['teacher_id' => $teacher->id]);
        $assignment = Assignment::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $student->id,
            'content' => 'Content',
        ]);

        // Add file with specific metadata
        $file = SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'test_document.pdf',
            'file_path' => 'assignments/test_document.pdf',
            'file_size' => 1536000,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        // Create version
        $version = $submission->createVersion();
        
        // Verify snapshot contains all metadata
        $snapshot = $version->file_paths_snapshot;
        $this->assertCount(1, $snapshot);
        
        $snapshotFile = $snapshot[0];
        $this->assertEquals('test_document.pdf', $snapshotFile['file_name'],
            "Snapshot should preserve file name");
        $this->assertEquals('assignments/test_document.pdf', $snapshotFile['file_path'],
            "Snapshot should preserve file path");
        $this->assertEquals(1536000, $snapshotFile['file_size'],
            "Snapshot should preserve file size");
        $this->assertEquals('application/pdf', $snapshotFile['mime_type'],
            "Snapshot should preserve MIME type");
    }
}
