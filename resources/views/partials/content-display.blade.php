@switch($content->type)
    @case('text')
        <div class="content-text editorjs-content">
            @if($content->isEditorJsContent())
                {!! $content->getRenderedContent() !!}
            @else
                {{-- Legacy HTML content --}}
                {!! $content->getRenderedContent() !!}
            @endif
        </div>
        @break

    @case('image')
        <div class="content-image text-center">
            @if($content->file_path || $content->external_url)
                <div class="image-container">
                    <img 
                        src="{{ $content->getDisplayContent() }}" 
                        alt="{{ $content->alt_text ?? $content->title ?? 'Content image' }}" 
                        class="content-image-display"
                        style="max-height: 120px !important; max-width: 100% !important; height: auto !important; width: auto !important; object-fit: contain;"
                        loading="lazy"
                        decoding="async"
                        oncontextmenu="return false;"
                        ondragstart="return false;"
                        onerror="this.style.display='none'; this.nextElementSibling.style.display='block';"
                    >
                    <div style="display: none; padding: 2rem; background: #f8f9fa; border-radius: 12px; border: 2px dashed #dee2e6;">
                        <i class="fas fa-image fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Image could not be loaded</p>
                    </div>
                </div>
                @if($content->alt_text)
                    <p class="text-muted small mt-3" style="font-size: 0.875rem; line-height: 1.5; color: #6c757d;">
                        <i class="fas fa-image me-1" style="color: #667eea;"></i>{{ $content->alt_text }}
                    </p>
                @endif
            @else
                <div class="alert alert-warning" style="border-radius: 12px; border-left: 4px solid #ffc107;">
                    <i class="fas fa-exclamation-triangle me-2"></i>No image available
                </div>
            @endif
        </div>
        @break

    @case('pdf')
        <div class="content-pdf">
            @if($content->file_path || $content->external_url)
                @php
                    // Use secure PDF viewer with anti-download protections
                    $viewerUrl = $content->getSecurePdfViewerUrl();
                @endphp
                @if($viewerUrl)
                    {{-- Secure PDF Viewer Button - Minimal & Elegant --}}
                    <div class="pdf-viewer-card" style="border: 1px solid #e0e0e0; border-radius: 6px; padding: 16px 20px; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="me-3">
                                    <i class="fas fa-file-pdf" style="font-size: 28px; color: #dc3545;"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1" style="font-size: 14px; font-weight: 600; color: #333;">{{ $content->title ?? 'PDF Document' }}</h6>
                                    @if($content->file_name)
                                        <p class="mb-0 text-muted" style="font-size: 12px;">
                                            {{ $content->file_name }}
                                            @if($content->formatted_file_size)
                                                <span class="ms-2">{{ $content->formatted_file_size }}</span>
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            </div>
                            <div class="ms-3">
                                <a href="{{ $viewerUrl }}" target="_blank" class="btn btn-sm btn-danger" style="padding: 6px 16px; font-size: 13px; white-space: nowrap;">
                                    <i class="fas fa-eye me-1"></i> View PDF
                                </a>
                            </div>
                        </div>
                        <div class="mt-2 pt-2" style="border-top: 1px solid #f0f0f0;">
                            <small class="text-muted" style="font-size: 11px;">
                                <i class="fas fa-lock me-1"></i> Protected viewing • No downloads • Watermarked
                            </small>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> PDF file not available
                    </div>
                @endif
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> PDF file not available
                </div>
            @endif
        </div>
        @break

    @case('audio')
        <div class="content-audio">
            @if($content->file_path || $content->external_url)
                <audio 
                    id="audio-{{ $content->id }}" 
                    controls 
                    controlsList="nodownload"
                    class="w-100"
                    oncontextmenu="return false;"
                >
                    <source src="{{ $content->getDisplayContent() }}" type="{{ $content->mime_type }}">
                    Your browser does not support the audio element.
                </audio>
                @if($content->file_name)
                    <p class="text-muted small mt-2">
                        <i class="fas fa-volume-up"></i> {{ $content->file_name }}
                    </p>
                @endif
                <script>
                    // Additional security for audio-{{ $content->id }}
                    document.addEventListener('DOMContentLoaded', function() {
                        const audio = document.getElementById('audio-{{ $content->id }}');
                        if (audio) {
                            // Prevent right-click context menu
                            audio.addEventListener('contextmenu', function(e) {
                                e.preventDefault();
                                return false;
                            });
                            
                            // Prevent keyboard shortcuts for download
                            audio.addEventListener('keydown', function(e) {
                                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                                    e.preventDefault();
                                    return false;
                                }
                            });
                        }
                    });
                </script>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Audio file not available
                </div>
            @endif
        </div>
        @break

    @case('video')
        <div class="content-video">
            @if($content->file_path || $content->external_url)
                <video 
                    id="video-{{ $content->id }}" 
                    controls 
                    controlsList="nodownload"
                    class="w-100" 
                    style="max-height: 400px;"
                    oncontextmenu="return false;"
                >
                    <source src="{{ $content->getDisplayContent() }}" type="{{ $content->mime_type }}">
                    Your browser does not support the video element.
                </video>
                @if($content->file_name)
                    <p class="text-muted small mt-2">
                        <i class="fas fa-video"></i> {{ $content->file_name }}
                    </p>
                @endif
                <script>
                    // Additional security for video-{{ $content->id }}
                    document.addEventListener('DOMContentLoaded', function() {
                        const video = document.getElementById('video-{{ $content->id }}');
                        if (video) {
                            // Prevent right-click context menu
                            video.addEventListener('contextmenu', function(e) {
                                e.preventDefault();
                                return false;
                            });
                            
                            // Prevent keyboard shortcuts for download
                            video.addEventListener('keydown', function(e) {
                                // Prevent Ctrl+S (Save)
                                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                                    e.preventDefault();
                                    return false;
                                }
                            });
                        }
                    });
                </script>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Video file not available
                </div>
            @endif
        </div>
        @break

    @default
        <div class="content-unknown">
            @if($content->content)
                <div class="alert alert-info">
                    {{ $content->content }}
                </div>
            @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> Unknown content type: {{ $content->type }}
                </div>
            @endif
@endswitch

@if($content->description)
    <div class="content-description mt-3">
        <small class="text-muted">{{ $content->description }}</small>
    </div>
@endif

@if($content->type === 'image')
<script>
    // Image quality optimization
    document.addEventListener('DOMContentLoaded', function() {
        const images = document.querySelectorAll('.content-image-display');
        
        images.forEach(function(img) {
            // Ensure high-quality rendering
            img.style.imageRendering = '-webkit-optimize-contrast';
            img.style.imageRendering = 'auto';
            
            // Add loaded class when image finishes loading
            if (img.complete) {
                img.classList.add('loaded');
            } else {
                img.addEventListener('load', function() {
                    img.classList.add('loaded');
                });
            }
            
            // Handle image load errors gracefully
            img.addEventListener('error', function() {
                console.warn('Failed to load image:', img.src);
            });
        });
    });
</script>
@endif