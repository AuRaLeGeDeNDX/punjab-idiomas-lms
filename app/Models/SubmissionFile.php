<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class SubmissionFile extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'submission_id',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
        'uploaded_at' => 'datetime',
    ];

    /**
     * Get the submission that owns the file.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    /**
     * Get the file size in human-readable format.
     *
     * @return string
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Generate a temporary signed URL for secure file access.
     *
     * @param int $expirationMinutes
     * @return string
     */
    public function getSignedUrl(int $expirationMinutes = 60): string
    {
        return URL::temporarySignedRoute(
            'submission.file.download',
            now()->addMinutes($expirationMinutes),
            ['submissionFile' => $this->id]
        );
    }

    /**
     * Check if the file exists in storage.
     *
     * @return bool
     */
    public function exists(): bool
    {
        return Storage::disk('local')->exists($this->file_path);
    }

    /**
     * Delete the file from storage.
     *
     * @return bool
     */
    public function deleteFile(): bool
    {
        if ($this->exists()) {
            return Storage::disk('local')->delete($this->file_path);
        }
        return true;
    }

    /**
     * Get the file extension.
     *
     * @return string
     */
    public function getExtension(): string
    {
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    /**
     * Check if the file is an image.
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the file is a PDF.
     *
     * @return bool
     */
    public function isPdf(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Check if the file is a document.
     *
     * @return bool
     */
    public function isDocument(): bool
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
        ];
        
        return in_array($this->mime_type, $documentTypes);
    }

    /**
     * Get the download URL (non-signed, for authorized users).
     *
     * @return string
     */
    public function getDownloadUrl(): string
    {
        return route('submission.file.download', ['submissionFile' => $this->id]);
    }

    /**
     * Boot method to handle model events.
     */
    protected static function boot()
    {
        parent::boot();

        // Delete file from storage when model is deleted
        static::deleting(function ($submissionFile) {
            $submissionFile->deleteFile();
        });
    }
}
