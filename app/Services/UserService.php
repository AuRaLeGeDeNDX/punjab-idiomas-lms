<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class UserService
{
    /**
     * Create a new user.
     */
    public function createUser(array $data): User
    {
        DB::beginTransaction();
        
        try {
            // Create the user
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'bio' => $data['bio'] ?? null,
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Assign role
            $role = Role::findByName($data['role']);
            $user->assignRole($role);

            DB::commit();
            
            // Clear relevant caches
            $this->clearUserCaches();
            
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing user.
     */
    public function updateUser(User $user, array $data): User
    {
        DB::beginTransaction();
        
        try {
            // Update basic user data
            $updateData = [
                'name' => $data['name'],
                'email' => $data['email'],
                'bio' => $data['bio'] ?? null,
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? $user->is_active,
            ];

            // Update password if provided
            if (!empty($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $user->update($updateData);

            // Update role if changed
            if (isset($data['role'])) {
                $newRole = Role::findByName($data['role']);
                $currentRoles = $user->getRoleNames();
                
                if (!$currentRoles->contains($data['role'])) {
                    $user->syncRoles([$newRole]);
                }
            }

            DB::commit();
            
            // Clear relevant caches
            $this->clearUserCaches($user);
            
            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Soft delete a user.
     */
    public function deleteUser(User $user): void
    {
        DB::beginTransaction();
        
        try {
            // Soft delete
            $user->delete();
            
            DB::commit();
            
            // Clear relevant caches
            $this->clearUserCaches($user);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Permanently delete a user (use with caution).
     */
    public function forceDeleteUser(User $user): void
    {
        DB::beginTransaction();
        
        try {
            // Remove all enrollments (if they exist)
            $user->enrollments()->delete();
            
            // Remove role assignments
            $user->syncRoles([]);
            
            // Permanent delete from database
            $user->forceDelete();
            
            DB::commit();
            
            // Clear relevant caches
            $this->clearUserCaches();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restoreUser(User $user): void
    {
        DB::beginTransaction();
        
        try {
            $user->restore();
            
            DB::commit();
            
            // Clear relevant caches
            $this->clearUserCaches($user);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Permanently delete all soft-deleted users.
     */
    public function emptyTrash(): int
    {
        DB::beginTransaction();
        
        try {
            $trashedUsers = User::onlyTrashed()->get();
            $count = $trashedUsers->count();
            
            foreach ($trashedUsers as $user) {
                // Remove all enrollments
                $user->enrollments()->delete();
                
                // Remove role assignments
                $user->syncRoles([]);
                
                // Permanent delete
                $user->forceDelete();
            }
            
            DB::commit();
            
            // Clear relevance caches
            $this->clearUserCaches();
            
            return $count;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get user statistics for dashboard.
     */
    public function getUserStatistics(): array
    {
        return Cache::remember('user_statistics', 1800, function () {
            return [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'inactive_users' => User::where('is_active', false)->count(),
                'students_count' => User::role('Student')->count(),
                'teachers_count' => User::role('Teacher')->count(),
                'admins_count' => User::role('Admin')->count(),
                'recent_registrations' => User::where('created_at', '>=', now()->subDays(7))->count(),
            ];
        });
    }

    /**
     * Get users by role.
     */
    public function getUsersByRole(string $roleName): \Illuminate\Database\Eloquent\Collection
    {
        return User::role($roleName)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Search users.
     */
    public function searchUsers(string $query, ?string $role = null, ?bool $isActive = null): \Illuminate\Database\Eloquent\Collection
    {
        $users = User::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%");
        });

        if ($role) {
            $users->role($role);
        }

        if ($isActive !== null) {
            $users->where('is_active', $isActive);
        }

        return $users->with('roles')->orderBy('name')->get();
    }

    /**
     * Validate user data.
     */
    public function validateUserData(array $data, ?User $user = null): void
    {
        // Check if email is unique
        $emailQuery = User::where('email', $data['email']);
        if ($user) {
            $emailQuery->where('id', '!=', $user->id);
        }
        
        if ($emailQuery->exists()) {
            throw ValidationException::withMessages([
                'email' => 'The email address is already taken.'
            ]);
        }

        // Validate role exists
        if (isset($data['role']) && !Role::where('name', $data['role'])->exists()) {
            throw ValidationException::withMessages([
                'role' => 'The selected role is invalid.'
            ]);
        }
    }

    /**
     * Reset user password.
     */
    public function resetPassword(User $user, string $newPassword): void
    {
        $user->update([
            'password' => Hash::make($newPassword)
        ]);
        
        // Clear user sessions if needed
        // This would require additional session management
    }

    /**
     * Toggle user active status.
     */
    public function toggleUserStatus(User $user): User
    {
        $user->update(['is_active' => !$user->is_active]);
        
        $this->clearUserCaches($user);
        
        return $user;
    }

    /**
     * Clear user-related caches.
     */
    protected function clearUserCaches(?User $user = null): void
    {
        Cache::forget('user_statistics');
        
        if ($user) {
            Cache::forget("user_profile_{$user->id}");
            Cache::forget("user_permissions_{$user->id}");
            Cache::forget("student_dashboard_{$user->id}");
        }
        
        // Clear role-based caches
        Cache::forget('active_students');
        Cache::forget('active_teachers');
        Cache::forget('active_admins');
    }
}