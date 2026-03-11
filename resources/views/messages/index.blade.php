@extends('layouts.app')

@section('title', 'Messages')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Messages</h1>
            <p class="text-gray-600 mt-1">Communicate with teachers, students, and administrators</p>
        </div>
        
        <a href="{{ route('messages.create') }}" 
           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium">
            New Message
        </a>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200 mb-6">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('messages.index', ['tab' => 'inbox']) }}" 
               class="py-2 px-1 border-b-2 font-medium text-sm {{ $tab === 'inbox' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Inbox
                @if($tab === 'inbox' && $unreadCount > 0)
                    <span class="ml-2 bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ $unreadCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('messages.index', ['tab' => 'sent']) }}" 
               class="py-2 px-1 border-b-2 font-medium text-sm {{ $tab === 'sent' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Sent
            </a>
        </nav>
    </div>

    @if($messages->count() > 0)
        <div class="bg-white rounded-lg shadow-md border border-gray-200">
            @foreach($messages as $message)
                @php /** @var \App\Models\Message $message */ @endphp
                <div class="border-b border-gray-200 last:border-b-0">
                    <a href="{{ route('messages.show', $message) }}" 
                       class="block p-4 hover:bg-gray-50 transition-colors duration-150">
                        <div class="flex items-center justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-3 mb-2">
                                    @if($tab === 'inbox' && !$message->isRead())
                                        <div class="w-2 h-2 bg-blue-600 rounded-full"></div>
                                    @endif
                                    
                                    <h3 class="text-sm font-medium text-gray-900 truncate {{ $tab === 'inbox' && !$message->isRead() ? 'font-semibold' : '' }}">
                                        {{ $message->subject }}
                                    </h3>
                                    
                                    @if($message->isReply())
                                        <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2 py-0.5 rounded">
                                            Reply
                                        </span>
                                    @endif
                                </div>
                                
                                <div class="flex items-center gap-4 text-sm text-gray-500">
                                    <span>
                                        {{ $tab === 'inbox' ? 'From: ' . $message->sender->name : 'To: ' . $message->recipient->name }}
                                    </span>
                                    <span>{{ $message->sent_at->format('M j, Y g:i A') }}</span>
                                </div>
                                
                                <p class="mt-1 text-sm text-gray-600 truncate">
                                    {{ \Illuminate\Support\Str::limit($message->message, 100) }}
                                </p>
                            </div>
                            
                            <div class="ml-4 flex-shrink-0">
                                <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $messages->appends(['tab' => $tab])->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md border border-gray-200 p-8 text-center">
            <div class="text-gray-400 mb-4">
                <svg class="mx-auto h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">
                {{ $tab === 'inbox' ? 'No messages received' : 'No messages sent' }}
            </h3>
            <p class="text-gray-500 mb-4">
                {{ $tab === 'inbox' ? 'Messages from other users will appear here.' : 'Messages you send will appear here.' }}
            </p>
            @if($tab === 'inbox')
                <a href="{{ route('messages.create') }}" 
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                    Send your first message
                </a>
            @endif
        </div>
    @endif
</div>
@endsection