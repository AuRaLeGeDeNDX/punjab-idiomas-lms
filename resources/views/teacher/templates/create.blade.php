@extends('layouts.app')

@section('title', 'Create Assignment Template')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Assignment Template</h1>
            <p class="text-gray-600">Create a reusable template for future assignments</p>
        </div>

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Template Form -->
        <form action="{{ route('teacher.templates.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
            @csrf

            <!-- Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Template Title <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="title" 
                       id="title" 
                       value="{{ old('title') }}"
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="e.g., Weekly Homework Template">
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Brief description of this template">{{ old('description') }}</textarea>
            </div>

            <!-- Instructions -->
            <div class="mb-6">
                <label for="instructions" class="block text-sm font-medium text-gray-700 mb-2">
                    Default Instructions
                </label>
                <textarea name="instructions" 
                          id="instructions" 
                          rows="5"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Default instructions for assignments created from this template">{{ old('instructions') }}</textarea>
            </div>

            <!-- Assignment Type and Submission Type -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label for="assignment_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Assignment Type <span class="text-red-500">*</span>
                    </label>
                    <select name="assignment_type" 
                            id="assignment_type" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select type...</option>
                        <option value="homework" {{ old('assignment_type') === 'homework' ? 'selected' : '' }}>Homework</option>
                        <option value="project" {{ old('assignment_type') === 'project' ? 'selected' : '' }}>Project</option>
                        <option value="quiz" {{ old('assignment_type') === 'quiz' ? 'selected' : '' }}>Quiz</option>
                        <option value="exam" {{ old('assignment_type') === 'exam' ? 'selected' : '' }}>Exam</option>
                        <option value="essay" {{ old('assignment_type') === 'essay' ? 'selected' : '' }}>Essay</option>
                    </select>
                </div>

                <div>
                    <label for="submission_type" class="block text-sm font-medium text-gray-700 mb-2">
                        Submission Type <span class="text-red-500">*</span>
                    </label>
                    <select name="submission_type" 
                            id="submission_type" 
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select type...</option>
                        <option value="text" {{ old('submission_type') === 'text' ? 'selected' : '' }}>Text Only</option>
                        <option value="file" {{ old('submission_type') === 'file' ? 'selected' : '' }}>File Upload Only</option>
                        <option value="both" {{ old('submission_type') === 'both' ? 'selected' : '' }}>Text and File</option>
                    </select>
                </div>
            </div>

            <!-- Max Score -->
            <div class="mb-6">
                <label for="max_score" class="block text-sm font-medium text-gray-700 mb-2">
                    Maximum Score <span class="text-red-500">*</span>
                </label>
                <input type="number" 
                       name="max_score" 
                       id="max_score" 
                       value="{{ old('max_score', 100) }}"
                       min="0"
                       max="1000"
                       step="0.01"
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Visibility -->
            <div class="mb-6">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="is_public" 
                           value="1"
                           {{ old('is_public') ? 'checked' : '' }}
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="ml-2 text-sm text-gray-700">
                        Make this template public (visible to all teachers)
                    </span>
                </label>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('teacher.templates.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                    Create Template
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
