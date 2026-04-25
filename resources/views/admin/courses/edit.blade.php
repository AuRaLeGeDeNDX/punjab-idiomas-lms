@extends('layouts.app')

@section('title', 'Edit Course - Admin')

@section('sidebar')
    @include('admin.sidebar')
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="creative-page-header fade-in-up">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1><i class="fas fa-edit me-2"></i>Edit Course: {{ $course->title }}</h1>
                        <p>Update course information, settings, and structure</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.courses.show', $course) }}" class="creative-btn creative-btn-outline">
                            <i class="fas fa-eye"></i>View Course
                        </a>
                        <a href="{{ route('admin.courses.index') }}" class="creative-btn creative-btn-outline">
                            <i class="fas fa-arrow-left"></i>Back to Courses
                        </a>
                    </div>
                </div>
            </div>

            <div class="creative-card fade-in-up stagger-1">
                <div class="creative-card-body">
                    <form method="POST" action="{{ route('admin.courses.update', $course) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-8">
                                <!-- Basic Information -->
                                <div class="mb-4">
                                    <h5 class="card-title">Basic Information</h5>
                                    
                                    <div class="mb-3">
                                        <label for="title" class="creative-form-label">Course Title <span class="text-danger">*</span></label>
                                        <input type="text" class="creative-form-input @error('title') is-invalid @enderror" 
                                               id="title" name="title" value="{{ old('title', $course->title) }}" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="creative-form-label">Description</label>
                                        <textarea class="creative-form-input @error('description') is-invalid @enderror" 
                                                  id="description" name="description" rows="4">{{ old('description', $course->description) }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="teacher_ids" class="creative-form-label">Assigned Teachers <span class="text-danger">*</span></label>
                                        <select class="creative-form-input select2 @error('teacher_ids') is-invalid @enderror" 
                                                id="teacher_ids" name="teacher_ids[]" multiple required>
                                            @foreach($teachers as $teacher)
                                                <option value="{{ $teacher->id }}" 
                                                        {{ (is_array(old('teacher_ids', $course->teachers->pluck('id')->toArray())) && in_array($teacher->id, old('teacher_ids', $course->teachers->pluck('id')->toArray()))) ? 'selected' : '' }}>
                                                    {{ $teacher->name }} ({{ $teacher->email }})
                                                </option>
                                            @endforeach
                                        </select>
                                        <small class="form-text text-muted">You can select multiple teachers for this course.</small>
                                        @error('teacher_ids')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category" class="creative-form-label">Category</label>
                                                <input type="text" class="creative-form-input @error('category') is-invalid @enderror" 
                                                       id="category" name="category" value="{{ old('category', $course->category) }}">
                                                @error('category')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="difficulty_level" class="creative-form-label">Difficulty Level</label>
                                                <select class="creative-form-input @error('difficulty_level') is-invalid @enderror" 
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
                                                <label for="duration_hours" class="creative-form-label">Duration (Hours)</label>
                                                <input type="number" class="creative-form-input @error('duration_hours') is-invalid @enderror" 
                                                       id="duration_hours" name="duration_hours" value="{{ old('duration_hours', $course->duration_hours) }}" min="1">
                                                @error('duration_hours')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="max_students" class="creative-form-label">Maximum Students</label>
                                                <input type="number" class="creative-form-input @error('max_students') is-invalid @enderror" 
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
                                                <label for="enrollment_start_date" class="creative-form-label">Enrollment Start Date</label>
                                                <input type="date" class="creative-form-input @error('enrollment_start_date') is-invalid @enderror" 
                                                       id="enrollment_start_date" name="enrollment_start_date" 
                                                       value="{{ old('enrollment_start_date', $course->enrollment_start_date?->format('Y-m-d')) }}">
                                                @error('enrollment_start_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="enrollment_end_date" class="creative-form-label">Enrollment End Date</label>
                                                <input type="date" class="creative-form-input @error('enrollment_end_date') is-invalid @enderror" 
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
                                                <label for="course_start_date" class="creative-form-label">Course Start Date</label>
                                                <input type="date" class="creative-form-input @error('course_start_date') is-invalid @enderror" 
                                                       id="course_start_date" name="course_start_date" 
                                                       value="{{ old('course_start_date', $course->course_start_date?->format('Y-m-d')) }}">
                                                @error('course_start_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="course_end_date" class="creative-form-label">Course End Date</label>
                                                <input type="date" class="creative-form-input @error('course_end_date') is-invalid @enderror" 
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
                                <div class="creative-card mb-4 fade-in-up stagger-2">
                                    <div class="creative-card-header">
                                        <h3><i class="fas fa-toggle-on"></i>Course Status</h3>
                                    </div>
                                    <div class="creative-card-body">
                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_published" name="is_published" 
                                                       value="1" {{ old('is_published', $course->is_published) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_published">
                                                    Published
                                                </label>
                                            </div>
                                            <small class="text-muted">Make course visible to students</small>
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="is_featured" name="is_featured" 
                                                       value="1" {{ old('is_featured', $course->is_featured) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="is_featured">
                                                    Featured Course
                                                </label>
                                            </div>
                                            <small class="text-muted">Highlight this course on the homepage</small>
                                        </div>

                                        <hr>

                                        <div class="mb-3">
                                            <small class="text-muted d-block">Created</small>
                                            <span>{{ $course->created_at->format('M j, Y \a\t g:i A') }}</span>
                                        </div>

                                        <div class="mb-3">
                                            <small class="text-muted d-block">Last Updated</small>
                                            <span>{{ $course->updated_at->format('M j, Y \a\t g:i A') }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Course Statistics -->
                                <div class="creative-card fade-in-up stagger-3">
                                    <div class="creative-card-header">
                                        <h3><i class="fas fa-chart-bar"></i>Course Statistics</h3>
                                    </div>
                                    <div class="creative-card-body">
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span>Enrolled Students:</span>
                                                <strong>{{ $course->enrollments()->where('status', 'active')->count() }}</strong>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span>Total Modules:</span>
                                                <strong>{{ $course->modules()->count() }}</strong>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <div class="d-flex justify-content-between">
                                                <span>Assignments:</span>
                                                <strong>{{ $course->assignments()->count() }}</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('admin.courses.show', $course) }}" class="creative-btn creative-btn-outline">Cancel</a>
                            <button type="submit" class="creative-btn creative-btn-primary">
                                <i class="fas fa-save"></i>Update Course
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Course Hierarchy Management Section -->
            <div id="course-hierarchy-app" 
                 data-course-id="{{ $course->id }}" 
                 data-csrf-token="{{ csrf_token() }}"
                 data-user-role="admin">
            </div>
        </div>
    </div>
</div>

@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--multiple {
        border: 1px solid rgba(0,0,0,0.1);
        border-radius: 8px;
        padding: 5px;
        background-color: #fff;
    }
    .dark .select2-container--default .select2-selection--multiple {
        background-color: #0f172a;
        border-color: rgba(255,255,255,0.1);
    }
    .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #f97316;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #f97316;
        border: none;
        color: white;
        border-radius: 4px;
        padding: 2px 8px;
    }
    .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: white;
        margin-right: 5px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Select teachers...",
            allowClear: true
        });
    });
</script>
    @vite(['resources/js/course-hierarchy-simple.jsx', 'resources/css/course-hierarchy.css'])
@endpush