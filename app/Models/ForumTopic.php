<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ForumTopic extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'forum_id',
        'user_id',
        'title',
        'content',
        'is_pinned',
        'is_locked',
        'is_active',
        'views_count',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_pinned' => 'boolean',
        'is_locked' => 'boolean',
        'is_active' => 'boolean',
        'views_count' => 'integer',
    ];

    /**
     * Get the forum that owns the topic.
     */
    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class);
    }

    /**
     * Get the user who created the topic.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the posts in this topic.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(ForumPost::class, 'topic_id')->orderBy('created_at');
    }

    /**
     * Scope a query to only include active topics.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include pinned topics.
     */
    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    /**
     * Get the latest post in this topic.
     */
    public function getLatestPost()
    {
        return $this->posts()->latest()->first();
    }

    /**
     * Increment the views count.
     */
    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Check if user can reply to this topic.
     */
    public function canUserReply(User $user): bool
    {
        if ($this->is_locked) {
            return $user->hasRole('admin') || $user->hasRole('teacher');
        }

        return $this->forum->canUserPost($user);
    }

    /**
     * Get the total number of replies (posts excluding the original).
     */
    public function getRepliesCount(): int
    {
        return $this->posts()->count();
    }
}