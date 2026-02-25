@extends('layouts.app')

@section('title', 'Create System Announcement')

@push('styles')
@vite(['resources/css/design-system.css', 'resources/css/components/buttons.css', 'resources/css/components/cards.css', 'resources/css/components/forms.css', 'resources/css/components/navigation.css'])
<link rel="stylesheet" href="{{ asset('css/admin-dashboard.css') }}">
@endpush

@section('sidebar')
    @include('admin.sidebar')
@endsection

@section('content')
<div class="admin-dashboard">
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-design-system">
            <div class="card-header">
                <h4 class="mb-0">Create System Announcement</h4>
                <small class="text-muted">Send announcements to users across the system</small>
            </div>
            
            <div class="card-body">
                <form action="{{ route('admin.announcements.store') }}" method="POST" id="announcementForm">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="title" class="form-label form-label-design-system">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-design-system @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title') }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="priority" class="form-label form-label-design-system">Priority <span class="text-danger">*</span></label>
                        <select class="form-select form-control-design-system @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                            <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Low Priority</option>
                            <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Medium Priority</option>
                            <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>High Priority</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="announcement_type" class="form-label form-label-design-system">Announcement Type <span class="text-danger">*</span></label>
                        <select class="form-select form-control-design-system @error('announcement_type') is-invalid @enderror" 
                                id="announcement_type" name="announcement_type" required onchange="toggleTargetOptions()">
                            <option value="">Select announcement type...</option>
                            <option value="system" {{ old('announcement_type') === 'system' ? 'selected' : '' }}>System-Wide (All Users)</option>
                            <option value="course" {{ old('announcement_type') === 'course' ? 'selected' : '' }}>All Courses</option>
                            <option value="selective" {{ old('announcement_type') === 'selective' ? 'selected' : '' }}>Selected Courses</option>
                        </select>
                        @error('announcement_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <!-- Target Roles (for system-wide announcements) -->
                    <div class="mb-3" id="target_roles_section" style="display: none;">
                        <label class="form-label form-label-design-system">Target User Roles</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="role_student" name="target_roles[]" value="Student" 
                                   {{ in_array('Student', old('target_roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_student">Students</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="role_teacher" name="target_roles[]" value="Teacher" 
                                   {{ in_array('Teacher', old('target_roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_teacher">Teachers</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="role_admin" name="target_roles[]" value="Admin" 
                                   {{ in_array('Admin', old('target_roles', [])) ? 'checked' : '' }}>
                            <label class="form-check-label" for="role_admin">Administrators</label>
                        </div>
                    </div>
                    
                    <!-- Target Courses (for selective announcements) -->
                    <div class="mb-3" id="target_courses_section" style="display: none;">
                        <label for="target_courses" class="form-label form-label-design-system">Select Courses</label>
                        <select class="form-select form-control-design-system" id="target_courses" name="target_courses[]" multiple size="8">
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}" {{ in_array($course->id, old('target_courses', [])) ? 'selected' : '' }}>
                                    {{ $course->title }} ({{ $course->teacher->name }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Hold Ctrl/Cmd to select multiple courses</div>
                        @error('target_courses')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="display_duration_days" class="form-label form-label-design-system">Display Duration (Optional)</label>
                        <select class="form-select form-control-design-system @error('display_duration_days') is-invalid @enderror" 
                                id="display_duration_days" name="display_duration_days">
                            <option value="">No expiration (permanent)</option>
                            <option value="1" {{ old('display_duration_days') == '1' ? 'selected' : '' }}>1 Day</option>
                            <option value="3" {{ old('display_duration_days') == '3' ? 'selected' : '' }}>3 Days</option>
                            <option value="7" {{ old('display_duration_days') == '7' ? 'selected' : '' }}>1 Week</option>
                            <option value="14" {{ old('display_duration_days') == '14' ? 'selected' : '' }}>2 Weeks</option>
                            <option value="30" {{ old('display_duration_days') == '30' ? 'selected' : '' }}>1 Month</option>
                            <option value="90" {{ old('display_duration_days') == '90' ? 'selected' : '' }}>3 Months</option>
                        </select>
                        <div class="form-text">How long should this announcement be displayed to users?</div>
                        @error('display_duration_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label form-label-design-system">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-design-system @error('message') is-invalid @enderror" 
                                  id="message" name="message" rows="8" required>{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.announcements.index') }}" class="btn quick-action-btn action-settings">
                            <i class="fas fa-arrow-left"></i> Back to Announcements
                        </a>
                        <button type="submit" class="btn quick-action-btn action-announcement">
                            <i class="fas fa-paper-plane"></i> Send Announcement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

<script>
function toggleTargetOptions() {
    const announcementType = document.getElementById('announcement_type').value;
    const rolesSection = document.getElementById('target_roles_section');
    const coursesSection = document.getElementById('target_courses_section');
    
    // Hide all sections first
    rolesSection.style.display = 'none';
    coursesSection.style.display = 'none';
    
    // Show relevant section based on type
    if (announcementType === 'system') {
        rolesSection.style.display = 'block';
        // Check all roles by default for system-wide
        document.getElementById('role_student').checked = true;
        document.getElementById('role_teacher').checked = true;
    } else if (announcementType === 'selective') {
        coursesSection.style.display = 'block';
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    toggleTargetOptions();
});
</script>
@endsection