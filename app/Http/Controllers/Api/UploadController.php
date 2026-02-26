<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    /**
     * Generate a pre-signed URL for direct-to-R2 uploads.
     */
    public function getPresignedUrl(Request $request)
    {
        // 1. Basic validation
        $validated = $request->validate([
            'filename' => 'required|string',
            'content_type' => 'required|string',
            'file_size' => 'required|integer',
            'topic' => 'required|string|in:content-blocks', // Extensible for other areas (users, covers, etc.)
            'subpage_id' => 'required_if:topic,content-blocks|integer|exists:subpages,id',
        ]);

        $topic = $validated['topic'];
        $contentType = $validated['content_type'];
        $fileSize = $validated['file_size'];

        // 2. Perform Topic-Specific Security and Limits Validation
        if ($topic === 'content-blocks') {
            // Check if user can create content in this subpage
            $subpage = \App\Models\Subpage::findOrFail($validated['subpage_id']);
            if (!\Illuminate\Support\Facades\Gate::allows('update', $subpage)) {
                return response()->json(['success' => false, 'message' => 'Unauthorized to upload to this subpage.'], 403);
            }

            // Check Content Block Type specific limits (if passed via front-end)
            $type = $request->input('block_type', 'unknown'); // e.g. 'video', 'pdf'
            $config = \App\Models\Content::getContentTypeConfig($type);
            
            $allowedExtensions = $config['allowed_extensions'] ?? [];
            $maxFileSize = $config['max_file_size'] ?? (10 * 1024 * 1024); // default 10MB
            
            // Adjust server limits to catch gross overages immediately
            $uploadMax = $this->convertToBytes(ini_get('upload_max_filesize'));
            $postMax = $this->convertToBytes(ini_get('post_max_size'));
            if ($uploadMax > 0 && $postMax > 0) {
                 $maxFileSize = min($maxFileSize, $uploadMax, $postMax);
            }

            if ($fileSize > $maxFileSize) {
                $maxMb = round($maxFileSize / 1048576, 2);
                return response()->json([
                    'success' => false, 
                    'message' => "File size exceeds maximum allowed limit of {$maxMb} MB."
                ], 422);
            }

            $extension = strtolower(pathinfo($validated['filename'], PATHINFO_EXTENSION));
            if (!empty($allowedExtensions) && !in_array($extension, $allowedExtensions)) {
                return response()->json([
                    'success' => false, 
                    'message' => "Extension '$extension' not allowed for this content type."
                ], 422);
            }

            // Generate structured path: /content-blocks/{course_id}/{module_id}/{subpage_id}/{uuid}.{ext}
            $courseId = $subpage->module->course_id;
            $moduleId = $subpage->module_id;
            $subpageId = $subpage->id;
            $uuid = \Illuminate\Support\Str::uuid()->toString();
            $safeName = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $validated['filename']);
            
            $storagePath = trim(config('filesystems.disks.r2.root', ''), '/');
            $prefix = $storagePath ? $storagePath . '/' : '';
            $path = "{$prefix}content-blocks/{$courseId}/{$moduleId}/{$subpageId}/{$uuid}_{$safeName}";

        } else {
            return response()->json(['success' => false, 'message' => 'Invalid upload topic.'], 400);
        }

        // 3. Generate Pre-signed URL using underlying AWS S3 Client
        try {
            /** @var \Aws\S3\S3Client $client */
            $client = \Illuminate\Support\Facades\Storage::disk('r2')->getClient();
            
            // Build the PutObject command
            $command = $client->getCommand('PutObject', [
                'Bucket' => config('filesystems.disks.r2.bucket'),
                'Key' => $path,
                'ContentType' => $contentType,
                // Depending on Cloudflare R2 setup, you might need to specify ACL or leave it out. 
                // Mostly leave it out for R2 unless public-read is strictly required and configured.
            ]);

            // Create a pre-signed URL that expires in 60 minutes
            $request = $client->createPresignedRequest($command, '+60 minutes');
            $presignedUrl = (string) $request->getUri();

            return response()->json([
                'success' => true,
                'data' => [
                    'upload_url' => $presignedUrl,
                    'path' => $path,           // The backend tracking path
                    'expires_in' => 3600,      // Seconds
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to generate presigned URL', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => 'Error generating upload URL.'], 500);
        }
    }

    /**
     * Helper to convert ini config like '256M' to bytes.
     */
    private function convertToBytes($val)
    {
        $val = trim($val);
        if (empty($val)) return 0;
        
        $last = strtolower($val[strlen($val) - 1]);
        $val = (int) $val;
        
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;
        }
        
        return $val;
    }
}
