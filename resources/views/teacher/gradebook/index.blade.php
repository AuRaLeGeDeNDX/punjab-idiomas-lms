@extends('layouts.app')

@section('title', 'Grade Book - ' . $gradeBook['course']->title)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">Grade Book</h1>
                    <p class="text-muted">{{ $gradeBook['course']->title }}</p>
                </div>
                <div class="btn-group">
                    <a href="{{ route('teacher.gradebook.configuration', $gradeBook['course']) }}" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-cog"></i> Configuration
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                            <i class="fas fa-download"></i> Export
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('teacher.gradebook.export', ['course' => $gradeBook['course'], 'format' => 'csv']) }}">Export as CSV</a></li>
                            <li><a class="dropdown-item" href="{{ route('teacher.gradebook.export', ['course' => $gradeBook['course'], 'format' => 'xlsx']) }}">Export as Excel</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $statistics['total_assignments'] }}</h4>
                                    <p class="mb-0">Total Assignments</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-tasks fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $statistics['total_submissions'] }}</h4>
                                    <p class="mb-0">Total Submissions</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-file-alt fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $statistics['graded_submissions'] }}</h4>
                                    <p class="mb-0">Graded</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-check-circle fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white">
                        <div class="card-body">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <h4 class="mb-0">{{ $statistics['pending_grading'] }}</h4>
                                    <p class="mb-0">Pending</p>
                                </div>
                                <div class="align-self-center">
                                    <i class="fas fa-clock fa-2x"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grade Book Table -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Student Grades</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="sticky-left">Student</th>
                                    @foreach($gradeBook['assignments'] as $assignment)
                                        <th class="text-center" style="min-width: 120px;">
                                            <div class="small">{{ $assignment->title }}</div>
                                            <div class="text-muted small">{{ $assignment->max_score }} pts</div>
                                        </th>
                                    @endforeach
                                    <th class="text-center bg-light" style="min-width: 120px;">
                                        <strong>Course Grade</strong>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($gradeBook['grade_matrix'] as $studentData)
                                    <tr>
                                        <td class="sticky-left bg-white">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                    {{ substr($studentData['student']->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="fw-medium">{{ $studentData['student']->name }}</div>
                                                    <div class="text-muted small">{{ $studentData['student']->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        @foreach($gradeBook['assignments'] as $assignment)
                                            <td class="text-center">
                                                @php $grade = $studentData['grades'][$assignment->id] @endphp
                                                @if($grade)
                                                    <div class="grade-cell">
                                                        <div class="fw-medium">
                                                            {{ $grade['score'] }}/{{ $assignment->max_score }}
                                                            @if($grade['is_locked'])
                                                                <i class="fas fa-lock text-danger ms-1" title="Locked"></i>
                                                            @endif
                                                        </div>
                                                        <div class="small text-muted">{{ number_format($grade['percentage'], 1) }}%</div>
                                                        <div class="badge badge-sm bg-{{ $grade['percentage'] >= 70 ? 'success' : ($grade['percentage'] >= 60 ? 'warning' : 'danger') }}">
                                                            {{ $grade['letter_grade'] }}
                                                        </div>
                                                        
                                                        <!-- Action Buttons -->
                                                        <div class="mt-1">
                                                            @if($grade['is_locked'])
                                                                @can('override', $grade['grade_model'])
                                                                    <a href="{{ route('admin.gradebook.show-override', $grade['grade_model']) }}" 
                                                                       class="btn btn-xs btn-warning" 
                                                                       title="Override Grade">
                                                                        <i class="fas fa-edit"></i>
                                                                    </a>
                                                                @endcan
                                                                @if($grade['overrides_count'] > 0)
                                                                    <a href="{{ route('teacher.gradebook.override-history', $grade['grade_model']) }}" 
                                                                       class="btn btn-xs btn-info" 
                                                                       title="View Override History ({{ $grade['overrides_count'] }})">
                                                                        <i class="fas fa-history"></i>
                                                                        <span class="badge bg-white text-dark">{{ $grade['overrides_count'] }}</span>
                                                                    </a>
                                                                @endif
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-center bg-light">
                                            @if($studentData['course_grade'])
                                                <div class="fw-bold">{{ number_format($studentData['course_grade']['percentage'], 1) }}%</div>
                                                <div class="badge bg-{{ $studentData['course_grade']['percentage'] >= 70 ? 'success' : ($studentData['course_grade']['percentage'] >= 60 ? 'warning' : 'danger') }}">
                                                    {{ $studentData['course_grade']['letter_grade'] }}
                                                </div>
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

            <!-- Assignment Statistics -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">Assignment Statistics</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($gradeBook['assignment_stats'] as $stats)
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="border rounded p-3">
                                            <h6 class="mb-2">{{ $stats['assignment']->title }}</h6>
                                            <div class="small text-muted mb-2">
                                                Submissions: {{ $stats['submitted_count'] }}/{{ $stats['total_students'] }}
                                                ({{ number_format(($stats['submitted_count']/$stats['total_students'])*100, 1) }}%)
                                            </div>
                                            @if($stats['average_score'])
                                                <div class="small">
                                                    <strong>Average:</strong> {{ number_format($stats['average_score'], 1) }}/{{ $stats['assignment']->max_score }}
                                                    ({{ number_format($stats['average_percentage'], 1) }}%)
                                                </div>
                                                <div class="small">
                                                    <strong>Range:</strong> {{ $stats['min_score'] }} - {{ $stats['max_score'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.sticky-left {
    position: sticky;
    left: 0;
    z-index: 10;
    background-color: white;
    border-right: 2px solid #dee2e6;
}

.grade-cell {
    min-width: 80px;
}

.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 14px;
}

.badge-sm {
    font-size: 0.75em;
}

.btn-xs {
    padding: 0.15rem 0.4rem;
    font-size: 0.75rem;
    line-height: 1.2;
}
</style>
@endsection