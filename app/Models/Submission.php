<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Submission extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('submission');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'assignment_id',
        'user_id',
        'content',
        'file_paths',
        'submitted_at',
        'is_late',
        'attempt_number',
        'status',
        'submission_type',
        'word_count',
        'file_size_bytes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_paths' => 'array',
        'submitted_at' => 'datetime',
        'is_late' => 'boolean',
        'attempt_number' => 'integer',
        'word_count' => 'integer',
        'file_size_bytes' => 'integer',
    ];

    /**
     * Get the assignment that owns the submission.
     */
    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    /**
     * Get the user that owns the submission.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the files associated with the submission (new normalized table).
     */
    public function files(): HasMany
    {
        return $this->hasMany(SubmissionFile::class);
    }

    /**
     * Get the versions of this submission.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(SubmissionVersion::class);
    }

    /**
     * Get the grade for the submission.
     */
    public function grade(): HasOne
    {
        return $this->hasOne(Grade::class);
    }

    /**
     * Check if the submission is graded.
     */
    public function isGraded(): bool
    {
        return $this->status === 'graded' && $this->grade()->exists();
    }

    /**
     * Check if the submission is late.
     */
    public function isLate(): bool
    {
        return $this->is_late;
    }

    /**
     * Get the file paths as an array.
     */
    public function getFilePaths(): array
    {
        return $this->file_paths ?? [];
    }

    /**
     * Mark the submission as graded.
     */
    public function markAsGraded(): void
    {
        $this->status = 'graded';
        $this->save();
    }

    /**
     * Mark the submission as pending review.
     */
    public function markAsPending(): void
    {
        $this->status = 'pending';
        $this->save();
    }

    /**
     * Mark the submission as reviewed (but not graded).
     */
    public function markAsReviewed(): void
    {
        $this->status = 'reviewed';
        $this->save();
    }

    /**
     * Get the submission type.
     */
    public function getSubmissionType(): string
    {
        return $this->submission_type ?? 'text';
    }

    /**
     * Check if submission has files.
     */
    public function hasFiles(): bool
    {
        return !empty($this->file_paths);
    }

    /**
     * Check if submission has text content.
     */
    public function hasTextContent(): bool
    {
        return !empty($this->content);
    }

    /**
     * Get formatted file size.
     */
    public function getFormattedFileSize(): string
    {
        if (!$this->file_size_bytes) {
            return 'N/A';
        }

        $bytes = $this->file_size_bytes;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Scope to get submissions by status.
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get late submissions.
     */
    public function scopeLate($query)
    {
        return $query->where('is_late', true);
    }

    /**
     * Scope to get submissions for a specific assignment.
     */
    public function scopeForAssignment($query, $assignmentId)
    {
        return $query->where('assignment_id', $assignmentId);
    }

    /**
     * Create a new version snapshot of this submission.
     *
     * @return SubmissionVersion
     */
    public function createVersion(): SubmissionVersion
    {
        // Get the next version number
        $nextVersion = $this->versions()->max('version_number') + 1;

        // Create file paths snapshot from current files
        $filePathsSnapshot = $this->files()->get()->map(function ($file) {
            return [
                'file_name' => $file->file_name,
                'file_path' => $file->file_path,
                'file_size' => $file->file_size,
                'mime_type' => $file->mime_type,
            ];
        })->toArray();

        // Create the version
        $version = $this->versions()->create([
            'version_number' => $nextVersion,
            'content' => $this->content,
            'file_paths_snapshot' => $filePathsSnapshot,
            'created_at' => now(),
        ]);

        // Prune old versions (keep only 10 most recent)
        $this->pruneOldVersions();

        return $version;
    }

    /**
     * Prune old versions, keeping only the 10 most recent.
     *
     * @return void
     */
    public function pruneOldVersions(): void
    {
        $versions = $this->versions()->orderBy('version_number', 'desc')->get();
        
        if ($versions->count() > 10) {
            $versionsToDelete = $versions->slice(10);
            foreach ($versionsToDelete as $version) {
                $version->delete();
            }
        }
    }

    /**
     * Get the total file size for this submission.
     *
     * @return int
     */
    public function getTotalFileSize(): int
    {
        return $this->files()->sum('file_size');
    }

    /**
     * Check if submission has multiple versions.
     *
     * @return bool
     */
    public function hasMultipleVersions(): bool
    {
        return $this->versions()->count() > 1;
    }

    /**
     * Get the latest version.
     *
     * @return SubmissionVersion|null
     */
    public function getLatestVersion(): ?SubmissionVersion
    {
        return $this->versions()->orderBy('version_number', 'desc')->first();
    }

    /**
     * Get all versions ordered chronologically.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getVersionsChronologically()
    {
        return $this->versions()->orderBy('version_number', 'asc')->get();
    }
}
