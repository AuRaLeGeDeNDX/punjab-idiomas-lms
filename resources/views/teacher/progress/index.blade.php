@extends('layouts.app')

@section('title', 'Student Progress')

@section('nav-items')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('teacher.courses.index') }}">My Courses</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('teacher.grading.index') }}">Grading Queue</a>
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
                <i class="fas fa-users me-2"></i>Student Progress
            </h1>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary" onclick="refreshProgress()">
                    <i class="fas fa-sync-alt me-1"></i>Refresh
                </button>
                <button type="button" class="btn btn-outline-success" onclick="exportProgress()">
                    <i class="fas fa-download me-1"></i>Export
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                <h4 class="text-primary">{{ $stats['total_students'] }}</h4>
                <p class="card-text">Total Students</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                <h4 class="text-success">{{ $stats['active_students'] }}</h4>
                <p class="card-text">Active Students</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-chart-line fa-2x text-info mb-2"></i>
                <h4 class="text-info">{{ $stats['average_progress'] }}%</h4>
                <p class="card-text">Average Progress</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-trophy fa-2x text-warning mb-2"></i>
                <h4 class="text-warning">{{ $stats['excellent_students'] }}</h4>
                <p class="card-text">Excellent (90%+)</p>
            </div>
        </div>
    </div>
</div>

<!-- Performance Overview -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="text-success">{{ $stats['excellent_students'] }}</h5>
                <p class="text-muted mb-0">Excellent Students</p>
                <small class="text-muted">90%+ Progress</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="text-warning">{{ $stats['good_students'] }}</h5>
                <p class="text-muted mb-0">Good Students</p>
                <small class="text-muted">70-89% Progress</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body text-center">
                <h5 class="text-danger">{{ $stats['struggling_students'] }}</h5>
                <p class="text-muted mb-0">Struggling Students</p>
                <small class="text-muted">Below 50% Progress</small>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Search -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('teacher.progress.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="course_id" class="form-label">Course</label>
                <select name="course_id" id="course_id" class="form-select">
                    <option value="">All Courses</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ $courseId == $course->id ? 'selected' : '' }}>
                            {{ $course->title }} ({{ $course->enrollments_count }} students)
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="completed" {{ $status === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="dropped" {{ $status === 'dropped' ? 'selected' : '' }}>Dropped</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="sort_by" class="form-label">Sort By</label>
                <select name="sort_by" id="sort_by" class="form-select">
                    <option value="name" {{ $sortBy === 'name' ? 'selected' : '' }}>Name</option>
                    <option value="progress" {{ $sortBy === 'progress' ? 'selected' : '' }}>Progress</option>
                    <option value="course" {{ $sortBy === 'course' ? 'selected' : '' }}>Course</option>
                    <option value="enrolled_at" {{ $sortBy === 'enrolled_at' ? 'selected' : '' }}>Enrolled Date</option>
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
                <label for="search" class="form-label">Search</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ $search }}" placeholder="Student name or email">
            </div>
            <div class="col-md-2">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i>Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Student Progress List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Student Progress Overview</h5>
    </div>
    <div class="card-body p-0">
        @forelse($enrollments as $enrollment)
            <div class="student-progress-item border-bottom p-3">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <div class="d-flex align-items-center">
                            <img src="{{ $enrollment->user->avatar ?? '/images/default-avatar.png' }}" 
                                 alt="{{ $enrollment->user->name }}" class="rounded-circle me-3" width="40" height="40">
                            <div>
                                <h6 class="mb-1">
                                    <a href="{{ route('teacher.progress.show', ['student' => $enrollment->user, 'course_id' => $enrollment->course_id]) }}" 
                                       class="text-decoration-none">
                                        {{ $enrollment->user->name }}
                                    </a>
                                </h6>
                                <small class="text-muted">{{ $enrollment->user->email }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-muted">
                            <small>Course:</small><br>
                            <span class="fw-medium">{{ Str::limit($enrollment->course->title, 20) }}</span>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="progress mb-1" style="height: 8px;">
                                <div class="progress-bar bg-{{ $enrollment->progress_metrics['status'] === 'excellent' ? 'success' : ($enrollment->progress_metrics['status'] === 'behind' ? 'danger' : 'primary') }}" 
                                     role="progressbar" style="width: {{ $enrollment->progress_metrics['completion_rate'] }}%">
                                </div>
                            </div>
                            <small class="fw-medium">{{ $enrollment->progress_metrics['completion_rate'] }}% Complete</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="fw-medium text-{{ $enrollment->progress_metrics['average_grade'] >= 80 ? 'success' : ($enrollment->progress_metrics['average_grade'] >= 60 ? 'warning' : 'danger') }}">
                                {{ $enrollment->progress_metrics['average_grade'] }}%
                            </div>
                            <small class="text-muted">Average Grade</small>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="text-center">
                            <div class="fw-medium">{{ $enrollment->progress_metrics['assignments_completed'] }}/{{ $enrollment->progress_metrics['total_assignments'] }}</div>
                            <small class="text-muted">Assignments</small>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="text-center">
                            @php
                                $statusColor = match($enrollment->progress_metrics['status']) {
                                    'excellent' => 'success',
                                    'behind' => 'danger',
                                    default => 'primary'
                                };
                                $statusIcon = match($enrollment->progress_metrics['status']) {
                                    'excellent' => 'trophy',
                                    'behind' => 'exclamation-triangle',
                                    default => 'user'
                                };
                            @endphp
                            <i class="fas fa-{{ $statusIcon }} text-{{ $statusColor }} fa-lg" 
                               title="{{ ucfirst($enrollment->progress_metrics['status']) }}"></i>
                        </div>
                    </div>
                </div>
                
                <!-- Additional Details (Expandable) -->
                <div class="row mt-2">
                    <div class="col-12">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex gap-3">
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>
                                    Enrolled: {{ $enrollment->enrolled_at->format('M j, Y') }}
                                </small>
                                <small class="text-muted">
                                    <i class="fas fa-clock me-1"></i>
                                    Last Activity: {{ $enrollment->progress_metrics['last_activity'] }}
                                </small>
                                @if($enrollment->progress_metrics['grade_trend'] !== 'insufficient_data')
                                    <small class="text-{{ $enrollment->progress_metrics['grade_trend'] === 'improving' ? 'success' : ($enrollment->progress_metrics['grade_trend'] === 'declining' ? 'danger' : 'muted') }}">
                                        <i class="fas fa-{{ $enrollment->progress_metrics['grade_trend'] === 'improving' ? 'arrow-up' : ($enrollment->progress_metrics['grade_trend'] === 'declining' ? 'arrow-down' : 'minus') }} me-1"></i>
                                        {{ ucfirst($enrollment->progress_metrics['grade_trend']) }}
                                    </small>
                                @endif
                            </div>
                            <div class="btn-group" role="group">
                                <a href="{{ route('teacher.progress.show', ['student' => $enrollment->user, 'course_id' => $enrollment->course_id]) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye me-1"></i>Details
                                </a>
                                <button class="btn btn-sm btn-outline-secondary" onclick="contactStudent({{ $enrollment->user->id }})">
                                    <i class="fas fa-envelope me-1"></i>Contact
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center py-5">
                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No students found</h5>
                <p class="text-muted">No students match the current filters.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- Pagination -->
@if($enrollments->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $enrollments->appends(request()->query())->links() }}
    </div>
@endif

<!-- Export Modal -->
<div class="modal fade" id="exportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Export Progress Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="exportForm">
                    <div class="mb-3">
                        <label for="exportCourse" class="form-label">Course</label>
                        <select class="form-select" id="exportCourse" name="course_id">
                            <option value="">All Courses</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="exportFormat" class="form-label">Format</label>
                        <select class="form-select" id="exportFormat" name="format">
                            <option value="csv">CSV</option>
                            <option value="json">JSON</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" onclick="executeExport()">
                    <i class="fas fa-download me-1"></i>Export
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function refreshProgress() {
    location.reload();
}

function exportProgress() {
    $('#exportModal').modal('show');
}

function executeExport() {
    const form = document.getElementById('exportForm');
    const formData = new FormData(form);
    
    const params = new URLSearchParams();
    for (let [key, value] of formData.entries()) {
        if (value) params.append(key, value);
    }
    
    window.location.href = '{{ route("teacher.progress.export") }}?' + params.toString();
    $('#exportModal').modal('hide');
}

function contactStudent(studentId) {
    // Implementation for contacting student
    // This could open a modal with messaging options or redirect to messaging system
    alert('Contact student functionality would be implemented here.');
}

// Auto-refresh every 10 minutes
setInterval(function() {
    if (document.visibilityState === 'visible') {
        fetch('{{ route("teacher.progress.index") }}?ajax=1')
            .then(response => response.json())
            .then(data => {
                if (data.stats) {
                    // Update statistics cards
                    document.querySelector('.text-primary h4').textContent = data.stats.total_students;
                    document.querySelector('.text-success h4').textContent = data.stats.active_students;
                    document.querySelector('.text-info h4').textContent = data.stats.average_progress + '%';
                    document.querySelector('.text-warning h4').textContent = data.stats.excellent_students;
                }
            })
            .catch(error => console.error('Auto-refresh error:', error));
    }
}, 600000); // 10 minutes

// Search functionality with debounce
let searchTimeout;
document.getElementById('search').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        this.form.submit();
    }, 500);
});
</script>
@endpush