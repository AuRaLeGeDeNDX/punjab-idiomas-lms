<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubmissionVersion extends Model
{
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'submission_id',
        'version_number',
        'content',
        'file_paths_snapshot',
        'created_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_paths_snapshot' => 'array',
        'created_at' => 'datetime',
        'version_number' => 'integer',
    ];

    /**
     * Get the submission that owns the version.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Get the file paths from the snapshot.
     *
     * @return array
     */
    public function getFilePaths(): array
    {
        return $this->file_paths_snapshot ?? [];
    }

    /**
     * Check if this version has files.
     *
     * @return bool
     */
    public function hasFiles(): bool
    {
        return !empty($this->file_paths_snapshot);
    }

    /**
     * Check if this version has text content.
     *
     * @return bool
     */
    public function hasContent(): bool
    {
        return !empty($this->content);
    }

    /**
     * Calculate the difference between this version and another version.
     *
     * @param SubmissionVersion $otherVersion
     * @return array
     */
    public function getDiff(SubmissionVersion $otherVersion): array
    {
        $thisContent = $this->content ?? '';
        $otherContent = $otherVersion->content ?? '';

        // Simple line-by-line diff
        $thisLines = explode("\n", $thisContent);
        $otherLines = explode("\n", $otherContent);

        $diff = [
            'added' => [],
            'removed' => [],
            'unchanged' => [],
        ];

        // Find removed lines (in other but not in this)
        foreach ($otherLines as $line) {
            if (!in_array($line, $thisLines)) {
                $diff['removed'][] = $line;
            }
        }

        // Find added lines (in this but not in other)
        foreach ($thisLines as $line) {
            if (!in_array($line, $otherLines)) {
                $diff['added'][] = $line;
            } else {
                $diff['unchanged'][] = $line;
            }
        }

        return $diff;
    }

    /**
     * Get a formatted diff for display.
     *
     * @param SubmissionVersion $otherVersion
     * @return string
     */
    public function getFormattedDiff(SubmissionVersion $otherVersion): string
    {
        $diff = $this->getDiff($otherVersion);
        $output = [];

        foreach ($diff['removed'] as $line) {
            $output[] = "- {$line}";
        }

        foreach ($diff['added'] as $line) {
            $output[] = "+ {$line}";
        }

        foreach ($diff['unchanged'] as $line) {
            $output[] = "  {$line}";
        }

        return implode("\n", $output);
    }

    /**
     * Scope to get versions ordered by version number.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('version_number', 'asc');
    }

    /**
     * Scope to get the latest version for a submission.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $submissionId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLatestForSubmission($query, int $submissionId)
    {
        return $query->where('submission_id', $submissionId)
                     ->orderBy('version_number', 'desc')
                     ->limit(1);
    }
}
