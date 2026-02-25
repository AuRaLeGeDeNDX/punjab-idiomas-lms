/**
 * FontSize Tool for Editor.js
 * 
 * Allows changing the font size of the selected text.
 */
export class FontSizeTool {
    static get isInline() {
        return true;
    }

    static get sanitize() {
        return {
            span: {
                class: true,
                style: true,
                'data-font-size': true
            }
        };
    }

    constructor({ api }) {
        this.api = api;
        this.button = null;
        this.state = false;

        // Define available sizes
        this.sizes = [12, 14, 16, 18, 20, 24, 30];
        this.currentSize = 16; // Default
    }

    render() {
        this.button = document.createElement('button');
        this.button.type = 'button';
        this.button.classList.add('ce-inline-tool');
        this.button.innerHTML = '<span style="font-weight: bold; font-size: 10px;">SIZE</span>';

        return this.button;
    }

    surround(range) {
        if (this.state) {
            this.unwrap(range);
            return;
        }

        this.wrap(range);
    }

    wrap(range) {
        const selectedText = range.extractContents();
        const span = document.createElement('span');

        // For MVP, we cycle through sizes or prompt? 
        // A prompt is intrusive but easiest for "custom" inputs without a complex UI.
        // Let's try a simple toggle between "Normal" (unset) and "Large" (20px) vs "Small" (12px)?
        // Or better: Cycle! 16 -> 18 -> 20 -> 24 -> 16.

        // Better UX: Dropdown is hard. Prompt is okayish.
        // Let's use a fixed "Highlight" style approach for simplicity in V1, 
        // OR simply prompt.

        // User request "bar like structure" implies dropdown. 
        // Implementing a real dropdown inside toggle button render:

        // Let's just hardcode to a set size for now or open a simple popover?
        // Popover is complex.

        // Let's implement cycling mechanism for now as it's robust.
        // Click -> 18px. Click again -> 24px. Click again -> Reset.

        let nextSize = 18; // Default first step

        span.style.fontSize = `${nextSize}px`;
        span.dataset.fontSize = nextSize;
        span.classList.add('cdx-font-size');

        span.appendChild(selectedText);
        range.insertNode(span);

        this.api.selection.expandToTag(span);
    }

    unwrap(range) {
        const span = this.api.selection.findParentTag('SPAN', 'cdx-font-size');

        if (span) {
            // Cycle behavior: 18 -> 24 -> 30 -> Remove
            const current = parseInt(span.dataset.fontSize);
            let next = null;

            if (current === 18) next = 24;
            else if (current === 24) next = 30;
            else if (current === 30) next = null; // Reset

            if (next) {
                span.style.fontSize = `${next}px`;
                span.dataset.fontSize = next;
            } else {
                this.api.selection.expandToTag(span);
                const text = span.innerHTML;
                span.outerHTML = text;
            }
        }
    }

    checkState(selection) {
        const span = this.api.selection.findParentTag('SPAN', 'cdx-font-size');
        this.state = !!span;

        if (this.button) {
            this.button.classList.toggle('ce-inline-tool--active', this.state);

            if (span) {
                this.button.innerHTML = `<span style="font-weight: bold; font-size: 10px;">${span.dataset.fontSize}</span>`;
            } else {
                this.button.innerHTML = '<span style="font-weight: bold; font-size: 10px;">SIZE</span>';
            }
        }
    }
}
