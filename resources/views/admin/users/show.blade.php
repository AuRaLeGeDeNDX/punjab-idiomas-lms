@extends('layouts.app')

@section('title', 'User Details - Admin')

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
                        <h1><i class="fas fa-user me-2"></i>User Details</h1>
                        <p>View and manage user information</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.users.edit', $user) }}" class="creative-btn creative-btn-primary">
                            <i class="fas fa-edit"></i>Edit User
                        </a>
                        <a href="{{ route('admin.users.index') }}" class="creative-btn creative-btn-outline">
                            <i class="fas fa-arrow-left"></i>Back to Users
                        </a>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <!-- User Information -->
                    <div class="creative-card mb-4 fade-in-up stagger-1">
                        <div class="creative-card-header">
                            <h3><i class="fas fa-info-circle"></i>User Information</h3>
                        </div>
                        <div class="creative-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="creative-form-label text-muted">Full Name</label>
                                        <p class="fw-medium">{{ $user->name }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="creative-form-label text-muted">Email Address</label>
                                        <p>{{ $user->email }}</p>
                                    </div>
                                    <div class="mb-3">
                                        <label class="creative-form-label text-muted">Phone Number</label>
                                        <p>{{ $user->phone ?: 'Not provided' }}</p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="creative-form-label text-muted">Role</label>
                                        <div>
                                            @foreach($user->roles as $role)
                                                <span class="creative-badge creative-badge-{{ $role->name === 'Admin' ? 'danger' : ($role->name === 'Teacher' ? 'warning' : 'info') }}">
                                                    {{ $role->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="creative-form-label text-muted">Status</label>
                                        <div>
                                            @if($user->is_active)
                                                <span class="creative-badge creative-badge-success">Active</span>
                                            @else
                                                <span class="creative-badge creative-badge-secondary">Inactive</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="creative-form-label text-muted">Member Since</label>
                                        <p>{{ $user->created_at->format('F j, Y') }}</p>
                                    </div>
                                </div>
                            </div>
                            
                            @if($user->bio)
                            <div class="mb-3">
                                <label class="creative-form-label text-muted">Bio</label>
                                <p>{{ $user->bio }}</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Course Information -->
                    @if($user->hasRole('Student') && $user->enrollments->count() > 0)
                    <div class="creative-card mb-4 fade-in-up stagger-2">
                        <div class="creative-card-header">
                            <h3><i class="fas fa-graduation-cap"></i>Enrolled Courses</h3>
                        </div>
                        <div class="creative-card-body">
                            <div class="table-responsive">
                                <table class="creative-table">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>Status</th>
                                            <th>Enrolled</th>
                                            <th>Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->enrollments as $enrollment)
                                        <tr>
                                            <td>
                                                <div>
                                                    <div class="fw-medium">{{ $enrollment->course->title }}</div>
                                                    <small class="text-muted">{{ $enrollment->course->code }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="creative-badge creative-badge-{{ $enrollment->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($enrollment->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $enrollment->created_at->format('M j, Y') }}</td>
                                            <td>
                                                <div class="progress" style="height: 6px;">
                                                    <div class="progress-bar" role="progressbar" 
                                                         style="width: {{ $enrollment->progress ?? 0 }}%"></div>
                                                </div>
                                                <small class="text-muted">{{ $enrollment->progress ?? 0 }}%</small>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($user->hasRole('Teacher') && $user->teachingCourses->count() > 0)
                    <div class="creative-card mb-4 fade-in-up stagger-3">
                        <div class="creative-card-header">
                            <h3><i class="fas fa-chalkboard-teacher"></i>Teaching Courses</h3>
                        </div>
                        <div class="creative-card-body">
                            <div class="table-responsive">
                                <table class="creative-table">
                                    <thead>
                                        <tr>
                                            <th>Course</th>
                                            <th>Students</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($user->teachingCourses as $course)
                                        <tr>
                                            <td>
                                                <div>
                                                    <div class="fw-medium">{{ $course->title }}</div>
                                                    <small class="text-muted">{{ $course->code }}</small>
                                                </div>
                                            </td>
                                            <td>{{ $course->enrollments_count ?? 0 }}</td>
                                            <td>
                                                <span class="creative-badge creative-badge-{{ $course->is_published ? 'success' : 'warning' }}">
                                                    {{ $course->is_published ? 'Published' : 'Draft' }}
                                                </span>
                                            </td>
                                            <td>{{ $course->created_at->format('M j, Y') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <!-- Quick Actions -->
                    <div class="creative-card mb-4 fade-in-up stagger-1">
                        <div class="creative-card-header">
                            <h3><i class="fas fa-bolt"></i>Quick Actions</h3>
                        </div>
                        <div class="creative-card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="creative-btn creative-btn-outline">
                                    <i class="fas fa-edit"></i>Edit Profile
                                </a>
                                
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                    @csrf
                                    <button type="submit" class="creative-btn creative-btn-outline w-100"
                                            onclick="return confirm('Are you sure you want to {{ $user->is_active ? 'deactivate' : 'activate' }} this user?')">
                                        <i class="fas fa-{{ $user->is_active ? 'pause' : 'play' }}"></i>
                                        {{ $user->is_active ? 'Deactivate' : 'Activate' }} User
                                    </button>
                                </form>
                                
                                <button type="button" class="creative-btn creative-btn-outline" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                    <i class="fas fa-key"></i>Reset Password
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- User Statistics -->
                    <div class="creative-card fade-in-up stagger-2">
                        <div class="creative-card-header">
                            <h3><i class="fas fa-chart-bar"></i>Statistics</h3>
                        </div>
                        <div class="creative-card-body">
                            <div class="row text-center">
                                @if($user->hasRole('Student'))
                                <div class="col-6">
                                    <div class="border-end">
                                        <h4 class="text-primary mb-1">{{ $user->enrollments->count() }}</h4>
                                        <small class="text-muted">Courses</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success mb-1">{{ $user->enrollments->where('status', 'active')->count() }}</h4>
                                    <small class="text-muted">Active</small>
                                </div>
                                @elseif($user->hasRole('Teacher'))
                                <div class="col-6">
                                    <div class="border-end">
                                        <h4 class="text-primary mb-1">{{ $user->teachingCourses->count() }}</h4>
                                        <small class="text-muted">Courses</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <h4 class="text-success mb-1">{{ $user->teachingCourses->where('is_published', true)->count() }}</h4>
                                    <small class="text-muted">Published</small>
                                </div>
                                @endif
                            </div>
                            
                            <hr class="my-3">
                            
                            <div class="small text-muted">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Last Login:</span>
                                    <span>{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}</span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span>Joined:</span>
                                    <span>{{ $user->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
@if($user->id !== auth()->id())
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.users.reset-password', $user) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Reset Password</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Reset password for <strong>{{ $user->name }}</strong></p>
                    
                    <div class="mb-3">
                        <label for="password" class="creative-form-label">New Password</label>
                        <input type="password" class="creative-form-input" id="password" name="password" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password_confirmation" class="creative-form-label">Confirm Password</label>
                        <input type="password" class="creative-form-input" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="creative-btn creative-btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="creative-btn creative-btn-primary">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection