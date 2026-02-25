<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Forum extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'title',
        'description',
        'is_active',
        'is_locked',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_locked' => 'boolean',
    ];

    /**
     * Get the course that owns the forum.
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get the user who created the forum.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the forum topics.
     */
    public function topics(): HasMany
    {
        return $this->hasMany(ForumTopic::class)->orderBy('is_pinned', 'desc')->orderBy('updated_at', 'desc');
    }

    /**
     * Get active topics only.
     */
    public function activeTopics(): HasMany
    {
        return $this->topics()->where('is_active', true);
    }

    /**
     * Scope a query to only include active forums.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the total number of posts in this forum.
     */
    public function getTotalPostsCount(): int
    {
        return $this->topics()->withCount('posts')->get()->sum('posts_count');
    }

    /**
     * Get the latest post in this forum.
     */
    public function getLatestPost()
    {
        return ForumPost::whereHas('topic', function ($query) {
            $query->where('forum_id', $this->id);
        })->latest()->first();
    }

    /**
     * Check if user can post in this forum.
     */
    public function canUserPost(User $user): bool
    {
        if ($this->is_locked) {
            return $user->hasRole('admin') || $user->hasRole('teacher');
        }

        // Check if user is enrolled in the course or is a teacher/admin
        return $user->hasRole('admin') || 
               $user->hasRole('teacher') || 
               $this->course->enrollments()->where('user_id', $user->id)->exists();
    }
}