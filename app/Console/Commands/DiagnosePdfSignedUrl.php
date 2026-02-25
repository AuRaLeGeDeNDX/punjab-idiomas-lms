<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Content;
use App\Services\SecurePdfService;

/**
 * Diagnostic Command for PDF Signed URL Generation and Validation
 * 
 * This command tests the signed URL generation and validation process
 * to identify issues causing 403 Forbidden errors in PDF streaming.
 * 
 * Requirements tested: 1.1, 1.2, 7.1, 7.2, 7.3
 * 
 * Usage: php artisan pdf:diagnose-signed-url [content_id]
 */
class DiagnosePdfSignedUrl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pdf:diagnose-signed-url {content_id? : The ID of the PDF content to test}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose PDF signed URL generation and validation issues';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('PDF Signed URL Diagnostic Tool');
        $this->info(str_repeat('=', 80));
        $this->newLine();

        // Step 1: Find or create test content
        $this->info('Step 1: Finding Test Content');
        $this->info(str_repeat('-', 80));

        $contentId = $this->argument('content_id');
        
        if ($contentId) {
            $content = Content::find($contentId);
            if (!$content) {
                $this->error("Content with ID {$contentId} not found");
                return 1;
            }
            $this->info("✓ Using specified content ID: {$contentId}");
        } else {
            $content = Content::where('type', 'pdf')->first();
            if (!$content) {
                $this->error("✗ No PDF content found in database");
                $this->warn("Please create a PDF content entry or specify a content ID");
                return 1;
            }
            $this->info("✓ Using first PDF content found: ID {$content->id}");
        }

        $this->table(
            ['Property', 'Value'],
            [
                ['ID', $content->id],
                ['Title', $content->title],
                ['Type', $content->type],
                ['File Path', $content->file_path],
                ['Storage Disk', $content->storage_disk ?? 'protected'],
            ]
        );
        $this->newLine();

        // Step 2: Test signed URL generation
        $this->info('Step 2: Testing Signed URL Generation');
        $this->info(str_repeat('-', 80));

        $pdfService = new SecurePdfService();

        try {
            $signedUrl = $pdfService->generateSecureUrl($content, 5);
            $this->info("✓ Signed URL generated successfully");
            $this->newLine();
            
            $this->line("Generated URL:");
            $this->line("  {$signedUrl}");
            $this->newLine();
            
            // Parse URL components
            $parsedUrl = parse_url($signedUrl);
            parse_str($parsedUrl['query'] ?? '', $queryParams);
            
            $this->table(
                ['Component', 'Value'],
                [
                    ['Scheme', $parsedUrl['scheme'] ?? 'N/A'],
                    ['Host', $parsedUrl['host'] ?? 'N/A'],
                    ['Path', $parsedUrl['path'] ?? 'N/A'],
                ]
            );
            
            $paramTable = [];
            foreach ($queryParams as $key => $value) {
                if ($key === 'signature') {
                    $paramTable[] = [$key, substr($value, 0, 30) . '... (truncated)'];
                } else {
                    $paramTable[] = [$key, $value];
                }
            }
            
            $this->table(['Parameter', 'Value'], $paramTable);
            $this->newLine();
            
            // Validate URL structure
            $this->info('URL Validation:');
            
            $validations = [];
            
            if (isset($parsedUrl['scheme']) && in_array($parsedUrl['scheme'], ['http', 'https'])) {
                $validations[] = ['✓', 'Valid scheme', $parsedUrl['scheme']];
            } else {
                $validations[] = ['✗', 'Invalid or missing scheme', 'FAIL'];
            }
            
            if (isset($parsedUrl['host'])) {
                $validations[] = ['✓', 'Has host (absolute URL)', $parsedUrl['host']];
            } else {
                $validations[] = ['✗', 'Missing host (not absolute)', 'FAIL'];
            }
            
            if (isset($queryParams['signature'])) {
                $validations[] = ['✓', 'Has signature parameter', 'OK'];
            } else {
                $validations[] = ['✗', 'Missing signature parameter', 'FAIL'];
            }
            
            if (isset($queryParams['expires'])) {
                $expiresAt = (int)$queryParams['expires'];
                $now = time();
                $timeUntilExpiry = $expiresAt - $now;
                $minutes = round($timeUntilExpiry / 60, 2);
                
                $validations[] = ['✓', 'Has expires parameter', date('Y-m-d H:i:s', $expiresAt)];
                $validations[] = ['ℹ', 'Time until expiry', "{$timeUntilExpiry}s ({$minutes} min)"];
                
                if ($timeUntilExpiry >= 300) {
                    $validations[] = ['✓', 'Expiration >= 5 minutes', 'PASS (Req 7.1)'];
                } else {
                    $validations[] = ['⚠', 'Expiration < 5 minutes', 'WARNING'];
                }
            } else {
                $validations[] = ['✗', 'Missing expires parameter', 'FAIL'];
            }
            
            $this->table(['Status', 'Check', 'Result'], $validations);
            $this->newLine();
            
        } catch (\Exception $e) {
            $this->error("✗ Failed to generate signed URL: " . $e->getMessage());
            $this->line("\nStack trace:");
            $this->line($e->getTraceAsString());
            return 1;
        }

        // Step 3: Test signature validation
        $this->info('Step 3: Testing Signature Validation');
        $this->info(str_repeat('-', 80));

        try {
            // Create a mock request with the signed URL
            $request = Request::create($signedUrl, 'GET');
            
            $this->line("Testing signature validation with Laravel's hasValidSignature()");
            $this->newLine();
            
            if ($request->hasValidSignature()) {
                $this->info("✓ Signature validation PASSED");
                $this->info("  The signed URL is valid and should work");
            } else {
                $this->error("✗ Signature validation FAILED");
                $this->warn("  This is likely the cause of 403 errors");
                $this->newLine();
                
                // Try to diagnose why validation failed
                $this->line("Diagnosing validation failure:");
                
                $diagnostics = [];
                
                if (!$request->query('signature')) {
                    $diagnostics[] = ['✗', 'Signature parameter missing', 'FAIL'];
                } else {
                    $diagnostics[] = ['✓', 'Signature parameter exists', 'OK'];
                }
                
                if (!$request->query('expires')) {
                    $diagnostics[] = ['✗', 'Expires parameter missing', 'FAIL'];
                } else {
                    $expires = (int)$request->query('expires');
                    $now = time();
                    
                    if ($expires < $now) {
                        $diagnostics[] = ['✗', 'URL has expired', 'FAIL'];
                    } else {
                        $diagnostics[] = ['✓', 'Expires parameter valid', 'OK'];
                    }
                }
                
                // Check URL encoding
                $originalUrl = $signedUrl;
                $decodedUrl = urldecode($signedUrl);
                
                if ($originalUrl !== $decodedUrl) {
                    $diagnostics[] = ['⚠', 'URL appears to be encoded', 'WARNING'];
                } else {
                    $diagnostics[] = ['✓', 'URL encoding correct', 'OK'];
                }
                
                $this->table(['Status', 'Check', 'Result'], $diagnostics);
            }
            
            $this->newLine();
            
        } catch (\Exception $e) {
            $this->error("✗ Failed to validate signature: " . $e->getMessage());
            $this->line("\nStack trace:");
            $this->line($e->getTraceAsString());
        }

        // Step 4: Simulate PDF.js fetch request
        $this->info('Step 4: Simulating PDF.js Fetch Request');
        $this->info(str_repeat('-', 80));

        $this->line("Simulating how PDF.js would fetch the PDF...");
        $this->newLine();

        try {
            // Parse the URL to simulate what PDF.js does
            $parsedUrl = parse_url($signedUrl);
            $path = $parsedUrl['path'];
            $query = $parsedUrl['query'] ?? '';
            
            // Create a new request as PDF.js would
            $pdfJsRequest = Request::create($path . '?' . $query, 'GET');
            
            $this->line("PDF.js would make a request to:");
            $this->line("  Path: {$path}");
            $this->line("  Query: {$query}");
            $this->newLine();
            
            // Test if this request would validate
            if ($pdfJsRequest->hasValidSignature()) {
                $this->info("✓ PDF.js request signature validation PASSED");
                $this->info("  PDF.js should be able to load the PDF successfully");
            } else {
                $this->error("✗ PDF.js request signature validation FAILED");
                $this->warn("  This explains why PDF.js gets 403 errors");
                $this->newLine();
                
                // Compare original request vs PDF.js request
                $this->line("Comparing original request vs PDF.js request:");
                
                $request = Request::create($signedUrl, 'GET');
                $originalQuery = $request->query();
                $pdfJsQuery = $pdfJsRequest->query();
                
                $comparison = [];
                foreach ($originalQuery as $key => $value) {
                    if (!isset($pdfJsQuery[$key])) {
                        $comparison[] = ['✗', $key, 'Missing in PDF.js request'];
                    } elseif ($pdfJsQuery[$key] !== $value) {
                        $comparison[] = ['⚠', $key, 'Value differs'];
                    } else {
                        $comparison[] = ['✓', $key, 'Matches'];
                    }
                }
                
                $this->table(['Status', 'Parameter', 'Result'], $comparison);
            }
            
            $this->newLine();
            
        } catch (\Exception $e) {
            $this->error("✗ Failed to simulate PDF.js request: " . $e->getMessage());
        }

        // Step 5: Test route configuration
        $this->info('Step 5: Testing Route Configuration');
        $this->info(str_repeat('-', 80));

        try {
            $routeName = 'secure.pdf.stream';
            $route = app('router')->getRoutes()->getByName($routeName);
            
            if ($route) {
                $this->info("✓ Route '{$routeName}' exists");
                $this->newLine();
                
                $middleware = $route->middleware();
                
                $this->table(
                    ['Property', 'Value'],
                    [
                        ['URI', $route->uri()],
                        ['Methods', implode(', ', $route->methods())],
                        ['Action', $route->getActionName()],
                        ['Middleware', empty($middleware) ? 'None' : implode(', ', $middleware)],
                    ]
                );
                
                $this->newLine();
                
                // Check if signed middleware is applied
                if (in_array('signed', $middleware)) {
                    $this->info("✓ 'signed' middleware is applied");
                } else {
                    $this->error("✗ 'signed' middleware is NOT applied");
                    $this->warn("  This will cause validation to fail");
                }
                
                // Check for conflicting middleware
                $conflictingMiddleware = ['auth', 'csrf'];
                foreach ($conflictingMiddleware as $mw) {
                    if (in_array($mw, $middleware)) {
                        $this->warn("⚠ '{$mw}' middleware is applied (may cause issues)");
                    }
                }
                
            } else {
                $this->error("✗ Route '{$routeName}' not found");
                $this->newLine();
                
                $this->line("Available routes with 'pdf' in name:");
                foreach (app('router')->getRoutes() as $route) {
                    if (stripos($route->getName(), 'pdf') !== false) {
                        $this->line("  - " . $route->getName() . " => " . $route->uri());
                    }
                }
            }
            
            $this->newLine();
            
        } catch (\Exception $e) {
            $this->error("✗ Failed to check route configuration: " . $e->getMessage());
        }

        // Step 6: Test file access
        $this->info('Step 6: Testing File Access');
        $this->info(str_repeat('-', 80));

        try {
            $storageDisk = $content->storage_disk ?? 'protected';
            $disk = Storage::disk($storageDisk);
            
            if ($disk->exists($content->file_path)) {
                $this->info("✓ File exists on disk '{$storageDisk}'");
                $this->newLine();
                
                $fileSize = $disk->size($content->file_path);
                $lastModified = $disk->lastModified($content->file_path);
                
                $this->table(
                    ['Property', 'Value'],
                    [
                        ['Size', number_format($fileSize) . ' bytes (' . round($fileSize / 1024 / 1024, 2) . ' MB)'],
                        ['Last Modified', date('Y-m-d H:i:s', $lastModified)],
                        ['Full Path', $disk->path($content->file_path)],
                    ]
                );
                
                // Check if file is readable
                $fullPath = $disk->path($content->file_path);
                if (is_readable($fullPath)) {
                    $this->info("✓ File is readable");
                } else {
                    $this->error("✗ File exists but is not readable (permission issue)");
                }
                
            } else {
                $this->error("✗ File does not exist on disk '{$storageDisk}'");
                $this->newLine();
                
                // Try other disks
                $this->line("Searching other storage disks...");
                $disksToTry = ['protected', 'private', 'public', 'local'];
                
                foreach ($disksToTry as $tryDisk) {
                    if ($tryDisk === $storageDisk) continue;
                    
                    try {
                        $testDisk = Storage::disk($tryDisk);
                        if ($testDisk->exists($content->file_path)) {
                            $this->info("  ✓ Found on disk '{$tryDisk}'");
                            $this->warn("  Content record has wrong storage_disk value");
                        }
                    } catch (\Exception $e) {
                        // Disk doesn't exist or error accessing it
                    }
                }
            }
            
            $this->newLine();
            
        } catch (\Exception $e) {
            $this->error("✗ Failed to check file access: " . $e->getMessage());
        }

        // Step 7: Summary and recommendations
        $this->info('Step 7: Summary and Recommendations');
        $this->info(str_repeat('=', 80));
        $this->newLine();

        // Collect issues found
        $issues = [];
        $warnings = [];

        // Check URL generation
        $parsedUrl = parse_url($signedUrl);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        if (!isset($parsedUrl['host'])) {
            $issues[] = "Signed URL is not absolute (missing host)";
        }

        if (!isset($queryParams['signature'])) {
            $issues[] = "Signed URL is missing signature parameter";
        }

        if (!isset($queryParams['expires'])) {
            $issues[] = "Signed URL is missing expires parameter";
        } else {
            $expiresAt = (int)$queryParams['expires'];
            $timeUntilExpiry = $expiresAt - time();
            if ($timeUntilExpiry < 300) {
                $warnings[] = "Expiration time is less than 5 minutes";
            }
        }

        // Check signature validation
        $request = Request::create($signedUrl, 'GET');
        if (!$request->hasValidSignature()) {
            $issues[] = "Signature validation fails for generated URL";
        }

        // Check route configuration
        $route = app('router')->getRoutes()->getByName('secure.pdf.stream');
        if (!$route) {
            $issues[] = "Route 'secure.pdf.stream' not found";
        } else {
            $middleware = $route->middleware();
            if (!in_array('signed', $middleware)) {
                $issues[] = "Route is missing 'signed' middleware";
            }
        }

        // Display results
        if (empty($issues) && empty($warnings)) {
            $this->info("✓ No issues found! The signed URL system appears to be working correctly.");
            $this->newLine();
            $this->line("If you're still experiencing 403 errors, check:");
            $this->line("  - Browser console for JavaScript errors");
            $this->line("  - Network tab for actual request/response details");
            $this->line("  - Laravel logs for detailed error messages");
        } else {
            if (!empty($issues)) {
                $this->error("Critical Issues Found:");
                foreach ($issues as $issue) {
                    $this->line("  • {$issue}");
                }
                $this->newLine();
            }
            
            if (!empty($warnings)) {
                $this->warn("Warnings:");
                foreach ($warnings as $warning) {
                    $this->line("  • {$warning}");
                }
                $this->newLine();
            }
            
            $this->info("Recommendations:");
            
            if (!isset($parsedUrl['host'])) {
                $this->line("  1. Ensure APP_URL is set correctly in .env file");
                $this->line("  2. Use URL::temporarySignedRoute() instead of route() helper");
                $this->line("  3. Force absolute URLs in config/app.php");
            }
            
            if (!$request->hasValidSignature()) {
                $this->line("  1. Check that APP_KEY is set and consistent");
                $this->line("  2. Verify URL encoding is not breaking the signature");
                $this->line("  3. Ensure the URL is not being modified before validation");
            }
            
            if ($route && !in_array('signed', $route->middleware())) {
                $this->line("  1. Add 'signed' middleware to the route in routes/web.php");
                $this->line("  2. Ensure middleware is applied correctly");
            }
        }

        $this->newLine();
        $this->info('Diagnostic Complete');
        $this->info(str_repeat('=', 80));
        $this->newLine();

        $this->line("For more information, check:");
        $this->line("  - Laravel logs: storage/logs/laravel.log");
        $this->line("  - Requirements: .kiro/specs/pdf-stream-403-fix/requirements.md");
        $this->line("  - Design: .kiro/specs/pdf-stream-403-fix/design.md");
        $this->newLine();

        return 0;
    }
}
