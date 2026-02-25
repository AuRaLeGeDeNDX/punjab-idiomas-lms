<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class AuthenticationService
{
    /**
     * Attempt to authenticate a user with rate limiting
     */
    public function authenticate(Request $request): bool
    {
        $this->ensureIsNotRateLimited($request);

        $credentials = $request->only('email', 'password');
        
        // Check if user exists and is active
        $user = User::where('email', $credentials['email'])->first();
        
        if (!$user || !$user->is_active) {
            $this->hitRateLimit($request);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // Update last login timestamp
            $user->update(['last_login_at' => now()]);
            
            // Clear rate limit on successful login
            RateLimiter::clear($this->throttleKey($request));
            
            return true;
        }

        $this->hitRateLimit($request);
        
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    /**
     * Ensure the login request is not rate limited
     */
    protected function ensureIsNotRateLimited(Request $request): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey($request), 5)) {
            return;
        }

        $seconds = RateLimiter::availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => [
                trans('auth.throttle', [
                    'seconds' => $seconds,
                    'minutes' => ceil($seconds / 60),
                ])
            ],
        ]);
    }

    /**
     * Increment the login attempts for the user
     */
    protected function hitRateLimit(Request $request): void
    {
        RateLimiter::hit($this->throttleKey($request), 60 * 5); // 5 minutes
    }

    /**
     * Get the rate limiting throttle key for the request
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(
            Str::lower($request->input('email')) . '|' . $request->ip()
        );
    }

    /**
     * Create a new user with specified role
     */
    public function createUser(array $userData, string $role = 'Student'): User
    {
        $user = User::create([
            'name' => $userData['name'],
            'email' => $userData['email'],
            'password' => Hash::make($userData['password']),
            'bio' => $userData['bio'] ?? null,
            'phone' => $userData['phone'] ?? null,
            'is_active' => $userData['is_active'] ?? true,
        ]);

        $user->assignRole($role);

        return $user;
    }

    /**
     * Update user's last login timestamp
     */
    public function updateLastLogin(User $user): void
    {
        $user->update(['last_login_at' => now()]);
    }

    /**
     * Deactivate a user account
     */
    public function deactivateUser(User $user): void
    {
        $user->update(['is_active' => false]);
    }

    /**
     * Activate a user account
     */
    public function activateUser(User $user): void
    {
        $user->update(['is_active' => true]);
    }
}