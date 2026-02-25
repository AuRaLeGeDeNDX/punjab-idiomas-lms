@extends('layouts.app')

@section('title', 'My Courses')

@section('nav-items')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('student.courses.index') }}">Browse Courses</a>
    </li>
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('student.courses.enrolled') }}">My Courses</a>
    </li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2><i class="fas fa-book-open me-2"></i>My Courses</h2>
                <div class="d-flex gap-2">
                    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
                    </a>
                    <a href="{{ route('student.courses.index') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Browse More Courses
                    </a>
                </div>
            </div>

            @if($enrollments->count() > 0)
                <div class="row">
                    @foreach($enrollments as $enrollment)
                        <div class="col-lg-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <h5 class="card-title mb-0">
                                            <a href="{{ route('student.courses.show', $enrollment->course) }}" 
                                               class="text-decoration-none">
                                                {{ $enrollment->course->title }}
                                            </a>
                                        </h5>
                                        <span class="badge bg-{{ $enrollment->status === 'completed' ? 'success' : 'primary' }}">
                                            {{ ucfirst($enrollment->status) }}
                                        </span>
                                    </div>
                                    
                                    <p class="card-text text-muted">
                                        {{ Str::limit($enrollment->course->description, 100) }}
                                    </p>
                                    
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Progress</small>
                                        <div class="progress mb-2">
                                            <div class="progress-bar {{ $enrollment->status === 'completed' ? 'bg-success' : '' }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $enrollment->progress_data['overall_progress'] ?? 0 }}%">
                                                {{ round($enrollment->progress_data['overall_progress'] ?? 0) }}%
                                            </div>
                                        </div>
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <small class="text-muted">
                                                    {{ $enrollment->progress_data['modules_completed'] ?? 0 }}/{{ $enrollment->progress_data['total_modules'] ?? 0 }}
                                                    <br>Modules
                                                </small>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted">
                                                    {{ $enrollment->enrolled_at->format('M j, Y') }}
                                                    <br>Enrolled
                                                </small>
                                            </div>
                                            <div class="col-4">
                                                <small class="text-muted">
                                                    {{ $enrollment->last_accessed_at->format('M j') }}
                                                    <br>Last Access
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex gap-2">
                                        @if($enrollment->status === 'active')
                                            <a href="{{ route('student.courses.modules', $enrollment->course) }}" 
                                               class="btn btn-primary btn-sm flex-fill">
                                                <i class="fas fa-play me-1"></i>Continue
                                            </a>
                                        @endif
                                        <a href="{{ route('student.courses.progress', $enrollment->course) }}" 
                                           class="btn btn-outline-info btn-sm">
                                            <i class="fas fa-chart-line me-1"></i>Progress
                                        </a>
                                        <a href="{{ route('student.courses.show', $enrollment->course) }}" 
                                           class="btn btn-outline-secondary btn-sm">
                                            <i class="fas fa-info-circle me-1"></i>Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No courses enrolled</h5>
                        <p class="text-muted">Start your learning journey by browsing and enrolling in available courses.</p>
                        <a href="{{ route('student.courses.index') }}" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Browse Courses
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection