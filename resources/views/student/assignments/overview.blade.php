@extends('layouts.app')

@section('title', 'My Assignments')

@push('styles')
@vite([
    'resources/css/design-system.css',
    'resources/css/creative-professional.css'
])
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="creative-page-header fade-in-up">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.dashboard') }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item active" style="color: #764ba2;">My Assignments</li>
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
            <h1><i class="fas fa-clipboard-list me-2"></i>My Assignments</h1>
            <p class="text-muted">Overview of all your assignments across all courses</p>
        </div>
    </div>

    <!-- Upcoming Assignments -->
    @if($upcomingAssignments->count() > 0)
        <div class="creative-card mb-4 fade-in-up stagger-1">
            <div class="creative-card-header">
                <h3><i class="fas fa-clock text-primary"></i> Upcoming Assignments</h3>
            </div>
            <div class="creative-card-body p-0">
                <div class="table-responsive">
                    <table class="creative-table">
                        <thead>
                            <tr>
                                <th>Assignment</th>
                                <th>Course</th>
                                <th>Due Date</th>
                                <th>Max Score</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($upcomingAssignments as $assignment)
                                <tr>
                                    <td>
                                        <strong>{{ $assignment->title }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $assignment->subpage->title }}</small>
                                    </td>
                                    <td>{{ $assignment->course->title }}</td>
                                    <td>
                                        {{ $assignment->due_date->format('M j, Y g:i A') }}
                                        <br>
                                        <small class="text-muted">{{ $assignment->due_date->diffForHumans() }}</small>
                                    </td>
                                    <td>{{ $assignment->max_score }} pts</td>
                                    <td>
                                        <a href="{{ route('student.courses.modules.subpages.assignments.show', [$assignment->course, $assignment->module, $assignment->subpage, $assignment]) }}" 
                                           class="creative-btn creative-btn-primary creative-btn-sm">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Overdue Assignments -->
    @if($overdueAssignments->count() > 0)
        <div class="creative-card mb-4 fade-in-up stagger-2">
            <div class="creative-card-header">
                <h3><i class="fas fa-exclamation-triangle text-danger"></i> Overdue Assignments</h3>
            </div>
            <div class="creative-card-body p-0">
                <div class="table-responsive">
                    <table class="creative-table">
                        <thead>
                            <tr>
                                <th>Assignment</th>
                                <th>Course</th>
                                <th>Due Date</th>
                                <th>Max Score</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($overdueAssignments as $assignment)
                                <tr class="table-danger">
                                    <td>
                                        <strong>{{ $assignment->title }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $assignment->subpage->title }}</small>
                                    </td>
                                    <td>{{ $assignment->course->title }}</td>
                                    <td>
                                        {{ $assignment->due_date->format('M j, Y g:i A') }}
                                        <br>
                                        <small class="text-danger">{{ $assignment->due_date->diffForHumans() }}</small>
                                    </td>
                                    <td>{{ $assignment->max_score }} pts</td>
                                    <td>
                                        <a href="{{ route('student.courses.modules.subpages.assignments.show', [$assignment->course, $assignment->module, $assignment->subpage, $assignment]) }}" 
                                           class="creative-btn creative-btn-danger creative-btn-sm">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Submitted Assignments -->
    @if($submittedAssignments->count() > 0)
        <div class="creative-card mb-4 fade-in-up stagger-3">
            <div class="creative-card-header">
                <h3><i class="fas fa-check-circle text-warning"></i> Submitted (Pending Grading)</h3>
            </div>
            <div class="creative-card-body p-0">
                <div class="table-responsive">
                    <table class="creative-table">
                        <thead>
                            <tr>
                                <th>Assignment</th>
                                <th>Course</th>
                                <th>Submitted</th>
                                <th>Max Score</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($submittedAssignments as $assignment)
                                @php $submission = $assignment->submissionFor(auth()->user()); @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $assignment->title }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $assignment->subpage->title }}</small>
                                    </td>
                                    <td>{{ $assignment->course->title }}</td>
                                    <td>
                                        {{ $submission->submitted_at->format('M j, Y g:i A') }}
                                        @if($submission->is_late)
                                            <br><span class="creative-badge creative-badge-danger">Late</span>
                                        @endif
                                    </td>
                                    <td>{{ $assignment->max_score }} pts</td>
                                    <td>
                                        <a href="{{ route('student.courses.modules.subpages.assignments.show', [$assignment->course, $assignment->module, $assignment->subpage, $assignment]) }}" 
                                           class="creative-btn creative-btn-outline creative-btn-sm">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Completed Assignments -->
    @if($completedAssignments->count() > 0)
        <div class="creative-card mb-4 fade-in-up stagger-4">
            <div class="creative-card-header">
                <h3><i class="fas fa-star text-success"></i> Completed (Graded)</h3>
            </div>
            <div class="creative-card-body p-0">
                <div class="table-responsive">
                    <table class="creative-table">
                        <thead>
                            <tr>
                                <th>Assignment</th>
                                <th>Course</th>
                                <th>Submitted</th>
                                <th>Score</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($completedAssignments as $assignment)
                                @php 
                                    $submission = $assignment->submissionFor(auth()->user());
                                    $grade = $submission->grade;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $assignment->title }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $assignment->subpage->title }}</small>
                                    </td>
                                    <td>{{ $assignment->course->title }}</td>
                                    <td>{{ $submission->submitted_at->format('M j, Y') }}</td>
                                    <td>
                                        <strong class="text-{{ $grade->score >= ($assignment->max_score * 0.7) ? 'success' : 'danger' }}">
                                            {{ $grade->score }} / {{ $assignment->max_score }}
                                        </strong>
                                        <br>
                                        <small class="text-muted">{{ $grade->getLetterGrade() }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('student.courses.modules.subpages.assignments.show', [$assignment->course, $assignment->module, $assignment->subpage, $assignment]) }}" 
                                           class="creative-btn creative-btn-success creative-btn-sm">
                                            <i class="fas fa-eye me-1"></i>View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Empty State -->
    @if($upcomingAssignments->count() == 0 && $overdueAssignments->count() == 0 && $submittedAssignments->count() == 0 && $completedAssignments->count() == 0)
        <div class="creative-card text-center py-5 fade-in-up">
            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No assignments yet</h4>
            <p class="text-muted">You don't have any assignments at the moment.</p>
        </div>
    @endif
</div>
@endsection
