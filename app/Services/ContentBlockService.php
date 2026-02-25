<?php

namespace App\Services;

use App\Enums\ContentSection;
use App\Enums\ContentVisibility;
use App\Models\Content;
use App\Models\ContentBlockVersion;
use App\Models\Subpage;
use App\Models\User;
use App\Services\SecureFileStorageService;
use App\Services\FileStorageDiagnosticService;
use App\Services\StorageConfigurationValidatorSimple as StorageConfigurationValidator;
use App\Services\RepairTriggerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ContentBlockService
{
    protected SecureFileStorageService $secureFileStorage;
    protected FileStorageDiagnosticService $diagnosticService;
    protected StorageConfigurationValidator $storageValidator;
    protected ?RepairTriggerService $repairTriggerService;

    public function __construct(
        SecureFileStorageService $secureFileStorage,
        FileStorageDiagnosticService $diagnosticService,
        StorageConfigurationValidator $storageValidator,
        ?RepairTriggerService $repairTriggerService = null
    ) {
        $this->secureFileStorage = $secureFileStorage;
        $this->diagnosticService = $diagnosticService;
        $this->storageValidator = $storageValidator;
        $this->repairTriggerService = $repairTriggerService;
    }
    /**
     * Create a new content block with atomic upload operations.
     * 
     * Requirements 4.3: Ensure atomic upload operations
     */
    public function createContentBlock(Subpage $subpage, array $data): Content
    {
        return DB::transaction(function () use ($subpage, $data) {
            $section = $data['section'] ?? ContentSection::MAIN_CONTENT->value;
            
            $contentData = [
                'subpage_id' => $subpage->id,
                'title' => $data['title'] ?? '',
                'description' => $data['description'] ?? null,
                'type' => $data['type'],
                // Fix: Handle empty arrays/objects by setting to null, otherwise JSON encode explicit arrays
                'content' => (isset($data['content']) && (is_array($data['content']) || is_object($data['content']))) 
                    ? (empty($data['content']) ? null : json_encode($data['content'])) 
                    : ($data['content'] ?? null),
                'external_url' => $data['external_url'] ?? null,
                'alt_text' => $data['alt_text'] ?? null,
                'metadata' => $data['metadata'] ?? [],
                'settings' => $data['settings'] ?? [],
                'section' => $section,
                'section_order' => $data['section_order'] ?? Content::getNextSectionOrderIndex($subpage->id, $section),
                'visibility' => $data['visibility'] ?? ContentVisibility::STUDENT->value,
                'order_index' => $data['order_index'] ?? Content::getNextOrderIndex($subpage->id),
                'is_active' => $data['is_active'] ?? true,
                'published_at' => $data['published_at'] ?? now(),
                'created_by' => Auth::id(),
                'updated_by' => Auth::id(),
            ];

            $fileData = null;
            $content = null;

            try {
                // Handle file upload if present
                if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
                    $fileData = $this->handleFileUpload($data['file'], $data['type'], $subpage);
                    $contentData = array_merge($contentData, $fileData);
                }

                // Create content record
                $content = Content::create($contentData);
                
                // POST-CREATION VERIFICATION: Validate database record matches actual file location (Requirement 2.3)
                if ($fileData) {
                    $this->verifyDatabaseRecordConsistency($content, $fileData['correlation_id'] ?? null);
                }
                
                // Create initial version snapshot
                ContentBlockVersion::createSnapshot($content, 'create', Auth::user());
                
                return $content;
                
            } catch (\Exception $e) {
                // ATOMIC ROLLBACK: If content creation fails after file upload, clean up the uploaded file
                if ($fileData && !$content) {
                    \Log::warning('ContentBlockService: Content creation failed after file upload, performing cleanup', [
                        'correlation_id' => $fileData['correlation_id'] ?? 'unknown',
                        'file_path' => $fileData['file_path'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                    
                    // Create minimal upload state for cleanup
                    $uploadState = new \stdClass();
                    $uploadState->filesCreated = [[
                        'disk' => $fileData['storage_disk'],
                        'path' => $fileData['file_path'],
                        'type' => 'main_file',
                        'created_at' => now()->toISOString(),
                    ]];
                    $uploadState->tempFiles = [];
                    $uploadState->storageOperations = [];
                    
                    $this->performAtomicUploadRollback($fileData, $uploadState, $fileData['correlation_id'] ?? Str::uuid()->toString());
                }
                
                // Re-throw the exception to maintain transaction rollback behavior
                throw $e;
            }
        });
    }

    /**
     * Update an existing content block with atomic upload operations.
     * 
     * Requirements 4.3: Ensure atomic upload operations
     */
    public function updateContentBlock(Content $content, array $data): Content
    {
        return DB::transaction(function () use ($content, $data) {
            // Create version snapshot before updating
            ContentBlockVersion::createSnapshot($content, 'update', Auth::user());
            
            $updateData = [
                'title' => $data['title'] ?? $content->title,
                'description' => $data['description'] ?? $content->description,
                'content' => $data['content'] ?? $content->content,
                'external_url' => $data['external_url'] ?? $content->external_url,
                'alt_text' => $data['alt_text'] ?? $content->alt_text,
                'metadata' => $data['metadata'] ?? $content->metadata,
                'settings' => $data['settings'] ?? $content->settings,
                'section' => $data['section'] ?? $content->section,
                'section_order' => $data['section_order'] ?? $content->section_order,
                'visibility' => $data['visibility'] ?? $content->visibility,
                'is_active' => $data['is_active'] ?? $content->is_active,
                'updated_by' => Auth::id(),
            ];

            $oldFileData = null;
            $newFileData = null;

            try {
                // Handle file upload if present
                if (isset($data['file']) && $data['file'] instanceof UploadedFile) {
                    // Store old file data for potential rollback
                    if ($content->file_path) {
                        $oldFileData = [
                            'file_path' => $content->file_path,
                            'storage_disk' => $content->storage_disk,
                            'original_filename' => $content->original_filename,
                            'file_size' => $content->file_size,
                        ];
                    }

                    // Upload new file first (before deleting old file for safety)
                    $newFileData = $this->handleFileUpload($data['file'], $content->type, $content->subpage);
                    $updateData = array_merge($updateData, $newFileData);
                    
                    // Update content record with new file data
                    $content->update($updateData);
                    
                    // POST-UPDATE VERIFICATION: Validate database record matches actual file location
                    $this->verifyDatabaseRecordConsistency($content->fresh(), $newFileData['correlation_id'] ?? null);
                    
                    // Only delete old file after successful verification of new file
                    if ($oldFileData) {
                        $this->deleteOldFileConsistently($content, $newFileData['correlation_id'] ?? Str::uuid()->toString());
                    }
                    
                    return $content->fresh();
                } else {
                    // No file upload, just update other data
                    $content->update($updateData);
                    return $content->fresh();
                }
                
            } catch (\Exception $e) {
                // ATOMIC ROLLBACK: If update fails after new file upload, clean up the new file and preserve old file
                if ($newFileData) {
                    \Log::warning('ContentBlockService: Content update failed after file upload, performing cleanup', [
                        'correlation_id' => $newFileData['correlation_id'] ?? 'unknown',
                        'content_id' => $content->id,
                        'new_file_path' => $newFileData['file_path'] ?? 'unknown',
                        'error' => $e->getMessage(),
                    ]);
                    
                    // Create minimal upload state for cleanup of new file
                    $uploadState = new \stdClass();
                    $uploadState->filesCreated = [[
                        'disk' => $newFileData['storage_disk'],
                        'path' => $newFileData['file_path'],
                        'type' => 'main_file',
                        'created_at' => now()->toISOString(),
                    ]];
                    $uploadState->tempFiles = [];
                    $uploadState->storageOperations = [];
                    
                    $this->performAtomicUploadRollback($newFileData, $uploadState, $newFileData['correlation_id'] ?? Str::uuid()->toString());
                    
                    // Note: Old file is preserved since we didn't delete it yet
                    \Log::info('ContentBlockService: Old file preserved during update rollback', [
                        'content_id' => $content->id,
                        'old_file_path' => $oldFileData['file_path'] ?? 'none',
                    ]);
                }
                
                // Re-throw the exception to maintain transaction rollback behavior
                throw $e;
            }
        });
    }

    /**
     * Delete a content block.
     */
    public function deleteContentBlock(Content $content, bool $forceDelete = false): bool
    {
        return DB::transaction(function () use ($content, $forceDelete) {
            // Create version snapshot before deleting
            ContentBlockVersion::createSnapshot($content, 'delete', Auth::user());
            
            if ($forceDelete) {
                // Delete associated file if exists using consistent approach
                if ($content->file_path) {
                    $this->deleteOldFileConsistently($content, Str::uuid()->toString());
                }
                return $content->forceDelete();
            }

            // Soft delete - keeps the file in storage for potential restoration
            return $content->delete();
        });
    }

    /**
     * Restore a soft-deleted content block.
     */
    public function restoreContentBlock(int $contentId): bool
    {
        return DB::transaction(function () use ($contentId) {
            $content = Content::onlyTrashed()->find($contentId);
            
            if (!$content) {
                return false;
            }

            $restored = $content->restore();
            
            if ($restored) {
                // Create version snapshot for restoration
                ContentBlockVersion::createSnapshot($content, 'restore_from_trash', Auth::user());
                
                // Clear cache
                Cache::forget("subpage_contents_{$content->subpage_id}");
            }

            return $restored;
        });
    }

    /**
     * Delete old file using consistent storage approach.
     * 
     * Requirements 2.1, 2.4: Consistent storage strategy and comprehensive audit logging
     * 
     * @param Content $content The content with file to delete
     * @param string $correlationId Correlation ID for tracking
     */
    private function deleteOldFileConsistently(Content $content, string $correlationId): void
    {
        \Log::info('ContentBlockService: Starting consistent file deletion', [
            'correlation_id' => $correlationId,
            'content_id' => $content->id,
            'file_path' => $content->file_path,
            'storage_disk' => $content->storage_disk,
        ]);

        try {
            // Find actual file location (may differ from recorded location)
            $actualLocation = $content->findActualFileLocation();
            
            if (!$actualLocation) {
                \Log::warning('ContentBlockService: File not found for deletion', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                    'recorded_path' => $content->file_path,
                    'recorded_disk' => $content->storage_disk,
                ]);
                return;
            }
            
            // Delete file from actual location
            $deleted = Storage::disk($actualLocation->getDisk())->delete($actualLocation->getPath());
            
            if ($deleted) {
                // COMPREHENSIVE AUDIT LOGGING: Log successful deletion
                $this->logFileDeletionSuccess($content, $actualLocation, $correlationId);
            } else {
                \Log::error('ContentBlockService: File deletion failed', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                    'actual_disk' => $actualLocation->getDisk(),
                    'actual_path' => $actualLocation->getPath(),
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('ContentBlockService: Exception during file deletion', [
                'correlation_id' => $correlationId,
                'content_id' => $content->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Log file deletion success with comprehensive audit information.
     * 
     * Requirements 2.4: Add comprehensive audit logging
     * 
     * @param Content $content The content that had its file deleted
     * @param \App\Services\FileLocation $actualLocation The actual file location
     * @param string $correlationId Correlation ID for tracking
     */
    private function logFileDeletionSuccess(Content $content, \App\Services\FileLocation $actualLocation, string $correlationId): void
    {
        $auditLog = [
            'correlation_id' => $correlationId,
            'event_type' => 'file_deletion_success',
            'timestamp' => now()->toISOString(),
            'operation' => 'file_deletion',
            'result' => 'success',
            'user_context' => [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()?->name,
                'ip_address' => request()->ip(),
            ],
            'content_context' => [
                'content_id' => $content->id,
                'content_type' => $content->type,
                'content_title' => $content->title,
                'subpage_id' => $content->subpage_id,
            ],
            'file_context' => [
                'recorded_path' => $content->file_path,
                'recorded_disk' => $content->storage_disk,
                'actual_path' => $actualLocation->getPath(),
                'actual_disk' => $actualLocation->getDisk(),
                'location_consistent' => $content->storage_disk === $actualLocation->getDisk(),
                'original_filename' => $content->original_filename,
                'file_size' => $content->file_size,
            ],
            'server_context' => [
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
                'request_uri' => request()->getRequestUri(),
            ],
        ];
        
        \Log::info('AUDIT_LOG: File deletion completed successfully', $auditLog);
    }

    /**
     * Reorder content blocks within a subpage.
     */
    public function reorderContentBlocks(Subpage $subpage, array $contentIds): bool
    {
        return DB::transaction(function () use ($subpage, $contentIds) {
            // Validate that all content IDs belong to this subpage
            $validContentIds = Content::where('subpage_id', $subpage->id)
                ->whereIn('id', $contentIds)
                ->pluck('id')
                ->toArray();

            if (count($validContentIds) !== count($contentIds)) {
                throw new \InvalidArgumentException('Some content blocks do not belong to this subpage.');
            }

            // Update order_index for each content block
            Content::reorderForSubpage($subpage->id, $contentIds);
            
            // Clear any relevant caches
            Cache::forget("subpage_contents_{$subpage->id}");
            
            return true;
        });
    }

    /**
     * Update layout (row/column structure) for multiple blocks.
     * Records layout versions for undo/history functionality.
     */
    public function updateLayout(Subpage $subpage, array $blocks): bool
    {
        return DB::transaction(function () use ($subpage, $blocks) {
            $userId = Auth::id();

            foreach ($blocks as $blockData) {
                $content = Content::find($blockData['id']);
                
                // Ensure content exists and belongs to this subpage
                if ($content && $content->subpage_id == $subpage->id) {
                    // Capture BEFORE state for versioning
                    $beforeState = [
                        'row' => $content->metadata['row'] ?? null,
                        'span' => $content->metadata['span'] ?? null,
                        'order' => $content->order_index,
                    ];

                    $updateData = ['updated_by' => $userId];
                    
                    if (isset($blockData['order_index'])) {
                        $updateData['order_index'] = $blockData['order_index'];
                    }
                    
                    if (isset($blockData['metadata'])) {
                        // Merge with existing metadata to preserve other fields
                        $currentMetadata = $content->metadata ?? [];
                        $updateData['metadata'] = array_merge($currentMetadata, $blockData['metadata']);
                    }

                    $content->update($updateData);

                    // Capture AFTER state
                    $afterState = [
                        'row' => $content->metadata['row'] ?? $beforeState['row'],
                        'span' => $content->metadata['span'] ?? $beforeState['span'],
                        'order' => $content->order_index,
                    ];

                    // Determine action type based on what changed
                    $action = 'update';
                    if ($beforeState['span'] !== $afterState['span']) {
                        $action = 'resize';
                    } elseif ($beforeState['row'] !== $afterState['row'] || $beforeState['order'] !== $afterState['order']) {
                        $action = 'move';
                    }

                    // Record version only if something actually changed
                    if ($beforeState !== $afterState) {
                        \App\Models\ContentLayoutVersion::recordChange(
                            $content,
                            $beforeState,
                            $afterState,
                            $action,
                            $userId
                        );
                    }
                }
            }
            
            Cache::forget("subpage_contents_{$subpage->id}");
            
            return true;
        });
    }

    /**
     * Duplicate a content block.
     */
    public function duplicateContentBlock(Content $content): Content
    {
        return DB::transaction(function () use ($content) {
            $duplicateData = $content->toArray();
            
            // Remove unique fields
            unset($duplicateData['id'], $duplicateData['created_at'], $duplicateData['updated_at']);
            
            // Update metadata
            $duplicateData['title'] = $duplicateData['title'] . ' (Copy)';
            $duplicateData['section_order'] = Content::getNextSectionOrderIndex($content->subpage_id, $content->section);
            $duplicateData['order_index'] = Content::getNextOrderIndex($content->subpage_id);
            $duplicateData['created_by'] = Auth::id();
            $duplicateData['updated_by'] = Auth::id();

            // Handle file duplication if needed
            if ($content->file_path) {
                $duplicateData = $this->duplicateFile($content, $duplicateData);
            }

            $duplicate = Content::create($duplicateData);
            
            // Create version snapshot for the duplicate
            ContentBlockVersion::createSnapshot($duplicate, 'create', Auth::user());
            
            return $duplicate;
        });
    }

    /**
     * Move content block to a different section.
     */
    public function moveToSection(Content $content, string $newSection, int $newSectionOrder, User $user = null): bool
    {
        $user = $user ?? Auth::user();
        
        return DB::transaction(function () use ($content, $newSection, $newSectionOrder, $user) {
            // Create version snapshot
            ContentBlockVersion::createSnapshot($content, 'move_section', $user);
            
            // Adjust order of blocks in the new section
            $this->adjustSectionOrderAfterInsertion($content->subpage_id, $newSection, $newSectionOrder, $content->id);
            
            // Update the content block
            $content->update([
                'section' => $newSection,
                'section_order' => $newSectionOrder,
                'updated_by' => $user->id,
            ]);
            
            // Clear cache
            Cache::forget("subpage_contents_{$content->subpage_id}");
            
            return true;
        });
    }

    /**
     * Reorder content blocks within a section.
     */
    public function reorderSectionBlocks(int $subpageId, string $section, array $contentIds): bool
    {
        return DB::transaction(function () use ($subpageId, $section, $contentIds) {
            foreach ($contentIds as $index => $contentId) {
                $content = Content::find($contentId);
                if ($content && $content->subpage_id == $subpageId) {
                    // Create version snapshot for reordering
                    ContentBlockVersion::createSnapshot($content, 'reorder', Auth::user());
                    
                    $content->update([
                        'section' => $section,
                        'section_order' => $index + 1,
                        'updated_by' => Auth::id(),
                    ]);
                }
            }
            
            Cache::forget("subpage_contents_{$subpageId}");
            return true;
        });
    }

    /**
     * Restore a content block to a previous version.
     */
    public function restoreVersion(Content $content, int $versionNumber, User $user = null): bool
    {
        $user = $user ?? Auth::user();
        
        $version = ContentBlockVersion::getVersion($content->id, $versionNumber);
        if (!$version) {
            throw new \InvalidArgumentException('Version not found.');
        }
        
        return DB::transaction(function () use ($content, $version, $user) {
            // Create snapshot of current state before restoring
            ContentBlockVersion::createSnapshot($content, 'restore', $user);
            
            // Restore from version data (excluding timestamps and IDs)
            $versionData = $version->version_data;
            unset($versionData['id'], $versionData['created_at'], $versionData['updated_at']);
            $versionData['updated_by'] = $user->id;
            
            $content->fill($versionData);
            $content->save();
            
            return true;
        });
    }

    /**
     * Get version history for a content block.
     */
    public function getVersionHistory(Content $content): \Illuminate\Database\Eloquent\Collection
    {
        return ContentBlockVersion::getVersionHistory($content->id);
    }

    /**
     * Adjust section order after inserting a new block.
     */
    private function adjustSectionOrderAfterInsertion(int $subpageId, string $section, int $insertPosition, int $excludeContentId = null): void
    {
        $query = Content::where('subpage_id', $subpageId)
            ->where('section', $section)
            ->where('section_order', '>=', $insertPosition);
            
        if ($excludeContentId) {
            $query->where('id', '!=', $excludeContentId);
        }
        
        $query->increment('section_order');
    }

    /**
     * Handle file upload for content blocks using consistent storage strategy with comprehensive audit logging.
     * 
     * This method implements Requirements 2.1, 2.2, 2.4, 4.3:
     * - Ensures consistent storage strategy across all upload operations
     * - Normalizes file paths consistently
     * - Adds comprehensive audit logging
     * - Verifies file exists at stored path immediately after upload
     * - Validates database record matches actual file location
     * - Includes rollback logic for failed verifications
     * - Implements atomic upload operations with comprehensive cleanup
     */
    private function handleFileUpload(UploadedFile $file, string $contentType, Subpage $subpage): array
    {
        $correlationId = Str::uuid()->toString();
        $uploadState = new \stdClass();
        $uploadState->filesCreated = [];
        $uploadState->tempFiles = [];
        $uploadState->storageOperations = [];
        
        // STORAGE VALIDATION: Check if file operations are allowed before proceeding
        // Requirements: 8.1, 8.4
        if (!$this->storageValidator->areFileOperationsAllowed($correlationId)) {
            \Log::error('ContentBlockService: File upload blocked due to storage validation failure', [
                'correlation_id' => $correlationId,
                'content_type' => $contentType,
                'subpage_id' => $subpage->id,
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
            ]);
            
            $this->logUploadOperationFailure($correlationId, 'storage_validation_failed', [
                'reason' => 'Storage configuration validation failed',
                'validation_status' => $this->storageValidator->getValidationStatus($correlationId),
            ]);
            
            throw new \RuntimeException(
                'File upload is currently unavailable due to storage configuration issues. Please try again later or contact support.',
                503 // Service Unavailable
            );
        }
        
        \Log::info('ContentBlockService: Storage validation passed for file upload', [
            'correlation_id' => $correlationId,
            'functional_disks' => count($this->storageValidator->getValidationStatus($correlationId)['functional_disks']),
        ]);
        
        // COMPREHENSIVE AUDIT LOGGING: Start upload operation
        $this->logUploadOperationStart($file, $contentType, $subpage, $correlationId);

        // Validate file type (basic validation - detailed validation is done in controller)
        $config = Content::getContentTypeConfig($contentType);
        $allowedExtensions = $config['allowed_extensions'] ?? [];
        $maxFileSize = $config['max_file_size'] ?? (10 * 1024 * 1024);

        $fileExtension = strtolower($file->getClientOriginalExtension());
        
        \Log::info('ContentBlockService: File validation', [
            'correlation_id' => $correlationId,
            'file_extension' => $fileExtension,
            'allowed_extensions' => $allowedExtensions,
            'file_size' => $file->getSize(),
            'max_file_size' => $maxFileSize
        ]);

        if (!in_array($fileExtension, $allowedExtensions)) {
            $this->logUploadOperationFailure($correlationId, 'file_type_not_allowed', [
                'file_extension' => $fileExtension,
                'allowed_extensions' => $allowedExtensions
            ]);
            throw new \InvalidArgumentException('File type not allowed for this content type.');
        }

        if ($file->getSize() > $maxFileSize) {
            $this->logUploadOperationFailure($correlationId, 'file_size_exceeds_limit', [
                'file_size' => $file->getSize(),
                'max_file_size' => $maxFileSize
            ]);
            throw new \InvalidArgumentException('File size exceeds maximum allowed size.');
        }

        // CONSISTENT STORAGE STRATEGY: Determine storage approach based on content type and security requirements
        $storageStrategy = $this->determineConsistentStorageStrategy($contentType, $subpage, $correlationId);
        
        try {
            $context = [
                'user_id' => auth()->id(),
                'course_id' => $subpage->module->course_id,
                'module_id' => $subpage->module_id,
                'subpage_id' => $subpage->id,
                'content_type' => $contentType,
                'storage_strategy' => $storageStrategy,
            ];
            
            \Log::info('ContentBlockService: Using consistent storage strategy', [
                'correlation_id' => $correlationId,
                'storage_strategy' => $storageStrategy,
                'context' => $context,
            ]);

            // ATOMIC UPLOAD OPERATION: Store file using consistent strategy with normalized paths
            $storageResult = $this->storeFileWithConsistentStrategy(
                $file, 
                $contentType, 
                $storageStrategy,
                $context, 
                $correlationId,
                $uploadState
            );
            
            // NORMALIZE FILE PATHS: Ensure consistent path format
            $normalizedResult = $this->normalizeFilePathsConsistently($storageResult, $correlationId);
            
            \Log::info('ContentBlockService: File storage completed with consistent strategy', [
                'correlation_id' => $correlationId,
                'storage_strategy' => $storageStrategy,
                'storage_disk' => $normalizedResult['storage_disk'],
                'normalized_path' => $normalizedResult['file_path'],
                'file_hash' => substr($normalizedResult['file_hash'], 0, 8) . '...', // Log partial hash
            ]);

            // POST-UPLOAD VERIFICATION (Requirements 1.2, 2.3)
            $verificationResult = $this->verifyFileUpload($normalizedResult, $correlationId);
            
            if (!$verificationResult['success']) {
                // ATOMIC ROLLBACK: Clean up all partially stored files and operations
                $this->performAtomicUploadRollback($normalizedResult, $uploadState, $correlationId);
                
                $this->logUploadOperationFailure($correlationId, 'post_upload_verification_failed', [
                    'verification_errors' => $verificationResult['errors'],
                    'file_path' => $normalizedResult['file_path'],
                    'storage_disk' => $normalizedResult['storage_disk'],
                    'cleanup_performed' => true,
                ]);
                
                throw new \InvalidArgumentException(
                    'File upload verification failed: ' . implode(', ', $verificationResult['errors'])
                );
            }
            
            // COMPREHENSIVE AUDIT LOGGING: Log successful upload with all details
            $this->logUploadOperationSuccess($normalizedResult, $storageStrategy, $correlationId);
            
            return $normalizedResult;
            
        } catch (\Exception $e) {
            // ATOMIC ROLLBACK: Clean up any partially stored files on any exception
            if (isset($normalizedResult)) {
                $this->performAtomicUploadRollback($normalizedResult, $uploadState, $correlationId);
            } else {
                $this->performPartialUploadCleanup($uploadState, $correlationId);
            }
            
            $this->logUploadOperationFailure($correlationId, 'storage_exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'content_type' => $contentType,
                'file_name' => $file->getClientOriginalName(),
                'cleanup_performed' => true,
            ]);
            
            throw new \InvalidArgumentException('Failed to store file securely: ' . $e->getMessage());
        }
    }

    /**
     * Verify file upload by checking file existence and database record consistency.
     * 
     * Requirements 1.2: Verify file exists at stored path immediately after upload
     * Requirements 2.3: Validate database record matches actual file location
     * 
     * @param array $fileData File data from upload
     * @param string $correlationId Correlation ID for tracking
     * @return array Verification result with success status and any errors
     */
    private function verifyFileUpload(array $fileData, string $correlationId): array
    {
        \Log::info('ContentBlockService: Starting post-upload verification', [
            'correlation_id' => $correlationId,
            'file_path' => $fileData['file_path'],
            'storage_disk' => $fileData['storage_disk'],
        ]);

        $errors = [];
        $verificationDetails = [];

        try {
            // 1. Verify file exists at stored path (Requirement 1.2)
            $storageDisk = $fileData['storage_disk'];
            $filePath = $fileData['file_path'];
            
            if (!Storage::disk($storageDisk)->exists($filePath)) {
                $errors[] = "File does not exist at stored path: {$storageDisk}://{$filePath}";
                \Log::error('ContentBlockService: File existence verification failed', [
                    'correlation_id' => $correlationId,
                    'storage_disk' => $storageDisk,
                    'file_path' => $filePath,
                ]);
            } else {
                $verificationDetails['file_exists'] = true;
                \Log::debug('ContentBlockService: File existence verified', [
                    'correlation_id' => $correlationId,
                    'storage_disk' => $storageDisk,
                    'file_path' => $filePath,
                ]);
            }

            // 2. Verify file size matches expected size
            if (empty($errors)) {
                try {
                    $actualSize = Storage::disk($storageDisk)->size($filePath);
                    $expectedSize = $fileData['file_size'];
                    
                    if ($actualSize !== $expectedSize) {
                        $errors[] = "File size mismatch: expected {$expectedSize} bytes, found {$actualSize} bytes";
                        \Log::warning('ContentBlockService: File size verification failed', [
                            'correlation_id' => $correlationId,
                            'expected_size' => $expectedSize,
                            'actual_size' => $actualSize,
                        ]);
                    } else {
                        $verificationDetails['size_verified'] = true;
                        \Log::debug('ContentBlockService: File size verified', [
                            'correlation_id' => $correlationId,
                            'file_size' => $actualSize,
                        ]);
                    }
                } catch (\Exception $e) {
                    $errors[] = "Failed to verify file size: " . $e->getMessage();
                    \Log::error('ContentBlockService: File size check failed', [
                        'correlation_id' => $correlationId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // 3. Verify file hash if available (integrity check)
            if (empty($errors) && !empty($fileData['file_hash'])) {
                try {
                    $actualPath = Storage::disk($storageDisk)->path($filePath);
                    if (file_exists($actualPath)) {
                        $actualHash = hash_file('sha256', $actualPath);
                        $expectedHash = $fileData['file_hash'];
                        
                        if ($actualHash !== $expectedHash) {
                            $errors[] = "File integrity check failed: hash mismatch";
                            \Log::warning('ContentBlockService: File hash verification failed', [
                                'correlation_id' => $correlationId,
                                'expected_hash' => substr($expectedHash, 0, 16) . '...',
                                'actual_hash' => substr($actualHash, 0, 16) . '...',
                            ]);
                        } else {
                            $verificationDetails['hash_verified'] = true;
                            \Log::debug('ContentBlockService: File hash verified', [
                                'correlation_id' => $correlationId,
                                'hash_prefix' => substr($actualHash, 0, 16) . '...',
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    // Hash verification is not critical, log warning but don't fail
                    \Log::warning('ContentBlockService: File hash check failed (non-critical)', [
                        'correlation_id' => $correlationId,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // 4. Verify storage disk accessibility and permissions
            try {
                $diskConfig = config("filesystems.disks.{$storageDisk}");
                if (!$diskConfig) {
                    $errors[] = "Storage disk '{$storageDisk}' is not configured";
                } else {
                    $verificationDetails['disk_configured'] = true;
                }
            } catch (\Exception $e) {
                $errors[] = "Storage disk configuration error: " . $e->getMessage();
                \Log::error('ContentBlockService: Storage disk verification failed', [
                    'correlation_id' => $correlationId,
                    'storage_disk' => $storageDisk,
                    'error' => $e->getMessage(),
                ]);
            }

        } catch (\Exception $e) {
            $errors[] = "Verification process failed: " . $e->getMessage();
            \Log::error('ContentBlockService: Post-upload verification process failed', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        $success = empty($errors);
        
        \Log::info('ContentBlockService: Post-upload verification completed', [
            'correlation_id' => $correlationId,
            'success' => $success,
            'errors_count' => count($errors),
            'verification_details' => $verificationDetails,
        ]);

        return [
            'success' => $success,
            'errors' => $errors,
            'details' => $verificationDetails,
        ];
    }

    /**
     * Rollback file upload by cleaning up stored files.
     * 
     * @param array $fileData File data from failed upload
     * @param string $correlationId Correlation ID for tracking
     * @deprecated Use performAtomicUploadRollback for enhanced cleanup
     */
    private function rollbackFileUpload(array $fileData, string $correlationId): void
    {
        \Log::info('ContentBlockService: Starting file upload rollback', [
            'correlation_id' => $correlationId,
            'file_path' => $fileData['file_path'],
            'storage_disk' => $fileData['storage_disk'],
        ]);

        try {
            $storageDisk = $fileData['storage_disk'];
            $filePath = $fileData['file_path'];
            
            // Attempt to delete the uploaded file
            if (Storage::disk($storageDisk)->exists($filePath)) {
                $deleted = Storage::disk($storageDisk)->delete($filePath);
                
                if ($deleted) {
                    \Log::info('ContentBlockService: File successfully deleted during rollback', [
                        'correlation_id' => $correlationId,
                        'storage_disk' => $storageDisk,
                        'file_path' => $filePath,
                    ]);
                } else {
                    \Log::warning('ContentBlockService: Failed to delete file during rollback', [
                        'correlation_id' => $correlationId,
                        'storage_disk' => $storageDisk,
                        'file_path' => $filePath,
                    ]);
                }
            } else {
                \Log::debug('ContentBlockService: File already does not exist during rollback', [
                    'correlation_id' => $correlationId,
                    'storage_disk' => $storageDisk,
                    'file_path' => $filePath,
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('ContentBlockService: File rollback failed', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'file_path' => $fileData['file_path'] ?? 'unknown',
                'storage_disk' => $fileData['storage_disk'] ?? 'unknown',
            ]);
        }
    }

    /**
     * Perform atomic upload rollback with comprehensive cleanup.
     * 
     * Requirements 4.3: Implement cleanup for partially stored files on upload failure
     * 
     * @param array $fileData File data from failed upload
     * @param \stdClass $uploadState Upload state tracking object
     * @param string $correlationId Correlation ID for tracking
     */
    private function performAtomicUploadRollback(array $fileData, \stdClass $uploadState, string $correlationId): void
    {
        \Log::info('ContentBlockService: Starting atomic upload rollback', [
            'correlation_id' => $correlationId,
            'file_path' => $fileData['file_path'] ?? 'unknown',
            'storage_disk' => $fileData['storage_disk'] ?? 'unknown',
            'files_created_count' => count($uploadState->filesCreated ?? []),
            'temp_files_count' => count($uploadState->tempFiles ?? []),
            'storage_operations_count' => count($uploadState->storageOperations ?? []),
        ]);

        $cleanupResults = [
            'files_deleted' => 0,
            'temp_files_cleaned' => 0,
            'operations_reverted' => 0,
            'errors' => [],
        ];

        try {
            // 1. Clean up the main uploaded file
            if (!empty($fileData['file_path']) && !empty($fileData['storage_disk'])) {
                $this->cleanupSingleFile($fileData['storage_disk'], $fileData['file_path'], $correlationId, $cleanupResults);
            }

            // 2. Clean up any additional files created during upload process
            if (!empty($uploadState->filesCreated)) {
                foreach ($uploadState->filesCreated as $fileInfo) {
                    $this->cleanupSingleFile($fileInfo['disk'], $fileInfo['path'], $correlationId, $cleanupResults);
                }
            }

            // 3. Clean up temporary files
            if (!empty($uploadState->tempFiles)) {
                foreach ($uploadState->tempFiles as $tempFile) {
                    $this->cleanupTemporaryFile($tempFile, $correlationId, $cleanupResults);
                }
            }

            // 4. Revert storage operations (e.g., directory creation, metadata updates)
            if (!empty($uploadState->storageOperations)) {
                foreach (array_reverse($uploadState->storageOperations) as $operation) {
                    $this->revertStorageOperation($operation, $correlationId, $cleanupResults);
                }
            }

            // 5. Clear any cached data related to the upload
            $this->clearUploadRelatedCache($fileData, $correlationId);

            \Log::info('ContentBlockService: Atomic upload rollback completed', [
                'correlation_id' => $correlationId,
                'cleanup_results' => $cleanupResults,
            ]);

        } catch (\Exception $e) {
            $cleanupResults['errors'][] = $e->getMessage();
            
            \Log::error('ContentBlockService: Atomic upload rollback failed', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'cleanup_results' => $cleanupResults,
            ]);
        }

        // Log comprehensive cleanup audit
        $this->logAtomicRollbackAudit($fileData, $uploadState, $cleanupResults, $correlationId);
    }

    /**
     * Perform partial upload cleanup when full file data is not available.
     * 
     * Requirements 4.3: Ensure atomic upload operations
     * 
     * @param \stdClass $uploadState Upload state tracking object
     * @param string $correlationId Correlation ID for tracking
     */
    private function performPartialUploadCleanup(\stdClass $uploadState, string $correlationId): void
    {
        \Log::info('ContentBlockService: Starting partial upload cleanup', [
            'correlation_id' => $correlationId,
            'files_created_count' => count($uploadState->filesCreated ?? []),
            'temp_files_count' => count($uploadState->tempFiles ?? []),
            'storage_operations_count' => count($uploadState->storageOperations ?? []),
        ]);

        $cleanupResults = [
            'files_deleted' => 0,
            'temp_files_cleaned' => 0,
            'operations_reverted' => 0,
            'errors' => [],
        ];

        try {
            // Clean up any files that were created during the failed upload
            if (!empty($uploadState->filesCreated)) {
                foreach ($uploadState->filesCreated as $fileInfo) {
                    $this->cleanupSingleFile($fileInfo['disk'], $fileInfo['path'], $correlationId, $cleanupResults);
                }
            }

            // Clean up temporary files
            if (!empty($uploadState->tempFiles)) {
                foreach ($uploadState->tempFiles as $tempFile) {
                    $this->cleanupTemporaryFile($tempFile, $correlationId, $cleanupResults);
                }
            }

            // Revert storage operations
            if (!empty($uploadState->storageOperations)) {
                foreach (array_reverse($uploadState->storageOperations) as $operation) {
                    $this->revertStorageOperation($operation, $correlationId, $cleanupResults);
                }
            }

            \Log::info('ContentBlockService: Partial upload cleanup completed', [
                'correlation_id' => $correlationId,
                'cleanup_results' => $cleanupResults,
            ]);

        } catch (\Exception $e) {
            $cleanupResults['errors'][] = $e->getMessage();
            
            \Log::error('ContentBlockService: Partial upload cleanup failed', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
                'cleanup_results' => $cleanupResults,
            ]);
        }
    }

    /**
     * Clean up a single file during rollback.
     * 
     * @param string $disk Storage disk
     * @param string $path File path
     * @param string $correlationId Correlation ID for tracking
     * @param array &$cleanupResults Cleanup results array (passed by reference)
     */
    private function cleanupSingleFile(string $disk, string $path, string $correlationId, array &$cleanupResults): void
    {
        try {
            if (Storage::disk($disk)->exists($path)) {
                $deleted = Storage::disk($disk)->delete($path);
                
                if ($deleted) {
                    $cleanupResults['files_deleted']++;
                    \Log::debug('ContentBlockService: File deleted during cleanup', [
                        'correlation_id' => $correlationId,
                        'storage_disk' => $disk,
                        'file_path' => $path,
                    ]);
                } else {
                    $cleanupResults['errors'][] = "Failed to delete file: {$disk}://{$path}";
                    \Log::warning('ContentBlockService: Failed to delete file during cleanup', [
                        'correlation_id' => $correlationId,
                        'storage_disk' => $disk,
                        'file_path' => $path,
                    ]);
                }
            } else {
                \Log::debug('ContentBlockService: File already does not exist during cleanup', [
                    'correlation_id' => $correlationId,
                    'storage_disk' => $disk,
                    'file_path' => $path,
                ]);
            }
        } catch (\Exception $e) {
            $cleanupResults['errors'][] = "Exception cleaning file {$disk}://{$path}: " . $e->getMessage();
            \Log::error('ContentBlockService: Exception during file cleanup', [
                'correlation_id' => $correlationId,
                'storage_disk' => $disk,
                'file_path' => $path,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clean up a temporary file during rollback.
     * 
     * @param string $tempFilePath Temporary file path
     * @param string $correlationId Correlation ID for tracking
     * @param array &$cleanupResults Cleanup results array (passed by reference)
     */
    private function cleanupTemporaryFile(string $tempFilePath, string $correlationId, array &$cleanupResults): void
    {
        try {
            if (file_exists($tempFilePath)) {
                $deleted = unlink($tempFilePath);
                
                if ($deleted) {
                    $cleanupResults['temp_files_cleaned']++;
                    \Log::debug('ContentBlockService: Temporary file deleted during cleanup', [
                        'correlation_id' => $correlationId,
                        'temp_file_path' => $tempFilePath,
                    ]);
                } else {
                    $cleanupResults['errors'][] = "Failed to delete temporary file: {$tempFilePath}";
                }
            }
        } catch (\Exception $e) {
            $cleanupResults['errors'][] = "Exception cleaning temporary file {$tempFilePath}: " . $e->getMessage();
            \Log::error('ContentBlockService: Exception during temporary file cleanup', [
                'correlation_id' => $correlationId,
                'temp_file_path' => $tempFilePath,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Revert a storage operation during rollback.
     * 
     * @param array $operation Storage operation details
     * @param string $correlationId Correlation ID for tracking
     * @param array &$cleanupResults Cleanup results array (passed by reference)
     */
    private function revertStorageOperation(array $operation, string $correlationId, array &$cleanupResults): void
    {
        try {
            switch ($operation['type']) {
                case 'directory_created':
                    // Remove empty directory if it was created during upload
                    $disk = $operation['disk'];
                    $directory = $operation['directory'];
                    
                    if (Storage::disk($disk)->exists($directory)) {
                        $files = Storage::disk($disk)->files($directory);
                        if (empty($files)) {
                            Storage::disk($disk)->deleteDirectory($directory);
                            $cleanupResults['operations_reverted']++;
                            \Log::debug('ContentBlockService: Empty directory removed during cleanup', [
                                'correlation_id' => $correlationId,
                                'disk' => $disk,
                                'directory' => $directory,
                            ]);
                        }
                    }
                    break;
                    
                case 'metadata_updated':
                    // Revert metadata changes if applicable
                    $cleanupResults['operations_reverted']++;
                    \Log::debug('ContentBlockService: Metadata operation reverted during cleanup', [
                        'correlation_id' => $correlationId,
                        'operation' => $operation,
                    ]);
                    break;
                    
                default:
                    \Log::debug('ContentBlockService: Unknown operation type during cleanup', [
                        'correlation_id' => $correlationId,
                        'operation_type' => $operation['type'],
                    ]);
            }
        } catch (\Exception $e) {
            $cleanupResults['errors'][] = "Exception reverting operation: " . $e->getMessage();
            \Log::error('ContentBlockService: Exception during operation revert', [
                'correlation_id' => $correlationId,
                'operation' => $operation,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Clear upload-related cache entries.
     * 
     * @param array $fileData File data
     * @param string $correlationId Correlation ID for tracking
     */
    private function clearUploadRelatedCache(array $fileData, string $correlationId): void
    {
        try {
            // Clear any cache entries that might have been created during upload
            $cacheKeys = [
                "upload_progress_{$correlationId}",
                "upload_metadata_{$correlationId}",
                "file_validation_{$correlationId}",
            ];
            
            foreach ($cacheKeys as $key) {
                Cache::forget($key);
            }
            
            \Log::debug('ContentBlockService: Upload-related cache cleared', [
                'correlation_id' => $correlationId,
                'cache_keys_cleared' => count($cacheKeys),
            ]);
            
        } catch (\Exception $e) {
            \Log::warning('ContentBlockService: Failed to clear upload-related cache', [
                'correlation_id' => $correlationId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log comprehensive audit information for atomic rollback.
     * 
     * Requirements 4.3: Add clear error feedback for users
     * 
     * @param array $fileData File data
     * @param \stdClass $uploadState Upload state
     * @param array $cleanupResults Cleanup results
     * @param string $correlationId Correlation ID for tracking
     */
    private function logAtomicRollbackAudit(array $fileData, \stdClass $uploadState, array $cleanupResults, string $correlationId): void
    {
        $auditLog = [
            'correlation_id' => $correlationId,
            'event_type' => 'atomic_upload_rollback',
            'timestamp' => now()->toISOString(),
            'operation' => 'upload_failure_cleanup',
            'result' => empty($cleanupResults['errors']) ? 'success' : 'partial_success',
            'user_context' => [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()?->name,
                'ip_address' => request()->ip(),
            ],
            'file_context' => [
                'file_path' => $fileData['file_path'] ?? 'unknown',
                'storage_disk' => $fileData['storage_disk'] ?? 'unknown',
                'original_filename' => $fileData['original_filename'] ?? 'unknown',
                'file_size' => $fileData['file_size'] ?? 0,
            ],
            'cleanup_summary' => [
                'files_deleted' => $cleanupResults['files_deleted'],
                'temp_files_cleaned' => $cleanupResults['temp_files_cleaned'],
                'operations_reverted' => $cleanupResults['operations_reverted'],
                'errors_count' => count($cleanupResults['errors']),
                'total_files_tracked' => count($uploadState->filesCreated ?? []),
                'total_temp_files_tracked' => count($uploadState->tempFiles ?? []),
                'total_operations_tracked' => count($uploadState->storageOperations ?? []),
            ],
            'cleanup_errors' => $cleanupResults['errors'],
            'user_feedback' => $this->generateUserFriendlyErrorMessage($cleanupResults),
        ];
        
        if (empty($cleanupResults['errors'])) {
            \Log::info('AUDIT_LOG: Atomic upload rollback completed successfully', $auditLog);
        } else {
            \Log::warning('AUDIT_LOG: Atomic upload rollback completed with errors', $auditLog);
        }
    }

    /**
     * Generate user-friendly error message for upload failures.
     * 
     * Requirements 4.3: Add clear error feedback for users
     * 
     * @param array $cleanupResults Cleanup results
     * @return string User-friendly error message
     */
    private function generateUserFriendlyErrorMessage(array $cleanupResults): string
    {
        if (empty($cleanupResults['errors'])) {
            return 'Upload failed, but all temporary files have been cleaned up successfully. Please try uploading again.';
        }
        
        $totalCleaned = $cleanupResults['files_deleted'] + $cleanupResults['temp_files_cleaned'];
        $errorCount = count($cleanupResults['errors']);
        
        if ($totalCleaned > 0 && $errorCount > 0) {
            return "Upload failed. Most temporary files were cleaned up successfully, but {$errorCount} cleanup operations encountered issues. Please try uploading again.";
        } elseif ($totalCleaned === 0 && $errorCount > 0) {
            return "Upload failed and some cleanup operations encountered issues. Please contact support if the problem persists.";
        } else {
            return 'Upload failed. Please check your file and try again.';
        }
    }

    /**
     * Verify that the database record matches the actual file location.
     * 
     * Requirements 2.3: Record correct storage disk and file path in database
     * 
     * @param Content $content The content record to verify
     * @param string|null $correlationId Correlation ID for tracking
     */
    private function verifyDatabaseRecordConsistency(Content $content, ?string $correlationId = null): void
    {
        $correlationId = $correlationId ?? Str::uuid()->toString();
        
        \Log::info('ContentBlockService: Starting database record consistency verification', [
            'correlation_id' => $correlationId,
            'content_id' => $content->id,
            'recorded_file_path' => $content->file_path,
            'recorded_storage_disk' => $content->storage_disk,
        ]);

        try {
            // Use the diagnostic service to verify the file location
            $diagnosticResult = $this->diagnosticService->diagnoseFileStorageIssues($content, $correlationId);
            
            if (!$diagnosticResult->fileExists()) {
                \Log::error('ContentBlockService: Database record verification failed - file not found', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                    'recorded_file_path' => $content->file_path,
                    'recorded_storage_disk' => $content->storage_disk,
                ]);
                
                throw new \InvalidArgumentException(
                    'Database record verification failed: File not found at recorded location'
                );
            }
            
            if ($diagnosticResult->hasInconsistencies()) {
                $inconsistencies = $diagnosticResult->getInconsistencies();
                
                \Log::warning('ContentBlockService: Database record inconsistencies detected', [
                    'correlation_id' => $correlationId,
                    'content_id' => $content->id,
                    'inconsistencies' => array_keys($inconsistencies),
                ]);
                
                // Trigger automatic repair for non-critical inconsistencies
                $criticalInconsistencies = ['storage_disk_mismatch', 'file_size_mismatch', 'file_hash_mismatch'];
                $hasCriticalInconsistency = !empty(array_intersect(array_keys($inconsistencies), $criticalInconsistencies));
                
                if (!$hasCriticalInconsistency && $this->repairTriggerService) {
                    // Attempt automatic repair for minor inconsistencies
                    $repairTriggered = $this->repairTriggerService->triggerRepairOnInconsistency(
                        $content, 
                        'post_upload_inconsistency',
                        [
                            'correlation_id' => $correlationId,
                            'inconsistencies' => array_keys($inconsistencies),
                            'trigger_context' => 'content_block_verification'
                        ]
                    );
                    
                    if ($repairTriggered) {
                        \Log::info('ContentBlockService: Automatic repair triggered for inconsistencies', [
                            'correlation_id' => $correlationId,
                            'content_id' => $content->id,
                            'inconsistencies' => array_keys($inconsistencies),
                        ]);
                    } else {
                        \Log::warning('ContentBlockService: Automatic repair could not be triggered', [
                            'correlation_id' => $correlationId,
                            'content_id' => $content->id,
                            'inconsistencies' => array_keys($inconsistencies),
                        ]);
                    }
                }
                
                // For newly created records, critical inconsistencies are still errors
                if ($hasCriticalInconsistency) {
                    throw new \InvalidArgumentException(
                        'Database record verification failed: Critical inconsistencies detected - ' . 
                        implode(', ', array_keys($inconsistencies))
                    );
                }
            }
            
            \Log::info('ContentBlockService: Database record consistency verification passed', [
                'correlation_id' => $correlationId,
                'content_id' => $content->id,
                'file_exists' => $diagnosticResult->fileExists(),
                'has_inconsistencies' => $diagnosticResult->hasInconsistencies(),
            ]);
            
        } catch (\Exception $e) {
            \Log::error('ContentBlockService: Database record consistency verification failed', [
                'correlation_id' => $correlationId,
                'content_id' => $content->id,
                'error' => $e->getMessage(),
            ]);
            
            // Re-throw the exception to fail the upload process
            throw $e;
        }
    }

    /**
     * Duplicate file for content duplication using consistent storage approach.
     * 
     * Requirements 2.1, 2.4: Consistent storage strategy and comprehensive audit logging
     */
    private function duplicateFile(Content $originalContent, array $duplicateData): array
    {
        $correlationId = Str::uuid()->toString();
        
        \Log::info('ContentBlockService: Starting file duplication with consistent storage', [
            'correlation_id' => $correlationId,
            'original_content_id' => $originalContent->id,
            'original_file_path' => $originalContent->file_path,
            'original_storage_disk' => $originalContent->storage_disk,
        ]);

        // Find actual file location
        $actualLocation = $originalContent->findActualFileLocation();
        
        if (!$actualLocation) {
            \Log::warning('ContentBlockService: Original file not found for duplication', [
                'correlation_id' => $correlationId,
                'original_content_id' => $originalContent->id,
                'recorded_path' => $originalContent->file_path,
                'recorded_disk' => $originalContent->storage_disk,
            ]);
            return $duplicateData;
        }

        try {
            // Generate consistent new filename
            $originalPath = $actualLocation->getPath();
            $pathInfo = pathinfo($originalPath);
            $extension = $pathInfo['extension'] ?? '';
            
            $newFilename = $this->generateConsistentFilename(
                new class($originalContent->original_filename ?? 'duplicate_file.' . $extension) {
                    private $filename;
                    public function __construct($filename) { $this->filename = $filename; }
                    public function getClientOriginalName() { return $this->filename; }
                    public function getClientOriginalExtension() { return pathinfo($this->filename, PATHINFO_EXTENSION); }
                },
                $correlationId
            );
            
            // Generate consistent storage path
            $newStoragePath = $this->generateConsistentStoragePath($originalContent->type, [
                'course_id' => $originalContent->subpage->module->course_id,
            ]);
            
            $newFilePath = $newStoragePath . '/' . $newFilename;
            
            // Copy file using same storage disk as original (maintain consistency)
            $copied = Storage::disk($actualLocation->getDisk())->copy($originalPath, $newFilePath);
            
            if ($copied) {
                // Normalize the new file path consistently
                $normalizedPath = $this->normalizeFilePathsConsistently([
                    'file_path' => $newFilePath,
                    'storage_disk' => $actualLocation->getDisk(),
                ], $correlationId);
                
                $duplicateData['file_path'] = $normalizedPath['file_path'];
                $duplicateData['storage_disk'] = $actualLocation->getDisk();
                $duplicateData['original_filename'] = $originalContent->original_filename;
                $duplicateData['file_size'] = $originalContent->file_size;
                $duplicateData['mime_type'] = $originalContent->mime_type;
                $duplicateData['file_hash'] = $originalContent->file_hash; // Will be different but we'll update later if needed
                
                // COMPREHENSIVE AUDIT LOGGING: Log successful duplication
                $this->logFileDuplicationSuccess($originalContent, $duplicateData, $correlationId);
                
                \Log::info('ContentBlockService: File duplication completed successfully', [
                    'correlation_id' => $correlationId,
                    'original_path' => $originalPath,
                    'new_path' => $normalizedPath['file_path'],
                    'storage_disk' => $actualLocation->getDisk(),
                ]);
            } else {
                \Log::error('ContentBlockService: File duplication failed', [
                    'correlation_id' => $correlationId,
                    'original_path' => $originalPath,
                    'new_path' => $newFilePath,
                    'storage_disk' => $actualLocation->getDisk(),
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('ContentBlockService: Exception during file duplication', [
                'correlation_id' => $correlationId,
                'original_content_id' => $originalContent->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return $duplicateData;
    }

    /**
     * Log file duplication success with comprehensive audit information.
     * 
     * Requirements 2.4: Add comprehensive audit logging
     * 
     * @param Content $originalContent The original content
     * @param array $duplicateData The duplicate data
     * @param string $correlationId Correlation ID for tracking
     */
    private function logFileDuplicationSuccess(Content $originalContent, array $duplicateData, string $correlationId): void
    {
        $auditLog = [
            'correlation_id' => $correlationId,
            'event_type' => 'file_duplication_success',
            'timestamp' => now()->toISOString(),
            'operation' => 'file_duplication',
            'result' => 'success',
            'user_context' => [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()?->name,
                'ip_address' => request()->ip(),
            ],
            'original_content' => [
                'content_id' => $originalContent->id,
                'content_type' => $originalContent->type,
                'file_path' => $originalContent->file_path,
                'storage_disk' => $originalContent->storage_disk,
                'file_size' => $originalContent->file_size,
            ],
            'duplicate_content' => [
                'file_path' => $duplicateData['file_path'],
                'storage_disk' => $duplicateData['storage_disk'],
                'file_size' => $duplicateData['file_size'],
                'original_filename' => $duplicateData['original_filename'],
            ],
            'consistency_checks' => [
                'path_normalized' => true,
                'storage_disk_consistent' => true,
                'duplication_strategy' => 'consistent_storage',
            ],
        ];
        
        \Log::info('AUDIT_LOG: File duplication completed successfully', $auditLog);
    }

    /**
     * Get content blocks for a subpage with proper permissions.
     */
    public function getContentBlocksForSubpage(Subpage $subpage, User $user = null): \Illuminate\Database\Eloquent\Collection
    {
        $user = $user ?? Auth::user();
        
        $query = $subpage->contents()->orderedBySection();

        // Apply visibility filters based on user role
        if ($user->hasRole('Student')) {
            $query->published()->visibleToStudents();
        } else {
            $query->visibleToTeachers();
        }

        return $query->with(['creator', 'updater'])->get();
    }

    /**
     * Get content blocks grouped by section.
     */
    public function getContentBlocksBySection(Subpage $subpage, User $user = null): array
    {
        $contentBlocks = $this->getContentBlocksForSubpage($subpage, $user);
        
        $sections = ContentSection::getAllSections();
        $groupedContent = [];
        
        foreach ($sections as $section) {
            $sectionBlocks = $contentBlocks->where('section', $section['value'])->values();
            $groupedContent[$section['value']] = [
                'section_info' => $section,
                'blocks' => $sectionBlocks,
                'block_count' => $sectionBlocks->count(),
            ];
        }
        
        return $groupedContent;
    }

    /**
     * Get trashed content blocks for a subpage.
     */
    public function getTrashedContentBlocksForSubpage(Subpage $subpage): \Illuminate\Database\Eloquent\Collection
    {
        return $subpage->contents()
            ->onlyTrashed()
            ->orderedBySection()
            ->with(['creator', 'updater'])
            ->get();
    }

    /**
     * Get trashed content blocks grouped by section.
     */
    public function getTrashedContentBlocksBySection(Subpage $subpage): array
    {
        $trashedBlocks = $this->getTrashedContentBlocksForSubpage($subpage);
        
        $sections = ContentSection::getAllSections();
        $groupedTrashed = [];
        
        foreach ($sections as $section) {
            $sectionBlocks = $trashedBlocks->where('section', $section['value'])->values();
            $groupedTrashed[$section['value']] = [
                'section_info' => $section,
                'blocks' => $sectionBlocks,
                'block_count' => $sectionBlocks->count(),
            ];
        }
        
        return $groupedTrashed;
    }

    /**
     * Initialize empty content page for new subpage.
     */
    public function initializeSubpageContent(Subpage $subpage): void
    {
        // Create a default welcome text block in the introduction section
        $this->createContentBlock($subpage, [
            'title' => 'Welcome',
            'type' => 'text',
            'content' => '<h2>Welcome to ' . $subpage->title . '</h2><p>This page is ready for content. Click the edit button to start adding content blocks.</p>',
            'section' => ContentSection::INTRODUCTION->value,
            'visibility' => ContentVisibility::STUDENT->value,
            'section_order' => 1,
        ]);
    }

    /**
     * Determine consistent storage strategy based on content type and security requirements.
     * 
     * Requirements 2.1: Apply clear rules for choosing between 'public' and 'protected' storage disks
     * 
     * @param string $contentType The content type
     * @param Subpage $subpage The subpage context
     * @param string $correlationId Correlation ID for tracking
     * @return string Storage strategy ('secure' or 'public')
     */
    private function determineConsistentStorageStrategy(string $contentType, Subpage $subpage, string $correlationId): string
    {
        \Log::info('ContentBlockService: Determining consistent storage strategy', [
            'correlation_id' => $correlationId,
            'content_type' => $contentType,
            'subpage_id' => $subpage->id,
            'course_id' => $subpage->module->course_id,
        ]);

        // CONSISTENT STORAGE RULES:
        // 1. All new uploads use 'protected' storage for security (outside web root)
        // 2. This ensures consistent behavior across all content types
        // 3. Files are served through secure controllers with proper access control
        
        $strategy = 'secure'; // Always use secure storage for new uploads
        
        \Log::info('ContentBlockService: Storage strategy determined', [
            'correlation_id' => $correlationId,
            'strategy' => $strategy,
            'storage_disk' => 'protected',
            'reasoning' => 'All new uploads use protected storage for security and consistency',
        ]);
        
        return $strategy;
    }

    /**
     * Store file using consistent strategy with normalized paths.
     * 
     * Requirements 2.1, 2.2, 4.3: Consistent storage strategy, normalized file paths, and atomic operations
     * 
     * @param UploadedFile $file The uploaded file
     * @param string $contentType The content type
     * @param string $storageStrategy The storage strategy
     * @param array $context Additional context
     * @param string $correlationId Correlation ID for tracking
     * @param \stdClass $uploadState Upload state tracking object
     * @return array Storage result
     */
    private function storeFileWithConsistentStrategy(
        UploadedFile $file, 
        string $contentType, 
        string $storageStrategy,
        array $context, 
        string $correlationId,
        \stdClass $uploadState
    ): array {
        \Log::info('ContentBlockService: Storing file with consistent strategy', [
            'correlation_id' => $correlationId,
            'storage_strategy' => $storageStrategy,
            'content_type' => $contentType,
        ]);

        if ($storageStrategy === 'secure') {
            // Use SecureFileStorageService for consistent secure storage
            $secureStorageResult = $this->secureFileStorage->storeFileSecurely(
                $file, 
                $contentType, 
                $context, 
                $correlationId
            );
            
            // Track the file creation for potential rollback
            $uploadState->filesCreated[] = [
                'disk' => $secureStorageResult['storage_disk'],
                'path' => $secureStorageResult['file_path'],
                'type' => 'main_file',
                'created_at' => now()->toISOString(),
            ];
            
            // Track storage operation for potential rollback
            $uploadState->storageOperations[] = [
                'type' => 'secure_file_storage',
                'disk' => $secureStorageResult['storage_disk'],
                'path' => $secureStorageResult['file_path'],
                'operation_at' => now()->toISOString(),
            ];
            
            // Convert to consistent format
            return [
                'file_path' => $secureStorageResult['file_path'],
                'original_filename' => $secureStorageResult['original_filename'],
                'file_size' => $secureStorageResult['file_size'],
                'mime_type' => $secureStorageResult['mime_type'],
                'file_hash' => $secureStorageResult['file_hash'],
                'storage_disk' => $secureStorageResult['storage_disk'],
                'correlation_id' => $correlationId,
                'storage_strategy' => $storageStrategy,
                // Legacy compatibility
                'file_name' => $secureStorageResult['original_filename'],
            ];
        } else {
            // Fallback to public storage (for backward compatibility)
            // This path should rarely be used with the new consistent strategy
            \Log::warning('ContentBlockService: Using fallback public storage', [
                'correlation_id' => $correlationId,
                'reason' => 'Fallback for backward compatibility',
            ]);
            
            $filename = $this->generateConsistentFilename($file, $correlationId);
            $storagePath = $this->generateConsistentStoragePath($contentType, $context);
            $fullPath = $storagePath . '/' . $filename;
            
            // Track temporary file path before storage
            $uploadState->tempFiles[] = $file->getRealPath();
            
            $storedPath = $file->storeAs($storagePath, $filename, 'public');
            
            // Track the file creation for potential rollback
            $uploadState->filesCreated[] = [
                'disk' => 'public',
                'path' => $storedPath,
                'type' => 'main_file',
                'created_at' => now()->toISOString(),
            ];
            
            // Track directory creation if it was created
            $directory = dirname($storedPath);
            if (!Storage::disk('public')->exists($directory)) {
                $uploadState->storageOperations[] = [
                    'type' => 'directory_created',
                    'disk' => 'public',
                    'directory' => $directory,
                    'operation_at' => now()->toISOString(),
                ];
            }
            
            return [
                'file_path' => $storedPath,
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'file_hash' => hash_file('sha256', $file->getRealPath()),
                'storage_disk' => 'public',
                'correlation_id' => $correlationId,
                'storage_strategy' => $storageStrategy,
                'file_name' => $file->getClientOriginalName(),
            ];
        }
    }

    /**
     * Normalize file paths consistently across all storage operations.
     * 
     * Requirements 2.4: Ensure file paths are normalized consistently
     * 
     * @param array $storageResult Storage result from upload
     * @param string $correlationId Correlation ID for tracking
     * @return array Normalized storage result
     */
    private function normalizeFilePathsConsistently(array $storageResult, string $correlationId): array
    {
        \Log::debug('ContentBlockService: Normalizing file paths consistently', [
            'correlation_id' => $correlationId,
            'original_path' => $storageResult['file_path'],
            'storage_disk' => $storageResult['storage_disk'],
        ]);

        // CONSISTENT PATH NORMALIZATION RULES:
        // 1. Remove leading/trailing slashes
        // 2. Convert backslashes to forward slashes (Windows compatibility)
        // 3. Remove duplicate slashes
        // 4. Ensure consistent directory separators
        
        $originalPath = $storageResult['file_path'];
        
        // Normalize path separators
        $normalizedPath = str_replace('\\', '/', $originalPath);
        
        // Remove duplicate slashes
        $normalizedPath = preg_replace('#/+#', '/', $normalizedPath);
        
        // Remove leading slash (storage paths should be relative)
        $normalizedPath = ltrim($normalizedPath, '/');
        
        // Remove trailing slash
        $normalizedPath = rtrim($normalizedPath, '/');
        
        // Ensure path is not empty
        if (empty($normalizedPath)) {
            throw new \InvalidArgumentException('File path cannot be empty after normalization');
        }
        
        $storageResult['file_path'] = $normalizedPath;
        
        \Log::info('ContentBlockService: File path normalized consistently', [
            'correlation_id' => $correlationId,
            'original_path' => $originalPath,
            'normalized_path' => $normalizedPath,
            'normalization_applied' => $originalPath !== $normalizedPath,
        ]);
        
        return $storageResult;
    }

    /**
     * Generate consistent filename for uploads.
     * 
     * @param UploadedFile $file The uploaded file
     * @param string $correlationId Correlation ID for tracking
     * @return string Consistent filename
     */
    private function generateConsistentFilename(UploadedFile $file, string $correlationId): string
    {
        $originalName = $file->getClientOriginalName();
        $pathInfo = pathinfo($originalName);
        $extension = isset($pathInfo['extension']) ? strtolower($pathInfo['extension']) : '';
        $basename = $pathInfo['filename'] ?? 'file';
        
        // Sanitize basename consistently
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $basename);
        $basename = trim($basename, '_-');
        $basename = substr($basename, 0, 50);
        
        if (empty($basename)) {
            $basename = 'content_file';
        }
        
        // Generate consistent unique components
        $timestamp = time();
        $randomBytes = random_bytes(8);
        $randomHex = bin2hex($randomBytes);
        
        $filename = sprintf('%s_%d_%s', $basename, $timestamp, $randomHex);
        
        if ($extension) {
            $filename .= ".{$extension}";
        }
        
        return $filename;
    }

    /**
     * Generate consistent storage path.
     * 
     * @param string $contentType The content type
     * @param array $context Additional context
     * @return string Storage path
     */
    private function generateConsistentStoragePath(string $contentType, array $context): string
    {
        $year = date('Y');
        $month = date('m');
        
        $coursePath = isset($context['course_id']) ? "courses/{$context['course_id']}" : 'general';
        
        return implode('/', [
            'content_blocks',
            $contentType,
            $year,
            $month,
            $coursePath
        ]);
    }

    /**
     * Log upload operation start with comprehensive audit information.
     * 
     * Requirements 2.4: Add comprehensive audit logging
     * 
     * @param UploadedFile $file The uploaded file
     * @param string $contentType The content type
     * @param Subpage $subpage The subpage context
     * @param string $correlationId Correlation ID for tracking
     */
    private function logUploadOperationStart(UploadedFile $file, string $contentType, Subpage $subpage, string $correlationId): void
    {
        $auditLog = [
            'correlation_id' => $correlationId,
            'event_type' => 'upload_operation_start',
            'timestamp' => now()->toISOString(),
            'operation' => 'file_upload',
            'user_context' => [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()?->name,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
            'file_context' => [
                'original_filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'file_size_formatted' => $this->formatBytes($file->getSize()),
                'mime_type' => $file->getMimeType(),
                'extension' => $file->getClientOriginalExtension(),
                'content_type' => $contentType,
            ],
            'business_context' => [
                'subpage_id' => $subpage->id,
                'subpage_title' => $subpage->title,
                'module_id' => $subpage->module_id,
                'module_title' => $subpage->module->title,
                'course_id' => $subpage->module->course_id,
                'course_title' => $subpage->module->course->title,
                'teacher_id' => $subpage->module->course->teacher_id,
            ],
            'server_context' => [
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
                'request_method' => request()->method(),
                'request_uri' => request()->getRequestUri(),
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
            ],
        ];
        
        \Log::info('AUDIT_LOG: Upload operation started', $auditLog);
    }

    /**
     * Log upload operation success with comprehensive audit information.
     * 
     * Requirements 2.4: Add comprehensive audit logging
     * 
     * @param array $storageResult Storage result
     * @param string $storageStrategy Storage strategy used
     * @param string $correlationId Correlation ID for tracking
     */
    private function logUploadOperationSuccess(array $storageResult, string $storageStrategy, string $correlationId): void
    {
        $auditLog = [
            'correlation_id' => $correlationId,
            'event_type' => 'upload_operation_success',
            'timestamp' => now()->toISOString(),
            'operation' => 'file_upload',
            'result' => 'success',
            'user_context' => [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()?->name,
                'ip_address' => request()->ip(),
            ],
            'storage_result' => [
                'file_path' => $storageResult['file_path'],
                'storage_disk' => $storageResult['storage_disk'],
                'storage_strategy' => $storageStrategy,
                'file_size' => $storageResult['file_size'],
                'file_size_formatted' => $this->formatBytes($storageResult['file_size']),
                'mime_type' => $storageResult['mime_type'],
                'file_hash_prefix' => substr($storageResult['file_hash'], 0, 16) . '...',
                'original_filename' => $storageResult['original_filename'],
            ],
            'consistency_checks' => [
                'path_normalized' => true,
                'storage_strategy_applied' => $storageStrategy,
                'post_upload_verification' => 'passed',
            ],
            'performance_metrics' => [
                'upload_completed_at' => now()->toISOString(),
            ],
        ];
        
        \Log::info('AUDIT_LOG: Upload operation completed successfully', $auditLog);
    }

    /**
     * Log upload operation failure with comprehensive audit information.
     * 
     * Requirements 2.4: Add comprehensive audit logging
     * 
     * @param string $correlationId Correlation ID for tracking
     * @param string $failureReason Reason for failure
     * @param array $failureContext Additional failure context
     */
    private function logUploadOperationFailure(string $correlationId, string $failureReason, array $failureContext): void
    {
        $auditLog = [
            'correlation_id' => $correlationId,
            'event_type' => 'upload_operation_failure',
            'timestamp' => now()->toISOString(),
            'operation' => 'file_upload',
            'result' => 'failure',
            'user_context' => [
                'user_id' => auth()->id(),
                'user_name' => auth()->user()?->name,
                'ip_address' => request()->ip(),
            ],
            'failure_details' => [
                'failure_reason' => $failureReason,
                'failure_context' => $failureContext,
            ],
            'server_context' => [
                'server_name' => $_SERVER['SERVER_NAME'] ?? 'unknown',
                'request_uri' => request()->getRequestUri(),
                'timestamp' => now()->toISOString(),
            ],
        ];
        
        \Log::error('AUDIT_LOG: Upload operation failed', $auditLog);
    }

    /**
     * Format bytes to human readable format.
     * 
     * @param int $bytes Number of bytes
     * @return string Formatted string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $bytes;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }
}