<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SubmissionFileEagerLoadingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_prevents_n_plus_one_queries_with_eager_loading()
    {
        $assignment = Assignment::factory()->create();

        // Create 5 submissions with 2 files each (different users to avoid unique constraint)
        for ($i = 0; $i < 5; $i++) {
            $user = User::factory()->create();
            $submission = Submission::factory()->create([
                'assignment_id' => $assignment->id,
                'user_id' => $user->id,
            ]);

            SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_name' => "test{$i}_1.pdf",
                'file_path' => "assignments/test{$i}_1.pdf",
                'file_size' => 1024000,
                'mime_type' => 'application/pdf',
                'uploaded_at' => now(),
            ]);

            SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_name' => "test{$i}_2.pdf",
                'file_path' => "assignments/test{$i}_2.pdf",
                'file_size' => 2048000,
                'mime_type' => 'application/pdf',
                'uploaded_at' => now(),
            ]);
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

        // With eager loading, we should have significantly fewer queries
        // The key assertion: eager loading should use fewer queries
        $this->assertLessThan($queriesWithoutEagerLoading, $queriesWithEagerLoading);
        
        // Verify we actually loaded the data
        $this->assertCount(5, $submissions);
        $this->assertCount(2, $submissions->first()->files);
    }

    /** @test */
    public function it_eager_loads_files_and_versions_together()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();

        // Create submission with files and versions
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'content' => 'Version 1',
        ]);

        SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'test.pdf',
            'file_path' => 'assignments/test.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        $submission->createVersion();

        $submission->content = 'Version 2';
        $submission->save();
        $submission->createVersion();

        // Test eager loading both relationships
        DB::enableQueryLog();
        $loadedSubmission = Submission::with(['files', 'versions'])
            ->find($submission->id);
        
        $fileCount = $loadedSubmission->files->count();
        $versionCount = $loadedSubmission->versions->count();
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be 3 queries: 1 for submission, 1 for files, 1 for versions
        $this->assertEquals(3, count($queries));
        $this->assertEquals(1, $fileCount);
        $this->assertEquals(2, $versionCount);
    }

    /** @test */
    public function it_can_eager_load_nested_relationships()
    {
        $assignment = Assignment::factory()->create();

        // Create multiple submissions (different users to avoid unique constraint)
        for ($i = 0; $i < 3; $i++) {
            $user = User::factory()->create();
            $submission = Submission::factory()->create([
                'assignment_id' => $assignment->id,
                'user_id' => $user->id,
            ]);

            SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_name' => "test{$i}.pdf",
                'file_path' => "assignments/test{$i}.pdf",
                'file_size' => 1024000,
                'mime_type' => 'application/pdf',
                'uploaded_at' => now(),
            ]);
        }

        // Eager load assignment with submissions and their files
        DB::enableQueryLog();
        $loadedAssignment = Assignment::with('submissions.files')
            ->find($assignment->id);
        
        foreach ($loadedAssignment->submissions as $submission) {
            $fileCount = $submission->files->count();
        }
        
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be 3 queries: 1 for assignment, 1 for submissions, 1 for all files
        $this->assertEquals(3, count($queries));
        $this->assertCount(3, $loadedAssignment->submissions);
    }
}
