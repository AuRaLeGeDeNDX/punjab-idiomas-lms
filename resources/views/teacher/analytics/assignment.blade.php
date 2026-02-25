@extends('layouts.app')

@section('title', 'Assignment Analytics - ' . $assignment->title)

@section('content')
<!-- Page Header -->
<div class="creative-page-header fade-in-up">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1><i class="fas fa-chart-bar me-2"></i>Assignment Analytics</h1>
            <p class="mb-0">{{ $assignment->title }}</p>
        </div>
        <div>
            <a href="{{ route('teacher.analytics.dashboard', $assignment->course) }}" class="creative-btn creative-btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Course Analytics
            </a>
        </div>
    </div>
</div>

<!-- Assignment Metrics -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
    <div class="creative-stat-card variant-success fade-in-up stagger-1">
        <div class="icon"><i class="fas fa-check-circle"></i></div>
        <div class="number">{{ number_format($analytics['completion_rate'], 1) }}%</div>
        <div class="label">Completion Rate</div>
    </div>
    
    <div class="creative-stat-card variant-info fade-in-up stagger-2">
        <div class="icon"><i class="fas fa-star"></i></div>
        <div class="number">{{ number_format($analytics['average_score'], 1) }}</div>
        <div class="label">Average Score</div>
    </div>
    
    <div class="creative-stat-card variant-warning fade-in-up stagger-3">
        <div class="icon"><i class="fas fa-clock"></i></div>
        <div class="number">{{ $analytics['late_count'] }}</div>
        <div class="label">Late Submissions</div>
    </div>
    
    <div class="creative-stat-card variant-danger fade-in-up stagger-4">
        <div class="icon"><i class="fas fa-times-circle"></i></div>
        <div class="number">{{ $analytics['not_submitted_count'] }}</div>
        <div class="label">Not Submitted</div>
    </div>
</div>

<div class="row">
    <!-- Grade Distribution -->
    <div class="col-lg-6 mb-4">
        <div class="creative-card">
            <div class="creative-card-header">
                <h3><i class="fas fa-chart-pie"></i> Grade Distribution</h3>
            </div>
            <div class="creative-card-body">
                @if(array_sum(array_values($analytics['grade_distribution'])) > 0)
                    <canvas id="gradeDistributionChart" height="200"></canvas>
                @else
                    <p class="text-muted text-center py-4">No grades available yet</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Grade Breakdown -->
    <div class="col-lg-6 mb-4">
        <div class="creative-card">
            <div class="creative-card-header">
                <h3><i class="fas fa-list"></i> Grade Breakdown</h3>
            </div>
            <div class="creative-card-body">
                <div class="list-group">
                    @foreach($analytics['grade_distribution'] as $grade => $count)
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <strong>{{ $grade }}</strong>
                                @if($grade === 'A')
                                    (90-100)
                                @elseif($grade === 'B')
                                    (80-89)
                                @elseif($grade === 'C')
                                    (70-79)
                                @elseif($grade === 'D')
                                    (60-69)
                                @else
                                    (0-59)
                                @endif
                            </span>
                            <span class="creative-badge creative-badge-primary">{{ $count }} students</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Student Submission List -->
<div class="row">
    <div class="col-12">
        <div class="creative-card">
            <div class="creative-card-header">
                <h3><i class="fas fa-users"></i> Student Submissions</h3>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted">
                        Showing {{ $analytics['student_list']['pagination']['from'] }} - {{ $analytics['student_list']['pagination']['to'] }} 
                        of {{ $analytics['student_list']['pagination']['total'] }} students
                    </span>
                    <select class="form-select form-select-sm" style="width: auto;" onchange="updatePerPage(this.value)">
                        <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15 per page</option>
                        <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25 per page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
                    </select>
                </div>
            </div>
            <div class="creative-card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Status</th>
                                <th>Submitted At</th>
                                <th>Score</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($analytics['student_list']['data'] as $student)
                                <tr>
                                    <td><strong>{{ $student['student_name'] }}</strong></td>
                                    <td>{{ $student['student_email'] }}</td>
                                    <td>
                                        @if($student['status'] === 'graded')
                                            <span class="creative-badge creative-badge-success">Graded</span>
                                        @elseif($student['status'] === 'submitted')
                                            <span class="creative-badge creative-badge-info">Submitted</span>
                                        @elseif($student['status'] === 'late')
                                            <span class="creative-badge creative-badge-warning">Late</span>
                                        @else
                                            <span class="creative-badge creative-badge-danger">Not Submitted</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($student['submitted_at'])
                                            {{ \Carbon\Carbon::parse($student['submitted_at'])->format('M d, Y H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($student['score'] !== null)
                                            <span class="creative-badge creative-badge-info">
                                                {{ number_format($student['score'], 1) }} / {{ $student['max_score'] }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($student['submission_id'])
                                            <a href="{{ route('teacher.grading.show', $student['submission_id']) }}" 
                                               class="creative-btn creative-btn-sm creative-btn-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No students enrolled in this course
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($analytics['student_list']['pagination']['last_page'] > 1)
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <div class="text-muted">
                            Page {{ $analytics['student_list']['pagination']['current_page'] }} of {{ $analytics['student_list']['pagination']['last_page'] }}
                        </div>
                        <nav aria-label="Student list pagination">
                            <ul class="pagination mb-0">
                                <!-- Previous Page -->
                                @if($analytics['student_list']['pagination']['current_page'] > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $analytics['student_list']['pagination']['current_page'] - 1]) }}">
                                            <i class="fas fa-chevron-left"></i> Previous
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link"><i class="fas fa-chevron-left"></i> Previous</span>
                                    </li>
                                @endif

                                <!-- Page Numbers -->
                                @php
                                    $currentPage = $analytics['student_list']['pagination']['current_page'];
                                    $lastPage = $analytics['student_list']['pagination']['last_page'];
                                    $start = max(1, $currentPage - 2);
                                    $end = min($lastPage, $currentPage + 2);
                                @endphp

                                @if($start > 1)
                                    <li class="page-item">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => 1]) }}">1</a>
                                    </li>
                                    @if($start > 2)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif
                                @endif

                                @for($i = $start; $i <= $end; $i++)
                                    <li class="page-item {{ $i == $currentPage ? 'active' : '' }}">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                    </li>
                                @endfor

                                @if($end < $lastPage)
                                    @if($end < $lastPage - 1)
                                        <li class="page-item disabled"><span class="page-link">...</span></li>
                                    @endif
                                    <li class="page-item">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $lastPage]) }}">{{ $lastPage }}</a>
                                    </li>
                                @endif

                                <!-- Next Page -->
                                @if($analytics['student_list']['pagination']['current_page'] < $analytics['student_list']['pagination']['last_page'])
                                    <li class="page-item">
                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $analytics['student_list']['pagination']['current_page'] + 1]) }}">
                                            Next <i class="fas fa-chevron-right"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link">Next <i class="fas fa-chevron-right"></i></span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
function updatePerPage(perPage) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', perPage);
    url.searchParams.set('page', 1); // Reset to first page
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    @if(array_sum(array_values($analytics['grade_distribution'])) > 0)
    // Grade Distribution Chart
    const gradeCtx = document.getElementById('gradeDistributionChart');
    if (gradeCtx) {
        const gradeData = @json($analytics['grade_distribution']);
        
        new Chart(gradeCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(gradeData),
                datasets: [{
                    data: Object.values(gradeData),
                    backgroundColor: [
                        'rgba(40, 167, 69, 0.8)',   // A - Green
                        'rgba(23, 162, 184, 0.8)',  // B - Cyan
                        'rgba(255, 193, 7, 0.8)',   // C - Yellow
                        'rgba(255, 133, 27, 0.8)',  // D - Orange
                        'rgba(220, 53, 69, 0.8)'    // F - Red
                    ],
                    borderColor: [
                        'rgba(40, 167, 69, 1)',
                        'rgba(23, 162, 184, 1)',
                        'rgba(255, 193, 7, 1)',
                        'rgba(255, 133, 27, 1)',
                        'rgba(220, 53, 69, 1)'
                    ],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} students (${percentage}%)`;
                            }
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
