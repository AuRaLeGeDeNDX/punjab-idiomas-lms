<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'teacher_id',
        'title',
        'description',
        'instructions',
        'assignment_type',
        'submission_type',
        'max_score',
        'is_public',
    ];

    protected $casts = [
        'max_score' => 'decimal:2',
        'is_public' => 'boolean',
    ];

    /**
     * Get the teacher who created this template.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * Create an assignment from this template.
     */
    public function createAssignment(array $additionalData = []): Assignment
    {
        $assignmentData = array_merge([
            'title' => $this->title,
            'description' => $this->description,
            'instructions' => $this->instructions,
            'assignment_type' => $this->assignment_type,
            'submission_type' => $this->submission_type,
            'max_score' => $this->max_score,
        ], $additionalData);

        return Assignment::create($assignmentData);
    }

    /**
     * Scope to get public templates.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope to get templates by teacher.
     */
    public function scopeByTeacher($query, int $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    /**
     * Scope to get accessible templates for a teacher.
     */
    public function scopeAccessibleBy($query, int $teacherId)
    {
        return $query->where(function ($q) use ($teacherId) {
            $q->where('teacher_id', $teacherId)
              ->orWhere('is_public', true);
        });
    }
}
