<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ExerciseSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'exercise_id',
        'user_id',
        'text_response',
        'file_path',
        'file_name',
        'file_size',
        'mime_type',
        'status',
        'score',
        'feedback',
        'graded_by',
        'submitted_at',
        'graded_at',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'file_size' => 'integer',
        'submitted_at' => 'datetime',
        'graded_at' => 'datetime',
    ];

    /**
     * Get the exercise this submission belongs to.
     */
    public function exercise(): BelongsTo
    {
        return $this->belongsTo(Exercise::class);
    }

    /**
     * Get the user who made this submission.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who graded this submission.
     */
    public function grader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'graded_by');
    }

    /**
     * Scope to get only submitted submissions.
     */
    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    /**
     * Scope to get only graded submissions.
     */
    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    /**
     * Check if submission has a file.
     */
    public function hasFile(): bool
    {
        return !empty($this->file_path) && Storage::exists($this->file_path);
    }

    /**
     * Get signed URL for file download.
     */
    public function getFileUrl(): ?string
    {
        if (!$this->hasFile()) {
            return null;
        }

        return Storage::temporaryUrl($this->file_path, now()->addHours(1));
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->file_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Check if submission is late.
     */
    public function isLate(): bool
    {
        if (!$this->exercise->due_date) {
            return false;
        }

        return $this->submitted_at->isAfter($this->exercise->due_date);
    }

    /**
     * Get score percentage.
     */
    public function getScorePercentageAttribute(): ?float
    {
        if (!$this->score || !$this->exercise->max_score) {
            return null;
        }

        return ($this->score / $this->exercise->max_score) * 100;
    }

    /**
     * Check if user can view this submission.
     */
    public function canBeViewedBy(User $user): bool
    {
        // Students can only view their own submissions
        if ($user->hasRole('student')) {
            return $this->user_id === $user->id;
        }

        // Teachers can view submissions in their courses
        if ($user->hasRole('teacher')) {
            $course = $this->exercise->subpage->module->course;
            return $course->teacher_id === $user->id;
        }

        // Admins can view all submissions
        return $user->hasRole('admin');
    }

    /**
     * Grade this submission.
     */
    public function grade(float $score, ?string $feedback = null, User $grader = null): void
    {
        $this->update([
            'score' => $score,
            'feedback' => $feedback,
            'status' => 'graded',
            'graded_by' => $grader?->id ?? auth()->id(),
            'graded_at' => now(),
        ]);
    }
}