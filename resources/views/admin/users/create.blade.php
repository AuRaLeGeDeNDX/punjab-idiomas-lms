@extends('layouts.app')

@section('title', 'Create User - Admin')

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
                        <h1><i class="fas fa-user-plus me-2"></i>Create New User</h1>
                        <p>Add a new user to the system with role and permissions</p>
                    </div>
                    <a href="{{ route('admin.users.index') }}" class="creative-btn creative-btn-outline">
                        <i class="fas fa-arrow-left"></i>Back to Users
                    </a>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="creative-card fade-in-up stagger-1">
                        <div class="creative-card-header">
                            <h3><i class="fas fa-info-circle"></i>User Information</h3>
                        </div>
                        <div class="creative-card-body">
                            <form method="POST" action="{{ route('admin.users.store') }}">
                                @csrf
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="creative-form-label">Full Name <span class="text-danger">*</span></label>
                                        <input type="text" class="creative-form-input @error('name') is-invalid @enderror" 
                                               id="name" name="name" value="{{ old('name') }}" required>
                                        @error('name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="creative-form-label">Email Address <span class="text-danger">*</span></label>
                                        <input type="email" class="creative-form-input @error('email') is-invalid @enderror" 
                                               id="email" name="email" value="{{ old('email') }}" required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="creative-form-label">Password <span class="text-danger">*</span></label>
                                        <input type="password" class="creative-form-input @error('password') is-invalid @enderror" 
                                               id="password" name="password" required>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="creative-form-label">Confirm Password <span class="text-danger">*</span></label>
                                        <input type="password" class="creative-form-input" 
                                               id="password_confirmation" name="password_confirmation" required>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="role" class="creative-form-label">Role <span class="text-danger">*</span></label>
                                        <select class="creative-form-input @error('role') is-invalid @enderror" id="role" name="role" required>
                                            <option value="">Select Role</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->name }}" {{ old('role') === $role->name ? 'selected' : '' }}>
                                                    {{ ucfirst($role->name) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('role')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="creative-form-label">Phone Number</label>
                                        <input type="text" class="creative-form-input @error('phone') is-invalid @enderror" 
                                               id="phone" name="phone" value="{{ old('phone') }}">
                                        @error('phone')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="bio" class="creative-form-label">Bio</label>
                                    <textarea class="creative-form-input @error('bio') is-invalid @enderror" 
                                              id="bio" name="bio" rows="3" placeholder="Brief description about the user...">{{ old('bio') }}</textarea>
                                    @error('bio')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                                               {{ old('is_active', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">
                                            Active User
                                        </label>
                                    </div>
                                    <small class="form-text text-secondary">Inactive users cannot log in to the system.</small>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="creative-btn creative-btn-primary">
                                        <i class="fas fa-save"></i>Create User
                                    </button>
                                    <a href="{{ route('admin.users.index') }}" class="creative-btn creative-btn-outline">
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="creative-card fade-in-up stagger-2">
                        <div class="creative-card-header">
                            <h3><i class="fas fa-shield-alt"></i>Role Information</h3>
                        </div>
                        <div class="creative-card-body">
                            <div id="role-info">
                                <p class="text-secondary">Select a role to see its permissions and description.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('role').addEventListener('change', function() {
    const roleInfo = document.getElementById('role-info');
    const selectedRole = this.value;
    
    const roleDescriptions = {
        'Admin': {
            description: 'Full system access with all administrative privileges.',
            permissions: ['Manage users', 'Manage courses', 'System configuration', 'View all data']
        },
        'Teacher': {
            description: 'Can create and manage courses, view enrolled students.',
            permissions: ['Create courses', 'Manage own courses', 'Grade assignments', 'View student progress']
        },
        'Student': {
            description: 'Can enroll in courses and access learning materials.',
            permissions: ['View assigned courses', 'Submit assignments', 'View grades', 'Access course materials']
        }
    };
    
    if (selectedRole && roleDescriptions[selectedRole]) {
        const role = roleDescriptions[selectedRole];
        roleInfo.innerHTML = `
            <h6 class="text-primary">${selectedRole}</h6>
            <p class="small text-secondary">${role.description}</p>
            <h6 class="mt-3">Key Permissions:</h6>
            <ul class="small">
                ${role.permissions.map(perm => `<li>${perm}</li>`).join('')}
            </ul>
        `;
    } else {
        roleInfo.innerHTML = '<p class="text-secondary">Select a role to see its permissions and description.</p>';
    }
});
</script>
@endpush