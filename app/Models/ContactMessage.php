<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'course_interest',
        'message',
        'status',
        'replied_at',
        'reply_message',
        'replied_by'
    ];

    protected $casts = [
        'replied_at' => 'datetime',
    ];

    /**
     * Get the user who replied to this message.
     */
    public function replier()
    {
        return $this->belongsTo(User::class, 'replied_by');
    }

    /**
     * Scope a query to only include unread messages.
     */
    public function scopeUnread($query)
    {
        return $query->where('status', 'unread');
    }
}
