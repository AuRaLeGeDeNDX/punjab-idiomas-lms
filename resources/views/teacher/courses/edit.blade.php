@extends('layouts.app')

@section('title', 'Edit Course - Teacher')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Edit Course: {{ $course->title }}</h1>
                <a href="{{ route('teacher.courses.show', $course) }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to Course
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('teacher.courses.update', $course) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8">
                                <!-- Basic Information -->
                                <div class="mb-4">
                                    <h5 class="card-title">Basic Information</h5>
                                    
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Course Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                               id="title" name="title" value="{{ old('title', $course->title) }}" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" name="description" rows="4">{{ old('description', $course->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category" class="form-label">Category</label>
                                                <input type="text" class="form-control @error('category') is-invalid @enderror" 
                                                       id="category" name="category" value="{{ old('category', $course->category) }}">
                                                @error('category')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="difficulty_level" class="form-label">Difficulty Level</label>
                                                <select class="form-select @error('difficulty_level') is-invalid @enderror" 
                                                        id="difficulty_level" name="difficulty_level">
                                                    <option value="">Select difficulty...</option>
                                                    <option value="beginner" {{ old('difficulty_level', $course->difficulty_level) == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                                    <option value="intermediate" {{ old('difficulty_level', $course->difficulty_level) == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                                    <option value="advanced" {{ old('difficulty_level', $course->difficulty_level) == 'advanced' ? 'selected' : '' }}>Advanced</option>
                                                </select>
                                                @error('difficulty_level')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="duration_hours" class="form-label">Duration (Hours)</label>
                                                <input type="number" class="form-control @error('duration_hours') is-invalid @enderror" 
                                                       id="duration_hours" name="duration_hours" value="{{ old('duration_hours', $course->duration_hours) }}" min="1">
                                                @error('duration_hours')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="max_students" class="form-label">Maximum Students</label>
                                                <input type="number" class="form-control @error('max_students') is-invalid @enderror" 
                                                       id="max_students" name="max_students" value="{{ old('max_students', $course->max_students) }}" min="1">
                                                @error('max_students')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Schedule Settings -->
                                <div class="mb-4">
                                    <h5 class="card-title">Schedule Settings</h5>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="enrollment_start_date" class="form-label">Enrollment Start Date</label>
                                                <input type="date" class="form-control @error('enrollment_start_date') is-invalid @enderror" 
                                                       id="enrollment_start_date" name="enrollment_start_date" 
                                                       value="{{ old('enrollment_start_date', $course->enrollment_start_date?->format('Y-m-d')) }}">
                                                @error('enrollment_start_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="enrollment_end_date" class="form-label">Enrollment End Date</label>
                                                <input type="date" class="form-control @error('enrollment_end_date') is-invalid @enderror" 
                                                       id="enrollment_end_date" name="enrollment_end_date" 
                                                       value="{{ old('enrollment_end_date', $course->enrollment_end_date?->format('Y-m-d')) }}">
                                                @error('enrollment_end_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="course_start_date" class="form-label">Course Start Date</label>
                                                <input type="date" class="form-control @error('course_start_date') is-invalid @enderror" 
                                                       id="course_start_date" name="course_start_date" 
                                                       value="{{ old('course_start_date', $course->course_start_date?->format('Y-m-d')) }}">
                                                @error('course_start_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="course_end_date" class="form-label">Course End Date</label>
                                                <input type="date" class="form-control @error('course_end_date') is-invalid @enderror" 
                                                       id="course_end_date" name="course_end_date" 
                                                       value="{{ old('course_end_date', $course->course_end_date?->format('Y-m-d')) }}">
                                                @error('course_end_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Course Status -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Course Status</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <div class="d-flex align-items-center">
                                                @if($course->is_published)
                                                    <span class="badge bg-success me-2">Published</span>
                                                    <small class="text-muted">Visible to students</small>
                                                @else
                                                    <span class="badge bg-secondary me-2">Draft</span>
                                                    <small class="text-muted">Not visible to students</small>
                                                @endif
                                            </div>
                                        </div>

                                        @if($course->is_featured)
                                            <div class="mb-3">
                                                <span class="badge bg-warning text-dark">Featured Course</span>
                                            </div>
                                        @endif

                                        <div class="mb-3">
                                            <small class="text-muted d-block">Created</small>
                                            <span>{{ $course->created_at->format('M j, Y') }}</span>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted d-block">Last Updated</small>
                                            <span>{{ $course->updated_at->format('M j, Y') }}</span>
                                        </div>


                                    </div>
                                </div>

                                <!-- Course Statistics -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Course Statistics</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span>Enrolled Students:</span>
                                                <strong>{{ $course->enrollments_count ?? 0 }}</strong>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span>Total Modules:</span>
                                                <strong>{{ $course->modules_count ?? 0 }}</strong>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span>Assignments:</span>
                                                <strong>{{ $course->assignments_count ?? 0 }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('teacher.courses.show', $course) }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update Course
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Course Hierarchy Management Section -->
            <div id="course-hierarchy-app" 
                 data-course-id="{{ $course->id }}" 
                 data-csrf-token="{{ csrf_token() }}"
                 data-user-role="teacher">
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
    @vite(['resources/js/course-hierarchy-simple.jsx', 'resources/css/course-hierarchy.css'])
@endpush