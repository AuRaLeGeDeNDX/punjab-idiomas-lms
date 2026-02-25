<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentLayoutVersion extends Model
{
    use HasFactory;

    protected $table = 'content_layout_versions';

    protected $fillable = [
        'content_id',
        'before_state',
        'after_state',
        'action',
        'user_id',
    ];

    protected $casts = [
        'before_state' => 'array',
        'after_state' => 'array',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Static Methods
    // ─────────────────────────────────────────────────────────────

    /**
     * Record a layout change for a content block.
     * 
     * @param Content $content The content being updated
     * @param array $beforeState Previous layout state: { row, span, order }
     * @param array $afterState New layout state: { row, span, order }
     * @param string $action The action type: 'resize', 'move', 'update'
     * @param int|null $userId User making the change
     * @return self
     */
    public static function recordChange(
        Content $content,
        array $beforeState,
        array $afterState,
        string $action = 'update',
        ?int $userId = null
    ): self {
        return self::create([
            'content_id' => $content->id,
            'before_state' => $beforeState,
            'after_state' => $afterState,
            'action' => $action,
            'user_id' => $userId ?? auth()->id(),
        ]);
    }

    /**
     * Get layout history for a content block.
     * 
     * @param int $contentId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getHistory(int $contentId, int $limit = 50)
    {
        return self::where('content_id', $contentId)
            ->with('user:id,name')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Restore a specific layout version.
     * 
     * @param int $versionId
     * @return array The before_state to restore
     */
    public static function getStateToRestore(int $versionId): ?array
    {
        $version = self::find($versionId);
        return $version?->before_state;
    }
}
