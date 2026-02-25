<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grade extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'submission_id',
        'grader_id',
        'score',
        'feedback',
        'graded_at',
        'is_published',
        'published_at',
        'rubric_scores',
        'grade_letter',
        'is_locked',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'score' => 'decimal:2',
        'graded_at' => 'datetime',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
        'is_locked' => 'boolean',
        'rubric_scores' => 'array',
    ];

    /**
     * Get the submission that owns the grade.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Get the grader (user) that created the grade.
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'grader_id');
    }

    /**
     * Get the grade overrides for this grade.
     */
    public function overrides(): HasMany
    {
        return $this->hasMany(GradeOverride::class);
    }

    /**
     * Get the student who received the grade.
     */
    public function student(): BelongsTo
    {
        return $this->submission->user();
    }

    /**
     * Get the assignment for this grade.
     */
    public function assignment(): BelongsTo
    {
        return $this->submission->assignment();
    }

    /**
     * Get the course for this grade.
     */
    public function course(): BelongsTo
    {
        return $this->submission->assignment->course();
    }

    /**
     * Get the score as a float.
     */
    public function getScore(): float
    {
        return (float) $this->score;
    }

    /**
     * Check if the grade is published.
     */
    public function isPublished(): bool
    {
        return $this->is_published;
    }

    /**
     * Publish the grade.
     */
    public function publish(): void
    {
        $this->is_published = true;
        $this->published_at = now();
        $this->save();

        // Mark submission as graded
        $this->submission->markAsGraded();

        // Dispatch grade notification job
        \App\Jobs\SendGradeNotification::dispatch($this);
    }

    /**
     * Calculate the percentage score.
     */
    public function getPercentage(): float
    {
        $maxScore = $this->submission->assignment->max_score;
        return $maxScore > 0 ? ($this->score / $maxScore) * 100 : 0;
    }

    /**
     * Get the letter grade based on percentage.
     */
    public function getLetterGrade(): string
    {
        if ($this->grade_letter) {
            return $this->grade_letter;
        }

        $percentage = $this->getPercentage();
        
        if ($percentage >= 90) return 'A';
        if ($percentage >= 80) return 'B';
        if ($percentage >= 70) return 'C';
        if ($percentage >= 60) return 'D';
        return 'F';
    }

    /**
     * Check if grade is passing.
     */
    public function isPassing(): bool
    {
        return $this->getPercentage() >= 60;
    }

    /**
     * Scope to get published grades.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope to get unpublished grades.
     */
    public function scopeUnpublished($query)
    {
        return $query->where('is_published', false);
    }

    /**
     * Scope to get grades for a specific assignment.
     */
    public function scopeForAssignment($query, $assignmentId)
    {
        return $query->whereHas('submission', function ($q) use ($assignmentId) {
            $q->where('assignment_id', $assignmentId);
        });
    }

    /**
     * Get rubric scores as array.
     */
    public function getRubricScores(): array
    {
        return $this->rubric_scores ?? [];
    }

    /**
     * Set rubric scores.
     */
    public function setRubricScores(array $scores): void
    {
        $this->rubric_scores = $scores;
        $this->save();
    }

    /**
     * Lock the grade to prevent further edits.
     */
    public function lock(): void
    {
        $this->update(['is_locked' => true]);
    }

    /**
     * Unlock the grade to allow edits.
     */
    public function unlock(): void
    {
        $this->update(['is_locked' => false]);
    }

    /**
     * Check if the grade is locked.
     */
    public function isLocked(): bool
    {
        return $this->is_locked;
    }

    /**
     * Check if the grade can be edited by the given user.
     */
    public function canBeEdited(User $user): bool
    {
        // Admins can always edit
        if ($user->hasRole('Admin')) {
            return true;
        }

        // If locked, non-admins cannot edit
        if ($this->is_locked) {
            return false;
        }

        // Teachers can edit their own grades
        return $this->grader_id === $user->id;
    }

    /**
     * Create an override record for this grade.
     */
    public function createOverride(User $admin, float $newScore, string $reason): GradeOverride
    {
        $override = $this->overrides()->create([
            'admin_id' => $admin->id,
            'original_score' => $this->score,
            'new_score' => $newScore,
            'reason' => $reason,
            'overridden_at' => now(),
        ]);

        // Update the grade score
        $this->update(['score' => $newScore]);

        return $override;
    }
}
