@extends('layouts.app')

@section('title', 'Grading Queue')

@section('nav-items')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('teacher.courses.index') }}">My Courses</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('teacher.progress.index') }}">Student Progress</a>
    </li>
@endsection

@section('sidebar')
    @include('teacher.sidebar')
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-check me-2"></i>Grading Queue
            </h1>
            <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                <button type="button" class="creative-btn creative-btn-outline-primary creative-btn-sm" onclick="refreshQueue()">
                    <i class="fas fa-sync-alt me-1"></i>Refresh
                </button>
                <button type="button" class="creative-btn creative-btn-outline-secondary creative-btn-sm" onclick="showBulkActions()">
                    <i class="fas fa-tasks me-1"></i>Bulk Actions
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4 stats-grid-2">
    <div class="col-md-3">
        <div class="creative-card text-center h-100">
            <div class="creative-card-body p-3">
                <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                <h4 class="text-warning mb-0">{{ $stats['total_pending'] }}</h4>
                <p class="small text-muted mb-0">Pending</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="creative-card text-center h-100">
            <div class="creative-card-body p-3">
                <i class="fas fa-exclamation-triangle fa-2x text-danger mb-2"></i>
                <h4 class="text-danger mb-0">{{ $stats['overdue_submissions'] }}</h4>
                <p class="small text-muted mb-0">Overdue</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="creative-card text-center h-100">
            <div class="creative-card-body p-3">
                <i class="fas fa-calendar-week fa-2x text-info mb-2"></i>
                <h4 class="text-info mb-0">{{ $stats['due_this_week'] }}</h4>
                <p class="small text-muted mb-0">This Week</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="creative-card text-center h-100">
            <div class="creative-card-body p-3">
                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                <h4 class="text-success mb-0">{{ $stats['graded_today'] }}</h4>
                <p class="small text-muted mb-0">Graded Today</p>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('teacher.grading.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="course_id" class="form-label">Course</label>
                <select name="course_id" id="course_id" class="form-select">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ $courseId == $course->id ? 'selected' : '' }}>
                            {{ $course->title }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="graded" {{ $status === 'graded' ? 'selected' : '' }}>Graded</option>
                    <option value="late" {{ $status === 'late' ? 'selected' : '' }}>Late</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="priority" class="form-label">Priority</label>
                <select name="priority" id="priority" class="form-select">
                    <option value="all" {{ $priority === 'all' ? 'selected' : '' }}>All</option>
                    <option value="overdue" {{ $priority === 'overdue' ? 'selected' : '' }}>Overdue</option>
                    <option value="due_soon" {{ $priority === 'due_soon' ? 'selected' : '' }}>Due Soon</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="sort_by" class="form-label">Sort By</label>
                <select name="sort_by" id="sort_by" class="form-select">
                    <option value="submitted_at" {{ $sortBy === 'submitted_at' ? 'selected' : '' }}>Submitted</option>
                    <option value="due_date" {{ $sortBy === 'due_date' ? 'selected' : '' }}>Due Date</option>
                    <option value="course" {{ $sortBy === 'course' ? 'selected' : '' }}>Course</option>
                    <option value="student" {{ $sortBy === 'student' ? 'selected' : '' }}>Student</option>
                </select>
            </div>
            <div class="col-md-1">
                <label for="sort_order" class="form-label">Order</label>
                <select name="sort_order" id="sort_order" class="form-select">
                    <option value="asc" {{ $sortOrder === 'asc' ? 'selected' : '' }}>Asc</option>
                    <option value="desc" {{ $sortOrder === 'desc' ? 'selected' : '' }}>Desc</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="creative-btn creative-btn-primary">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Submissions List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Submissions to Grade</h5>
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleSelectAll()">
            <label class="form-check-label" for="selectAll">
                Select All
            </label>
        </div>
    </div>
    <div class="card-body p-0">
        @forelse($submissions as $submission)
            <div class="submission-item border-bottom p-3 {{ $submission->is_late ? 'border-start border-danger border-3' : '' }} responsive-card-list-item">
                <div class="row align-items-center">
                    <div class="col-md-1 d-none d-md-block">
                        <div class="form-check">
                            <input class="form-check-input submission-checkbox" type="checkbox" 
                                   value="{{ $submission->id }}" id="submission_{{ $submission->id }}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <h6 class="mb-1">{{ $submission->assignment->title }}</h6>
                        <small class="text-muted d-block">{{ $submission->assignment->course->title }}</small>
                        @if($submission->is_late)
                            <span class="creative-badge creative-badge-danger creative-badge-sm mt-1">Late</span>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex align-items-center mt-2 mt-md-0">
                            <img src="{{ $submission->user->avatar ?? '/images/default-avatar.png' }}" 
                                 alt="{{ $submission->user->name }}" class="rounded-circle me-2" width="28" height="28">
                            <div>
                                <div class="fw-medium small">{{ $submission->user->name }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted small mt-2 mt-md-0">
                            <span class="d-md-none">Submitted: </span>
                            <span class="fw-medium text-dark">{{ $submission->submitted_at->format('M j, g:i A') }}</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted small mt-1 mt-md-0">
                            <span class="d-md-none">Due: </span>
                            <span class="fw-medium {{ $submission->assignment->due_date->isPast() ? 'text-danger' : 'text-dark' }}">
                                {{ $submission->assignment->due_date->format('M j, g:i A') }}
                            </span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="d-flex gap-2 mt-3 mt-md-0">
                            <a href="{{ route('teacher.grading.show', $submission) }}" 
                               class="creative-btn creative-btn-primary creative-btn-sm flex-fill">
                                <i class="fas fa-edit me-1"></i>Grade
                            </a>
                            @if($submission->files->count() > 0)
                                <button class="creative-btn creative-btn-outline-secondary creative-btn-sm" onclick="viewFiles({{ $submission->id }})">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No submissions to grade</h5>
                <p class="text-muted">All caught up! Check back later for new submissions.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Pagination -->
@if($submissions->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $submissions->appends(request()->query())->links() }}
    </div>
@endif

<!-- Bulk Actions Modal -->
<div class="modal fade" id="bulkActionsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bulk Actions</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="bulkActionsForm">
                    <div class="mb-3">
                        <label for="bulkAction" class="form-label">Action</label>
                        <select class="form-select" id="bulkAction" name="action" required>
                            <option value="">Select Action</option>
                            <option value="grade">Apply Grade</option>
                            <option value="return">Return to Students</option>
                            <option value="extend_deadline">Extend Deadline</option>
                        </select>
                    </div>
                    
                    <div id="gradeFields" class="d-none">
                        <div class="mb-3">
                            <label for="bulkScore" class="form-label">Score</label>
                            <input type="number" class="form-control" id="bulkScore" name="score" min="0" max="100">
                        </div>
                        <div class="mb-3">
                            <label for="bulkFeedback" class="form-label">Feedback</label>
                            <textarea class="form-control" id="bulkFeedback" name="feedback" rows="3"></textarea>
                        </div>
                    </div>
                    
                    <div id="extensionFields" class="d-none">
                        <div class="mb-3">
                            <label for="daysExtension" class="form-label">Days to Extend</label>
                            <input type="number" class="form-control" id="daysExtension" name="days_extension" min="1" max="30">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="executeBulkAction()">Apply Action</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function refreshQueue() {
    location.reload();
}

function showBulkActions() {
    const selected = getSelectedSubmissions();
    if (selected.length === 0) {
        alert('Please select at least one submission.');
        return;
    }
    
    $('#bulkActionsModal').modal('show');
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.submission-checkbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = selectAll.checked;
    });
}

function getSelectedSubmissions() {
    return Array.from(document.querySelectorAll('.submission-checkbox:checked'))
        .map(checkbox => checkbox.value);
}

function executeBulkAction() {
    const selected = getSelectedSubmissions();
    const formData = new FormData(document.getElementById('bulkActionsForm'));
    
    if (selected.length === 0) {
        alert('Please select at least one submission.');
        return;
    }
    
    formData.append('submission_ids', JSON.stringify(selected));
    
    fetch('{{ route("teacher.grading.bulk") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.message) {
            alert(data.message);
            location.reload();
        } else {
            alert('Action failed: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while processing the action.');
    });
    
    $('#bulkActionsModal').modal('hide');
}

function viewFiles(submissionId) {
    // Implementation for viewing submission files
    window.open(`/teacher/submissions/${submissionId}/files`, '_blank');
}

// Show/hide fields based on selected action
document.getElementById('bulkAction').addEventListener('change', function() {
    const action = this.value;
    const gradeFields = document.getElementById('gradeFields');
    const extensionFields = document.getElementById('extensionFields');
    
    gradeFields.classList.add('d-none');
    extensionFields.classList.add('d-none');
    
    if (action === 'grade') {
        gradeFields.classList.remove('d-none');
    } else if (action === 'extend_deadline') {
        extensionFields.classList.remove('d-none');
    }
});

// Auto-refresh every 5 minutes
setInterval(function() {
    if (document.visibilityState === 'visible') {
        fetch('{{ route("teacher.grading.index") }}?ajax=1')
            .then(response => response.json())
            .then(data => {
                // Update stats without full page reload
                if (data.stats) {
                    // Update statistics cards
                    document.querySelector('.text-warning h4').textContent = data.stats.total_pending;
                    document.querySelector('.text-danger h4').textContent = data.stats.overdue_submissions;
                    document.querySelector('.text-info h4').textContent = data.stats.due_this_week;
                    document.querySelector('.text-success h4').textContent = data.stats.graded_today;
                }
            })
            .catch(error => console.error('Auto-refresh error:', error));
    }
}, 300000); // 5 minutes
</script>
@endpush