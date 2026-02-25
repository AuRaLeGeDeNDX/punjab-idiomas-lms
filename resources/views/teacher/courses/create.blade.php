@extends('layouts.app')

@section('title', 'Create Course - Teacher')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h3 mb-0">Create New Course</h1>
                <a href="{{ route('teacher.courses.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to My Courses
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('teacher.courses.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-8">
                                <!-- Basic Information -->
                                <div class="mb-4">
                                    <h5 class="card-title">Basic Information</h5>
                                    
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Course Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                               id="title" name="title" value="{{ old('title') }}" required>
                                        @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description</label>
                                        <textarea class="form-control @error('description') is-invalid @enderror" 
                                                  id="description" name="description" rows="4">{{ old('description') }}</textarea>
                                        @error('description')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="category" class="form-label">Category</label>
                                                <input type="text" class="form-control @error('category') is-invalid @enderror" 
                                                       id="category" name="category" value="{{ old('category') }}">
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
                                                    <option value="beginner" {{ old('difficulty_level') == 'beginner' ? 'selected' : '' }}>Beginner</option>
                                                    <option value="intermediate" {{ old('difficulty_level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                                                    <option value="advanced" {{ old('difficulty_level') == 'advanced' ? 'selected' : '' }}>Advanced</option>
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
                                                       id="duration_hours" name="duration_hours" value="{{ old('duration_hours') }}" min="1">
                                                @error('duration_hours')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="max_students" class="form-label">Maximum Students</label>
                                                <input type="number" class="form-control @error('max_students') is-invalid @enderror" 
                                                       id="max_students" name="max_students" value="{{ old('max_students') }}" min="1">
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
                                                       id="enrollment_start_date" name="enrollment_start_date" value="{{ old('enrollment_start_date') }}">
                                                @error('enrollment_start_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="enrollment_end_date" class="form-label">Enrollment End Date</label>
                                                <input type="date" class="form-control @error('enrollment_end_date') is-invalid @enderror" 
                                                       id="enrollment_end_date" name="enrollment_end_date" value="{{ old('enrollment_end_date') }}">
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
                                                       id="course_start_date" name="course_start_date" value="{{ old('course_start_date') }}">
                                                @error('course_start_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="course_end_date" class="form-label">Course End Date</label>
                                                <input type="date" class="form-control @error('course_end_date') is-invalid @enderror" 
                                                       id="course_end_date" name="course_end_date" value="{{ old('course_end_date') }}">
                                                @error('course_end_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Course Guidelines -->
                                <div class="card">
                                    <div class="card-header">
                                        <h6 class="card-title mb-0">Course Creation Tips</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <h6><i class="fas fa-lightbulb text-warning me-2"></i>Title</h6>
                                            <small class="text-muted">Choose a clear, descriptive title that tells students what they'll learn.</small>
                                        </div>

                                        <div class="mb-3">
                                            <h6><i class="fas fa-align-left text-info me-2"></i>Description</h6>
                                            <small class="text-muted">Explain what students will achieve, prerequisites, and course outcomes.</small>
                                        </div>

                                        <div class="mb-3">
                                            <h6><i class="fas fa-layer-group text-success me-2"></i>Difficulty</h6>
                                            <small class="text-muted">Set appropriate expectations for your target audience.</small>
                                        </div>

                                        <div class="mb-3">
                                            <h6><i class="fas fa-clock text-primary me-2"></i>Duration</h6>
                                            <small class="text-muted">Estimate total learning time including assignments and activities.</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('teacher.courses.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Create Course
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-update enrollment end date when start date changes
    document.getElementById('enrollment_start_date').addEventListener('change', function() {
        const startDate = new Date(this.value);
        const endDateInput = document.getElementById('enrollment_end_date');
        
        if (startDate && !endDateInput.value) {
            const endDate = new Date(startDate);
            endDate.setDate(endDate.getDate() + 30); // Default 30 days enrollment period
            endDateInput.value = endDate.toISOString().split('T')[0];
        }
    });

    // Auto-update course end date when start date changes
    document.getElementById('course_start_date').addEventListener('change', function() {
        const startDate = new Date(this.value);
        const endDateInput = document.getElementById('course_end_date');
        
        if (startDate && !endDateInput.value) {
            const endDate = new Date(startDate);
            endDate.setDate(endDate.getDate() + 90); // Default 90 days course duration
            endDateInput.value = endDate.toISOString().split('T')[0];
        }
    });
</script>
@endpush