<?php

namespace Tests\Unit;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\SubmissionVersion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubmissionVersionTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_submission_version()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'content' => 'Original content',
        ]);

        $version = $submission->createVersion();

        $this->assertDatabaseHas('submission_versions', [
            'submission_id' => $submission->id,
            'version_number' => 1,
            'content' => 'Original content',
        ]);

        $this->assertEquals(1, $version->version_number);
        $this->assertEquals('Original content', $version->content);
    }

    /** @test */
    public function it_creates_file_snapshot_in_version()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'content' => 'Original content',
        ]);

        // Add files to submission
        SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_name' => 'test.pdf',
            'file_path' => 'assignments/test.pdf',
            'file_size' => 1024000,
            'mime_type' => 'application/pdf',
            'uploaded_at' => now(),
        ]);

        $version = $submission->createVersion();

        $this->assertNotEmpty($version->file_paths_snapshot);
        $this->assertCount(1, $version->file_paths_snapshot);
        $this->assertEquals('test.pdf', $version->file_paths_snapshot[0]['file_name']);
    }

    /** @test */
    public function it_increments_version_numbers()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'content' => 'Version 1',
        ]);

        $version1 = $submission->createVersion();
        
        $submission->content = 'Version 2';
        $submission->save();
        $version2 = $submission->createVersion();

        $submission->content = 'Version 3';
        $submission->save();
        $version3 = $submission->createVersion();

        $this->assertEquals(1, $version1->version_number);
        $this->assertEquals(2, $version2->version_number);
        $this->assertEquals(3, $version3->version_number);
    }

    /** @test */
    public function it_prunes_old_versions_keeping_only_10_most_recent()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'content' => 'Initial content',
        ]);

        // Create 12 versions
        for ($i = 1; $i <= 12; $i++) {
            $submission->content = "Version {$i}";
            $submission->save();
            $submission->createVersion();
        }

        // Should only have 10 versions (oldest 2 pruned)
        $this->assertEquals(10, $submission->versions()->count());
        
        // Verify the oldest versions were deleted (versions 1 and 2)
        $this->assertDatabaseMissing('submission_versions', [
            'submission_id' => $submission->id,
            'version_number' => 1,
        ]);
        $this->assertDatabaseMissing('submission_versions', [
            'submission_id' => $submission->id,
            'version_number' => 2,
        ]);
        
        // Verify the newest versions still exist (versions 3-12)
        $this->assertDatabaseHas('submission_versions', [
            'submission_id' => $submission->id,
            'version_number' => 3,
        ]);
        $this->assertDatabaseHas('submission_versions', [
            'submission_id' => $submission->id,
            'version_number' => 12,
        ]);
    }

    /** @test */
    public function it_can_calculate_diff_between_versions()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'content' => "Line 1\nLine 2\nLine 3",
        ]);

        $version1 = $submission->createVersion();

        $submission->content = "Line 1\nLine 2 Modified\nLine 3\nLine 4";
        $submission->save();
        $version2 = $submission->createVersion();

        $diff = $version2->getDiff($version1);

        $this->assertArrayHasKey('added', $diff);
        $this->assertArrayHasKey('removed', $diff);
        $this->assertArrayHasKey('unchanged', $diff);
        
        $this->assertContains('Line 2 Modified', $diff['added']);
        $this->assertContains('Line 4', $diff['added']);
        $this->assertContains('Line 2', $diff['removed']);
    }

    /** @test */
    public function submission_has_multiple_versions_check()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'content' => 'Version 1',
        ]);

        $this->assertFalse($submission->hasMultipleVersions());

        $submission->createVersion();
        $this->assertFalse($submission->hasMultipleVersions());

        $submission->content = 'Version 2';
        $submission->save();
        $submission->createVersion();
        
        $this->assertTrue($submission->hasMultipleVersions());
    }

    /** @test */
    public function it_can_get_latest_version()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'content' => 'Version 1',
        ]);

        $submission->createVersion();
        
        $submission->content = 'Version 2';
        $submission->save();
        $submission->createVersion();

        $submission->content = 'Version 3';
        $submission->save();
        $version3 = $submission->createVersion();

        $latestVersion = $submission->getLatestVersion();
        
        $this->assertEquals($version3->id, $latestVersion->id);
        $this->assertEquals(3, $latestVersion->version_number);
        $this->assertEquals('Version 3', $latestVersion->content);
    }

    /** @test */
    public function it_gets_versions_chronologically()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'content' => 'Version 1',
        ]);

        $submission->createVersion();
        
        $submission->content = 'Version 2';
        $submission->save();
        $submission->createVersion();

        $submission->content = 'Version 3';
        $submission->save();
        $submission->createVersion();

        $versions = $submission->getVersionsChronologically();
        
        $this->assertCount(3, $versions);
        $this->assertEquals(1, $versions[0]->version_number);
        $this->assertEquals(2, $versions[1]->version_number);
        $this->assertEquals(3, $versions[2]->version_number);
    }

    /** @test */
    public function deleting_submission_cascades_to_versions()
    {
        $user = User::factory()->create();
        $assignment = Assignment::factory()->create();
        $submission = Submission::factory()->create([
            'assignment_id' => $assignment->id,
            'user_id' => $user->id,
            'content' => 'Version 1',
        ]);

        $version = $submission->createVersion();
        $versionId = $version->id;

        // Delete the submission
        $submission->delete();

        // Verify the version was also deleted
        $this->assertDatabaseMissing('submission_versions', ['id' => $versionId]);
    }
}
