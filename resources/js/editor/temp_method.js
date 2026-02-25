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

    // Pointer Move Handler (Unified Dispatcher)
    const onPointerMove = (evt) => {
        evt.preventDefault();
        evt.stopPropagation();

        const deltaX = evt.clientX - startX;
        const deltaY = evt.clientY - startY;

        const colsChanged = Math.round(deltaX / colWidth);

        switch (dir) {
            case 'right': {
                let newSpan = startSpan + colsChanged;
                const maxSpan = this._getAvailableSpan(rowEl, colEl) + startSpan;
                newSpan = Math.max(minSpan, Math.min(newSpan, maxSpan));

                colEl.style.gridColumn = `${startCol} / span ${newSpan}`;
                colEl.dataset.span = newSpan;

                if (newSpan === minSpan && colsChanged < 0) {
                    this._showResizeHint(wrapper, 'Readable width limit reached');
                }
                break;
            }

            case 'left': {
                let newSpan = startSpan - colsChanged;
                const maxSpan = startSpan + (startCol - 1);
                newSpan = Math.max(minSpan, Math.min(newSpan, maxSpan));
                const newColStart = startCol + (startSpan - newSpan);

                colEl.style.gridColumn = `${newColStart} / span ${newSpan}`;
                colEl.dataset.span = newSpan;
                colEl.dataset.colStart = newColStart;

                if (newSpan === minSpan && colsChanged > 0) {
                    this._showResizeHint(wrapper, 'Readable width limit reached');
                }
                break;
            }

            case 'bottom': {
                const newHeight = Math.max(30, startHeight + deltaY);
                wrapper.style.minHeight = `${newHeight}px`;
                wrapper.dataset.minHeight = newHeight;
                break;
            }

            case 'top': {
                const newPadding = Math.max(0, startPaddingTop + deltaY);
                wrapper.style.paddingTop = `${newPadding}px`;
                wrapper.dataset.paddingTop = newPadding;
                break;
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
        wrapper.releasePointerCapture(evt.pointerId); // Release capture

        // Save current span to responsive data
        if (dir === 'left' || dir === 'right') {
            const currentSpan = parseInt(colEl.dataset.span);
            const spanData = this._normalizeSpanData(colEl.dataset.spanData);
            spanData[this.activeBreakpoint] = currentSpan;
            colEl.dataset.spanData = JSON.stringify(spanData);
        }

        await this.saveLayout();
    };

    // Pointer Capture for consistent tracking
    wrapper.setPointerCapture(e.pointerId);

    document.addEventListener('pointermove', onPointerMove, { passive: false });
    document.addEventListener('pointerup', onPointerUp);
    wrapper.classList.add('is-resizing');
    document.body.style.cursor = dir === 'top' || dir === 'bottom' ? 'ns-resize' : 'ew-resize';
}
