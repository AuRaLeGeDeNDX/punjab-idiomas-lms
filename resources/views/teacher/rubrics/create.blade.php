@extends('layouts.app')

@section('title', 'Create Rubric')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Create Rubric</h1>
            <p class="text-gray-600">Assignment: {{ $assignment->title }}</p>
            <p class="text-sm text-gray-500">Max Score: {{ $assignment->max_score }} points</p>
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

        <!-- Rubric Form -->
        <form action="{{ route('teacher.rubrics.store', $assignment) }}" method="POST" class="bg-white rounded-lg shadow-md p-6">
            @csrf

            <!-- Rubric Title -->
            <div class="mb-6">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Rubric Title <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       name="title" 
                       id="title" 
                       value="{{ old('title', $assignment->title . ' Rubric') }}"
                       required
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <!-- Rubric Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Description
                </label>
                <textarea name="description" 
                          id="description" 
                          rows="3"
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                          placeholder="Optional description of the rubric">{{ old('description') }}</textarea>
            </div>

            <!-- Criteria Section -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Grading Criteria</h3>
                    <button type="button" 
                            onclick="addCriterion()" 
                            class="px-3 py-1 bg-green-600 text-white text-sm rounded hover:bg-green-700">
                        <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add Criterion
                    </button>
                </div>

                <div id="criteriaContainer" class="space-y-4">
                    <!-- Criteria will be added here dynamically -->
                </div>

                <div class="mt-4 p-4 bg-blue-50 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-700">Total Points:</span>
                        <span id="totalPoints" class="text-lg font-bold text-blue-600">0</span>
                    </div>
                    <p class="text-xs text-gray-600 mt-1">
                        Must equal assignment max score: {{ $assignment->max_score }} points
                    </p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                <a href="{{ route('teacher.courses.modules.subpages.assignments.show', [$assignment->course, $assignment->module, $assignment->subpage, $assignment]) }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                    Cancel
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                    Create Rubric
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
let criterionCount = 0;

function addCriterion() {
    const container = document.getElementById('criteriaContainer');
    const index = criterionCount++;
    
    const criterionHtml = `
        <div class="criterion-item border border-gray-300 rounded-lg p-4 bg-gray-50" data-index="${index}">
            <div class="flex items-start justify-between mb-3">
                <h4 class="text-sm font-semibold text-gray-900">Criterion ${index + 1}</h4>
                <button type="button" 
                        onclick="removeCriterion(${index})" 
                        class="text-red-600 hover:text-red-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Criterion Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="criteria[${index}][name]" 
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="e.g., Content Quality">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Description
                    </label>
                    <textarea name="criteria[${index}][description]" 
                              rows="2"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Describe what this criterion evaluates"></textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Maximum Points <span class="text-red-500">*</span>
                    </label>
                    <input type="number" 
                           name="criteria[${index}][max_points]" 
                           required
                           min="0"
                           step="0.01"
                           onchange="updateTotalPoints()"
                           class="criterion-points w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="0">
                </div>
            </div>
        </div>
    `;
    
    container.insertAdjacentHTML('beforeend', criterionHtml);
    updateTotalPoints();
}

function removeCriterion(index) {
    const criterion = document.querySelector(`[data-index="${index}"]`);
    if (criterion) {
        criterion.remove();
        updateTotalPoints();
    }
}

function updateTotalPoints() {
    const pointInputs = document.querySelectorAll('.criterion-points');
    let total = 0;
    
    pointInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        total += value;
    });
    
    document.getElementById('totalPoints').textContent = total.toFixed(2);
    
    // Visual feedback if total doesn't match
    const maxScore = {{ $assignment->max_score }};
    const totalElement = document.getElementById('totalPoints');
    if (Math.abs(total - maxScore) < 0.01) {
        totalElement.classList.remove('text-red-600');
        totalElement.classList.add('text-green-600');
    } else {
        totalElement.classList.remove('text-green-600');
        totalElement.classList.add('text-red-600');
    }
}

// Add initial criterion
document.addEventListener('DOMContentLoaded', function() {
    addCriterion();
});
</script>
@endpush
@endsection
