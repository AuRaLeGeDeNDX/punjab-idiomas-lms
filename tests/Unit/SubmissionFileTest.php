<?php

namespace Tests\Unit;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionFileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_submission_file()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
        ]);

        $submissionFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'test.pdf',
            'file_path' => 'assignments/test.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        $this->assertDatabaseHas('submission_files', [
            'submission_id' => $submission->id,
            'file_name' => 'test.pdf',
        ]);

        $this->assertEquals('test.pdf', $submissionFile->file_name);
        $this->assertEquals(1024000, $submissionFile->file_size);
    }

    /** @test */
    public function it_belongs_to_a_submission()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
        ]);

        $submissionFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'test.pdf',
            'file_path' => 'assignments/test.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        $this->assertInstanceOf(Submission::class, $submissionFile->submission);
        $this->assertEquals($submission->id, $submissionFile->submission->id);
    }

    /** @test */
    public function it_formats_file_size_correctly()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
        ]);

        $submissionFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'test.pdf',
            'file_path' => 'assignments/test.pdf',
            'file_size' => 1024000, // 1000 KB
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        $formattedSize = $submissionFile->getFormattedSize();
        $this->assertStringContainsString('KB', $formattedSize);
    }

    /** @test */
    public function it_can_detect_file_types()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
        ]);

        $pdfFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'test.pdf',
            'file_path' => 'assignments/test.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        $imageFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'test.jpg',
            'file_path' => 'assignments/test.jpg',
            'file_size' => 512000,
            'mime_type' => 'image/jpeg',
            'uploaded_at' => now(),
        ]);

        $this->assertTrue($pdfFile->isPdf());
        $this->assertTrue($pdfFile->isDocument());
        $this->assertFalse($pdfFile->isImage());

        $this->assertTrue($imageFile->isImage());
        $this->assertFalse($imageFile->isPdf());
    }

    /** @test */
    public function submission_has_many_files()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
        ]);

        SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'test1.pdf',
            'file_path' => 'assignments/test1.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'test2.pdf',
            'file_path' => 'assignments/test2.pdf',
            'file_size' => 2048000,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        $this->assertCount(2, $submission->files);
        $this->assertEquals(3072000, $submission->getTotalFileSize());
    }

    /** @test */
    public function deleting_submission_cascades_to_files()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
        ]);

        $submissionFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'test.pdf',
            'file_path' => 'assignments/test.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        $fileId = $submissionFile->id;

        // Delete the submission
        $submission->delete();

        // Verify the file was also deleted
        $this->assertDatabaseMissing('submission_files', ['id' => $fileId]);
    }
}
