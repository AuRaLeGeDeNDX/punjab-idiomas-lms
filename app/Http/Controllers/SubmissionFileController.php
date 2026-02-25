<?php

namespace App\Http\Controllers;

use App\Models\SubmissionFile;
use App\Services\FileSecurityService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SubmissionFileController extends Controller
{
    protected FileSecurityService $fileSecurityService;

    public function __construct(FileSecurityService $fileSecurityService)
    {
        $this->fileSecurityService = $fileSecurityService;
    }

    /**
     * Download a submission file with signed URL validation
     *
     * @param Request $request
     * @param SubmissionFile $submissionFile
     * @return StreamedResponse|Response
     */
    public function download(Request $request, SubmissionFile $submissionFile)
    {
        // Verify the signed URL is valid
        if (!$request->hasValidSignature()) {
            Log::warning('Invalid signed URL attempt', [
                'submission_file_id' => $submissionFile->id,
                'user_id' => auth()->id(),
                'ip_address' => $request->ip()
            ]);

            abort(403, 'This file access link has expired or is invalid. Please request a new link.');
        }

        // Authorize file access (will be implemented in sub-task 2.7)
        $this->authorize('download', $submissionFile);

        // Check if file exists
        if (!$submissionFile->exists()) {
            Log::error('File not found in storage', [
                'submission_file_id' => $submissionFile->id,
                'file_path' => $submissionFile->file_path
            ]);

            abort(404, 'File not found in storage.');
        }

        // Log the download
        Log::info('File downloaded', [
            'submission_file_id' => $submissionFile->id,
            'file_name' => $submissionFile->file_name,
            'user_id' => auth()->id(),
            'submission_id' => $submissionFile->submission_id
        ]);

        // Stream the file
        return Storage::disk('local')->download(
            $submissionFile->file_path,
            $submissionFile->file_name,
            [
                'Content-Type' => $submissionFile->mime_type,
                'Content-Disposition' => 'attachment; filename="' . $submissionFile->file_name . '"',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'X-XSS-Protection' => '1; mode=block'
            ]
        );
    }

    /**
     * Download file using encrypted path (alternative method)
     * This is used by the FileSecurityService generateSignedUrl method
     *
     * @param Request $request
     * @return StreamedResponse|Response
     */
    public function downloadByPath(Request $request)
    {
        // Verify the signed URL is valid
        if (!$request->hasValidSignature()) {
            Log::warning('Invalid signed URL attempt for path download', [
                'user_id' => auth()->id(),
                'ip_address' => $request->ip()
            ]);

            abort(403, 'This file access link has expired or is invalid. Please request a new link.');
        }

        try {
            // Decrypt the file path
            $filePath = decrypt($request->get('path'));
        } catch (\Exception $e) {
            Log::error('Failed to decrypt file path', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            abort(400, 'Invalid file path.');
        }

        // Find the submission file by path
        $submissionFile = SubmissionFile::where('file_path', $filePath)->firstOrFail();

        // Authorize file access
        $this->authorize('download', $submissionFile);

        // Check if file exists
        if (!Storage::disk('local')->exists($filePath)) {
            Log::error('File not found in storage', [
                'file_path' => $filePath
            ]);

            abort(404, 'File not found in storage.');
        }

        // Log the download
        Log::info('File downloaded by path', [
            'file_path' => $filePath,
            'file_name' => $submissionFile->file_name,
            'user_id' => auth()->id()
        ]);

        // Stream the file
        return Storage::disk('local')->download(
            $filePath,
            $submissionFile->file_name,
            [
                'Content-Type' => $submissionFile->mime_type,
                'Content-Disposition' => 'attachment; filename="' . $submissionFile->file_name . '"',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'X-XSS-Protection' => '1; mode=block'
            ]
        );
    }
}
