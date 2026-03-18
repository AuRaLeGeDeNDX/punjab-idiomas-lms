@extends('layouts.app')

@section('title', 'Student Dashboard')

@section('nav-items')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('student.courses.enrolled') }}">My Courses</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('student.courses.index') }}">Browse Courses</a>
    </li>
@endsection

@section('sidebar')
    @include('student.sidebar')
@endsection

@section('content')
<!-- Page Header -->
<div class="creative-page-header fade-in-up">
    <h1><i class="fas fa-user-graduate me-2"></i>Welcome back, {{ auth()->user()->name }}!</h1>
    <p>Here's your learning progress and upcoming assignments.</p>
</div>

<!-- Quick Stats -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="creative-stat-card fade-in-up stagger-1">
        <div class="icon"><i class="fas fa-book"></i></div>
        <div class="number">{{ $enrolledCoursesCount }}</div>
        <div class="label">Enrolled Courses</div>
    </div>
    
    <div class="creative-stat-card variant-warning fade-in-up stagger-2">
        <div class="icon"><i class="fas fa-clipboard-list"></i></div>
        <div class="number">{{ $pendingAssignmentsCount }}</div>
        <div class="label">Pending Assignments</div>
    </div>
    
    <div class="creative-stat-card variant-info fade-in-up stagger-3">
        <div class="icon"><i class="fas fa-clock"></i></div>
        <div class="number">{{ $overdueAssignmentsCount }}</div>
        <div class="label">Overdue</div>
        @if($overdueAssignmentsCount > 0)
            <div class="subtitle" style="color: var(--color-danger);">Needs attention</div>
        @endif
    </div>
    
    <div class="creative-stat-card variant-success fade-in-up stagger-4">
        <div class="icon"><i class="fas fa-star"></i></div>
        <div class="number">{{ number_format($averageGrade, 1) }}%</div>
        <div class="label">Average Grade</div>
    </div>
</div>

<div class="row">
    <!-- My Courses -->
    <div class="col-lg-8">
        <div class="creative-card mb-4">
            <div class="creative-card-header">
                <h3 class="mb-0"><i class="fas fa-book me-2"></i>My Courses</h3>
                <a href="{{ route('student.courses.enrolled') }}" class="creative-btn creative-btn-outline-primary creative-btn-sm">View All</a>
            </div>
            <div class="creative-card-body">
                @forelse($enrolledCourses as $enrollment)
                    <div class="d-flex align-items-center mb-3 p-3 border rounded responsive-card-list-item">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">
                                <a href="{{ route('student.courses.show', $enrollment->course) }}" class="text-decoration-none">
                                    {{ $enrollment->course->title }}
                                </a>
                            </h6>
                            <p class="text-muted mb-1">{{ Str::limit($enrollment->course->description, 80) }}</p>
                            <small class="text-muted d-block mb-2">
                                <i class="fas fa-calendar-alt me-1"></i>Enrolled: {{ $enrollment->enrolled_at->format('M j, Y') }}
                            </small>
                            <div class="progress" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar" role="progressbar" 
                                     style="width: {{ $enrollment->progress_data['overall_progress'] ?? 0 }}%; background: var(--gradient-primary);">
                                </div>
                            </div>
                            <small class="text-muted mt-1 d-block">{{ round($enrollment->progress_data['overall_progress'] ?? 0) }}% complete</small>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('student.courses.show', $enrollment->course) }}" 
                               class="creative-btn creative-btn-primary w-100">Continue</a>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4">
                        <i class="fas fa-book fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No courses assigned</h5>
                        <p class="text-muted">Contact your teacher or administrator to be assigned to courses.</p>
                        <a href="{{ route('student.courses.index') }}" class="creative-btn creative-btn-outline">Browse Available Courses</a>
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

        <!-- Upcoming Assignments -->
        <div class="creative-card mb-4">
            <div class="creative-card-header">
                <h3><i class="fas fa-calendar-alt"></i> Upcoming Assignments</h3>
            </div>
            <div class="creative-card-body">
                @forelse($upcomingAssignments as $assignment)
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 border-start border-3 {{ $assignment->isOverdue() ? 'border-danger' : 'border-warning' }} rounded responsive-card-list-item" style="background: {{ $assignment->isOverdue() ? 'rgba(239, 68, 68, 0.03)' : 'rgba(245, 158, 11, 0.03)' }};">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $assignment->title }}</h6>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <i class="fas fa-book text-muted small"></i>
                                <span class="small fw-medium">{{ $assignment->course->title }}</span>
                            </div>
                            <div class="small {{ $assignment->isOverdue() ? 'text-danger fw-bold' : 'text-warning' }}">
                                <i class="fas fa-calendar-alt me-1"></i>Due: {{ $assignment->due_date ? $assignment->due_date->format('M j, g:i A') : 'No due date' }}
                            </div>
                        </div>
                        <div class="text-end">
                            <a href="{{ route('student.courses.modules.subpages.assignments.show', [$assignment->course, $assignment->module, $assignment->subpage, $assignment]) }}" 
                               class="creative-btn {{ $assignment->isOverdue() ? 'creative-btn-outline-danger' : 'creative-btn-outline-warning' }} creative-btn-sm w-100">View</a>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center">No upcoming assignments</p>
                @endforelse
            </div>
        </div>

        <!-- Recent Grades -->
        <div class="creative-card">
            <div class="creative-card-header">
                <h3><i class="fas fa-chart-bar"></i> Recent Grades</h3>
            </div>
            <div class="creative-card-body">
                @forelse($recentGrades as $grade)
                    @if($grade->submission && $grade->submission->assignment)
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h6 class="mb-1">{{ $grade->submission->assignment->title }}</h6>
                            <small class="text-muted">{{ $grade->submission->assignment->course?->title ?? 'Unknown Course' }}</small>
                        </div>
                        <div class="text-end">
                            <div class="h5 mb-0 text-{{ $grade->isPassing() ? 'success' : 'danger' }}">
                                {{ $grade->score }}/{{ $grade->submission->assignment->max_score }}
                            </div>
                            <small class="text-muted">{{ $grade->getLetterGrade() }}</small>
                        </div>
                    </div>
                    @endif
                @empty
                    <p class="text-muted text-center">No grades yet</p>
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
    // Refresh notification count and recent activities
    $.get('{{ route("student.dashboard") }}?ajax=1', function(data) {
        // Update notification badge if needed
        if (data.notificationCount !== undefined) {
            $('.notification-badge .badge').text(data.notificationCount);
        }
    });
}, 300000);
</script>
@endpush