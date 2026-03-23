<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Spatie\Activitylog\Facades\Activity;

class SystemSettingsController extends Controller
{
    use AuthorizesRequests;
    
    /**
     * Display system settings.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', \App\Models\User::class); // Admin only
        
        $settings = $this->getSystemSettings();
        
        if ($request->expectsJson()) {
            return response()->json($settings);
        }
        
        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Update system settings.
     */
    public function update(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\User::class);

        // Capture admin info BEFORE config:clear/config:cache commands (they reset auth state)
        $adminId   = auth()->id();
        $adminName = auth()->user()?->name ?? 'Unknown';
        $adminUser = auth()->user();
        
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_url' => 'required|url',
            'app_timezone' => 'required|string',
            'mail_driver' => 'required|string',
            'mail_host' => 'nullable|string',
            'mail_port' => 'nullable|integer',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|email',
            'mail_from_name' => 'required|string',
            'cache_driver' => 'required|string',
            'session_driver' => 'required|string',
            'queue_driver' => 'required|string',
            'file_max_size' => 'required|integer|min:1|max:2048',
            'allowed_file_types' => 'required|string',
            'maintenance_mode' => 'boolean',
            'registration_enabled' => 'boolean',
            'email_verification' => 'boolean',
            'password_reset_enabled' => 'boolean',
            'max_login_attempts' => 'required|integer|min:1|max:10',
            'lockout_duration' => 'required|integer|min:1|max:60',
        ]);
        
        try {
            $this->updateEnvironmentFile($validated);
            // Only clear config cache — do NOT run config:cache here.
            // config:cache makes env() return null in application code, which would
            // break getSystemSettings() and the settings page on the next load.
            // The Optimize button is the correct place to run config:cache.
            Artisan::call('config:clear');
            
            // Log the settings update (use pre-captured values, auth state may be reset now)
            Log::info('System settings updated by admin', [
                'admin_id' => $adminId,
                'admin_name' => $adminName,
                'updated_settings' => array_keys($validated)
            ]);

            if ($adminUser) {
                activity('admin')
                    ->causedBy($adminUser)
                    ->withProperties(['updated_settings' => array_keys($validated)])
                    ->log('System settings updated');
            }
            
            return response()->json([
                'message' => 'System settings updated successfully',
                'settings' => $this->getSystemSettings()
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update system settings', [
                'error' => $e->getMessage(),
                'admin_id' => $adminId
            ]);
            
            return response()->json([
                'error' => 'Failed to update system settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear application cache.
     */
    public function clearCache(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\User::class);
        
        // Capture admin info BEFORE artisan commands (they can reset auth state)
        $adminId = auth()->id();
        $adminName = auth()->user()?->name ?? 'Unknown';
        
        $cacheType = $request->get('type', 'all');
        
        try {
            switch ($cacheType) {
                case 'config':
                    Artisan::call('config:clear');
                    break;
                case 'route':
                    Artisan::call('route:clear');
                    break;
                case 'view':
                    Artisan::call('view:clear');
                    break;
                case 'application':
                    Cache::flush();
                    break;
                case 'all':
                default:
                    Artisan::call('cache:clear');
                    Artisan::call('config:clear');
                    Artisan::call('route:clear');
                    Artisan::call('view:clear');
                    Cache::flush();
                    break;
            }
            
            Log::info('Cache cleared by admin', [
                'type' => $cacheType,
                'admin_id' => $adminId,
                'admin_name' => $adminName
            ]);

            if (auth()->user()) {
                activity('admin')
                    ->causedBy(auth()->user())
                    ->withProperties(['cache_type' => $cacheType])
                    ->log('Cache cleared');
            }
            
            return response()->json([
                'message' => ucfirst($cacheType) . ' cache cleared successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to clear cache: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle maintenance mode.
     */
    public function toggleMaintenance(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\User::class);
        
        // Capture admin info BEFORE artisan commands
        $adminId = auth()->id();
        $adminName = auth()->user()?->name ?? 'Unknown';
        
        $enable = $request->boolean('enable');
        
        try {
            if ($enable) {
                Artisan::call('down', [
                    '--message' => $request->get('message', 'System maintenance in progress'),
                    '--retry' => $request->get('retry', 60)
                ]);
            } else {
                Artisan::call('up');
            }
            
            Log::info('Maintenance mode toggled by admin', [
                'enabled' => $enable,
                'admin_id' => $adminId,
                'admin_name' => $adminName
            ]);
            
            if (auth()->user()) {
                activity('admin')
                    ->causedBy(auth()->user())
                    ->withProperties(['enabled' => $enable])
                    ->log('Maintenance mode toggled');
            }
            
            return response()->json([
                'message' => 'Maintenance mode ' . ($enable ? 'enabled' : 'disabled'),
                'maintenance_mode' => $enable
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to toggle maintenance mode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Run system optimization.
     */
    public function optimize(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\User::class);
        
        // Capture admin info BEFORE artisan commands (config:cache resets auth state)
        $adminId = auth()->id();
        $adminName = auth()->user()?->name ?? 'Unknown';
        
        try {
            $results = [];
            
            // Run config:cache
            try {
                Artisan::call('config:cache');
                $results['config'] = 'success';
            } catch (\Exception $e) {
                $results['config'] = 'failed: ' . $e->getMessage();
                Log::error('Config cache failed during optimization', [
                    'error' => $e->getMessage(),
                    'admin_id' => $adminId
                ]);
            }
            
            // Run route:cache — SKIPPED
            // This app uses closure-based routes (subpages.php), which are
            // incompatible with Laravel's route:cache. Attempting it throws
            // a LogicException ("Unable to prepare route for serialization").
            $results['routes'] = 'skipped (closure routes detected)';
            
            // Run view:cache
            try {
                Artisan::call('view:cache');
                $results['views'] = 'success';
            } catch (\Exception $e) {
                $results['views'] = 'failed: ' . $e->getMessage();
                Log::error('View cache failed during optimization', [
                    'error' => $e->getMessage(),
                    'admin_id' => $adminId
                ]);
            }
            
            // Check if any command failed
            $hasFailures = false;
            foreach ($results as $key => $result) {
                if (strpos($result, 'failed') !== false) {
                    $hasFailures = true;
                    break;
                }
            }
            
            if ($hasFailures) {
                Log::warning('System optimization completed with errors', [
                    'results' => $results,
                    'admin_id' => $adminId,
                    'admin_name' => $adminName
                ]);
                
                return response()->json([
                    'message' => 'System optimization completed with some errors',
                    'results' => $results
                ], 207); // 207 Multi-Status
            }
            
            Log::info('System optimization run by admin', [
                'admin_id' => $adminId,
                'admin_name' => $adminName,
                'results' => $results
            ]);
            
            return response()->json([
                'message' => 'System optimization completed successfully',
                'results' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('System optimization failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'admin_id' => $adminId
            ]);
            
            return response()->json([
                'error' => 'System optimization failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Backup system configuration.
     */
    public function backup(Request $request): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\User::class);
        
        // Capture admin info BEFORE any operations
        $adminId = auth()->id();
        $adminName = auth()->user()?->name ?? 'Unknown';
        
        try {
            $backupData = [
                'timestamp' => now()->toISOString(),
                'admin_id' => $adminId,
                'admin_name' => $adminName,
                'settings' => $this->getSystemSettings(),
                'environment' => $this->getEnvironmentVariables()
            ];
            
            $filename = 'system_backup_' . now()->format('Y-m-d_H-i-s') . '.json';
            $backupPath = storage_path('app/backups/' . $filename);
            
            if (!File::exists(dirname($backupPath))) {
                File::makeDirectory(dirname($backupPath), 0755, true);
            }
            
            File::put($backupPath, json_encode($backupData, JSON_PRETTY_PRINT));
            
            Log::info('System backup created by admin', [
                'filename' => $filename,
                'admin_id' => $adminId,
                'admin_name' => $adminName
            ]);

            if (auth()->user()) {
                activity('admin')
                    ->causedBy(auth()->user())
                    ->withProperties(['filename' => $filename])
                    ->log('System backup created');
            }
            
            return response()->json([
                'message' => 'System backup created successfully',
                'filename' => $filename,
                'download_url' => route('admin.settings.download-backup', $filename)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Backup creation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download backup file.
     */
    public function downloadBackup(string $filename)
    {
        $this->authorize('viewAny', \App\Models\User::class);
        
        $backupPath = storage_path('app/backups/' . $filename);
        
        if (!File::exists($backupPath) || !$this->isValidBackupFile($filename)) {
            abort(404, 'Backup file not found');
        }
        
        return response()->download($backupPath);
    }

    /**
     * Get system information.
     */
    public function systemInfo(): JsonResponse
    {
        $this->authorize('viewAny', \App\Models\User::class);
        
        return response()->json([
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
            'database_version' => $this->getDatabaseVersion(),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'timezone' => config('app.timezone'),
            'locale' => config('app.locale'),
            'debug_mode' => config('app.debug'),
            'environment' => config('app.env'),
            'storage_path' => storage_path(),
            'cache_driver' => config('cache.default'),
            'session_driver' => config('session.driver'),
            'queue_driver' => config('queue.default'),
            'mail_driver' => config('mail.default')
        ]);
    }

    /**
     * Get current system settings.
     */
    private function getSystemSettings(): array
    {
        return [
            'app_name'               => config('app.name'),
            'app_url'                => config('app.url'),
            'app_timezone'           => config('app.timezone'),
            'app_debug'              => config('app.debug'),
            'app_env'                => config('app.env'),
            'mail_driver'            => config('mail.default'),
            'mail_host'              => config('mail.mailers.smtp.host'),
            'mail_port'              => config('mail.mailers.smtp.port'),
            'mail_username'          => config('mail.mailers.smtp.username'),
            'mail_encryption'        => config('mail.mailers.smtp.encryption'),
            'mail_from_address'      => config('mail.from.address'),
            'mail_from_name'         => config('mail.from.name'),
            'cache_driver'           => config('cache.default'),
            'session_driver'         => config('session.driver'),
            'queue_driver'           => config('queue.default'),
            // These use env() directly because they have no matching built-in config key
            'file_max_size'          => (int) env('FILE_MAX_SIZE', 10),
            'allowed_file_types'     => env('ALLOWED_FILE_TYPES', 'jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip'),
            'maintenance_mode'       => app()->isDownForMaintenance(),
            'registration_enabled'   => filter_var(env('REGISTRATION_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
            'email_verification'     => filter_var(env('EMAIL_VERIFICATION', false), FILTER_VALIDATE_BOOLEAN),
            'password_reset_enabled' => filter_var(env('PASSWORD_RESET_ENABLED', true), FILTER_VALIDATE_BOOLEAN),
            'max_login_attempts'     => (int) env('MAX_LOGIN_ATTEMPTS', 5),
            'lockout_duration'       => (int) env('LOCKOUT_DURATION', 15),
        ];
    }

    /**
     * Update environment file.
     */
    private function updateEnvironmentFile(array $settings): void
    {
        $envPath = base_path('.env');
        
        if (!File::exists($envPath)) {
            throw new \Exception('Environment file not found');
        }
        
        $envContent = File::get($envPath);
        
        // Map settings to environment variables
        $envMappings = [
            // Application
            'app_name'                 => 'APP_NAME',
            'app_url'                  => 'APP_URL',
            'app_timezone'             => 'APP_TIMEZONE',
            // Mail
            'mail_driver'              => 'MAIL_MAILER',
            'mail_host'                => 'MAIL_HOST',
            'mail_port'                => 'MAIL_PORT',
            'mail_username'            => 'MAIL_USERNAME',
            'mail_password'            => 'MAIL_PASSWORD',
            'mail_encryption'          => 'MAIL_ENCRYPTION',
            'mail_from_address'        => 'MAIL_FROM_ADDRESS',
            'mail_from_name'           => 'MAIL_FROM_NAME',
            // Drivers
            'cache_driver'             => 'CACHE_DRIVER',
            'session_driver'           => 'SESSION_DRIVER',
            'queue_driver'             => 'QUEUE_CONNECTION',
            // File uploads
            'file_max_size'            => 'FILE_MAX_SIZE',
            'allowed_file_types'       => 'ALLOWED_FILE_TYPES',
            // Security
            'registration_enabled'     => 'REGISTRATION_ENABLED',
            'email_verification'       => 'EMAIL_VERIFICATION',
            'password_reset_enabled'   => 'PASSWORD_RESET_ENABLED',
            'max_login_attempts'       => 'MAX_LOGIN_ATTEMPTS',
            'lockout_duration'         => 'LOCKOUT_DURATION',
        ];

        // Boolean fields — must be written as true/false strings in .env
        $booleanFields = ['registration_enabled', 'email_verification', 'password_reset_enabled'];

        foreach ($envMappings as $setting => $envVar) {
            if (!array_key_exists($setting, $settings)) {
                continue;
            }

            $value = $settings[$setting];

            if (in_array($setting, $booleanFields)) {
                // Cast to proper bool then to string so it writes 'true' or 'false'
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false';
            } elseif (is_string($value) && str_contains($value, ' ')) {
                // Quote strings that contain spaces
                $value = '"' . $value . '"';
            }

            $pattern = "/^{$envVar}=.*/m";
            $replacement = "{$envVar}={$value}";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }
        
        File::put($envPath, $envContent);
    }

    /**
     * Clear and rebuild the configuration cache (used by Optimize only).
     * WARNING: After config:cache, env() calls in application code return null.
     * Do NOT call this from update() — only call from optimize().
     */
    private function updateConfigCache(): void
    {
        Artisan::call('config:clear');
        Artisan::call('config:cache');
    }

    /**
     * Get environment variables (safe subset).
     */
    private function getEnvironmentVariables(): array
    {
        return [
            'APP_NAME'               => env('APP_NAME'),
            'APP_ENV'                => env('APP_ENV'),
            'APP_DEBUG'              => env('APP_DEBUG'),
            'APP_URL'                => env('APP_URL'),
            'APP_TIMEZONE'           => env('APP_TIMEZONE'),
            'CACHE_DRIVER'           => env('CACHE_DRIVER'),
            'SESSION_DRIVER'         => env('SESSION_DRIVER'),
            'QUEUE_CONNECTION'       => env('QUEUE_CONNECTION'),
            'MAIL_MAILER'            => env('MAIL_MAILER'),
            'MAIL_FROM_ADDRESS'      => env('MAIL_FROM_ADDRESS'),
            'MAIL_FROM_NAME'         => env('MAIL_FROM_NAME'),
            // File upload & security settings
            'FILE_MAX_SIZE'          => env('FILE_MAX_SIZE'),
            'ALLOWED_FILE_TYPES'     => env('ALLOWED_FILE_TYPES'),
            'REGISTRATION_ENABLED'   => env('REGISTRATION_ENABLED'),
            'EMAIL_VERIFICATION'     => env('EMAIL_VERIFICATION'),
            'PASSWORD_RESET_ENABLED' => env('PASSWORD_RESET_ENABLED'),
            'MAX_LOGIN_ATTEMPTS'     => env('MAX_LOGIN_ATTEMPTS'),
            'LOCKOUT_DURATION'       => env('LOCKOUT_DURATION'),
        ];
    }

    /**
     * Get database version.
     */
    private function getDatabaseVersion(): string
    {
        try {
            $pdo = \DB::connection()->getPdo();
            return $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Check if filename is a valid backup file.
     */
    private function isValidBackupFile(string $filename): bool
    {
        return preg_match('/^system_backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.json$/', $filename);
    }
}