@extends('layouts.app')

@section('title', 'Edit User - Admin')

@section('sidebar')
    @include('admin.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="creative-page-header fade-in-up">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-user-edit me-2"></i>Edit User</h1>
                        <p>Update user information and settings</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.users.show', $user) }}" class="creative-btn creative-btn-outline">
                            <i class="fas fa-eye"></i>View User
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="creative-btn creative-btn-outline">
                            <i class="fas fa-arrow-left"></i>Back to Users
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="creative-card fade-in-up stagger-1">
                        <div class="creative-card-header">
                            <h3><i class="fas fa-info-circle"></i>User Information</h3>
                        </div>
                        <div class="creative-card-body">
                            <form method="POST" action="{{ route('admin.users.update', $user) }}">
                                @csrf
                                @method('PUT')
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="creative-form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="creative-form-input @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name', $user->name) }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="creative-form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="creative-form-input @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email', $user->email) }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="creative-form-label">New Password</label>
                                        <input type="password" class="creative-form-input @error('password') is-invalid @enderror" 
                                               id="password" name="password">
                                        <small class="form-text text-muted">Leave blank to keep current password</small>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="creative-form-label">Confirm New Password</label>
                                        <input type="password" class="creative-form-input" 
                                               id="password_confirmation" name="password_confirmation">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="role" class="creative-form-label">Role <span class="text-danger">*</span></label>
                                        <select class="creative-form-input @error('role') is-invalid @enderror" id="role" name="role" required
                                                {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                            <option value="">Select Role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" 
                                                        {{ old('role', $user->roles->first()?->name) === $role->name ? 'selected' : '' }}>
                                                    {{ ucfirst($role->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @if($user->id === auth()->id())
                                            <small class="form-text text-muted">You cannot change your own role</small>
                                            <input type="hidden" name="role" value="{{ $user->roles->first()?->name }}">
                                        @endif
                                        @error('role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="creative-form-label">Phone Number</label>
                                        <input type="text" class="creative-form-input @error('phone') is-invalid @enderror" 
                                               id="phone" name="phone" value="{{ old('phone', $user->phone) }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="bio" class="creative-form-label">Bio</label>
                                    <textarea class="creative-form-input @error('bio') is-invalid @enderror" 
                                              id="bio" name="bio" rows="3" placeholder="Brief description about the user...">{{ old('bio', $user->bio) }}</textarea>
                                    @error('bio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                               {{ $user->id === auth()->id() ? 'disabled' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active User
                                        </label>
                                        @if($user->id === auth()->id())
                                            <input type="hidden" name="is_active" value="1">
                                        @endif
                                    </div>
                                    @if($user->id === auth()->id())
                                        <small class="form-text text-muted">You cannot deactivate your own account</small>
                                    @else
                                        <small class="form-text text-muted">Inactive users cannot log in to the system</small>
                                    @endif
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="creative-btn creative-btn-primary">
                                        <i class="fas fa-save"></i>Update User
                                    </button>
                                    <a href="{{ route('admin.users.show', $user) }}" class="creative-btn creative-btn-outline">
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="creative-card mb-4 fade-in-up stagger-2">
                        <div class="creative-card-header">
                            <h3><i class="fas fa-info"></i>Current Information</h3>
                        </div>
                        <div class="creative-card-body">
                            <div class="mb-3">
                                <label class="creative-form-label text-muted small">Current Role</label>
                                <div>
                                    @foreach($user->roles as $role)
                                        <span class="creative-badge creative-badge-{{ $role->name === 'Admin' ? 'danger' : ($role->name === 'Teacher' ? 'warning' : 'info') }}">
                                            {{ $role->name }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="creative-form-label text-muted small">Status</label>
                                <div>
                                    @if($user->is_active)
                                        <span class="creative-badge creative-badge-success">Active</span>
                                    @else
                                        <span class="creative-badge creative-badge-secondary">Inactive</span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="creative-form-label text-muted small">Member Since</label>
                                <p class="mb-0">{{ $user->created_at->format('F j, Y') }}</p>
                            </div>
                            
                            <div class="mb-3">
                                <label class="creative-form-label text-muted small">Last Updated</label>
                                <p class="mb-0">{{ $user->updated_at->format('F j, Y g:i A') }}</p>
                            </div>
                        </div>
                    </div>

                    @if($user->id !== auth()->id())
                    <div class="creative-card fade-in-up stagger-3">
                        <div class="creative-card-header">
                            <h3 class="text-danger"><i class="fas fa-exclamation-triangle"></i>Danger Zone</h3>
                        </div>
                        <div class="creative-card-body">
                            <p class="small text-muted mb-3">These actions cannot be undone. Please be careful.</p>
                            
                            <div class="d-grid gap-2">
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                    @csrf
                                    <button type="submit" class="creative-btn creative-btn-outline w-100"
                                            onclick="return confirm('Are you sure you want to {{ $user->is_active ? 'deactivate' : 'activate' }} this user?')">
                                        <i class="fas fa-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }} User
                                    </button>
                                </form>
                                
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="creative-btn creative-btn-outline w-100 text-danger"
                                            onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
                                        <i class="fas fa-trash"></i>Delete User
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('role').addEventListener('change', function() {
    const selectedRole = this.value;
    
    // You can add role-specific warnings or information here
    if (selectedRole === 'Admin') {
        // Show admin warning if needed
    }
});
</script>
@endpush