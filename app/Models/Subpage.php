<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subpage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'module_id',
        'title',
        'description',
        'order_index',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer',
    ];

    /**
     * Get the module that owns the subpage.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /**
     * Get the course through the module.
     */
    public function course(): BelongsTo
    {
        return $this->module()->getRelated()->course();
    }

    /**
     * Get all content items for this subpage.
     */
    public function contents(): HasMany
    {
        return $this->hasMany(Content::class);
    }

    /**
     * Get active content items for this subpage.
     */
    public function activeContents(): HasMany
    {
        return $this->contents()->where('is_active', true)->orderBy('order_index');
    }

    /**
     * Get all exercises for this subpage.
     */
    public function exercises(): HasMany
    {
        return $this->hasMany(Exercise::class);
    }

    /**
     * Get active exercises for this subpage.
     */
    public function activeExercises(): HasMany
    {
        return $this->exercises()->active()->ordered();
    }

    /**
     * Get all assignments for this subpage.
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get active assignments for this subpage.
     */
    public function activeAssignments(): HasMany
    {
        return $this->assignments()->active()->ordered();
    }

    /**
     * Get published assignments for this subpage.
     */
    public function publishedAssignments(): HasMany
    {
        return $this->assignments()->published()->active()->ordered();
    }

    /**
     * Scope to get only active subpages.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order subpages by their order_index.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    /**
     * Scope to get subpages for a specific module.
     */
    public function scopeForModule($query, $moduleId)
    {
        return $query->where('module_id', $moduleId);
    }

    /**
     * Check if user can access this subpage.
     */
    public function canBeAccessedBy(User $user): bool
    {
        // Students can only access active subpages in courses they're enrolled in
        if ($user->hasRole('Student')) {
            return $this->is_active && 
                   $user->enrollments()->where('course_id', $this->module->course_id)->exists();
        }

        // Teachers can access subpages in their courses
        if ($user->hasRole('Teacher')) {
            return $this->module->course->teacher_id === $user->id;
        }

        // Admins can access all subpages
        return $user->hasRole('Admin');
    }

    /**
     * Get the next order index for this module.
     */
    public static function getNextOrderIndex($moduleId): int
    {
        return static::where('module_id', $moduleId)->max('order_index') + 1;
    }

    /**
     * Reorder subpages within a module.
     */
    public static function reorderForModule($moduleId, array $subpageIds): void
    {
        foreach ($subpageIds as $index => $subpageId) {
            static::where('id', $subpageId)
                  ->where('module_id', $moduleId)
                  ->update(['order_index' => $index + 1]);
        }
    }
}