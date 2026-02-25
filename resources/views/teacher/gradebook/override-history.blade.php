@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Grade Override History</h1>
            <p class="text-gray-600">
                View all administrative overrides for this grade.
            </p>
        </div>

        <!-- Grade Information Card -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Grade Information</h2>
            
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
                    <p class="text-sm text-gray-600">Status</p>
                    <p class="font-semibold text-gray-900">
                        @if($grade->isLocked())
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                </svg>
                                Locked
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Unlocked
                            </span>
                        @endif
                    </p>
                </div>
                
                <div>
                    <p class="text-sm text-gray-600">Total Overrides</p>
                    <p class="font-semibold text-gray-900">{{ $grade->overrides->count() }}</p>
                </div>
            </div>
        </div>

        <!-- Override History -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Override History</h2>
                @can('override', $grade)
                    <a 
                        href="{{ route('admin.gradebook.show-override', $grade) }}" 
                        class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        New Override
                    </a>
                @endcan
            </div>
            
            @if($grade->overrides->count() > 0)
                <div class="space-y-6">
                    @foreach($grade->overrides as $override)
                    <div class="border-l-4 {{ $override->isIncrease() ? 'border-green-500' : 'border-red-500' }} pl-4 py-3 bg-gray-50 rounded-r">
                        <div class="flex justify-between items-start mb-2">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="text-lg font-bold text-gray-900">
                                        {{ $override->original_score }} → {{ $override->new_score }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $override->isIncrease() ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $override->isIncrease() ? '+' : '' }}{{ number_format($override->getScoreDifference(), 2) }} points
                                    </span>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm text-gray-600 mb-3">
                                    <div>
                                        <span class="font-medium">Overridden By:</span> {{ $override->admin->name }}
                                    </div>
                                    <div>
                                        <span class="font-medium">Date:</span> {{ $override->overridden_at->format('M j, Y g:i A') }}
                                    </div>
                                    <div class="md:col-span-2">
                                        <span class="font-medium">Time Ago:</span> {{ $override->overridden_at->diffForHumans() }}
                                    </div>
                                </div>
                                
                                <div class="bg-white rounded p-3 border border-gray-200">
                                    <p class="text-sm font-medium text-gray-700 mb-1">Reason:</p>
                                    <p class="text-sm text-gray-900">{{ $override->reason }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No Override History</h3>
                    <p class="mt-1 text-sm text-gray-500">This grade has not been overridden by an administrator.</p>
                    @can('override', $grade)
                        <div class="mt-6">
                            <a 
                                href="{{ route('admin.gradebook.show-override', $grade) }}" 
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                            >
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Create Override
                            </a>
                        </div>
                    @endcan
                </div>
            @endif
        </div>

        <!-- Back Button -->
        <div class="mt-6">
            <a 
                href="{{ route('teacher.gradebook.index', $grade->submission->assignment->course) }}" 
                class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800"
            >
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Grade Book
            </a>
        </div>
    </div>
</div>
@endsection
