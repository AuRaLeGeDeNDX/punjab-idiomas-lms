<?php

namespace App\Models;

use App\Enums\ContentSection;
use App\Enums\ContentVisibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Content extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'subpage_id',
        'title',
        'description',
        'type',
        'content',
        'file_path',
        'file_name',
        'original_filename',
        'file_size',
        'mime_type',
        'file_hash',
        'storage_disk',
        'metadata',
        'external_url',
        'alt_text',
        'settings',
        'section',
        'section_order',
        'visibility',
        'order_index',
        'is_active',
        'published_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'order_index' => 'integer',
        'section_order' => 'integer',
        'file_size' => 'integer',
        'metadata' => 'array',
        'settings' => 'array',
        'published_at' => 'datetime',
        'section' => ContentSection::class,
        'visibility' => ContentVisibility::class,
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'secure_url',
        'formatted_file_size',
        'secure_viewer_url',
    ];

    /**
     * Get the content block versions.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(ContentBlockVersion::class, 'content_block_id');
    }

    /**
     * Get the subpage that owns the content.
     */
    public function subpage(): BelongsTo
    {
        return $this->belongsTo(Subpage::class);
    }

    /**
     * Get the user who created this content.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this content.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope to get only active content.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order content by order_index.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_index');
    }

    /**
     * Scope to get content visible to students.
     */
    public function scopeVisibleToStudents($query)
    {
        return $query->where('visibility', ContentVisibility::STUDENT);
    }

    /**
     * Scope to get content visible to teachers.
     */
    public function scopeVisibleToTeachers($query)
    {
        return $query->whereIn('visibility', [ContentVisibility::STUDENT, ContentVisibility::TEACHER_ONLY]);
    }

    /**
     * Scope to get content in a specific section.
     */
    public function scopeInSection($query, ContentSection|string $section)
    {
        if ($section instanceof ContentSection) {
            $section = $section->value;
        }
        return $query->where('section', $section);
    }

    /**
     * Scope to order content by section and section order.
     */
    public function scopeOrderedBySection($query)
    {
        return $query->orderByRaw("
            CASE section 
                WHEN 'introduction' THEN 1
                WHEN 'main_content' THEN 2
                WHEN 'practice' THEN 3
                WHEN 'resources' THEN 4
                ELSE 5
            END
        ")->orderBy('section_order')->orderBy('order_index');
    }

    /**
     * Check if user can access this content.
     */
    public function canBeAccessedBy(User $user): bool
    {
        // Check subpage access first
        if (!$this->subpage->canBeAccessedBy($user)) {
            return false;
        }

        // Students can only see student-visible content
        if ($user->hasRole('Student')) {
            return $this->visibility === ContentVisibility::STUDENT && $this->is_active;
        }

        // Teachers and admins can see all content
        return true;
    }

    /**
     * Get signed URL for file access with comprehensive file existence validation.
     * 
     * This enhanced method includes:
     * - File existence validation before URL generation
     * - Fallback logic to search alternative storage locations
     * - Comprehensive error logging with diagnostic information
     * - Integration with FileStorageDiagnosticService
     * 
     * Requirements: 3.3, 3.4
     */
    public function getSignedUrl(): ?string
    {
        if (!$this->file_path) {
            \Log::debug('Content getSignedUrl: No file path recorded', [
                'content_id' => $this->id,
                'content_type' => $this->type,
            ]);
            return null;
        }

        // Generate correlation ID for tracking this operation
        $correlationId = \Illuminate\Support\Str::uuid()->toString();
        
        \Log::debug('Content getSignedUrl: Starting URL generation with file validation', [
            'correlation_id' => $correlationId,
            'content_id' => $this->id,
            'recorded_file_path' => $this->file_path,
            'recorded_storage_disk' => $this->storage_disk,
            'content_type' => $this->type,
        ]);

        try {
            // Use the new findActualFileLocation() method to find actual file location
            $actualLocation = $this->findActualFileLocation();
            
            if (!$actualLocation) {
                // File not found anywhere - log comprehensive error information
                $this->logFileNotFoundError($correlationId);
                return null;
            }
            
            // Check if file location differs from recorded location
            $recordedDisk = $this->storage_disk ?? 'public';
            if ($actualLocation->getDisk() !== $recordedDisk) {
                \Log::warning('Content getSignedUrl: File found on different disk than recorded', [
                    'correlation_id' => $correlationId,
                    'content_id' => $this->id,
                    'recorded_disk' => $recordedDisk,
                    'actual_disk' => $actualLocation->getDisk(),
                    'file_path' => $this->file_path,
                    'recommendation' => 'Update storage_disk field in database to match actual location',
                ]);
            }
            
            // Generate URL based on actual file location
            $url = $this->generateUrlForActualLocation($actualLocation, $correlationId);
            
            if ($url) {
                \Log::debug('Content getSignedUrl: URL generated successfully', [
                    'correlation_id' => $correlationId,
                    'content_id' => $this->id,
                    'actual_disk' => $actualLocation->getDisk(),
                    'url_type' => $this->determineUrlType($url),
                ]);
            }
            
            return $url;
            
        } catch (\Exception $e) {
            // Log comprehensive error information
            \Log::error('Content getSignedUrl: URL generation failed with exception', [
                'correlation_id' => $correlationId,
                'content_id' => $this->id,
                'recorded_file_path' => $this->file_path,
                'recorded_storage_disk' => $this->storage_disk,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'recommendation' => 'Check file storage configuration and file existence',
            ]);
            
            return null;
        }
    }

    /**
     * Get the secure URL for the content file.
     * Appended via $appends array.
     */
    public function getSecureUrlAttribute(): ?string
    {
        // Don't generate URLs for text content or if no file path
        if (!$this->supportsFiles() || !$this->file_path) {
            return null;
        }

        // Return signed secure URL
        return $this->getSignedUrl();
    }

    /**
     * Get the secure PDF viewer URL for the content.
     * Appended via $appends array. Returns null for non-PDF content.
     */
    public function getSecureViewerUrlAttribute(): ?string
    {
        return $this->getSecurePdfViewerUrl();
    }

    /**
     * Check if content is file-based.
     */
    public function isFile(): bool
    {
        return in_array($this->type, ['pdf', 'image', 'audio', 'video', 'file']);
    }

    /**
     * Get human-readable file size.
     */
    public function getFormattedFileSizeAttribute(): ?string
    {
        if (!$this->file_size) {
            return null;
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->file_size;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    /**
     * Get the next section order index for this subpage and section.
     */
    public static function getNextSectionOrderIndex($subpageId, ContentSection|string $section): int
    {
        if ($section instanceof ContentSection) {
            $section = $section->value;
        }
        return static::where('subpage_id', $subpageId)
                    ->where('section', $section)
                    ->max('section_order') + 1;
    }

    /**
     * Get the next order index for this subpage.
     */
    public static function getNextOrderIndex($subpageId): int
    {
        return static::where('subpage_id', $subpageId)->max('order_index') + 1;
    }

    /**
     * Get content types with their configurations.
     */
    public static function getContentTypes(): array
    {
        return [
            'text' => [
                'label' => 'Text Block',
                'icon' => 'fas fa-paragraph',
                'description' => 'Rich text content with headings, paragraphs, and lists',
                'supports_files' => false,
                'supports_external_url' => false,
            ],
            'image' => [
                'label' => 'Image Block',
                'icon' => 'fas fa-image',
                'description' => 'Upload and display images',
                'supports_files' => true,
                'supports_external_url' => true,
                'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'max_file_size' => 10 * 1024 * 1024, // 10MB
            ],
            'pdf' => [
                'label' => 'PDF Block',
                'icon' => 'fas fa-file-pdf',
                'description' => 'Upload PDF documents',
                'supports_files' => true,
                'supports_external_url' => false,
                'allowed_extensions' => ['pdf'],
                'max_file_size' => 50 * 1024 * 1024, // 50MB
            ],
            'audio' => [
                'label' => 'Audio Block',
                'icon' => 'fas fa-volume-up',
                'description' => 'Upload audio files and voice recordings',
                'supports_files' => true,
                'supports_external_url' => true,
                'allowed_extensions' => ['mp3', 'wav', 'ogg', 'm4a'],
                'max_file_size' => 100 * 1024 * 1024, // 100MB
            ],
            'video' => [
                'label' => 'Video Block',
                'icon' => 'fas fa-video',
                'description' => 'Upload videos or embed from external sources',
                'supports_files' => true,
                'supports_external_url' => true,
                'allowed_extensions' => ['mp4', 'webm', 'ogg', 'mov'],
                'max_file_size' => 500 * 1024 * 1024, // 500MB
            ],
            'youtube' => [
                'label' => 'YouTube',
                'icon' => 'fab fa-youtube',
                'description' => 'Embed YouTube video',
                'supports_files' => false,
                'supports_external_url' => true,
            ],
            'spacer' => [
                'label' => 'Spacer',
                'icon' => 'fas fa-arrows-alt-v',
                'description' => 'Vertical whitespace',
                'supports_files' => false,
            ],
            'divider' => [
                'label' => 'Divider',
                'icon' => 'fas fa-minus',
                'description' => 'Horizontal separator',
                'supports_files' => false,
            ],
        ];
    }

    /**
     * Get configuration for a specific content type.
     */
    public static function getContentTypeConfig(string $type): ?array
    {
        return static::getContentTypes()[$type] ?? null;
    }

    /**
     * Check if content type supports files.
     */
    public function supportsFiles(): bool
    {
        $config = static::getContentTypeConfig($this->type);
        return $config['supports_files'] ?? false;
    }

    /**
     * Check if content type supports external URLs.
     */
    public function supportsExternalUrl(): bool
    {
        $config = static::getContentTypeConfig($this->type);
        return $config['supports_external_url'] ?? false;
    }

    /**
     * Get allowed file extensions for this content type.
     */
    public function getAllowedExtensions(): array
    {
        $config = static::getContentTypeConfig($this->type);
        return $config['allowed_extensions'] ?? [];
    }

    /**
     * Get max file size for this content type.
     */
    public function getMaxFileSize(): int
    {
        $config = static::getContentTypeConfig($this->type);
        return $config['max_file_size'] ?? (10 * 1024 * 1024); // Default 10MB
    }

    /**
     * Check if content is published.
     */
    public function isPublished(): bool
    {
        return $this->is_active && 
               ($this->published_at === null || $this->published_at->isPast());
    }

    /**
     * Scope to get published content.
     */
    public function scopePublished($query)
    {
        return $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('published_at')
                          ->orWhere('published_at', '<=', now());
                    });
    }

    /**
     * Get content for display (with proper formatting).
     */
    public function getDisplayContent(): string
    {
        switch ($this->type) {
            case 'text':
                return $this->content ?? '';
                
            case 'video':
                // Priority: external_url first, then secure video streaming
                if ($this->external_url) {
                    return $this->external_url;
                }
                
                if ($this->file_path) {
                    // Use secure video streaming with signed URLs
                    return $this->getSecureVideoUrl() ?? '';
                }
                
                return '';
                
            case 'audio':
                // Priority: external_url first, then secure audio streaming
                if ($this->external_url) {
                    return $this->external_url;
                }
                
                if ($this->file_path) {
                    // Use secure audio streaming with signed URLs
                    return $this->getSecureAudioUrl() ?? '';
                }
                
                return '';
                
            case 'image':
                // Priority: external_url first, then secure image serving
                if ($this->external_url) {
                    return $this->external_url;
                }
                
                if ($this->file_path) {
                    // Use secure image serving with signed URLs
                    return $this->getSecureImageUrl() ?? '';
                }
                
                return '';
                
            case 'pdf':
                // Priority: external_url first, then secure PDF streaming
                if ($this->external_url) {
                    return $this->external_url;
                }
                
                if ($this->file_path) {
                    // Use secure PDF streaming with signed URLs
                    return $this->getSecurePdfUrl() ?? '';
                }
                
                return '';
                
            default:
                return $this->content ?? '';
        }
    }

    /**
     * Detect if content is Editor.js JSON or legacy HTML
     *
     * @return bool
     */
    public function isEditorJsContent(): bool
    {
        if (empty($this->content)) {
            \Log::debug('Content format detection: empty content', [
                'content_id' => $this->id ?? 'unsaved',
                'content_type' => $this->type,
                'format' => 'empty'
            ]);
            return false;
        }

        // Try to decode as JSON
        $decoded = json_decode($this->content, true);
        
        // Check if it's valid JSON with blocks array (Editor.js format)
        $isEditorJs = is_array($decoded) && isset($decoded['blocks']) && is_array($decoded['blocks']);
        
        // Log content format detection for debugging
        \Log::debug('Content format detection', [
            'content_id' => $this->id ?? 'unsaved',
            'content_type' => $this->type,
            'format' => $isEditorJs ? 'editorjs' : 'html',
            'has_blocks' => isset($decoded['blocks']),
            'is_valid_json' => is_array($decoded)
        ]);
        
        return $isEditorJs;
    }

    /**
     * Get content for editing (always returns array for Editor.js)
     *
     * @return array
     */
    public function getEditableContent(): array
    {
        if ($this->isEditorJsContent()) {
            \Log::debug('Loading Editor.js content for editing', [
                'content_id' => $this->id ?? 'unsaved',
                'format' => 'editorjs'
            ]);
            return json_decode($this->content, true);
        }

        // Convert legacy HTML to Editor.js format
        \Log::info('Converting legacy HTML to Editor.js format', [
            'content_id' => $this->id ?? 'unsaved',
            'content_type' => $this->type,
            'html_length' => strlen($this->content ?? '')
        ]);
        
        return $this->convertHtmlToEditorJs($this->content ?? '');
    }

    /**
     * Get content for display (rendered HTML)
     *
     * @return string
     */
    public function getRenderedContent(): string
    {
        if ($this->isEditorJsContent()) {
            \Log::debug('Rendering Editor.js content to HTML', [
                'content_id' => $this->id ?? 'unsaved',
                'format' => 'editorjs'
            ]);
            
            $renderer = new \App\Services\EditorJsRenderer();
            $data = json_decode($this->content, true);
            return $renderer->render($data);
        }

        // Return sanitized legacy HTML
        \Log::debug('Rendering legacy HTML content', [
            'content_id' => $this->id ?? 'unsaved',
            'format' => 'html',
            'html_length' => strlen($this->content ?? '')
        ]);
        
        return $this->sanitizeHtml($this->content ?? '');
    }

    /**
     * Convert legacy HTML to Editor.js format
     *
     * @param string $html
     * @return array
     */
    private function convertHtmlToEditorJs(string $html): array
    {
        if (empty($html)) {
            \Log::debug('HTML to Editor.js conversion: empty HTML', [
                'content_id' => $this->id ?? 'unsaved'
            ]);
            return [
                'time' => time() * 1000,
                'blocks' => [],
                'version' => '2.28.0'
            ];
        }

        $blocks = [];
        
        // Parse HTML into a DOM document
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true); // Suppress HTML parsing warnings
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        
        $xpath = new \DOMXPath($dom);
        $body = $xpath->query('//body')->item(0);
        
        if ($body) {
            foreach ($body->childNodes as $node) {
                $block = $this->convertNodeToBlock($node);
                if ($block) {
                    $blocks[] = $block;
                }
            }
        }
        
        // If no blocks were created, create a single paragraph with the HTML
        if (empty($blocks)) {
            \Log::debug('HTML to Editor.js conversion: no blocks parsed, creating single paragraph', [
                'content_id' => $this->id ?? 'unsaved',
                'html_length' => strlen($html)
            ]);
            $blocks[] = [
                'id' => uniqid(),
                'type' => 'paragraph',
                'data' => [
                    'text' => strip_tags($html)
                ]
            ];
        }

        \Log::info('HTML to Editor.js conversion completed', [
            'content_id' => $this->id ?? 'unsaved',
            'blocks_created' => count($blocks),
            'block_types' => array_count_values(array_column($blocks, 'type'))
        ]);

        return [
            'time' => time() * 1000,
            'blocks' => $blocks,
            'version' => '2.28.0'
        ];
    }

    /**
     * Convert a DOM node to an Editor.js block
     *
     * @param \DOMNode $node
     * @return array|null
     */
    private function convertNodeToBlock(\DOMNode $node): ?array
    {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            // Handle text nodes
            if ($node->nodeType === XML_TEXT_NODE) {
                $text = trim($node->textContent);
                if (!empty($text)) {
                    return [
                        'id' => uniqid(),
                        'type' => 'paragraph',
                        'data' => [
                            'text' => htmlspecialchars($text, ENT_QUOTES, 'UTF-8')
                        ]
                    ];
                }
            }
            return null;
        }

        $tagName = strtolower($node->nodeName);
        $innerHTML = $this->getInnerHTML($node);

        switch ($tagName) {
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                $level = (int) substr($tagName, 1);
                // Preserve inline formatting in headers
                $text = $this->preserveInlineFormatting($innerHTML);
                return [
                    'id' => uniqid(),
                    'type' => 'header',
                    'data' => [
                        'text' => $text,
                        'level' => $level
                    ]
                ];

            case 'p':
                // Preserve inline formatting in paragraphs
                $text = $this->preserveInlineFormatting($innerHTML);
                if (!empty(trim(strip_tags($text)))) {
                    return [
                        'id' => uniqid(),
                        'type' => 'paragraph',
                        'data' => [
                            'text' => $text
                        ]
                    ];
                }
                return null;

            case 'ul':
            case 'ol':
                $items = [];
                foreach ($node->childNodes as $child) {
                    if ($child->nodeName === 'li') {
                        $itemHtml = $this->getInnerHTML($child);
                        // Preserve inline formatting in list items
                        $items[] = $this->preserveInlineFormatting($itemHtml);
                    }
                }
                if (!empty($items)) {
                    return [
                        'id' => uniqid(),
                        'type' => 'list',
                        'data' => [
                            'style' => $tagName === 'ol' ? 'ordered' : 'unordered',
                            'items' => $items
                        ]
                    ];
                }
                return null;

            case 'blockquote':
                // Preserve inline formatting in blockquotes
                $text = $this->preserveInlineFormatting($innerHTML);
                return [
                    'id' => uniqid(),
                    'type' => 'quote',
                    'data' => [
                        'text' => $text,
                        'caption' => ''
                    ]
                ];

            case 'pre':
                // For pre tags, get the code content without formatting
                $code = strip_tags($innerHTML);
                return [
                    'id' => uniqid(),
                    'type' => 'code',
                    'data' => [
                        'code' => $code
                    ]
                ];

            case 'code':
                // Standalone code tags (not inside pre)
                $code = strip_tags($innerHTML);
                return [
                    'id' => uniqid(),
                    'type' => 'code',
                    'data' => [
                        'code' => $code
                    ]
                ];

            default:
                // For other elements, treat as paragraph with inline formatting
                $text = $this->preserveInlineFormatting($innerHTML);
                if (!empty(trim(strip_tags($text)))) {
                    return [
                        'id' => uniqid(),
                        'type' => 'paragraph',
                        'data' => [
                            'text' => $text
                        ]
                    ];
                }
                return null;
        }
    }

    /**
     * Preserve inline formatting tags (b, i, strong, em, u, a, code, mark)
     * while stripping other tags
     *
     * @param string $html
     * @return string
     */
    private function preserveInlineFormatting(string $html): string
    {
        // Allow safe inline formatting tags
        $allowedTags = '<b><i><u><strong><em><a><code><mark>';
        
        // Strip all tags except allowed ones
        $preserved = strip_tags($html, $allowedTags);
        
        // Sanitize anchor tags to prevent XSS
        $preserved = preg_replace_callback(
            '/<a\s+([^>]*?)href=["\']([^"\']*)["\']([^>]*)>/i',
            function($matches) {
                $href = $matches[2];
                
                // Block javascript:, data:, and vbscript: URLs
                if (preg_match('/^\s*(javascript|data|vbscript):/i', $href)) {
                    return '<a ' . $matches[1] . 'href="#"' . $matches[3] . '>';
                }
                
                return $matches[0];
            },
            $preserved
        );
        
        return $preserved;
    }

    /**
     * Get inner HTML of a DOM node
     *
     * @param \DOMNode $node
     * @return string
     */
    private function getInnerHTML(\DOMNode $node): string
    {
        $innerHTML = '';
        foreach ($node->childNodes as $child) {
            $innerHTML .= $node->ownerDocument->saveHTML($child);
        }
        return $innerHTML;
    }

    /**
     * Sanitize HTML for safe display
     *
     * @param string $html
     * @return string
     */
    private function sanitizeHtml(string $html): string
    {
        // Allow safe HTML tags
        $allowedTags = '<p><br><strong><em><b><i><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6><blockquote><pre><code>';
        
        // Strip all tags except allowed ones
        $sanitized = strip_tags($html, $allowedTags);
        
        // Remove javascript: and data: URLs from links
        $sanitized = preg_replace_callback(
            '/<a\s+([^>]*?)href=["\']([^"\']*)["\']([^>]*)>/i',
            function($matches) {
                $href = $matches[2];
                
                // Block javascript:, data:, and vbscript: URLs
                if (preg_match('/^\s*(javascript|data|vbscript):/i', $href)) {
                    return '<a ' . $matches[1] . 'href="#"' . $matches[3] . '>';
                }
                
                return $matches[0];
            },
            $sanitized
        );
        
        return $sanitized;
    }

    /**
     * Generate URL for the actual file location.
     * 
     * @param \App\Services\FileLocation $actualLocation The actual file location
     * @param string $correlationId Correlation ID for tracking
     * @return string|null Generated URL or null if generation fails
     */
    private function generateUrlForActualLocation(\App\Services\FileLocation $actualLocation, string $correlationId): ?string
    {
        $disk = $actualLocation->getDisk();
        $path = $actualLocation->getPath();
        
        try {
            if ($disk === 'protected' || $disk === 'r2') {
            // For protected/R2 files, use the secure file controller route
            try {
                // For video type, use secure video streaming route
                if ($this->type === 'video') {
                    $url = \Illuminate\Support\Facades\URL::temporarySignedRoute(
                        'secure.video.stream',
                        now()->addMinutes(10),
                        ['content' => $this->id]
                    );
                } else {
                    $url = route('secure-files.download-content', ['content' => $this->id]);
                }
                
                \Log::debug('Content generateUrlForActualLocation: Generated secure route URL', [
                    'correlation_id' => $correlationId,
                    'content_id' => $this->id,
                    'disk' => $disk,
                ]);
                
                return $url;
            } catch (\Exception $e) {
                // Fallback if route helper is not available
                $fallbackUrl = url("/secure-files/content/{$this->id}");
                
                \Log::warning('Content generateUrlForActualLocation: Route helper failed, using fallback URL', [
                    'correlation_id' => $correlationId,
                    'content_id' => $this->id,
                    'disk' => $disk,
                    'route_error' => $e->getMessage(),
                    'fallback_url' => $fallbackUrl,
                ]);
                
                return $fallbackUrl;
            }
        } else {
            // For public files, use direct asset access
            $url = asset('storage/' . $path);
            
            \Log::debug('Content generateUrlForActualLocation: Generated asset URL', [
                'correlation_id' => $correlationId,
                'content_id' => $this->id,
                'disk' => $disk,
                'asset_path' => 'storage/' . $path,
            ]);
            
            return $url;
        } 
        } catch (\Exception $e) {
            \Log::error('Content generateUrlForActualLocation: URL generation failed', [
                'correlation_id' => $correlationId,
                'content_id' => $this->id,
                'disk' => $disk,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            
            return null;
        }
    }

    /**
     * Log comprehensive error information when file is not found.
     * 
     * @param string $correlationId Correlation ID for tracking
     */
    private function logFileNotFoundError(string $correlationId): void
    {
        $recordedDisk = $this->storage_disk ?? 'public';
        
        // Gather diagnostic information
        $diagnosticInfo = [
            'file_exists_on_recorded_disk' => false,
            'file_exists_on_public_disk' => false,
            'file_exists_on_protected_disk' => false,
            'recorded_disk_accessible' => false,
            'public_disk_accessible' => false,
            'protected_disk_accessible' => false,
        ];
        
        // Check file existence on different disks
        try {
            $diagnosticInfo['file_exists_on_recorded_disk'] = Storage::disk($recordedDisk)->exists($this->file_path);
            $diagnosticInfo['recorded_disk_accessible'] = true;
        } catch (\Exception $e) {
            \Log::debug('Content logFileNotFoundError: Error checking recorded disk', [
                'correlation_id' => $correlationId,
                'disk' => $recordedDisk,
                'error' => $e->getMessage(),
            ]);
        }
        
        try {
            $diagnosticInfo['file_exists_on_public_disk'] = Storage::disk('public')->exists($this->file_path);
            $diagnosticInfo['public_disk_accessible'] = true;
        } catch (\Exception $e) {
            \Log::debug('Content logFileNotFoundError: Error checking public disk', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
            ]);
        }
        
        try {
            $diagnosticInfo['file_exists_on_protected_disk'] = Storage::disk('protected')->exists($this->file_path);
            $diagnosticInfo['protected_disk_accessible'] = true;
        } catch (\Exception $e) {
            \Log::debug('Content logFileNotFoundError: Error checking protected disk', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
            ]);
        }
        
        // Log comprehensive error with diagnostic information
        \Log::error('Content getSignedUrl: File not found on any storage disk', [
            'correlation_id' => $correlationId,
            'event_type' => 'file_not_found',
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'content_details' => [
                'content_id' => $this->id,
                'content_type' => $this->type,
                'recorded_file_path' => $this->file_path,
                'recorded_storage_disk' => $recordedDisk,
                'recorded_file_size' => $this->file_size,
                'recorded_file_hash' => $this->file_hash ? substr($this->file_hash, 0, 16) . '...' : null,
                'created_at' => $this->created_at?->toISOString(),
                'updated_at' => $this->updated_at?->toISOString(),
            ],
            'diagnostic_info' => $diagnosticInfo,
            'recommendations' => [
                'investigate_file_deletion' => 'Check if file was accidentally deleted or moved',
                'check_storage_configuration' => 'Verify storage disk configuration and accessibility',
                'run_file_audit' => 'Run comprehensive file audit to identify similar issues',
                'consider_file_recovery' => 'Check backups or file recovery options if available',
            ],
            'server_info' => [
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
                'request_uri' => request()->getRequestUri() ?? 'cli',
            ],
        ]);
    }

    /**
     * Determine the type of URL generated.
     * 
     * @param string $url The generated URL
     * @return string URL type for logging
     */
    private function determineUrlType(string $url): string
    {
        if (str_contains($url, '/secure-files/')) {
            return 'secure_route';
        } elseif (str_contains($url, '/storage/')) {
            return 'asset_url';
        } elseif (str_contains($url, 'asset')) {
            return 'asset_url';
        } else {
            return 'unknown';
        }
    }

    /**
     * Find the actual location of the file across multiple storage disks.
     * 
     * This method implements logic to search for files across storage disks,
     * logs storage inconsistencies when found, and returns a FileLocation value object
     * with the actual file location.
     * 
     * Requirements: 2.3, 7.1
     * 
     * @return \App\Services\FileLocation|null The actual file location or null if not found
     */
    public function findActualFileLocation(): ?\App\Services\FileLocation
    {
        if (!$this->file_path) {
            \Log::debug('Content findActualFileLocation: No file path recorded', [
                'content_id' => $this->id,
                'content_type' => $this->type,
            ]);
            return null;
        }

        // Generate correlation ID for tracking this operation
        $correlationId = \Illuminate\Support\Str::uuid()->toString();
        
        \Log::debug('Content findActualFileLocation: Starting file location search', [
            'correlation_id' => $correlationId,
            'content_id' => $this->id,
            'recorded_file_path' => $this->file_path,
            'recorded_storage_disk' => $this->storage_disk,
            'content_type' => $this->type,
        ]);

        // Define storage disks to check in priority order
        // Check recorded disk first, then alternative disks
        $recordedDisk = $this->storage_disk ?? 'public';
        $disksToCheck = [$recordedDisk];
        
        // Add alternative disks
        $allDisks = ['public', 'protected','r2'];
        foreach ($allDisks as $disk) {
            if ($disk !== $recordedDisk) {
                $disksToCheck[] = $disk;
            }
        }
        
        foreach ($disksToCheck as $disk) {
            try {
                if (Storage::disk($disk)->exists($this->file_path)) {
                    $fileLocation = new \App\Services\FileLocation($disk, $this->file_path);
                    
                    // Log inconsistency if file found on different disk than recorded
                    if ($disk !== $recordedDisk) {
                        \Log::warning('Content findActualFileLocation: Storage inconsistency detected', [
                            'correlation_id' => $correlationId,
                            'event_type' => 'storage_inconsistency',
                            'timestamp' => now()->toISOString(),
                            'content_id' => $this->id,
                            'content_type' => $this->type,
                            'recorded_disk' => $recordedDisk,
                            'actual_disk' => $disk,
                            'file_path' => $this->file_path,
                            'recommendation' => 'Update storage_disk field in database to match actual location',
                            'server_info' => [
                                'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
                                'request_uri' => request()->getRequestUri() ?? 'cli',
                            ],
                        ]);
                    } else {
                        \Log::debug('Content findActualFileLocation: File found at recorded location', [
                            'correlation_id' => $correlationId,
                            'content_id' => $this->id,
                            'storage_disk' => $disk,
                            'file_path' => $this->file_path,
                        ]);
                    }
                    
                    \Log::info('Content findActualFileLocation: File location found', [
                        'correlation_id' => $correlationId,
                        'content_id' => $this->id,
                        'found_on_disk' => $disk,
                        'recorded_disk' => $recordedDisk,
                        'is_consistent' => $disk === $recordedDisk,
                    ]);
                    
                    return $fileLocation;
                }
            } catch (\Exception $e) {
                \Log::warning('Content findActualFileLocation: Error checking storage disk', [
                    'correlation_id' => $correlationId,
                    'content_id' => $this->id,
                    'disk' => $disk,
                    'error_message' => $e->getMessage(),
                    'error_trace' => $e->getTraceAsString(),
                ]);
            }
        }
        
        // File not found anywhere - log comprehensive error
        \Log::error('Content findActualFileLocation: File not found on any storage disk', [
            'correlation_id' => $correlationId,
            'event_type' => 'file_not_found',
            'timestamp' => now()->toISOString(),
            'content_id' => $this->id,
            'content_type' => $this->type,
            'recorded_file_path' => $this->file_path,
            'recorded_storage_disk' => $recordedDisk,
            'disks_checked' => $disksToCheck,
            'recommendations' => [
                'investigate_file_deletion' => 'Check if file was accidentally deleted or moved',
                'check_storage_configuration' => 'Verify storage disk configuration and accessibility',
                'run_file_audit' => 'Run comprehensive file audit to identify similar issues',
                'consider_file_recovery' => 'Check backups or file recovery options if available',
            ],
            'server_info' => [
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
                'request_uri' => request()->getRequestUri() ?? 'cli',
            ],
        ]);
        
        return null;
    }

    /**
     * Reorder content blocks within a subpage.
     */
    public static function reorderForSubpage(int $subpageId, array $contentIds): void
    {
        foreach ($contentIds as $index => $contentId) {
            static::where('id', $contentId)
                  ->where('subpage_id', $subpageId)
                  ->update(['order_index' => $index + 1]);
        }
    }

    /**
     * Generate signed URL for secure video streaming.
     * 
     * @param int $expirationMinutes URL expiration time in minutes (default: 10)
     * @return string|null Signed URL or null if not a video
     */
    public function getSecureVideoUrl(int $expirationMinutes = 10): ?string
    {
        if ($this->type !== 'video') {
            return null;
        }

        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'secure.video.stream',
            now()->addMinutes($expirationMinutes),
            ['content' => $this->id]
        );
    }

    /**
     * Generate signed URL for secure PDF streaming (legacy method).
     * 
     * This method generates a signed URL to the PDF data stream endpoint.
     * For the full secure viewer experience with anti-download protections,
     * use getSecurePdfViewerUrl() instead.
     * 
     * Requirements:
     * - Generate absolute URLs for PDF.js compatibility (Requirement 7.3)
     * - Minimum expiration of 5 minutes (300 seconds) (Requirement 7.1)
     * - Include all necessary parameters for validation (Requirement 7.2)
     * - Use correct route name 'secure.pdf.stream' (Requirement 7.3)
     * 
     * @param int $expirationMinutes URL expiration time in minutes (minimum: 5)
     * @return string|null Signed URL or null if not a PDF
     */
    public function getSecurePdfUrl(int $expirationMinutes = 10): ?string
    {
        if ($this->type !== 'pdf') {
            return null;
        }

        // Enforce minimum expiration of 5 minutes (300 seconds) per Requirement 7.1
        $expirationMinutes = max($expirationMinutes, 5);

        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'secure.pdf.stream',                    // Correct route name (Requirement 7.3)
            now()->addMinutes($expirationMinutes),  // Minimum 5 minutes (Requirement 7.1)
            ['content' => $this->id],               // All necessary parameters (Requirement 7.2)
            true                                     // Force absolute URL generation
        );
    }

    /**
     * Generate URL for enhanced PDF.js viewer with anti-download protections.
     * 
     * This method generates a URL to the secure PDF viewer that includes:
     * - PDF.js rendering (no native browser PDF viewer)
     * - Dynamic watermarking
     * - Disabled download/print/copy
     * - Page-level tracking
     * - Short-lived session tokens
     * 
     * @return string|null Viewer URL or null if not a PDF
     */
    public function getSecurePdfViewerUrl(): ?string
    {
        if ($this->type !== 'pdf') {
            return null;
        }

        $pdfService = app(\App\Services\SecurePdfService::class);
        return $pdfService->generateViewerUrl($this, auth()->user());
    }

    /**
     * Generate signed URL for secure audio streaming.
     * 
     * @param int $expirationMinutes URL expiration time in minutes (default: 10)
     * @return string|null Signed URL or null if not audio
     */
    public function getSecureAudioUrl(int $expirationMinutes = 10): ?string
    {
        if ($this->type !== 'audio') {
            return null;
        }

        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'secure.audio.stream',
            now()->addMinutes($expirationMinutes),
            ['content' => $this->id]
        );
    }

    /**
     * Generate signed URL for secure image serving.
     * 
     * @param int $expirationMinutes URL expiration time in minutes (default: 10)
     * @return string|null Signed URL or null if not an image
     */
    public function getSecureImageUrl(int $expirationMinutes = 10): ?string
    {
        if ($this->type !== 'image') {
            return null;
        }

        return \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'secure.image.serve',
            now()->addMinutes($expirationMinutes),
            ['content' => $this->id]
        );
    }

    /**
     * Get secure URL for any media type.
     * 
     * @param int $expirationMinutes URL expiration time in minutes (default: 10)
     * @return string|null Signed URL or null if not a supported media type
     */
    public function getSecureMediaUrl(int $expirationMinutes = 10): ?string
    {
        return match($this->type) {
            'video' => $this->getSecureVideoUrl($expirationMinutes),
            'pdf' => $this->getSecurePdfUrl($expirationMinutes),
            'audio' => $this->getSecureAudioUrl($expirationMinutes),
            'image' => $this->getSecureImageUrl($expirationMinutes),
            default => null,
        };
    }
}
