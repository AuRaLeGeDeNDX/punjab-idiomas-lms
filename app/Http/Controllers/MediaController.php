<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Services\FileStorageDiagnosticService;
use App\Services\FileOperationLogger;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaController extends Controller
{
    protected FileStorageDiagnosticService $diagnosticService;
    protected $fileOperationLogger;

    public function __construct(
        FileStorageDiagnosticService $diagnosticService,
        ?FileOperationLogger $fileOperationLogger = null
    ) {
        $this->diagnosticService = $diagnosticService;
        $this->fileOperationLogger = $fileOperationLogger ?? new class {
            public function __call($method, $args) {
                \Log::info("FileOperationLogger::{$method} called", $args);
                return \Illuminate\Support\Str::uuid()->toString();
            }
        };
    }
    /**
     * Serve a private content file with access control and enhanced diagnostics.
     */
    public function serveContentFile(Request $request, Content $content): Response|StreamedResponse
    {
        // Check if user can access this content
        if (!Gate::allows('view', $content)) {
            $this->fileOperationLogger->logFileAccessDenial(
                $content,
                'User does not have permission to view this content',
                ['access_method' => 'media_controller']
            );
            abort(403, 'Access denied to this content.');
        }

        // Check if content has a file
        if (!$content->file_path) {
            $this->fileOperationLogger->logFileServingError(
                $content,
                'Content has no file path',
                ['access_method' => 'media_controller']
            );
            abort(404, 'File not found.');
        }

        try {
            // Run comprehensive diagnostic check
            $diagnosticResult = $this->diagnosticService->diagnoseFileStorageIssues($content);
            
            if (!$diagnosticResult->fileExists()) {
                // File not found - log comprehensive diagnostic information
                $correlationId = $this->fileOperationLogger->logFileServingError(
                    $content,
                    'File not found in storage during media serving',
                    [
                        'access_method' => 'media_controller',
                        'diagnostic_result' => $diagnosticResult->toArray(),
                        'inconsistencies' => $diagnosticResult->getInconsistencies(),
                        'recommendations' => $diagnosticResult->getRecommendations(),
                    ]
                );
                
                // Log storage inconsistency if detected
                if ($diagnosticResult->hasInconsistencies()) {
                    $this->fileOperationLogger->logStorageInconsistency(
                        $content,
                        $diagnosticResult->getInconsistencies()
                    );
                }
                
                \Log::warning('MediaController: Content file not found in storage', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                    'recorded_file_path' => $content->file_path,
                    'recorded_storage_disk' => $content->storage_disk,
                    'diagnostic_summary' => [
                        'file_exists' => false,
                        'inconsistencies_count' => count($diagnosticResult->getInconsistencies()),
                        'has_recommendations' => !empty($diagnosticResult->getRecommendations()),
                    ]
                ]);
                
                abort(404, 'File not found in storage.');
            }
            
            // Get actual file location from diagnostic result
            $actualLocation = $diagnosticResult->getActualLocation();
            $disk = $actualLocation->getDisk();
            $filePath = $actualLocation->getPath();
            
            // Log storage inconsistency if file is in different location than recorded
            if ($disk !== $content->storage_disk) {
                $this->fileOperationLogger->logStorageInconsistency(
                    $content,
                    [
                        'type' => 'storage_disk_mismatch',
                        'recorded_disk' => $content->storage_disk,
                        'actual_disk' => $disk,
                        'file_path' => $filePath,
                        'message' => 'File found on different storage disk than recorded',
                    ]
                );
            }

            // Get file info
            $mimeType = $content->mime_type ?: Storage::disk($disk)->mimeType($filePath);
            $fileSize = $content->file_size ?: Storage::disk($disk)->size($filePath);
            $fileName = $content->file_name ?: basename($filePath);

            // Set appropriate headers
            $headers = [
                'Content-Type' => $mimeType,
                'Content-Length' => $fileSize,
                'Cache-Control' => 'private, max-age=3600', // Cache for 1 hour
                'X-Content-Type-Options' => 'nosniff',
            ];

            // For downloads (PDFs), set Content-Disposition
            if ($content->type === 'pdf') {
                $headers['Content-Disposition'] = 'inline; filename="' . $fileName . '"';
            }

            // Log successful file access
            $this->fileOperationLogger->logFileAccess(
                $content,
                'media_controller_serve',
                [
                    'actual_storage_disk' => $disk,
                    'file_size' => $fileSize,
                    'mime_type' => $mimeType,
                    'diagnostic_passed' => true,
                ]
            );

            // Stream the file
            return Storage::disk($disk)->response($filePath, $fileName, $headers);

        } catch (\Exception $e) {
            $correlationId = $this->fileOperationLogger->logFileServingError(
                $content,
                'Failed to serve content file: ' . $e->getMessage(),
                [
                    'access_method' => 'media_controller',
                    'exception_type' => get_class($e),
                    'exception_trace' => $e->getTraceAsString(),
                ]
            );
            
            \Log::error('MediaController: Failed to serve content file', [
                'correlation_id' => $correlationId,
                'content_id' => $content->id,
                'file_path' => $content->file_path,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            abort(500, 'Failed to serve file.');
        }
    }
}