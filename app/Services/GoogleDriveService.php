<?php

namespace App\Services;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;
use Illuminate\Support\Facades\Log;

/**
 * GoogleDriveService
 *
 * Authenticates via a Service Account JSON key (no browser OAuth).
 * All backup folders are created automatically if they don't exist.
 */
class GoogleDriveService
{
    protected Drive $service;
    protected string $rootFolderId;

    public function __construct()
    {
        $client = new Client();
        $credPath = config('backup.google_drive.credentials_path');

        if (! file_exists($credPath)) {
            throw new \RuntimeException(
                "Google Drive service account JSON not found at: {$credPath}\n" .
                "Download it from Google Cloud Console and place it there."
            );
        }

        $client->setAuthConfig($credPath);
        $client->addScope(Drive::DRIVE);
        $client->setApplicationName('Punjab Idiomas Backup');

        $this->service     = new Drive($client);
        $this->rootFolderId = config('backup.google_drive.backup_folder_id') ?: 'root';
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Upload a local file to a nested path inside the root backup folder.
     *
     * @param  string  $localPath  Absolute path to the local file
     * @param  string  $drivePath  Slash-separated folder path, e.g. "Database/2026/02"
     * @param  string  $filename   Final filename on Drive (with extension)
     * @return string  Google Drive file ID
     */
    public function upload(string $localPath, string $drivePath, string $filename): string
    {
        $folderId = $this->ensurePath($drivePath);

        $fileMetadata = new DriveFile([
            'name'    => $filename,
            'parents' => [$folderId],
        ]);

        $mimeType = mime_content_type($localPath) ?: 'application/octet-stream';

        $result = $this->service->files->create(
            $fileMetadata,
            [
                'data'       => file_get_contents($localPath),
                'mimeType'   => $mimeType,
                'uploadType' => 'multipart',
                'fields'     => 'id,name,size',
            ]
        );

        Log::info('[Backup] Uploaded to Google Drive', [
            'file'     => $filename,
            'drive_id' => $result->id,
            'path'     => $drivePath,
        ]);

        return $result->id;
    }

    /**
     * Upload raw string content (e.g. a DB dump) without writing a temp file twice.
     */
    public function uploadContent(string $content, string $drivePath, string $filename, string $mimeType = 'application/octet-stream'): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'backup_');
        file_put_contents($tmp, $content);
        try {
            return $this->upload($tmp, $drivePath, $filename);
        } finally {
            @unlink($tmp);
        }
    }

    /**
     * List files in a Drive folder path, sorted newest first.
     *
     * @return array<array{id:string,name:string,size:int,modified:string}>
     */
    public function listBackups(string $drivePath): array
    {
        $folderId = $this->findPath($drivePath);
        if (! $folderId) {
            return [];
        }

        $results = $this->service->files->listFiles([
            'q'       => "'{$folderId}' in parents and trashed = false",
            'fields'  => 'files(id,name,size,modifiedTime)',
            'orderBy' => 'modifiedTime desc',
        ]);

        return array_map(fn ($f) => [
            'id'       => $f->id,
            'name'     => $f->name,
            'size'     => (int) $f->size,
            'modified' => $f->modifiedTime,
        ], $results->getFiles());
    }

    /**
     * Download a Drive file by ID to a local destination path.
     */
    public function download(string $fileId, string $localDestination): void
    {
        $response = $this->service->files->get($fileId, ['alt' => 'media']);
        file_put_contents($localDestination, $response->getBody()->getContents());

        Log::info('[Backup] Downloaded from Google Drive', [
            'file_id'     => $fileId,
            'destination' => $localDestination,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Path helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Ensure every segment of a slash-separated path exists as a Drive folder,
     * creating any missing segments. Returns the leaf folder ID.
     */
    protected function ensurePath(string $path): string
    {
        $segments = array_filter(explode('/', $path));
        $parentId = $this->rootFolderId;

        foreach ($segments as $segment) {
            $parentId = $this->findOrCreateFolder($segment, $parentId);
        }

        return $parentId;
    }

    /**
     * Find the leaf folder ID for a path, or return null if missing.
     */
    protected function findPath(string $path): ?string
    {
        $segments = array_filter(explode('/', $path));
        $parentId = $this->rootFolderId;

        foreach ($segments as $segment) {
            $id = $this->findFolder($segment, $parentId);
            if (! $id) {
                return null;
            }
            $parentId = $id;
        }

        return $parentId;
    }

    protected function findFolder(string $name, string $parentId): ?string
    {
        $safeName = addslashes($name);
        $results  = $this->service->files->listFiles([
            'q'      => "name='{$safeName}' and '{$parentId}' in parents and mimeType='application/vnd.google-apps.folder' and trashed=false",
            'fields' => 'files(id)',
        ]);

        $files = $results->getFiles();
        return $files ? $files[0]->id : null;
    }

    protected function findOrCreateFolder(string $name, string $parentId): string
    {
        $existing = $this->findFolder($name, $parentId);
        if ($existing) {
            return $existing;
        }

        $folder = new DriveFile([
            'name'     => $name,
            'mimeType' => 'application/vnd.google-apps.folder',
            'parents'  => [$parentId],
        ]);

        $created = $this->service->files->create($folder, ['fields' => 'id']);
        return $created->id;
    }
}
