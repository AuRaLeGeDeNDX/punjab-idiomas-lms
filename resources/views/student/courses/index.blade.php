@extends('layouts.app')

@section('title', 'Browse Courses')

@section('nav-items')
    <li class="nav-item">
        <a class="nav-link active" href="{{ route('student.courses.index') }}">Browse Courses</a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="{{ route('student.courses.enrolled') }}">My Courses</a>
    </li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center gap-3">
                    <h2><i class="fas fa-book me-2"></i>Browse Courses</h2>
                    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Dashboard
                    </a>
                </div>
                <div class="alert alert-info mb-0 py-2 px-3">
                    <small>
                        <i class="fas fa-info-circle me-1"></i>
                        Course assignment is managed by teachers and administrators
                    </small>
                </div>
            </div>

            <!-- Filters -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET" action="{{ route('student.courses.index') }}">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label for="search" class="form-label">Search</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="search" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Search courses...">
                            </div>
                            <div class="col-md-3">
                                <label for="category" class="form-label">Category</label>
                                <select class="form-select" id="category" name="category">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" 
                                                {{ request('category') == $category ? 'selected' : '' }}>
                                            {{ $category }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="difficulty" class="form-label">Difficulty</label>
                                <select class="form-select" id="difficulty" name="difficulty">
                                    <option value="">All Levels</option>
                                    @foreach($difficultyLevels as $level)
                                        <option value="{{ $level }}" 
                                                {{ request('difficulty') == $level ? 'selected' : '' }}>
                                            {{ ucfirst($level) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search me-1"></i>Filter
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Courses Grid -->
            @if($courses->count() > 0)
                <div class="row">
                    @foreach($courses as $course)
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body d-flex flex-column">
                                    <h5 class="card-title">
                                        <a href="{{ route('student.courses.show', $course) }}" 
                                           class="text-decoration-none">
                                            {{ $course->title }}
                                        </a>
                                    </h5>
                                    
                                    <div class="mb-2">
                                        @if($course->category)
                                            <span class="badge bg-secondary me-1">{{ $course->category }}</span>
                                        @endif
                                        @if($course->difficulty_level)
                                            <span class="badge bg-info">{{ ucfirst($course->difficulty_level) }}</span>
                                        @endif
                                    </div>
                                    
                                    <p class="card-text text-muted flex-grow-1">
                                        {{ Str::limit($course->description, 120) }}
                                    </p>
                                    
                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <small class="text-muted">
                                                <i class="fas fa-user me-1"></i>{{ $course->teacher->name }}
                                            </small>
                                            @if($course->duration_hours)
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>{{ $course->duration_hours }}h
                                                </small>
                                            @endif
                                        </div>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                {{ $course->enrollments()->where('status', 'active')->count() }} students
                                                @if($course->max_students)
                                                    / {{ $course->max_students }}
                                                @endif
                                            </small>
                                            
                                            <a href="{{ route('student.courses.show', $course) }}" 
                                               class="btn btn-sm btn-outline-primary">
                                                View Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No courses found</h5>
                        <p class="text-muted">Try adjusting your search criteria or browse all available courses.</p>
                        @if(request()->hasAny(['search', 'category', 'difficulty']))
                            <a href="{{ route('student.courses.index') }}" class="btn btn-primary">
                                <i class="fas fa-refresh me-2"></i>Clear Filters
                            </a>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection