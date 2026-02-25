@extends('layouts.app')

@section('title', $courseWithModules->title)

@section('nav-items')
    <li class="nav-item">
        <a class="nav-link" href="{{ route('student.courses.index') }}">Browse Courses</a>
    </li>
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
                        <a href="{{ route('student.courses.index') }}">Courses</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $courseWithModules->title }}
                    </li>
                </ol>
            </nav>

            <!-- Course Header -->
            <div class="row mb-4">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h1 class="card-title">{{ $courseWithModules->title }}</h1>
                            <p class="text-muted mb-3">
                                <i class="fas fa-user me-2"></i>{{ $courseWithModules->teacher->name }}
                                @if($courseWithModules->category)
                                    • <span class="badge bg-secondary">{{ $courseWithModules->category }}</span>
                                @endif
                                @if($courseWithModules->difficulty_level)
                                    • <span class="badge bg-info">{{ ucfirst($courseWithModules->difficulty_level) }}</span>
                                @endif
                            </p>
                            
                            @if($courseWithModules->description)
                                <p class="card-text">{{ $courseWithModules->description }}</p>
                            @endif

                            @if($courseWithModules->duration_hours)
                                <p class="text-muted">
                                    <i class="fas fa-clock me-2"></i>{{ $courseWithModules->duration_hours }} hours
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Course Information</h5>
                            
                            <div class="mb-3">
                                <small class="text-muted">Enrollment Status</small>
                                <div>
                                    @if($isEnrolled)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Enrolled
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Not Enrolled</span>
                                    @endif
                                </div>
                            </div>

                            <div class="mb-3">
                                <small class="text-muted">Students Enrolled</small>
                                <div>{{ $enrollmentCount }}
                                    @if($courseWithModules->max_students)
                                        / {{ $courseWithModules->max_students }}
                                    @endif
                                </div>
                            </div>

                            @if($courseWithModules->hasPrerequisites())
                                <div class="mb-3">
                                    <small class="text-muted">Prerequisites</small>
                                    <div>
                                        @foreach($courseWithModules->prerequisiteCourses() as $prerequisite)
                                            <div class="d-flex align-items-center mb-1">
                                                @if($prerequisitesMet)
                                                    <i class="fas fa-check text-success me-2"></i>
                                                @else
                                                    <i class="fas fa-times text-danger me-2"></i>
                                                @endif
                                                <small>{{ $prerequisite->title }}</small>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Course Assignment Information -->
                            <div class="d-grid gap-2">
                                @if($isEnrolled)
                                    <a href="{{ route('student.courses.modules', $courseWithModules) }}" 
                                       class="btn btn-primary">
                                        <i class="fas fa-play me-2"></i>Continue Learning
                                    </a>
                                    <a href="{{ route('student.courses.progress', $courseWithModules) }}" 
                                       class="btn btn-outline-info">
                                        <i class="fas fa-chart-line me-2"></i>View Progress
                                    </a>
                                    <div class="alert alert-info mt-2">
                                        <small>
                                            <i class="fas fa-info-circle me-1"></i>
                                            To be removed from this course, please contact your teacher or administrator.
                                        </small>
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <h6><i class="fas fa-user-plus me-2"></i>Course Assignment Required</h6>
                                        <p class="mb-2">Students cannot enroll themselves in courses. You need to be assigned by:</p>
                                        <ul class="mb-2">
                                            <li>Your teacher: {{ $courseWithModules->teacher->name }}</li>
                                            <li>A system administrator</li>
                                        </ul>
                                        <small class="text-muted">
                                            Please contact them to request access to this course.
                                        </small>
                                    </div>
                                    
                                    @if(!$prerequisitesMet)
                                        <div class="alert alert-danger">
                                            <small>
                                                <i class="fas fa-exclamation-triangle me-1"></i>
                                                Prerequisites not met for this course.
                                            </small>
                                        </div>
                                    @elseif(!$courseWithModules->isEnrollmentOpen())
                                        <div class="alert alert-secondary">
                                            <small>
                                                <i class="fas fa-calendar-times me-1"></i>
                                                Enrollment period has ended.
                                            </small>
                                        </div>
                                    @elseif($courseWithModules->isFull())
                                        <div class="alert alert-secondary">
                                            <small>
                                                <i class="fas fa-users me-1"></i>
                                                Course has reached maximum capacity.
                                            </small>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Course Modules -->
            @if($courseWithModules->modules->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>Course Content
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($courseWithModules->modules as $module)
                                @if($module->is_published || $isEnrolled)
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    {{ $module->title }}
                                                    @if(!$module->is_published)
                                                        <span class="badge bg-warning ms-2">Draft</span>
                                                    @endif
                                                </h6>
                                                @if($module->description)
                                                    <p class="card-text text-muted">{{ Str::limit($module->description, 100) }}</p>
                                                @endif
                                                
                                                @if($isEnrolled && $module->is_published)
                                                    <a href="{{ route('student.courses.module', [$courseWithModules, $module]) }}" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye me-1"></i>View Module
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection