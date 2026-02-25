@extends('layouts.app')

@section('title', 'Progress - ' . $course->title)

@section('nav-items')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('student.courses.enrolled') }}">My Courses</a>
    </li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.courses.enrolled') }}">My Courses</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.courses.show', $course) }}">{{ $course->title }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">Progress</li>
                </ol>
            </nav>

            <!-- Overall Progress -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-line me-2"></i>Course Progress
                            </h5>
                        </div>
                        <div class="card-body">
                            <h4>{{ $course->title }}</h4>
                            <div class="progress mb-3" style="height: 25px;">
                                <div class="progress-bar progress-bar-striped" 
                                     role="progressbar" 
                                     style="width: {{ $progressData['overall_progress'] }}%">
                                    {{ $progressData['overall_progress'] }}%
                                </div>
                            </div>
                            
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h5 class="text-primary">{{ $progressData['modules_completed'] }}</h5>
                                        <small class="text-muted">Modules Completed</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h5 class="text-info">{{ $progressData['total_modules'] }}</h5>
                                        <small class="text-muted">Total Modules</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h5 class="text-success">{{ $enrollment->enrolled_at->format('M j, Y') }}</h5>
                                        <small class="text-muted">Enrolled Date</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3">
                                        <h5 class="text-warning">{{ $enrollment->last_accessed_at->format('M j, Y') }}</h5>
                                        <small class="text-muted">Last Accessed</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h6 class="mb-0">Quick Actions</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('student.courses.modules', $course) }}" 
                                   class="btn btn-primary">
                                    <i class="fas fa-play me-2"></i>Continue Learning
                                </a>
                                <a href="{{ route('student.courses.show', $course) }}" 
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-info-circle me-2"></i>Course Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Module Progress -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-list me-2"></i>Module Progress
                    </h5>
                </div>
                <div class="card-body">
                    @if(count($progressData['module_progress']) > 0)
                        <div class="row">
                            @foreach($progressData['module_progress'] as $moduleProgress)
                                <div class="col-md-6 mb-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="card-title mb-0">{{ $moduleProgress['module_title'] }}</h6>
                                                @if($moduleProgress['is_completed'])
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check me-1"></i>Complete
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">In Progress</span>
                                                @endif
                                            </div>
                                            
                                            <div class="progress mb-2">
                                                <div class="progress-bar {{ $moduleProgress['is_completed'] ? 'bg-success' : '' }}" 
                                                     role="progressbar" 
                                                     style="width: {{ $moduleProgress['progress_percentage'] }}%">
                                                    {{ $moduleProgress['progress_percentage'] }}%
                                                </div>
                                            </div>
                                            
                                            <a href="{{ route('student.courses.module', [$course, $moduleProgress['module_id']]) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>View Module
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-list fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No modules available</h5>
                            <p class="text-muted">This course doesn't have any published modules yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Learning Record Summary -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Learning Record Summary
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center">
                            <h4 class="text-primary">{{ $learningRecord['total_courses'] }}</h4>
                            <small class="text-muted">Total Courses</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-success">{{ $learningRecord['completed_courses'] }}</h4>
                            <small class="text-muted">Completed</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-info">{{ $learningRecord['active_courses'] }}</h4>
                            <small class="text-muted">Active</small>
                        </div>
                        <div class="col-md-3 text-center">
                            <h4 class="text-warning">{{ $learningRecord['total_learning_hours'] }}</h4>
                            <small class="text-muted">Learning Hours</small>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <small class="text-muted">Overall Completion Rate</small>
                        <div class="progress">
                            <div class="progress-bar bg-success" 
                                 role="progressbar" 
                                 style="width: {{ $learningRecord['overall_completion_rate'] }}%">
                                {{ round($learningRecord['overall_completion_rate'], 1) }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection