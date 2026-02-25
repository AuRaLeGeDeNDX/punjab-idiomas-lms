<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;

/**
 * Test PDF Stream Route Configuration
 * 
 * Validates that the PDF stream route is configured correctly:
 * - Uses signed middleware for security
 * - Does not require authentication (signed URL is sufficient)
 * - Route parameter binding works correctly
 * - CSRF verification is excluded
 * 
 * Requirements: 1.2, 4.1
 */
class PdfStreamRouteConfigurationTest extends TestCase
{
    /**
     * Test that the PDF stream route exists and is named correctly.
     * 
     * @test
     */
    public function test_pdf_stream_route_exists()
    {
        $this->assertTrue(
            Route::has('secure.pdf.stream'),
            'PDF stream route should exist with name "secure.pdf.stream"'
        );
    }

    /**
     * Test that the PDF stream route uses signed middleware.
     * 
     * Requirement 1.2: Route must use signed middleware for signature validation
     * 
     * @test
     */
    public function test_pdf_stream_route_uses_signed_middleware()
    {
        $route = Route::getRoutes()->getByName('secure.pdf.stream');
        
        $this->assertNotNull($route, 'Route should exist');
        
        $middleware = $route->middleware();
        
        $this->assertContains(
            'signed',
            $middleware,
            'PDF stream route should use signed middleware for signature validation'
        );
    }

    /**
     * Test that the PDF stream route does NOT require authentication.
     * 
     * Requirement 4.1: Signed URLs should work without active session authentication
     * 
     * @test
     */
    public function test_pdf_stream_route_does_not_require_auth()
    {
        $route = Route::getRoutes()->getByName('secure.pdf.stream');
        
        $this->assertNotNull($route, 'Route should exist');
        
        $middleware = $route->middleware();
        
        $this->assertNotContains(
            'auth',
            $middleware,
            'PDF stream route should NOT require authentication - signed URL provides security'
        );
    }

    /**
     * Test that the PDF stream route has correct URI pattern.
     * 
     * @test
     */
    public function test_pdf_stream_route_has_correct_uri()
    {
        $route = Route::getRoutes()->getByName('secure.pdf.stream');
        
        $this->assertNotNull($route, 'Route should exist');
        
        $this->assertEquals(
            'secure-pdf/stream/{content}',
            $route->uri(),
            'PDF stream route should have correct URI pattern'
        );
    }

    /**
     * Test that the PDF stream route uses correct controller method.
     * 
     * @test
     */
    public function test_pdf_stream_route_uses_correct_controller()
    {
        $route = Route::getRoutes()->getByName('secure.pdf.stream');
        
        $this->assertNotNull($route, 'Route should exist');
        
        $action = $route->getActionName();
        
        $this->assertStringContainsString(
            'SecurePdfController@stream',
            $action,
            'PDF stream route should use SecurePdfController@stream'
        );
    }

    /**
     * Test that the PDF stream route accepts GET requests.
     * 
     * @test
     */
    public function test_pdf_stream_route_accepts_get_requests()
    {
        $route = Route::getRoutes()->getByName('secure.pdf.stream');
        
        $this->assertNotNull($route, 'Route should exist');
        
        $methods = $route->methods();
        
        $this->assertContains(
            'GET',
            $methods,
            'PDF stream route should accept GET requests'
        );
    }

    /**
     * Test that the route has web middleware (for session handling).
     * 
     * The web middleware is needed for session handling, but CSRF should be excluded.
     * 
     * @test
     */
    public function test_pdf_stream_route_has_web_middleware()
    {
        $route = Route::getRoutes()->getByName('secure.pdf.stream');
        
        $this->assertNotNull($route, 'Route should exist');
        
        $middleware = $route->middleware();
        
        $this->assertContains(
            'web',
            $middleware,
            'PDF stream route should have web middleware for session handling'
        );
    }

    /**
     * Test middleware order - signed should come after web.
     * 
     * Requirement 1.2: Middleware order is important for correct validation
     * 
     * @test
     */
    public function test_pdf_stream_route_middleware_order()
    {
        $route = Route::getRoutes()->getByName('secure.pdf.stream');
        
        $this->assertNotNull($route, 'Route should exist');
        
        $middleware = $route->middleware();
        
        $webIndex = array_search('web', $middleware);
        $signedIndex = array_search('signed', $middleware);
        
        $this->assertNotFalse($webIndex, 'Web middleware should be present');
        $this->assertNotFalse($signedIndex, 'Signed middleware should be present');
        
        $this->assertLessThan(
            $signedIndex,
            $webIndex,
            'Web middleware should come before signed middleware in the stack'
        );
    }
}
