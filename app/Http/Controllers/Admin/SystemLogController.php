<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;

class SystemLogController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Display system logs.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', \App\Models\User::class); // Admin only
        
        $logLevel = $request->get('level', 'all');
        $logDate = $request->get('date', now()->format('Y-m-d'));
        $search = $request->get('search', '');
        $perPage = $request->get('per_page', 50);
        
        $logs = $this->getLogEntries($logDate, $logLevel, $search, $perPage);
        $logFiles = $this->getAvailableLogFiles();
        $logStats = $this->getLogStatistics($logDate);
        
        if ($request->expectsJson()) {
            return response()->json([
                'logs' => $logs,
                'stats' => $logStats,
                'files' => $logFiles
            ]);
        }
        
        return view('admin.logs.index', compact('logs', 'logFiles', 'logStats', 'logLevel', 'logDate', 'search'));
    }

    /**
     * Download log file.
     */
    public function download(Request $request, string $filename)
    {
        $this->authorize('viewAny', \App\Models\User::class);
        
        $logPath = storage_path('logs/' . $filename);
        
        if (!File::exists($logPath) || !$this->isValidLogFile($filename)) {
            abort(404, 'Log file not found');
        }
        
        return response()->download($logPath);
    }

    /**
     * Clear log file.
     */
    public function clear(Request $request, string $filename): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\User::class);
        
        $logPath = storage_path('logs/' . $filename);
        
        if (!File::exists($logPath) || !$this->isValidLogFile($filename)) {
            return response()->json(['error' => 'Log file not found'], 404);
        }
        
        try {
            File::put($logPath, '');
            
            // Log the action
            Log::info('Log file cleared by admin', [
                'file' => $filename,
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name
            ]);
            
            return response()->json(['message' => 'Log file cleared successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to clear log file'], 500);
        }
    }

    /**
     * Get system health status.
     */
    public function health(): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\User::class);
        
        $health = [
            'status' => 'healthy',
            'checks' => [
                'database' => $this->checkDatabase(),
                'storage' => $this->checkStorage(),
                'cache' => $this->checkCache(),
                'queue' => $this->checkQueue(),
                'logs' => $this->checkLogs()
            ]
        ];
        
        // Determine overall status
        $failedChecks = collect($health['checks'])->filter(fn($check) => !$check['status'])->count();
        
        if ($failedChecks > 0) {
            $health['status'] = $failedChecks > 2 ? 'critical' : 'warning';
        }
        
        return response()->json($health);
    }

    /**
     * Get log entries from file.
     */
    private function getLogEntries(string $date, string $level, string $search, int $perPage): array
    {
        // Try daily log file first
        $logFile = storage_path("logs/laravel-{$date}.log");
        
        // If daily log doesn't exist, use the single log file
        if (!File::exists($logFile)) {
            $logFile = storage_path("logs/laravel.log");
        }
        
        if (!File::exists($logFile)) {
            return [
                'data' => [],
                'total' => 0,
                'current_page' => 1,
                'last_page' => 1
            ];
        }
        
        $content = File::get($logFile);
        $lines = explode("\n", $content);
        $entries = [];
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $entry = $this->parseLogLine($line);
            if (!$entry) continue;
            
            // Filter by date if using single log file
            if (basename($logFile) === 'laravel.log') {
                $entryDate = Carbon::parse($entry['timestamp'])->format('Y-m-d');
                if ($entryDate !== $date) {
                    continue;
                }
            }
            
            // Filter by level
            if ($level !== 'all' && strtolower($entry['level']) !== strtolower($level)) {
                continue;
            }
            
            // Filter by search
            if ($search && stripos($entry['message'], $search) === false) {
                continue;
            }
            
            $entries[] = $entry;
        }
        
        // Sort by timestamp (newest first)
        usort($entries, fn($a, $b) => strtotime($b['timestamp']) - strtotime($a['timestamp']));
        
        // Paginate
        $total = count($entries);
        $currentPage = request()->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedEntries = array_slice($entries, $offset, $perPage);
        
        return [
            'data' => $paginatedEntries,
            'total' => $total,
            'current_page' => $currentPage,
            'last_page' => ceil($total / $perPage),
            'per_page' => $perPage
        ];
    }

    /**
     * Parse a single log line.
     */
    private function parseLogLine(string $line): ?array
    {
        // Laravel log format: [2024-01-12 10:30:45] local.ERROR: Message
        if (!preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.+)$/', $line, $matches)) {
            return null;
        }
        
        return [
            'timestamp' => $matches[1],
            'environment' => $matches[2],
            'level' => $matches[3],
            'message' => $matches[4],
            'formatted_time' => Carbon::parse($matches[1])->diffForHumans()
        ];
    }

    /**
     * Get available log files.
     */
    private function getAvailableLogFiles(): array
    {
        $logPath = storage_path('logs');
        $files = File::glob($logPath . '/*.log');
        
        return collect($files)->map(function ($file) {
            $filename = basename($file);
            $size = File::size($file);
            $modified = File::lastModified($file);
            
            return [
                'name' => $filename,
                'size' => $this->formatBytes($size),
                'size_bytes' => $size,
                'modified' => Carbon::createFromTimestamp($modified)->format('Y-m-d H:i:s'),
                'modified_human' => Carbon::createFromTimestamp($modified)->diffForHumans()
            ];
        })->sortByDesc('modified')->values()->toArray();
    }

    /**
     * Get log statistics for a specific date.
     */
    private function getLogStatistics(string $date): array
    {
        // Try daily log file first
        $logFile = storage_path("logs/laravel-{$date}.log");
        
        // If daily log doesn't exist, use the single log file
        if (!File::exists($logFile)) {
            $logFile = storage_path("logs/laravel.log");
        }
        
        if (!File::exists($logFile)) {
            return [
                'total' => 0,
                'by_level' => [],
                'file_size' => 0
            ];
        }
        
        $content = File::get($logFile);
        $lines = explode("\n", $content);
        $stats = [
            'total' => 0,
            'by_level' => [
                'emergency' => 0,
                'alert' => 0,
                'critical' => 0,
                'error' => 0,
                'warning' => 0,
                'notice' => 0,
                'info' => 0,
                'debug' => 0
            ],
            'file_size' => $this->formatBytes(File::size($logFile))
        ];
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            $entry = $this->parseLogLine($line);
            if (!$entry) continue;
            
            // Filter by date if using single log file
            if (basename($logFile) === 'laravel.log') {
                $entryDate = Carbon::parse($entry['timestamp'])->format('Y-m-d');
                if ($entryDate !== $date) {
                    continue;
                }
            }
            
            $stats['total']++;
            $level = strtolower($entry['level']);
            if (isset($stats['by_level'][$level])) {
                $stats['by_level'][$level]++;
            }
        }
        
        return $stats;
    }

    /**
     * Check if filename is a valid log file.
     */
    private function isValidLogFile(string $filename): bool
    {
        return preg_match('/^[a-zA-Z0-9\-_.]+\.log$/', $filename) && 
               !str_contains($filename, '..') && 
               !str_contains($filename, '/');
    }

    /**
     * Format bytes to human readable format.
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * System health checks.
     */
    private function checkDatabase(): array
    {
        try {
            \DB::connection()->getPdo();
            return ['status' => true, 'message' => 'Database connection OK'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Database connection failed'];
        }
    }

    private function checkStorage(): array
    {
        $storagePath = storage_path();
        $freeBytes = disk_free_space($storagePath);
        $totalBytes = disk_total_space($storagePath);
        $usedPercentage = (($totalBytes - $freeBytes) / $totalBytes) * 100;
        
        return [
            'status' => $usedPercentage < 90,
            'message' => sprintf('Storage %.1f%% used', $usedPercentage),
            'details' => [
                'free' => $this->formatBytes($freeBytes),
                'total' => $this->formatBytes($totalBytes),
                'used_percentage' => round($usedPercentage, 1)
            ]
        ];
    }

    private function checkCache(): array
    {
        try {
            \Cache::put('health_check', 'ok', 60);
            $value = \Cache::get('health_check');
            return ['status' => $value === 'ok', 'message' => 'Cache working'];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Cache not working'];
        }
    }

    private function checkQueue(): array
    {
        try {
            $failedJobs = \DB::table('failed_jobs')->count();
            return [
                'status' => $failedJobs < 10,
                'message' => "Queue OK ({$failedJobs} failed jobs)",
                'failed_jobs' => $failedJobs
            ];
        } catch (\Exception $e) {
            return ['status' => false, 'message' => 'Queue check failed'];
        }
    }

    private function checkLogs(): array
    {
        $logPath = storage_path('logs');
        $logFiles = File::glob($logPath . '/*.log');
        $totalSize = 0;
        
        foreach ($logFiles as $file) {
            $totalSize += File::size($file);
        }
        
        $totalSizeMB = $totalSize / (1024 * 1024);
        
        return [
            'status' => $totalSizeMB < 100,
            'message' => sprintf('Log files: %.1f MB', $totalSizeMB),
            'total_size' => $this->formatBytes($totalSize),
            'file_count' => count($logFiles)
        ];
    }
}