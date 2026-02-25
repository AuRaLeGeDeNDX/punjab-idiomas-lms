@extends('layouts.app')

@section('title', 'Course Analytics - ' . $course->title)

@section('content')
<!-- Page Header -->
<div class="creative-page-header fade-in-up">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-chart-line me-2"></i>Course Analytics</h1>
            <p class="mb-0">{{ $course->title }} ({{ $course->code }})</p>
        </div>
        <div>
            <a href="{{ route('teacher.analytics.export', $course) }}" class="creative-btn creative-btn-primary">
                <i class="fas fa-download me-2"></i>Export CSV
            </a>
            <a href="{{ route('teacher.courses.show', $course) }}" class="creative-btn creative-btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Course
            </a>
        </div>
    </div>
</div>

<!-- Overall Metrics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="creative-stat-card variant-success fade-in-up stagger-1">
        <div class="icon"><i class="fas fa-check-circle"></i></div>
        <div class="number">{{ number_format($analytics['overall_completion_rate'], 1) }}%</div>
        <div class="label">Completion Rate</div>
    </div>
    
    <div class="creative-stat-card variant-info fade-in-up stagger-2">
        <div class="icon"><i class="fas fa-star"></i></div>
        <div class="number">{{ number_format($analytics['average_score'], 1) }}</div>
        <div class="label">Average Score</div>
    </div>
</div>

<div class="row">
    <!-- Submission Timeline -->
    <div class="col-lg-8 mb-4">
        <div class="creative-card">
            <div class="creative-card-header">
                <h3><i class="fas fa-chart-area"></i> Submission Timeline (Last 30 Days)</h3>
            </div>
            <div class="creative-card-body">
                @if(count($analytics['submission_timeline']) > 0)
                    <canvas id="submissionTimelineChart" height="80"></canvas>
                @else
                    <p class="text-muted text-center py-4">No submissions in the last 30 days</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="col-lg-4 mb-4">
        <div class="creative-card">
            <div class="creative-card-header">
                <h3><i class="fas fa-trophy"></i> Top Performers</h3>
            </div>
            <div class="creative-card-body">
                @forelse($analytics['top_performers'] as $performer)
                    <div class="d-flex align-items-center mb-3 p-2 border-bottom">
                        <div class="flex-grow-1">
                            <strong>{{ $performer['student_name'] }}</strong>
                            <br>
                            <small class="text-muted">{{ $performer['graded_count'] }} assignments</small>
                        </div>
                        <div class="text-end">
                            <span class="creative-badge creative-badge-success">
                                {{ number_format($performer['average_score'], 1) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">No graded assignments yet</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- At-Risk Students -->
    <div class="col-lg-4 mb-4">
        <div class="creative-card">
            <div class="creative-card-header">
                <h3><i class="fas fa-exclamation-triangle"></i> At-Risk Students</h3>
            </div>
            <div class="creative-card-body">
                @forelse($analytics['at_risk_students'] as $student)
                    <div class="d-flex align-items-center mb-3 p-2 border-bottom">
                        <div class="flex-grow-1">
                            <strong>{{ $student['student_name'] }}</strong>
                            <br>
                            <small class="text-muted">
                                @if($student['missing_count'] > 0)
                                    {{ $student['missing_count'] }} missing
                                @else
                                    {{ $student['graded_count'] }} assignments
                                @endif
                            </small>
                        </div>
                        <div class="text-end">
                            <span class="creative-badge creative-badge-danger">
                                {{ number_format($student['average_score'], 1) }}
                            </span>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">No at-risk students identified</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Assignment Statistics -->
    <div class="col-lg-8 mb-4">
        <div class="creative-card">
            <div class="creative-card-header">
                <h3><i class="fas fa-clipboard-list"></i> Assignment Statistics</h3>
            </div>
            <div class="creative-card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Assignment</th>
                                <th>Completion</th>
                                <th>Avg Score</th>
                                <th>Late</th>
                                <th>Not Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($analytics['assignment_stats'] as $stat)
                                <tr>
                                    <td>
                                        <strong>{{ $stat['title'] }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $stat['type'] }}</small>
                                    </td>
                                    <td>
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar bg-success" role="progressbar" 
                                                 style="width: {{ $stat['completion_rate'] }}%"
                                                 aria-valuenow="{{ $stat['completion_rate'] }}" 
                                                 aria-valuemin="0" aria-valuemax="100">
                                                {{ number_format($stat['completion_rate'], 0) }}%
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="creative-badge creative-badge-info">
                                            {{ number_format($stat['average_score'], 1) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($stat['late_count'] > 0)
                                            <span class="creative-badge creative-badge-warning">
                                                {{ $stat['late_count'] }}
                                            </span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($stat['not_submitted_count'] > 0)
                                            <span class="creative-badge creative-badge-danger">
                                                {{ $stat['not_submitted_count'] }}
                                            </span>
                                        @else
                                            <span class="text-muted">0</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('teacher.analytics.assignment', $stat['assignment_id']) }}" 
                                           class="creative-btn creative-btn-sm creative-btn-primary">
                                            <i class="fas fa-chart-bar"></i> Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No assignments found for this course
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(count($analytics['submission_timeline']) > 0)
    // Submission Timeline Chart
    const timelineCtx = document.getElementById('submissionTimelineChart');
    if (timelineCtx) {
        const timelineData = @json($analytics['submission_timeline']);
        
        new Chart(timelineCtx, {
            type: 'line',
            data: {
                labels: timelineData.map(item => item.date),
                datasets: [{
                    label: 'Submissions',
                    data: timelineData.map(item => item.count),
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
    @endif
});
</script>
@endpush
