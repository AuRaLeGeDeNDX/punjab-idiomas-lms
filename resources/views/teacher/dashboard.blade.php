@extends('layouts.app')

@section('title', 'Teacher Dashboard')

@section('nav-items')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('teacher.courses.index') }}">My Courses</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('teacher.courses.create') }}">Create Course</a>
    </li>
@endsection

@section('sidebar')
    @include('teacher.sidebar')
@endsection

@section('content')
<!-- Page Header -->
<div class="creative-page-header fade-in-up">
    <h1><i class="fas fa-chalkboard-teacher me-2"></i>Welcome back, {{ auth()->user()->name }}!</h1>
    <p>Here's your teaching overview and recent activity.</p>
</div>

<!-- Quick Stats -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="creative-stat-card variant-success fade-in-up stagger-1">
        <div class="icon"><i class="fas fa-book"></i></div>
        <div class="number">{{ $coursesCount }}</div>
        <div class="label">My Courses</div>
    </div>
    
    <div class="creative-stat-card variant-info fade-in-up stagger-2">
        <div class="icon"><i class="fas fa-users"></i></div>
        <div class="number">{{ $totalStudentsCount }}</div>
        <div class="label">Total Students</div>
    </div>
    
    <div class="creative-stat-card variant-warning fade-in-up stagger-3">
        <div class="icon"><i class="fas fa-clipboard-list"></i></div>
        <div class="number">{{ $pendingGradingCount }}</div>
        <div class="label">Pending Grading</div>
    </div>
    
    <div class="creative-stat-card fade-in-up stagger-4">
        <div class="icon"><i class="fas fa-tasks"></i></div>
        <div class="number">{{ $activeAssignmentsCount }}</div>
        <div class="label">Active Assignments</div>
    </div>
</div>

<div class="row">
    <!-- My Courses -->
    <div class="col-lg-8">
        <div class="creative-card mb-4">
            <div class="creative-card-header">
                <h3 class="mb-0"><i class="fas fa-book me-2"></i>My Courses</h3>
                <a href="{{ route('teacher.courses.create') }}" class="creative-btn creative-btn-success">
                    <i class="fas fa-plus me-1"></i> Create New
                </a>
            </div>
            <div class="creative-card-body">
                @forelse($courses as $course)
                    <div class="d-flex align-items-center mb-3 p-3 border rounded responsive-card-list-item">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <a href="{{ route('teacher.courses.show', $course) }}" class="text-decoration-none">
                                    {{ $course->title }}
                                </a>
                                @if($course->is_published)
                                    <span class="creative-badge creative-badge-success creative-badge-sm ms-2">Published</span>
                                @else
                                    <span class="creative-badge creative-badge-secondary creative-badge-sm ms-2">Draft</span>
                                @endif
                            </h6>
                            <p class="text-muted mb-1">{{ Str::limit($course->description, 100) }}</p>
                            <small class="text-muted d-block">
                                <i class="fas fa-users me-1"></i>{{ $course->enrollments_count }} students enrolled
                                <i class="fas fa-book-open ms-2 me-1"></i>{{ $course->modules_count }} modules
                            </small>
                        </div>
                        <div class="text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('teacher.courses.show', $course) }}" 
                                   class="creative-btn creative-btn-outline-primary creative-btn-sm">Manage</a>
                                <a href="{{ route('teacher.courses.edit', $course) }}" 
                                   class="creative-btn creative-btn-outline-secondary creative-btn-sm">Edit</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No courses created</h5>
                        <p class="text-muted">Create your first course to start teaching.</p>
                        <a href="{{ route('teacher.courses.create') }}" class="creative-btn creative-btn-success">Create Course</a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="creative-card">
            <div class="creative-card-header">
                <h3><i class="fas fa-history"></i> Recent Activity</h3>
            </div>
            <div class="creative-card-body">
                @forelse($recentActivities as $activity)
                    <div class="d-flex align-items-start mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas {{ $activity['icon'] }} text-{{ $activity['color'] }} me-3"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div>{{ $activity['message'] }}</div>
                            <small class="text-muted">{{ $activity['time'] }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">No recent activity</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Sidebar Content -->
    <div class="col-lg-4">
        <!-- System Announcements -->
        <div class="creative-card mb-4">
            <div class="creative-card-header">
                <h3><i class="fas fa-bullhorn"></i> Announcements</h3>
            </div>
            <div class="creative-card-body">
                @forelse($announcements as $announcement)
                    <div class="mb-3 p-3 border rounded">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="mb-1">{{ $announcement->title }}</h6>
                            @if($announcement->priority === 'high')
                                <span class="creative-badge creative-badge-danger">High</span>
                            @elseif($announcement->priority === 'medium')
                                <span class="creative-badge creative-badge-warning">Medium</span>
                            @else
                                <span class="creative-badge creative-badge-primary">Low</span>
                            @endif
                        </div>
                        <p class="text-muted small mb-2">{{ Str::limit($announcement->message, 100) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                {{ $announcement->published_at->format('M j, Y') }}
                                @if($announcement->isSystemWide())
                                    <span class="creative-badge creative-badge-info ms-1">System</span>
                                @endif
                            </small>
                            @if($announcement->display_until)
                                <small class="text-muted">
                                    Expires: {{ $announcement->display_until->format('M j') }}
                                </small>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">No announcements</p>
                @endforelse
            </div>
        </div>

        <!-- Pending Grading -->
        <div class="creative-card mb-4">
            <div class="creative-card-header">
                <h3><i class="fas fa-clipboard-check"></i> Pending Grading</h3>
            </div>
            <div class="creative-card-body">
                @forelse($pendingSubmissions as $submission)
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 border-start border-3 border-warning rounded responsive-card-list-item" style="background: rgba(245, 158, 11, 0.03);">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $submission->assignment->title }}</h6>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-user-circle text-muted"></i>
                                <span class="small fw-medium">{{ $submission->user->name }}</span>
                            </div>
                            <div class="small text-muted">
                                <i class="fas fa-clock me-1"></i>Submitted: {{ $submission->submitted_at->format('M j, g:i A') }}
                                @if($submission->is_late)
                                    <span class="text-danger fw-bold ms-1">(Late)</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-end ms-3">
                            <a href="{{ route('teacher.grading.show', $submission) }}" 
                               class="creative-btn creative-btn-outline-warning creative-btn-sm w-100">Grade</a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">No submissions to grade</p>
                @endforelse
            </div>
        </div>

        <!-- Course Performance -->
        <div class="creative-card">
            <div class="creative-card-header">
                <h3><i class="fas fa-chart-line"></i> Course Performance</h3>
            </div>
            <div class="creative-card-body">
                @forelse($coursePerformance as $performance)
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <h6 class="mb-0">{{ $performance['course_title'] }}</h6>
                            <small class="text-muted">{{ $performance['avg_grade'] }}% avg</small>
                        </div>
                        <div class="progress" style="height: 8px; border-radius: 4px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $performance['avg_grade'] }}%; background: {{ $performance['avg_grade'] >= 80 ? 'var(--gradient-success)' : ($performance['avg_grade'] >= 60 ? 'var(--gradient-warning)' : 'var(--color-danger)') }};">
                            </div>
                        </div>
                        <small class="text-muted">{{ $performance['students_count'] }} students</small>
                    </div>
                @empty
                    <p class="text-muted text-center">No performance data yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Auto-refresh dashboard data every 5 minutes
setInterval(function() {
    $.get('{{ route("teacher.dashboard") }}?ajax=1', function(data) {
        if (data.pendingGradingCount !== undefined) {
            // Update pending grading count
            $('.card-body h5:contains("' + {{ $pendingGradingCount }} + '")').text(data.pendingGradingCount);
        }
    });
}, 300000);
</script>
@endpush