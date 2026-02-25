@extends('layouts.app')

@section('title', 'Assignments - ' . $subpage->title)

@push('styles')
@vite([
    'resources/css/design-system.css',
    'resources/css/creative-professional.css'
])
@endpush

@section('content')
<div class="container-fluid">
    <!-- Page Header with Breadcrumb -->
    <div class="creative-page-header fade-in-up d-flex justify-content-between align-items-start">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.dashboard') }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.courses.show', $course) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            {{ $course->title }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.courses.module', [$course, $module]) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            {{ $module->title }}
                        </a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.courses.modules.subpages.show', [$course, $module, $subpage]) }}" style="text-decoration: none; color: #667eea; transition: all 0.2s;">
                            {{ $subpage->title }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active" style="color: #764ba2;">Assignments</li>
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
            <h1><i class="fas fa-clipboard-list me-2"></i>Assignments</h1>
            <p>{{ $subpage->title }} - {{ $module->title }}</p>
        </div>
        <a href="{{ route('student.dashboard') }}" class="creative-btn creative-btn-outline">
            <i class="fas fa-arrow-left me-2"></i>Dashboard
        </a>
    </div>

    @if($assignments->count() > 0)
        <div class="creative-card fade-in-up stagger-1">
            <div class="creative-card-body p-0">
                <div class="table-responsive">
                    <table class="creative-table">
                        <thead>
                            <tr>
                                <th>Assignment</th>
                                <th>Type</th>
                                <th>Due Date</th>
                                <th>Max Score</th>
                                <th>Status</th>
                                <th>Your Score</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $assignment)
                                @php 
                                    $submission = $assignment->submissions->first();
                                    $isOverdue = $assignment->isOverdue();
                                @endphp
                                <tr class="{{ $isOverdue && !$submission ? 'table-warning' : '' }}">
                                    <td>
                                        <div>
                                            <h6 class="mb-1">{{ $assignment->title }}</h6>
                                            @if($assignment->description)
                                                <small class="text-muted">{{ Str::limit($assignment->description, 80) }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="creative-badge creative-badge-secondary">{{ ucfirst($assignment->assignment_type) }}</span>
                                        <br>
                                        <small class="text-muted">{{ ucfirst($assignment->submission_type) }}</small>
                                    </td>
                                    <td>
                                        @if($assignment->due_date)
                                            <div>{{ $assignment->due_date->format('M j, Y') }}</div>
                                            <small class="text-muted">{{ $assignment->due_date->format('g:i A') }}</small>
                                            @if($isOverdue)
                                                <br><small class="text-danger">{{ $assignment->due_date->diffForHumans() }}</small>
                                            @else
                                                <br><small class="text-muted">{{ $assignment->due_date->diffForHumans() }}</small>
                                            @endif
                                        @else
                                            <span class="text-muted">No due date</span>
                                        @endif
                                    </td>
                                    <td>{{ $assignment->max_score }} pts</td>
                                    <td>
                                        @if($submission)
                                            @if($submission->grade && $submission->grade->is_published)
                                                <span class="creative-badge creative-badge-success">
                                                    <i class="fas fa-check-circle me-1"></i>Graded
                                                </span>
                                            @else
                                                <span class="creative-badge creative-badge-warning">
                                                    <i class="fas fa-clock me-1"></i>Submitted
                                                </span>
                                            @endif
                                            @if($submission->is_late)
                                                <br><span class="creative-badge creative-badge-danger">Late</span>
                                            @endif
                                        @elseif($isOverdue && !$assignment->allow_late_submission)
                                            <span class="creative-badge creative-badge-danger">
                                                <i class="fas fa-times-circle me-1"></i>Overdue
                                            </span>
                                        @else
                                            <span class="creative-badge creative-badge-secondary">
                                                <i class="fas fa-circle me-1"></i>Not Submitted
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($submission && $submission->grade && $submission->grade->is_published)
                                            <strong class="text-{{ $submission->grade->score >= ($assignment->max_score * 0.7) ? 'success' : 'danger' }}">
                                                {{ $submission->grade->score }} / {{ $assignment->max_score }}
                                            </strong>
                                            <br>
                                            <small class="text-muted">{{ $submission->grade->getLetterGrade() }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('student.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment]) }}" 
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
    @else
        <div class="creative-card text-center py-5 fade-in-up">
            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No assignments yet</h4>
            <p class="text-muted">There are no assignments for this section yet.</p>
            <a href="{{ route('student.courses.modules.subpages.show', [$course, $module, $subpage]) }}" 
               class="creative-btn creative-btn-outline">
                <i class="fas fa-arrow-left me-2"></i>Back to {{ $subpage->title }}
            </a>
        </div>
    @endif
</div>
@endsection
