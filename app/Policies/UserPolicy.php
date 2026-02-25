<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any users.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can empty the trash.
     */
    public function emptyTrash(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can view the user.
     */
    public function view(User $user, User $model): bool
    {
        // Admins can view all users
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Users can view their own profile
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can create users.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can update the user.
     */
    public function update(User $user, User $model): bool
    {
        // Admins can update all users except they cannot change their own role
        if ($user->hasRole('Admin')) {
            return true;
        }

        // Users can update their own profile (limited fields)
        return $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the user.
     */
    public function delete(User $user, User $model): bool
    {
        // Only admins can delete users
        if (!$user->hasRole('Admin')) {
            return false;
        }

        // Admins cannot delete themselves
        if ($user->id === $model->id) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the user.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->hasRole('Admin');
    }

    /**
     * Determine whether the user can permanently delete the user.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return $user->hasRole('Admin') && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can manage user roles.
     */
    public function manageRoles(User $user, User $model): bool
    {
        // Only admins can manage roles
        if (!$user->hasRole('Admin')) {
            return false;
        }

        // Admins cannot change their own role
        return $user->id !== $model->id;
    }

    /**
     * Determine whether the user can deactivate users.
     */
    public function deactivate(User $user, User $model): bool
    {
        // Only admins can deactivate users
        if (!$user->hasRole('Admin')) {
            return false;
        }

        // Admins cannot deactivate themselves
        return $user->id !== $model->id;
    }

    /**
     * Determine whether the user can reset passwords.
     */
    public function resetPassword(User $user, User $model): bool
    {
        return $user->hasRole('Admin');
    }
}