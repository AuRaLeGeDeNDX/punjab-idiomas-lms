<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'course_id',
        'enrolled_at',
        'completed_at',
        'progress_percentage',
        'last_accessed_at',
        'status',
        'assigned_by',
        'assigned_at',
        'assignment_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
        'last_accessed_at' => 'datetime',
        'assigned_at' => 'datetime',
        'progress_percentage' => 'decimal:2',
    ];

    /**
     * Get the user that owns the enrollment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the course that owns the enrollment.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the user who assigned this enrollment.
     */
    public function assignor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get the student for this enrollment (alias for user relationship).
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the progress percentage as a float.
     */
    public function getProgressPercentage(): float
    {
        return (float) $this->progress_percentage;
    }

    /**
     * Mark a module as complete and update progress.
     */
    public function markModuleComplete(Module $module): void
    {
        // This would typically involve more complex logic
        // to track individual module completion
        $totalModules = $this->course->modules()->count();
        if ($totalModules > 0) {
            // Simplified progress calculation
            $this->progress_percentage = min(100, $this->progress_percentage + (100 / $totalModules));
            $this->last_accessed_at = now();
            $this->save();
        }
    }

    /**
     * Mark module as accessed and update last accessed time.
     */
    public function markModuleAccessed(Module $module): void
    {
        $this->update(['last_accessed_at' => now()]);
        
        // Clear any cached progress data
        \Illuminate\Support\Facades\Cache::forget("enrollment:progress:{$this->id}");
    }

    /**
     * Check if the enrollment is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if the enrollment is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed' || $this->progress_percentage >= 100;
    }

    /**
     * Check if this enrollment was assigned by an admin or teacher.
     */
    public function wasAssigned(): bool
    {
        return !is_null($this->assigned_by);
    }

    /**
     * Get the assignment information.
     */
    public function getAssignmentInfo(): ?array
    {
        if (!$this->wasAssigned()) {
            return null;
        }

        return [
            'assigned_by' => $this->assignor,
            'assigned_at' => $this->assigned_at,
            'assignment_notes' => $this->assignment_notes,
        ];
    }
}
