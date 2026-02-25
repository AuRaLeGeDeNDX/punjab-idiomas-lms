@extends('layouts.app')

@section('title', 'Edit Course - Teacher')

@section('content')
<div class="container">
    <h1>Edit Course: {{ $course->title }}</h1>
    
    <form method="POST" action="{{ route('teacher.courses.update', $course) }}">
        @csrf
        @method('PUT')
        
        <div class="mb-3">
            <label for="title" class="form-label">Course Title</label>
            <input type="text" class="form-control" id="title" name="title" value="{{ $course->title }}" required>
        </div>
        
        <div class="mb-3">
            <label for="description" class="form-label">Description</label>
            <textarea class="form-control" id="description" name="description" rows="4">{{ $course->description }}</textarea>
        </div>
        
        <button type="submit" class="btn btn-primary">Update Course</button>
        <a href="{{ route('teacher.courses.show', $course) }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection