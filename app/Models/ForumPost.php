<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForumPost extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'topic_id',
        'user_id',
        'content',
        'is_edited',
        'edited_at',
        'edited_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_edited' => 'boolean',
        'edited_at' => 'datetime',
    ];

    /**
     * Get the topic that owns the post.
     */
    public function topic(): BelongsTo
    {
        return $this->belongsTo(ForumTopic::class, 'topic_id');
    }

    /**
     * Get the user who created the post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the user who edited the post.
     */
    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Mark the post as edited.
     */
    public function markAsEdited(User $editor): void
    {
        $this->update([
            'is_edited' => true,
            'edited_at' => now(),
            'edited_by' => $editor->id,
        ]);
    }

    /**
     * Check if user can edit this post.
     */
    public function canUserEdit(User $user): bool
    {
        // Users can edit their own posts, admins and teachers can edit any post
        return $this->user_id === $user->id || 
               $user->hasRole('admin') || 
               $user->hasRole('teacher');
    }

    /**
     * Check if user can delete this post.
     */
    public function canUserDelete(User $user): bool
    {
        // Same rules as editing
        return $this->canUserEdit($user);
    }

    /**
     * Get the forum through the topic relationship.
     */
    public function getForum()
    {
        return $this->topic->forum;
    }
}