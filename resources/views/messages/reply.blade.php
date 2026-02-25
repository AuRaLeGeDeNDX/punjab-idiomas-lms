@extends('layouts.app')

@section('title', 'Reply to: ' . $message->subject)

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <!-- Original Message -->
            <div class="card mb-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Original Message</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <strong>Subject:</strong> {{ $message->subject }}
                    </div>
                    <div class="mb-2">
                        <strong>From:</strong> {{ $message->sender->name }}
                    </div>
                    <div class="mb-3">
                        <strong>Sent:</strong> {{ $message->sent_at->format('M j, Y \a\t g:i A') }}
                    </div>
                    <div class="border-start border-3 border-secondary ps-3">
                        {!! nl2br(e($message->message)) !!}
                    </div>
                </div>
            </div>

            <!-- Reply Form -->
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Reply to Message</h4>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('messages.reply.store', $message) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">To</label>
                            <input type="text" class="form-control" value="{{ $message->sender->name }}" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Subject</label>
                            <input type="text" class="form-control" value="Re: {{ $message->subject }}" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="message" class="form-label">Your Reply <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('message') is-invalid @enderror" 
                                      id="message" name="message" rows="8" required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('messages.show', $message) }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Message
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Send Reply
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection