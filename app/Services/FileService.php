<?php

namespace App\Services;

use App\Models\FileUpload;
use App\Models\User;
use App\Models\Course;
use App\Helpers\FileSecurityHelper;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class FileService
{
    /**
     * Maximum file size in bytes (50MB)
     */
    private const MAX_FILE_SIZE = 52428800;

    /**
     * Upload a file with security validation
     */
    public function uploadFile(
        UploadedFile $file, 
        User $user, 
        ?Course $course = null, 
        bool $isPublic = false
    ): FileUpload {
        // Comprehensive security scan
        $scanResults = FileSecurityHelper::scanFile($file);
        
        if (!$scanResults['safe']) {
            $threats = implode(', ', $scanResults['threats']);
            throw new Exception("File upload blocked due to security threats: {$threats}");
        }
        
        // Log warnings if any
        if (!empty($scanResults['warnings'])) {
            Log::warning('File upload warnings', [
                'user_id' => $user->id,
                'filename' => $file->getClientOriginalName(),
                'warnings' => $scanResults['warnings']
            ]);
        }

        // Validate file type
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getMimeType();
        
        if (!FileSecurityHelper::isFileTypeAllowed($extension, $mimeType)) {
            throw new Exception("File type not allowed: {$extension} ({$mimeType})");
        }

        // Generate secure file name
        $originalName = $file->getClientOriginalName();
        $storedName = FileSecurityHelper::generateSecureFilename($originalName);
        
        // Determine storage path based on course and user
        $storagePath = $this->generateStoragePath($user, $course, $isPublic);
        $fullPath = $storagePath . '/' . $storedName;
        
        // Calculate file hash for deduplication
        $fileHash = hash_file('sha256', $file->getRealPath());
        
        // Check for existing file with same hash
        $existingFile = $this->findExistingFile($fileHash, $user, $course);
        if ($existingFile) {
            Log::info('File already exists, returning existing record', [
                'user_id' => $user->id,
                'course_id' => $course?->id,
                'file_hash' => $fileHash,
                'existing_file_id' => $existingFile->id
            ]);
            return $existingFile;
        }
        
        // Store file
        $disk = $isPublic ? 'public' : 'local';
        $file->storeAs($storagePath, $storedName, $disk);
        
        // Create file record
        $fileUpload = FileUpload::create([
            'user_id' => $user->id,
            'course_id' => $course?->id,
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'file_path' => $fullPath,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'file_hash' => $fileHash,
            'is_public' => $isPublic,
            'uploaded_at' => now(),
        ]);
        
        Log::info('File uploaded successfully', [
            'file_id' => $fileUpload->id,
            'user_id' => $user->id,
            'course_id' => $course?->id,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'security_info' => $scanResults['info']
        ]);
        
        return $fileUpload;
    }

    /**
     * Generate a temporary URL for secure file access
     */
    public function generateTemporaryUrl(FileUpload $fileUpload, int $expirationMinutes = 60): string
    {
        $token = Str::random(64);
        $expiresAt = Carbon::now()->addMinutes($expirationMinutes);
        
        // Store token in cache with file information
        Cache::put(
            "file_token:{$token}",
            [
                'file_id' => $fileUpload->id,
                'user_id' => $fileUpload->user_id,
                'course_id' => $fileUpload->course_id,
                'expires_at' => $expiresAt->timestamp
            ],
            $expirationMinutes * 60
        );
        
        return route('files.download', ['token' => $token]);
    }

    /**
     * Validate temporary token and return file information
     */
    public function validateTemporaryToken(string $token): ?array
    {
        $fileInfo = Cache::get("file_token:{$token}");
        
        if (!$fileInfo || $fileInfo['expires_at'] < time()) {
            return null;
        }
        
        return $fileInfo;
    }

    /**
     * Get file by token for download
     */
    public function getFileByToken(string $token): ?FileUpload
    {
        $fileInfo = $this->validateTemporaryToken($token);
        
        if (!$fileInfo) {
            return null;
        }
        
        return FileUpload::find($fileInfo['file_id']);
    }

    /**
     * Create a new version of an existing file
     */
    public function createFileVersion(
        FileUpload $originalFile, 
        UploadedFile $newFile, 
        User $user
    ): FileUpload {
        // Comprehensive security scan
        $scanResults = FileSecurityHelper::scanFile($newFile);
        
        if (!$scanResults['safe']) {
            $threats = implode(', ', $scanResults['threats']);
            throw new Exception("File upload blocked due to security threats: {$threats}");
        }
        
        // Validate file type
        $extension = strtolower($newFile->getClientOriginalExtension());
        $mimeType = $newFile->getMimeType();
        
        if (!FileSecurityHelper::isFileTypeAllowed($extension, $mimeType)) {
            throw new Exception("File type not allowed: {$extension} ({$mimeType})");
        }
        
        // Create new version with incremented version number
        $versionNumber = $this->getNextVersionNumber($originalFile);
        $originalName = $originalFile->original_name;
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        
        $versionedName = "{$baseName}_v{$versionNumber}.{$extension}";
        $storedName = FileSecurityHelper::generateSecureFilename($versionedName);
        
        // Use same storage path as original
        $storagePath = dirname($originalFile->file_path);
        $fullPath = $storagePath . '/' . $storedName;
        
        // Calculate file hash
        $fileHash = hash_file('sha256', $newFile->getRealPath());
        
        // Store file
        $disk = $originalFile->is_public ? 'public' : 'local';
        $newFile->storeAs($storagePath, $storedName, $disk);
        
        // Create new file record
        $newVersion = FileUpload::create([
            'user_id' => $user->id,
            'course_id' => $originalFile->course_id,
            'original_name' => $versionedName,
            'stored_name' => $storedName,
            'file_path' => $fullPath,
            'file_size' => $newFile->getSize(),
            'mime_type' => $newFile->getMimeType(),
            'file_hash' => $fileHash,
            'is_public' => $originalFile->is_public,
            'uploaded_at' => now(),
        ]);
        
        Log::info('File version created', [
            'original_file_id' => $originalFile->id,
            'new_version_id' => $newVersion->id,
            'version_number' => $versionNumber,
            'user_id' => $user->id,
            'security_info' => $scanResults['info']
        ]);
        
        return $newVersion;
    }

    /**
     * Get all versions of a file
     */
    public function getFileVersions(FileUpload $file): array
    {
        $baseName = pathinfo($file->original_name, PATHINFO_FILENAME);
        $extension = pathinfo($file->original_name, PATHINFO_EXTENSION);
        
        // Find all files with similar names in the same course
        $versions = FileUpload::where('course_id', $file->course_id)
            ->where('user_id', $file->user_id)
            ->where(function ($query) use ($baseName, $extension) {
                $query->where('original_name', 'like', "{$baseName}%{$extension}")
                      ->orWhere('original_name', $file->original_name);
            })
            ->orderBy('uploaded_at', 'asc')
            ->get();
        
        return $versions->toArray();
    }

    /**
     * Delete a file and its physical storage
     */
    public function deleteFile(FileUpload $file): bool
    {
        try {
            // Delete physical file
            $disk = $file->is_public ? 'public' : 'local';
            Storage::disk($disk)->delete($file->file_path);
            
            // Delete database record
            $file->delete();
            
            Log::info('File deleted successfully', [
                'file_id' => $file->id,
                'file_path' => $file->file_path
            ]);
            
            return true;
        } catch (Exception $e) {
            Log::error('Failed to delete file', [
                'file_id' => $file->id,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }

    /**
     * Get storage usage statistics
     */
    public function getStorageStats(?Course $course = null): array
    {
        $query = FileUpload::query();
        
        if ($course) {
            $query->where('course_id', $course->id);
        }
        
        $stats = $query->selectRaw('
            COUNT(*) as total_files,
            SUM(file_size) as total_size,
            AVG(file_size) as average_size,
            MAX(file_size) as largest_file,
            MIN(file_size) as smallest_file
        ')->first();
        
        return [
            'total_files' => $stats->total_files ?? 0,
            'total_size' => $stats->total_size ?? 0,
            'total_size_human' => $this->formatBytes($stats->total_size ?? 0),
            'average_size' => $stats->average_size ?? 0,
            'average_size_human' => $this->formatBytes($stats->average_size ?? 0),
            'largest_file' => $stats->largest_file ?? 0,
            'largest_file_human' => $this->formatBytes($stats->largest_file ?? 0),
            'smallest_file' => $stats->smallest_file ?? 0,
            'smallest_file_human' => $this->formatBytes($stats->smallest_file ?? 0),
        ];
    }

    /**
     * Generate storage path based on user and course
     */
    private function generateStoragePath(User $user, ?Course $course, bool $isPublic): string
    {
        $basePath = $isPublic ? 'public' : 'private';
        
        if ($course) {
            return "{$basePath}/courses/{$course->id}/users/{$user->id}";
        }
        
        return "{$basePath}/users/{$user->id}";
    }

    /**
     * Find existing file with same hash
     */
    private function findExistingFile(string $fileHash, User $user, ?Course $course): ?FileUpload
    {
        return FileUpload::where('file_hash', $fileHash)
            ->where('user_id', $user->id)
            ->where('course_id', $course?->id)
            ->first();
    }

    /**
     * Get next version number for a file
     */
    private function getNextVersionNumber(FileUpload $originalFile): int
    {
        $baseName = pathinfo($originalFile->original_name, PATHINFO_FILENAME);
        $extension = pathinfo($originalFile->original_name, PATHINFO_EXTENSION);
        
        $existingVersions = FileUpload::where('course_id', $originalFile->course_id)
            ->where('user_id', $originalFile->user_id)
            ->where('original_name', 'like', "{$baseName}_v%{$extension}")
            ->get();
        
        $maxVersion = 0;
        foreach ($existingVersions as $version) {
            if (preg_match('/_v(\d+)\./', $version->original_name, $matches)) {
                $maxVersion = max($maxVersion, (int)$matches[1]);
            }
        }
        
        return $maxVersion + 1;
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}