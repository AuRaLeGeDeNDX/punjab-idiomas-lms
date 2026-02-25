<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceOptimizationMiddleware
{
    /**
     * Handle an incoming request with performance optimizations.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        
        // Add cache headers for static content
        if ($this->isStaticContent($request)) {
            return $this->handleStaticContent($request, $next);
        }
        
        // Handle API requests with caching
        if ($request->is('api/*')) {
            return $this->handleApiRequest($request, $next);
        }
        
        $response = $next($request);
        
        // Add performance headers
        $this->addPerformanceHeaders($response, $startTime);
        
        // Log slow requests
        $this->logSlowRequests($request, $startTime);
        
        return $response;
    }

    /**
     * Check if the request is for static content.
     */
    private function isStaticContent(Request $request): bool
    {
        $staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'woff', 'woff2', 'ttf'];
        $extension = pathinfo($request->path(), PATHINFO_EXTENSION);
        
        return in_array(strtolower($extension), $staticExtensions);
    }

    /**
     * Handle static content with appropriate caching headers.
     */
    private function handleStaticContent(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Set cache headers for static content (1 year)
        $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        $response->headers->set('Expires', gmdate('D, d M Y H:i:s \G\M\T', time() + 31536000));
        
        // Add ETag for better caching
        $etag = md5($response->getContent());
        $response->headers->set('ETag', $etag);
        
        // Check if client has cached version
        if ($request->header('If-None-Match') === $etag) {
            return response('', 304);
        }
        
        return $response;
    }

    /**
     * Handle API requests with caching where appropriate.
     */
    private function handleApiRequest(Request $request, Closure $next): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }
        
        // Create cache key based on request
        $cacheKey = 'api_' . md5($request->fullUrl() . auth()->id());
        
        // Check if we should cache this endpoint
        if ($this->shouldCacheApiEndpoint($request)) {
            return Cache::remember($cacheKey, 300, function () use ($request, $next) {
                return $next($request);
            });
        }
        
        return $next($request);
    }

    /**
     * Determine if an API endpoint should be cached.
     */
    private function shouldCacheApiEndpoint(Request $request): bool
    {
        $cacheableEndpoints = [
            'api/courses',
            'api/modules',
            'api/subpages',
            'api/content',
        ];
        
        foreach ($cacheableEndpoints as $endpoint) {
            if ($request->is($endpoint . '*')) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Add performance-related headers to the response.
     */
    private function addPerformanceHeaders(Response $response, float $startTime): void
    {
        $executionTime = microtime(true) - $startTime;
        
        // Add timing header
        $response->headers->set('X-Response-Time', round($executionTime * 1000, 2) . 'ms');
        
        // Add security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // Only set X-Frame-Options if not already set by controller
        if (!$response->headers->has('X-Frame-Options')) {
            $response->headers->set('X-Frame-Options', 'DENY');
        }
        
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /**
     * Log slow requests for performance monitoring.
     */
    private function logSlowRequests(Request $request, float $startTime): void
    {
        $executionTime = microtime(true) - $startTime;
        $threshold = config('app.slow_request_threshold', 2.0); // 2 seconds default
        
        if ($executionTime > $threshold) {
            Log::warning('Slow request detected', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time' => round($executionTime, 3),
                'memory_usage' => memory_get_peak_usage(true),
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Optimize database queries by adding eager loading hints.
     */
    public static function optimizeQueries(): void
    {
        // Add global scopes for common optimizations
        DB::listen(function ($query) {
            // Log queries that take longer than 1 second
            if ($query->time > 1000) {
                Log::warning('Slow query detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time,
                ]);
            }
        });
    }

    /**
     * Preload commonly accessed data.
     */
    public static function preloadCommonData(): void
    {
        // Preload popular courses
        Cache::remember('popular_courses', 3600, function () {
            return \App\Models\Course::withCount('enrollments')
                ->orderBy('enrollments_count', 'desc')
                ->limit(10)
                ->get();
        });
        
        // Preload system settings
        Cache::remember('system_settings', 3600, function () {
            return [
                'max_file_size' => config('filesystems.max_file_size'),
                'allowed_file_types' => config('filesystems.allowed_types'),
                'maintenance_mode' => config('app.maintenance_mode', false),
            ];
        });
    }
}