<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use AuthorizesRequests;
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users.
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', User::class);
        
        $query = User::with('roles');
        
        // Apply filters
        if ($request->has('role') && $request->role !== '') {
            $query->role($request->role);
        }
        
        if ($request->has('status') && $request->status !== '') {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->paginate(20);
        
        // Get filter options
        $roles = \Illuminate\Support\Facades\Cache::remember('roles_list', 3600, function () {
            return \Spatie\Permission\Models\Role::orderBy('name')->get();
        });
        
        if ($request->expectsJson()) {
            return response()->json([
                'users' => $users,
                'roles' => $roles
            ]);
        }
        
        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $this->authorize('create', User::class);
        
        $roles = \Illuminate\Support\Facades\Cache::remember('roles_list', 3600, function () {
            return \Spatie\Permission\Models\Role::orderBy('name')->get();
        });
        
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $this->authorize('create', User::class);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
            'bio' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        try {
            $user = $this->userService->createUser($validated);
// Clear cached lists so dropdowns reflect the new user
Cache::forget('teachers_list');
Cache::forget('admin_dashboard');
            
            
            $message = 'User created successfully!';
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'user' => $user->load('roles')
                ], 201);
            }
            
            return redirect()
                ->route('admin.users.show', $user)
                ->with('success', $message);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to create user: ' . $e->getMessage()
                ], 500);
            }
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create user: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified user.
     */
    public function show(User $user): View|JsonResponse
    {
        $this->authorize('view', $user);
        
        $user->load(['roles', 'enrollments.course', 'teachingCourses']);
        
        if (request()->expectsJson()) {
            return response()->json(['user' => $user]);
        }
        
        return view('admin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $this->authorize('update', $user);
        
        $roles = \Illuminate\Support\Facades\Cache::remember('roles_list', 3600, function () {
            return \Spatie\Permission\Models\Role::orderBy('name')->get();
        });
        
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $this->authorize('update', $user);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
            'bio' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
        ]);

        try {
            $this->userService->updateUser($user, $validated);
            Cache::forget('teachers_list');
Cache::forget('admin_dashboard');
            $message = 'User updated successfully!';
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'user' => $user->fresh()->load('roles')
                ]);
            }
            
            return redirect()
                ->route('admin.users.show', $user)
                ->with('success', $message);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to update user: ' . $e->getMessage()
                ], 500);
            }
            
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update user: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified user from storage (Soft Delete).
     */
    public function destroy(User $user): RedirectResponse|JsonResponse
    {
        $this->authorize('delete', $user);
        
        try {
            $this->userService->deleteUser($user);
            Cache::forget('teachers_list');
            Cache::forget('admin_dashboard');
            $message = 'User moved to trash successfully!';
            
            if (request()->expectsJson()) {
                return response()->json(['message' => $message]);
            }
            
            return redirect()
                ->route('admin.users.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to move user to trash: ' . $e->getMessage()
                ], 500);
            }
            
            return back()
                ->withErrors(['error' => 'Failed to move user to trash: ' . $e->getMessage()]);
        }
    }

    /**
     * Display a listing of soft-deleted users.
     */
    public function trashed(Request $request): View
    {
        $this->authorize('viewAny', User::class);
        
        $query = User::onlyTrashed()->with('roles');
        
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $users = $query->paginate(20);
        
        return view('admin.users.trashed', compact('users'));
    }

    /**
     * Restore a soft-deleted user.
     */
    public function restore(User $user): RedirectResponse
    {
        try {
            $this->authorize('restore', $user);
            
            $this->userService->restoreUser($user);
            
            return redirect()
                ->route('admin.users.index')
                ->with('success', 'User restored successfully!');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'Failed to restore user: ' . $e->getMessage()]);
        }
    }

    /**
     * Permanently delete a user from trash.
     */
    public function forceDelete(User $user): RedirectResponse|JsonResponse
    {
        try {
            $this->authorize('delete', $user);
            
            $this->userService->forceDeleteUser($user);
            
            $message = 'User permanently deleted!';
            
            if (request()->expectsJson()) {
                return response()->json(['message' => $message]);
            }
            
            return redirect()
                ->route('admin.users.trashed')
                ->with('success', $message);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to permanently delete user: ' . $e->getMessage()
                ], 500);
            }
            
            return back()
                ->withErrors(['error' => 'Failed to permanently delete user: ' . $e->getMessage()]);
        }
    }

    /**
     * Permanently delete all soft-deleted users.
     */
    public function emptyTrash(): RedirectResponse|JsonResponse
    {
        $this->authorize('emptyTrash', User::class);
        
        try {
            $count = $this->userService->emptyTrash();
            
            $message = "Trash emptied! Permanently deleted {$count} user(s).";
            
            if (request()->expectsJson()) {
                return response()->json(['message' => $message]);
            }
            
            return redirect()
                ->route('admin.users.index')
                ->with('success', $message);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to empty trash: ' . $e->getMessage()
                ], 500);
            }
            
            return back()
                ->withErrors(['error' => 'Failed to empty trash: ' . $e->getMessage()]);
        }
    }

    /**
     * Reset user password.
     */
    public function resetPassword(Request $request, User $user): RedirectResponse|JsonResponse
    {
        $this->authorize('resetPassword', $user);
        
        $validated = $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        try {
            $user->update([
                'password' => Hash::make($validated['password'])
            ]);
            
            $message = 'Password reset successfully!';
            
            if ($request->expectsJson()) {
                return response()->json(['message' => $message]);
            }
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to reset password: ' . $e->getMessage()
                ], 500);
            }
            
            return back()
                ->withErrors(['error' => 'Failed to reset password: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle user active status.
     */
    public function toggleStatus(User $user): RedirectResponse|JsonResponse
    {
        $this->authorize('deactivate', $user);
        
        try {
            $user->update(['is_active' => !$user->is_active]);
            
            $message = $user->is_active ? 'User activated successfully!' : 'User deactivated successfully!';
            
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'user' => $user->fresh()
                ]);
            }
            
            return back()->with('success', $message);
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Failed to toggle user status: ' . $e->getMessage()
                ], 500);
            }
            
            return back()
                ->withErrors(['error' => 'Failed to toggle user status: ' . $e->getMessage()]);
        }
    }
}
