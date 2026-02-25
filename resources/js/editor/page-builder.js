/**
 * PageBuilder / PageLayoutEngine
 * 
 * Implements Google Sites-style layout management and Unified Rendering.
 * Serves as both the Editor (Teacher) and the Viewer (Student).
 */


// NOTE: SortableJS removed - using pointer-based drag engine instead
import axios from 'axios';
import { EditorManager } from './editor-init.js';


export class PageBuilder {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        if (!this.container) {
            console.error(`PageBuilder: Container #${containerId} not found`);
            return;
        }

        // --- Instance Identifier for Debugging ---
        this.instanceId = Math.random().toString(36).substring(2, 9);
        console.log(`[PageBuilder] Instance initialized: ${this.instanceId}`);

        // --- Intelligent Resize Policies ---
        // --- Intelligent Resize Policies ---
        this.resizePolicy = {
            text: {
                minSpanPx: 280,
                minHeight: 48,
                allowTopResize: true,
                allowBottomResize: true,
                allowClip: false
            },
            image: {
                minSpan: 2,
                minHeight: 120,
                maintainAspectRatio: true,
                allowClip: false
            },
            video: {
                minSpan: 4,
                minHeight: 180,
                maintainAspectRatio: true
            },
            youtube: {
                minSpan: 4,
                minHeight: 180,
                maintainAspectRatio: true
            },
            audio: {
                minSpan: 6,
                minHeight: 64
            },
            default: {
                minSpan: 2,
                minHeight: 48
            }
        };

        this.initialData = options.initialData || [];
        this.readOnly = options.readOnly || false;
        this.options = options; // Assign options for later use

        this.options = options;
        this.saveLayoutEndpoint = options.saveLayoutEndpoint || null;
        this.saveContentBaseUrl = options.saveContentBaseUrl || null; // FIX: Assign properly

        // Toolbar (Phase 6: Rich Text) - Removed per user request
        this.toolbar = null;


        // Block Registry (Phase 5)
        this.blockRegistry = new Map();
        this._registerDefaultBlocks();

        this.csrfToken = options.csrfToken || null;

        // Callbacks
        this.onLayoutSaved = options.onLayoutSaved || (() => { });
        this.onLayoutError = options.onLayoutError || ((err) => console.error(err));
        this.onEditBlock = options.onEditBlock || (() => console.warn('onEditBlock not implemented'));
        this.onDeleteBlock = options.onDeleteBlock || (() => console.warn('onDeleteBlock not implemented'));

        // State
        this.editors = new Map(); // blockId -> EditorManager instance
        this.saveTimeouts = new Map(); // blockId -> timeoutId
        this.rows = [];
        this.sortables = []; // Track sortable instances
        this.isResizing = false; // Global Resize Lock

        // Responsive Layout (Phase 1)
        this.activeBreakpoint = 'desktop'; // 'desktop' | 'tablet' | 'mobile'

        // Undo/Redo (Phase 3)
        this.undoStack = [];
        this.redoStack = [];
        this.maxUndoSteps = 50;

        // Clipboard (Phase 4)
        this.clipboard = null;

        // Initialize Creation Promises tracking
        this.blockCreationPromises = new Map();

        // Google Sites Drop Indicator (Column-Aware Drag)
        this.dropIndicator = this._createDropIndicator();
        this.isDragging = false;
        this.activeDropRow = null;

        // --- Multi-File Upload State ---
        this.pendingBlocks = new Map(); // correlationId -> { type, fileName }

        this._init();
        this._setupKeyboardShortcuts();
    }

    _init() {
        this.render(); // render() calls _parseDataToLayout() internally
    }

    /**
     * Groups content blocks into Rows and Columns
     * Uses metadata.row_id and metadata.column_index
     */
    /**
     * Groups content blocks into Rows.
     * CRITICAL: Each block gets its own dedicated column (1 Block = 1 Column).
     * Uses metadata.row_id for row grouping. Column is per-block.
     */
    _parseDataToLayout() {
        const rowsMap = new Map();

        // Sort blocks by order first to preserve visual sequence
        const sortedBlocks = [...this.initialData].sort((a, b) => {
            const orderA = a.order || a.order_index || 0;
            const orderB = b.order || b.order_index || 0;
            return orderA - orderB;
        });

        sortedBlocks.forEach((block, index) => {
            const meta = block.metadata || {};
            // Use metadata row or fallback to a unique row per block
            const rowId = meta.row || meta.row_id || `row-${block.id}`;

            // Get span from metadata - preserve object for responsive support
            // Legacy: number, New: { desktop, tablet, mobile }
            let spanData = meta.span;
            if (!spanData && meta.width) {
                const w = meta.width;
                if (typeof w === 'number' && w <= 12) {
                    spanData = w;
                } else if (typeof w === 'string' && !w.includes('px') && parseInt(w) <= 12) {
                    spanData = parseInt(w);
                }
            }
            // Default to 12 if nothing found
            if (!spanData) spanData = 12;

            if (!rowsMap.has(rowId)) {
                rowsMap.set(rowId, {
                    id: rowId,
                    columns: []
                });
            }

            const row = rowsMap.get(rowId);

            // CRITICAL: Each block gets its OWN column. 1:1 mapping.
            const col = {
                id: `col-${block.id}`,
                index: row.columns.length, // Position in row
                order: block.order || block.order_index || index,
                spanData: spanData, // Preserve original (number or object)
                col_start: meta.col_start || 1, // Freeform column position
                block: block // Single block, not an array
            };
            row.columns.push(col);
        });

        // Sort rows by minimum block order
        this.rows = Array.from(rowsMap.values()).sort((a, b) => {
            return this._getMinOrder(a) - this._getMinOrder(b);
        });

        return this.rows;
    }

    _getMinOrder(row) {
        let min = Infinity;
        row.columns.forEach(col => {
            // UPDATED: col.block is now a single block, not an array
            if (col.block) {
                const order = col.block.order || col.block.order_index || 0;
                if (order < min) min = order;
            }
        });
        return min === Infinity ? 0 : min;
    }

    /**
     * Main Render Method (The Contract)
     * Clears container and builds the DOM based on specific Layout state.
     */
    render() {
        // PHASE 2: Block render during active drag
        if (this._dragState) {
            console.warn('[Render] Blocked: drag in progress');
            return;
        }

        // Defensive: Clear stale references
        this.editors.clear();

        this.container.innerHTML = '';

        const grouped = this._parseDataToLayout();
        grouped.forEach(row => {
            const rowEl = this._createRowElement(row);
            this.container.appendChild(rowEl);
        });

        // Run orphan cleanup after render
        this.cleanupOrphanBlocks();
    }

    /**
     * Create a row element with columns.
     * CRITICAL: Each column has exactly ONE block (1:1 mapping).
     */
    _createRowElement(row) {
        const rowEl = document.createElement('div');
        rowEl.className = 'content-row page-row mb-3';
        rowEl.dataset.rowId = row.id;

        // Sort columns by order
        const sortedCols = [...row.columns].sort((a, b) => a.order - b.order);

        sortedCols.forEach(col => {
            const colEl = document.createElement('div');

            // RESPONSIVE: Resolve span for current breakpoint
            let span = this._getSpanForBreakpoint(col.spanData);
            if (span < 1) span = 1;
            if (span > 12) span = 12;

            // Freeform positioning: col_start determines column position
            const colStart = col.col_start || 1;

            colEl.className = 'page-col p-2';
            // Use grid-column: start / span X for precise positioning
            colEl.style.gridColumn = `${colStart} / span ${span}`;
            colEl.style.gridRow = '1'; // Ensure all columns align to top of row
            colEl.dataset.span = span;
            colEl.dataset.colStart = colStart;
            colEl.dataset.spanData = JSON.stringify(col.spanData); // Preserve for save
            colEl.dataset.colId = col.id;

            // Render the single block for this column
            if (col.block) {
                const blockEl = this._renderBlock(col.block);
                colEl.appendChild(blockEl);

                // DEBUG GUARD: Ensure only 1 block per column
                if (colEl.querySelectorAll('.page-block').length > 1) {
                    console.error('INVALID LAYOUT: Multiple blocks in one column', col.id);
                }
            } else {
                // Empty column placeholder for drag targets
                colEl.classList.add('empty-col');
                const placeholder = document.createElement('div');
                placeholder.className = 'empty-col-placeholder';
                placeholder.innerHTML = '<span class="text-muted small">Drop here</span>';
                colEl.appendChild(placeholder);
            }

            // Add Editor-only features
            if (!this.readOnly) {
                colEl.classList.add('editor-col-active');
                // NOTE: Pointer-based drag is handled via drag handle in _attachBlockActions
            }

            rowEl.appendChild(colEl);
        });

        // NOTE: Pointer-based drag is initialized in _attachBlockActions
        // via the drag handle button, NOT here.

        return rowEl;
    }

    // [Legacy Code Removed: _initResize]
    // Consolidating all resize logic to _initBlockResize (Grid Span)

    /**
     * Initialize 2D Media Resize (Images/Videos)
     */
    // [Legacy Code Removed: _initMediaResize]
    _initMediaResize(e, wrapper, img, direction) { }

    /**
     * Helper to wrap media with 8 handles
     */
    // [Legacy Code Removed: _wrapResizableMedia]
    _wrapResizableMedia(contentEl, block) {
        return contentEl;
    }

    /**
     * Generic Resize Handler for ALL Block Types
     * Supports T/B/L/R directions (Google Sites Style).
     */
    /**
     * Generic Resize Handler for ALL Block Types
     * Supports T/B/L/R directions (Google Sites Style).
     * Uses Pointer Events for Touch Support.
     */
    _initBlockResize(e, wrapper, dir) {
        // Stop default browser behavior
        e.preventDefault();
        e.stopPropagation();

        // Target elements
        const colEl = wrapper.closest('.page-col');
        if (!colEl) return;

        const blockType = wrapper.dataset.type || 'text';
        const rowEl = colEl.closest('.content-row') || colEl.closest('.page-row');

        // State Checkpoint
        this._pushUndoState();

        // Lock UI
        const originalOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        this.isResizing = true;
        this._toggleSortables(false);

        // Initial Metrics
        const startX = e.clientX;
        const startY = e.clientY;
        const startSpan = parseInt(colEl.dataset.span || 12);
        const startCol = parseInt(colEl.dataset.colStart || 1);
        const startHeight = wrapper.offsetHeight;
        const startPaddingTop = parseInt(window.getComputedStyle(wrapper).paddingTop) || 0;

        const colWidth = rowEl.offsetWidth / 12;
        const minSpan = this._getMinSpanForBlock(blockType, colWidth);

        // Row overlay
        rowEl.classList.add('resize-active');
        wrapper.classList.add('is-resizing');

        // Analyze Direction
        // dir can be: 'n', 's', 'e', 'w', 'ne', 'nw', 'se', 'sw'
        // Mapped to: 'top', 'bottom', 'right', 'left' in previous code, but now we use raw Map values
        // Actually, in _renderBlock we mapped 'ne' -> 'ne', 'e' -> 'right'
        // Let's normalize inside the loop.

        // Check components
        const isHorizontal = ['right', 'left', 'ne', 'nw', 'se', 'sw'].includes(dir);
        const isVertical = ['top', 'bottom', 'ne', 'nw', 'se', 'sw'].includes(dir);

        const isLeft = ['left', 'nw', 'sw'].includes(dir);
        const isRight = ['right', 'ne', 'se'].includes(dir);
        const isTop = ['top', 'ne', 'nw'].includes(dir);
        const isBottom = ['bottom', 'se', 'sw'].includes(dir);

        // Pre-calculate limits
        const collisionLimitLeft = this._getCollisionLimitLeft(rowEl, colEl);
        const maxSpanRight = this._getAvailableSpan(rowEl, colEl) + startSpan;

        // Pointer Move Handler (Unified Dispatcher)
        const onPointerMove = (evt) => {
            evt.preventDefault();
            evt.stopPropagation();

            const deltaX = evt.clientX - startX;
            const deltaY = evt.clientY - startY;

            // --- HORIZONTAL RESIZE ---
            if (isHorizontal) {
                const colsChanged = Math.round(deltaX / colWidth);

                if (isRight) {
                    let newSpan = startSpan + colsChanged;
                    newSpan = Math.max(minSpan, Math.min(newSpan, maxSpanRight));

                    colEl.style.gridColumn = `${startCol} / span ${newSpan}`;
                    colEl.dataset.span = newSpan;

                    if (newSpan === minSpan && colsChanged < 0) this._showResizeHint(wrapper, 'Min width reached');
                }
                else if (isLeft) {
                    // Invert logic for left: moving left (negative delta) increases span
                    let newSpan = startSpan - colsChanged;

                    // Hard limit: cannot go left of collisionLimitLeft
                    // Max span possible = (currentEnd) - collisionLimitLeft
                    // currentEnd = startCol + startSpan (exclusive)
                    const currentEnd = startCol + startSpan;
                    const maxSpanLeft = currentEnd - collisionLimitLeft;

                    newSpan = Math.max(minSpan, Math.min(newSpan, maxSpanLeft));

                    // Calculate new start
                    const newColStart = currentEnd - newSpan;

                    colEl.style.gridColumn = `${newColStart} / span ${newSpan}`;
                    colEl.dataset.span = newSpan;
                    colEl.dataset.colStart = newColStart;

                    if (newSpan === minSpan && colsChanged > 0) this._showResizeHint(wrapper, 'Min width reached');
                    if (newColStart === collisionLimitLeft && colsChanged < 0) this._showResizeHint(wrapper, 'Collision detected');
                }
            }

            // --- VERTICAL RESIZE ---
            if (isVertical) {
                if (isBottom) {
                    const newHeight = Math.max(30, startHeight + deltaY);
                    wrapper.style.minHeight = `${newHeight}px`;
                    wrapper.dataset.minHeight = newHeight;
                }
                else if (isTop) {
                    const newPadding = Math.max(0, startPaddingTop + deltaY);
                    wrapper.style.paddingTop = `${newPadding}px`;
                    wrapper.dataset.paddingTop = newPadding;
                }
            }
        };

        const onPointerUp = async (evt) => {
            document.removeEventListener('pointermove', onPointerMove);
            document.removeEventListener('pointerup', onPointerUp);
            document.body.style.overflow = originalOverflow;
            rowEl.classList.remove('resize-active');
            this.isResizing = false;
            this._toggleSortables(true);
            wrapper.classList.remove('is-resizing');
            document.body.style.cursor = '';
            wrapper.releasePointerCapture(evt.pointerId);

            // Save current span to responsive data
            if (isHorizontal) {
                const currentSpan = parseInt(colEl.dataset.span);
                const currentColStart = parseInt(colEl.dataset.colStart) || startCol;

                // PHASE 15: Post-resize collision guard — snap back if overlap detected
                if (this._hasOverlap(rowEl, currentColStart, currentSpan, colEl)) {
                    console.warn('[Resize] Post-resize overlap detected → snapping back');
                    colEl.style.gridColumn = `${startCol} / span ${startSpan}`;
                    colEl.dataset.span = startSpan;
                    colEl.dataset.colStart = startCol;
                } else {
                    const spanData = this._normalizeSpanData(colEl.dataset.spanData);
                    spanData[this.activeBreakpoint] = currentSpan;
                    colEl.dataset.spanData = JSON.stringify(spanData);
                }
            }

            await this.saveLayout();
        };

        // Pointer Capture for consistent tracking
        wrapper.setPointerCapture(e.pointerId);

        document.addEventListener('pointermove', onPointerMove, { passive: false });
        document.addEventListener('pointerup', onPointerUp);
    }

    /*
     * YouTube Rendering
     */
    _renderYouTube(block) {
        const container = document.createElement('div');
        container.className = 'media-container position-relative';

        // Data source: block.data.url (new) or block.external_url (legacy/fallback)
        // Check both locations
        const url = block.data?.url || block.external_url || block.secure_url;
        const startTime = block.settings?.startTime || 0;

        if (url) {
            // Extract Video ID
            let videoId = null;
            try {
                // Handle different formats:
                // - youtube.com/watch?v=ID
                // - youtu.be/ID
                // - youtube.com/embed/ID
                const urlObj = new URL(url);
                if (urlObj.hostname.includes('youtube.com')) {
                    videoId = urlObj.searchParams.get('v');
                    if (!videoId && urlObj.pathname.includes('/embed/')) {
                        videoId = urlObj.pathname.split('/embed/')[1];
                    }
                } else if (urlObj.hostname.includes('youtu.be')) {
                    videoId = urlObj.pathname.substring(1);
                }
            } catch (e) {
                // Invalid URL
            }

            if (videoId) {
                const iframe = document.createElement('iframe');
                iframe.src = `https://www.youtube.com/embed/${videoId}?start=${startTime}&rel=0`;
                // Remove w-100 shadow-sm rounded to let parent handle layout
                // But keep border-0 if needed
                iframe.style.border = 'none';
                iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
                iframe.allowFullscreen = true;

                // Remove inline aspect ratio - let resize handle it
                // iframe.style.aspectRatio = '16/9';

                container.appendChild(iframe);
            } else {
                container.innerHTML = `
                    <div class="alert alert-warning d-flex align-items-center mb-0">
                        <i class="fab fa-youtube fa-2x me-3 text-danger"></i>
                        <div>
                            <strong>Invalid YouTube URL</strong><br/>
                            <small class="text-muted">${url}</small>
                        </div>
                    </div>`;
            }

        } else {
            // Placeholder state
            container.innerHTML = `
                <div class="p-5 bg-light rounded border border-dashed text-center clickable-placeholder">
                    <i class="fab fa-youtube fa-3x text-danger mb-3"></i>
                    <h5 class="text-muted">Add YouTube Video</h5>
                    <button class="btn btn-outline-primary btn-sm mt-2">Enter URL</button>
                </div>
            `;
            // Click to open settings
            container.onclick = (e) => {
                // Only if not dragging?
                if (!document.body.classList.contains('is-dragging-block') && !this.readOnly) {
                    this._openSettings(block);
                }
            };
        }

        return container;
    }

    _renderBlock(block) {
        // Defensive: Skip invalid blocks
        if (!block || !block.type) {
            console.warn('Skipping invalid block rendering:', block);
            return null;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'page-block mb-3 position-relative';
        wrapper.dataset.id = block.id; // Vital for SortableJS
        wrapper.dataset.contentId = block.id; // Vital for _saveLayout
        wrapper.dataset.type = block.type;

        // GRID LOGIC: Apply Column Span (Default 12)
        const meta = block.metadata || {};
        const span = meta.span || 12;
        wrapper.style.gridColumn = `span ${span}`;
        wrapper.style.setProperty('--block-span', span);
        wrapper.dataset.span = span;

        // GRID LOGIC: Apply Height & Padding (Vertical Resize)
        let minHeight = meta.minHeight;

        // Fix for Read-Only PDF: Ensure visible default height if not resized
        if (!minHeight && block.type === 'pdf') {
            minHeight = 600; // Default PDF height
        }

        if (minHeight) wrapper.style.minHeight = `${minHeight}px`;
        if (meta.paddingTop) wrapper.style.paddingTop = `${meta.paddingTop}px`;

        wrapper.dataset.minHeight = minHeight || '';
        wrapper.dataset.paddingTop = meta.paddingTop || '';

        if (!this.readOnly) {
            wrapper.classList.add('editable-block');
            wrapper.classList.add('is-resizable');

            const createHandle = (direction) => {
                const h = document.createElement('div');
                const dirMap = {
                    'e': 'right', 'w': 'left', 'n': 'top', 's': 'bottom',
                    'ne': 'ne', 'nw': 'nw', 'se': 'se', 'sw': 'sw'
                };
                const cursorMap = {
                    'e': 'ew-resize', 'w': 'ew-resize', 'n': 'ns-resize', 's': 'ns-resize',
                    'ne': 'nesw-resize', 'nw': 'nwse-resize', 'se': 'nwse-resize', 'sw': 'nesw-resize'
                };

                h.className = `resize-handle ${dirMap[direction]}`;
                h.style.cursor = cursorMap[direction];

                h.addEventListener('pointerdown', (e) => {
                    e.stopPropagation();
                    e.preventDefault();
                    this._initBlockResize(e, wrapper, dirMap[direction]);
                });
                return h;
            };

            ['n', 's', 'w', 'e', 'ne', 'nw', 'se', 'sw'].forEach(dir => wrapper.appendChild(createHandle(dir)));
        }

        // Render Content based on Type via Registry
        let contentEl;
        const renderer = this.blockRegistry.get(block.type);
        if (renderer) {
            contentEl = renderer(block);
        } else {
            console.warn(`No renderer found for block type: ${block.type}`);
            contentEl = this._renderUnknownBlock(block);
        }

        // Legacy fallback for old 'text' type if not mapped correctly (safety)
        if (!contentEl && block.type === 'text') {
            contentEl = this._renderTextBlock(block);
        }

        wrapper.appendChild(contentEl);

        // Editor: Hover Actions
        if (!this.readOnly) {
            this._attachBlockActions(wrapper, block);
        }

        return wrapper;
    }

    /**
     * Managed Pending (Uploading) Blocks
     */
    addPendingBlock(type, fileName, correlationId) {
        console.log(`[PageBuilder] Adding pending block: ${fileName} (${correlationId})`);

        // 1. Store in internal state
        this.pendingBlocks.set(correlationId, { type, fileName });

        // 2. Create Placeholder
        const placeholder = this._createPendingPlaceholder(type, fileName, correlationId);

        // 3. Deterministic Placement: Append to last row or create one
        let lastRow = this.container.lastElementChild;
        if (!lastRow || !lastRow.classList.contains('page-row')) {
            lastRow = this._createNewRow();
            this.container.appendChild(lastRow);
        }

        // Create a new column for this block (1 block = 1 col)
        const colEl = document.createElement('div');
        colEl.className = 'page-col p-2 editor-col-active';
        colEl.style.gridColumn = '1 / span 12'; // Default to full width for pending
        colEl.style.gridRow = '1';
        colEl.appendChild(placeholder);

        lastRow.appendChild(colEl);
        return placeholder;
    }

    updatePendingBlock(correlationId, blockData) {
        const placeholder = this.container.querySelector(`.page-block[data-correlation-id="${correlationId}"]`);
        if (!placeholder) {
            console.warn(`[PageBuilder] updatePendingBlock: Placeholder not found for ${correlationId}`);
            return;
        }

        this.pendingBlocks.delete(correlationId);

        const realBlock = this._renderBlock(blockData);
        if (realBlock) {
            // FULL NODE REPLACEMENT - Ghost Shield
            placeholder.replaceWith(realBlock);
            console.log(`[PageBuilder] Pending block ${correlationId} updated to real ID ${blockData.id}`);
        } else {
            placeholder.remove();
        }

        this.cleanupOrphanBlocks();
    }

    removePendingBlock(correlationId) {
        this.pendingBlocks.delete(correlationId);
        const placeholder = this.container.querySelector(`.page-block[data-correlation-id="${correlationId}"]`);
        if (placeholder) {
            placeholder.remove();
            console.log(`[PageBuilder] Pending block ${correlationId} removed.`);
        }
        this.cleanupOrphanBlocks();
    }

    _createPendingPlaceholder(type, fileName, correlationId) {
        const wrapper = document.createElement('div');
        wrapper.className = 'page-block pending-block mb-3 position-relative p-4 bg-light border border-dashed rounded text-center';
        wrapper.dataset.correlationId = correlationId;
        wrapper.dataset.type = type;

        wrapper.innerHTML = `
            <div class="pending-content">
                <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem;">
                    <span class="visually-hidden">Uploading...</span>
                </div>
                <div class="file-name fw-bold text-truncate mb-2" title="${fileName}">${fileName}</div>
                <div class="upload-progress-container mb-2">
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" style="width: 0%"></div>
                    </div>
                </div>
                <div class="pending-status small text-muted">Preparing upload...</div>
            </div>
        `;
        return wrapper;
    }

    _updatePlaceholderProgress(correlationId, percent, status) {
        const placeholder = this.container.querySelector(`.page-block[data-correlation-id="${correlationId}"]`);
        if (!placeholder) return;

        const progressBar = placeholder.querySelector('.progress-bar');
        const statusEl = placeholder.querySelector('.pending-status');

        if (progressBar) progressBar.style.width = `${percent}%`;
        if (statusEl && status) statusEl.textContent = status;
    }

    cleanupOrphanBlocks() {
        // Remove .page-block if they lack both real ID and correlation ID
        this.container.querySelectorAll('.page-block').forEach(el => {
            if (!el.dataset.id && !el.dataset.correlationId) {
                console.log('[PageBuilder] Cleaning up orphan block node');
                el.remove();
            }
        });

        // Cleanup empty columns (optional but ensures clean grid)
        this.container.querySelectorAll('.page-col').forEach(el => {
            if (el.children.length === 0) el.remove();
        });

        // Cleanup empty rows
        this.container.querySelectorAll('.page-row').forEach(el => {
            if (el.children.length === 0) el.remove();
        });
    }

    _createNewRow() {
        const rowId = `row-${Date.now()}`;
        const rowEl = document.createElement('div');
        rowEl.className = 'content-row page-row mb-3';
        rowEl.dataset.rowId = rowId;
        return rowEl;
    }

    // --- Specific Renderers ---

    _renderTextBlock(block) {
        const el = document.createElement('div');
        const editorId = `editor-${block.id}`;
        el.id = editorId;

        // Editor Mode: Initialize EditorJS
        if (!this.readOnly) {
            el.className = 'prose-content';
            setTimeout(() => this._initEditorJs(block, editorId), 0);
        } else {
            // Reader Mode: Render pre-rendered HTML from server
            el.className = 'prose-content read-only w-100 mw-100';
            el.style.maxWidth = '100%';

            // Detect if content is valid EditorJS JSON
            let isJson = false;
            try {
                if (typeof block.content === 'object' && block.content !== null) {
                    isJson = true; // Already an object (though usually it's a string from DB)
                } else if (typeof block.content === 'string' && (block.content.trim().startsWith('{') || block.content.trim().startsWith('['))) {
                    const parsed = JSON.parse(block.content);
                    if (parsed && typeof parsed === 'object' && (parsed.blocks || parsed.time)) {
                        isJson = true;
                    }
                }
            } catch (e) {
                // Not JSON
            }

            // Use server-side rendered content if available (preferred)
            // But only if it's NOT just the raw JSON string again (sanity check)
            const rendered = block.rendered_content;
            let useHtml = false;

            if (rendered && typeof rendered === 'string' && rendered.length > 0) {
                useHtml = true;
            } else if (!isJson) {
                // If not JSON, assume legacy HTML
                useHtml = true;
            }

            if (useHtml) {
                el.innerHTML = rendered || block.content || '';
            } else {
                // Fallback: Initialize EditorJS in read-only mode for JSON content
                const editorContainer = document.createElement('div');
                editorContainer.id = editorId; // Move ID to inner container
                el.removeAttribute('id'); // Remove ID from wrapper

                // CRITICAL: Force EditorJS default styles to expand
                const styleOverride = document.createElement('style');
                styleOverride.innerHTML = `
                    #${editorId} .codex-editor__redactor {
                        margin-right: 0 !important;
                        margin-left: 0 !important;
                        max-width: 100% !important;
                        padding-bottom: 0 !important;
                    }
                    #${editorId} .ce-block__content {
                        max-width: 100% !important;
                    }
                `;
                el.appendChild(styleOverride);
                el.appendChild(editorContainer);

                setTimeout(() => this._initEditorJs(block, editorId, true), 0);
            }
        }
        return el;
        return el;
    }

    _initEditorJs(block, holderId, readOnly = false) {
        let data = {};
        try {
            data = typeof block.content === 'string' ? JSON.parse(block.content) : block.content;
        } catch (e) {
            console.warn('Invalid JSON content for block', block.id);
        }

        const manager = new EditorManager(holderId, {
            placeholder: 'Type something...',
            minHeight: 10,
            autofocus: false,
            readOnly: readOnly,
            onChange: async () => {
                if (!readOnly) this._handleTextChange(block.id, manager);
            }
        });

        manager.init(data).then(() => {
            this.editors.set(block.id, manager);
        });
    }

    _renderImageBlock(block) {
        const container = document.createElement('div');
        // Clean container - handled by parent .page-block styles
        container.className = 'w-100 h-100';

        const url = block.secure_url || block.signed_url || block.file_path;
        if (url) {
            const img = document.createElement('img');
            img.src = url;
            img.loading = 'lazy';
            // No specific classes needed, parent CSS handles it

            container.appendChild(img);
        } else {
            // Placeholder...
            // Placeholder...
            container.innerHTML = `<div class="p-5 bg-light text-center opacity-25"></div>`;
        }
        return container;
    }

    _renderVideoBlock(block) {
        const container = document.createElement('div');
        container.className = 'w-100 h-100';

        const url = block.secure_url || block.signed_url || block.external_url;

        if (url) {
            const video = document.createElement('video');
            video.src = url;
            video.controls = true;
            // SECURE: Prevent download button and context menu
            video.setAttribute('controlsList', 'nodownload');
            video.oncontextmenu = (e) => e.preventDefault();

            video.preload = 'metadata';
            container.appendChild(video);

            // SECURE: Prevent keyboard shortcuts for download
            video.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && (e.key === 's' || e.key === 'S')) {
                    e.preventDefault();
                }
            });
        } else {
            // Placeholder...
            // Placeholder...
            container.innerHTML = `<div class="p-5 bg-light text-center opacity-25"></div>`;
        }

        return container;
    }

    _renderPdfBlock(block) {
        const container = document.createElement('div');
        container.className = 'w-100 h-100';

        const url = block.secure_url || block.signed_url || block.file_path;
        // ... (PDF logic same, but remove container classes and _wrapResizableMedia)
        // For brevity in diff, ensuring structure matches

        const viewerUrl = block.secure_viewer_url;
        const directUrl = block.secure_url || block.signed_url || block.file_path;

        if (viewerUrl) {
            const iframe = document.createElement('iframe');
            iframe.src = viewerUrl;
            // iframe.className = 'w-100 h-100 border-0 rounded shadow-sm';
            iframe.style.border = 'none';
            iframe.style.width = '100%';
            iframe.style.height = '100%';

            iframe.setAttribute('allow', 'fullscreen');
            // iframe.style.minHeight = '600px'; // REMOVED

            container.appendChild(iframe);

            // Add "Open in New Tab" button
            const openBtn = document.createElement('a');
            openBtn.href = viewerUrl;
            openBtn.target = '_blank';
            openBtn.className = 'btn btn-sm btn-light position-absolute top-0 end-0 m-2 shadow-sm border';
            openBtn.style.zIndex = '5';
            openBtn.innerHTML = '<i class="fas fa-external-link-alt me-1"></i> Open';
            container.appendChild(openBtn);

        } else if (directUrl) {
            const obj = document.createElement('object');
            obj.data = directUrl;
            obj.type = 'application/pdf';
            obj.style.width = '100%';
            obj.style.height = '100%';
            // obj.style.height = '600px'; // REMOVED

            obj.innerHTML = `<p>Unable to display PDF. <a href="${directUrl}" target="_blank">Download</a> instead.</p>`;
            container.appendChild(obj);
        } else {
            container.innerHTML = `<div class="p-5 bg-light text-center opacity-25"></div>`;
        }
        return container;
    }

    _renderAudioBlock(block) {
        const container = document.createElement('div');
        container.className = 'media-container p-3 bg-light rounded border d-flex align-items-center gap-3';

        const url = block.secure_url || block.signed_url;

        if (url) {
            const icon = document.createElement('i');
            icon.className = 'fas fa-music text-primary fa-lg';
            container.appendChild(icon);

            const audio = document.createElement('audio');
            audio.src = url;
            audio.controls = true;
            audio.className = 'flex-grow-1';
            container.appendChild(audio);
        } else {
            container.innerHTML = 'Audio source missing';
        }
        return container;
    }

    _renderUnknownBlock(block) {
        const el = document.createElement('div');
        el.className = 'alert alert-danger mb-0';
        el.textContent = `Unknown block type: ${block.type}`;
        return el;
    }

    /**
     * Handle block reordering/moving - sets col_start for freeform positioning
     */
    async _handleBlockMove(evt) {
        const colEl = evt.item;
        const blockId = colEl.dataset.colId;

        // Get target column from last mouse position
        const targetCol = this._lastTargetColumn || 1;

        // Find and update block metadata
        const block = this.initialData.find(b => b.id == blockId);
        if (block) {
            if (!block.metadata) block.metadata = {};
            block.metadata.col_start = targetCol;

            // Update DOM immediately
            const span = block.metadata.span || 12;
            colEl.style.gridColumn = `${targetCol} / span ${span}`;
            colEl.dataset.colStart = targetCol;

            console.log(`Block ${blockId} moved to column ${targetCol}, span ${span}`);
        }

        // Reset tracking
        this._lastTargetColumn = null;
        this._lastTargetRow = null;

        await this.saveLayout();
    }

    // --- Editor Interactions ---

    _attachBlockActions(wrapper, block) {
        const actions = document.createElement('div');
        // CSS handles hover visibility via opacity
        actions.className = 'block-actions position-absolute top-0 end-0 p-1';
        actions.style.zIndex = '25';

        // Buttons
        const btnClass = 'btn btn-sm btn-light shadow-sm me-1';

        // Only show Settings button for supported blocks
        const hasSettings = ['spacer', 'divider', 'youtube', 'carousel', 'social'].includes(block.type);

        actions.innerHTML = `
            <button class="${btnClass} drag-handle" title="Drag"><i class="fas fa-grip-vertical"></i></button>
            ${hasSettings ? `<button class="${btnClass} settings-btn" title="Settings"><i class="fas fa-cog"></i></button>` : ''}
            <button class="${btnClass} edit-btn" title="Edit"><i class="fas fa-pen"></i></button>
            <button class="${btnClass} text-danger delete-btn" title="Delete"><i class="fas fa-trash"></i></button>
        `;

        wrapper.appendChild(actions);

        // Edit Handler
        const isUtilityBlock = ['youtube', 'spacer', 'divider', 'carousel', 'social'].includes(block.type);

        if (isUtilityBlock) {
            // Redirect Edit to Settings for Utility Blocks
            actions.querySelector('.edit-btn').onclick = (e) => {
                e.stopPropagation();
                this._openSettings(block);
            };
        } else {
            // Default Edit behavior (Content Modal)
            actions.querySelector('.edit-btn').onclick = (e) => {
                e.stopPropagation();
                this.onEditBlock(block.id);
            };
        }

        // Settings Handler (Gear Icon)
        if (hasSettings) {
            actions.querySelector('.settings-btn').onclick = (e) => {
                e.stopPropagation();
                this._openSettings(block);
            };
        }

        // Delete Handler
        actions.querySelector('.delete-btn').onclick = (e) => {
            e.stopPropagation();
            const title = block.data?.title || block.type || 'content';
            this.onDeleteBlock(block.id, title);
        };

        // POINTER-BASED DRAG (Google Sites style)
        const dragHandle = actions.querySelector('.drag-handle');
        dragHandle.addEventListener('pointerdown', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this._startPointerDrag(e, wrapper, block);
        });
    }

    // ===== GOOGLE SITES-STYLE DRAG ENGINE =====

    /**
     * Create invisible insert zones between rows for new row creation
     */
    _createInsertZones() {
        const container = this.container;
        const rows = container.querySelectorAll('.page-row');

        // Remove existing zones
        container.querySelectorAll('.row-insert-zone').forEach(z => z.remove());

        // Create zone before first row
        if (rows.length > 0) {
            this._createSingleInsertZone(container, 0, rows[0]);
        }

        // Create zones between and after rows
        rows.forEach((row, index) => {
            this._createSingleInsertZone(container, index + 1, row.nextElementSibling);
        });
    }

    /**
     * Create a single insert zone
     */
    _createSingleInsertZone(container, insertIndex, beforeElement) {
        const zone = document.createElement('div');
        zone.className = 'row-insert-zone';
        zone.dataset.insertIndex = insertIndex;

        if (beforeElement) {
            container.insertBefore(zone, beforeElement);
        } else {
            container.appendChild(zone);
        }
    }

    /**
     * Start pointer-based drag (Google Sites style)
     * @param {PointerEvent} e - The pointerdown event
     * @param {HTMLElement} wrapper - The .page-block wrapper element
     * @param {Object} block - Block data object
     */
    _startPointerDrag(e, wrapper, block) {
        // Find the column containing this block
        const colEl = wrapper.closest('.page-col');
        if (!colEl) return;

        console.log('[Pointer Drag] Started drag for block:', block.id);

        // Save undo state
        this._pushUndoState();

        // Store drag state
        this._dragState = {
            blockId: block.id,
            block: block,
            sourceCol: colEl,
            sourceRow: colEl.closest('.page-row'),
            startX: e.clientX,
            startY: e.clientY,
            offsetX: 0,
            offsetY: 0,
            dropMode: null,           // 'new-row' or 'in-row'
            dropTarget: null,         // Insert zone or row element
            targetColStart: 1,        // Target column position (1-12)
            capturingElement: e.target,  // PHASE 1: Store for cleanup
            pointerId: e.pointerId,      // PHASE 1: Pointer ID for release
            // PHASE 15: Store original position for revert-on-overlap
            originalColStart: parseInt(colEl.dataset.colStart) || 1,
            originalSpan: parseInt(colEl.dataset.span) || 12,
            originalRow: colEl.closest('.page-row')
        };

        // Create insert zones between rows for new row creation
        this._createInsertZones();

        // SLOT SYSTEM: Create column drop slots for all existing rows
        this._createColumnSlots();

        // Add body class to activate drag CSS (slot visibility, pointer-events)
        document.body.classList.add('is-pointer-dragging');
        console.log('[DEBUG] Body class added. Slots with class:', document.querySelectorAll('.column-drop-slot').length);

        // Create floating ghost that follows cursor
        // CRITICAL: Capture exact pixel dimensions BEFORE any layout changes
        const rect = colEl.getBoundingClientRect();
        this._dragGhost = colEl.cloneNode(true);
        this._dragGhost.classList.add('pointer-drag-ghost');

        // PIXEL-LOCK: Freeze dimensions and KILL all layout influence
        this._dragGhost.style.cssText = `
            position: fixed !important;
            left: ${rect.left}px;
            top: ${rect.top}px;
            width: ${rect.width}px !important;
            height: ${rect.height}px !important;
            max-width: none !important;
            min-width: unset !important;
            max-height: none !important;
            min-height: unset !important;
            z-index: 9999;
            pointer-events: none;
            opacity: 0.85;
            transform: rotate(2deg);
            box-shadow: 0 12px 32px rgba(0,0,0,0.25);
            background: white;
            border-radius: 8px;
            grid-column: unset !important;
            grid-row: unset !important;
            flex: none !important;
            display: block !important;
            overflow: hidden;
        `;
        document.body.appendChild(this._dragGhost);

        // Calculate offset from cursor to element top-left
        this._dragState.offsetX = e.clientX - rect.left;
        this._dragState.offsetY = e.clientY - rect.top;

        // Dim the source element
        colEl.style.opacity = '0.3';

        // Add body class for styling
        document.body.classList.add('is-pointer-dragging');

        // Capture pointer for reliable tracking (stored in _dragState for cleanup)
        e.target.setPointerCapture(e.pointerId);

        // Bind move and up handlers
        this._boundPointerMove = this._onPointerMove.bind(this);
        this._boundPointerUp = this._onPointerUp.bind(this);

        document.addEventListener('pointermove', this._boundPointerMove);
        document.addEventListener('pointerup', this._boundPointerUp);
    }

    /**
     * Handle pointer move during drag
     * @param {PointerEvent} e
     */
    _onPointerMove(e) {
        if (!this._dragGhost || !this._dragState) return;

        // Track current mouse position for insertion indicator
        this._dragState.currentX = e.clientX;
        this._dragState.currentY = e.clientY;

        // Move ghost to follow cursor
        const x = e.clientX - this._dragState.offsetX;
        const y = e.clientY - this._dragState.offsetY;
        this._dragGhost.style.left = `${x}px`;
        this._dragGhost.style.top = `${y}px`;

        // Detect element underneath cursor
        // Note: ghost has pointer-events: none, so no need to hide it
        const target = document.elementFromPoint(e.clientX, e.clientY);

        // DEBUG: Always log what elementFromPoint returns
        if (target) {
            console.log('[Hit Detection]', {
                tag: target.tagName,
                class: target.className,
                isSlot: target.classList?.contains('column-drop-slot'),
                isZone: target.classList?.contains('row-insert-zone')
            });
        }

        const dropSlot = target?.closest('.column-drop-slot');
        const insertZone = target?.closest('.row-insert-zone');

        // Checklist Verification Logs and Legacy Logs
        console.log('[DEBUG] Hover slot:', dropSlot?.dataset.slot);
        if (insertZone) console.log('[Hit Detection] âœ“ ZONE FOUND:', insertZone.dataset.insertIndex);

        // Highlight potential drop targets
        this._updateDropHighlight(target, e.clientX);
    }

    /**
     * Handle pointer up - execute drop based on dropMode
     * @param {PointerEvent} e
     */
    _onPointerUp(e) {
        // Remove listeners
        document.removeEventListener('pointermove', this._boundPointerMove);
        document.removeEventListener('pointerup', this._boundPointerUp);

        if (!this._dragState) {
            this._cleanupDrag();
            return;
        }

        const { dropMode, dropTarget } = this._dragState;

        // Checklist Verification Log
        console.log('[DROP]', {
            mode: dropMode,
            slot: dropTarget?.dataset?.slot
        });

        // PHASE 2: Atomic drop execution (all-or-nothing)
        try {
            // Execute drop based on mode
            if (dropMode === 'new-row' && dropTarget) {
                // Drop into insert zone
                this._executeNewRowDrop(dropTarget);
            } else if ((dropMode === 'into-slot' || dropMode === 'reorder') && dropTarget) {
                // PHASE 15: Collision gate — if preview showed overlap, revert to original position
                if (this._dragState.isBlocked) {
                    console.log('[Drop] Collision detected → reverting to original position');
                    this._revertToOriginalPosition();
                } else {
                    // Drop into column slot - extract row from slot element
                    const targetRow = dropTarget.closest('.page-row');
                    // Extract slot index (1-12) from DragState (Unified Resolver)
                    const slotIndex = this._dragState.targetSlot || 1;
                    this._executeInRowDrop(targetRow, slotIndex);
                }
            } else {
                // dropMode is null or invalid â†’ cancel drop
                console.log('[Drop] Cancelled: no valid drop zone');
                return; // GUARD: Do not save layout if no drop occurred
            }
        } catch (error) {
            console.error('[Drop] Failed:', error);
            // On error, cleanup will restore visual state
        } finally {
            // Always cleanup, even if drop failed
            this._cleanupDrag();
        }
    }

    /**
     * Update drop highlight during drag â€” SLOT-BASED DETECTION
     * Only .column-drop-slot or .row-insert-zone are valid targets
     */
    _updateDropHighlight(target, clientX) {
        // Clear all previous highlights
        this._clearAllHighlights();

        if (!target || !this._dragState) return;

        // Check for insert zone (between rows) â€” creates new row
        const insertZone = target.closest('.row-insert-zone');

        // Check for row (any child: slot, block, empty space)
        const row = target.closest('.page-row');

        // GOLDEN RULE: Ghost preview must use the EXACT SAME resolver as drop execution.
        // Both use _resolveDropSlot() - no exceptions, no drift.
        if (row && !insertZone) {
            // MODE: Drop into row

            // UNIFIED RESOLVER: always calculate column index from cursor X
            const slotIndex = this._resolveDropSlot(row, clientX);

            // Determine if same row or cross-row
            if (row === this._dragState.sourceRow) {
                this._dragState.dropMode = 'reorder';
            } else {
                this._dragState.dropMode = 'into-slot';
            }

            this._dragState.dropTarget = row;
            this._dragState.targetSlot = slotIndex;

            console.log('[Drag] Mode:', this._dragState.dropMode, 'slot:', slotIndex);

            // Update Preview (Ghost Block)
            this._updateDropPreview(row, slotIndex);

        } else if (insertZone) {
            // MODE: New Row Creation
            insertZone.classList.add('active');
            this._dragState.dropMode = 'new-row';
            this._dragState.dropTarget = insertZone;
            console.log('[Drag] Mode: new-row at index', insertZone.dataset.insertIndex);

            // Clear preview if moving to zone
            this._clearDropPreview();
        } else {
            // No valid target â†’ will cancel on drop
            this._dragState.dropMode = null;
            this._dragState.dropTarget = null;
            this._clearDropPreview();
        }
    }

    /**
     * Resolve the target column index based on raw cursor position.
     * Single Source of Truth for both Preview and Execution.
     */
    _resolveDropSlot(row, clientX) {
        const rowRect = row.getBoundingClientRect();
        // 12 columns
        const colWidth = rowRect.width / 12;
        // Relative X
        const relativeX = clientX - rowRect.left;

        let slotIndex = Math.floor(relativeX / colWidth) + 1;

        // Clamp to valid range
        if (slotIndex < 1) slotIndex = 1;
        if (slotIndex > 12) slotIndex = 12;

        return slotIndex;
    }

    /**
     * Clear all drag highlights
     */
    _clearAllHighlights() {
        // Clear row highlights
        document.querySelectorAll('.pointer-drop-target').forEach(el => {
            el.classList.remove('pointer-drop-target');
        });

        // Clear insert zone highlights
        document.querySelectorAll('.row-insert-zone.active').forEach(el => {
            el.classList.remove('active');
        });

        // Clear column slot highlights
        document.querySelectorAll('.column-drop-slot.active').forEach(el => {
            el.classList.remove('active');
        });

        this._clearDropPreview();
    }

    /**
     * Clear drop preview ghost
     */
    _clearDropPreview() {
        document.querySelectorAll('.drop-preview').forEach(el => el.remove());
    }

    /**
     * Helper: Calculate available span and collisions.
     * Shared logic for preview and executing drop.
     */
    _calculateAvailableSpan(targetRow, startSlot, requestedSpan, sourceCol) {
        const rowRect = targetRow.getBoundingClientRect();
        const colWidth = rowRect.width / 12;

        const refCols = Array.from(targetRow.querySelectorAll(':scope > .page-col')).filter(c => c !== sourceCol && !c.classList.contains('drop-preview'));

        const occupied = [];
        refCols.forEach(col => {
            const rect = col.getBoundingClientRect();
            // Calculate start/span relative to row
            // Note: rect.left might be sub-pixel, Math.round is safe
            const offset = rect.left - rowRect.left;
            let start = Math.round(offset / colWidth) + 1;
            let span = Math.round(rect.width / colWidth);

            // Safety
            if (start < 1) start = 1;
            if (span < 1) span = 1;

            occupied.push({ start, end: start + span, el: col });
        });

        // Determine New Block Metrics
        let newSpan = requestedSpan;
        if (startSlot + newSpan > 13) {
            newSpan = 13 - startSlot;
        }

        // Collision Detection (Shrink to fit)
        let available = newSpan;

        occupied.forEach(block => {
            // Check if block overlaps our proposed range
            if (block.start > startSlot && block.start < startSlot + available) {
                // Obstacle! Shrink available space
                available = block.start - startSlot;
            }
            // Check if dropped inside (immediate collision)
            if (block.start <= startSlot && block.end > startSlot) {
                available = 0;
            }
        });

        return { available, occupied };
    }

    /**
     * Render or update drop preview (Ghost Block)
     */
    _updateDropPreview(targetRow, slotIndex) {
        let preview = targetRow.querySelector('.drop-preview');
        if (!preview) {
            preview = document.createElement('div');
            preview.className = 'drop-preview';
            preview.style.gridRow = '1'; // Ensure it stays in the content row
            targetRow.appendChild(preview);
        }

        const { sourceCol } = this._dragState;
        const requestedSpan = parseInt(sourceCol.dataset.span || 4);

        // Match height of the source column (the block being dragged)
        const sourceRect = sourceCol.getBoundingClientRect();
        preview.style.height = `${sourceRect.height}px`;

        const { available } = this._calculateAvailableSpan(targetRow, slotIndex, requestedSpan, sourceCol);

        if (available <= 0) {
            preview.classList.add('is-invalid');
            preview.style.gridColumn = `${slotIndex} / span ${requestedSpan}`;
            preview.style.display = 'block';
            // PHASE 15: Set blocking flag so _onPointerUp knows to redirect
            this._dragState.isBlocked = true;
        } else {
            preview.classList.remove('is-invalid');
            preview.style.gridColumn = `${slotIndex} / span ${available}`;
            preview.style.display = 'block';
            // PHASE 15: Clear blocking flag — valid drop zone
            this._dragState.isBlocked = false;
        }
    }



    /**
     * SLOT SYSTEM: Create column drop slots for all rows
     * 6 fixed slots per row, visible only during drag
     */
    _createColumnSlots() {
        console.log('[DEBUG] _createColumnSlots called, container:', this.container);
        const rows = this.container.querySelectorAll('.page-row');
        console.log('[DEBUG] Found rows:', rows.length);
        rows.forEach(row => {
            // Idempotency check: Don't add slots if they already exist
            if (row.querySelector('.column-drop-slot')) return;

            // Get row's actual pixel height for explicit slot dimensions
            const rowHeight = row.getBoundingClientRect().height;
            console.log('[DEBUG] Row height:', rowHeight, 'px');

            // Check if row is empty
            const existingBlocks = row.querySelectorAll('.page-col').length;

            if (existingBlocks === 0) {
                // Empty row: create single full-width slot
                const slot = document.createElement('div');
                slot.className = 'column-drop-slot empty-row-slot';
                slot.dataset.slot = '1';
                slot.style.width = '100%';
                slot.style.height = `${rowHeight}px`;
                slot.style.left = '0';
                row.appendChild(slot);
            } else {
                // Row has content: create 12 slots for full grid targeting
                for (let i = 1; i <= 12; i++) {
                    const slot = document.createElement('div');
                    slot.className = 'column-drop-slot';
                    slot.dataset.slot = i;
                    slot.style.height = `${rowHeight}px`;
                    row.appendChild(slot);
                }
            }
        });
        // Verify slots were added to DOM
        const totalSlots = document.querySelectorAll('.column-drop-slot').length;
        console.log('[DEBUG] Total slots in DOM after creation:', totalSlots);
    }

    /**
     * Execute drop: Create NEW ROW at insert zone position.
     * ROW-ISOLATED: Only mutates sourceRow and the newly created row.
     */
    _executeNewRowDrop(insertZone) {
        const { sourceCol, sourceRow } = this._dragState;
        const insertIndex = parseInt(insertZone.dataset.insertIndex);

        console.log('[Drop] Creating new row at index:', insertIndex);

        // Create new row element
        const newRow = document.createElement('div');
        newRow.className = 'content-row page-row mb-3';
        newRow.dataset.rowId = `row-${Date.now()}`;

        // Insert at correct position
        const existingRows = this.container.querySelectorAll('.page-row');
        if (insertIndex < existingRows.length) {
            this.container.insertBefore(newRow, existingRows[insertIndex]);
        } else {
            this.container.appendChild(newRow);
        }

        // Move block to new row at full width
        newRow.appendChild(sourceCol);
        sourceCol.style.gridColumn = '1 / span 12';
        sourceCol.dataset.span = 12;
        sourceCol.dataset.colStart = 1;

        // Fix source row (if different â€” it always is for new-row drops)
        if (sourceRow) {
            const remainingCols = sourceRow.querySelectorAll(':scope > .page-col');
            if (remainingCols.length === 0) {
                sourceRow.remove();
                console.log('[Drop] Source row empty â†’ removed');
            } else {
                this._applyEqualSpans(sourceRow);
                console.log('[Drop] Source row rebalanced (' + remainingCols.length + ' blocks)');
            }
        }

        // Save
        this._normalizeOrderIndexes();
        // Save
        this._normalizeOrderIndexes();
        this.saveLayout(true); // Force save (drag is finishing)
        console.log('[Drop] âœ“ New row created and saved');
    }

    /**
     * Execute drop: Place block into an existing row.
     * ROW-ISOLATED: Only mutates sourceRow and targetRow. Never touches other rows.
     * 
     * INVARIANT: A drag operation may ONLY mutate source row + target row.
     * 
     * SLOT SYSTEM: Now accepts targetRow directly (slot resolution done in _onPointerUp)
     */
    /**
     * Execute drop: Place block into an existing row at EXACT SLOT (1-12).
     * ABSOLUTE GRID LOGIC:
     * 1. Measure all existing blocks to know occupied columns.
     * 2. Place new block at dropSlotIndex.
     * 3. Shrink new block if it overlaps existing content.
     */
    _executeInRowDrop(targetRow, dropSlotIndex = 1) { // Default to 1
        const { sourceCol, sourceRow } = this._dragState;

        console.log('[Drop] Executing at Slot ' + dropSlotIndex);

        // 1. Measure Existing Layout
        // We use getBoundingClientRect because CSS Grid auto-placement might not set dataset.colStart
        const rowRect = targetRow.getBoundingClientRect();
        const colWidth = rowRect.width / 12;

        const refCols = Array.from(targetRow.querySelectorAll(':scope > .page-col')).filter(c => c !== sourceCol);

        const occupied = [];
        refCols.forEach(col => {
            const rect = col.getBoundingClientRect();
            // Calculate start/span relative to row
            // Note: rect.left might be sub-pixel, Math.round is safe
            const offset = rect.left - rowRect.left;
            let start = Math.round(offset / colWidth) + 1;
            let span = Math.round(rect.width / colWidth);

            // Safety
            if (start < 1) start = 1;
            if (span < 1) span = 1;

            // Save explicit position for future reference (and collision logic)
            // We force explicit grid column now to "lock" it
            col.style.gridColumn = `${start} / span ${span}`;
            col.dataset.colStart = start;
            col.dataset.span = span;

            occupied.push({ start, end: start + span, el: col });
        });

        // 2. Determine New Block Metrics
        let start = dropSlotIndex;
        // Default span 4 for new blocks (User: "Drop any block anywhere")
        // If moving existing block, keep its span?
        let newSpan = parseInt(sourceCol.dataset.span || 4);

        // Clip to row end (13)
        if (start + newSpan > 13) {
            newSpan = 13 - start;
        }

        // 3. Collision Detection (Shrink to fit)
        // Find nearest obstacle to the right
        let available = newSpan;

        occupied.forEach(block => {
            // Check if block overlaps our proposed range
            if (block.start > start && block.start < start + available) {
                // Obstacle! Shrink available space
                available = block.start - start;
            }
            // Check if dropped inside
            if (block.start <= start && block.end > start) {
                available = 0; // Collision
            }
        });

        // PHASE 15: If no space available, revert to original position
        if (available <= 0) {
            console.warn('[Drop] No space at Slot ' + start + ' → reverting to original position');
            this._revertToOriginalPosition();
            return; // EXIT: block returned to original position
        }

        // 4. Apply to New Block
        sourceCol.style.gridColumn = `${start} / span ${available}`;
        sourceCol.style.gridRow = '1'; // Force alignment with ghost and top of row
        sourceCol.dataset.span = available;
        sourceCol.dataset.colStart = start;

        // 5. Insert into DOM (Sorted Order)
        const allCols = [...occupied, { start, end: start + available, el: sourceCol }];
        allCols.sort((a, b) => a.start - b.start);

        allCols.forEach(item => {
            targetRow.appendChild(item.el);
        });

        // 6. Cleanup Source Row
        if (sourceRow && sourceRow !== targetRow) {
            const remaining = sourceRow.querySelectorAll(':scope > .page-col');
            if (remaining.length === 0) sourceRow.remove();
        }

        // 7. Save
        this._normalizeOrderIndexes();
        // 7. Save
        this._normalizeOrderIndexes();
        this.saveLayout(true); // Force save (drag is finishing)
        console.log('[Drop] âœ“ Layout saved (Absolute Grid)');
    }

    /**
     * Overflow handler: Target row is full, create new row below it.
     * Places the dragged block in a new row at full width.
     * NEVER merges rows. NEVER modifies the target row.
     */
    _executeOverflowDrop(targetRow) {
        const { sourceCol, sourceRow } = this._dragState;

        // Create new row AFTER target row
        const newRow = document.createElement('div');
        newRow.className = 'content-row page-row mb-3';
        newRow.dataset.rowId = `row-${Date.now()}`;
        targetRow.parentNode.insertBefore(newRow, targetRow.nextSibling);

        // Move block into new row at full width
        newRow.appendChild(sourceCol);
        sourceCol.style.gridColumn = '1 / span 12';
        sourceCol.dataset.span = 12;
        sourceCol.dataset.colStart = 1;

        // Fix source row
        if (sourceRow) {
            const remainingCols = sourceRow.querySelectorAll(':scope > .page-col');
            if (remainingCols.length === 0) {
                sourceRow.remove();
                console.log('[Drop] Source row empty â†’ removed');
            } else {
                this._applyEqualSpans(sourceRow);
            }
        }

        this._normalizeOrderIndexes();
        this._normalizeOrderIndexes();
        this.saveLayout(true); // Force save
        console.log('[Drop] âœ“ Overflow â†’ new row created and saved');
    }

    /**
     * PROPORTIONAL AUTO-FIT: Adjust spans to fit 12 columns.
     * Instead of wrapping or forcing equal widths, we scale down proportionally.
     * Example: Existing (8) + New (6) = 14.
     * Scale: 8 -> 6, 6 -> 6. Total 12.
     */
    _applyEqualSpans(rowEl) {
        const cols = Array.from(rowEl.querySelectorAll(':scope > .page-col'));
        if (cols.length === 0) return;

        // 1. Calculate total requested span
        let totalSpan = 0;
        cols.forEach(col => {
            let span = parseInt(col.dataset.span || 12);
            if (span < 1) span = 12; // Safety
            totalSpan += span;
        });

        console.log(`[AutoFit] Total requested span: ${totalSpan} (Target: 12)`);

        // 2. Proportional Scaling
        let currentSum = 0;
        let colStart = 1;

        cols.forEach((col, index) => {
            let originalSpan = parseInt(col.dataset.span || 12);

            // Calculate proportional span
            let newSpan;
            if (index === cols.length - 1) {
                // Last item fills/cuts to exactly 12
                newSpan = 12 - currentSum;
            } else {
                // Formula: floor(original * 12 / totalSpan)
                // If totalSpan < 12 (e.g. single item 8), this scales UP to 12.
                // If totalSpan > 12 (overflow), scales DOWN.
                if (totalSpan > 0) {
                    newSpan = Math.floor((originalSpan * 12) / totalSpan);
                } else {
                    newSpan = 12;
                }
                if (newSpan < 1) newSpan = 1;
            }

            // Safety: Ensure we don't exceed 12 if somehow logic fails
            if (newSpan < 1) newSpan = 1;

            currentSum += newSpan;

            // Apply new span
            col.style.gridColumn = `${colStart} / span ${newSpan}`;
            col.dataset.span = newSpan;
            col.dataset.colStart = colStart;

            console.log(`[AutoFit] Col ${index}: was ${originalSpan}, now ${newSpan}`);

            colStart += newSpan;
        });
    }

    /**
     * Normalize order_index values to sequential integers
     */
    _normalizeOrderIndexes() {
        // Sort by current order and reassign sequential integers
        const sorted = [...this.initialData].sort((a, b) => {
            const orderA = a.order_index || a.order || 0;
            const orderB = b.order_index || b.order || 0;
            return orderA - orderB;
        });

        sorted.forEach((block, index) => {
            block.order_index = index + 1;
            block.order = index + 1;
        });
    }

    /**
     * Cleanup after drag ends
     */
    _cleanupDrag() {
        // PHASE 1: Release pointer capture FIRST (critical for browser state)
        if (this._dragState?.capturingElement && this._dragState?.pointerId) {
            try {
                this._dragState.capturingElement.releasePointerCapture(this._dragState.pointerId);
                console.log('[Cleanup] âœ“ Pointer capture released');
            } catch (e) {
                // Element may have been removed from DOM â€” safe to ignore
                console.warn('[Cleanup] Could not release pointer capture:', e.message);
            }
        }

        // Remove ghost
        if (this._dragGhost) {
            this._dragGhost.remove();
            this._dragGhost = null;
        }

        // Restore source element opacity
        if (this._dragState?.sourceCol) {
            this._dragState.sourceCol.style.opacity = '';
        }

        // Remove all highlights
        document.querySelectorAll('.pointer-drop-target').forEach(el => {
            el.classList.remove('pointer-drop-target');
        });

        // Remove insert zones
        document.querySelectorAll('.row-insert-zone').forEach(el => el.remove());

        // SLOT SYSTEM: Remove all column slots
        document.querySelectorAll('.column-drop-slot').forEach(el => el.remove());

        // Remove column indicators
        document.querySelectorAll('.col-insert-indicator').forEach(el => el.remove());

        // Remove legacy insertion indicator
        document.querySelectorAll('.insertion-indicator').forEach(el => el.remove());

        // Remove body class
        document.body.classList.remove('is-pointer-dragging');

        // Clear state
        this._dragState = null;
        // Note: _dragPointerId removed (now in _dragState)
    }

    /**
     * Phase 5: Generic Settings Panel
     */
    _openSettings(block) {
        const modalEl = document.getElementById('contentEditModal');
        const modalTitle = modalEl.querySelector('.modal-title');
        const form = modalEl.querySelector('#content-edit-form');
        const saveBtn = modalEl.querySelector('#save-content-block');

        // Reset modal
        modalTitle.textContent = `${block.type.charAt(0).toUpperCase() + block.type.slice(1)} Settings`;
        form.innerHTML = '';

        // Ensure settings object exists
        if (!block.settings) block.settings = block.metadata?.settings || {};

        // Generate Fields based on Type
        let fields = '';

        if (block.type === 'spacer') {
            const height = block.settings.height || '50px';
            const heightVal = parseInt(height);
            fields = `
                <div class="mb-3">
                    <label class="form-label">Spacer Height (px)</label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="range" id="spacer-slider" class="form-range" min="10" max="300" step="10" value="${heightVal}" 
                            oninput="document.getElementById('spacer-val').value = this.value + 'px'">
                        <input type="text" id="spacer-val" class="form-control" style="width: 80px;" value="${height}"
                            onchange="let val = parseInt(this.value); if(isNaN(val)) val = 50; if(val < 10) val = 10; if(val > 300) val = 300; this.value = val + 'px'; document.getElementById('spacer-slider').value = val;">
                    </div>
                </div>
            `;
        } else if (block.type === 'divider') {
            const style = block.settings.style || 'solid';
            const width = block.settings.width || '100%';
            fields = `
                <div class="mb-3">
                    <label class="form-label">Style</label>
                    <select class="form-select" name="style">
                        <option value="solid" ${style === 'solid' ? 'selected' : ''}>Solid Line</option>
                        <option value="dashed" ${style === 'dashed' ? 'selected' : ''}>Dashed</option>
                        <option value="dotted" ${style === 'dotted' ? 'selected' : ''}>Dotted</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Width</label>
                    <select class="form-select" name="width">
                        <option value="100%" ${width === '100%' ? 'selected' : ''}>Full Width (100%)</option>
                        <option value="75%" ${width === '75%' ? 'selected' : ''}>75%</option>
                        <option value="50%" ${width === '50%' ? 'selected' : ''}>50%</option>
                    </select>
                </div>
             `;
        } else if (block.type === 'youtube') {
            const url = (block.data?.url) || block.secure_url || '';
            const start = block.settings.startTime || 0;
            fields = `
                <div class="mb-3">
                    <label class="form-label">YouTube URL</label>
                    <input type="url" class="form-control" name="url" value="${url}" placeholder="https://youtube.com/watch?v=...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Start Time (seconds)</label>
                    <input type="number" class="form-control" name="startTime" value="${start}" min="0">
                </div>
            `;
        } else {
            fields = `<p class="text-muted">No settings available for this block type.</p>`;
        }

        form.innerHTML = fields;

        // Clone button to remove previous listeners
        const newSaveBtn = saveBtn.cloneNode(true);
        saveBtn.parentNode.replaceChild(newSaveBtn, saveBtn);

        newSaveBtn.onclick = async () => {
            // Collect Data
            const formData = new FormData(form);

            // Update Settings
            // Ensure settings and data are Objects, not Arrays (PHP default is [])
            // This prevents JSON.stringify from stripping custom properties added to arrays.
            if (!block.settings || Array.isArray(block.settings)) block.settings = {};
            if (!block.data || Array.isArray(block.data)) block.data = {};

            if (block.type === 'spacer') {
                block.settings.height = document.getElementById('spacer-val').value;
            }

            if (block.type === 'divider') {
                block.settings.style = formData.get('style');
                block.settings.width = formData.get('width');
            }

            if (block.type === 'youtube') {
                block.settings.url = formData.get('url');
                block.settings.startTime = formData.get('startTime');

                // Legacy/Fallback: Update data too for immediate reaction if needed
                block.data = { ...block.data, url: formData.get('url') };
            }

            // Persist to Metadata (Backend source of truth for Layout)
            // AND to Settings (Backend source of truth for Content)
            if (!block.metadata) block.metadata = {};
            block.metadata.settings = block.settings;

            // Re-render block in DOM
            const oldWrapper = this.container.querySelector(`[data-content-id="${block.id}"]`);
            if (oldWrapper) {
                const newContent = this.blockRegistry.get(block.type)(block);
                oldWrapper.innerHTML = '';
                oldWrapper.appendChild(newContent);
                if (!this.readOnly) this._attachBlockActions(oldWrapper, block);
            }

            // Save Content Settings IMMEDIATELY to Backend
            try {
                // Ensure we send the specific fields we want to persist
                // ContentBlockController::update will handle 'settings' input
                await this._saveBlockContent(block.id, {
                    settings: block.settings,
                    // If we updated 'data' for youtube, send it too
                    data: block.data
                });

                // Also save layout (for metadata sync)
                await this._saveLayout();
            } catch (e) {
                console.error('Failed to save settings:', e);
                // TODO: Show toast error
            }

            // Close Modal
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            modalInstance.hide();
        };

        // Show Modal
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }

    // _initSortable removed to prevent double initialization. 
    // Sortable is initialized per-column in _createRowElement.

    /**
     * Public API: Add a new block programmatically
     */
    async addBlock(type, data = {}, settings = {}) {
        console.time('addBlock'); // Perf Audit

        // 1. Optimistic Creation (Client-Side)
        const tempId = `temp-${Date.now()}`;
        const newBlock = {
            id: tempId,
            type: type,
            title: `New ${type.charAt(0).toUpperCase() + type.slice(1)}`,
            description: '',
            content: data, // JSON content
            settings: settings,
            section: 'main_content', // Default section
            visibility: 'student', // Default visibility (student/teacher_only)
            is_active: "1", // Use string "1" to match Laravel boolean validator expectations in form-data like contexts
            order_index: this.initialData.length + 1,
            metadata: { row: null, span: 12, col_start: 1 } // Ensure metadata exists
        };

        // 2. Render Immediately
        this.initialData.push(newBlock);

        // Create a brand new row for the new block
        const rowId = `row-${Date.now()}`;
        const rowEl = document.createElement('div');
        rowEl.className = 'content-row page-row mb-3';
        rowEl.dataset.rowId = rowId;

        const colEl = document.createElement('div');
        colEl.className = 'page-col p-2';
        colEl.style.gridColumn = '1 / span 12';
        colEl.dataset.span = '12';
        colEl.dataset.colStart = '1';
        colEl.dataset.colId = `col-${Date.now()}`;
        colEl.dataset.spanData = JSON.stringify({ span: 12, start: 1 });

        const blockEl = this._renderBlock(newBlock);
        colEl.appendChild(blockEl);

        if (!this.readOnly) {
            colEl.classList.add('editor-col-active');
        }

        rowEl.appendChild(colEl);
        this.container.appendChild(rowEl);

        // Add slots for the new row
        this._createColumnSlots();

        // Scroll to it
        rowEl.scrollIntoView({ behavior: 'smooth', block: 'center' });

        console.log('Block added (Optimistic):', newBlock);
        console.timeEnd('addBlock'); // Should be < 10ms

        // 3. Background Persistence
        if (!this.blockCreationPromises) this.blockCreationPromises = new Map();

        const fileTypes = ['image', 'video', 'pdf', 'audio'];
        const isFileBlock = fileTypes.includes(newBlock.type);

        if (isFileBlock) {
            console.log(`[OptimisticUI] Skipping background creation for file-based block type: ${newBlock.type}. Creation will happen during upload.`);

            // Open edit modal immediately for file-based blocks (before returning)
            if (settings._openSettings) {
                if (!window.editContentBlock) {
                    this._openSettings(newBlock, true);
                } else {
                    setTimeout(() => {
                        window.editContentBlock(newBlock.id, newBlock);
                    }, 20);
                }
            }

            return; // EXIT EARLY: Don't create an empty shell on the server
        }

        const payload = { ...newBlock };
        delete payload.id; // Let server assign ID
        // Clean other client-only props if needed

        console.log(`[OptimisticUI] [${this.instanceId}] Starting background creation for ${tempId}`, payload);

        const savePromise = axios.post(this.options.saveContentBaseUrl, payload, {
            headers: {
                'X-CSRF-TOKEN': this.csrfToken,
                'Content-Type': 'application/json'
            }
        }).then(response => {
            if (response.data && response.data.success) {
                const realBlock = response.data.data;
                console.log(`[OptimisticUI] [${this.instanceId}] Creation success for ${tempId}. Real ID: ${realBlock.id}`);
                this._resolveTempBlock(tempId, realBlock);
                return realBlock;
            }
            console.error(`[OptimisticUI] [${this.instanceId}] Creation server-side failure for ${tempId}:`, response.data);
            throw new Error('Creation failed: ' + (response.data.message || 'Unknown error'));
        }).catch(err => {
            const errorData = err.response ? err.response.data : null;
            console.error(`[OptimisticUI] [${this.instanceId}] Background save exception for ${tempId}:`, {
                status: err.response?.status,
                data: errorData,
                message: err.message
            });

            // Rollback UI
            this._removeBlockNode(tempId);
            this.initialData = this.initialData.filter(b => b.id !== tempId);
            this.blockCreationPromises.delete(tempId);

            // If it's a validation error, try to show the details
            let msg = 'Failed to create block.';
            if (errorData && errorData.message) msg += ' ' + errorData.message;
            if (errorData && errorData.errors) {
                const detailedErrors = Object.values(errorData.errors).flat().join('\n');
                msg += '\n\n' + detailedErrors;
            }

            alert(`[Background Save Error]\n${msg}`); // NUCLEAR ALERT
            throw err;
        });

        console.log(`[OptimisticUI] [${this.instanceId}] Promise stored in map for ${tempId}`);
        this.blockCreationPromises.set(tempId, savePromise);

        // 4. Open Settings Immediately
        if (settings._openSettings) {
            // Internal Settings (Spacer, Divider, etc)
            if (['youtube', 'spacer', 'divider', 'carousel'].includes(type) || !window.editContentBlock) {
                this._openSettings(newBlock, true);
            } else {
                // External blade modal (Text, Image, PDF)
                // PASS THE PRELOADED DATA to avoid fetch!
                if (window.editContentBlock) {
                    window.editContentBlock(newBlock.id, newBlock);
                }
            }
        }
    }

    /**
     * Swap temp ID for real ID after server response
     */
    _resolveTempBlock(tempId, realBlock) {
        console.log(`[OptimisticUI] Resolving ${tempId} -> real ID ${realBlock.id}`, realBlock);

        // Update Internal Data
        const index = this.initialData.findIndex(b => b.id === tempId);
        if (index !== -1) {
            this.initialData[index] = { ...this.initialData[index], ...realBlock };
        }

        // Update DOM
        const wrapper = this.container.querySelector(`.page-block[data-id="${tempId}"]`);
        if (wrapper) {
            wrapper.dataset.id = realBlock.id;
            wrapper.dataset.contentId = realBlock.id;

            // Update actions (Edit/Delete buttons bound to ID)
            if (!this.readOnly) {
                const actions = wrapper.querySelector('.block-actions');
                if (actions) actions.remove();
                this._attachBlockActions(wrapper, this.initialData[index]);
            }
        }

        // CRITICAL FIX: Update Open Modal Form if it's editing this block
        const form = document.getElementById('content-edit-form');
        if (form) {
            const currentEditId = form.dataset.editId || form.dataset.contentId;
            console.log(`[OptimisticUI] Checking form for update. Form ID: ${currentEditId}, Resolution Target: ${tempId}`);

            if (currentEditId === tempId) {
                console.log(`[OptimisticUI] ✓ MATCH: Updating open modal ID: ${tempId} -> ${realBlock.id}`);
                form.dataset.editId = realBlock.id;
                form.dataset.contentId = realBlock.id;

                // Update hidden ID input if exists
                const idInput = form.querySelector('input[name="id"]');
                if (idInput) idInput.value = realBlock.id;
            } else if (currentEditId) {
                console.warn(`[OptimisticUI] ⚠ MISMATCH: Form is editing ${currentEditId}, not ${tempId}. Not updating.`);
            } else {
                console.log(`[OptimisticUI] Form is empty or has no ID. Not updating.`);
            }
        }

        // Cleanup Promise
        this.blockCreationPromises.delete(tempId);

        // Notify global events if anyone is listening
        const event = new CustomEvent('block-resolved', {
            detail: { tempId, realId: realBlock.id, block: realBlock }
        });
        window.dispatchEvent(event);
    }

    removeBlockNode(id) {
        return this._removeBlockNode(id);
    }

    _removeBlockNode(id) {
        const wrapper = this.container.querySelector(`.page-block[data-id="${id}"]`);
        if (wrapper) {
            const col = wrapper.closest('.page-col');
            if (!col) return;
            const row = col.closest('.page-row');
            col.remove();
            if (row && row.children.length === 0) row.remove();
        }
    }

    /**
     * Delete a block (API + DOM)
     */
    async deleteBlock(blockId) {
        if (!blockId) return;

        // Optimistic DOM removal
        this._removeBlockNode(blockId);

        // Remove from initialData
        this.initialData = this.initialData.filter(b => b.id != blockId);

        // RACE CONDITION: If it's a temp block, we need to wait for its creation to finish so we have a real ID to delete
        if (String(blockId).startsWith('temp-')) {
            console.log(`[OptimisticUI] Waiting for ${blockId} to resolve before deleting on server.`);
            const promise = this.blockCreationPromises?.get(blockId);
            if (promise) {
                try {
                    const realBlock = await promise;
                    blockId = realBlock.id;
                    console.log(`[OptimisticUI] ID resolved to ${blockId}. Proceeding with server delete.`);
                } catch (e) {
                    console.log(`[OptimisticUI] Creation for ${blockId} failed, no orphan to delete.`);
                    this.blockCreationPromises?.delete(blockId);
                    return;
                }
            } else {
                console.warn(`[OptimisticUI] No creation promise found for ${blockId}. Aborting server delete.`);
                return;
            }
        }

        try {
            // Using POST with _method override for better compatibility with some firewalls/environments
            await axios.post(`${this.saveContentBaseUrl}/${blockId}`, {
                _method: 'DELETE'
            }, {
                headers: { 'X-CSRF-TOKEN': this.csrfToken }
            });
            console.log(`[OptimisticUI] Block ${blockId} deleted on server.`);
        } catch (e) {
            console.error('[OptimisticUI] Failed to delete block on server:', e);
            // Since we already removed it from DOM, we don't revert (destructive action)
            // But we notify the log. In a full implementation, we might offer an "Undo" or "Restore".
        }
    }

    /**
     * Public Save Method
     */
    async saveLayout(force = false) {
        return this._saveLayout(force);
    }

    async _saveLayout(force = false) {
        // PHASE 2: Block save during active drag (unless forced by drop completion)
        if (this._dragState && !force) {
            console.warn('[Save] Blocked: drag in progress');
            return;
        }

        if (this.readOnly) return;

        const payload = [];

        // Traverse DOM: Each page-col has exactly one page-block (1:1)
        const rows = this.container.querySelectorAll('.page-row');
        rows.forEach((rowEl, rowIndex) => {
            const cols = rowEl.querySelectorAll(':scope > .page-col');
            let rowSpanTotal = 0;

            cols.forEach((colEl, colIndex) => {
                // Read span from the COLUMN element (source of truth)
                const span = parseInt(colEl.dataset.span) || 12;
                rowSpanTotal += span;

                // Each column has exactly one block
                const blockEl = colEl.querySelector('.page-block');
                if (!blockEl) return; // Skip empty columns

                const blockId = blockEl.dataset.contentId;
                if (!blockId || String(blockId).startsWith('temp-')) {
                    console.log(`[SaveLayout] Skipping block with no/temporary ID: ${blockId}`);
                    return;
                }

                // Lookup original block to preserve other metadata
                const block = this.initialData.find(b => b.id == blockId);
                const existingMeta = block ? { ...block.metadata } : {};

                // Clean legacy fields
                delete existingMeta.dimensions;
                delete existingMeta.width;
                delete existingMeta.height;
                delete existingMeta.column;
                delete existingMeta.column_index;
                delete existingMeta.row_id;

                // SPAN SOURCE OF TRUTH: dataset.span is authoritative
                // Build spanData FROM dataset.span â€” never the reverse
                const currentSpan = parseInt(colEl.dataset.span) || 12;
                const spanData = { desktop: currentSpan, tablet: 12, mobile: 12 };

                // Freeform positioning: read col_start from DOM
                const colStart = parseInt(colEl.dataset.colStart) || 1;

                payload.push({
                    id: parseInt(blockId),
                    order_index: colIndex + 1,
                    metadata: {
                        ...existingMeta,
                        row: rowIndex + 1,
                        span: spanData, // Store as object { desktop, tablet, mobile }
                        col_start: colStart, // Freeform column position
                        minHeight: parseInt(blockEl.dataset.minHeight) || null,
                        paddingTop: parseInt(blockEl.dataset.paddingTop) || null
                    }
                });
            });

            // GUARD: Warn if row exceeds 12 columns
            if (rowSpanTotal > 12) {
                console.warn(`Row ${rowIndex + 1} exceeds 12 columns (total: ${rowSpanTotal}). Layout may overflow.`);
            }
        });

        console.log('Saving Layout (1:1 Architecture):', payload);

        try {
            await axios.post(this.saveLayoutEndpoint, { blocks: payload });
            if (this.onLayoutSaved) this.onLayoutSaved();
        } catch (error) {
            console.error('Failed to save layout:', error);
            if (this.onLayoutError) this.onLayoutError(error);
        }
    }

    // Old _saveLayout removed from here

    _handleTextChange(blockId, manager) {
        if (this.readOnly) return;
        if (this.saveTimeouts.has(blockId)) clearTimeout(this.saveTimeouts.get(blockId));

        const timeoutId = setTimeout(async () => {
            try {
                const content = await manager.save();
                this._saveBlockContent(blockId, {
                    type: 'text',
                    content: JSON.stringify(content),
                    is_active: true,
                    visibility: 'student'
                });
            } catch (e) {
                console.error('Error saving text block', e);
            }
        }, 1000);

        this.saveTimeouts.set(blockId, timeoutId);
    }

    async _saveBlockContent(blockId, data) {
        if (!this.saveContentBaseUrl) return;

        const block = this.initialData.find(b => b.id == blockId);
        if (block) {
            data = { ...block, ...data };
            // Clean payload of read-only/server-generated fields
            const readOnlyFields = [
                'file', 'created_at', 'updated_at', 'creator', 'updater',
                'permissions', 'type_config', 'section_info', 'visibility_info',
                'signed_url', 'secure_viewer_url', 'formatted_file_size',
                'display_content', 'editable_content', 'rendered_content' // excessive data
            ];
            readOnlyFields.forEach(field => delete data[field]);
        }

        const url = `${this.saveContentBaseUrl}/${blockId}`;
        console.log('Sending Block Save Payload:', JSON.stringify(data));

        try {
            data['_method'] = 'PUT';
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                const errorData = await response.json();
                console.error('Block auto-save server error:', errorData);
                alert(`Failed to save block settings: ${errorData.message || response.statusText}`);
                throw new Error(errorData.message || 'Server returned ' + response.status);
            }

            const responseText = await response.text();
            console.log('Block auto-saved (Server Response):', responseText);

        } catch (e) {
            console.error('Auto-save error', e);
            // Optionally notify user
        }
    }
    /**
     * Phase 5: Block Registry System
     */
    _registerDefaultBlocks() {
        this.blockRegistry.set('text', this._renderTextBlock.bind(this));
        this.blockRegistry.set('image', this._renderImageBlock.bind(this));
        this.blockRegistry.set('video', this._renderVideoBlock.bind(this));
        this.blockRegistry.set('pdf', this._renderPdfBlock.bind(this));
        this.blockRegistry.set('audio', this._renderAudioBlock.bind(this));

        // Phase 5A: Utility Blocks
        this.blockRegistry.set('spacer', this._renderSpacer.bind(this));
        this.blockRegistry.set('divider', this._renderDivider.bind(this));
        this.blockRegistry.set('youtube', this._renderYouTube.bind(this));
    }

    _renderSpacer(block) {
        const el = document.createElement('div');
        const settings = block.settings || block.metadata?.settings || {};
        const height = settings.height || '50px';

        el.style.height = height;
        el.className = 'w-100 bg-transparent user-select-none d-flex align-items-center justify-content-center';

        if (!this.readOnly) {
            el.className += ' border border-dashed text-muted opacity-50';
            el.innerHTML = `<small>Spacer (${height})</small>`;
        }
        return el;
    }

    _renderDivider(block) {
        const el = document.createElement('div');
        const settings = block.settings || block.metadata?.settings || {};
        const style = settings.style || 'solid'; // dashed, dotted
        const width = settings.width || '100%';

        el.className = 'w-100 py-2 d-flex justify-content-center';

        const hr = document.createElement('hr');
        hr.className = 'my-2';
        hr.style.width = width;
        hr.style.borderTopStyle = style;
        hr.style.opacity = '0.15';

        el.appendChild(hr);
        return el;
    }

    /*
     * YouTube Rendering (Consolidated)
     */
    _renderYouTube(block) {
        const container = document.createElement('div');
        container.className = 'media-container position-relative';
        container.style.minHeight = '200px'; // Ensure visibility even if empty

        // Settings Resolution (Handle PHP empty array [] case)
        let settings = block.settings || block.metadata?.settings || {};
        if (Array.isArray(settings)) settings = {}; // Force object if array

        // Priority: settings.url -> data.url -> external_url -> secure_url
        // Check legacy data structure locations too
        const url = settings.url || (block.data && block.data.url) || block.external_url || block.secure_url;
        const startTime = settings.startTime || 0;

        let videoId = null;

        if (url) {
            // Extract Video ID
            try {
                const cleanUrl = url.trim();
                // Handle different formats:
                // - youtube.com/watch?v=ID
                // - youtu.be/ID
                // - youtube.com/embed/ID
                // - plain ID?
                if (cleanUrl.length === 11 && !cleanUrl.includes('/')) {
                    videoId = cleanUrl;
                } else {
                    const urlObj = new URL(cleanUrl);
                    if (urlObj.hostname.includes('youtube.com')) {
                        videoId = urlObj.searchParams.get('v');
                        if (!videoId && urlObj.pathname.includes('/embed/')) {
                            videoId = urlObj.pathname.split('/embed/')[1];
                        }
                    } else if (urlObj.hostname.includes('youtu.be')) {
                        videoId = urlObj.pathname.substring(1);
                    }
                }
            } catch (e) {
                // fallback regex
                const match = url.match(/(?:youtu\.be\/|youtube\.com\/.*v=)([^&]+)/);
                if (match) videoId = match[1];
            }

            if (videoId) {
                const iframe = document.createElement('iframe');
                iframe.src = `https://www.youtube.com/embed/${videoId}?start=${startTime}&rel=0`;
                iframe.className = 'w-100 shadow-sm rounded';
                iframe.allow = 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
                iframe.allowFullscreen = true;

                // Aspect Ratio 16:9
                iframe.style.aspectRatio = '16/9';

                container.appendChild(iframe);
            } else {
                container.innerHTML = `
                    <div class="alert alert-warning d-flex align-items-center mb-0">
                        <i class="fab fa-youtube fa-2x me-3 text-danger"></i>
                        <div>
                            <strong>Invalid YouTube URL</strong><br/>
                            <small class="text-muted">${url}</small>
                        </div>
                    </div>`;
            }
        } else {
            // Placeholder state
            container.innerHTML = `
                <div class="p-5 bg-light rounded border border-dashed text-center clickable-placeholder">
                    <i class="fab fa-youtube fa-3x text-danger mb-3"></i>
                    <h5 class="text-muted">Add YouTube Video</h5>
                    <button class="btn btn-outline-primary btn-sm mt-2">Enter URL</button>
                    ${this.readOnly ? '<br><small class="text-muted">(Edit mode only)</small>' : ''}
                </div>
            `;
            // Click to open settings
            container.onclick = (e) => {
                if (!document.body.classList.contains('is-dragging-block') && !this.readOnly) {
                    this._openSettings(block);
                }
            };
        }

        return container;
    }

    /**
     * Helper: Toggle Sortables (enable/disable)
     * Used during resize to prevent drag interference
     */
    _toggleSortables(enable) {
        if (!this.sortables) return;
        this.sortables.forEach(s => s.option('disabled', !enable));
    }

    // =============================================
    // PHASE 1: RESPONSIVE LAYOUT SUPPORT
    // =============================================

    /**
     * Normalize span data to support responsive breakpoints.
     * @param {number|object} spanData - Either a number (legacy) or { desktop, tablet, mobile }
     * @returns {number} The span for the current breakpoint
     */
    _getSpanForBreakpoint(spanData) {
        if (typeof spanData === 'number') {
            return spanData; // Legacy: single number applies to all breakpoints
        }
        if (typeof spanData === 'object' && spanData !== null) {
            return spanData[this.activeBreakpoint] || spanData.desktop || 12;
        }
        return 12; // Default
    }

    /**
     * Get the full span object, normalizing legacy data.
     * @param {number|object} spanData
     * @returns {object} { desktop, tablet, mobile }
     */
    _normalizeSpanData(spanData) {
        if (typeof spanData === 'number') {
            return { desktop: spanData, tablet: 12, mobile: 12 };
        }
        if (typeof spanData === 'object' && spanData !== null) {
            return {
                desktop: spanData.desktop || 12,
                tablet: spanData.tablet || 12,
                mobile: spanData.mobile || 12
            };
        }
        return { desktop: 12, tablet: 12, mobile: 12 };
    }

    /**
     * Switch the active breakpoint and re-render.
     * @param {'desktop'|'tablet'|'mobile'} breakpoint
     */
    setBreakpoint(breakpoint) {
        if (!['desktop', 'tablet', 'mobile'].includes(breakpoint)) {
            console.warn('Invalid breakpoint:', breakpoint);
            return;
        }
        this.activeBreakpoint = breakpoint;

        // Update container class for CSS preview sizing
        this.container.classList.remove('preview-desktop', 'preview-tablet', 'preview-mobile');
        this.container.classList.add(`preview-${breakpoint}`);

        // Re-render with new breakpoint spans
        this.render();
    }

    // =============================================
    // PHASE 2: ROW OVERFLOW PROTECTION
    // =============================================

    /**
     * Calculate available columns in a row for a specific column.
     * @param {HTMLElement} rowEl
     * @param {HTMLElement} excludeColEl - Column to exclude from calculation
     * @returns {number} Available columns (12 - sibling spans)
     */
    _getCollisionLimitLeft(rowEl, colEl) {
        const cols = rowEl.querySelectorAll('.page-col');
        const myColStart = parseInt(colEl.dataset.colStart) || 1;
        let limit = 1;

        cols.forEach(col => {
            if (col !== colEl) {
                const otherStart = parseInt(col.dataset.colStart) || 1;
                const otherSpan = parseInt(col.dataset.span) || 12;
                const otherEnd = otherStart + otherSpan;

                // If block is to the left of us
                if (otherEnd <= myColStart) {
                    limit = Math.max(limit, otherEnd);
                }
            }
        });
        return limit;
    }

    /**
     * Calculate available columns in a row for a specific column.
     * @param {HTMLElement} rowEl
     * @param {HTMLElement} excludeColEl - Column to exclude from calculation
     * @returns {number} Available columns (12 - sibling spans)
     */
    _getAvailableSpan(rowEl, excludeColEl) {
        const cols = rowEl.querySelectorAll('.page-col');
        let usedSpan = 0;
        cols.forEach(col => {
            if (col !== excludeColEl) {
                usedSpan += parseInt(col.dataset.span) || 0;
            }
        });
        return Math.max(1, 12 - usedSpan);
    }

    /**
     * PHASE 15: Check if a proposed block position overlaps any existing blocks in a row.
     * @param {HTMLElement} rowEl - The row to check
     * @param {number} proposedStart - Proposed grid column start (1-12)
     * @param {number} proposedSpan - Proposed column span
     * @param {HTMLElement|null} excludeCol - Column to exclude (e.g., the block being moved)
     * @returns {boolean} True if overlap detected
     */
    _hasOverlap(rowEl, proposedStart, proposedSpan, excludeCol = null) {
        const cols = Array.from(rowEl.querySelectorAll(':scope > .page-col'))
            .filter(c => c !== excludeCol && !c.classList.contains('drop-preview'));

        const proposedEnd = proposedStart + proposedSpan;

        return cols.some(col => {
            const colStart = parseInt(col.dataset.colStart) || 1;
            const colSpan = parseInt(col.dataset.span) || 12;
            const colEnd = colStart + colSpan;

            // Standard AABB overlap: two ranges [A_start, A_end) and [B_start, B_end) overlap
            // if A_start < B_end AND A_end > B_start
            return proposedStart < colEnd && proposedEnd > colStart;
        });
    }

    /**
     * PHASE 15: Revert dragged block to its original position.
     * Called when a drop would cause overlap — block snaps back, no other blocks move.
     */
    _revertToOriginalPosition() {
        if (!this._dragState) return;

        const { sourceCol, originalRow, originalColStart, originalSpan } = this._dragState;

        // Restore grid position
        sourceCol.style.gridColumn = `${originalColStart} / span ${originalSpan}`;
        sourceCol.style.gridRow = '1';
        sourceCol.dataset.colStart = originalColStart;
        sourceCol.dataset.span = originalSpan;

        // If the block was moved to a different row during the drag attempt,
        // move it back to the original row
        if (sourceCol.parentElement !== originalRow && originalRow) {
            originalRow.appendChild(sourceCol);
        }

        console.log(`[Drop] Reverted to original position: col ${originalColStart}, span ${originalSpan}`);

        // Normalize and save
        this._normalizeOrderIndexes();
        this.saveLayout(true);
    }

    // =============================================
    // PHASE 3: UNDO / REDO
    // =============================================

    /**
     * Capture current layout state as a snapshot.
     * @returns {Array} Array of { id, row, span, order }
     */
    _captureLayoutSnapshot() {
        const snapshot = [];
        const rows = this.container.querySelectorAll('.page-row');
        rows.forEach((rowEl, rowIndex) => {
            const cols = rowEl.querySelectorAll('.page-col');
            cols.forEach((colEl, colIndex) => {
                const blockEl = colEl.querySelector('.page-block');
                if (blockEl && blockEl.dataset.contentId) {
                    snapshot.push({
                        id: parseInt(blockEl.dataset.contentId),
                        row: rowIndex + 1,
                        span: parseInt(colEl.dataset.span) || 12,
                        order: colIndex + 1
                    });
                }
            });
        });
        return snapshot;
    }

    /**
     * Push current state to undo stack before making changes.
     */
    _pushUndoState() {
        const snapshot = this._captureLayoutSnapshot();
        this.undoStack.push(snapshot);
        if (this.undoStack.length > this.maxUndoSteps) {
            this.undoStack.shift(); // Remove oldest
        }
        this.redoStack = []; // Clear redo stack on new action
    }

    /**
     * Undo the last layout change.
     */
    async undo() {
        if (this.undoStack.length === 0) {
            console.log('Nothing to undo');
            return;
        }

        // Save current state to redo stack
        this.redoStack.push(this._captureLayoutSnapshot());

        // Restore previous state
        const previousState = this.undoStack.pop();
        await this._applyLayoutSnapshot(previousState);
    }

    /**
     * Redo the last undone layout change.
     */
    async redo() {
        if (this.redoStack.length === 0) {
            console.log('Nothing to redo');
            return;
        }

        // Save current state to undo stack
        this.undoStack.push(this._captureLayoutSnapshot());

        // Restore next state
        const nextState = this.redoStack.pop();
        await this._applyLayoutSnapshot(nextState);
    }

    /**
     * Apply a layout snapshot (for undo/redo).
     * @param {Array} snapshot
     */
    async _applyLayoutSnapshot(snapshot) {
        // Update initialData with snapshot values
        snapshot.forEach(item => {
            const block = this.initialData.find(b => b.id === item.id);
            if (block) {
                block.metadata = block.metadata || {};
                block.metadata.row = item.row;
                block.metadata.span = item.span;
                block.order_index = item.order;
            }
        });

        // Re-parse and render
        this._parseDataToLayout();
        this.render();

        // Save to server
        await this.saveLayout();
    }

    /**
     * Helper: Calculate minimum span for a block based on content type and row width.
     */
    _getMinSpanForBlock(type, colWidth) {
        const policy = this.resizePolicy[type] || this.resizePolicy.default;

        if (policy.minSpanPx && colWidth > 0) {
            return Math.ceil(policy.minSpanPx / colWidth);
        }

        return policy.minSpan || 2;
    }

    /**
     * Show a temporary hint when hit limits
     */
    _showResizeHint(wrapper, message) {
        if (wrapper.querySelector('.resize-hint')) return;

        const hint = document.createElement('div');
        hint.className = 'resize-hint';
        hint.textContent = message;
        wrapper.appendChild(hint);

        setTimeout(() => hint.remove(), 1000);
    }

    /**
     * Setup keyboard shortcuts for undo/redo.
     */
    _setupKeyboardShortcuts() {
        if (this.readOnly) return;

        document.addEventListener('keydown', (e) => {
            // Check if focus is inside an editor (don't intercept)
            if (e.target.closest('.codex-editor') || e.target.closest('input') || e.target.closest('textarea')) {
                return;
            }

            // Ctrl+Z = Undo
            if (e.ctrlKey && !e.shiftKey && e.key === 'z') {
                e.preventDefault();
                this.undo();
            }

            // Ctrl+Shift+Z = Redo
            if (e.ctrlKey && e.shiftKey && e.key === 'Z') {
                e.preventDefault();
                this.redo();
            }
        });
    }

    // =============================================
    // PHASE 4: COPY / PASTE BLOCKS
    // =============================================

    /**
     * Copy a block to internal clipboard.
     * @param {number|string} blockId
     */
    copyBlock(blockId) {
        const block = this.initialData.find(b => b.id == blockId);
        if (!block) {
            console.warn('Block not found for copy:', blockId);
            return;
        }

        this.clipboard = {
            type: block.type,
            content: block.content,
            settings: block.settings || {},
            metadata: {
                span: this._normalizeSpanData(block.metadata?.span || 12)
            }
        };

        console.log('Block copied to clipboard:', this.clipboard);
    }

    /**
     * Paste block from clipboard into a target row.
     * @param {string} targetRowId - Row to paste into
     */
    async pasteBlock(targetRowId) {
        if (!this.clipboard) {
            console.warn('Clipboard is empty');
            return;
        }

        // TODO: POST to backend to create new block
        // For now, log the action
        console.log('Paste block into row:', targetRowId, this.clipboard);
        alert('Paste functionality requires backend endpoint. Block data logged to console.');
    }

    /**
     * Duplicate a block within the same row if space allows.
     * @param {number|string} blockId
     */
    async duplicateBlock(blockId) {
        const block = this.initialData.find(b => b.id == blockId);
        if (!block) return;

        const span = this._getSpanForBreakpoint(block.metadata?.span || 12);
        const rowId = block.metadata?.row;

        // Check if there's space in the row
        // (Simplified: just check if total would exceed 12)
        const rowBlocks = this.initialData.filter(b => b.metadata?.row === rowId);
        const usedSpan = rowBlocks.reduce((sum, b) => {
            return sum + this._getSpanForBreakpoint(b.metadata?.span || 12);
        }, 0);

        if (usedSpan + span > 12) {
            alert('Not enough space in this row to duplicate.');
            return;
        }

        // TODO: POST to backend to duplicate
        console.log('Duplicate block:', blockId, 'Span:', span);
        alert('Duplicate functionality requires backend endpoint.');
    }

    // ============================================
    // Freeform Drop Zone Handling
    // ============================================

    /**
     * Handle drop on a specific column zone.
     * @param {DragEvent} e - The drop event
     * @param {string} rowId - The row ID
     * @param {number} targetCol - The target column (1-12)
     */
    _handleDropOnZone(e, rowId, targetCol) {
        // Get the dragged block ID from dataTransfer
        const blockId = e.dataTransfer.getData('text/plain');
        if (!blockId) {
            console.warn('No block ID in dataTransfer');
            return;
        }

        // Find the block in initialData
        const block = this.initialData.find(b => b.id == blockId);
        if (!block) {
            console.warn('Block not found:', blockId);
            return;
        }

        // Push undo state before change
        this._pushUndoState();

        // Update block metadata
        if (!block.metadata) block.metadata = {};
        block.metadata.col_start = targetCol;

        // Parse row number from rowId (format: "row-X" or just number)
        let rowNum = parseInt(rowId);
        if (isNaN(rowNum)) {
            rowNum = parseInt(rowId.replace('row-', ''));
        }
        if (!isNaN(rowNum)) {
            block.metadata.row = rowNum;
        }

        console.log(`Block ${blockId} dropped at row ${rowNum}, col ${targetCol}`);

        // Re-render and save
        this._parseDataToLayout();
        this.render();
        this.saveLayout();
    }

    // ============================================
    // Google Sites Column-Aware Drop Indicator
    // ============================================

    /**
     * Create the drop indicator element.
     * A vertical blue bar that shows exact insertion point.
     */
    _createDropIndicator() {
        const indicator = document.createElement('div');
        indicator.className = 'drop-indicator';
        indicator.style.display = 'none';
        document.body.appendChild(indicator);
        return indicator;
    }

    /**
     * Calculate target column (1-12) from mouse X position.
     * @param {HTMLElement} rowEl - The row element
     * @param {number} mouseX - The clientX of the mouse
     * @returns {number} Column number (1-12)
     */
    _getTargetColumn(rowEl, mouseX) {
        const rect = rowEl.getBoundingClientRect();
        const colWidth = rect.width / 12;
        const relativeX = mouseX - rect.left;
        return Math.min(12, Math.max(1, Math.floor(relativeX / colWidth) + 1));
    }

    /**
     * Position the drop indicator at a column boundary.
     * @param {HTMLElement} rowEl - The row element
     * @param {number} mouseX - The clientX of the mouse
     */
    _positionDropIndicator(rowEl, mouseX) {
        if (!rowEl) {
            this._hideDropIndicator();
            return;
        }

        const rect = rowEl.getBoundingClientRect();
        const colWidth = rect.width / 12;
        const targetCol = this._getTargetColumn(rowEl, mouseX);

        // Position at the left edge of target column
        const leftPos = rect.left + (targetCol - 1) * colWidth;

        this.dropIndicator.style.left = `${leftPos}px`;
        this.dropIndicator.style.top = `${rect.top}px`;
        this.dropIndicator.style.height = `${rect.height}px`;
        this.dropIndicator.style.display = 'block';

        this.activeDropRow = rowEl;
    }

    /**
     * Hide the drop indicator.
     */
    _hideDropIndicator() {
        this.dropIndicator.style.display = 'none';
        this.activeDropRow = null;
    }

    /**
     * Find the insertion index based on target column position.
     * @param {HTMLElement} rowEl - The row element
     * @param {number} targetCol - The target column (1-12)
     * @param {HTMLElement} draggedCol - The column being dragged
     * @returns {number} Insertion index
     */
    _getInsertionIndex(rowEl, targetCol, draggedCol) {
        const cols = [...rowEl.querySelectorAll('.page-col')].filter(c => c !== draggedCol);
        let insertIndex = 0;
        let usedCols = 0;

        for (let i = 0; i < cols.length; i++) {
            const colSpan = parseInt(cols[i].dataset.span || 12);
            usedCols += colSpan;

            // If target column is before or at this block's end position
            if (targetCol <= usedCols) {
                insertIndex = i;
                break;
            }
            insertIndex = i + 1;
        }

        return insertIndex;
    }
}
