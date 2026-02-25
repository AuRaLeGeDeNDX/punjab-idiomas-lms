<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;
use App\Services\StorageConfigurationValidatorSimple as StorageConfigurationValidator;

/**
 * StorageValidationMiddleware - Prevents file operations when storage validation fails.
 * 
 * This middleware integrates storage configuration validation into the application lifecycle
 * by checking storage validation status before allowing file operations to proceed.
 * 
 * Requirements: 8.1, 8.4
 */
class StorageValidationMiddleware
{
    protected StorageConfigurationValidator $storageValidator;

    public function __construct(StorageConfigurationValidator $storageValidator)
    {
        $this->storageValidator = $storageValidator;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only process file operation requests — skip everything else
        if (!$this->isFileOperationRequest($request)) {
            return $next($request);
        }

        $correlationId = $request->header('X-Correlation-ID') ?? \Illuminate\Support\Str::uuid()->toString();
        $request->headers->set('X-Correlation-ID', $correlationId);

        // Check if file operations are allowed
        if (!$this->storageValidator->areFileOperationsAllowed($correlationId)) {
            Log::warning('StorageValidationMiddleware: File operation blocked', [
                'correlation_id' => $correlationId,
                'route' => $request->route()?->getName(),
                'user_id' => auth()->id(),
            ]);

            $validationStatus = $this->storageValidator->getValidationStatus($correlationId);
            
            return response()->json([
                'success' => false,
                'error' => 'File operations are currently unavailable due to storage configuration issues.',
                'error_code' => 'STORAGE_VALIDATION_FAILED',
                'message' => 'The storage system is experiencing configuration issues. Please try again later or contact support.',
                'correlation_id' => $correlationId,
                'recommendations' => $this->getPublicRecommendations($validationStatus),
            ], 503);
        }

        return $next($request);
    }

    /**
     * Determine if the request is a file operation that requires storage validation.
     * 
     * @param Request $request
     * @return bool
     */
    private function isFileOperationRequest(Request $request): bool
    {
        // Check for file uploads
        if ($request->hasFile('file')) {
            return true;
        }

        // Check for file-related routes
        $fileOperationRoutes = [
            'content-blocks.store',
            'content-blocks.update',
            'secure-files.download-content',
            'media.show',
            'media.download',
        ];

        $routeName = $request->route()?->getName();
        if ($routeName && in_array($routeName, $fileOperationRoutes)) {
            return true;
        }

        // Check for file-related paths
        $fileOperationPaths = [
            'api/content-blocks',
            'secure-files',
            'media',
        ];

        $path = $request->path();
        foreach ($fileOperationPaths as $fileOpPath) {
            if (str_starts_with($path, $fileOpPath)) {
                return true;
            }
        }

        // Check for file-related actions in request data
        $action = $request->input('action');
        if ($action && in_array($action, ['upload', 'download', 'store', 'update'])) {
            return true;
        }

        return false;
    }

    /**
     * Get public-safe recommendations from validation status.
     * 
     * @param array $validationStatus
     * @return array
     */
    private function getPublicRecommendations(array $validationStatus): array
    {
        $publicRecommendations = [];

        // Convert internal recommendations to user-friendly messages
        $recommendations = $validationStatus['recommendations'] ?? [];
        
        foreach ($recommendations as $recommendation) {
            switch ($recommendation['type'] ?? '') {
                case 'outdated_validation':
                    $publicRecommendations[] = 'Storage system is being validated. Please try again in a few minutes.';
                    break;
                case 'no_validation_cache':
                    $publicRecommendations[] = 'Storage system is initializing. Please try again shortly.';
                    break;
                case 'configuration_errors':
                    $publicRecommendations[] = 'Storage system is experiencing configuration issues. Please contact support if this persists.';
                    break;
                default:
                    $publicRecommendations[] = 'Please try again later or contact support if the issue persists.';
            }
        }

        // Provide default recommendation if none available
        if (empty($publicRecommendations)) {
            $publicRecommendations[] = 'Storage system is temporarily unavailable. Please try again later.';
        }

        return array_unique($publicRecommendations);
    }
}