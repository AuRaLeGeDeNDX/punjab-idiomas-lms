<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // If AJAX request, return only updated data
        if ($request->ajax()) {
            return response()->json([
                'systemHealth' => $this->calculateSystemHealth(),
            ]);
        }

        // Cache dashboard data for 2 minutes
        $cacheKey = "admin_dashboard";
        $dashboardData = Cache::remember($cacheKey, 120, function () {
            return $this->getDashboardData();
        });

        return view('admin.dashboard', $dashboardData);
    }

    private function getDashboardData(): array
    {
        // User statistics
        $totalUsersCount = User::count();
        $activeUsersCount = User::where('last_login_at', '>', now()->subDays(30))->count();
        $studentsCount = User::role('Student')->count();
        $teachersCount = User::role('Teacher')->count();
        $adminsCount = User::role('Admin')->count();

        // Course statistics
        $totalCoursesCount = Course::count();
        $publishedCoursesCount = Course::where('is_published', true)->count();

        // Enrollment statistics
        $totalEnrollmentsCount = Enrollment::count();
        $activeEnrollmentsCount = Enrollment::where('status', 'active')->count();

        // System health
        $systemHealth = $this->calculateSystemHealth();
        $systemStatus = $systemHealth >= 90 ? 'Excellent' : ($systemHealth >= 70 ? 'Good' : 'Needs Attention');

        // Recent system activities
        $recentActivities = $this->getRecentSystemActivities();

        // System alerts
        $systemAlerts = $this->getSystemAlerts();

        // Storage usage
        $storageUsage = $this->getStorageUsage();

        return [
            'totalUsersCount' => $totalUsersCount,
            'activeUsersCount' => $activeUsersCount,
            'studentsCount' => $studentsCount,
            'teachersCount' => $teachersCount,
            'adminsCount' => $adminsCount,
            'totalCoursesCount' => $totalCoursesCount,
            'publishedCoursesCount' => $publishedCoursesCount,
            'totalEnrollmentsCount' => $totalEnrollmentsCount,
            'activeEnrollmentsCount' => $activeEnrollmentsCount,
            'systemHealth' => $systemHealth,
            'systemStatus' => $systemStatus,
            'recentActivities' => $recentActivities,
            'systemAlerts' => $systemAlerts,
            'storageUsage' => $storageUsage,
        ];
    }

    private function calculateSystemHealth(): int
    {
        $health = 100;

        // Check database connectivity
        try {
            DB::connection()->getPdo();
        } catch (\Exception $e) {
            $health -= 30;
        }

        // Check Redis connectivity (only if configured)
        if (config('cache.default') === 'redis') {
            try {
                Cache::store('redis')->put('health_check', 'ok', 1);
                Cache::store('redis')->forget('health_check');
            } catch (\Exception $e) {
                $health -= 20;
            }
        }

        // Check storage availability (cached for 5 min to avoid slow I/O)
        try {
            $usagePercent = Cache::remember('admin_disk_usage_percent', 300, function() {
                $diskSpace = disk_free_space(storage_path());
                $totalSpace = disk_total_space(storage_path());
                return (($totalSpace - $diskSpace) / $totalSpace) * 100;
            });
            
            if ($usagePercent > 90) {
                $health -= 25;
            } elseif ($usagePercent > 80) {
                $health -= 15;
            }
        } catch (\Exception $e) {
            $health -= 10;
        }

        // Check for recent errors in logs
        $recentErrors = $this->getRecentLogErrors();
        if ($recentErrors > 10) {
            $health -= 15;
        } elseif ($recentErrors > 5) {
            $health -= 10;
        }

        return max(0, $health);
    }

    private function getRecentSystemActivities(): array
    {
        $activities = [];

        // Recent user registrations
        $recentUsers = User::orderBy('created_at', 'desc')->limit(3)->get();
        foreach ($recentUsers as $user) {
            $activities[] = [
                'time' => $user->created_at->format('M j, H:i'),
                'user' => 'System',
                'action' => 'User Registration',
                'resource' => $user->name . ' (' . $user->getRoleNames()->first() . ')',
                'status' => 'Success',
                'status_color' => 'success',
            ];
        }

        // Recent course creations
        $recentCourses = Course::with('teacher')->orderBy('created_at', 'desc')->limit(2)->get();
        foreach ($recentCourses as $course) {
            $activities[] = [
                'time' => $course->created_at->format('M j, H:i'),
                'user' => $course->teacher->name ?? 'Unknown',
                'action' => 'Course Created',
                'resource' => $course->title,
                'status' => $course->is_published ? 'Published' : 'Draft',
                'status_color' => $course->is_published ? 'success' : 'warning',
            ];
        }

        // Sort by time
        usort($activities, function ($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });

        return array_slice($activities, 0, 10);
    }

    private function getSystemAlerts(): array
    {
        $alerts = [];

        // Check for low disk space
        try {
            $diskSpace = disk_free_space(storage_path());
            $totalSpace = disk_total_space(storage_path());
            $usagePercent = (($totalSpace - $diskSpace) / $totalSpace) * 100;
            
            if ($usagePercent > 90) {
                $alerts[] = [
                    'type' => 'danger',
                    'title' => 'Low Disk Space',
                    'message' => 'Storage is ' . round($usagePercent, 1) . '% full',
                    'time' => now()->format('M j, H:i'),
                ];
            } elseif ($usagePercent > 80) {
                $alerts[] = [
                    'type' => 'warning',
                    'title' => 'Disk Space Warning',
                    'message' => 'Storage is ' . round($usagePercent, 1) . '% full',
                    'time' => now()->format('M j, H:i'),
                ];
            }
        } catch (\Exception $e) {
            $alerts[] = [
                'type' => 'danger',
                'title' => 'Storage Check Failed',
                'message' => 'Unable to check disk space',
                'time' => now()->format('M j, H:i'),
            ];
        }

        // Check for recent errors
        $recentErrors = $this->getRecentLogErrors();
        if ($recentErrors > 5) {
            $alerts[] = [
                'type' => 'warning',
                'title' => 'Recent Errors',
                'message' => "{$recentErrors} errors in the last hour",
                'time' => now()->format('M j, H:i'),
            ];
        }

        // Check for inactive teachers
        $inactiveTeachers = User::role('Teacher')
            ->where('last_login_at', '<', now()->subDays(7))
            ->count();
        
        if ($inactiveTeachers > 0) {
            $alerts[] = [
                'type' => 'info',
                'title' => 'Inactive Teachers',
                'message' => "{$inactiveTeachers} teachers haven't logged in for 7+ days",
                'time' => now()->format('M j, H:i'),
            ];
        }

        return $alerts;
    }

    private function getStorageUsage(): array
    {
        try {
            // Cache disk stats for 30 minutes
            $diskStats = Cache::remember('admin_disk_usage_stats', 1800, function() {
                $diskSpace = disk_free_space(storage_path());
                $totalSpace = disk_total_space(storage_path());
                return [
                    'free' => $diskSpace,
                    'total' => $totalSpace,
                    'used' => $totalSpace - $diskSpace
                ];
            });
            
            $totalSpace = $diskStats['total'];
            $usedSpace = $diskStats['used'];
            
            $filesPercentage = round(($usedSpace / $totalSpace) * 100, 1);
            
            // Database size — cache for 30 minutes to avoid heavy query on every load
            $dbSize = Cache::remember('admin_db_size', 1800, function () {
                try {
                    $tables = DB::select('SHOW TABLE STATUS');
                    $size = 0;
                    foreach ($tables as $table) {
                        $size += $table->Data_length + $table->Index_length;
                    }
                    return $size;
                } catch (\Exception $e) {
                    return 0;
                }
            });
            
            $dbPercentage = $totalSpace > 0 ? round(($dbSize / $totalSpace) * 100, 1) : 0;
            
            return [
                'files_used' => $this->formatBytes($usedSpace),
                'files_total' => $this->formatBytes($totalSpace),
                'files_percentage' => $filesPercentage,
                'db_used' => $this->formatBytes($dbSize),
                'db_total' => $this->formatBytes($totalSpace),
                'db_percentage' => $dbPercentage,
            ];
        } catch (\Exception $e) {
            return [
                'files_used' => 'Unknown',
                'files_total' => 'Unknown',
                'files_percentage' => 0,
                'db_used' => 'Unknown',
                'db_total' => 'Unknown',
                'db_percentage' => 0,
            ];
        }
    }

    private function getRecentLogErrors(): int
    {
        try {
            $logFile = storage_path('logs/laravel.log');
            if (!file_exists($logFile)) {
                return 0;
            }
            
            $errorCount = 0;
            $fileSize = filesize($logFile);
            
            // Only read the last 64KB of the log file (tail-based approach)
            // This prevents freezing on large log files (100MB+)
            $readBytes = min($fileSize, 65536);
            
            $handle = fopen($logFile, 'r');
            if ($handle) {
                // Seek to near the end of the file
                if ($fileSize > $readBytes) {
                    fseek($handle, -$readBytes, SEEK_END);
                    // Skip the first partial line
                    fgets($handle);
                }
                
                while (($line = fgets($handle)) !== false) {
                    if (strpos($line, 'ERROR') !== false || strpos($line, 'CRITICAL') !== false) {
                        $errorCount++;
                    }
                }
                fclose($handle);
            }
            
            return min($errorCount, 50);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
