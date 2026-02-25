@extends('layouts.app')

@section('title', 'Create Forum - ' . $course->title)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Create New Forum</h4>
                    <small class="text-muted">Course: {{ $course->title }}</small>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('courses.forums.store', $course) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label for="title" class="form-label">Forum Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" 
                                   id="title" name="title" value="{{ old('title') }}" required>
                            @error('title')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            <div class="form-text">Provide a brief description of what this forum is for.</div>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_locked" name="is_locked" value="1" {{ old('is_locked') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_locked">
                                    Lock Forum
                                </label>
                                <div class="form-text">Locked forums can only be posted to by teachers and administrators.</div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('courses.forums.index', $course) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Forums
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Create Forum
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection