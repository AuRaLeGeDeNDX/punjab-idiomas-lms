<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Announcement extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'user_id',
        'title',
        'message',
        'is_published',
        'published_at',
        'priority',
        'display_until',
        'display_duration_days',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
        'display_until' => 'datetime',
    ];

    /**
     * Get the course that owns the announcement.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Check if this is a system-wide announcement.
     */
    public function isSystemWide(): bool
    {
        return is_null($this->course_id);
    }

    /**
     * Get the user who created the announcement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include published announcements.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to order by priority and date.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('priority', 'desc')
                    ->orderBy('published_at', 'desc');
    }

    /**
     * Get the priority label.
     */
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'high' => 'High Priority',
            'medium' => 'Medium Priority',
            'low' => 'Low Priority',
            default => 'Normal',
        };
    }

    /**
     * Check if announcement is recent (within last 7 days).
     */
    public function isRecent(): bool
    {
        return $this->published_at && $this->published_at->gt(now()->subDays(7));
    }

    /**
     * Check if announcement is currently active (within display duration).
     */
    public function isActive(): bool
    {
        if (!$this->is_published) {
            return false;
        }

        // If display_until is set, check if it's still valid
        if ($this->display_until) {
            return now()->lte($this->display_until);
        }

        // If no display_until is set, announcement is always active when published
        return true;
    }

    /**
     * Scope a query to only include active announcements.
     */
    public function scopeActive($query)
    {
        return $query->where('is_published', true)
                    ->where(function ($q) {
                        $q->whereNull('display_until')
                          ->orWhere('display_until', '>=', now());
                    });
    }

    /**
     * Get announcements visible to a specific user role.
     */
    public function scopeVisibleToRole($query, string $role)
    {
        return $query->active()
                    ->where(function ($q) use ($role) {
                        // System-wide announcements (course_id is null)
                        $q->whereNull('course_id');
                        
                        // Course-specific announcements for students/teachers
                        if ($role === 'Student') {
                            $q->orWhereHas('course.enrollments', function ($enrollmentQuery) {
                                $enrollmentQuery->where('user_id', auth()->id());
                            });
                        } elseif ($role === 'Teacher') {
                            $q->orWhereHas('course', function ($courseQuery) {
                                $courseQuery->where('teacher_id', auth()->id());
                            });
                        }
                    });
    }
}