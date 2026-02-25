@extends('layouts.app')

@section('title', 'My Progress Dashboard')

@section('content')
<!-- Page Header -->
<div class="creative-page-header fade-in-up">
    <h1><i class="fas fa-chart-line me-2"></i>My Progress Dashboard</h1>
    <p>Track your academic performance and upcoming assignments</p>
</div>

<!-- Overall Progress Metrics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="creative-stat-card variant-success fade-in-up stagger-1">
        <div class="icon"><i class="fas fa-check-circle"></i></div>
        <div class="number">{{ number_format($analytics['completion_progress']['completion_rate'], 1) }}%</div>
        <div class="label">Completion Rate</div>
        <small class="text-muted">{{ $analytics['completion_progress']['completed'] }} of {{ $analytics['completion_progress']['total'] }} assignments</small>
    </div>
    
    <div class="creative-stat-card variant-info fade-in-up stagger-2">
        <div class="icon"><i class="fas fa-star"></i></div>
        <div class="number">{{ number_format($analytics['average_score'], 1) }}</div>
        <div class="label">Average Score</div>
        <small class="text-muted">Across all graded assignments</small>
    </div>
</div>

<div class="row">
    <!-- Upcoming Assignments -->
    <div class="col-lg-6 mb-4">
        <div class="creative-card">
            <div class="creative-card-header">
                <h3><i class="fas fa-calendar-alt"></i> Upcoming Assignments</h3>
                <span class="creative-badge creative-badge-info">{{ count($analytics['upcoming_assignments']) }}</span>
            </div>
            <div class="creative-card-body">
                @forelse($analytics['upcoming_assignments'] as $assignment)
                    <div class="alert alert-info mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $assignment['title'] }}</strong>
                                <br>
                                <small class="text-muted">{{ $assignment['course_title'] }}</small>
                            </div>
                            <span class="creative-badge creative-badge-warning">
                                {{ $assignment['days_until_due'] }} days left
                            </span>
                        </div>
                        <div class="mt-2">
                            <small><i class="fas fa-clock"></i> Due: {{ \Carbon\Carbon::parse($assignment['due_date'])->format('M d, Y H:i') }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">No upcoming assignments</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Overdue & Missed Assignments -->
    <div class="col-lg-6 mb-4">
        <div class="creative-card">
            <div class="creative-card-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Attention Needed</h3>
            </div>
            <div class="creative-card-body">
                <!-- Overdue Assignments -->
                @if(count($analytics['overdue_assignments']) > 0)
                    <h6 class="text-warning"><i class="fas fa-clock"></i> Overdue (Late Submission Allowed)</h6>
                    @foreach($analytics['overdue_assignments'] as $assignment)
                        <div class="alert alert-warning mb-2">
                            <strong>{{ $assignment['title'] }}</strong>
                            <br>
                            <small>{{ $assignment['course_title'] }}</small>
                            <br>
                            <small class="text-danger">Due: {{ \Carbon\Carbon::parse($assignment['due_date'])->format('M d, Y') }}</small>
                        </div>
                    @endforeach
                @endif

                <!-- Missed Assignments -->
                @if(count($analytics['missed_assignments']) > 0)
                    <h6 class="text-danger mt-3"><i class="fas fa-times-circle"></i> Missed (No Late Submission)</h6>
                    @foreach($analytics['missed_assignments'] as $assignment)
                        <div class="alert alert-danger mb-2">
                            <strong>{{ $assignment['title'] }}</strong>
                            <br>
                            <small>{{ $assignment['course_title'] }}</small>
                            <br>
                            <small>Due: {{ \Carbon\Carbon::parse($assignment['due_date'])->format('M d, Y') }}</small>
                        </div>
                    @endforeach
                @endif

                @if(count($analytics['overdue_assignments']) === 0 && count($analytics['missed_assignments']) === 0)
                    <p class="text-muted text-center py-3">
                        <i class="fas fa-check-circle text-success"></i> All caught up!
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Grades -->
    <div class="col-lg-6 mb-4">
        <div class="creative-card">
            <div class="creative-card-header">
                <h3><i class="fas fa-graduation-cap"></i> Recent Grades</h3>
            </div>
            <div class="creative-card-body">
                @forelse($analytics['recent_grades'] as $grade)
                    <div class="d-flex align-items-center mb-3 p-3 border rounded">
                        <div class="flex-grow-1">
                            <strong>{{ $grade['assignment_title'] }}</strong>
                            <br>
                            <small class="text-muted">{{ $grade['course_title'] }}</small>
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-calendar"></i> {{ \Carbon\Carbon::parse($grade['graded_at'])->format('M d, Y') }}
                            </small>
                        </div>
                        <div class="text-end">
                            <h4 class="mb-0">
                                <span class="creative-badge creative-badge-{{ $grade['score'] >= 90 ? 'success' : ($grade['score'] >= 70 ? 'info' : 'warning') }}">
                                    {{ number_format($grade['score'], 1) }}
                                </span>
                            </h4>
                            <small class="text-muted">/ {{ $grade['max_score'] }}</small>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3">No graded assignments yet</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Performance Trend -->
    <div class="col-lg-6 mb-4">
        <div class="creative-card">
            <div class="creative-card-header">
                <h3><i class="fas fa-chart-area"></i> Performance Trend</h3>
            </div>
            <div class="creative-card-body">
                @if(count($analytics['performance_trend']) > 0)
                    <canvas id="performanceTrendChart" height="200"></canvas>
                @else
                    <p class="text-muted text-center py-4">Not enough data to show trend</p>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    @if(count($analytics['performance_trend']) > 0)
    // Performance Trend Chart
    const trendCtx = document.getElementById('performanceTrendChart');
    if (trendCtx) {
        const trendData = @json($analytics['performance_trend']);
        
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: trendData.map(item => item.assignment_title),
                datasets: [{
                    label: 'Score',
                    data: trendData.map(item => item.score),
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
                        callbacks: {
                            label: function(context) {
                                return `Score: ${context.parsed.y.toFixed(1)}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20
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
