<?php

namespace Tests\Unit;

use App\Models\Content;
use App\Models\User;
use App\Services\SecurePdfService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurePdfServiceTokenTest extends TestCase
{
    use RefreshDatabase;

    private SecurePdfService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SecurePdfService();
    }

    /** @test */
    public function it_generates_viewer_url_with_token()
    {
        $user = User::factory()->create();
        $content = Content::factory()->create(['type' => 'pdf']);

        $url = $this->service->generateViewerUrl($content, $user);

        // Verify URL format: /secure-pdf/viewer/{content_id}/{token}
        $this->assertStringContainsString('/secure-pdf/viewer/', $url);
        $this->assertStringContainsString((string)$content->id, $url);
    }

    /** @test */
    public function it_generates_token_with_required_structure()
    {
        $user = User::factory()->create();
        $content = Content::factory()->create(['type' => 'pdf']);

        // Use reflection to access private method for testing
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateSessionToken');
        $method->setAccessible(true);

        $token = $method->invoke($this->service, $content, $user);

        // Token should be base64 encoded
        $this->assertNotEmpty($token);
        $decoded = base64_decode($token, true);
        $this->assertNotFalse($decoded, 'Token should be valid base64');

        // Decoded token should have payload.signature structure
        $parts = explode('.', $decoded);
        $this->assertCount(2, $parts, 'Token should have payload and signature');

        [$payloadJson, $signature] = $parts;

        // Payload should be valid JSON
        $payload = json_decode($payloadJson, true);
        $this->assertIsArray($payload);

        // Payload should contain required fields
        $this->assertArrayHasKey('user_id', $payload);
        $this->assertArrayHasKey('content_id', $payload);
        $this->assertArrayHasKey('expires_at', $payload);

        // Verify field values
        $this->assertEquals($user->id, $payload['user_id']);
        $this->assertEquals($content->id, $payload['content_id']);
        $this->assertGreaterThan(now()->timestamp, $payload['expires_at']);
    }

    /** @test */
    public function it_generates_token_with_valid_hmac_signature()
    {
        $user = User::factory()->create();
        $content = Content::factory()->create(['type' => 'pdf']);

        // Use reflection to access private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateSessionToken');
        $method->setAccessible(true);

        $token = $method->invoke($this->service, $content, $user);
        $decoded = base64_decode($token, true);
        [$payloadJson, $signature] = explode('.', $decoded);

        // Verify signature is HMAC-SHA256 of payload
        $expectedSignature = hash_hmac('sha256', $payloadJson, config('app.key'), false);
        $this->assertEquals($expectedSignature, $signature);
    }

    /** @test */
    public function it_uses_configured_expiration_time()
    {
        config(['secure-pdf.token_expiration_minutes' => 30]);

        $user = User::factory()->create();
        $content = Content::factory()->create(['type' => 'pdf']);

        // Use reflection to access private method
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateSessionToken');
        $method->setAccessible(true);

        $token = $method->invoke($this->service, $content, $user);
        $decoded = base64_decode($token, true);
        [$payloadJson] = explode('.', $decoded);
        $payload = json_decode($payloadJson, true);

        // Verify expiration is approximately 30 minutes from now
        $expectedExpiration = now()->addMinutes(30)->timestamp;
        $this->assertEqualsWithDelta($expectedExpiration, $payload['expires_at'], 5);
    }

    /** @test */
    public function it_validates_valid_token()
    {
        $user = User::factory()->create();
        $content = Content::factory()->create(['type' => 'pdf']);

        // Generate token
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateSessionToken');
        $method->setAccessible(true);
        $token = $method->invoke($this->service, $content, $user);

        // Validate token
        $result = $this->service->validateSessionToken($token);

        $this->assertIsArray($result);
        $this->assertEquals($user->id, $result['user_id']);
        $this->assertEquals($content->id, $result['content_id']);
        $this->assertArrayHasKey('expires_at', $result);
    }

    /** @test */
    public function it_validates_token_with_content_id_verification()
    {
        $user = User::factory()->create();
        $content = Content::factory()->create(['type' => 'pdf']);

        // Generate token
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateSessionToken');
        $method->setAccessible(true);
        $token = $method->invoke($this->service, $content, $user);

        // Validate with correct content_id
        $result = $this->service->validateSessionToken($token, $content->id);
        $this->assertIsArray($result);

        // Validate with wrong content_id
        $result = $this->service->validateSessionToken($token, 999);
        $this->assertNull($result);
    }

    /** @test */
    public function it_validates_token_with_user_id_verification()
    {
        $user = User::factory()->create();
        $content = Content::factory()->create(['type' => 'pdf']);

        // Generate token
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('generateSessionToken');
        $method->setAccessible(true);
        $token = $method->invoke($this->service, $content, $user);

        // Validate with correct user_id
        $result = $this->service->validateSessionToken($token, null, $user->id);
        $this->assertIsArray($result);

        // Validate with wrong user_id
        $result = $this->service->validateSessionToken($token, null, 999);
        $this->assertNull($result);
    }

    /** @test */
    public function it_rejects_expired_token()
    {
        $user = User::factory()->create();
        $content = Content::factory()->create(['type' => 'pdf']);

        // Create expired token manually
        $payload = [
            'user_id' => $user->id,
            'content_id' => $content->id,
            'expires_at' => now()->subMinutes(10)->timestamp, // Expired 10 minutes ago
        ];

        $payloadJson = json_encode($payload);
        $signature = hash_hmac('sha256', $payloadJson, config('app.key'), false);
        $tokenData = $payloadJson . '.' . $signature;
        $token = base64_encode($tokenData);

        // Validate expired token
        $result = $this->service->validateSessionToken($token);
        $this->assertNull($result);
    }

    /** @test */
    public function it_rejects_token_with_invalid_signature()
    {
        $user = User::factory()->create();
        $content = Content::factory()->create(['type' => 'pdf']);

        // Create token with invalid signature
        $payload = [
            'user_id' => $user->id,
            'content_id' => $content->id,
            'expires_at' => now()->addMinutes(60)->timestamp,
        ];

        $payloadJson = json_encode($payload);
        $invalidSignature = 'invalid_signature_here';
        $tokenData = $payloadJson . '.' . $invalidSignature;
        $token = base64_encode($tokenData);

        // Validate token with invalid signature
        $result = $this->service->validateSessionToken($token);
        $this->assertNull($result);
    }

    /** @test */
    public function it_rejects_malformed_token()
    {
        // Test various malformed tokens
        $malformedTokens = [
            'not_base64_at_all',
            base64_encode('no_dot_separator'),
            base64_encode('too.many.dots.here'),
            base64_encode('invalid_json.signature'),
            '',
        ];

        foreach ($malformedTokens as $token) {
            $result = $this->service->validateSessionToken($token);
            $this->assertNull($result, "Malformed token should be rejected: {$token}");
        }
    }

    /** @test */
    public function it_rejects_token_with_missing_fields()
    {
        // Create token with missing fields
        $payload = [
            'user_id' => 1,
            // Missing content_id and expires_at
        ];

        $payloadJson = json_encode($payload);
        $signature = hash_hmac('sha256', $payloadJson, config('app.key'), false);
        $tokenData = $payloadJson . '.' . $signature;
        $token = base64_encode($tokenData);

        // Validate token with missing fields
        $result = $this->service->validateSessionToken($token);
        $this->assertNull($result);
    }
}
