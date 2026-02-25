@extends('layouts.app')

@section('title', 'Course Announcements')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">{{ $course->title }} - Announcements</h1>
            <p class="text-gray-600 mt-1">Stay updated with course announcements</p>
        </div>
        
        @can('update', $course)
        <a href="{{ route('courses.announcements.create', $course) }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
            Create Announcement
        </a>
        @endcan
    </div>

    @if($announcements->count() > 0)
        <div class="space-y-4">
            @foreach($announcements as $announcement)
                <div class="bg-white rounded-lg shadow-md border border-gray-200 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-xl font-semibold text-gray-900">
                                    {{ $announcement->title }}
                                </h3>
                                @if($announcement->priority === 'high')
                                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                        High Priority
                                    </span>
                                @elseif($announcement->priority === 'medium')
                                    <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                        Medium Priority
                                    </span>
                                @endif
                                
                                @if($announcement->isRecent())
                                    <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                        New
                                    </span>
                                @endif
                            </div>
                            
                            <div class="text-sm text-gray-500 mb-3">
                                By {{ $announcement->user->name }} • 
                                {{ $announcement->published_at->format('M j, Y g:i A') }}
                            </div>
                        </div>
                        
                        @can('update', $course)
                        <div class="flex gap-2">
                            <a href="{{ route('courses.announcements.edit', [$course, $announcement]) }}" 
                               class="text-blue-600 hover:text-blue-800 text-sm">
                                Edit
                            </a>
                            <form action="{{ route('courses.announcements.destroy', [$course, $announcement]) }}" 
                                  method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        class="text-red-600 hover:text-red-800 text-sm"
                                        onclick="return confirm('Are you sure you want to delete this announcement?')">
                                    Delete
                                </button>
                            </form>
                        </div>
                        @endcan
                    </div>
                    
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed">{{ $announcement->message }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $announcements->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-8 text-center">
            <div class="text-gray-400 mb-4">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.648 9.168-4z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No announcements yet</h3>
            <p class="text-gray-500">Course announcements will appear here when they are posted.</p>
        </div>
    @endif
</div>
@endsection