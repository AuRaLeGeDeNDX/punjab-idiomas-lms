<?php

namespace Tests\Unit;

use App\Models\Content;
use App\Services\SecurePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;
use Tests\Traits\PropertyTesting;

/**
 * Property-based tests for signed URL generation in PDF streaming.
 * 
 * **Feature: pdf-stream-403-fix, Property 13: Signed URL Generation Correctness**
 * **Validates: Requirements 7.1, 7.2, 7.3**
 * 
 * These tests verify that signed URLs are generated correctly across many different
 * inputs, ensuring:
 * - URLs have minimum expiration of 5 minutes (300 seconds)
 * - All required parameters (signature, expires) are present
 * - URLs are absolute and properly formatted
 * - Signatures are valid and can be verified by Laravel
 */
class SecurePdfSignedUrlGenerationPropertyTest extends TestCase
{
    use RefreshDatabase, PropertyTesting;

    private SecurePdfService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SecurePdfService();
    }

    /**
     * Property 13: Signed URL Generation Correctness
     * 
     * **Validates: Requirements 7.1, 7.2, 7.3**
     * 
     * For any PDF content and any requested expiration time, the generated signed URL
     * should:
     * 1. Be an absolute URL (include protocol and domain)
     * 2. Have an expiration time of at least 5 minutes (300 seconds)
     * 3. Include all necessary parameters (signature, expires)
     * 4. Use the correct route name and path
     * 5. Have a valid signature that Laravel can verify
     * 
     * This property must hold across all valid inputs to ensure PDF.js can reliably
     * load PDFs using the generated URLs.
     */
    public function test_property_signed_url_generation_correctness()
    {
        $this->propertyTest(function () {
            // Generate random test scenarios
            $scenarios = $this->generateSignedUrlScenarios();
            
            foreach ($scenarios as $scenario) {
                // Create content with random data
                $content = Content::factory()->create([
                    'type' => 'pdf',
                    'title' => $scenario['title'],
                    'file_path' => $scenario['file_path'],
                    'file_size' => $scenario['file_size'],
                ]);
                
                // Generate signed URL with requested expiration
                $url = $this->service->generateSecureUrl($content, $scenario['requested_expiration']);
                
                // Verify all correctness properties
                $this->assertSignedUrlCorrectness($url, $content, $scenario);
                
                // Clean up
                $content->delete();
            }
        }, 100, 'Signed URL Generation Correctness');
    }

    /**
     * Generate random scenarios for signed URL testing.
     * 
     * Creates diverse test cases with:
     * - Various expiration times (including edge cases)
     * - Different content titles and file paths
     * - Random file sizes
     * 
     * @return array Array of test scenarios
     */
    private function generateSignedUrlScenarios(): array
    {
        $scenarios = [];
        
        // Generate 10 random scenarios per property test iteration
        for ($i = 0; $i < 10; $i++) {
            $scenarios[] = [
                'title' => $this->generateRandomTitle(),
                'file_path' => $this->generateRandomFilePath(),
                'file_size' => rand(1024, 10 * 1024 * 1024), // 1KB to 10MB
                'requested_expiration' => $this->generateRandomExpiration(),
            ];
        }
        
        return $scenarios;
    }

    /**
     * Generate a random PDF title.
     * 
     * @return string Random title
     */
    private function generateRandomTitle(): string
    {
        $prefixes = ['Lecture', 'Assignment', 'Reading', 'Tutorial', 'Guide', 'Manual', 'Document'];
        $subjects = ['Mathematics', 'Physics', 'Chemistry', 'Biology', 'History', 'Literature'];
        $numbers = rand(1, 100);
        
        return $prefixes[array_rand($prefixes)] . ' ' . 
               $subjects[array_rand($subjects)] . ' ' . 
               $numbers;
    }

    /**
     * Generate a random file path.
     * 
     * @return string Random file path
     */
    private function generateRandomFilePath(): string
    {
        $directories = ['pdfs', 'documents', 'content', 'files', 'uploads'];
        $filename = 'file_' . rand(1000, 9999) . '_' . time();
        
        return $directories[array_rand($directories)] . '/' . $filename . '.pdf';
    }

    /**
     * Generate a random expiration time.
     * 
     * Includes edge cases:
     * - Very short times (should be enforced to minimum 5 minutes)
     * - Exactly 5 minutes (boundary case)
     * - Normal times (5-60 minutes)
     * - Long times (60+ minutes)
     * 
     * @return int Expiration time in minutes
     */
    private function generateRandomExpiration(): int
    {
        $expirations = [
            // Edge cases - below minimum
            1, 2, 3, 4,
            // Boundary case - exactly minimum
            5,
            // Normal cases
            10, 15, 20, 30, 45, 60,
            // Long cases
            90, 120, 180,
        ];
        
        return $expirations[array_rand($expirations)];
    }

    /**
     * Assert that a signed URL satisfies all correctness properties.
     * 
     * Validates Requirements 7.1, 7.2, 7.3:
     * - Minimum expiration of 5 minutes
     * - All necessary parameters present
     * - Correct route name and absolute URL
     * - Valid signature
     * 
     * @param string $url Generated signed URL
     * @param Content $content Content model
     * @param array $scenario Test scenario
     */
    private function assertSignedUrlCorrectness(string $url, Content $content, array $scenario): void
    {
        // Property 1: URL must be absolute (Requirement 7.3)
        $this->assertMatchesRegularExpression(
            '/^https?:\/\//',
            $url,
            "URL must be absolute (start with http:// or https://). Got: {$url}"
        );
        
        $this->assertStringContainsString(
            '://',
            $url,
            "URL must contain protocol separator. Got: {$url}"
        );
        
        // Parse URL for detailed validation
        $parsedUrl = parse_url($url);
        $this->assertIsArray($parsedUrl, "URL must be parseable. Got: {$url}");
        $this->assertArrayHasKey('scheme', $parsedUrl, "URL must have a scheme. Got: {$url}");
        $this->assertArrayHasKey('host', $parsedUrl, "URL must have a host. Got: {$url}");
        $this->assertArrayHasKey('path', $parsedUrl, "URL must have a path. Got: {$url}");
        $this->assertArrayHasKey('query', $parsedUrl, "URL must have query parameters. Got: {$url}");
        
        // Property 2: URL must include all necessary parameters (Requirement 7.2)
        parse_str($parsedUrl['query'] ?? '', $queryParams);
        
        $this->assertArrayHasKey(
            'signature',
            $queryParams,
            "URL must have signature parameter. Got: {$url}"
        );
        
        $this->assertArrayHasKey(
            'expires',
            $queryParams,
            "URL must have expires parameter. Got: {$url}"
        );
        
        $this->assertNotEmpty(
            $queryParams['signature'],
            "Signature parameter must not be empty. Got: {$url}"
        );
        
        $this->assertNotEmpty(
            $queryParams['expires'],
            "Expires parameter must not be empty. Got: {$url}"
        );
        
        // Property 3: Expiration must be at least 5 minutes (Requirement 7.1)
        $expiresAt = (int) $queryParams['expires'];
        $now = now()->timestamp;
        $expirationSeconds = $expiresAt - $now;
        
        $this->assertGreaterThanOrEqual(
            295, // Allow 5 seconds tolerance for test execution time
            $expirationSeconds,
            "Expiration must be at least 5 minutes (300 seconds). " .
            "Requested: {$scenario['requested_expiration']} minutes, " .
            "Got: {$expirationSeconds} seconds. URL: {$url}"
        );
        
        // If requested expiration was less than 5 minutes, verify it was enforced to 5
        if ($scenario['requested_expiration'] < 5) {
            $this->assertLessThanOrEqual(
                310, // 5 minutes + 10 seconds tolerance
                $expirationSeconds,
                "When requesting less than 5 minutes, expiration should be enforced to exactly 5 minutes. " .
                "Requested: {$scenario['requested_expiration']} minutes, " .
                "Got: {$expirationSeconds} seconds. URL: {$url}"
            );
        }
        
        // Property 4: URL must use correct route path (Requirement 7.3)
        $this->assertStringContainsString(
            '/secure-pdf/stream/',
            $url,
            "URL must use correct route path '/secure-pdf/stream/'. Got: {$url}"
        );
        
        $this->assertStringContainsString(
            (string) $content->id,
            $url,
            "URL must include content ID. Expected: {$content->id}, Got: {$url}"
        );
        
        // Property 5: Signature must be valid (Requirement 7.2)
        // Create a request object to test signature validation
        $request = request()->create($url);
        
        $this->assertTrue(
            $request->hasValidSignature(),
            "Generated URL must have a valid signature that Laravel can verify. " .
            "URL: {$url}, " .
            "Signature: {$queryParams['signature']}, " .
            "Expires: {$queryParams['expires']}"
        );
        
        // Additional validation: Verify signature hasn't expired
        $this->assertGreaterThan(
            $now,
            $expiresAt,
            "Signature expiration time must be in the future. " .
            "Now: {$now}, Expires: {$expiresAt}, URL: {$url}"
        );
    }

    /**
     * Test that minimum expiration is enforced across all edge cases.
     * 
     * This focused test specifically validates Requirement 7.1 by testing
     * various expiration times below the minimum threshold.
     */
    public function test_property_minimum_expiration_enforcement()
    {
        $this->propertyTest(function () {
            // Test with various expiration times below minimum
            $belowMinimumExpirations = [0, 1, 2, 3, 4];
            
            foreach ($belowMinimumExpirations as $requestedMinutes) {
                $content = Content::factory()->create(['type' => 'pdf']);
                
                $url = $this->service->generateSecureUrl($content, $requestedMinutes);
                
                // Parse URL and extract expiration
                $parsedUrl = parse_url($url);
                parse_str($parsedUrl['query'] ?? '', $queryParams);
                
                $expiresAt = (int) $queryParams['expires'];
                $now = now()->timestamp;
                $actualSeconds = $expiresAt - $now;
                
                // Must be at least 5 minutes
                $this->assertGreaterThanOrEqual(
                    295,
                    $actualSeconds,
                    "Requesting {$requestedMinutes} minutes should be enforced to minimum 5 minutes. " .
                    "Got: {$actualSeconds} seconds"
                );
                
                // Should not be much more than 5 minutes (allowing 10 second tolerance)
                $this->assertLessThanOrEqual(
                    310,
                    $actualSeconds,
                    "Requesting {$requestedMinutes} minutes should result in exactly 5 minutes. " .
                    "Got: {$actualSeconds} seconds"
                );
                
                $content->delete();
            }
        }, 50, 'Minimum Expiration Enforcement');
    }

    /**
     * Test that all required parameters are present across random scenarios.
     * 
     * This focused test specifically validates Requirement 7.2 by ensuring
     * signature and expires parameters are always included.
     */
    public function test_property_required_parameters_present()
    {
        $this->propertyTest(function () {
            // Generate random content
            $content = Content::factory()->create([
                'type' => 'pdf',
                'title' => 'Test PDF ' . rand(1, 1000),
                'file_path' => 'test/file_' . rand(1000, 9999) . '.pdf',
            ]);
            
            // Generate URL with random expiration
            $expiration = rand(5, 120);
            $url = $this->service->generateSecureUrl($content, $expiration);
            
            // Parse URL
            $parsedUrl = parse_url($url);
            $this->assertIsArray($parsedUrl, "URL must be parseable");
            $this->assertArrayHasKey('query', $parsedUrl, "URL must have query string");
            
            parse_str($parsedUrl['query'], $queryParams);
            
            // Verify required parameters
            $this->assertArrayHasKey('signature', $queryParams, "Must have signature parameter");
            $this->assertArrayHasKey('expires', $queryParams, "Must have expires parameter");
            
            // Verify parameters are not empty
            $this->assertNotEmpty($queryParams['signature'], "Signature must not be empty");
            $this->assertNotEmpty($queryParams['expires'], "Expires must not be empty");
            
            // Verify signature is a valid hash (hexadecimal string)
            $this->assertMatchesRegularExpression(
                '/^[a-f0-9]+$/',
                $queryParams['signature'],
                "Signature must be a valid hexadecimal hash"
            );
            
            // Verify expires is a valid timestamp
            $this->assertIsNumeric($queryParams['expires'], "Expires must be numeric");
            $this->assertGreaterThan(0, (int) $queryParams['expires'], "Expires must be positive");
            
            $content->delete();
        }, 100, 'Required Parameters Present');
    }

    /**
     * Test that URLs are always absolute across different configurations.
     * 
     * This focused test specifically validates Requirement 7.3 by ensuring
     * URLs are always absolute regardless of the environment.
     */
    public function test_property_absolute_url_generation()
    {
        $this->propertyTest(function () {
            // Generate random content
            $content = Content::factory()->create([
                'type' => 'pdf',
                'title' => 'Test PDF ' . rand(1, 1000),
            ]);
            
            // Generate URL
            $url = $this->service->generateSecureUrl($content);
            
            // Must start with protocol
            $this->assertMatchesRegularExpression(
                '/^https?:\/\//',
                $url,
                "URL must start with http:// or https://"
            );
            
            // Must be parseable as absolute URL
            $parsedUrl = parse_url($url);
            $this->assertArrayHasKey('scheme', $parsedUrl, "Must have scheme (http/https)");
            $this->assertArrayHasKey('host', $parsedUrl, "Must have host/domain");
            $this->assertArrayHasKey('path', $parsedUrl, "Must have path");
            
            // Scheme must be http or https
            $this->assertContains(
                $parsedUrl['scheme'],
                ['http', 'https'],
                "Scheme must be http or https"
            );
            
            // Host must not be empty
            $this->assertNotEmpty($parsedUrl['host'], "Host must not be empty");
            
            // Path must include the route
            $this->assertStringContainsString(
                '/secure-pdf/stream/',
                $parsedUrl['path'],
                "Path must include route"
            );
            
            $content->delete();
        }, 100, 'Absolute URL Generation');
    }
}
