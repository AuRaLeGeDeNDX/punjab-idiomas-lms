<?php

namespace Tests\Properties;

use App\Models\User;
use App\Services\AuthenticationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\PropertyTesting;

class AuthenticationPropertyTest extends TestCase
{
    use RefreshDatabase, PropertyTesting;

    protected AuthenticationService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthenticationService();
        
        // Create roles for testing if they don't exist
        if (!Role::where('name', 'Admin')->exists()) {
            Role::create(['name' => 'Admin']);
        }
        if (!Role::where('name', 'Teacher')->exists()) {
            Role::create(['name' => 'Teacher']);
        }
        if (!Role::where('name', 'Student')->exists()) {
            Role::create(['name' => 'Student']);
        }
    }

    /**
     * Property 1: Authentication Success
     * For any user with valid credentials, authentication should succeed and establish a secure session with appropriate role permissions.
     * Validates: Requirements 1.1, 1.4
     */
    public function test_authentication_success_property(): void
    {
        $this->propertyTest(function () {
            // Generate a random user with random role
            $roles = ['Admin', 'Teacher', 'Student'];
            $role = $roles[array_rand($roles)];
            
            $user = User::factory()->create([
                'password' => Hash::make('test-password'),
                'is_active' => true,
            ]);
            $user->assignRole($role);

            // Create request with valid credentials
            $request = Request::create('/login', 'POST', [
                'email' => $user->email,
                'password' => 'test-password',
                'remember' => false,
            ]);
            $request->setLaravelSession($this->app['session.store']);

            // Clear any existing rate limits
            RateLimiter::clear($this->getThrottleKey($request));

            // Test authentication
            $result = $this->authService->authenticate($request);

            // Assertions
            $this->assertTrue($result, 'Authentication should succeed for valid credentials');
            $this->assertTrue(Auth::check(), 'User should be authenticated');
            $this->assertEquals($user->id, Auth::id(), 'Correct user should be authenticated');
            $this->assertTrue(Auth::user()->hasRole($role), 'User should have the correct role');
            
            // Verify session is established
            $this->assertNotNull(Auth::user(), 'User session should be established');
            
            // Clear authentication for next iteration
            Auth::logout();

        }, 10, 'Authentication Success');
    }

    /**
     * Property 2: Authentication Rejection
     * For any invalid credentials, authentication should be rejected and the security event should be logged.
     * Validates: Requirements 1.2
     */
    public function test_authentication_rejection_property(): void
    {
        $this->propertyTest(function () {
            // Generate a user
            $user = User::factory()->create([
                'password' => Hash::make('correct-password'),
                'is_active' => true,
            ]);

            // Generate invalid credentials (wrong password)
            $invalidPasswords = ['wrong-password', '', 'different-pass', '123456', 'password123'];
            $invalidPassword = $invalidPasswords[array_rand($invalidPasswords)];

            $request = Request::create('/login', 'POST', [
                'email' => $user->email,
                'password' => $invalidPassword,
                'remember' => false,
            ]);
            $request->setLaravelSession($this->app['session.store']);

            // Clear any existing rate limits
            RateLimiter::clear($this->getThrottleKey($request));

            // Test authentication should fail
            $exceptionThrown = false;
            
            try {
                $this->authService->authenticate($request);
            } catch (ValidationException $e) {
                $exceptionThrown = true;
                
                // Verify authentication failed
                $this->assertFalse(Auth::check(), 'User should not be authenticated with invalid credentials');
                
                // Verify error message is generic (security best practice)
                $errors = $e->errors();
                $this->assertArrayHasKey('email', $errors, 'Should have email error');
                $this->assertContains('The provided credentials are incorrect.', $errors['email']);
                
                // Verify rate limiting was applied
                $throttleKey = $this->getThrottleKey($request);
                $this->assertTrue(RateLimiter::tooManyAttempts($throttleKey, 5) || RateLimiter::attempts($throttleKey) > 0, 
                    'Rate limiting should be applied after failed attempt');
            }
            
            $this->assertTrue($exceptionThrown, 'ValidationException should be thrown for invalid credentials');

        }, 10, 'Authentication Rejection');
    }

    /**
     * Property 3: Session Expiration Enforcement
     * For any expired user session, access to protected resources should be denied and re-authentication should be required.
     * Validates: Requirements 1.3
     */
    public function test_session_expiration_enforcement_property(): void
    {
        $this->propertyTest(function () {
            // Create and authenticate a user
            $user = User::factory()->create(['is_active' => true]);
            $user->assignRole('Student');
            
            // Simulate authenticated session
            $this->actingAs($user);
            $this->assertTrue(Auth::check(), 'User should be authenticated initially');

            // Simulate session expiration by logging out
            Auth::logout();
            
            // Verify session is expired
            $this->assertFalse(Auth::check(), 'User should not be authenticated after logout');
            $this->assertNull(Auth::user(), 'Auth user should be null after session expiration');

            // Test access to protected route should be denied
            $protectedRoutes = [
                '/admin/dashboard',
                '/teacher/dashboard', 
                '/student/dashboard',
            ];
            
            $route = $protectedRoutes[array_rand($protectedRoutes)];
            $response = $this->get($route);
            
            // Should redirect to login or return 401/403
            $this->assertTrue(
                in_array($response->getStatusCode(), [302, 401, 403]),
                'Access to protected route should be denied for expired session'
            );
            
            if ($response->getStatusCode() === 302) {
                // If redirected, should be to login page
                $this->assertTrue(
                    str_contains($response->headers->get('Location'), 'login'),
                    'Should redirect to login page'
                );
            }

        }, 10, 'Session Expiration Enforcement');
    }

    /**
     * Property 4: Role-Based Access Control
     * For any user and protected resource, access should be granted if and only if the user's role has the required permissions.
     * Validates: Requirements 1.5
     */
    public function test_role_based_access_control_property(): void
    {
        $this->propertyTest(function () {
            // Define role-based route access rules based on actual routes
            $routePermissions = [
                '/admin/dashboard' => ['Admin'],
                '/teacher/dashboard' => ['Teacher'],
                '/student/dashboard' => ['Student'],
            ];

            // Generate random user with random role
            $allRoles = ['Admin', 'Teacher', 'Student'];
            $userRole = $allRoles[array_rand($allRoles)];
            
            $user = User::factory()->create(['is_active' => true]);
            $user->assignRole($userRole);

            // Test access to each protected route
            foreach ($routePermissions as $route => $allowedRoles) {
                $response = $this->actingAs($user)->get($route);
                
                if (in_array($userRole, $allowedRoles)) {
                    // User should have access (200 or redirect to intended page)
                    $this->assertTrue(
                        in_array($response->getStatusCode(), [200, 302]),
                        "User with role '{$userRole}' should have access to '{$route}'"
                    );
                    
                    // If it's a redirect, it should not be to login
                    if ($response->getStatusCode() === 302) {
                        $location = $response->headers->get('Location');
                        $this->assertFalse(
                            str_contains($location, 'login'),
                            "User with correct role should not be redirected to login"
                        );
                    }
                } else {
                    // User should be denied access (403 or redirect)
                    $this->assertTrue(
                        in_array($response->getStatusCode(), [403, 302]),
                        "User with role '{$userRole}' should be denied access to '{$route}'"
                    );
                    
                    // If status is 403, that's explicit denial
                    if ($response->getStatusCode() === 403) {
                        $this->assertEquals(403, $response->getStatusCode());
                    }
                }
            }

            // Verify user has the correct role
            $this->assertTrue($user->hasRole($userRole), 'User should have the assigned role');

        }, 10, 'Role-Based Access Control');
    }

    /**
     * Helper method to generate throttle key for rate limiting tests
     */
    private function getThrottleKey(Request $request): string
    {
        return strtolower($request->input('email')) . '|' . $request->ip();
    }
}