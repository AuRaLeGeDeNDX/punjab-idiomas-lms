@extends('layouts.app')

@section('title', $course->title . ' - Coming Soon')

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
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">
                        <a href="{{ route('student.courses.index') }}">Courses</a>
                    </li>
                    <li class="breadcrumb-item active">{{ $course->title }}</li>
                </ol>
            </nav>

            <div class="card shadow-sm text-center" style="border: none; border-radius: 16px; overflow: hidden;">
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 3rem 2rem;">
                    <i class="fas fa-clock fa-4x text-white mb-3" style="opacity: 0.9;"></i>
                    <h2 class="text-white fw-bold mb-2">Coming Soon!</h2>
                    <p class="text-white mb-0" style="opacity: 0.85; font-size: 1.1rem;">
                        This course is being prepared for you
                    </p>
                </div>
                <div class="card-body p-4">
                    <h4 class="fw-bold mb-3">{{ $course->title }}</h4>

                    @if($course->description)
                        <p class="text-muted mb-4">{{ Str::limit($course->description, 200) }}</p>
                    @endif

                    <div class="d-flex justify-content-center gap-4 mb-4" style="font-size: 0.9rem;">
                        @if($course->teacher)
                            <div>
                                <i class="fas fa-user-tie me-1" style="color: #667eea;"></i>
                                <span class="text-muted">{{ $course->teacher->name }}</span>
                            </div>
                        @endif
                        @if($course->difficulty_level)
                            <div>
                                <i class="fas fa-layer-group me-1" style="color: #667eea;"></i>
                                <span class="text-muted">{{ ucfirst($course->difficulty_level) }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="alert mb-4" style="background: #f0f4ff; border: 1px solid #dde4ff; border-radius: 10px;">
                        <i class="fas fa-info-circle me-2" style="color: #667eea;"></i>
                        <span style="color: #4a5568;">
                            Your teacher is still setting up this course. You'll be able to access the content once it's published.
                        </span>
                    </div>

                    <a href="{{ route('student.courses.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-arrow-left me-2"></i>Browse Other Courses
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
