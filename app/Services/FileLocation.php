<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * FileLocation represents an immutable file location with disk and path information.
 * 
 * This value object encapsulates file location data and provides methods for
 * existence checking and path manipulation with validation logic.
 * 
 * Requirements: 2.3, 7.1
 */
class FileLocation
{
    private string $disk;
    private string $path;

    /**
     * Create a new FileLocation instance.
     * 
     * @param string $disk Storage disk name
     * @param string $path File path on the disk
     * @throws \InvalidArgumentException If disk or path is invalid
     */
    public function __construct(string $disk, string $path)
    {
        $this->validateDisk($disk);
        $this->validatePath($path);
        
        $this->disk = $disk;
        $this->path = $this->normalizePath($path);
    }

    /**
     * Get the storage disk name.
     * 
     * @return string Storage disk name
     */
    public function getDisk(): string
    {
        return $this->disk;
    }

    /**
     * Get the file path.
     * 
     * @return string File path
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Check if the file exists at this location.
     * 
     * @return bool True if file exists, false otherwise
     */
    public function exists(): bool
    {
        try {
            return Storage::disk($this->disk)->exists($this->path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get the file size in bytes.
     * 
     * @return int|null File size in bytes or null if file doesn't exist
     */
    public function getSize(): ?int
    {
        try {
            if (!$this->exists()) {
                return null;
            }
            return Storage::disk($this->disk)->size($this->path);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the file's last modified timestamp.
     * 
     * @return int|null Last modified timestamp or null if file doesn't exist
     */
    public function getLastModified(): ?int
    {
        try {
            if (!$this->exists()) {
                return null;
            }
            return Storage::disk($this->disk)->lastModified($this->path);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the absolute file system path.
     * 
     * @return string|null Absolute path or null if not accessible
     */
    public function getAbsolutePath(): ?string
    {
        try {
            return Storage::disk($this->disk)->path($this->path);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Get the file's MIME type.
     * 
     * @return string|null MIME type or null if file doesn't exist
     */
    public function getMimeType(): ?string
    {
        try {
            if (!$this->exists()) {
                return null;
            }
            return Storage::disk($this->disk)->mimeType($this->path);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Calculate file hash.
     * 
     * @param string $algorithm Hash algorithm (default: sha256)
     * @return string|null File hash or null if file doesn't exist or is too large
     */
    public function calculateHash(string $algorithm = 'sha256'): ?string
    {
        try {
            if (!$this->exists()) {
                return null;
            }
            
            // Only calculate hash for files smaller than 100MB to avoid memory issues
            $size = $this->getSize();
            if ($size === null || $size > 100 * 1024 * 1024) {
                return null;
            }
            
            $absolutePath = $this->getAbsolutePath();
            if (!$absolutePath || !file_exists($absolutePath)) {
                return null;
            }
            
            return hash_file($algorithm, $absolutePath);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Check if this location is the same as another location.
     * 
     * @param FileLocation $other Other file location
     * @return bool True if locations are the same
     */
    public function equals(FileLocation $other): bool
    {
        return $this->disk === $other->disk && $this->path === $other->path;
    }

    /**
     * Check if this location represents the same file as another location.
     * This compares the actual file content, not just the location.
     * 
     * @param FileLocation $other Other file location
     * @return bool True if files are the same
     */
    public function isSameFile(FileLocation $other): bool
    {
        if ($this->equals($other)) {
            return true;
        }
        
        // Compare file sizes first (quick check)
        $thisSize = $this->getSize();
        $otherSize = $other->getSize();
        
        if ($thisSize === null || $otherSize === null || $thisSize !== $otherSize) {
            return false;
        }
        
        // Compare file hashes (more expensive but accurate)
        $thisHash = $this->calculateHash();
        $otherHash = $other->calculateHash();
        
        return $thisHash !== null && $otherHash !== null && $thisHash === $otherHash;
    }

    /**
     * Get the file extension.
     * 
     * @return string|null File extension or null if no extension
     */
    public function getExtension(): ?string
    {
        $pathInfo = pathinfo($this->path);
        return $pathInfo['extension'] ?? null;
    }

    /**
     * Get the filename without path.
     * 
     * @return string Filename
     */
    public function getFilename(): string
    {
        return basename($this->path);
    }

    /**
     * Get the directory path.
     * 
     * @return string Directory path
     */
    public function getDirectory(): string
    {
        return dirname($this->path);
    }

    /**
     * Create a new FileLocation with a different path on the same disk.
     * 
     * @param string $newPath New file path
     * @return FileLocation New FileLocation instance
     */
    public function withPath(string $newPath): FileLocation
    {
        return new self($this->disk, $newPath);
    }

    /**
     * Create a new FileLocation with a different disk for the same path.
     * 
     * @param string $newDisk New storage disk
     * @return FileLocation New FileLocation instance
     */
    public function withDisk(string $newDisk): FileLocation
    {
        return new self($newDisk, $this->path);
    }

    /**
     * Convert to array representation.
     * 
     * @return array Array representation of the file location
     */
    public function toArray(): array
    {
        return [
            'disk' => $this->disk,
            'path' => $this->path,
            'exists' => $this->exists(),
            'size' => $this->getSize(),
            'last_modified' => $this->getLastModified(),
            'mime_type' => $this->getMimeType(),
            'extension' => $this->getExtension(),
            'filename' => $this->getFilename(),
            'directory' => $this->getDirectory(),
        ];
    }

    /**
     * Convert to JSON representation.
     * 
     * @return string JSON representation
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * String representation of the file location.
     * 
     * @return string String representation
     */
    public function __toString(): string
    {
        return "{$this->disk}://{$this->path}";
    }

    /**
     * Validate storage disk name.
     * 
     * @param string $disk Storage disk name
     * @throws \InvalidArgumentException If disk is invalid
     */
    private function validateDisk(string $disk): void
    {
        if (empty($disk)) {
            throw new \InvalidArgumentException('Storage disk name cannot be empty');
        }
        
        // Check if disk is configured (this will throw exception if not configured)
        try {
            Storage::disk($disk);
        } catch (\Exception $e) {
            throw new \InvalidArgumentException("Storage disk '{$disk}' is not configured: " . $e->getMessage());
        }
    }

    /**
     * Validate file path.
     * 
     * @param string $path File path
     * @throws \InvalidArgumentException If path is invalid
     */
    private function validatePath(string $path): void
    {
        if (empty($path)) {
            throw new \InvalidArgumentException('File path cannot be empty');
        }
        
        // Check for potentially dangerous path components
        $dangerousPatterns = ['../', '..\\', '/..', '\\..'];
        foreach ($dangerousPatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                throw new \InvalidArgumentException("File path contains dangerous pattern: {$pattern}");
            }
        }
        
        // Check for absolute paths (should be relative to storage root)
        if (str_starts_with($path, '/') || preg_match('/^[A-Za-z]:/', $path)) {
            throw new \InvalidArgumentException('File path should be relative to storage root');
        }
    }

    /**
     * Normalize file path.
     * 
     * @param string $path File path
     * @return string Normalized path
     */
    private function normalizePath(string $path): string
    {
        // Convert backslashes to forward slashes
        $path = str_replace('\\', '/', $path);
        
        // Remove leading slash if present
        $path = ltrim($path, '/');
        
        // Remove duplicate slashes
        $path = preg_replace('/\/+/', '/', $path);
        
        // Remove trailing slash
        $path = rtrim($path, '/');
        
        return $path;
    }
}