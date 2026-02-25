@extends('layouts.app')

@section('title', $exercise->title . ' - Exercise Management')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('teacher.courses.index') }}">Courses</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('teacher.courses.show', $course) }}">{{ $course->title }}</a>
            </li>
            <li class="breadcrumb-item">
                <a href="{{ route('teacher.courses.modules.subpages.show', [$course, $module, $subpage]) }}">
                    {{ $subpage->title }}
                </a>
            </li>
            <li class="breadcrumb-item active">{{ $exercise->title }}</li>
        </ol>
    </nav>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">{{ $exercise->title }}</h1>
            <div class="text-muted">
                <span class="badge badge-{{ $exercise->is_active ? 'success' : 'secondary' }}">
                    {{ $exercise->is_active ? 'Active' : 'Inactive' }}
                </span>
                <span class="ml-2">Max Score: {{ $exercise->max_score }} points</span>
                @if($exercise->due_date)
                    <span class="ml-2">Due: {{ $exercise->due_date->format('M j, Y g:i A') }}</span>
                @endif
            </div>
        </div>
        <div>
            <a href="{{ route('teacher.courses.modules.subpages.exercises.edit', [$course, $module, $subpage, $exercise]) }}" 
               class="btn btn-outline-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <a href="{{ route('teacher.courses.modules.subpages.exercises.submissions.index', [$course, $module, $subpage, $exercise]) }}" 
               class="btn btn-primary">
                <i class="fas fa-list"></i> View Submissions ({{ $stats['total_submissions'] }})
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Exercise Content -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">Exercise Details</h5>
                </div>
                <div class="card-body">
                    @if($exercise->description)
                        <div class="mb-4">
                            <h6>Description:</h6>
                            <p class="text-muted">{{ $exercise->description }}</p>
                        </div>
                    @endif

                    <div class="mb-4">
                        <h6>Question:</h6>
                        <div class="p-3 bg-light rounded">
                            {!! nl2br(e($exercise->question)) !!}
                        </div>
                    </div>

                    <!-- TEACHER/ADMIN VIEW: Answer is visible -->
                    <div class="mb-4">
                        <h6>Answer: <span class="badge badge-warning">Teacher Only</span></h6>
                        <div class="p-3 bg-warning-light rounded border-left border-warning">
                            {!! nl2br(e($exercise->answer)) !!}
                        </div>
                    </div>

                    @if($exercise->instructions)
                        <div class="mb-3">
                            <h6>Instructions:</h6>
                            <div class="alert alert-info">
                                {!! nl2br(e($exercise->instructions)) !!}
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Submissions -->
            @if($exercise->submissions->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Submissions</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Submitted</th>
                                        <th>Status</th>
                                        <th>Score</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($exercise->submissions->take(10) as $submission)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-2">
                                                        {{ substr($submission->user->name, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-medium">{{ $submission->user->name }}</div>
                                                        <small class="text-muted">{{ $submission->user->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                {{ $submission->submitted_at->format('M j, g:i A') }}
                                                @if($submission->isLate())
                                                    <span class="badge badge-warning badge-sm ml-1">Late</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($submission->status === 'graded')
                                                    <span class="badge badge-success">Graded</span>
                                                @else
                                                    <span class="badge badge-warning">Pending</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($submission->score !== null)
                                                    <span class="font-weight-medium">
                                                        {{ $submission->score }}/{{ $exercise->max_score }}
                                                    </span>
                                                    <small class="text-muted">
                                                        ({{ round($submission->score_percentage, 1) }}%)
                                                    </small>
                                                @else
                                                    <span class="text-muted">Not graded</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('teacher.courses.modules.subpages.exercises.submissions.show', [$course, $module, $subpage, $exercise, $submission]) }}" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($exercise->submissions->count() > 10)
                            <div class="card-footer text-center">
                                <a href="{{ route('teacher.courses.modules.subpages.exercises.submissions.index', [$course, $module, $subpage, $exercise]) }}" 
                                   class="btn btn-outline-primary">
                                    View All {{ $exercise->submissions->count() }} Submissions
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No submissions yet</h5>
                        <p class="text-muted">Students haven't submitted any responses to this exercise.</p>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Statistics -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-bar"></i> Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 mb-0 text-primary">{{ $stats['total_submissions'] }}</div>
                            <small class="text-muted">Total Submissions</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0 text-success">{{ $stats['graded_submissions'] }}</div>
                            <small class="text-muted">Graded</small>
                        </div>
                    </div>
                    <hr>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 mb-0 text-warning">{{ $stats['pending_submissions'] }}</div>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-0 text-info">
                                {{ $stats['average_score'] ? round($stats['average_score'], 1) : 'N/A' }}
                            </div>
                            <small class="text-muted">Avg Score</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Exercise Settings -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-cog"></i> Settings
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Submission Type:</strong>
                        <span class="badge badge-info">
                            {{ ucfirst(str_replace('_', ' ', $exercise->submission_type)) }}
                        </span>
                    </div>
                    <div class="mb-2">
                        <strong>Order:</strong> {{ $exercise->order_index }}
                    </div>
                    <div class="mb-2">
                        <strong>Created:</strong>
                        {{ $exercise->created_at->format('M j, Y') }}
                    </div>
                    <div>
                        <strong>Last Updated:</strong>
                        {{ $exercise->updated_at->format('M j, Y') }}
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-outline-{{ $exercise->is_active ? 'warning' : 'success' }} btn-sm toggle-active-btn" 
                                data-id="{{ $exercise->id }}">
                            <i class="fas fa-{{ $exercise->is_active ? 'eye-slash' : 'eye' }}"></i>
                            {{ $exercise->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        
                        @if($stats['total_submissions'] > 0)
                            <a href="{{ route('teacher.courses.modules.subpages.exercises.export', [$course, $module, $subpage, $exercise]) }}" 
                               class="btn btn-outline-info btn-sm">
                                <i class="fas fa-download"></i> Export Results
                            </a>
                        @endif
                        
                        <a href="{{ route('teacher.courses.modules.subpages.exercises.edit', [$course, $module, $subpage, $exercise]) }}" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit Exercise
                        </a>
                        
                        <button class="btn btn-outline-danger btn-sm delete-btn" 
                                data-id="{{ $exercise->id }}">
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this exercise?</p>
                <p class="text-danger"><strong>Warning:</strong> This will also delete all student submissions.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle active status
    document.querySelector('.toggle-active-btn')?.addEventListener('click', function() {
        const exerciseId = this.dataset.id;
        
        fetch(`{{ route('teacher.courses.modules.subpages.exercises.index', [$course, $module, $subpage]) }}/${exerciseId}/toggle-active`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    });

    // Delete functionality
    document.querySelector('.delete-btn')?.addEventListener('click', function() {
        const exerciseId = this.dataset.id;
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `{{ route('teacher.courses.modules.subpages.exercises.index', [$course, $module, $subpage]) }}/${exerciseId}`;
        $('#deleteModal').modal('show');
    });
});
</script>

<style>
.bg-warning-light {
    background-color: #fff3cd !important;
}
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
}
</style>
@endpush