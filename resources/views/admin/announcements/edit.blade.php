@extends('layouts.app')

@section('title', 'Edit System Announcement')

@push('styles')
@vite(['resources/css/design-system.css', 'resources/css/components/buttons.css', 'resources/css/components/cards.css', 'resources/css/components/forms.css', 'resources/css/components/alerts.css', 'resources/css/components/navigation.css'])
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
                <h4 class="mb-0">Edit System Announcement</h4>
                <small class="text-muted">Update system-wide announcement</small>
            </div>
            
            <div class="card-body">
                <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label for="title" class="form-label form-label-design-system">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-design-system @error('title') is-invalid @enderror" 
                               id="title" name="title" value="{{ old('title', $announcement->title) }}" required>
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="priority" class="form-label form-label-design-system">Priority <span class="text-danger">*</span></label>
                        <select class="form-select form-control-design-system @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                            <option value="low" {{ old('priority', $announcement->priority) === 'low' ? 'selected' : '' }}>Low Priority</option>
                            <option value="medium" {{ old('priority', $announcement->priority) === 'medium' ? 'selected' : '' }}>Medium Priority</option>
                            <option value="high" {{ old('priority', $announcement->priority) === 'high' ? 'selected' : '' }}>High Priority</option>
                        </select>
                        @error('priority')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="display_duration_days" class="form-label form-label-design-system">Display Duration (Optional)</label>
                        <select class="form-select form-control-design-system @error('display_duration_days') is-invalid @enderror" 
                                id="display_duration_days" name="display_duration_days">
                            <option value="">No expiration (permanent)</option>
                            <option value="1" {{ old('display_duration_days', $announcement->display_duration_days) == '1' ? 'selected' : '' }}>1 Day</option>
                            <option value="3" {{ old('display_duration_days', $announcement->display_duration_days) == '3' ? 'selected' : '' }}>3 Days</option>
                            <option value="7" {{ old('display_duration_days', $announcement->display_duration_days) == '7' ? 'selected' : '' }}>1 Week</option>
                            <option value="14" {{ old('display_duration_days', $announcement->display_duration_days) == '14' ? 'selected' : '' }}>2 Weeks</option>
                            <option value="30" {{ old('display_duration_days', $announcement->display_duration_days) == '30' ? 'selected' : '' }}>1 Month</option>
                            <option value="90" {{ old('display_duration_days', $announcement->display_duration_days) == '90' ? 'selected' : '' }}>3 Months</option>
                        </select>
                        <div class="form-text">
                            How long should this announcement be displayed to users?
                            @if($announcement->display_until)
                                <br><strong>Current expiration:</strong> {{ $announcement->display_until->format('M j, Y g:i A') }}
                            @endif
                        </div>
                        @error('display_duration_days')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="mb-3">
                        <label for="message" class="form-label form-label-design-system">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-design-system @error('message') is-invalid @enderror" 
                                  id="message" name="message" rows="8" required>{{ old('message', $announcement->message) }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="alert alert-design-system alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Note:</strong> This is a system-wide announcement. Changes will be visible to all users who received the original announcement.
                    </div>
                    
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.announcements.show', $announcement) }}" class="btn quick-action-btn action-settings">
                            <i class="fas fa-arrow-left"></i> Cancel
                        </a>
                        <button type="submit" class="btn quick-action-btn action-create-course">
                            <i class="fas fa-save"></i> Update Announcement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>
@endsection