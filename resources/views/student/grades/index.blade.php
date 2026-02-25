@extends('layouts.app')

@section('title', 'My Grades')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0">My Grades</h1>
                    <p class="text-muted">View your grades across all enrolled courses</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Dashboard
                    </a>
                    <a href="{{ route('student.grades.transcript') }}" class="btn btn-outline-primary">
                        <i class="fas fa-download me-2"></i>Download Transcript
                    </a>
                </div>
            </div>

            @if($enrollments->isEmpty())
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                        <h5>No Enrolled Courses</h5>
                        <p class="text-muted">You are not currently enrolled in any courses.</p>
                        <a href="{{ route('student.courses.index') }}" class="btn btn-primary">
                            Browse Courses
                        </a>
                    </div>
                </div>
            @else
                <div class="row">
                    @foreach($enrollments as $enrollment)
                        @php
                            $course = $enrollment->course;
                            $courseGrade = $courseGrades[$course->id] ?? null;
                        @endphp
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100">
                                <div class="card-header">
                                    <h6 class="mb-0">{{ $course->title }}</h6>
                                    <small class="text-muted">{{ $course->category }}</small>
                                </div>
                                <div class="card-body">
                                    @if($courseGrade && $courseGrade['percentage'] > 0)
                                        <div class="text-center mb-3">
                                            <div class="display-6 fw-bold text-{{ $courseGrade['percentage'] >= $course->getPassingGrade() ? 'success' : 'danger' }}">
                                                {{ number_format($courseGrade['percentage'], 1) }}%
                                            </div>
                                            <div class="badge bg-{{ $courseGrade['percentage'] >= 70 ? 'success' : ($courseGrade['percentage'] >= 60 ? 'warning' : 'danger') }} fs-6">
                                                {{ $courseGrade['letter_grade'] }}
                                            </div>
                                        </div>

                                        <div class="progress mb-3" style="height: 8px;">
                                            <div class="progress-bar bg-{{ $courseGrade['percentage'] >= $course->getPassingGrade() ? 'success' : 'danger' }}" 
                                                 style="width: {{ min($courseGrade['percentage'], 100) }}%"></div>
                                        </div>

                                        <div class="small text-muted">
                                            <div class="d-flex justify-content-between">
                                                <span>Total Points:</span>
                                                <span>{{ $courseGrade['total_score'] }}/{{ $courseGrade['total_possible'] }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Passing Grade:</span>
                                                <span>{{ $course->getPassingGrade() }}%</span>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-center py-3">
                                            <i class="fas fa-clock fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No grades available yet</p>
                                        </div>
                                    @endif
                                </div>
                                <div class="card-footer">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            Enrolled: {{ $enrollment->enrolled_at->format('M j, Y') }}
                                        </small>
                                        <a href="{{ route('student.grades.course', $course) }}" class="btn btn-sm btn-outline-primary">
                                            View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Overall Statistics -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Academic Summary</h5>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3">
                                        <div class="border-end">
                                            <h4 class="text-primary mb-0">{{ $enrollments->count() }}</h4>
                                            <small class="text-muted">Enrolled Courses</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border-end">
                                            @php
                                                $coursesWithGrades = collect($courseGrades)->filter(function($grade) {
                                                    return $grade && $grade['percentage'] > 0;
                                                })->count();
                                            @endphp
                                            <h4 class="text-info mb-0">{{ $coursesWithGrades }}</h4>
                                            <small class="text-muted">Courses with Grades</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="border-end">
                                            @php
                                                $passingCourses = collect($courseGrades)->filter(function($grade, $courseId) use ($enrollments) {
                                                    $course = $enrollments->firstWhere('course_id', $courseId)->course;
                                                    return $grade && $grade['percentage'] >= $course->getPassingGrade();
                                                })->count();
                                            @endphp
                                            <h4 class="text-success mb-0">{{ $passingCourses }}</h4>
                                            <small class="text-muted">Passing Courses</small>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        @php
                                            $totalPercentage = collect($courseGrades)->filter(function($grade) {
                                                return $grade && $grade['percentage'] > 0;
                                            })->avg('percentage');
                                        @endphp
                                        <h4 class="text-warning mb-0">
                                            {{ $totalPercentage ? number_format($totalPercentage, 1) . '%' : 'N/A' }}
                                        </h4>
                                        <small class="text-muted">Overall Average</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection