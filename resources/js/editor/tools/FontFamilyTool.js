/**
 * FontFamily Tool for Editor.js
 * 
 * Allows changing the font family of the selected text.
 */
export class FontFamilyTool {
    static get isInline() {
        return true;
    }

    static get sanitize() {
        return {
            span: {
                class: true,
                style: true,
                'data-font-family': true
            }
        };
    }

    constructor({ api }) {
        this.api = api;
        this.button = null;
        this.state = false;
    }

    render() {
        this.button = document.createElement('button');
        this.button.type = 'button';
        this.button.classList.add('ce-inline-tool');
        this.button.innerHTML = '<i class="fas fa-font"></i>';

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

        // Cycle: Serif -> Monospace -> Sans-Serif (Default)
        span.style.fontFamily = 'serif';
        span.dataset.fontFamily = 'serif';
        span.classList.add('cdx-font-family');

        span.appendChild(selectedText);
        range.insertNode(span);
        this.api.selection.expandToTag(span);
    }

    unwrap(range) {
        const span = this.api.selection.findParentTag('SPAN', 'cdx-font-family');

        if (span) {
            const current = span.dataset.fontFamily;
            let next = null;

            // Toggle Logic
            if (current === 'serif') next = 'monospace';
            else if (current === 'monospace') next = 'cursive';
            else next = null; // Back to default (sans-serif)

            if (next) {
                span.style.fontFamily = next;
                span.dataset.fontFamily = next;
            } else {
                this.api.selection.expandToTag(span);
                const text = span.innerHTML;
                span.outerHTML = text;
            }
        }
    }

    checkState(selection) {
        const span = this.api.selection.findParentTag('SPAN', 'cdx-font-family');
        this.state = !!span;

        if (this.button) {
            this.button.classList.toggle('ce-inline-tool--active', this.state);
        }
    }
}
