<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Exercise extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subpage_id',
        'title',
        'description',
        'question',
        'answer',
        'instructions',
        'submission_type',
        'max_score',
        'due_date',
        'is_active',
        'order_index',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer',
        'max_score' => 'integer',
        'due_date' => 'datetime',
    ];

    /**
     * SECURITY: Hidden attributes that should NEVER be exposed to students
     */
    protected $hidden = [];

    /**
     * Get the subpage that owns the exercise.
     */
    public function subpage(): BelongsTo
    {
        return $this->belongsTo(Subpage::class);
    }

    /**
     * Get all submissions for this exercise.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(ExerciseSubmission::class);
    }

    /**
     * Get submissions that have been graded.
     */
    public function gradedSubmissions(): HasMany
    {
        return $this->submissions()->where('status', 'graded');
    }

    /**
     * Get submission for a specific user.
     */
    public function submissionFor(User $user): ?ExerciseSubmission
    {
        return $this->submissions()->where('user_id', $user->id)->first();
    }

    /**
     * Scope to get only active exercises.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order exercises by order_index.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    /**
     * SECURITY: Check if user can view the answer.
     * CRITICAL: Students must NEVER see answers.
     */
    public function canViewAnswer(User $user): bool
    {
        // Only teachers and admins can view answers
        return $user->hasAnyRole(['admin', 'teacher']);
    }

    /**
     * SECURITY: Get safe attributes for students (without answer).
     */
    public function toStudentArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'question' => $this->question,
            'instructions' => $this->instructions,
            'submission_type' => $this->submission_type,
            'max_score' => $this->max_score,
            'due_date' => $this->due_date?->toISOString(),
            'is_active' => $this->is_active,
            'order_index' => $this->order_index,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            // NEVER include 'answer' field
        ];
    }

    /**
     * SECURITY: Get full attributes for teachers/admins (with answer).
     */
    public function toTeacherArray(): array
    {
        return array_merge($this->toStudentArray(), [
            'answer' => $this->answer, // Only available to teachers/admins
        ]);
    }

    /**
     * SECURITY: Override toArray to prevent accidental answer exposure.
     */
    public function toArray(): array
    {
        $user = auth()->user();
        
        if (!$user) {
            // No authenticated user - return minimal data
            return [
                'id' => $this->id,
                'title' => $this->title,
            ];
        }

        if ($this->canViewAnswer($user)) {
            return $this->toTeacherArray();
        }

        return $this->toStudentArray();
    }

    /**
     * SECURITY: Override toJson to prevent accidental answer exposure.
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    /**
     * Check if exercise is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast();
    }

    /**
     * Check if user can submit to this exercise.
     */
    public function canSubmit(User $user): bool
    {
        if (!$this->is_active) {
            return false;
        }

        // Check if user is enrolled in the course
        $course = $this->subpage->module->course;
        if (!$user->enrollments()->where('course_id', $course->id)->exists()) {
            return false;
        }

        // Check if already submitted
        if ($this->submissionFor($user)) {
            return false;
        }

        // Check if overdue (optional - you might allow late submissions)
        // return !$this->isOverdue();

        return true;
    }

    /**
     * Get the next order index for this subpage.
     */
    public static function getNextOrderIndex($subpageId): int
    {
        return static::where('subpage_id', $subpageId)->max('order_index') + 1;
    }
}