<?php

namespace App\Services;

use App\Models\Content;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * SecurePdfService
 * 
 * Enhanced PDF security service with:
 * - Watermarking support
 * - Access logging with page tracking
 * - Signed URL generation
 * - Anti-download protections
 */
class SecurePdfService
{
    /**
     * Generate a secure, short-lived signed URL for PDF viewing.
     * 
     * This generates a signed URL to the PDF data stream endpoint
     * used by PDF.js to load the PDF file.
     * 
     * Requirements:
     * - Generate absolute URLs for PDF.js compatibility (Requirement 7.3)
     * - Minimum expiration of 5 minutes (300 seconds) (Requirement 7.1)
     * - Include all necessary parameters for validation (Requirement 7.2)
     * - Use correct route name 'secure.pdf.stream' (Requirement 7.3)
     * 
     * @param Content $content
     * @param int $expirationMinutes Minimum 5 minutes
     * @return string Absolute signed URL
     */
    public function generateSecureUrl(Content $content, int $expirationMinutes = 10): string
    {
        // Enforce minimum expiration of 5 minutes (300 seconds) per Requirement 7.1
        $expirationMinutes = max($expirationMinutes, 5);
        
        // For R2-stored files, generate a direct pre-signed URL to avoid double-hop
        // (VPS downloads from R2, then re-serves to browser = slow)
        // Direct R2 pre-signed URL lets PDF.js load straight from Cloudflare's edge CDN
        /* TEMPORARILY DISABLED TO PREVENT CORS ERRORS IN PDF.JS
        if ($content->storage_disk === 'r2' && $content->file_path) {
            try {
                $disk = Storage::disk('r2');
                
                // Verify file exists on R2
                if ($disk->exists($content->file_path)) {
                    // @var \Aws\S3\S3Client $client
                    $client = $disk->getClient();
                    
                    $command = $client->getCommand('GetObject', [
                        'Bucket' => config('filesystems.disks.r2.bucket'),
                        'Key'    => $content->file_path,
                    ]);
                    
                    $request = $client->createPresignedRequest(
                        $command, 
                        "+{$expirationMinutes} minutes"
                    );
                    
                    $directUrl = (string) $request->getUri();
                    
                    Log::info('SecurePDF: Generated direct R2 pre-signed URL', [
                        'content_id' => $content->id,
                        'expires_in_minutes' => $expirationMinutes,
                    ]);
                    
                    return $directUrl;
                }
            } catch (\Exception $e) {
                Log::warning('SecurePDF: Failed to generate R2 pre-signed URL, falling back to VPS proxy', [
                    'content_id' => $content->id,
                    'error' => $e->getMessage(),
                ]);
                // Fall through to VPS-proxied URL below
            }
        }
        */
        
        // Fallback: Generate VPS-proxied signed URL (for local/public/protected disks)
        return \URL::temporarySignedRoute(
            'secure.pdf.stream',
            now()->addMinutes($expirationMinutes),
            ['content' => $content->id],
            true
        );
    }

    /**
     * Generate a secure URL for PDF.js viewer.
     * 
     * @param Content $content
     * @param User $user
     * @param int $expirationMinutes
     * @return string
     */
    public function generateViewerUrl(Content $content, User $user, int $expirationMinutes = 10): string
    {
        // Generate session token for this viewing session
        $sessionToken = $this->generateSessionToken($content, $user);
        
        return route('secure.pdf.viewer', [
            'content' => $content->id,
            'token' => $sessionToken,
        ]);
    }

    /**
     * Generate a unique session token for PDF viewing.
     * 
     * Token structure: base64(json_payload + '.' + hmac_signature)
     * Payload includes: user_id, content_id, expires_at
     * Signature: HMAC-SHA256 of payload using app key
     * 
     * @param Content $content
     * @param User $user
     * @return string
     */
    private function generateSessionToken(Content $content, User $user): string
    {
        // Get token expiration from config (default: 60 minutes)
        $expirationMinutes = config('secure-pdf.token_expiration_minutes', 60);
        $expiresAt = now()->addMinutes($expirationMinutes)->timestamp;
        
        // Create payload with required fields
        $payload = [
            'user_id' => $user->id,
            'content_id' => $content->id,
            'expires_at' => $expiresAt,
        ];
        
        // Convert payload to JSON
        $payloadJson = json_encode($payload);
        
        // Generate HMAC-SHA256 signature using app key
        $signature = hash_hmac('sha256', $payloadJson, config('app.key'), false);
        
        // Combine payload and signature
        $tokenData = $payloadJson . '.' . $signature;
        
        // Return base64 encoded token
        return base64_encode($tokenData);
    }

    /**
     * Validate a PDF viewing session token.
     * 
     * Validates token structure, signature, and expiration.
     * Returns decoded payload if valid, null otherwise.
     * 
     * @param string $token
     * @param int|null $contentId Optional content ID to verify
     * @param int|null $userId Optional user ID to verify
     * @return array|null Returns payload array if valid, null if invalid
     */
    public function validateSessionToken(string $token, ?int $contentId = null, ?int $userId = null): ?array
    {
        try {
            // Decode base64 token
            $tokenData = base64_decode($token, true);
            
            if ($tokenData === false) {
                Log::warning('Invalid token: base64 decode failed');
                return null;
            }
            
            // Split into payload and signature
            $parts = explode('.', $tokenData);
            
            if (count($parts) !== 2) {
                Log::warning('Invalid token: malformed structure');
                return null;
            }
            
            [$payloadJson, $signature] = $parts;
            
            // Verify HMAC signature
            $expectedSignature = hash_hmac('sha256', $payloadJson, config('app.key'), false);
            
            if (!hash_equals($expectedSignature, $signature)) {
                Log::warning('Invalid token: signature mismatch');
                return null;
            }
            
            // Decode payload
            $payload = json_decode($payloadJson, true);
            
            if (!$payload || !isset($payload['user_id'], $payload['content_id'], $payload['expires_at'])) {
                Log::warning('Invalid token: missing required fields');
                return null;
            }
            
            // Check expiration
            if ($payload['expires_at'] < now()->timestamp) {
                Log::info('Token expired', ['expires_at' => $payload['expires_at'], 'now' => now()->timestamp]);
                return null;
            }
            
            // Verify content_id if provided
            if ($contentId !== null && $payload['content_id'] !== $contentId) {
                Log::warning('Token content_id mismatch', ['expected' => $contentId, 'actual' => $payload['content_id']]);
                return null;
            }
            
            // Verify user_id if provided
            if ($userId !== null && $payload['user_id'] !== $userId) {
                Log::warning('Token user_id mismatch', ['expected' => $userId, 'actual' => $payload['user_id']]);
                return null;
            }
            
            return $payload;
            
        } catch (\Exception $e) {
            Log::error('Token validation exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Log PDF access with detailed information.
     * 
     * @param Content $content
     * @param User $user
     * @param array $metadata
     * @return void
     */
    public function logAccess(Content $content, User $user, array $metadata = []): void
    {
        $logData = array_merge([
            'event' => 'pdf_access',
            'content_id' => $content->id,
            'content_title' => $content->title,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'user_email' => $user->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
        ], $metadata);

        Log::channel('daily')->info('Secure PDF Access', $logData);
        
        // Store in database for analytics (optional)
        \DB::table('pdf_access_logs')->insert([
            'content_id' => $content->id,
            'user_id' => $user->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'metadata' => json_encode($metadata),
            'access_granted' => true,
            'accessed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Log page view within a PDF.
     * 
     * @param Content $content
     * @param User $user
     * @param int $pageNumber
     * @param int $totalPages
     * @return void
     */
    public function logPageView(Content $content, User $user, int $pageNumber, int $totalPages): void
    {
        $this->logAccess($content, $user, [
            'action' => 'page_view',
            'page_number' => $pageNumber,
            'total_pages' => $totalPages,
            'progress_percentage' => round(($pageNumber / $totalPages) * 100, 2),
        ]);
    }

    /**
     * Get watermark data for a user.
     * 
     * @param User $user
     * @param Content $content
     * @return array
     */
    public function getWatermarkData(User $user, Content $content): array
    {
        return [
            'text' => $this->generateWatermarkText($user, $content),
            'opacity' => 0.15,
            'fontSize' => 14,
            'rotation' => -45,
            'color' => '#000000',
        ];
    }

    /**
     * Generate watermark text.
     * 
     * @param User $user
     * @param Content $content
     * @return string
     */
    private function generateWatermarkText(User $user, Content $content): string
    {
        return implode(' • ', [
            $user->name,
            $user->email,
            now()->format('Y-m-d H:i'),
            'Confidential – LMS',
        ]);
    }

    /**
     * Check if user has permission to view PDF.
     * 
     * @param Content $content
     * @param User $user
     * @return bool
     */
    public function canView(Content $content, User $user): bool
    {
        // Check if admin first - admins can view everything
        $isAdmin = $user->hasRole('Admin');
        if ($isAdmin) {
            Log::info('SecurePDF: Admin access granted', [
                'user_id' => $user->id,
                'content_id' => $content->id,
            ]);
            return true;
        }

        // Get course through relationships
        $subpage = $content->subpage;
        if (!$subpage) {
            Log::warning('SecurePDF: No subpage found for content', [
                'content_id' => $content->id,
                'subpage_id' => $content->subpage_id,
                'user_id' => $user->id,
            ]);
            return false;
        }

        $module = $subpage->module;
        if (!$module) {
            Log::warning('SecurePDF: No module found for subpage', [
                'content_id' => $content->id,
                'subpage_id' => $subpage->id,
                'module_id' => $subpage->module_id,
                'user_id' => $user->id,
            ]);
            return false;
        }

        $course = $module->course;
        if (!$course) {
            Log::warning('SecurePDF: No course found for module', [
                'content_id' => $content->id,
                'module_id' => $module->id,
                'course_id' => $module->course_id,
                'user_id' => $user->id,
            ]);
            return false;
        }

        // Check if teacher
        $isTeacher = $course->teacher_id === $user->id;
        if ($isTeacher) {
            Log::info('SecurePDF: Teacher access granted', [
                'user_id' => $user->id,
                'content_id' => $content->id,
                'course_id' => $course->id,
            ]);
            return true;
        }

        // Check enrollment
        $isEnrolled = $user->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'active')
            ->exists();

        if ($isEnrolled) {
            Log::info('SecurePDF: Student access granted (enrolled)', [
                'user_id' => $user->id,
                'content_id' => $content->id,
                'course_id' => $course->id,
            ]);
            return true;
        }

        Log::warning('SecurePDF: Access denied - not enrolled', [
            'user_id' => $user->id,
            'content_id' => $content->id,
            'course_id' => $course->id,
            'is_admin' => $isAdmin,
            'is_teacher' => $isTeacher,
            'is_enrolled' => $isEnrolled,
        ]);

        return false;
    }

    /**
     * Get PDF viewing statistics for a user.
     * 
     * @param User $user
     * @param Content $content
     * @return array
     */
    public function getViewingStats(User $user, Content $content): array
    {
        $logs = \DB::table('pdf_access_logs')
            ->where('content_id', $content->id)
            ->where('user_id', $user->id)
            ->get();

        $pageViews = $logs->filter(function ($log) {
            $metadata = json_decode($log->metadata, true);
            return isset($metadata['action']) && $metadata['action'] === 'page_view';
        });

        $maxPage = $pageViews->max(function ($log) {
            $metadata = json_decode($log->metadata, true);
            return $metadata['page_number'] ?? 0;
        });

        return [
            'total_accesses' => $logs->count(),
            'total_page_views' => $pageViews->count(),
            'max_page_reached' => $maxPage ?? 0,
            'first_access' => $logs->min('created_at'),
            'last_access' => $logs->max('created_at'),
        ];
    }
}
