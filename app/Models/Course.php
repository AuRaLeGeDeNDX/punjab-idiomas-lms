<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Course extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('course');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'teacher_id',
        'category',
        'difficulty_level',
        'duration_hours',
        'max_students',
        'enrollment_start_date',
        'enrollment_end_date',
        'course_start_date',
        'course_end_date',
        'is_published',
        'is_featured',
        'prerequisites',
        'grading_scheme',
        'passing_grade',
        'auto_publish_grades',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'enrollment_start_date' => 'date',
        'enrollment_end_date' => 'date',
        'course_start_date' => 'date',
        'course_end_date' => 'date',
        'is_published' => 'boolean',
        'is_featured' => 'boolean',
        'prerequisites' => 'array',
        'grading_scheme' => 'array',
        'passing_grade' => 'decimal:2',
        'auto_publish_grades' => 'boolean',
    ];

    /**
     * Check if a user is assigned as a teacher to this course.
     */
    public function hasTeacher(User $user): bool
    {
        return $this->teachers->contains($user->id) || $this->teacher_id === $user->id;
    }

    /**
     * Get the teachers that are assigned to the course.
     */
    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_teacher');
    }

    /**
     * Get the primary teacher (legacy support).
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Get the modules for the course.
     */
    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('order_index');
    }

    /**
     * Get the enrollments for the course.
     */
    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Get the assignments for the course.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get the file uploads for the course.
     */
    public function fileUploads(): HasMany
    {
        return $this->hasMany(FileUpload::class);
    }

    /**
     * Get the announcements for the course.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }

    /**
     * Get the forums for the course.
     */
    public function forums(): HasMany
    {
        return $this->hasMany(Forum::class);
    }

    /**
     * Scope a query to only include published courses.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    /**
     * Scope a query to only include courses accessible by a user.
     */
    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->hasRole('admin')) {
            return $query;
        }

        if ($user->hasRole('teacher')) {
            return $query->where(function ($q) use ($user) {
                $q->whereHas('teachers', function ($sq) use ($user) {
                    $sq->where('users.id', $user->id);
                })->orWhere('teacher_id', $user->id) // Keep legacy support for now
                  ->orWhere('is_published', true);
            });
        }

        return $query->where('is_published', true);
    }

    /**
     * Check if the course is full.
     */
    public function isFull(): bool
    {
        if (!$this->max_students) {
            return false;
        }

        return $this->enrollments()->where('status', 'active')->count() >= $this->max_students;
    }

    /**
     * Get prerequisite courses.
     */
    public function prerequisiteCourses()
    {
        if (!$this->prerequisites || empty($this->prerequisites)) {
            return collect();
        }

        return Course::whereIn('id', $this->prerequisites)->get();
    }

    /**
     * Add a prerequisite course.
     */
    public function addPrerequisite(Course $prerequisite): void
    {
        $prerequisites = $this->prerequisites ?? [];
        
        if (!in_array($prerequisite->id, $prerequisites)) {
            $prerequisites[] = $prerequisite->id;
            $this->update(['prerequisites' => $prerequisites]);
        }
    }

    /**
     * Remove a prerequisite course.
     */
    public function removePrerequisite(Course $prerequisite): void
    {
        $prerequisites = $this->prerequisites ?? [];
        
        $prerequisites = array_filter($prerequisites, function ($id) use ($prerequisite) {
            return $id !== $prerequisite->id;
        });
        
        $this->update(['prerequisites' => array_values($prerequisites)]);
    }

    /**
     * Check if this course has prerequisites.
     */
    public function hasPrerequisites(): bool
    {
        return !empty($this->prerequisites);
    }

    /**
     * Get enrolled students for this course.
     */
    public function students()
    {
        return $this->hasManyThrough(User::class, Enrollment::class, 'course_id', 'id', 'id', 'user_id')
                    ->where('enrollments.status', 'active');
    }

    /**
     * Check if a specific user is enrolled in this course.
     */
    public function isEnrolledBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        
        return $this->enrollments()
                    ->where('user_id', $user->id)
                    ->where('status', 'active')
                    ->exists();
    }

    /**
     * Get active enrollments count.
     */
    public function getActiveEnrollmentsCount(): int
    {
        return $this->enrollments()->where('status', 'active')->count();
    }

    /**
     * Check if enrollment is currently open.
     */
    public function isEnrollmentOpen(): bool
    {
        $now = now();
        
        if ($this->enrollment_start_date && $now->lt($this->enrollment_start_date)) {
            return false;
        }
        
        if ($this->enrollment_end_date && $now->gt($this->enrollment_end_date)) {
            return false;
        }
        
        return true;
    }

    /**
     * Get the grading scheme for this course.
     */
    public function getGradingScheme(): array
    {
        return $this->grading_scheme ?? [
            'quiz' => 20,
            'assignment' => 40,
            'project' => 25,
            'exam' => 15,
        ];
    }

    /**
     * Set the grading scheme for this course.
     */
    public function setGradingScheme(array $scheme): void
    {
        // Validate that weights sum to 100
        $totalWeight = array_sum($scheme);
        if ($totalWeight !== 100) {
            throw new \InvalidArgumentException('Grading scheme weights must sum to 100');
        }

        $this->update(['grading_scheme' => $scheme]);
    }

    /**
     * Get the passing grade threshold.
     */
    public function getPassingGrade(): float
    {
        return (float) $this->passing_grade;
    }

    /**
     * Check if grades should be auto-published.
     */
    public function shouldAutoPublishGrades(): bool
    {
        return $this->auto_publish_grades;
    }

    /**
     * Get all grades for this course.
     */
    public function grades()
    {
        return $this->hasManyThrough(
            Grade::class,
            Assignment::class,
            'course_id',
            'submission_id',
            'id',
            'id'
        )->join('submissions', 'grades.submission_id', '=', 'submissions.id')
         ->where('submissions.assignment_id', '=', DB::raw('assignments.id'));
    }
}
