{{-- Server-side rendered content block --}}
<div class="content-block" data-id="{{ $block->id }}" data-section="{{ $block->section }}">
    <div class="content-block-header">
        <div class="d-flex align-items-center flex-grow-1">
            <i class="fas fa-grip-vertical content-block-drag-handle"></i>
            @php
                $typeConfig = $contentTypes[$block->type] ?? [];
            @endphp
            <i class="{{ $typeConfig['icon'] ?? 'fas fa-file' }} me-2"></i>
            <div>
                <h6 class="mb-0">{{ $block->title ?: 'Untitled Block' }}</h6>
                <small class="text-muted">{{ $typeConfig['label'] ?? $block->type }}</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @php
                $visibilityClass = match($block->visibility->value) {
                    'student' => 'bg-success',
                    'teacher_only' => 'bg-warning',
                    default => 'bg-secondary'
                };
                $visibilityLabel = match($block->visibility->value) {
                    'student' => 'Students',
                    'teacher_only' => 'Teachers Only',
                    default => ucfirst($block->visibility->value)
                };
            @endphp
            <span class="badge {{ $visibilityClass }} visibility-badge">{{ $visibilityLabel }}</span>
            @if(!$block->is_active)
                <span class="badge bg-secondary visibility-badge">Draft</span>
            @endif
            <div class="content-block-actions">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="editContentBlock({{ $block->id }})" title="Edit">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-info" onclick="showVersionHistory({{ $block->id }})" title="Version History">
                    <i class="fas fa-history"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="duplicateContentBlock({{ $block->id }})" title="Duplicate">
                    <i class="fas fa-copy"></i>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="deleteContentBlock({{ $block->id }}, '{{ addslashes($block->title ?: 'Untitled Block') }}')" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="content-block-body">
        @switch($block->type)
            @case('text')
                <div class="text-preview">
                    @php
                        $renderedContent = $block->getRenderedContent();
                        $truncated = strlen($renderedContent) > 200 ? substr($renderedContent, 0, 200) . '...' : $renderedContent;
                    @endphp
                    {!! $truncated !!}
                </div>
                @break
            @case('image')
                @if($block->file_path)
                    @php
                        $signedUrl = $block->getSignedUrl();
                    @endphp
                    @if($signedUrl)
                        <img src="{{ $signedUrl }}" alt="{{ $block->alt_text }}" style="max-width: 200px; max-height: 100px; object-fit: cover;">
                    @else
                        <div class="alert alert-warning" style="max-width: 200px; max-height: 100px; display: flex; align-items: center; justify-content: center; font-size: 12px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="ms-1">Image not available</span>
                        </div>
                    @endif
                @elseif($block->external_url)
                    <img src="{{ $block->external_url }}" alt="{{ $block->alt_text }}" style="max-width: 200px; max-height: 100px; object-fit: cover;">
                @else
                    <div class="text-muted">No image uploaded</div>
                @endif
                @break
            @case('pdf')
                @if($block->file_path)
                    @php
                        // Use secure PDF viewer for consistent experience
                        $viewerUrl = $block->getSecurePdfViewerUrl();
                    @endphp
                    @if($viewerUrl)
                        <div class="pdf-preview-card" style="border: 1px solid #dc3545; border-radius: 4px; padding: 15px; background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-file-pdf text-danger me-2" style="font-size: 32px;"></i>
                                    <div>
                                        <div class="fw-bold">{{ $block->file_name ?: 'PDF Document' }}</div>
                                        @if($block->formatted_file_size)
                                            <small class="text-muted">{{ $block->formatted_file_size }}</small>
                                        @endif
                                    </div>
                                </div>
                                <a href="{{ $viewerUrl }}" target="_blank" class="btn btn-sm btn-danger">
                                    <i class="fas fa-eye me-1"></i> Preview
                                </a>
                            </div>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <i class="fas fa-lock me-1"></i> Secure viewer with watermark
                                </small>
                            </div>
                        </div>
                    @else
                        <div class="alert alert-warning mb-0" style="font-size: 12px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="ms-1">PDF not available</span>
                        </div>
                    @endif
                @else
                    <div class="d-flex align-items-center text-muted">
                        <i class="fas fa-file-pdf text-danger me-2"></i> 
                        No PDF uploaded
                    </div>
                @endif
                @break
            @case('audio')
                @if($block->file_path || $block->external_url)
                    @php
                        // Use external URL if available, otherwise use secure audio URL
                        $audioUrl = $block->external_url ?: $block->getSecureAudioUrl(60);
                    @endphp
                    @if($audioUrl)
                        <div class="audio-preview-container">
                            <audio 
                                controls 
                                controlsList="nodownload"
                                style="width: 100%; max-width: 300px; height: 40px;"
                            >
                                <source src="{{ $audioUrl }}" type="{{ $block->mime_type ?? 'audio/mpeg' }}">
                                Your browser does not support the audio element.
                            </audio>
                        </div>
                        <div class="mt-1 small text-muted">
                            <i class="fas fa-volume-up text-info me-1"></i>
                            {{ $block->file_name ?: 'Audio File' }}
                        </div>
                    @else
                        <div class="alert alert-warning mb-0" style="font-size: 12px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="ms-1">Audio not available</span>
                        </div>
                    @endif
                @else
                    <div class="d-flex align-items-center text-muted">
                        <i class="fas fa-volume-up text-info me-2"></i> 
                        No audio uploaded
                    </div>
                @endif
                @break
            @case('video')
                @if($block->file_path || $block->external_url)
                    @php
                        // Use external URL if available, otherwise use secure video URL
                        $videoUrl = $block->external_url ?: $block->getSecureVideoUrl(60);
                    @endphp
                    @if($videoUrl)
                        <div class="video-preview-container" style="border: 1px solid #ddd; border-radius: 4px; overflow: hidden; background: #000;">
                            <video 
                                controls 
                                controlsList="nodownload"
                                style="width: 100%; max-height: 150px;"
                            >
                                <source src="{{ $videoUrl }}" type="{{ $block->mime_type ?? 'video/mp4' }}">
                                Your browser does not support the video element.
                            </video>
                        </div>
                        <div class="mt-1 small text-muted">
                            <i class="fas fa-video text-primary me-1"></i>
                            {{ $block->file_name ?: 'Video File' }}
                        </div>
                    @else
                        <div class="alert alert-warning mb-0" style="font-size: 12px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span class="ms-1">Video not available</span>
                        </div>
                    @endif
                @else
                    <div class="d-flex align-items-center text-muted">
                        <i class="fas fa-video text-primary me-2"></i> 
                        No video uploaded
                    </div>
                @endif
                @break
            @default
                <div class="text-muted">Content block</div>
        @endswitch
    </div>
</div>