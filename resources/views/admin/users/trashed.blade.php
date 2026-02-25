@extends('layouts.app')

@section('title', 'User Trash - Admin')

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
                        <h1><i class="fas fa-trash-restore me-2"></i>User Trash</h1>
                        <p>Manage soft-deleted users</p>
                    </div>
                    <div class="d-flex gap-2">
                        @if($users->count() > 0)
                            <form action="{{ route('admin.users.empty-trash') }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete ALL users in the trash? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="creative-btn creative-btn-danger">
                                    <i class="fas fa-dumpster"></i>Empty Trash
                                </button>
                            </form>
                        @endif
                        <a href="{{ route('admin.users.index') }}" class="creative-btn creative-btn-outline">
                            <i class="fas fa-arrow-left"></i>Back to Users
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="creative-card mb-4">
                <div class="creative-card-body">
                    <form method="GET" action="{{ route('admin.users.trashed') }}" class="row g-3">
                        <div class="col-md-9">
                            <label for="search" class="creative-form-label">Search</label>
                            <input type="text" class="creative-form-input" id="search" name="search" 
                                   value="{{ request('search') }}" placeholder="Name or email...">
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="creative-btn creative-btn-primary">
                                <i class="fas fa-search"></i>Filter
                            </button>
                            <a href="{{ route('admin.users.trashed') }}" class="creative-btn creative-btn-outline">
                                <i class="fas fa-times"></i>Clear
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="creative-card">
                <div class="creative-card-header">
                    <h3><i class="fas fa-users-slash"></i> Deleted Users ({{ $users->total() }})</h3>
                </div>
                <div class="creative-card-body" style="padding: 0;">
                    @if($users->count() > 0)
                        <div class="table-responsive">
                            <table class="creative-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Role</th>
                                        <th>Deleted At</th>
                                        <th width="150">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $user)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-secondary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
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
                                                <span class="creative-badge creative-badge-secondary">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>
                                            <small>{{ $user->deleted_at->format('M j, Y g:i A') }}</small>
                                            <br>
                                            <small class="text-muted">({{ $user->deleted_at->diffForHumans() }})</small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="creative-btn creative-btn-outline" 
                                                            style="padding: 0.5rem 0.75rem; color: var(--color-success); border-color: var(--color-success);"
                                                            title="Restore User"
                                                            onclick="return confirm('Restore this user?')">
                                                        <i class="fas fa-trash-restore"></i>
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.users.force-delete', $user->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="creative-btn creative-btn-outline" 
                                                            style="padding: 0.5rem 0.75rem; color: var(--color-danger); border-color: var(--color-danger);"
                                                            title="Permanently Delete"
                                                            onclick="return confirm('Permanently delete this user? This cannot be undone!')">
                                                        <i class="fas fa-user-times"></i>
                                                    </button>
                                                </form>
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
                            <i class="fas fa-trash fa-3x mb-3" style="color: var(--color-gray-400);"></i>
                            <h5 style="color: var(--color-gray-600);">Trash is empty</h5>
                            <p style="color: var(--color-gray-500);">
                                Deleted users will appear here.
                            </p>
                            <a href="{{ route('admin.users.index') }}" class="creative-btn creative-btn-primary">
                                <i class="fas fa-arrow-left"></i>Back to Active Users
                            </a>
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
