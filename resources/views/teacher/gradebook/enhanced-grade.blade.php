@extends('layouts.app')

@section('title', 'Grade Submission - Enhanced Interface')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Grade Submission</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $assignment->title }} - {{ $submission->student->name }}
                    </p>
                </div>
                <div class="flex items-center space-x-3">
                    <span id="autosave-status" class="text-sm text-gray-500">
                        <span class="inline-block w-2 h-2 rounded-full bg-gray-400 mr-2"></span>
                        Not saved
                    </span>
                    <a href="{{ route('teacher.gradebook.index', $assignment->course) }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                        Back to Gradebook
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Split Screen Layout -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Left Panel: Submission Content -->
            <div class="space-y-6">
                <!-- Submission Info Card -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Submission Details</h2>
                    
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Student:</dt>
                            <dd class="text-sm text-gray-900">{{ $submission->student->name }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Submitted:</dt>
                            <dd class="text-sm text-gray-900">
                                {{ $submission->submitted_at?->format('M d, Y g:i A') ?? 'Not submitted' }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Status:</dt>
                            <dd>
                                @if($submission->is_late)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Late
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        On Time
                                    </span>
                                @endif
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Max Score:</dt>
                            <dd class="text-sm text-gray-900">{{ $assignment->max_score }} points</dd>
                        </div>
                    </dl>
                </div>

                <!-- Text Content -->
                @if($submission->content)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Submission Text</h3>
                    <div class="prose prose-sm max-w-none">
                        {!! nl2br(e($submission->content)) !!}
                    </div>
                </div>
                @endif

                <!-- Files -->
                @if($submission->files->count() > 0)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Attached Files</h3>
                    <div class="space-y-3">
                        @foreach($submission->files as $file)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                            <div class="flex items-center space-x-3">
                                <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $file->file_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $file->getFormattedSize() }}</p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                @if(in_array($file->mime_type, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif', 'text/plain']))
                                <button onclick="previewFile('{{ $file->id }}', '{{ $file->mime_type }}')" 
                                        class="px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-800">
                                    Preview
                                </button>
                                @endif
                                <a href="{{ route('files.download', $file->getSignedUrl()) }}" 
                                   class="px-3 py-1 text-xs font-medium text-gray-600 hover:text-gray-800">
                                    Download
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- File Preview Modal -->
                <div id="file-preview-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                    <div class="bg-white rounded-lg max-w-4xl w-full max-h-[90vh] overflow-hidden">
                        <div class="flex items-center justify-between p-4 border-b">
                            <h3 class="text-lg font-semibold">File Preview</h3>
                            <button onclick="closePreview()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        <div id="preview-content" class="p-4 overflow-auto max-h-[calc(90vh-80px)]">
                            <!-- Preview content will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Panel: Grading Form -->
            <div class="space-y-6">
                <form id="grading-form" action="{{ route('teacher.gradebook.store-grade', [$assignment, $submission]) }}" method="POST">
                    @csrf
                    
                    <!-- Score Input -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Grade</h3>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="score" class="block text-sm font-medium text-gray-700 mb-2">
                                    Score (out of {{ $assignment->max_score }})
                                </label>
                                <input type="number" 
                                       name="score" 
                                       id="score" 
                                       min="0" 
                                       max="{{ $assignment->max_score }}" 
                                       step="0.01"
                                       value="{{ old('score', $existingGrade?->score) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       required>
                                @error('score')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Percentage Display -->
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">Percentage:</span>
                                <span id="percentage-display" class="text-lg font-bold text-gray-900">0%</span>
                            </div>
                        </div>
                    </div>

                    <!-- Rubric Grading (if exists) -->
                    @if($assignment->rubric)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Rubric: {{ $assignment->rubric->title }}</h3>
                        
                        <div class="space-y-4">
                            @foreach($assignment->rubric->criteria as $criterion)
                            <div class="border-b border-gray-200 pb-4 last:border-0">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <h4 class="text-sm font-medium text-gray-900">{{ $criterion->criterion_name }}</h4>
                                        @if($criterion->criterion_description)
                                        <p class="text-xs text-gray-600 mt-1">{{ $criterion->criterion_description }}</p>
                                        @endif
                                    </div>
                                    <span class="text-sm text-gray-500 ml-4">/ {{ $criterion->max_points }} pts</span>
                                </div>
                                <input type="number" 
                                       name="rubric_scores[{{ $criterion->id }}]" 
                                       min="0" 
                                       max="{{ $criterion->max_points }}" 
                                       step="0.01"
                                       class="rubric-score w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       data-max="{{ $criterion->max_points }}">
                            </div>
                            @endforeach
                        </div>

                        <div class="mt-4 p-3 bg-blue-50 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-blue-900">Total Rubric Score:</span>
                                <span id="rubric-total" class="text-lg font-bold text-blue-900">0 / {{ $assignment->rubric->getTotalPoints() }}</span>
                            </div>
                            <button type="button" 
                                    onclick="applyRubricScore()" 
                                    class="mt-2 w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                                Apply Rubric Score to Grade
                            </button>
                        </div>
                    </div>
                    @endif

                    <!-- Feedback -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Feedback</h3>
                            <button type="button" 
                                    onclick="toggleQuickComments()" 
                                    class="text-sm text-blue-600 hover:text-blue-800">
                                Quick Comments
                            </button>
                        </div>

                        <!-- Quick Comments Dropdown -->
                        <div id="quick-comments" class="hidden mb-4 p-3 bg-gray-50 rounded-lg">
                            <p class="text-xs text-gray-600 mb-2">Click to insert:</p>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" onclick="insertComment('Excellent work!')" class="px-2 py-1 text-xs bg-white border border-gray-300 rounded hover:bg-gray-50">Excellent work!</button>
                                <button type="button" onclick="insertComment('Good effort, but needs improvement.')" class="px-2 py-1 text-xs bg-white border border-gray-300 rounded hover:bg-gray-50">Needs improvement</button>
                                <button type="button" onclick="insertComment('Please see me during office hours.')" class="px-2 py-1 text-xs bg-white border border-gray-300 rounded hover:bg-gray-50">See me</button>
                                <button type="button" onclick="insertComment('Well organized and clearly written.')" class="px-2 py-1 text-xs bg-white border border-gray-300 rounded hover:bg-gray-50">Well organized</button>
                                <button type="button" onclick="insertComment('Missing key requirements.')" class="px-2 py-1 text-xs bg-white border border-gray-300 rounded hover:bg-gray-50">Missing requirements</button>
                            </div>
                        </div>

                        <textarea name="feedback" 
                                  id="feedback" 
                                  rows="8" 
                                  class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                  placeholder="Enter feedback for the student...">{{ old('feedback', $existingGrade?->feedback) }}</textarea>
                        @error('feedback')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <div class="flex items-center justify-between">
                            <button type="button" 
                                    onclick="saveDraft()" 
                                    class="px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Save Draft
                            </button>
                            <button type="submit" 
                                    class="px-6 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700">
                                Save Grade
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Previous Grades (if exists) -->
                @if($existingGrade)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Previous Grade</h3>
                    <dl class="space-y-2">
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Score:</dt>
                            <dd class="text-sm text-gray-900">{{ $existingGrade->score }} / {{ $assignment->max_score }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Graded by:</dt>
                            <dd class="text-sm text-gray-900">{{ $existingGrade->grader->name ?? 'System' }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm font-medium text-gray-500">Graded at:</dt>
                            <dd class="text-sm text-gray-900">{{ $existingGrade->graded_at->format('M d, Y g:i A') }}</dd>
                        </div>
                    </dl>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-save functionality
let autosaveTimer;
let isDirty = false;

// Track form changes
document.getElementById('grading-form').addEventListener('input', function() {
    isDirty = true;
    updateAutosaveStatus('pending');
    
    // Reset autosave timer
    clearTimeout(autosaveTimer);
    autosaveTimer = setTimeout(saveDraft, 30000); // 30 seconds
});

// Calculate percentage
document.getElementById('score').addEventListener('input', function() {
    const score = parseFloat(this.value) || 0;
    const maxScore = {{ $assignment->max_score }};
    const percentage = (score / maxScore * 100).toFixed(1);
    document.getElementById('percentage-display').textContent = percentage + '%';
});

// Calculate rubric total
document.querySelectorAll('.rubric-score').forEach(input => {
    input.addEventListener('input', function() {
        let total = 0;
        document.querySelectorAll('.rubric-score').forEach(el => {
            total += parseFloat(el.value) || 0;
        });
        document.getElementById('rubric-total').textContent = total.toFixed(2) + ' / {{ $assignment->rubric?->getTotalPoints() ?? 0 }}';
    });
});

// Apply rubric score to main score
function applyRubricScore() {
    let total = 0;
    document.querySelectorAll('.rubric-score').forEach(el => {
        total += parseFloat(el.value) || 0;
    });
    document.getElementById('score').value = total.toFixed(2);
    document.getElementById('score').dispatchEvent(new Event('input'));
}

// Save draft
function saveDraft() {
    if (!isDirty) return;
    
    updateAutosaveStatus('saving');
    
    const formData = new FormData(document.getElementById('grading-form'));
    
    fetch('{{ route("teacher.gradebook.save-draft", [$assignment, $submission]) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        isDirty = false;
        updateAutosaveStatus('saved');
        setTimeout(() => updateAutosaveStatus('idle'), 2000);
    })
    .catch(error => {
        console.error('Autosave failed:', error);
        updateAutosaveStatus('error');
    });
}

// Update autosave status indicator
function updateAutosaveStatus(status) {
    const statusEl = document.getElementById('autosave-status');
    const dot = statusEl.querySelector('.inline-block');
    
    switch(status) {
        case 'pending':
            dot.className = 'inline-block w-2 h-2 rounded-full bg-yellow-400 mr-2';
            statusEl.innerHTML = dot.outerHTML + 'Unsaved changes';
            break;
        case 'saving':
            dot.className = 'inline-block w-2 h-2 rounded-full bg-blue-400 mr-2 animate-pulse';
            statusEl.innerHTML = dot.outerHTML + 'Saving...';
            break;
        case 'saved':
            dot.className = 'inline-block w-2 h-2 rounded-full bg-green-400 mr-2';
            statusEl.innerHTML = dot.outerHTML + 'Saved';
            break;
        case 'error':
            dot.className = 'inline-block w-2 h-2 rounded-full bg-red-400 mr-2';
            statusEl.innerHTML = dot.outerHTML + 'Save failed';
            break;
        default:
            dot.className = 'inline-block w-2 h-2 rounded-full bg-gray-400 mr-2';
            statusEl.innerHTML = dot.outerHTML + 'All changes saved';
    }
}

// Quick comments
function toggleQuickComments() {
    document.getElementById('quick-comments').classList.toggle('hidden');
}

function insertComment(text) {
    const textarea = document.getElementById('feedback');
    const cursorPos = textarea.selectionStart;
    const textBefore = textarea.value.substring(0, cursorPos);
    const textAfter = textarea.value.substring(cursorPos);
    textarea.value = textBefore + text + textAfter;
    textarea.focus();
    textarea.setSelectionRange(cursorPos + text.length, cursorPos + text.length);
    textarea.dispatchEvent(new Event('input'));
}

// File preview
function previewFile(fileId, mimeType) {
    const modal = document.getElementById('file-preview-modal');
    const content = document.getElementById('preview-content');
    
    modal.classList.remove('hidden');
    content.innerHTML = '<div class="text-center py-8"><div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div></div>';
    
    // Load preview content based on mime type
    // This would need actual implementation with file URLs
    setTimeout(() => {
        if (mimeType.startsWith('image/')) {
            content.innerHTML = '<img src="/api/files/' + fileId + '/preview" class="max-w-full h-auto">';
        } else if (mimeType === 'application/pdf') {
            content.innerHTML = '<iframe src="/api/files/' + fileId + '/preview" class="w-full h-[600px]"></iframe>';
        } else if (mimeType === 'text/plain') {
            content.innerHTML = '<pre class="whitespace-pre-wrap text-sm">Loading text content...</pre>';
        }
    }, 500);
}

function closePreview() {
    document.getElementById('file-preview-modal').classList.add('hidden');
}

// Warn before leaving with unsaved changes
window.addEventListener('beforeunload', function(e) {
    if (isDirty) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// Initialize percentage display
document.getElementById('score').dispatchEvent(new Event('input'));
</script>
@endpush
@endsection
