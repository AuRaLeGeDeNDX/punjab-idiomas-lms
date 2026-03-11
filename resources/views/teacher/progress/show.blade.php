@extends('layouts.app')

@section('title', 'Student Progress - ' . $student->name)

@section('nav-items')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('teacher.progress.index') }}">Back to Progress</a>
    </li>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <img src="{{ $student->avatar ?? '/images/default-avatar.png' }}" 
                     alt="{{ $student->name }}" class="rounded-circle me-3" width="48" height="48">
                <div>
                    <h1 class="h3 mb-0">{{ $student->name }}</h1>
                    <p class="text-muted mb-0">{{ $student->email }} • {{ $enrollment->course->title }}</p>
                </div>
            </div>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary" onclick="contactStudent()">
                    <i class="fas fa-envelope me-1"></i>Contact Student
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="exportStudentData()">
                    <i class="fas fa-download me-1"></i>Export Data
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Student Overview Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-percentage fa-2x text-primary mb-2"></i>
                <h4 class="text-primary">{{ $enrollment->progress_percentage ?? 0 }}%</h4>
                <p class="card-text">Overall Progress</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-star fa-2x text-warning mb-2"></i>
                <h4 class="text-warning">{{ round($gradeHistory->avg('score'), 1) ?? 'N/A' }}</h4>
                <p class="card-text">Average Grade</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-tasks fa-2x text-success mb-2"></i>
                <h4 class="text-success">{{ $assignments->where('submissions.0.status', '!=', 'draft')->count() }}/{{ $assignments->count() }}</h4>
                <p class="card-text">Assignments Done</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center">
            <div class="card-body">
                <i class="fas fa-calendar fa-2x text-info mb-2"></i>
                <h4 class="text-info">{{ $enrollment->enrolled_at->diffInDays() }}</h4>
                <p class="card-text">Days Enrolled</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column -->
    <div class="col-lg-8">
        <!-- Module Progress -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Module Progress</h5>
            </div>
            <div class="card-body">
                @foreach($courseProgress as $moduleData)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6 class="mb-0">{{ $moduleData['module']->title }}</h6>
                            <span class="badge bg-{{ $moduleData['progress_percentage'] >= 80 ? 'success' : ($moduleData['progress_percentage'] >= 50 ? 'warning' : 'danger') }}">
                                {{ $moduleData['progress_percentage'] }}%
                            </span>
                        </div>
                        <div class="progress mb-2" style="height: 10px;">
                            <div class="progress-bar bg-{{ $moduleData['progress_percentage'] >= 80 ? 'success' : ($moduleData['progress_percentage'] >= 50 ? 'warning' : 'danger') }}" 
                                 role="progressbar" style="width: {{ $moduleData['progress_percentage'] }}%">
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $moduleData['completed_assignments'] }} of {{ $moduleData['total_assignments'] }} assignments completed
                        </small>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Assignment Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-clipboard-list me-2"></i>Assignment Details</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Assignment</th>
                                <th>Module</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th>Grade</th>
                                <th>Submitted</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($assignments as $assignment)
                                @php
                                    $submission = $assignment->submissions->first();
                                    $grade = $submission?->grade;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $assignment->title }}</div>
                                        <small class="text-muted">{{ $assignment->max_points }} points</small>
                                    </td>
                                    <td>{{ $assignment->module->title }}</td>
                                    <td>
                                        <span class="{{ $assignment->due_date->isPast() && !$submission ? 'text-danger' : '' }}">
                                            {{ $assignment->due_date->format('M j, Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($submission)
                                            @if($grade)
                                                <span class="badge bg-success">Graded</span>
                                            @else
                                                <span class="badge bg-warning">Submitted</span>
                                            @endif
                                            @if($submission->is_late)
                                                <span class="badge bg-danger ms-1">Late</span>
                                            @endif
                                        @elseif($assignment->due_date->isPast())
                                            <span class="badge bg-danger">Missing</span>
                                        @else
                                            <span class="badge bg-secondary">Not Started</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($grade)
                                            <span class="fw-medium text-{{ $grade->score >= ($assignment->max_points * 0.8) ? 'success' : ($grade->score >= ($assignment->max_points * 0.6) ? 'warning' : 'danger') }}">
                                                {{ $grade->score }}/{{ $assignment->max_points }}
                                            </span>
                                            <small class="text-muted d-block">
                                                ({{ round(($grade->score / $assignment->max_points) * 100, 1) }}%)
                                            </small>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($submission)
                                            {{ $submission->submitted_at->format('M j, g:i A') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Grade History Chart -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Grade Trend</h5>
            </div>
            <div class="card-body">
                <canvas id="gradeChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <!-- Right Column -->
    <div class="col-lg-4">
        <!-- Student Information -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-user me-2"></i>Student Information</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Name:</strong> {{ $student->name }}
                </div>
                <div class="mb-3">
                    <strong>Email:</strong> {{ $student->email }}
                </div>
                <div class="mb-3">
                    <strong>Enrolled:</strong> {{ $enrollment->enrolled_at->format('M j, Y') }}
                </div>
                <div class="mb-3">
                    <strong>Status:</strong> 
                    <span class="badge bg-{{ $enrollment->status === 'active' ? 'success' : ($enrollment->status === 'completed' ? 'primary' : 'danger') }}">
                        {{ ucfirst($enrollment->status) }}
                    </span>
                </div>
                @if($student->last_login_at)
                    <div class="mb-3">
                        <strong>Last Login:</strong> {{ $student->last_login_at->diffForHumans() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Performance Summary -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Performance Summary</h6>
            </div>
            <div class="card-body">
                @php
                    $completedAssignments = $assignments->where('submissions.0.status', '!=', 'draft')->count();
                    $totalAssignments = $assignments->count();
                    $gradedAssignments = $assignments->whereNotNull('submissions.0.grade')->count();
                    $lateSubmissions = $assignments->where('submissions.0.is_late', true)->count();
                    $averageGrade = $gradeHistory->avg('score');
                @endphp
                
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <small>Completion Rate</small>
                        <small>{{ $totalAssignments > 0 ? round(($completedAssignments / $totalAssignments) * 100, 1) : 0 }}%</small>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" 
                             style="width: {{ $totalAssignments > 0 ? ($completedAssignments / $totalAssignments) * 100 : 0 }}%">
                        </div>
                    </div>
                </div>
                
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h5 class="text-success mb-0">{{ $gradedAssignments }}</h5>
                            <small class="text-muted">Graded</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h5 class="text-danger mb-0">{{ $lateSubmissions }}</h5>
                        <small class="text-muted">Late</small>
                    </div>
                </div>
                
                @if($averageGrade)
                    <hr>
                    <div class="text-center">
                        <h4 class="text-{{ $averageGrade >= 80 ? 'success' : ($averageGrade >= 60 ? 'warning' : 'danger') }} mb-0">
                            {{ round($averageGrade, 1) }}%
                        </h4>
                        <small class="text-muted">Overall Average</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Participation & Engagement -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-comments me-2"></i>Participation</h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4">
                        <h6 class="mb-0">{{ $participationData['forum_posts'] }}</h6>
                        <small class="text-muted">Forum Posts</small>
                    </div>
                    <div class="col-4">
                        <h6 class="mb-0">{{ $participationData['discussion_replies'] }}</h6>
                        <small class="text-muted">Replies</small>
                    </div>
                    <div class="col-4">
                        <h6 class="mb-0">{{ $participationData['peer_reviews'] }}</h6>
                        <small class="text-muted">Reviews</small>
                    </div>
                </div>
                
                @if($participationData['engagement_score'] > 0)
                    <hr>
                    <div class="text-center">
                        <div class="mb-2">
                            <strong>Engagement Score</strong>
                        </div>
                        <div class="progress">
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: {{ $participationData['engagement_score'] }}%">
                            </div>
                        </div>
                        <small class="text-muted">{{ $participationData['engagement_score'] }}%</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activity</h6>
            </div>
            <div class="card-body">
                @forelse($gradeHistory->take(5) as $grade)
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <div class="fw-medium small">{{ Str::limit($grade->submission->assignment->title, 25) }}</div>
                            <small class="text-muted">{{ $grade->created_at->format('M j, g:i A') }}</small>
                        </div>
                        <span class="badge bg-{{ $grade->score >= ($grade->submission->assignment->max_points * 0.8) ? 'success' : ($grade->score >= ($grade->submission->assignment->max_points * 0.6) ? 'warning' : 'danger') }}">
                            {{ round(($grade->score / $grade->submission->assignment->max_points) * 100, 1) }}%
                        </span>
                    </div>
                @empty
                    <p class="text-muted text-center">No graded assignments yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Grade Trend Chart
@php
    $chartData = $gradeHistory->map(function($grade) {
        return [
            'date' => $grade->created_at->format('M j'),
            'score' => round(($grade->score / $grade->submission->assignment->max_points) * 100, 1),
            'assignment' => $grade->submission->assignment->title
        ];
    });
@endphp
const gradeData = @json($chartData);

const ctx = document.getElementById('gradeChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: gradeData.map(item => item.date),
        datasets: [{
            label: 'Grade %',
            data: gradeData.map(item => item.score),
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                max: 100,
                ticks: {
                    callback: function(value) {
                        return value + '%';
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    title: function(context) {
                        const index = context[0].dataIndex;
                        return gradeData[index].assignment;
                    },
                    label: function(context) {
                        return 'Grade: ' + context.parsed.y + '%';
                    }
                }
            }
        }
    }
});

function contactStudent() {
    // Implementation for contacting student
    alert('Contact student functionality would be implemented here.');
}

function exportStudentData() {
    // Implementation for exporting student data
    const studentId = {{ $student->id }};
    const courseId = {{ $enrollment->course_id }};
    window.location.href = `/teacher/students/${studentId}/export?course_id=${courseId}`;
}
</script>
@endpush