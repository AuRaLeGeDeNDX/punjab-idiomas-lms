<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentBlockVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_block_id',
        'version_number',
        'version_data',
        'action_type',
        'created_by',
    ];

    protected $casts = [
        'version_data' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false; // Only using created_at

    /**
     * Get the content block this version belongs to.
     */
    public function contentBlock(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_block_id');
    }

    /**
     * Get the user who created this version.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Create a version snapshot for a content block.
     */
    public static function createSnapshot(Content $content, string $actionType, User $user): self
    {
        $versionNumber = self::where('content_block_id', $content->id)->max('version_number') + 1;
        
        return self::create([
            'content_block_id' => $content->id,
            'version_number' => $versionNumber,
            'version_data' => $content->toArray(),
            'action_type' => $actionType,
            'created_by' => $user->id,
        ]);
    }

    /**
     * Get the latest version number for a content block.
     */
    public static function getLatestVersionNumber(int $contentBlockId): int
    {
        return self::where('content_block_id', $contentBlockId)->max('version_number') ?? 0;
    }

    /**
     * Get version history for a content block.
     */
    public static function getVersionHistory(int $contentBlockId): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('content_block_id', $contentBlockId)
            ->with('creator')
            ->orderBy('version_number', 'desc')
            ->get();
    }

    /**
     * Get a specific version of a content block.
     */
    public static function getVersion(int $contentBlockId, int $versionNumber): ?self
    {
        return self::where('content_block_id', $contentBlockId)
            ->where('version_number', $versionNumber)
            ->with('creator')
            ->first();
    }
}