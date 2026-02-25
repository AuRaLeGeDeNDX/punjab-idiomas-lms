<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'module_id',
        'subpage_id',
        'title',
        'description',
        'instructions',
        'assignment_type',
        'submission_type',
        'max_score',
        'due_date',
        'is_published',
        'is_active',
        'order_index',
        'published_at',
        'scheduled_publish_at',
        'allow_late_submission',
        'auto_grade',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'datetime',
        'published_at' => 'datetime',
        'scheduled_publish_at' => 'datetime',
        'max_score' => 'decimal:2',
        'is_published' => 'boolean',
        'is_active' => 'boolean',
        'allow_late_submission' => 'boolean',
        'auto_grade' => 'boolean',
        'order_index' => 'integer',
    ];

    /**
     * Get the course that owns the assignment.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the module that owns the assignment.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the subpage that owns the assignment.
     */
    public function subpage(): BelongsTo
    {
        return $this->belongsTo(Subpage::class);
    }

    /**
     * Get the submissions for the assignment.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Get the rubric for this assignment.
     */
    public function rubric()
    {
        return $this->hasOne(Rubric::class);
    }

    /**
     * Get published grades for this assignment.
     */
    public function publishedGrades(): HasMany
    {
        return $this->hasManyThrough(Grade::class, Submission::class)
                    ->where('grades.is_published', true);
    }

    /**
     * Scope to get only active assignments.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only published assignments.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope to order assignments by order_index.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    /**
     * Check if the assignment is auto-graded.
     */
    public function isAutoGraded(): bool
    {
        return $this->auto_grade;
    }

    /**
     * Get the maximum score for the assignment.
     */
    public function getMaxScore(): int
    {
        return (int) $this->max_score;
    }

    /**
     * Check if the assignment is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }

    /**
     * Check if late submissions are allowed.
     */
    public function allowsLateSubmission(): bool
    {
        return $this->allow_late_submission;
    }

    /**
     * Check if user can submit to this assignment.
     */
    public function canSubmit(User $user): bool
    {
        if (!$this->is_active || !$this->is_published) {
            return false;
        }

        // Check if user is enrolled in the course
        if (!$user->enrollments()->where('course_id', $this->course_id)->exists()) {
            return false;
        }

        // Check if already submitted (assuming one submission per student)
        if ($this->submissions()->where('user_id', $user->id)->exists()) {
            return false;
        }

        // Check if overdue and late submissions not allowed
        if ($this->isOverdue() && !$this->allowsLateSubmission()) {
            return false;
        }

        return true;
    }

    /**
     * Get submission for a specific user.
     */
    public function submissionFor(User $user): ?Submission
    {
        return $this->submissions()->with('grade')->where('user_id', $user->id)->first();
    }

    /**
     * Check if assignment has submission from a specific user.
     */
    public function hasSubmissionFrom(User $user): bool
    {
        return $this->submissions()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if assignment has any submissions.
     */
    public function hasSubmissions(): bool
    {
        return $this->submissions()->exists();
    }

    /**
     * Get submission statistics.
     */
    public function getSubmissionStats(): array
    {
        $total = $this->submissions()->count();
        $graded = $this->submissions()->where('status', 'graded')->count();
        $pending = $total - $graded;
        $avgScore = $this->submissions()
            ->join('grades', 'submissions.id', '=', 'grades.submission_id')
            ->where('grades.is_published', true)
            ->avg('grades.score');

        return [
            'total_submissions' => $total,
            'graded_submissions' => $graded,
            'pending_submissions' => $pending,
            'average_score' => $avgScore ? round($avgScore, 2) : null,
        ];
    }

    /**
     * Publish the assignment.
     */
    public function publish(): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    /**
     * Unpublish the assignment.
     */
    public function unpublish(): void
    {
        $this->update([
            'is_published' => false,
            'published_at' => null,
        ]);
    }

    /**
     * Get the next order index for this subpage.
     */
    public static function getNextOrderIndex($subpageId): int
    {
        return static::where('subpage_id', $subpageId)->max('order_index') + 1;
    }

    /**
     * Check if assignment is scheduled for future publication.
     */
    public function isScheduled(): bool
    {
        return $this->scheduled_publish_at !== null && $this->scheduled_publish_at->isFuture();
    }

    /**
     * Check if assignment is ready for auto-publication.
     */
    public function isReadyForAutoPublish(): bool
    {
        return $this->scheduled_publish_at !== null 
            && $this->scheduled_publish_at->isPast() 
            && !$this->is_published;
    }

    /**
     * Schedule assignment for publication.
     */
    public function schedulePublication(\DateTime $dateTime): void
    {
        $this->update([
            'scheduled_publish_at' => $dateTime,
        ]);
    }

    /**
     * Cancel scheduled publication.
     */
    public function cancelSchedule(): void
    {
        $this->update([
            'scheduled_publish_at' => null,
        ]);
    }

    /**
     * Scope to get assignments ready for auto-publication.
     */
    public function scopeReadyForAutoPublish($query)
    {
        return $query->whereNotNull('scheduled_publish_at')
            ->where('scheduled_publish_at', '<=', now())
            ->where('is_published', false);
    }
}
