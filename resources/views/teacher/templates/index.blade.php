@extends('layouts.app')

@section('title', 'Assignment Templates')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Assignment Templates</h1>
                <p class="text-gray-600 mt-1">Create reusable templates for assignments</p>
            </div>
            <a href="{{ route('teacher.templates.create') }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Create Template
            </a>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
        @endif

        <!-- Templates Grid -->
        @if($templates->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($templates as $template)
            <div class="bg-white rounded-lg shadow-md border border-gray-200 hover:shadow-lg transition">
                <div class="p-6">
                    <!-- Template Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $template->title }}</h3>
                            <div class="flex items-center space-x-2 text-sm text-gray-600">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    {{ ucfirst($template->assignment_type) }}
                                </span>
                                @if($template->is_public)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                    Public
                                </span>
                                @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                    Private
                                </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Template Details -->
                    @if($template->description)
                    <p class="text-sm text-gray-600 mb-4 line-clamp-3">{{ $template->description }}</p>
                    @endif

                    <div class="space-y-2 mb-4">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Submission Type:</span>
                            <span class="font-medium text-gray-900">{{ ucfirst($template->submission_type) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Max Score:</span>
                            <span class="font-medium text-gray-900">{{ $template->max_score }} points</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Created By:</span>
                            <span class="font-medium text-gray-900">{{ $template->teacher->name }}</span>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-2 pt-4 border-t border-gray-200">
                        <button onclick="showApplyModal({{ $template->id }}, '{{ $template->title }}')" 
                                class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 transition">
                            Use Template
                        </button>
                        @if($template->teacher_id === auth()->id())
                        <form action="{{ route('teacher.templates.destroy', $template) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    onclick="return confirm('Are you sure you want to delete this template?')"
                                    class="px-3 py-2 border border-red-300 text-red-600 text-sm rounded hover:bg-red-50 transition">
                                Delete
                            </button>
                        </form>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $templates->links() }}
        </div>
        @else
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Templates Yet</h3>
            <p class="text-gray-600 mb-4">Create your first assignment template to reuse across courses</p>
            <a href="{{ route('teacher.templates.create') }}" 
               class="inline-block px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">
                Create Template
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Apply Template Modal -->
<div id="applyModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Apply Template</h3>
            <p class="text-sm text-gray-600 mb-4">Select a course and subpage to create an assignment from this template.</p>
            
            <form id="applyForm" method="GET">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Course</label>
                    <select name="course_id" id="courseSelect" required 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Select a course...</option>
                        <!-- Populated via JavaScript or server-side -->
                    </select>
                </div>
                
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" onclick="closeApplyModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">
                        Continue
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showApplyModal(templateId, templateTitle) {
    document.getElementById('applyModal').classList.remove('hidden');
    // In a real implementation, you would populate the course dropdown here
}

function closeApplyModal() {
    document.getElementById('applyModal').classList.add('hidden');
}
</script>
@endpush
@endsection
