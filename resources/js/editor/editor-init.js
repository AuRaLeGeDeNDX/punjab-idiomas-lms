/**
 * Editor.js Initialization Module
 * 
 * This module provides the EditorManager class for managing Editor.js lifecycle:
 * - Initialization with configuration and data
 * - Content saving (returns JSON)
 * - Proper cleanup on destroy
 * 
 * Requirements: 2.1, 2.3, 2.4, 7.3
 */

import EditorJS from '@editorjs/editorjs';
import { editorTools } from './editor-tools.js';

/**
 * EditorManager class handles Editor.js lifecycle
 */
export class EditorManager {
    /**
     * Create a new EditorManager instance
     * 
     * @param {string} holderId - The ID of the DOM element to hold the editor
     * @param {Object} config - Additional configuration options
     */
    constructor(holderId, config = {}) {
        this.holderId = holderId;
        this.config = config;
        this.editor = null;
        this.ready = false;
    }

    /**
     * Initialize the Editor.js instance
     * 
     * @param {Object|null} data - Editor.js JSON data to load (optional)
     * @returns {Promise<void>}
     */
    async init(data = null) {
        try {
            const editorConfig = {
                holder: this.holderId,
                tools: editorTools,
                data: data || undefined,
                placeholder: this.config.placeholder || 'Start writing your content...',
                minHeight: this.config.minHeight || 200,
                ...this.config
            };

            this.editor = new EditorJS(editorConfig);
            await this.editor.isReady;
            this.ready = true;
        } catch (error) {
            console.error('Failed to initialize Editor.js:', error);
            this.ready = false;
            throw error;
        }
    }

    /**
     * Save the current editor content
     * 
     * @returns {Promise<Object>} Editor.js JSON output
     */
    async save() {
        if (!this.isReady()) {
            throw new Error('Editor is not initialized');
        }

        try {
            const outputData = await this.editor.save();
            return outputData;
        } catch (error) {
            console.error('Failed to save editor content:', error);
            throw error;
        }
    }

    /**
     * Destroy the Editor.js instance and clean up resources
     */
    destroy() {
        if (this.editor) {
            try {
                this.editor.destroy();
                this.editor = null;
                this.ready = false;
            } catch (error) {
                console.error('Failed to destroy editor:', error);
            }
        }
    }

    /**
     * Check if the editor is ready for use
     * 
     * @returns {boolean} True if editor is initialized and ready
     */
    isReady() {
        return this.ready && this.editor !== null;
    }
}
