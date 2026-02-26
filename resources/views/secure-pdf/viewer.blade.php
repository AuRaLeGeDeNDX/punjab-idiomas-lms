<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $content->title }} - Secure PDF Viewer</title>
    
    <!-- PDF.js Library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: #2c3e50;
            color: #fff;
            overflow: hidden;
            /* Disable text selection */
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /* Disable context menu */
        body {
            -webkit-touch-callout: none;
        }

        #viewer-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        #toolbar {
            background: linear-gradient(180deg, #1f1f1f 0%, #1a1a1a 100%);
            padding: 12px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #444;
            flex-shrink: 0;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
        }

        #toolbar-left, #toolbar-center, #toolbar-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .toolbar-button {
            background: linear-gradient(135deg, #3a3a3a 0%, #2d2d2d 100%);
            border: 1px solid #555;
            color: #fff;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            position: relative;
            overflow: hidden;
        }

        .toolbar-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .toolbar-button:hover:not(:disabled)::before {
            width: 300px;
            height: 300px;
        }

        .toolbar-button:hover:not(:disabled) {
            background: linear-gradient(135deg, #4a4a4a 0%, #3d3d3d 100%);
            border-color: #666;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            transform: translateY(-1px);
        }

        .toolbar-button:active:not(:disabled) {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        .toolbar-button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
            background: #2a2a2a;
            box-shadow: none;
        }

        #page-info {
            color: #ccc;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 500;
        }

        #page-input {
            background: linear-gradient(135deg, #3a3a3a 0%, #2d2d2d 100%);
            border: 1px solid #555;
            color: #fff;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 14px;
            width: 60px;
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        #page-input:hover {
            border-color: #666;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.3);
        }

        #page-input:focus {
            outline: none;
            border-color: #777;
            box-shadow: 0 0 0 3px rgba(119, 119, 119, 0.2);
            background: linear-gradient(135deg, #4a4a4a 0%, #3d3d3d 100%);
        }

        #zoom-select {
            background: linear-gradient(135deg, #3a3a3a 0%, #2d2d2d 100%);
            border: 1px solid #555;
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }

        #zoom-select:hover {
            background: linear-gradient(135deg, #4a4a4a 0%, #3d3d3d 100%);
            border-color: #666;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }

        #zoom-select:focus {
            outline: none;
            border-color: #777;
            box-shadow: 0 0 0 3px rgba(119, 119, 119, 0.2);
        }

        #pdf-canvas-container {
            flex: 1;
            overflow: auto;
            background: #2c3e50;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px;
            position: relative;
        }

        #pdf-canvas {
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.6);
            max-width: 100%;
            height: auto;
            transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1), 
                        transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 4px;
        }

        #pdf-canvas.rendering {
            opacity: 0.6;
            transform: scale(0.98);
        }

        #loading-indicator {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: linear-gradient(135deg, rgba(26, 26, 26, 0.95) 0%, rgba(20, 20, 20, 0.95) 100%);
            padding: 40px 60px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .spinner {
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top: 4px solid #fff;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 0.8s cubic-bezier(0.5, 0, 0.5, 1) infinite;
            margin: 0 auto 20px;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        #loading-indicator > div:last-child {
            font-size: 16px;
            font-weight: 500;
            color: #fff;
            letter-spacing: 0.5px;
        }

        #watermark-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .watermark-logo {
            width: 45%;
            max-width: 400px;
            min-width: 150px;
            height: auto;
            opacity: 0.15;
            pointer-events: none;
            filter: grayscale(20%);
            transform: rotate(-35deg);
        }

        .security-notice {
            background: #ff6b6b;
            color: #fff;
            padding: 10px 20px;
            text-align: center;
            font-size: 13px;
            border-bottom: 1px solid #ff5252;
        }

        .document-title {
            font-size: 16px;
            font-weight: 600;
            color: #fff;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }

        /* Hide default PDF.js toolbar and UI elements */
        #toolbarViewerLeft,
        #toolbarViewerRight,
        #toolbarViewerMiddle,
        #toolbarContainer,
        #secondaryToolbar,
        #sidebarContainer,
        .toolbar,
        .findbar,
        #viewerContainer > .toolbar {
            display: none !important;
            visibility: hidden !important;
        }

        /* Ensure PDF.js viewer elements don't interfere */
        .pdfViewer .page {
            border: none !important;
        }

        /* ============================================
           RESPONSIVE DESIGN - Desktop and Tablet
           Requirement 5.7: Responsive viewport adaptation
           ============================================ */

        /* Large Desktop (1920px and above) */
        @media screen and (min-width: 1920px) {
            #toolbar {
                padding: 15px 40px;
            }

            #pdf-canvas-container {
                padding: 40px;
            }

            .watermark-logo {
                max-width: 500px;
            }
        }

        /* Standard Desktop (1200px - 1919px) */
        @media screen and (min-width: 1200px) and (max-width: 1919px) {
            #toolbar {
                padding: 12px 30px;
            }

            #pdf-canvas-container {
                padding: 30px;
            }

            .watermark-logo {
                max-width: 400px;
            }
        }

        /* Small Desktop / Large Tablet Landscape (992px - 1199px) */
        @media screen and (min-width: 992px) and (max-width: 1199px) {
            #toolbar {
                padding: 12px 20px;
            }

            #toolbar-left, #toolbar-center, #toolbar-right {
                gap: 12px;
            }

            .toolbar-button {
                padding: 8px 14px;
                font-size: 13px;
            }

            #pdf-canvas-container {
                padding: 20px;
            }

            .watermark-logo {
                max-width: 350px;
            }

            .document-title {
                font-size: 15px;
            }
        }

        /* Tablet Portrait / Small Laptop (768px - 991px) */
        @media screen and (min-width: 768px) and (max-width: 991px) {
            #toolbar {
                padding: 10px 15px;
                flex-wrap: wrap;
                gap: 10px;
            }

            #toolbar-left {
                width: 100%;
                justify-content: center;
                order: 1;
            }

            #toolbar-center {
                order: 2;
                gap: 10px;
            }

            #toolbar-right {
                order: 3;
                gap: 10px;
            }

            .toolbar-button {
                padding: 7px 12px;
                font-size: 12px;
            }

            #pdf-canvas-container {
                padding: 15px;
            }

            .watermark-logo {
                max-width: 300px;
            }

            .document-title {
                font-size: 14px;
            }

            .security-notice {
                font-size: 12px;
                padding: 8px 15px;
            }

            /* Adjust zoom select for smaller screens */
            #zoom-select {
                padding: 7px 10px;
                font-size: 12px;
            }
        }

        /* Small Tablet (600px - 767px) */
        @media screen and (max-width: 767px) {
            #toolbar {
                padding: 8px 10px;
                flex-direction: column;
                gap: 8px;
            }

            #toolbar-left, #toolbar-center, #toolbar-right {
                width: 100%;
                justify-content: center;
                gap: 8px;
            }

            .toolbar-button {
                padding: 6px 10px;
                font-size: 11px;
            }

            #pdf-canvas-container {
                padding: 10px;
            }

            .watermark-logo {
                max-width: 220px;
            }

            .document-title {
                font-size: 13px;
                text-align: center;
            }

            .security-notice {
                font-size: 11px;
                padding: 6px 10px;
            }

            #page-input {
                width: 50px;
                padding: 5px 8px;
                font-size: 12px;
            }

            #zoom-select {
                padding: 6px 8px;
                font-size: 11px;
            }

            /* Stack navigation buttons vertically on very small screens */
            #toolbar-center {
                flex-wrap: wrap;
            }
        }

        /* Landscape orientation adjustments */
        @media screen and (orientation: landscape) and (max-height: 600px) {
            #toolbar {
                padding: 8px 15px;
            }

            #pdf-canvas-container {
                padding: 10px;
            }

            .security-notice {
                padding: 6px 15px;
                font-size: 11px;
            }

            /* Reduce watermark size in landscape to avoid obstruction */
            .watermark-logo {
                max-width: 280px;
                opacity: 0.12;
            }
        }

        /* High DPI / Retina Display adjustments */
        @media screen and (-webkit-min-device-pixel-ratio: 2),
               screen and (min-resolution: 192dpi) {
            .watermark-logo {
                image-rendering: -webkit-optimize-contrast;
            }

            #pdf-canvas {
                image-rendering: -webkit-optimize-contrast;
                image-rendering: crisp-edges;
            }
        }

        /* Ensure full-width layout adapts to viewport */
        @media screen and (max-width: 1199px) {
            #viewer-container {
                width: 100%;
                max-width: 100%;
            }

            #pdf-canvas {
                max-width: 100%;
                width: auto;
            }
        }

        /* Print media query (block printing) */
        @media print {
            body {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <!-- Security Notice -->
    <div class="security-notice">
        🔒 This document is protected. Downloading, printing, and copying are disabled for security.
    </div>

    <!-- Viewer Container -->
    <div id="viewer-container">
        <!-- Toolbar -->
        <div id="toolbar">
            <div id="toolbar-left">
                <span class="document-title">{{ $content->title }}</span>
            </div>
            
            <div id="toolbar-center">
                <button class="toolbar-button" id="prev-page" disabled>← Previous</button>
                <span id="page-info">
                    Page 
                    <input type="number" id="page-input" min="1" value="1" />
                    of <span id="total-pages">--</span>
                </span>
                <button class="toolbar-button" id="next-page" disabled>Next →</button>
            </div>
            
            <div id="toolbar-right">
                <select id="zoom-select" class="toolbar-button">
                    <option value="0.5">50%</option>
                    <option value="0.75">75%</option>
                    <option value="1" selected>100%</option>
                    <option value="1.25">125%</option>
                    <option value="1.5">150%</option>
                    <option value="2">200%</option>
                    <option value="fit">Fit Width</option>
                </select>
                <button class="toolbar-button" id="zoom-in">Zoom In</button>
                <button class="toolbar-button" id="zoom-out">Zoom Out</button>
            </div>
        </div>

        <!-- PDF Canvas Container -->
        <div id="pdf-canvas-container">
            <canvas id="pdf-canvas"></canvas>
            <!-- Watermark Overlay (inside canvas container to clip to PDF area) -->
            <div id="watermark-overlay"></div>
        </div>

        <!-- Loading Indicator -->
        <div id="loading-indicator">
            <div class="spinner"></div>
            <div>Loading PDF...</div>
        </div>
    </div>

    <script>
        // Configuration
        // Use JSON encoding to prevent HTML entity encoding of & characters in the signed URL
        // Blade's double-brace syntax HTML-encodes & to &amp; which breaks signature validation
        const PDF_URL = @json($pdfDataUrl ?? '');
        const SESSION_TOKEN = '{{ $sessionToken ?? '' }}';
        const CONTENT_ID = {{ $content->id ?? 0 }};
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

        // Watermark data
        const WATERMARK_DATA = {
            userName: @json($user['name'] ?? 'Unknown User'),
            userEmail: @json($user['email'] ?? ''),
            timestamp: @json($user['timestamp'] ?? ''),
            userIp: @json($user['ip'] ?? '')
        };

        // PDF.js Configuration
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        
        // Verify PDF.js loaded correctly
        console.log('PDF.js version:', pdfjsLib.version);
        console.log('PDF.js worker source:', pdfjsLib.GlobalWorkerOptions.workerSrc);
        console.log('PDF URL to load:', PDF_URL);
        console.log('Content ID:', CONTENT_ID);
        
        // Check if PDF_URL is valid
        if (!PDF_URL || PDF_URL === '') {
            console.error('CRITICAL: PDF_URL is empty or undefined!');
            displayError(
                'Configuration Error',
                'The PDF URL is missing. Please refresh the page.',
                true
            );
        }

        // State
        let pdfDoc = null;
        let currentPage = 1;
        let totalPages = 0;
        let currentScale = 1.0;
        let rendering = false;

        // DOM Elements
        const canvas = document.getElementById('pdf-canvas');
        const ctx = canvas.getContext('2d');
        const loadingIndicator = document.getElementById('loading-indicator');
        const prevButton = document.getElementById('prev-page');
        const nextButton = document.getElementById('next-page');
        const pageInput = document.getElementById('page-input');
        const totalPagesSpan = document.getElementById('total-pages');
        const zoomSelect = document.getElementById('zoom-select');
        const zoomInButton = document.getElementById('zoom-in');
        const zoomOutButton = document.getElementById('zoom-out');

        // ============================================
        // WATERMARK RENDERING (Logo Watermark)
        // ============================================

        /**
         * Render a centered logo watermark over the PDF viewer
         */
        function renderWatermark() {
            const overlay = document.getElementById('watermark-overlay');
            
            // Clear existing watermarks
            overlay.innerHTML = '';
            
            // Create logo watermark image
            const logo = document.createElement('img');
            logo.src = '/images/logo.png';
            logo.className = 'watermark-logo';
            logo.alt = '';
            logo.draggable = false;
            
            overlay.appendChild(logo);
            
            console.log('Logo watermark rendered');
        }

        // ============================================
        // ANTI-DOWNLOAD PROTECTIONS
        // Requirements: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7
        // ============================================

        /**
         * Disable right-click context menu
         * Requirement 3.1: Block right-click to prevent context menu
         */
        document.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            console.warn('Right-click is disabled on this secure viewer');
            return false;
        });

        /**
         * Disable keyboard shortcuts
         * Requirements 3.2, 3.3, 3.4: Block Ctrl+S, Ctrl+P, Ctrl+C
         */
        document.addEventListener('keydown', (e) => {
            // Disable Ctrl+S (Save) - Requirement 3.2
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                alert('Downloading is disabled for this document.');
                console.warn('Download attempt blocked: Ctrl+S');
                return false;
            }
            
            // Disable Ctrl+P (Print) - Requirement 3.3
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                alert('Printing is disabled for this document.');
                console.warn('Print attempt blocked: Ctrl+P');
                return false;
            }
            
            // Disable Ctrl+C (Copy) - Requirement 3.4
            if ((e.ctrlKey || e.metaKey) && e.key === 'c') {
                e.preventDefault();
                alert('Copying is disabled for this document.');
                console.warn('Copy attempt blocked: Ctrl+C');
                return false;
            }
            
            // Disable F12 (DevTools) - Note: This can be bypassed
            if (e.key === 'F12') {
                e.preventDefault();
                console.warn('Developer tools access attempt detected');
                return false;
            }
        });

        /**
         * Disable text selection on canvas
         * Requirement 3.6: Prevent text selection
         */
        canvas.style.userSelect = 'none';
        canvas.style.webkitUserSelect = 'none';
        canvas.style.mozUserSelect = 'none';
        canvas.style.msUserSelect = 'none';

        /**
         * Prevent drag and drop
         * Requirement 3.5: Block drag-and-drop operations
         */
        canvas.addEventListener('dragstart', (e) => {
            e.preventDefault();
            console.warn('Drag-and-drop is disabled on this secure viewer');
            return false;
        });

        /**
         * Developer tools detection
         * Requirement 3.7: Detect and warn about developer tools usage
         * 
         * Note: This is a heuristic detection method that checks for unusual
         * window dimensions which may indicate DevTools is open. It can be
         * bypassed but serves as a deterrent and logging mechanism.
         */
        let devToolsWarningShown = false;
        
        function detectDevTools() {
            const threshold = 160;
            const widthDiff = window.outerWidth - window.innerWidth;
            const heightDiff = window.outerHeight - window.innerHeight;
            
            // Check if window dimensions suggest DevTools is open
            if (widthDiff > threshold || heightDiff > threshold) {
                if (!devToolsWarningShown) {
                    console.warn('⚠️ SECURITY WARNING: Developer tools detected. Unauthorized download attempts are logged and may result in access revocation.');
                    devToolsWarningShown = true;
                    
                    // Log to server
                    fetch('/secure-pdf/log-devtools-detection/{{ $content->id }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                        },
                        body: JSON.stringify({
                            session_token: SESSION_TOKEN,
                            timestamp: new Date().toISOString(),
                        }),
                    }).catch(error => {
                        console.error('Error logging DevTools detection:', error);
                    });
                }
            } else {
                // Reset warning flag if DevTools is closed
                devToolsWarningShown = false;
            }
        }
        
        // Check for DevTools every second
        setInterval(detectDevTools, 1000);
        
        // Initial check
        detectDevTools();

        // ============================================
        // PDF LOADING AND RENDERING
        // ============================================

        // ============================================
        // ERROR HANDLING
        // Requirements 6.3, 6.4: Enhanced error handling with user-friendly messages
        // ============================================

        /**
         * Display user-friendly error message
         * Requirement 6.4: Display meaningful error messages
         */
        function displayError(message, details, canRetry = false) {
            const errorHtml = `
                <div style="color: #ff6b6b; text-align: center; max-width: 500px; margin: 0 auto;">
                    <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
                    <div style="font-size: 18px; font-weight: 600; margin-bottom: 15px;">${message}</div>
                    <div style="font-size: 14px; color: #ccc; margin-bottom: 20px;">${details}</div>
                    ${canRetry ? '<button id="retry-button" class="toolbar-button" style="margin-top: 10px;">Retry Loading</button>' : ''}
                    <div style="font-size: 12px; color: #999; margin-top: 20px;">
                        If the problem persists, please contact support.
                    </div>
                </div>
            `;
            
            loadingIndicator.innerHTML = errorHtml;
            loadingIndicator.style.display = 'flex';
            
            // Add retry button handler if applicable
            if (canRetry) {
                const retryButton = document.getElementById('retry-button');
                if (retryButton) {
                    retryButton.addEventListener('click', () => {
                        loadingIndicator.innerHTML = '<div class="spinner"></div><div>Retrying...</div>';
                        setTimeout(() => loadPDF(), 500);
                    });
                }
            }
        }

        /**
         * Extract detailed error information from PDF.js error object
         * PDF.js errors are often complex objects that need special handling
         */
        function extractErrorDetails(error) {
            const details = {
                message: 'Unknown error',
                name: 'UnknownError',
                type: 'unknown',
                technical: {}
            };
            
            // Handle different error types
            if (typeof error === 'string') {
                details.message = error;
                details.technical.raw = error;
            } else if (error instanceof Error) {
                details.message = error.message || 'Unknown error';
                details.name = error.name || 'Error';
                details.technical.stack = error.stack;
            } else if (typeof error === 'object' && error !== null) {
                // PDF.js often returns plain objects with error info
                details.message = error.message || error.msg || error.toString();
                details.name = error.name || 'PDFError';
                
                // Extract all properties from the error object
                for (const key in error) {
                    if (error.hasOwnProperty(key)) {
                        details.technical[key] = error[key];
                    }
                }
            }
            
            return details;
        }
        
        /**
         * Log error to server with full context
         * Requirement 6.3: Log errors with full context
         */
        function logError(errorType, errorMessage, errorDetails) {
            const errorData = {
                error_type: errorType,
                error_message: errorMessage,
                error_details: errorDetails,
                content_id: CONTENT_ID,
                session_token: SESSION_TOKEN,
                pdf_url: PDF_URL,
                user_agent: navigator.userAgent,
                viewport: {
                    width: window.innerWidth,
                    height: window.innerHeight
                },
                timestamp: new Date().toISOString()
            };
            
            // Log to console for debugging
            console.error('PDF Error:', errorData);
            
            // Send to server
            fetch('/secure-pdf/log-error/{{ $content->id }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify(errorData),
            }).catch(err => {
                console.error('Error logging to server:', err);
            });
        }

        /**
         * Categorize PDF.js error and determine if it's retryable
         * Requirement 6.3: Catch PDF.js errors and categorize them
         */
        function categorizeError(error) {
            const errorInfo = {
                type: 'unknown',
                message: 'An unexpected error occurred',
                details: 'Please try refreshing the page.',
                canRetry: false,
                technicalDetails: error.toString()
            };
            
            // Check error name and message for specific error types
            const errorString = error.toString().toLowerCase();
            const errorName = error.name ? error.name.toLowerCase() : '';
            const errorMessage = error.message ? error.message.toLowerCase() : '';
            
            console.log('Categorizing error:', {
                errorString,
                errorName,
                errorMessage,
                errorObject: error
            });
            
            // PDF.js specific errors
            if (errorName === 'passwordexception' || errorString.includes('password')) {
                errorInfo.type = 'password_protected';
                errorInfo.message = 'Password Protected PDF';
                errorInfo.details = 'This PDF is password protected and cannot be viewed in the browser.';
                errorInfo.canRetry = false;
            }
            // PDF.js parsing errors (most common issue)
            else if (errorName === 'invalidpdferror' || 
                     errorString.includes('invalid pdf') || 
                     errorString.includes('corrupt') ||
                     errorString.includes('parse') ||
                     errorString.includes('pdf.js') ||
                     errorString.includes('[object') ||
                     errorString === 'object') {
                errorInfo.type = 'pdf_parse_error';
                errorInfo.message = 'PDF Loading Error';
                errorInfo.details = 'The PDF could not be loaded. This may be due to browser compatibility or PDF format issues. Try refreshing the page or using a different browser.';
                errorInfo.canRetry = true;
            }
            // Network errors (retryable)
            else if (errorString.includes('network') || 
                errorString.includes('fetch') || 
                errorString.includes('connection') ||
                errorName === 'networkerror') {
                errorInfo.type = 'network';
                errorInfo.message = 'Network Connection Error';
                errorInfo.details = 'Unable to connect to the server. Please check your internet connection.';
                errorInfo.canRetry = true;
            }
            // 403 Forbidden errors
            else if (errorString.includes('403') || 
                     errorString.includes('forbidden') ||
                     errorString.includes('signature')) {
                errorInfo.type = 'forbidden';
                errorInfo.message = 'Access Denied';
                errorInfo.details = 'Your access link may have expired. Please refresh the page to get a new link.';
                errorInfo.canRetry = true;
            }
            // 404 Not Found errors
            else if (errorString.includes('404') || 
                     errorString.includes('not found')) {
                errorInfo.type = 'not_found';
                errorInfo.message = 'Document Not Found';
                errorInfo.details = 'The requested document could not be found on the server.';
                errorInfo.canRetry = false;
            }
            // 204 No Content errors (server returned empty response)
            else if (errorString.includes('204') || 
                     errorString.includes('no content') ||
                     errorString.includes('unexpected server response (204)')) {
                errorInfo.type = 'no_content';
                errorInfo.message = 'Empty Server Response';
                errorInfo.details = 'The server returned an empty response. This may be due to a middleware or routing issue. Please refresh the page or contact support if the problem persists.';
                errorInfo.canRetry = true;
                errorInfo.technicalDetails = 'HTTP 204 No Content - Server processed request but returned no data. Check Laravel logs and middleware configuration.';
            }
            // Timeout errors (retryable)
            else if (errorString.includes('timeout') || 
                     errorString.includes('timed out')) {
                errorInfo.type = 'timeout';
                errorInfo.message = 'Request Timeout';
                errorInfo.details = 'The server took too long to respond. Please try again.';
                errorInfo.canRetry = true;
            }
            // Server errors (retryable)
            else if (errorString.includes('500') || 
                     errorString.includes('502') ||
                     errorString.includes('503') ||
                     errorString.includes('server error')) {
                errorInfo.type = 'server_error';
                errorInfo.message = 'Server Error';
                errorInfo.details = 'The server encountered an error. Please try again in a moment.';
                errorInfo.canRetry = true;
            }
            // Memory errors
            else if (errorString.includes('memory') || 
                     errorString.includes('out of memory')) {
                errorInfo.type = 'memory';
                errorInfo.message = 'Memory Error';
                errorInfo.details = 'Your browser ran out of memory. Try closing other tabs and refreshing.';
                errorInfo.canRetry = true;
            }
            // CORS errors
            else if (errorString.includes('cors') || 
                     errorString.includes('cross-origin')) {
                errorInfo.type = 'cors';
                errorInfo.message = 'Cross-Origin Error';
                errorInfo.details = 'The PDF could not be loaded due to browser security restrictions. Please refresh the page.';
                errorInfo.canRetry = true;
            }
            
            return errorInfo;
        }

        // Load PDF with enhanced error handling
        async function loadPDF() {
            try {
                console.log('=== PDF Load Attempt ===');
                console.log('PDF_URL:', PDF_URL);
                console.log('PDF_URL length:', PDF_URL.length);
                console.log('PDF_URL type:', typeof PDF_URL);
                
                // Test the URL with a simple fetch first to see what response we get
                console.log('Testing URL with fetch...');
                try {
                    const testResponse = await fetch(PDF_URL, {
                        method: 'HEAD',  // HEAD request to check without downloading
                        credentials: 'omit'  // Don't send cookies
                    });
                    console.log('Fetch test response status:', testResponse.status);
                    console.log('Fetch test response headers:', [...testResponse.headers.entries()]);
                    
                    if (testResponse.status === 204) {
                        console.error('❌ CRITICAL: Server returned 204 No Content on HEAD request!');
                        throw new Error('Server returned 204 No Content. The PDF stream endpoint is not returning data. Check Laravel logs and middleware configuration.');
                    }
                    
                    if (testResponse.status !== 200) {
                        console.warn('⚠️ Unexpected status code:', testResponse.status);
                    }
                } catch (fetchError) {
                    console.error('Fetch test failed:', fetchError);
                    // Continue anyway - PDF.js might handle it differently
                }
                
                console.log('Starting PDF.js load...');
                
                // Configure PDF.js to load the signed URL without credentials
                // Requirements 2.1, 2.2, 6.1: Pass signed URL without modification
                // withCredentials: false ensures cookies/session aren't sent
                // This allows the signed URL to work independently of session state
                //
                // Enhanced configuration for large PDF files:
                // - disableAutoFetch: Load pages on-demand instead of all at once
                // - disableStream: Disable streaming for better compatibility
                // - disableRange: Disable range requests for simpler loading
                const loadingTask = pdfjsLib.getDocument({
                    url: PDF_URL,
                    withCredentials: false,
                    disableAutoFetch: true,  // Load pages on-demand (better for large files)
                    disableStream: false,     // Keep streaming enabled for range requests
                    disableRange: false,      // Keep range requests enabled
                    maxImageSize: -1,         // No image size limit
                    isEvalSupported: false,   // Disable eval for security
                    cMapUrl: 'https://cdn.jsdelivr.net/npm/pdfjs-dist@3.11.174/cmaps/',
                    cMapPacked: true
                });
                
                // Handle loading progress
                loadingTask.onProgress = function(progress) {
                    if (progress.total > 0) {
                        const percent = Math.round((progress.loaded / progress.total) * 100);
                        const loadingText = loadingIndicator.querySelector('div:last-child');
                        if (loadingText) {
                            const sizeMB = (progress.total / 1024 / 1024).toFixed(2);
                            loadingText.textContent = `Loading PDF... ${percent}% (${sizeMB} MB)`;
                        }
                        console.log(`PDF loading progress: ${percent}%`);
                    }
                };
                
                console.log('Waiting for PDF.js to load document...');
                pdfDoc = await loadingTask.promise;
                console.log('PDF.js returned document object:', pdfDoc);
                
                // SAFETY CHECK: Verify pdfDoc is valid and has pages
                if (!pdfDoc || !pdfDoc.numPages || pdfDoc.numPages === 0) {
                    throw new Error('PDF document loaded but is invalid or has no pages');
                }
                
                totalPages = pdfDoc.numPages;
                console.log('PDF loaded successfully:', totalPages, 'pages');
                
                totalPagesSpan.textContent = totalPages;
                pageInput.max = totalPages;
                loadingIndicator.style.display = 'none';
                
                // Render first page
                await renderPage(1);
                
                // Enable navigation buttons
                updateNavigationButtons();
                
            } catch (error) {
                // Ensure pdfDoc is null on error to prevent null reference errors
                pdfDoc = null;
                
                console.error('PDF loading failed:', error);
                console.error('Error name:', error.name);
                console.error('Error message:', error.message);
                console.error('Error stack:', error.stack);
                
                // Categorize the error
                const errorInfo = categorizeError(error);
                
                // Log error with full context
                logError(errorInfo.type, errorInfo.message, {
                    details: errorInfo.details,
                    technical: errorInfo.technicalDetails,
                    can_retry: errorInfo.canRetry,
                    error_name: error.name,
                    error_message: error.message,
                    error_stack: error.stack ? error.stack.substring(0, 500) : 'No stack trace'
                });
                
                // Display user-friendly error message
                displayError(errorInfo.message, errorInfo.details, errorInfo.canRetry);
            }
        }

        // Render specific page with enhanced error handling
        async function renderPage(pageNum) {
            if (rendering) return;

            // NULL SAFETY: Check if pdfDoc is loaded before attempting to render
            if (!pdfDoc) {
                console.error('Cannot render page: PDF document not loaded');
                displayError(
                    'PDF Not Loaded',
                    'The PDF document failed to load. Please refresh the page to try again.',
                    true
                );
                return;
            }

            rendering = true;

            // Add smooth transition effect
            canvas.classList.add('rendering');

            try {
                const page = await pdfDoc.getPage(pageNum);
                
                // Calculate scale with responsive adjustments
                let viewport;
                if (zoomSelect.value === 'fit') {
                    // Responsive container width calculation
                    const container = document.getElementById('pdf-canvas-container');
                    const containerWidth = container.clientWidth - 40;
                    
                    // Adjust padding based on viewport size
                    let paddingAdjustment = 40;
                    if (window.innerWidth < 768) {
                        paddingAdjustment = 20;
                    } else if (window.innerWidth < 992) {
                        paddingAdjustment = 30;
                    }
                    
                    const effectiveWidth = container.clientWidth - paddingAdjustment;
                    const pageViewport = page.getViewport({ scale: 1.0 });
                    currentScale = effectiveWidth / pageViewport.width;
                    viewport = page.getViewport({ scale: currentScale });
                } else {
                    // Apply responsive scale adjustments for fixed zoom levels
                    let responsiveScale = currentScale;
                    
                    // On smaller screens, slightly reduce the scale to ensure fit
                    if (window.innerWidth < 768 && currentScale > 1.0) {
                        responsiveScale = currentScale * 0.9;
                    } else if (window.innerWidth < 992 && currentScale > 1.5) {
                        responsiveScale = currentScale * 0.95;
                    }
                    
                    viewport = page.getViewport({ scale: responsiveScale });
                }

                // Set canvas dimensions
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                // Render page
                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };

                await page.render(renderContext).promise;
                
                currentPage = pageNum;
                pageInput.value = currentPage;
                
                // Log page view
                logPageView(currentPage, totalPages);
                
                // Update navigation
                updateNavigationButtons();
                
                // Remove transition effect
                canvas.classList.remove('rendering');
                
                rendering = false;
            } catch (error) {
                console.error('Error rendering page:', error);
                canvas.classList.remove('rendering');
                rendering = false;
                
                // Log rendering error
                logError('rendering_error', 'Failed to render page', {
                    page_number: pageNum,
                    error_message: error.toString(),
                    current_scale: currentScale,
                    viewport_size: {
                        width: window.innerWidth,
                        height: window.innerHeight
                    }
                });
                
                // Show error message on canvas
                ctx.fillStyle = '#2c3e50';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
                ctx.fillStyle = '#ff6b6b';
                ctx.font = '16px Arial';
                ctx.textAlign = 'center';
                ctx.fillText('Failed to render page ' + pageNum, canvas.width / 2, canvas.height / 2);
                ctx.fillStyle = '#ccc';
                ctx.font = '14px Arial';
                ctx.fillText('Please try navigating to another page', canvas.width / 2, canvas.height / 2 + 30);
            }
        }

        // Update navigation button states
        function updateNavigationButtons() {
            prevButton.disabled = currentPage <= 1;
            nextButton.disabled = currentPage >= totalPages;
        }

        // ============================================
        // NAVIGATION CONTROLS
        // ============================================

        prevButton.addEventListener('click', () => {
            if (currentPage > 1) {
                // Add visual feedback
                prevButton.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    prevButton.style.transform = 'scale(1)';
                }, 100);
                
                renderPage(currentPage - 1);
            }
        });

        nextButton.addEventListener('click', () => {
            if (currentPage < totalPages) {
                // Add visual feedback
                nextButton.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    nextButton.style.transform = 'scale(1)';
                }, 100);
                
                renderPage(currentPage + 1);
            }
        });

        // Page input handler
        pageInput.addEventListener('change', () => {
            let pageNum = parseInt(pageInput.value);
            
            // Validate page number
            if (isNaN(pageNum) || pageNum < 1) {
                pageNum = 1;
            } else if (pageNum > totalPages) {
                pageNum = totalPages;
            }
            
            // Update input value to validated number
            pageInput.value = pageNum;
            
            // Render the requested page
            if (pageNum !== currentPage) {
                renderPage(pageNum);
            }
        });

        // Handle Enter key in page input
        pageInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                pageInput.blur(); // Trigger change event
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            // Don't interfere with page input
            if (document.activeElement === pageInput) {
                return;
            }
            
            if (e.key === 'ArrowLeft' && currentPage > 1) {
                renderPage(currentPage - 1);
            } else if (e.key === 'ArrowRight' && currentPage < totalPages) {
                renderPage(currentPage + 1);
            }
        });

        // ============================================
        // ZOOM CONTROLS
        // ============================================

        zoomSelect.addEventListener('change', () => {
            // Add visual feedback
            zoomSelect.style.transform = 'scale(0.95)';
            setTimeout(() => {
                zoomSelect.style.transform = 'scale(1)';
            }, 100);
            
            if (zoomSelect.value === 'fit') {
                renderPage(currentPage);
            } else {
                currentScale = parseFloat(zoomSelect.value);
                renderPage(currentPage);
            }
        });

        zoomInButton.addEventListener('click', () => {
            // Add visual feedback
            zoomInButton.style.transform = 'scale(0.95)';
            setTimeout(() => {
                zoomInButton.style.transform = 'scale(1)';
            }, 100);
            
            // Find current zoom level in select options
            const currentValue = parseFloat(zoomSelect.value);
            const options = Array.from(zoomSelect.options).map(opt => parseFloat(opt.value)).filter(v => !isNaN(v));
            
            // Find next higher zoom level
            const nextZoom = options.find(v => v > currentValue) || Math.min(currentScale + 0.25, 3.0);
            
            currentScale = nextZoom;
            zoomSelect.value = currentScale;
            renderPage(currentPage);
        });

        zoomOutButton.addEventListener('click', () => {
            // Add visual feedback
            zoomOutButton.style.transform = 'scale(0.95)';
            setTimeout(() => {
                zoomOutButton.style.transform = 'scale(1)';
            }, 100);
            
            // Find current zoom level in select options
            const currentValue = parseFloat(zoomSelect.value);
            const options = Array.from(zoomSelect.options).map(opt => parseFloat(opt.value)).filter(v => !isNaN(v)).reverse();
            
            // Find next lower zoom level
            const nextZoom = options.find(v => v < currentValue) || Math.max(currentScale - 0.25, 0.5);
            
            currentScale = nextZoom;
            zoomSelect.value = currentScale;
            renderPage(currentPage);
        });

        // ============================================
        // LOGGING
        // ============================================

        // Log page view to server
        function logPageView(pageNumber, totalPages) {
            fetch('/secure-pdf/log-page-view/{{ $content->id }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN,
                },
                body: JSON.stringify({
                    page_number: pageNumber,
                    total_pages: totalPages,
                    session_token: SESSION_TOKEN,
                }),
            }).catch(error => {
                console.error('Error logging page view:', error);
            });
        }

        // ============================================
        // INITIALIZATION
        // ============================================

        // Render watermark on page load
        renderWatermark();

        // Re-render watermark on window resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            // Re-render watermark immediately
            renderWatermark();
            
            // Debounce PDF re-rendering to avoid excessive renders
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => {
                // Re-render current page if in fit mode or on significant size change
                if (zoomSelect.value === 'fit' || Math.abs(window.innerWidth - lastViewportWidth) > 100) {
                    renderPage(currentPage);
                    lastViewportWidth = window.innerWidth;
                }
            }, 300);
        });

        // Track viewport width for resize detection
        let lastViewportWidth = window.innerWidth;

        // Load PDF on page load
        loadPDF();

        // Warn user before leaving
        window.addEventListener('beforeunload', (e) => {
            e.preventDefault();
            e.returnValue = '';
        });
    </script>
</body>
</html>
