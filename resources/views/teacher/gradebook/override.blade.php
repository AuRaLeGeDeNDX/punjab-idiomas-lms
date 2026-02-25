@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Override Locked Grade</h1>
            <p class="text-gray-600">
                As an administrator, you can override this locked grade. The original grader will be notified of this change.
            </p>
        </div>

        <!-- Grade Information Card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Current Grade Information</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600">Assignment</p>
                    <p class="font-semibold text-gray-900">{{ $grade->submission->assignment->title }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-600">Course</p>
                    <p class="font-semibold text-gray-900">{{ $grade->submission->assignment->course->title }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-600">Student</p>
                    <p class="font-semibold text-gray-900">{{ $grade->submission->user->name }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-600">Original Grader</p>
                    <p class="font-semibold text-gray-900">{{ $grade->grader->name }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-600">Current Score</p>
                    <p class="font-semibold text-gray-900">
                        {{ $grade->score }} / {{ $grade->submission->assignment->max_score }}
                        <span class="text-sm text-gray-600">({{ number_format($grade->getPercentage(), 1) }}%)</span>
                    </p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-600">Letter Grade</p>
                    <p class="font-semibold text-gray-900">{{ $grade->getLetterGrade() }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-600">Graded At</p>
                    <p class="font-semibold text-gray-900">{{ $grade->graded_at->format('M j, Y g:i A') }}</p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="font-semibold text-gray-900">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                            </svg>
                            Locked
                        </span>
                    </p>
                </div>
            </div>
            
            @if($grade->feedback)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <p class="text-sm text-gray-600 mb-2">Original Feedback</p>
                <div class="bg-gray-50 rounded p-3 text-gray-900">
                    {{ $grade->feedback }}
                </div>
            </div>
            @endif
        </div>

        <!-- Override History (if any) -->
        @if($grade->overrides->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Previous Overrides</h2>
            
            <div class="space-y-4">
                @foreach($grade->overrides as $override)
                <div class="border-l-4 border-blue-500 pl-4 py-2">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="font-semibold text-gray-900">
                                {{ $override->original_score }} → {{ $override->new_score }}
                                <span class="text-sm font-normal text-gray-600">
                                    ({{ $override->isIncrease() ? '+' : '' }}{{ number_format($override->getScoreDifference(), 2) }} points)
                                </span>
                            </p>
                            <p class="text-sm text-gray-600 mt-1">
                                By {{ $override->admin->name }} on {{ $override->overridden_at->format('M j, Y g:i A') }}
                            </p>
                            <p class="text-sm text-gray-700 mt-2">
                                <span class="font-medium">Reason:</span> {{ $override->reason }}
                            </p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Override Form -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Override Grade</h2>
            
            <form action="{{ route('admin.gradebook.override', $grade) }}" method="POST">
                @csrf
                
                <!-- New Score -->
                <div class="mb-6">
                    <label for="new_score" class="block text-sm font-medium text-gray-700 mb-2">
                        New Score <span class="text-red-500">*</span>
                    </label>
                    <div class="flex items-center space-x-2">
                        <input 
                            type="number" 
                            name="new_score" 
                            id="new_score" 
                            step="0.01"
                            min="0"
                            max="{{ $grade->submission->assignment->max_score }}"
                            value="{{ old('new_score', $grade->score) }}"
                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('new_score') border-red-500 @enderror"
                            required
                        >
                        <span class="text-gray-600">/ {{ $grade->submission->assignment->max_score }}</span>
                    </div>
                    @error('new_score')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        Enter the new score for this submission (0 to {{ $grade->submission->assignment->max_score }})
                    </p>
                </div>

                <!-- Reason -->
                <div class="mb-6">
                    <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Reason for Override <span class="text-red-500">*</span>
                    </label>
                    <textarea 
                        name="reason" 
                        id="reason" 
                        rows="4"
                        class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('reason') border-red-500 @enderror"
                        placeholder="Explain why this grade is being overridden (minimum 10 characters)..."
                        required
                    >{{ old('reason') }}</textarea>
                    @error('reason')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">
                        Provide a detailed explanation for this override. The original grader will see this reason.
                    </p>
                </div>

                <!-- Warning Notice -->
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                <strong>Important:</strong> This action will:
                            </p>
                            <ul class="mt-2 text-sm text-yellow-700 list-disc list-inside space-y-1">
                                <li>Update the grade score immediately</li>
                                <li>Create an audit log entry with your name and reason</li>
                                <li>Send a notification to {{ $grade->grader->name }} (the original grader)</li>
                                <li>Be visible in the override history</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-3">
                    <a 
                        href="{{ route('admin.gradebook.index', $grade->submission->assignment->course) }}" 
                        class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Cancel
                    </a>
                    <button 
                        type="submit" 
                        class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Override Grade
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
