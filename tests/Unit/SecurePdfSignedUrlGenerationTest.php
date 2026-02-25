<?php

namespace Tests\Unit;

use App\Models\Content;
use App\Services\SecurePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

/**
 * Test signed URL generation for PDF streaming.
 * 
 * Validates Requirements 7.1, 7.2, 7.3:
 * - Minimum expiration of 5 minutes (300 seconds)
 * - All necessary parameters included
 * - Correct route name used
 * - Absolute URLs generated
 */
class SecurePdfSignedUrlGenerationTest extends TestCase
{
    use RefreshDatabase;

    private SecurePdfService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SecurePdfService();
    }

    /** @test */
    public function it_generates_absolute_url()
    {
        $content = Content::factory()->create(['type' => 'pdf']);

        $url = $this->service->generateSecureUrl($content);

        // Verify URL is absolute (starts with http:// or https://)
        $this->assertMatchesRegularExpression('/^https?:\/\//', $url, 'URL should be absolute');
        $this->assertStringContainsString('://', $url, 'URL should contain protocol');
    }

    /** @test */
    public function it_enforces_minimum_expiration_of_5_minutes()
    {
        $content = Content::factory()->create(['type' => 'pdf']);

        // Try to generate URL with less than 5 minutes
        $url = $this->service->generateSecureUrl($content, 2);

        // Parse the URL to extract the expires parameter
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        $this->assertArrayHasKey('expires', $queryParams, 'URL should have expires parameter');

        $expiresAt = (int) $queryParams['expires'];
        $now = now()->timestamp;
        $expirationSeconds = $expiresAt - $now;

        // Should be at least 5 minutes (300 seconds), allowing for small timing differences
        $this->assertGreaterThanOrEqual(295, $expirationSeconds, 'Expiration should be at least 5 minutes');
    }

    /** @test */
    public function it_uses_default_expiration_of_at_least_5_minutes()
    {
        $content = Content::factory()->create(['type' => 'pdf']);

        // Generate URL with default expiration
        $url = $this->service->generateSecureUrl($content);

        // Parse the URL to extract the expires parameter
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        $expiresAt = (int) $queryParams['expires'];
        $now = now()->timestamp;
        $expirationSeconds = $expiresAt - $now;

        // Default is 10 minutes, should be at least 5 minutes
        $this->assertGreaterThanOrEqual(295, $expirationSeconds, 'Default expiration should be at least 5 minutes');
    }

    /** @test */
    public function it_includes_all_necessary_parameters()
    {
        $content = Content::factory()->create(['type' => 'pdf']);

        $url = $this->service->generateSecureUrl($content);

        // Parse the URL
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        // Verify required parameters are present
        $this->assertArrayHasKey('signature', $queryParams, 'URL should have signature parameter');
        $this->assertArrayHasKey('expires', $queryParams, 'URL should have expires parameter');
        $this->assertNotEmpty($queryParams['signature'], 'Signature should not be empty');
        $this->assertNotEmpty($queryParams['expires'], 'Expires should not be empty');
    }

    /** @test */
    public function it_uses_correct_route_name()
    {
        $content = Content::factory()->create(['type' => 'pdf']);

        $url = $this->service->generateSecureUrl($content);

        // Verify URL contains the correct route path
        $this->assertStringContainsString('/secure-pdf/stream/', $url, 'URL should use correct route path');
        $this->assertStringContainsString((string) $content->id, $url, 'URL should include content ID');
    }

    /** @test */
    public function it_generates_valid_signature()
    {
        $content = Content::factory()->create(['type' => 'pdf']);

        $url = $this->service->generateSecureUrl($content);

        // Verify the signature is valid by checking if Laravel can validate it
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        // Laravel's signed URL validation
        $this->assertTrue(
            URL::hasValidSignature(request()->create($url)),
            'Generated URL should have a valid signature'
        );
    }

    /** @test */
    public function content_model_generates_absolute_url_with_minimum_expiration()
    {
        $content = Content::factory()->create(['type' => 'pdf']);

        // Test with less than 5 minutes
        $url = $content->getSecurePdfUrl(2);

        // Verify URL is absolute
        $this->assertMatchesRegularExpression('/^https?:\/\//', $url, 'URL should be absolute');

        // Parse and verify expiration
        $parsedUrl = parse_url($url);
        parse_str($parsedUrl['query'] ?? '', $queryParams);

        $expiresAt = (int) $queryParams['expires'];
        $now = now()->timestamp;
        $expirationSeconds = $expiresAt - $now;

        // Should be at least 5 minutes
        $this->assertGreaterThanOrEqual(295, $expirationSeconds, 'Content model should enforce minimum 5 minutes');
    }

    /** @test */
    public function content_model_returns_null_for_non_pdf_content()
    {
        $content = Content::factory()->create(['type' => 'image']);

        $url = $content->getSecurePdfUrl();

        $this->assertNull($url, 'Should return null for non-PDF content');
    }
}
