/**
 * Editor.js Content Renderer Module
 * 
 * This module provides functions to render Editor.js JSON content to HTML,
 * detect content format, and sanitize HTML for XSS prevention.
 * 
 * Requirements: 4.1, 4.2, 5.1, 7.2
 */

/**
 * Detect if content is Editor.js JSON format
 * 
 * @param {string|Object} content - Content to check
 * @returns {boolean} True if content is Editor.js JSON
 */
export function isEditorJsContent(content) {
    if (!content) {
        console.debug('Content format detection: empty content', { format: 'empty' });
        return false;
    }

    // If it's already an object, check for blocks property
    if (typeof content === 'object') {
        const isEditorJs = Array.isArray(content.blocks);
        console.debug('Content format detection', {
            format: isEditorJs ? 'editorjs' : 'unknown',
            hasBlocks: !!content.blocks,
            isObject: true
        });
        return isEditorJs;
    }

    // If it's a string, try to parse as JSON
    if (typeof content === 'string') {
        try {
            const parsed = JSON.parse(content);
            const isEditorJs = Array.isArray(parsed.blocks);
            console.debug('Content format detection', {
                format: isEditorJs ? 'editorjs' : 'html',
                hasBlocks: !!parsed.blocks,
                isValidJson: true
            });
            return isEditorJs;
        } catch (e) {
            console.debug('Content format detection', {
                format: 'html',
                isValidJson: false,
                parseError: e.message
            });
            return false;
        }
    }

    console.debug('Content format detection: unknown type', {
        format: 'unknown',
        contentType: typeof content
    });
    return false;
}

/**
 * Sanitize HTML to prevent XSS attacks
 * Allows only safe inline formatting tags
 * 
 * @param {string} html - HTML string to sanitize
 * @returns {string} Sanitized HTML
 */
export function sanitizeHtml(html) {
    if (!html) {
        return '';
    }

    // Create a temporary div to parse HTML
    const temp = document.createElement('div');
    temp.textContent = html;
    let sanitized = temp.innerHTML;

    // Allow only safe inline formatting tags
    const allowedTags = ['b', 'i', 'u', 'strong', 'em', 'mark', 'code', 'a'];
    const tagPattern = /<(\/?)([\w]+)([^>]*)>/g;

    sanitized = sanitized.replace(tagPattern, (match, slash, tag, attrs) => {
        const lowerTag = tag.toLowerCase();

        if (allowedTags.includes(lowerTag)) {
            // For anchor tags, only allow href attribute
            if (lowerTag === 'a' && !slash) {
                const hrefMatch = attrs.match(/href=["']([^"']*)["']/);
                if (hrefMatch) {
                    const href = hrefMatch[1];
                    // Only allow http, https, and mailto links
                    if (href.match(/^(https?:\/\/|mailto:)/)) {
                        return `<${tag} href="${href}" target="_blank" rel="noopener noreferrer">`;
                    }
                }
                return `<${tag}>`;
            }
            return `<${slash}${tag}>`;
        }

        // Remove disallowed tags
        return '';
    });

    return sanitized;
}

/**
 * Render a single Editor.js block to HTML
 * 
 * @param {Object} block - Editor.js block object
 * @returns {string} HTML string
 */
export function renderBlock(block) {
    const { type, data } = block;

    switch (type) {
        case 'header':
            return renderHeader(data);
        case 'paragraph':
            return renderParagraph(data);
        case 'list':
            return renderList(data);
        case 'quote':
            return renderQuote(data);
        case 'code':
            return renderCode(data);
        case 'image':
            return renderImage(data);
        case 'attaches':
            return renderAttaches(data);
        default:
            console.warn(`Unknown block type: ${type}`);
            return '';
    }
}

/**
 * Render image block
 */
function renderImage(data) {
    const url = data.file && data.file.url ? data.file.url : '';
    const caption = data.caption ? sanitizeHtml(data.caption) : '';
    const withBorder = data.withBorder ? 'border' : '';
    const withBackground = data.withBackground ? 'bg-gray-100 p-2' : '';
    const stretched = data.stretched ? 'w-full' : '';

    if (!url) return '';

    return `
        <figure class="my-4 ${withBackground} ${withBorder} ${stretched}">
            <img src="${url}" alt="${caption}" class="max-w-full h-auto rounded-lg mx-auto">
            ${caption ? `<figcaption class="text-center text-sm text-gray-500 mt-2">${caption}</figcaption>` : ''}
        </figure>
    `;
}

/**
 * Render attachment block
 */
function renderAttaches(data) {
    const url = data.file && data.file.url ? data.file.url : '';
    const title = data.title || (data.file && data.file.name) || 'Attachment';
    const size = data.file && data.file.size ? formatBytes(data.file.size) : '';
    const extension = data.file && data.file.extension ? data.file.extension.toUpperCase() : 'FILE';

    if (!url) return '';

    return `
        <a href="${url}" target="_blank" rel="noopener noreferrer" class="flex items-center p-4 my-4 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors no-underline group">
            <div class="flex-shrink-0 flex items-center justify-center w-10 h-10 rounded bg-indigo-50 text-indigo-600 font-bold text-xs uppercase">
                ${extension}
            </div>
            <div class="ml-4 flex-grow">
                <div class="text-sm font-medium text-gray-900 group-hover:text-indigo-600">${sanitizeHtml(title)}</div>
                ${size ? `<div class="text-xs text-gray-500">${size}</div>` : ''}
            </div>
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
        </a>
    `;
}

/**
 * Format bytes to human readable string
 */
function formatBytes(bytes, decimals = 2) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const dm = decimals < 0 ? 0 : decimals;
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

/**
 * Render header block
 */
function renderHeader(data) {
    const level = data.level || 2;
    const text = sanitizeHtml(data.text || '');
    return `<h${level}>${text}</h${level}>`;
}

/**
 * Render paragraph block
 */
function renderParagraph(data) {
    const text = sanitizeHtml(data.text || '');
    return `<p>${text}</p>`;
}

/**
 * Render list block
 */
function renderList(data) {
    const style = data.style || 'unordered';
    const tag = style === 'ordered' ? 'ol' : 'ul';
    const items = (data.items || []).map(item => {
        const sanitized = sanitizeHtml(item);
        return `<li>${sanitized}</li>`;
    }).join('');

    return `<${tag}>${items}</${tag}>`;
}

/**
 * Render quote block
 */
function renderQuote(data) {
    const text = sanitizeHtml(data.text || '');
    const caption = data.caption ? sanitizeHtml(data.caption) : '';

    let html = '<blockquote>';
    html += `<p>${text}</p>`;
    if (caption) {
        html += `<cite>${caption}</cite>`;
    }
    html += '</blockquote>';

    return html;
}

/**
 * Render code block
 */
function renderCode(data) {
    const code = data.code || '';
    // For code blocks, escape HTML entities but don't use sanitizeHtml
    const temp = document.createElement('div');
    temp.textContent = code;
    const escaped = temp.innerHTML;

    return `<pre><code>${escaped}</code></pre>`;
}

/**
 * Render Editor.js JSON content to HTML
 * 
 * @param {Object|string} content - Editor.js JSON data (object or JSON string)
 * @returns {string} Rendered HTML string
 */
export function renderEditorContent(content) {
    if (!content) {
        return '';
    }

    let data;

    // Parse JSON string if needed
    if (typeof content === 'string') {
        try {
            data = JSON.parse(content);
        } catch (e) {
            console.error('Failed to parse Editor.js content:', e);
            return '<p class="text-red-500">Error: Invalid content format</p>';
        }
    } else {
        data = content;
    }

    // Validate structure
    if (!data.blocks || !Array.isArray(data.blocks)) {
        console.error('Invalid Editor.js data structure');
        return '<p class="text-red-500">Error: Invalid content structure</p>';
    }

    // Render each block
    const html = data.blocks.map(block => renderBlock(block)).join('\n');

    return html;
}
