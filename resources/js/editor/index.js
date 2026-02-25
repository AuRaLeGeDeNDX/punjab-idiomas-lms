/**
 * Editor.js Module Index
 * 
 * This module provides a clean API for importing Editor.js functionality.
 * It exports all editor functions and classes for use in both Blade and React.
 * 
 * Requirements: 7.4
 */

console.log('Editor Index Loaded');

// Export EditorManager class for editor lifecycle management
export { EditorManager } from './editor-init.js';

// Export tools configuration
export { editorTools } from './editor-tools.js';

// Export rendering and utility functions
export {
    renderEditorContent,
    isEditorJsContent,
    sanitizeHtml
} from './editor-renderer.js';

// Make EditorManager globally available for non-module scripts
import { EditorManager } from './editor-init.js';
import { PageBuilder } from './page-builder.js';
window.EditorManager = EditorManager;
window.PageBuilder = PageBuilder;
