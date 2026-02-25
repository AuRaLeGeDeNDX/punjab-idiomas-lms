@extends('layouts.app')

@section('title', 'User Management - Admin')

@section('sidebar')
    @include('admin.sidebar')
@endsection

@push('styles')
@vite(['resources/css/design-system.css', 'resources/css/components/buttons.css', 'resources/css/components/cards.css', 'resources/css/components/forms.css', 'resources/css/components/tables.css', 'resources/css/components/alerts.css'])
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="creative-page-header fade-in-up">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-users me-2"></i>User Management</h1>
                        <p>Manage system users, roles, and permissions</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.users.trashed') }}" class="creative-btn creative-btn-outline text-danger">
                            <i class="fas fa-trash-alt"></i>View Trash
                        </a>
                        <a href="{{ route('admin.users.create') }}" class="creative-btn creative-btn-primary">
                            <i class="fas fa-user-plus"></i>Add User
                        </a>
                        <a href="{{ route('admin.dashboard') }}" class="creative-btn creative-btn-outline">
                            <i class="fas fa-arrow-left"></i>Back
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="creative-card mb-4">
                <div class="creative-card-body">
                    <form method="GET" action="{{ route('admin.users.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="search" class="creative-form-label">Search</label>
                            <input type="text" class="creative-form-input" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Name or email...">
                        </div>
                        <div class="col-md-2">
                            <label for="role" class="creative-form-label">Role</label>
                            <select class="creative-form-input" id="role" name="role">
                                <option value="">All Roles</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                                        {{ ucfirst($role->name) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="status" class="creative-form-label">Status</label>
                            <select class="creative-form-input" id="status" name="status">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="creative-btn creative-btn-primary">
                                <i class="fas fa-search"></i>Filter
                            </button>
                            <a href="{{ route('admin.users.index') }}" class="creative-btn creative-btn-outline">
                                <i class="fas fa-times"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="creative-card">
                <div class="creative-card-header">
                    <h3><i class="fas fa-users"></i> Users ({{ $users->total() }})</h3>
                </div>
                <div class="creative-card-body" style="padding: 0;">
                    @if($users->count() > 0)
                        <div class="table-responsive">
                            <table class="creative-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                        <th>Last Login</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-medium">{{ $user->name }}</div>
                                                    <small class="text-muted">{{ $user->email }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @foreach($user->roles as $role)
                                                <span class="creative-badge creative-badge-{{ $role->name === 'Admin' ? 'danger' : ($role->name === 'Teacher' ? 'warning' : 'info') }}">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @if($user->is_active)
                                                <span class="creative-badge creative-badge-success">Active</span>
                                            @else
                                                <span class="creative-badge creative-badge-primary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $user->created_at->format('M j, Y') }}</small>
                                        </td>
                                        <td>
                                            @if($user->last_login_at)
                                                <small>{{ $user->last_login_at->diffForHumans() }}</small>
                                            @else
                                                <small class="text-muted">Never</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.users.show', $user) }}" 
                                                   class="creative-btn creative-btn-outline" style="padding: 0.5rem 0.75rem;" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.users.edit', $user) }}" 
                                                   class="creative-btn creative-btn-outline" style="padding: 0.5rem 0.75rem;" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                @if($user->id !== auth()->id())
                                                    <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}" class="d-inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="creative-btn creative-btn-outline" style="padding: 0.5rem 0.75rem; border-color: {{ $user->is_active ? 'var(--color-warning)' : 'var(--color-success)' }}; color: {{ $user->is_active ? 'var(--color-warning)' : 'var(--color-success)' }};" 
                                                                title="{{ $user->is_active ? 'Deactivate' : 'Activate' }}"
                                                                onclick="return confirm('Are you sure you want to {{ $user->is_active ? 'deactivate' : 'activate' }} this user?')">
                                                            <i class="fas fa-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        @if($users->hasPages())
                            <div style="padding: 1.5rem; border-top: 1px solid var(--color-gray-200);">
                                {{ $users->appends(request()->query())->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-users fa-3x mb-3" style="color: var(--color-gray-400);"></i>
                            <h5 style="color: var(--color-gray-600);">No users found</h5>
                            <p style="color: var(--color-gray-500);">
                                @if(request()->hasAny(['search', 'role', 'status']))
                                    Try adjusting your filters or 
                                    <a href="{{ route('admin.users.index') }}">clear all filters</a>.
                                @else
                                    Start by creating your first user.
                                @endif
                            </p>
                            @if(!request()->hasAny(['search', 'role', 'status']))
                                <a href="{{ route('admin.users.create') }}" class="creative-btn creative-btn-primary">
                                    <i class="fas fa-user-plus"></i>Add First User
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.avatar-sm {
    width: 40px;
    height: 40px;
    font-size: 16px;
    font-weight: 600;
}
</style>
@endpush