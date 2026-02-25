<?php

namespace App\Http\Controllers;

use App\Models\FileUpload;
use App\Services\FileService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Facades\Activity;

class FileController extends Controller
{
    public function __construct(
        private FileService $fileService
    ) {}

    /**
     * Upload a file
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
            'course_id' => 'nullable|exists:courses,id',
            'is_public' => 'boolean'
        ]);

        try {
            $course = $request->course_id ? \App\Models\Course::find($request->course_id) : null;
            
            // Check if user can upload to this course
            if ($course && !Gate::allows('upload-to-course', $course)) {
                return response()->json(['error' => 'Unauthorized to upload to this course'], 403);
            }

            $fileUpload = $this->fileService->uploadFile(
                $request->file('file'),
                auth()->user(),
                $course,
                $request->boolean('is_public', false)
            );
            
            activity('file')
                ->causedBy(auth()->user())
                ->performedOn($fileUpload)
                ->withProperties([
                    'original_name' => $fileUpload->original_name,
                    'file_size' => $fileUpload->file_size,
                    'course_id' => $course ? $course->id : null
                ])
                ->log('File uploaded');

            return response()->json([
                'success' => true,
                'file' => [
                    'id' => $fileUpload->id,
                    'original_name' => $fileUpload->original_name,
                    'file_size' => $fileUpload->file_size,
                    'file_size_human' => $fileUpload->getHumanReadableSize(),
                    'mime_type' => $fileUpload->mime_type,
                    'uploaded_at' => $fileUpload->uploaded_at->format('Y-m-d H:i:s'),
                    'download_url' => $this->fileService->generateTemporaryUrl($fileUpload)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('File upload failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Download a file using temporary token
     */
    public function download(string $token)
    {
        $fileUpload = $this->fileService->getFileByToken($token);
        
        if (!$fileUpload) {
            abort(404, 'File not found or access token expired');
        }

        // Additional authorization check
        if (!Gate::allows('download-file', $fileUpload)) {
            abort(403, 'Unauthorized to download this file');
        }

        try {
            $disk = $fileUpload->is_public ? 'public' : 'local';
            
            if (!Storage::disk($disk)->exists($fileUpload->file_path)) {
                abort(404, 'File not found on storage');
            }

            $fileContent = Storage::disk($disk)->get($fileUpload->file_path);
            
            Log::info('File downloaded', [
                'file_id' => $fileUpload->id,
                'user_id' => auth()->id(),
                'file_name' => $fileUpload->original_name
            ]);
            
            if (auth()->user()) {
                activity('file')
                    ->causedBy(auth()->user())
                    ->performedOn($fileUpload)
                    ->withProperties(['original_name' => $fileUpload->original_name])
                    ->log('File downloaded');
            }

            return response($fileContent)
                ->header('Content-Type', $fileUpload->mime_type)
                ->header('Content-Disposition', 'attachment; filename="' . $fileUpload->original_name . '"')
                ->header('Content-Length', $fileUpload->file_size);

        } catch (\Exception $e) {
            Log::error('File download failed', [
                'file_id' => $fileUpload->id,
                'error' => $e->getMessage()
            ]);

            abort(500, 'Failed to download file');
        }
    }

    /**
     * Get file information
     */
    public function show(FileUpload $fileUpload)
    {
        if (!Gate::allows('view-file', $fileUpload)) {
            abort(403, 'Unauthorized to view this file');
        }

        return response()->json([
            'id' => $fileUpload->id,
            'original_name' => $fileUpload->original_name,
            'file_size' => $fileUpload->file_size,
            'file_size_human' => $fileUpload->getHumanReadableSize(),
            'mime_type' => $fileUpload->mime_type,
            'uploaded_at' => $fileUpload->uploaded_at->format('Y-m-d H:i:s'),
            'is_public' => $fileUpload->is_public,
            'is_image' => $fileUpload->isImage(),
            'is_document' => $fileUpload->isDocument(),
            'extension' => $fileUpload->getExtension(),
            'course' => $fileUpload->course ? [
                'id' => $fileUpload->course->id,
                'title' => $fileUpload->course->title
            ] : null,
            'uploader' => [
                'id' => $fileUpload->user->id,
                'name' => $fileUpload->user->name
            ],
            'download_url' => $this->fileService->generateTemporaryUrl($fileUpload)
        ]);
    }

    /**
     * Delete a file
     */
    public function destroy(FileUpload $fileUpload)
    {
        if (!Gate::allows('delete-file', $fileUpload)) {
            abort(403, 'Unauthorized to delete this file');
        }

        try {
            $originalName = $fileUpload->original_name;
            $success = $this->fileService->deleteFile($fileUpload);
            
            if ($success) {
                activity('file')
                    ->causedBy(auth()->user())
                    ->withProperties(['deleted_file_name' => $originalName])
                    ->log('File deleted');
                    
                return response()->json(['success' => true, 'message' => 'File deleted successfully']);
            } else {
                return response()->json(['success' => false, 'error' => 'Failed to delete file'], 500);
            }

        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'file_id' => $fileUpload->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['success' => false, 'error' => 'Failed to delete file'], 500);
        }
    }

    /**
     * Create a new version of a file
     */
    public function createVersion(Request $request, FileUpload $fileUpload)
    {
        $request->validate([
            'file' => 'required|file'
        ]);

        if (!Gate::allows('update-file', $fileUpload)) {
            abort(403, 'Unauthorized to update this file');
        }

        try {
            $newVersion = $this->fileService->createFileVersion(
                $fileUpload,
                $request->file('file'),
                auth()->user()
            );

            return response()->json([
                'success' => true,
                'file' => [
                    'id' => $newVersion->id,
                    'original_name' => $newVersion->original_name,
                    'file_size' => $newVersion->file_size,
                    'file_size_human' => $newVersion->getHumanReadableSize(),
                    'mime_type' => $newVersion->mime_type,
                    'uploaded_at' => $newVersion->uploaded_at->format('Y-m-d H:i:s'),
                    'download_url' => $this->fileService->generateTemporaryUrl($newVersion)
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('File version creation failed', [
                'original_file_id' => $fileUpload->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get all versions of a file
     */
    public function versions(FileUpload $fileUpload)
    {
        if (!Gate::allows('view-file', $fileUpload)) {
            abort(403, 'Unauthorized to view this file');
        }

        $versions = $this->fileService->getFileVersions($fileUpload);

        return response()->json([
            'versions' => array_map(function ($version) {
                return [
                    'id' => $version['id'],
                    'original_name' => $version['original_name'],
                    'file_size' => $version['file_size'],
                    'file_size_human' => (new FileUpload($version))->getHumanReadableSize(),
                    'uploaded_at' => $version['uploaded_at'],
                    'download_url' => $this->fileService->generateTemporaryUrl(new FileUpload($version))
                ];
            }, $versions)
        ]);
    }

    /**
     * Get storage statistics
     */
    public function storageStats(Request $request)
    {
        $course = $request->course_id ? \App\Models\Course::find($request->course_id) : null;
        
        if ($course && !Gate::allows('view-course', $course)) {
            abort(403, 'Unauthorized to view course statistics');
        }

        $stats = $this->fileService->getStorageStats($course);

        return response()->json($stats);
    }
}