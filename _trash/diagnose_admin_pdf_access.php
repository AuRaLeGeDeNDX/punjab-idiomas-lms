<?php
/**
 * Admin PDF Access Diagnostic Script
 * 
 * This script helps diagnose why admin users are seeing "PDF Not Loaded" errors.
 * Run this from the command line: php diagnose_admin_pdf_access.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Content;
use Illuminate\Support\Facades\Log;

echo "=== Admin PDF Access Diagnostic ===\n\n";

// Step 1: Check if we have admin users
echo "Step 1: Checking for admin users...\n";
$admins = User::role('Admin')->get();

if ($admins->isEmpty()) {
    echo "❌ ERROR: No admin users found in the system!\n";
    echo "   Please create an admin user first.\n\n";
    exit(1);
}

echo "✅ Found " . $admins->count() . " admin user(s):\n";
foreach ($admins as $admin) {
    echo "   - {$admin->name} (ID: {$admin->id}, Email: {$admin->email})\n";
}
echo "\n";

// Step 2: Check for PDF content
echo "Step 2: Checking for PDF content...\n";
$pdfContent = Content::where('type', 'pdf')->first();

if (!$pdfContent) {
    echo "❌ ERROR: No PDF content found in the system!\n";
    echo "   Please upload a PDF first.\n\n";
    exit(1);
}

echo "✅ Found PDF content:\n";
echo "   - Title: {$pdfContent->title}\n";
echo "   - ID: {$pdfContent->id}\n";
echo "   - File Path: {$pdfContent->file_path}\n";
echo "   - Storage Disk: {$pdfContent->storage_disk}\n";
echo "   - Is Active: " . ($pdfContent->is_active ? 'Yes' : 'No') . "\n";
echo "\n";

// Step 3: Check if PDF file exists
echo "Step 3: Checking if PDF file exists on disk...\n";
$storageDisk = $pdfContent->storage_disk ?? 'protected';
$disk = \Storage::disk($storageDisk);

if (!$disk->exists($pdfContent->file_path)) {
    echo "❌ ERROR: PDF file not found at: {$pdfContent->file_path}\n";
    echo "   Storage disk: {$storageDisk}\n";
    
    // Try other disks
    echo "   Checking other disks...\n";
    $disksToTry = ['protected', 'private', 'public'];
    $found = false;
    
    foreach ($disksToTry as $tryDisk) {
        if (\Storage::disk($tryDisk)->exists($pdfContent->file_path)) {
            echo "   ✅ Found file on '{$tryDisk}' disk!\n";
            echo "   Update the content record: UPDATE contents SET storage_disk = '{$tryDisk}' WHERE id = {$pdfContent->id};\n";
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        echo "   ❌ File not found on any disk!\n";
        echo "   Please re-upload the PDF.\n\n";
        exit(1);
    }
} else {
    echo "✅ PDF file exists on disk\n";
    $path = $disk->path($pdfContent->file_path);
    $fileSize = filesize($path);
    echo "   - Full path: {$path}\n";
    echo "   - File size: " . number_format($fileSize) . " bytes\n";
}
echo "\n";

// Step 4: Test admin role check
echo "Step 4: Testing admin role check...\n";
$testAdmin = $admins->first();
$hasAdminRole = $testAdmin->hasRole('Admin');

if ($hasAdminRole) {
    echo "✅ Admin role check working correctly\n";
    echo "   User '{$testAdmin->name}' has Admin role\n";
} else {
    echo "❌ ERROR: Admin role check failed!\n";
    echo "   User '{$testAdmin->name}' does not have Admin role\n";
    echo "   This should not happen - check Spatie permissions setup\n\n";
    exit(1);
}
echo "\n";

// Step 5: Check recent PDF access logs
echo "Step 5: Checking recent PDF access logs...\n";
$recentLogs = \DB::table('pdf_access_logs')
    ->where('content_id', $pdfContent->id)
    ->orderBy('accessed_at', 'desc')
    ->limit(5)
    ->get();

if ($recentLogs->isEmpty()) {
    echo "⚠️  No access logs found for this PDF\n";
} else {
    echo "✅ Found " . $recentLogs->count() . " recent access log(s):\n";
    foreach ($recentLogs as $log) {
        $user = User::find($log->user_id);
        $userName = $user ? $user->name : 'Unknown';
        $status = $log->access_granted ? '✅ Granted' : '❌ Denied';
        echo "   - {$status} | User: {$userName} | Token: {$log->session_token} | Reason: {$log->failure_reason}\n";
    }
}
echo "\n";

// Step 6: Check Laravel logs for errors
echo "Step 6: Checking Laravel logs for recent PDF errors...\n";
$logFile = storage_path('logs/laravel.log');

if (file_exists($logFile)) {
    $logContent = file_get_contents($logFile);
    $lines = explode("\n", $logContent);
    $recentErrors = [];
    
    // Get last 100 lines
    $lastLines = array_slice($lines, -100);
    
    foreach ($lastLines as $line) {
        if (stripos($line, 'SecurePDF') !== false || stripos($line, 'PDF Error') !== false) {
            $recentErrors[] = $line;
        }
    }
    
    if (empty($recentErrors)) {
        echo "✅ No recent PDF errors in logs\n";
    } else {
        echo "⚠️  Found " . count($recentErrors) . " recent PDF-related log entries:\n";
        foreach (array_slice($recentErrors, -5) as $error) {
            echo "   " . substr($error, 0, 150) . "...\n";
        }
    }
} else {
    echo "⚠️  Log file not found: {$logFile}\n";
}
echo "\n";

// Step 7: Generate test URL
echo "Step 7: Generating test viewer URL...\n";
try {
    $pdfService = app(\App\Services\SecurePdfService::class);
    $viewerUrl = $pdfService->generateViewerUrl($pdfContent, $testAdmin);
    
    echo "✅ Test URL generated successfully:\n";
    echo "   {$viewerUrl}\n";
    echo "\n";
    echo "   Try accessing this URL while logged in as: {$testAdmin->email}\n";
} catch (\Exception $e) {
    echo "❌ ERROR generating test URL: {$e->getMessage()}\n";
    echo "   Stack trace:\n";
    echo "   " . $e->getTraceAsString() . "\n";
}
echo "\n";

// Step 8: Check if admin bypass is in place
echo "Step 8: Checking if admin bypass code is in place...\n";
$controllerFile = app_path('Http/Controllers/SecurePdfController.php');
$controllerContent = file_get_contents($controllerFile);

if (strpos($controllerContent, 'ADMIN BYPASS') !== false) {
    echo "✅ Admin bypass code found in SecurePdfController\n";
} else {
    echo "❌ ERROR: Admin bypass code NOT found in SecurePdfController!\n";
    echo "   The fix may not have been applied correctly.\n";
    echo "   Please check: {$controllerFile}\n";
}
echo "\n";

// Summary
echo "=== Diagnostic Summary ===\n";
echo "1. Admin users: " . ($admins->count() > 0 ? '✅' : '❌') . "\n";
echo "2. PDF content: " . ($pdfContent ? '✅' : '❌') . "\n";
echo "3. PDF file exists: " . ($disk->exists($pdfContent->file_path) ? '✅' : '❌') . "\n";
echo "4. Admin role check: " . ($hasAdminRole ? '✅' : '❌') . "\n";
echo "5. Admin bypass code: " . (strpos($controllerContent, 'ADMIN BYPASS') !== false ? '✅' : '❌') . "\n";
echo "\n";

echo "=== Next Steps ===\n";
echo "1. Make sure you're logged in as an admin user\n";
echo "2. Clear your browser cache (Ctrl+Shift+Delete)\n";
echo "3. Clear Laravel cache: php artisan cache:clear\n";
echo "4. Try accessing the test URL above\n";
echo "5. Check browser console (F12) for JavaScript errors\n";
echo "6. Check Network tab (F12) for failed requests\n";
echo "\n";

echo "If the problem persists, check:\n";
echo "- Browser console for JavaScript errors\n";
echo "- Network tab for 403/404 errors\n";
echo "- Laravel logs: tail -f storage/logs/laravel.log\n";
echo "- PDF access logs in database\n";
echo "\n";
