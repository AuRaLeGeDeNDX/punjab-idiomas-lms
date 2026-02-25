@extends('layouts.app')

@section('title', $assignment->title)

@push('styles')
@vite([
    'resources/css/design-system.css',
    'resources/css/creative-professional.css'
])
@endpush

@php
    $routePrefix = auth()->user()->hasRole('Admin') ? 'admin' : 'teacher';
@endphp

@section('content')
<div class="container-fluid">
    <!-- Page Header with Breadcrumb -->
    <div class="creative-page-header fade-in-up">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route($routePrefix . '.dashboard') }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route($routePrefix . '.courses.show', $course) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            {{ $course->title }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('teacher.modules.show', [$course, $module]) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            {{ $module->title }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route($routePrefix . '.courses.modules.subpages.show', [$course, $module, $subpage]) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            {{ $subpage->title }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.index', [$course, $module, $subpage]) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            Assignments
                        </a>
                    </li>
                    <li class="breadcrumb-item active" style="color: #764ba2;">{{ $assignment->title }}</li>
                </ol>
            </nav>
            <style>
                .breadcrumb-item + .breadcrumb-item::before {
                    content: "›";
                    color: #667eea;
                }
                .breadcrumb-item a:hover {
                    color: #764ba2 !important;
                }
            </style>
            <h1><i class="fas fa-clipboard-list me-2"></i>{{ $assignment->title }}</h1>
            <p>{{ $subpage->title }} - {{ $module->title }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Assignment Details Card -->
            <div class="creative-card mb-4 fade-in-up stagger-1">
                <div class="creative-card-header d-flex justify-content-between align-items-center">
                    <h3><i class="fas fa-info-circle"></i> Assignment Details</h3>
                    <div class="btn-group">
                        <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.edit', [$course, $module, $subpage, $assignment]) }}" 
                           class="creative-btn creative-btn-outline creative-btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                        @if(!$assignment->is_published)
                            <form method="POST" 
                                  action="{{ route($routePrefix . '.courses.modules.subpages.assignments.publish', [$course, $module, $subpage, $assignment]) }}" 
                                  class="d-inline">
                                @csrf
                                <button type="submit" class="creative-btn creative-btn-success creative-btn-sm">
                                    <i class="fas fa-paper-plane me-1"></i>Publish
                                </button>
                            </form>
                        @else
                            <form method="POST" 
                                  action="{{ route($routePrefix . '.courses.modules.subpages.assignments.unpublish', [$course, $module, $subpage, $assignment]) }}" 
                                  class="d-inline">
                                @csrf
                                <button type="submit" class="creative-btn creative-btn-warning creative-btn-sm">
                                    <i class="fas fa-eye-slash me-1"></i>Unpublish
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
                <div class="creative-card-body">
                    @if($assignment->description)
                        <div class="mb-4">
                            <h5 class="text-muted mb-2">Description</h5>
                            <p>{{ $assignment->description }}</p>
                        </div>
                    @endif

                    @if($assignment->instructions)
                        <div class="mb-4">
                            <h5 class="text-muted mb-2">Instructions</h5>
                            <div class="p-3 bg-light rounded">
                                {!! nl2br(e($assignment->instructions)) !!}
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <strong>Type:</strong>
                            <span class="creative-badge creative-badge-secondary ms-2">
                                {{ ucfirst($assignment->assignment_type) }}
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Submission Type:</strong>
                            <span class="creative-badge creative-badge-info ms-2">
                                {{ ucfirst($assignment->submission_type) }}
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Max Score:</strong>
                            <span class="ms-2">{{ $assignment->max_score }} points</span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Due Date:</strong>
                            @if($assignment->due_date)
                                <span class="ms-2">
                                    {{ $assignment->due_date->format('M j, Y g:i A') }}
                                    @if($assignment->isOverdue())
                                        <span class="creative-badge creative-badge-danger ms-1">Overdue</span>
                                    @endif
                                </span>
                            @else
                                <span class="text-muted ms-2">No due date</span>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Late Submissions:</strong>
                            <span class="ms-2">
                                @if($assignment->allow_late_submission)
                                    <i class="fas fa-check text-success"></i> Allowed
                                @else
                                    <i class="fas fa-times text-danger"></i> Not Allowed
                                @endif
                            </span>
                        </div>
                        <div class="col-md-6 mb-3">
                            <strong>Auto Grade:</strong>
                            <span class="ms-2">
                                @if($assignment->auto_grade)
                                    <i class="fas fa-check text-success"></i> Enabled
                                @else
                                    <i class="fas fa-times text-muted"></i> Disabled
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submissions Overview -->
            <div class="creative-card fade-in-up stagger-2">
                <div class="creative-card-header d-flex justify-content-between align-items-center">
                    <h3><i class="fas fa-file-upload"></i> Submissions</h3>
                    <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.submissions.index', [$course, $module, $subpage, $assignment]) }}" 
                       class="creative-btn creative-btn-outline creative-btn-sm">
                        <i class="fas fa-list me-1"></i>View All
                    </a>
                </div>
                <div class="creative-card-body">
                    @if($assignment->submissions->count() > 0)
                        <div class="table-responsive">
                            <table class="creative-table">
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Submitted</th>
                                        <th>Status</th>
                                        <th>Grade</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignment->submissions->take(10) as $submission)
                                        <tr>
                                            <td>{{ $submission->user->name }}</td>
                                            <td>
                                                {{ $submission->submitted_at ? $submission->submitted_at->format('M j, Y g:i A') : 'Not submitted' }}
                                            </td>
                                            <td>
                                                @if($submission->submitted_at)
                                                    @if($submission->grade)
                                                        <span class="creative-badge creative-badge-success">Graded</span>
                                                    @else
                                                        <span class="creative-badge creative-badge-warning">Pending Review</span>
                                                    @endif
                                                @else
                                                    <span class="creative-badge creative-badge-secondary">Not Submitted</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($submission->grade)
                                                    <strong>{{ $submission->grade->score }}</strong> / {{ $assignment->max_score }}
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.submissions.show', [$course, $module, $subpage, $assignment, $submission]) }}" 
                                                   class="creative-btn creative-btn-outline creative-btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($assignment->submissions->count() > 10)
                            <div class="text-center mt-3">
                                <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.submissions.index', [$course, $module, $subpage, $assignment]) }}" 
                                   class="creative-btn creative-btn-outline">
                                    View All {{ $assignment->submissions->count() }} Submissions
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No submissions yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status Card -->
            <div class="creative-card mb-4 fade-in-up stagger-3">
                <div class="creative-card-header">
                    <h3><i class="fas fa-chart-bar"></i> Status</h3>
                </div>
                <div class="creative-card-body">
                    <div class="mb-3">
                        <strong>Publication Status:</strong>
                        <div class="mt-2">
                            @if($assignment->is_published)
                                <span class="creative-badge creative-badge-success">
                                    <i class="fas fa-check-circle me-1"></i>Published
                                </span>
                                <br>
                                <small class="text-muted">
                                    Published on {{ $assignment->published_at->format('M j, Y g:i A') }}
                                </small>
                            @elseif($assignment->isScheduled())
                                <span class="creative-badge creative-badge-info">
                                    <i class="fas fa-clock me-1"></i>Scheduled
                                </span>
                                <br>
                                <small class="text-muted">
                                    Will publish on {{ $assignment->scheduled_publish_at->format('M j, Y g:i A') }}
                                </small>
                                <form method="POST" 
                                      action="{{ route($routePrefix . '.courses.modules.subpages.assignments.cancel-schedule', [$course, $module, $subpage, $assignment]) }}" 
                                      class="mt-2">
                                    @csrf
                                    <button type="submit" class="creative-btn creative-btn-warning creative-btn-sm w-100">
                                        <i class="fas fa-times-circle me-1"></i>Cancel Schedule
                                    </button>
                                </form>
                            @else
                                <span class="creative-badge creative-badge-warning">
                                    <i class="fas fa-file me-1"></i>Draft
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="mb-3">
                        <strong>Active Status:</strong>
                        <div class="mt-2">
                            @if($assignment->is_active)
                                <span class="creative-badge creative-badge-success">Active</span>
                            @else
                                <span class="creative-badge creative-badge-secondary">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <strong>Created:</strong>
                        <div class="mt-2">
                            <small class="text-muted">
                                {{ $assignment->created_at->format('M j, Y g:i A') }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="creative-card mb-4 fade-in-up stagger-4">
                <div class="creative-card-header">
                    <h3><i class="fas fa-chart-pie"></i> Statistics</h3>
                </div>
                <div class="creative-card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Total Submissions:</span>
                            <strong class="text-primary">{{ $stats['total_submissions'] }}</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" 
                                 style="width: {{ $stats['total_submissions'] > 0 ? 100 : 0 }}%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Graded:</span>
                            <strong class="text-success">{{ $stats['graded_submissions'] }}</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-success" 
                                 style="width: {{ $stats['total_submissions'] > 0 ? ($stats['graded_submissions'] / $stats['total_submissions'] * 100) : 0 }}%"></div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span>Pending:</span>
                            <strong class="text-warning">{{ $stats['pending_submissions'] }}</strong>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-warning" 
                                 style="width: {{ $stats['total_submissions'] > 0 ? ($stats['pending_submissions'] / $stats['total_submissions'] * 100) : 0 }}%"></div>
                        </div>
                    </div>

                    @if($stats['average_score'] !== null)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>Average Score:</span>
                                <strong>{{ number_format($stats['average_score'], 1) }} / {{ $assignment->max_score }}</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-info" 
                                     style="width: {{ ($stats['average_score'] / $assignment->max_score * 100) }}%"></div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions Card -->
            <div class="creative-card fade-in-up stagger-5">
                <div class="creative-card-header">
                    <h3><i class="fas fa-tools"></i> Actions</h3>
                </div>
                <div class="creative-card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.submissions.index', [$course, $module, $subpage, $assignment]) }}" 
                           class="creative-btn creative-btn-primary">
                            <i class="fas fa-list me-2"></i>View All Submissions
                        </a>
                        
                        @if($assignment->due_date && $assignment->is_published)
                            <form method="POST" 
                                  action="{{ route($routePrefix . '.courses.modules.subpages.assignments.send-reminders', [$course, $module, $subpage, $assignment]) }}">
                                @csrf
                                <button type="submit" class="creative-btn creative-btn-info w-100">
                                    <i class="fas fa-bell me-2"></i>Send Due Date Reminders
                                </button>
                            </form>
                        @endif

                        <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.edit', [$course, $module, $subpage, $assignment]) }}" 
                           class="creative-btn creative-btn-outline">
                            <i class="fas fa-edit me-2"></i>Edit Assignment
                        </a>

                        <form method="POST" 
                              action="{{ route($routePrefix . '.courses.modules.subpages.assignments.destroy', [$course, $module, $subpage, $assignment]) }}"
                              onsubmit="return confirm('Are you sure you want to delete this assignment? This action cannot be undone.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="creative-btn creative-btn-danger w-100">
                                <i class="fas fa-trash me-2"></i>Delete Assignment
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

