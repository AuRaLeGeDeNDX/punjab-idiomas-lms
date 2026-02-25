/**
 * Editor.js Tools Configuration
 * 
 * This module defines the tools available in the Editor.js instance.
 * Tools include: Header, List, Quote, Code, and Paragraph (default).
 * 
 * Requirements: 2.2, 7.1, 7.2
 */

import Header from '@editorjs/header';
import List from '@editorjs/list';
import Quote from '@editorjs/quote';
import Paragraph from '@editorjs/paragraph';
import CodeTool from '@editorjs/code';
import ImageTool from '@editorjs/image';
import AttachesTool from '@editorjs/attaches';
import Underline from '@editorjs/underline';
import Marker from '@editorjs/marker';
import TextAlignmentTool from 'editorjs-text-alignment-blocktune';
import { FontSizeTool } from './tools/FontSizeTool.js';
import { FontFamilyTool } from './tools/FontFamilyTool.js';

/**
 * Editor.js tools configuration object
 * Each tool is configured with its class and optional settings
 */
export const editorTools = {
    // Block Tunes
    textAlignment: {
        class: TextAlignmentTool,
        config: {
            default: "left",
            blocks: {
                header: 'center',
                list: 'left'
            }
        },
    },

    // Inline Tools
    underline: Underline,
    marker: Marker,
    fontSize: FontSizeTool,
    fontFamily: FontFamilyTool,

    // Block Tools
    paragraph: {
        class: Paragraph,
        inlineToolbar: true,
        tunes: ['textAlignment'],
        config: {
            placeholder: 'Type something...'
        }
    },
    header: {
        class: Header,
        inlineToolbar: true,
        tunes: ['textAlignment'],
        config: {
            levels: [2, 3, 4],
            defaultLevel: 2
        }
    },
    list: {
        class: List,
        inlineToolbar: true,
        tunes: ['textAlignment']
    },
    quote: {
        class: Quote,
        inlineToolbar: true,
        tunes: ['textAlignment']
    },
    code: {
        class: CodeTool
    },
    image: {
        class: ImageTool,
        tunes: ['textAlignment'],
        config: {
            uploader: {
                /**
                 * Upload file to the server and return an object with expected data
                 * @param {File} file - file selected from the device or pasted by drag-n-drop
                 * @returns {Promise.<{success, file: {url}}>}
                 */
                uploadByFile(file) {
                    const formData = new FormData();
                    formData.append('file', file);
                    // Add is_public flag if needed, or rely on default
                    // formData.append('is_public', 1);

                    // Get CSRF token
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    return fetch('/files/upload', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': token || '',
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                return {
                                    success: 1,
                                    file: {
                                        url: data.file.download_url,
                                        // extended data for our renderer if needed
                                        file_id: data.file.id,
                                        original_name: data.file.original_name
                                    }
                                };
                            }
                            return {
                                success: 0,
                                message: data.error || 'Upload failed'
                            };
                        })
                        .catch(error => {
                            console.error('Image upload failed:', error);
                            return {
                                success: 0,
                                message: 'Network error'
                            };
                        });
                }
            }
        }
    },
    attaches: {
        class: AttachesTool,
        config: {
            uploader: {
                uploadByFile(file) {
                    const formData = new FormData();
                    formData.append('file', file);

                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    return fetch('/files/upload', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': token || '',
                            'Accept': 'application/json'
                        }
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                return {
                                    success: 1,
                                    file: {
                                        url: data.file.download_url,
                                        size: data.file.file_size,
                                        name: data.file.original_name,
                                        extension: data.file.original_name.split('.').pop()
                                    }
                                };
                            }
                            return {
                                success: 0
                            };
                        })
                        .catch(error => {
                            console.error('File upload failed:', error);
                            return { success: 0 };
                        });
                }
            }
        }
    }
};
