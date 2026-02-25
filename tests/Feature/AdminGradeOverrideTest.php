<?php

namespace Tests\Feature;

use App\Models\Assignment;
use App\Models\Course;
use App\Models\Grade;
use App\Models\GradeOverride;
use App\Models\Submission;
use App\Models\User;
use App\Notifications\GradeOverriddenNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminGradeOverrideTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teacher;
    protected User $student;
    protected Course $course;
    protected Assignment $assignment;
    protected Submission $submission;
    protected Grade $grade;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles if they don't exist
        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Teacher', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Student', 'guard_name' => 'web']);

        // Create users with Spatie roles
        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');
        
        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');
        
        $this->student = User::factory()->create();
        $this->student->assignRole('Student');

        // Create course and assignment
        $this->course = Course::factory()->create(['teacher_id' => $this->teacher->id]);
        $this->assignment = Assignment::factory()->create([
            'course_id' => $this->course->id,
            'max_score' => 100,
            'is_published' => true,
        ]);

        // Create submission and grade
        $this->submission = Submission::factory()->create([
            'assignment_id' => $this->assignment->id,
            'user_id' => $this->student->id,
            'status' => 'graded',
        ]);

        $this->grade = Grade::factory()->create([
            'submission_id' => $this->submission->id,
            'grader_id' => $this->teacher->id,
            'score' => 85,
            'is_published' => true,
            'is_locked' => true,
        ]);
    }

    /** @test */
    public function admin_can_view_override_form_for_locked_grade()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.gradebook.show-override', $this->grade));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.gradebook.override');
        $response->assertViewHas('grade', $this->grade);
    }

    /** @test */
    public function teacher_cannot_view_override_form()
    {
        $response = $this->actingAs($this->teacher)
            ->get(route('admin.gradebook.show-override', $this->grade));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_override_locked_grade_with_valid_reason()
    {
        Notification::fake();

        $newScore = 95;
        $reason = 'Student demonstrated additional understanding in follow-up discussion.';

        $response = $this->actingAs($this->admin)
            ->post(route('admin.gradebook.override', $this->grade), [
                'new_score' => $newScore,
                'reason' => $reason,
            ]);

        $response->assertRedirect(route('teacher.gradebook.index', $this->course));
        $response->assertSessionHas('success');

        // Verify grade was updated
        $this->grade->refresh();
        $this->assertEquals($newScore, $this->grade->score);

        // Verify override record was created
        $this->assertDatabaseHas('grade_overrides', [
            'grade_id' => $this->grade->id,
            'admin_id' => $this->admin->id,
            'original_score' => 85,
            'new_score' => $newScore,
            'reason' => $reason,
        ]);

        // Verify notification was sent to original grader
        Notification::assertSentTo(
            $this->teacher,
            GradeOverriddenNotification::class,
            function ($notification) {
                return $notification->override->grade_id === $this->grade->id;
            }
        );
    }

    /** @test */
    public function override_requires_reason_with_minimum_10_characters()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.gradebook.override', $this->grade), [
                'new_score' => 95,
                'reason' => 'Too short',
            ]);

        $response->assertSessionHasErrors('reason');
        
        // Verify grade was not updated
        $this->grade->refresh();
        $this->assertEquals(85, $this->grade->score);
    }

    /** @test */
    public function override_requires_valid_score_within_max_score()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.gradebook.override', $this->grade), [
                'new_score' => 150, // Exceeds max_score of 100
                'reason' => 'This is a valid reason with more than 10 characters.',
            ]);

        $response->assertSessionHasErrors('new_score');
        
        // Verify grade was not updated
        $this->grade->refresh();
        $this->assertEquals(85, $this->grade->score);
    }

    /** @test */
    public function teacher_cannot_override_locked_grade()
    {
        $response = $this->actingAs($this->teacher)
            ->post(route('admin.gradebook.override', $this->grade), [
                'new_score' => 95,
                'reason' => 'This is a valid reason with more than 10 characters.',
            ]);

        $response->assertStatus(403);
        
        // Verify grade was not updated
        $this->grade->refresh();
        $this->assertEquals(85, $this->grade->score);
    }

    /** @test */
    public function admin_can_view_override_history()
    {
        // Create some override records
        $override1 = GradeOverride::factory()->create([
            'grade_id' => $this->grade->id,
            'admin_id' => $this->admin->id,
            'original_score' => 85,
            'new_score' => 90,
            'reason' => 'First override reason with sufficient length.',
        ]);

        $override2 = GradeOverride::factory()->create([
            'grade_id' => $this->grade->id,
            'admin_id' => $this->admin->id,
            'original_score' => 90,
            'new_score' => 95,
            'reason' => 'Second override reason with sufficient length.',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.gradebook.override-history', $this->grade));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.gradebook.override-history');
        $response->assertViewHas('grade');
        $response->assertSee('First override reason');
        $response->assertSee('Second override reason');
    }

    /** @test */
    public function teacher_can_view_override_history_for_their_own_grades()
    {
        $override = GradeOverride::factory()->create([
            'grade_id' => $this->grade->id,
            'admin_id' => $this->admin->id,
            'original_score' => 85,
            'new_score' => 90,
            'reason' => 'Override reason with sufficient length for testing.',
        ]);

        $response = $this->actingAs($this->teacher)
            ->get(route('teacher.gradebook.override-history', $this->grade));

        $response->assertStatus(200);
        $response->assertViewIs('teacher.gradebook.override-history');
        $response->assertSee('Override reason');
    }

    /** @test */
    public function override_creates_audit_trail_with_all_required_fields()
    {
        $newScore = 92;
        $reason = 'Detailed explanation for the grade override with sufficient length.';

        $this->actingAs($this->admin)
            ->post(route('admin.gradebook.override', $this->grade), [
                'new_score' => $newScore,
                'reason' => $reason,
            ]);

        $override = GradeOverride::where('grade_id', $this->grade->id)->first();

        $this->assertNotNull($override);
        $this->assertEquals($this->grade->id, $override->grade_id);
        $this->assertEquals($this->admin->id, $override->admin_id);
        $this->assertEquals(85, $override->original_score);
        $this->assertEquals($newScore, $override->new_score);
        $this->assertEquals($reason, $override->reason);
        $this->assertNotNull($override->overridden_at);
    }

    /** @test */
    public function multiple_overrides_create_complete_history()
    {
        Notification::fake();

        // First override
        $this->actingAs($this->admin)
            ->post(route('admin.gradebook.override', $this->grade), [
                'new_score' => 90,
                'reason' => 'First override with sufficient explanation length.',
            ]);

        $this->grade->refresh();

        // Second override
        $this->actingAs($this->admin)
            ->post(route('admin.gradebook.override', $this->grade), [
                'new_score' => 95,
                'reason' => 'Second override with sufficient explanation length.',
            ]);

        $this->grade->refresh();

        // Verify final score
        $this->assertEquals(95, $this->grade->score);

        // Verify both overrides are recorded
        $overrides = GradeOverride::where('grade_id', $this->grade->id)
            ->orderBy('overridden_at')
            ->get();

        $this->assertCount(2, $overrides);
        $this->assertEquals(85, $overrides[0]->original_score);
        $this->assertEquals(90, $overrides[0]->new_score);
        $this->assertEquals(90, $overrides[1]->original_score);
        $this->assertEquals(95, $overrides[1]->new_score);
    }

    /** @test */
    public function override_notification_includes_all_required_information()
    {
        Notification::fake();

        $newScore = 95;
        $reason = 'Student demonstrated additional understanding in follow-up discussion.';

        $this->actingAs($this->admin)
            ->post(route('admin.gradebook.override', $this->grade), [
                'new_score' => $newScore,
                'reason' => $reason,
            ]);

        Notification::assertSentTo(
            $this->teacher,
            GradeOverriddenNotification::class,
            function ($notification) use ($newScore, $reason) {
                $override = $notification->override;
                return $override->grade_id === $this->grade->id &&
                       $override->admin_id === $this->admin->id &&
                       $override->original_score == 85 &&
                       $override->new_score == $newScore &&
                       $override->reason === $reason;
            }
        );
    }

    /** @test */
    public function grade_lock_prevents_teacher_edits()
    {
        $this->assertTrue($this->grade->isLocked());
        $this->assertFalse($this->grade->canBeEdited($this->teacher));
        $this->assertTrue($this->grade->canBeEdited($this->admin));
    }

    /** @test */
    public function teacher_can_lock_their_own_grade()
    {
        $unlockedGrade = Grade::factory()->create([
            'submission_id' => $this->submission->id,
            'grader_id' => $this->teacher->id,
            'score' => 80,
            'is_locked' => false,
        ]);

        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.gradebook.lock', $unlockedGrade));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $unlockedGrade->refresh();
        $this->assertTrue($unlockedGrade->isLocked());
    }

    /** @test */
    public function teacher_can_unlock_their_own_grade()
    {
        $lockedGrade = Grade::factory()->create([
            'submission_id' => $this->submission->id,
            'grader_id' => $this->teacher->id,
            'score' => 80,
            'is_locked' => true,
        ]);

        $response = $this->actingAs($this->teacher)
            ->post(route('teacher.gradebook.unlock', $lockedGrade));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $lockedGrade->refresh();
        $this->assertFalse($lockedGrade->isLocked());
    }
}
