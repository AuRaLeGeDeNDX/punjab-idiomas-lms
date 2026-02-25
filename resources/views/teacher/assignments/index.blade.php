@extends('layouts.app')

@section('title', 'Assignments - ' . $subpage->title)

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
        <div class="d-flex justify-content-between align-items-center">
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
            <div class="d-flex gap-2">
                <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.trashed', [$course, $module, $subpage]) }}" 
                   class="creative-btn creative-btn-outline" style="border-color: var(--color-warning); color: var(--color-warning);">
                    <i class="fas fa-trash me-2"></i>View Trash
                </a>
                <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.create', [$course, $module, $subpage]) }}" 
                   class="creative-btn creative-btn-primary">
                    <i class="fas fa-plus me-2"></i>Create Assignment
                </a>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
                                <th>Submissions</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="assignments-table">
                            @foreach($assignments as $assignment)
                                <tr data-assignment-id="{{ $assignment->id }}">
                                    <td>
                                        <div>
                                            <h6 class="mb-1">
                                                <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment]) }}" 
                                                   class="text-decoration-none">
                                                    {{ $assignment->title }}
                                                </a>
                                            </h6>
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
                                            @if($assignment->isOverdue())
                                                <br><small class="text-danger">Overdue</small>
                                            @endif
                                        @else
                                            <span class="text-muted">No due date</span>
                                        @endif
                                    </td>
                                    <td>{{ $assignment->max_score }} pts</td>
                                    <td>
                                        @php $stats = $assignment->getSubmissionStats() @endphp
                                        <div>
                                            <strong>{{ $stats['total_submissions'] }}</strong> submitted
                                        </div>
                                        <small class="text-muted">
                                            {{ $stats['graded_submissions'] }} graded
                                        </small>
                                    </td>
                                    <td>
                                        @if($assignment->is_published)
                                            <span class="creative-badge creative-badge-success">Published</span>
                                        @elseif($assignment->isScheduled())
                                            <span class="creative-badge creative-badge-info">
                                                <i class="fas fa-clock me-1"></i>Scheduled
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                {{ $assignment->scheduled_publish_at->format('M j, g:i A') }}
                                            </small>
                                        @else
                                            <span class="creative-badge creative-badge-warning">Draft</span>
                                        @endif
                                        @if(!$assignment->is_active)
                                            <br><span class="creative-badge creative-badge-secondary">Inactive</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.show', [$course, $module, $subpage, $assignment]) }}" 
                                               class="creative-btn creative-btn-outline" style="padding: 0.5rem 0.75rem;" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.edit', [$course, $module, $subpage, $assignment]) }}" 
                                               class="creative-btn creative-btn-outline" style="padding: 0.5rem 0.75rem;" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @if($assignment->isScheduled())
                                                <form method="POST" 
                                                      action="{{ route($routePrefix . '.courses.modules.subpages.assignments.cancel-schedule', [$course, $module, $subpage, $assignment]) }}" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Cancel scheduled publication?');">
                                                    @csrf
                                                    <button type="submit" class="creative-btn creative-btn-outline" style="padding: 0.5rem 0.75rem; border-color: var(--color-warning); color: var(--color-warning);" title="Cancel Schedule">
                                                        <i class="fas fa-times-circle"></i>
                                                    </button>
                                                </form>
                                            @elseif(!$assignment->is_published)
                                                <form method="POST" 
                                                      action="{{ route($routePrefix . '.courses.modules.subpages.assignments.publish', [$course, $module, $subpage, $assignment]) }}" 
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="creative-btn creative-btn-success" style="padding: 0.5rem 0.75rem;" title="Publish">
                                                        <i class="fas fa-paper-plane"></i>
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
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $assignments->links() }}
        </div>
    @else
        <div class="creative-card text-center py-5 fade-in-up">
            <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
            <h4 class="text-muted">No assignments yet</h4>
            <p class="text-muted mb-4">Create your first assignment to get started.</p>
            <a href="{{ route($routePrefix . '.courses.modules.subpages.assignments.create', [$course, $module, $subpage]) }}" 
               class="creative-btn creative-btn-primary">
                <i class="fas fa-plus me-2"></i>Create Assignment
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
// Add sortable functionality for reordering assignments
$(document).ready(function() {
    $('#assignments-table').sortable({
        handle: '.drag-handle',
        update: function(event, ui) {
            let assignmentIds = [];
            $('#assignments-table tr').each(function() {
                let id = $(this).data('assignment-id');
                if (id) assignmentIds.push(id);
            });

            $.ajax({
                url: '{{ route("teacher.courses.modules.subpages.assignments.reorder", [$course, $module, $subpage]) }}',
                method: 'POST',
                data: {
                    assignment_ids: assignmentIds,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success('Assignment order updated successfully');
                    }
                },
                error: function() {
                    toastr.error('Failed to update assignment order');
                }
            });
        }
    });
});
</script>
@endpush
@endsection