<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfAccessLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'content_id',
        'session_token',
        'ip_address',
        'access_granted',
        'failure_reason',
        'accessed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'access_granted' => 'boolean',
        'accessed_at' => 'datetime',
    ];

    /**
     * Get the user that accessed the PDF.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the content that was accessed.
     */
    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
