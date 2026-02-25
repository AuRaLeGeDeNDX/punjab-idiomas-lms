<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the landing page is served at root.
     */
    public function test_landing_page_is_served(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertViewIs('landing');
        $response->assertSee('Punjab Idiomas');
        $response->assertSee('Login');
        $response->assertDontSee('Dashboard'); // Should not see Dashboard when guest
    }

    /**
     * Test authenticated user sees dashboard link.
     */
    public function test_authenticated_user_redirects_to_dashboard(): void
    {
        // Note: The HomeController redirects authenticated users to dashboard immediately
        // So we won't see the landing page as an auth user, we'll get a redirect.
        // This confirms the logic in HomeController::index
        
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/');

        $response->assertRedirect(route('dashboard'));
    }
}
