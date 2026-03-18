@extends('layouts.app')

@section('title', 'My Courses - Teacher')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="creative-page-header fade-in-up responsive-card-list-item">
                <h1 class="mb-0"><i class="fas fa-book me-2"></i>My Courses</h1>
                <div class="d-flex flex-wrap gap-2 mt-2 mt-md-0">
                    <a href="{{ route('teacher.dashboard') }}" class="creative-btn creative-btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back
                    </a>
                    <a href="{{ route('teacher.courses.create') }}" class="creative-btn creative-btn-primary">
                        <i class="fas fa-plus me-1"></i>New Course
                    </a>
                </div>
            </div>

            <!-- Courses Grid -->
            @if($courses->count() > 0)
                <div class="row">
                    @foreach($courses as $course)
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <h5 class="card-title">
                                        <a href="{{ route('teacher.courses.show', $course) }}" class="text-decoration-none">
                                            {{ $course->title }}
                                        </a>
                                    </h5>
                                    <div class="d-flex flex-column gap-1">
                                        @if($course->is_published)
                                            <span class="badge bg-success">Published</span>
                                        @else
                                            <span class="badge bg-secondary">Draft</span>
                                        @endif
                                        @if($course->is_featured)
                                            <span class="badge bg-warning text-dark">Featured</span>
                                        @endif
                                    </div>
                                </div>

                                @if($course->description)
                                    <p class="card-text text-muted">{{ Str::limit($course->description, 100) }}</p>
                                @endif

                                <div class="row text-center mb-3">
                                    <div class="col-4">
                                        <div class="border-end">
                                            <h6 class="text-primary mb-0">{{ $course->enrollments_count ?? 0 }}</h6>
                                            <small class="text-muted">Students</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="border-end">
                                            <h6 class="text-info mb-0">{{ $course->modules_count ?? 0 }}</h6>
                                            <small class="text-muted">Modules</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <h6 class="text-success mb-0">{{ $course->assignments_count ?? 0 }}</h6>
                                        <small class="text-muted">Assignments</small>
                                    </div>
                                </div>

                                @if($course->category)
                                    <div class="mb-2">
                                        <span class="badge bg-info">{{ $course->category }}</span>
                                    </div>
                                @endif

                                <div class="mb-2">
                                    <small class="text-muted">
                                        Created: {{ $course->created_at->format('M j, Y') }}
                                    </small>
                                </div>
                            </div>
                            <div class="creative-card-footer">
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="{{ route('teacher.courses.show', $course) }}"
                                       class="creative-btn creative-btn-outline-primary creative-btn-sm flex-fill">
                                        <i class="fas fa-cog me-1"></i>Manage
                                    </a>
                                    <a href="{{ route('teacher.courses.edit', $course) }}"
                                       class="creative-btn creative-btn-outline-secondary creative-btn-sm flex-fill">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($course->is_published)
                                        <form method="POST" action="{{ route('teacher.courses.unpublish', $course) }}" class="d-inline flex-fill">
                                            @csrf
                                            <button type="submit" class="creative-btn creative-btn-outline-warning creative-btn-sm w-100" title="Unpublish">
                                                <i class="fas fa-eye-slash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('teacher.courses.publish', $course) }}" class="d-inline flex-fill">
                                            @csrf
                                            <button type="submit" class="creative-btn creative-btn-outline-success creative-btn-sm w-100" title="Publish">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <form action="{{ route('teacher.courses.destroy', $course) }}" method="POST"
                                          class="d-inline flex-fill" onsubmit="return confirm('Delete this course?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="creative-btn creative-btn-outline-danger creative-btn-sm w-100">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-graduation-cap fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No courses created yet</h5>
                    <p class="text-muted">Start building your first course to share knowledge with students.</p>
                    <a href="{{ route('teacher.courses.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Create Your First Course
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection