<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
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
            min-height: 100vh;
        }

        @media screen and (min-width: 768px) {
            #viewer-container {
                height: 100vh;
            }
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


        }

        /* Standard Desktop (1200px - 1919px) */
        @media screen and (min-width: 1200px) and (max-width: 1919px) {
            #toolbar {
                padding: 12px 30px;
            }

            #pdf-canvas-container {
                padding: 30px;
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

        /* =============================================
           MOBILE-FIRST REDESIGN (<768px)
           - Hide top toolbar (title is on parent page)
           - Hide security banner (blocks content)
           - Full-width PDF canvas
           - Sticky bottom toolbar with icon buttons
           ============================================= */
        @media screen and (max-width: 767px) {
            /* A) Hide top toolbar entirely */
            #toolbar {
                display: none !important;
            }

            /* B) Hide security banner */
            .security-notice {
                display: none !important;
            }

            /* C) Full-width canvas, clear bottom toolbar */
            #pdf-canvas-container {
                display: block !important;
                height: auto !important;
                min-height: auto !important;
                padding: 5px 0 56px 0 !important; /* Spacing for top/bottom, NO horizontal padding on container */
                background: #f1f3f4 !important;
                overflow: auto !important;
                flex: none !important;
                touch-action: none;
            }

            #pdf-scale-wrapper {
                display: block;
                width: fit-content;
                height: fit-content;
                margin: 0; /* REQUIREMENT: No horizontal centering logic */
                transform-origin: 0 0;
                will-change: transform;
            }

            #pdf-canvas {
                max-width: 100% !important;
                width: auto !important;
                height: auto !important;
                display: block;
                margin: 0 10px 16px 10px !important; /* Apply breathing space via margin on canvas instead */
                border-radius: 6px !important;
                box-shadow: 0 2px 6px rgba(0,0,0,0.15) !important;
                background: white !important;
                transition: none !important;
            }

            /* Remove container shadows and forced heights on mobile */
            #viewer-container {
                display: block !important;
                height: auto !important;
                min-height: auto !important;
                flex: none !important;
                box-shadow: none;
                background: #f1f3f4;
            }

            /* Ensure body/html don't force height/stretching */
            body, html {
                height: auto !important;
                min-height: auto !important;
                overflow: visible !important;
            }



            /* D) Mobile Bottom Toolbar */
            #mobile-toolbar {
                display: flex !important;
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
                height: 56px;
                background: #111;
                justify-content: space-between;
                align-items: center;
                padding: 0 12px;
                z-index: 1000;
                box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.3);
                gap: 4px;
            }

            .mobile-toolbar-group {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            .mobile-toolbar-btn {
                min-width: 44px;
                min-height: 44px;
                border: none;
                background: rgba(255, 255, 255, 0.1);
                color: #fff;
                border-radius: 8px;
                font-size: 18px;
                display: flex;
                align-items: center;
                justify-content: center;
                cursor: pointer;
                -webkit-tap-highlight-color: transparent;
                transition: background 0.15s ease;
            }

            .mobile-toolbar-btn:active {
                background: rgba(255, 255, 255, 0.25);
            }

            .mobile-toolbar-btn:disabled {
                opacity: 0.3;
                cursor: not-allowed;
            }

            .mobile-page-indicator {
                color: #ccc;
                font-size: 14px;
                font-weight: 500;
                white-space: nowrap;
                min-width: 60px;
                text-align: center;
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

        <!-- Mobile-Only Bottom Toolbar (hidden on desktop) -->
        <div id="mobile-toolbar" style="display: none;">
            <div class="mobile-toolbar-group">
                <button class="mobile-toolbar-btn" id="mob-prev" disabled aria-label="Previous page">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M12.5 15L7.5 10L12.5 5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
                <span class="mobile-page-indicator" id="mob-page-info">1 / —</span>
                <button class="mobile-toolbar-btn" id="mob-next" disabled aria-label="Next page">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M7.5 5L12.5 10L7.5 15" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </button>
            </div>
            <div class="mobile-toolbar-group">
                <button class="mobile-toolbar-btn" id="mob-zoom-out" aria-label="Zoom out">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M5 10H15" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                <button class="mobile-toolbar-btn" id="mob-zoom-in" aria-label="Zoom in">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M10 5V15M5 10H15" stroke="white" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
            </div>
        </div>

        <!-- PDF Canvas Container -->
        <div id="pdf-canvas-container" class="pdf-scroll-container">
            <div id="pdf-scale-wrapper" class="pdf-scale-wrapper">
                <canvas id="pdf-canvas"></canvas>
            </div>
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
        let isPinching = false;

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
        let renderTimeout;
        async function renderPage(pageNum, immediate = false) {
            if (rendering) return;

            // Debounce rendering unless immediate=true
            if (!immediate && !isPinching) {
                clearTimeout(renderTimeout);
                renderTimeout = setTimeout(() => renderPage(pageNum, true), 150);
                return;
            }

            // NULL SAFETY: Check if pdfDoc is loaded before attempting to render
            if (!pdfDoc) return;

            rendering = true;

            // Add smooth transition effect (skip if pinching for "no white flash" experience)
            if (!isPinching) {
                canvas.classList.add('rendering');
            }

            try {
                const page = await pdfDoc.getPage(pageNum);
                const viewport = page.getViewport({ scale: currentScale });

                // Set canvas dimensions
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                // Clear previous context to save memory/prevent heavy feel
                ctx.clearRect(0, 0, canvas.width, canvas.height);

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
                    current_scale: currentScale
                });
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

        const scaleWrapper = document.getElementById('pdf-scale-wrapper');

        zoomInButton.addEventListener('click', () => {
            const oldValue = currentScale;
            currentScale = Math.min(currentScale + 0.25, 5.0);
            
            // Hybrid Button Zoom: Scale visually first
            const factor = currentScale / oldValue;
            scaleWrapper.style.transition = 'transform 0.2s ease-out';
            scaleWrapper.style.transform = `scale(${factor})`;
            scaleWrapper.style.transformOrigin = 'center top';

            setTimeout(() => {
                scaleWrapper.style.transition = 'none';
                scaleWrapper.style.transform = '';
                renderPage(currentPage, true);
            }, 200);
        });

        zoomOutButton.addEventListener('click', () => {
            const oldValue = currentScale;
            currentScale = Math.max(currentScale - 0.25, 0.5);
            
            // Hybrid Button Zoom: Scale visually first
            const factor = currentScale / oldValue;
            scaleWrapper.style.transition = 'transform 0.2s ease-out';
            scaleWrapper.style.transform = `scale(${factor})`;
            scaleWrapper.style.transformOrigin = 'center top';

            setTimeout(() => {
                scaleWrapper.style.transition = 'none';
                scaleWrapper.style.transform = '';
                renderPage(currentPage, true);
            }, 200);
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



        // Re-render watermark on window resize
        let resizeTimeout;
        window.addEventListener('resize', () => {
            
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

        // ============================================
        // MOBILE: Default to Fit Width + wire bottom toolbar
        // ============================================
        const isMobile = window.innerWidth < 768;
        if (isMobile) {
            zoomSelect.value = 'fit';
        }

        // Load PDF on page load
        loadPDF();

        // Mobile bottom toolbar wiring
        (function initMobileToolbar() {
            const mobPrev = document.getElementById('mob-prev');
            const mobNext = document.getElementById('mob-next');
            const mobPageInfo = document.getElementById('mob-page-info');
            const mobZoomIn = document.getElementById('mob-zoom-in');
            const mobZoomOut = document.getElementById('mob-zoom-out');
            if (!mobPrev) return; // not rendered (shouldn't happen)

            // Sync mobile page indicator whenever desktop updates
            const origUpdateNav = updateNavigationButtons;
            updateNavigationButtons = function() {
                origUpdateNav();
                // Sync mobile UI
                mobPrev.disabled = prevButton.disabled;
                mobNext.disabled = nextButton.disabled;
                mobPageInfo.textContent = currentPage + ' / ' + (totalPages || '—');
            };

            // ============================================
            // TRUE CONTENT-ANCHOR PINCH ZOOM
            // ============================================
            let touchStartDist = 0;
            let touchStartScale = 1.0;
            let initialScale = 1.0;
            let contentX = 0;
            let contentY = 0;
            let midpointX = 0;
            let midpointY = 0;
            let containerRect = null;
            let liveScale = 1.0;
            let rafPending = false;

            const container = document.getElementById('pdf-canvas-container');
            const scaleWrapper = document.getElementById('pdf-scale-wrapper');

            container.addEventListener('touchstart', (e) => {
                if (e.touches.length === 2 && window.innerWidth < 768) {
                    isPinching = true;
                    const t1 = e.touches[0];
                    const t2 = e.touches[1];
                    
                    touchStartDist = Math.hypot(t1.clientX - t2.clientX, t1.clientY - t2.clientY);
                    initialScale = currentScale;
                    
                    containerRect = container.getBoundingClientRect();
                    midpointX = (t1.clientX + t2.clientX) / 2;
                    midpointY = (t1.clientY + t2.clientY) / 2;
                    
                    // Requirement: Convert to content coordinates
                    contentX = (midpointX - containerRect.left + container.scrollLeft) / initialScale;
                    contentY = (midpointY - containerRect.top + container.scrollTop) / initialScale;
                }
            }, { passive: true });

            container.addEventListener('touchmove', (e) => {
                if (isPinching && e.touches.length === 2) {
                    if (e.cancelable) e.preventDefault();

                    const t1 = e.touches[0];
                    const t2 = e.touches[1];
                    const currentDist = Math.hypot(t1.clientX - t2.clientX, t1.clientY - t2.clientY);
                    
                    if (touchStartDist > 0) {
                        const scaleFactor = currentDist / touchStartDist;
                        liveScale = scaleFactor;
                        const absoluteLiveScale = initialScale * liveScale;
                        
                        if (!rafPending) {
                            rafPending = true;
                            requestAnimationFrame(() => {
                                // Requirement: Apply scale ONLY to wrapper
                                scaleWrapper.style.transform = `scale(${liveScale})`;
                                scaleWrapper.style.transformOrigin = '0 0';
                                
                                // Requirement: Precise scroll compensation
                                container.scrollLeft = contentX * absoluteLiveScale - (midpointX - containerRect.left);
                                container.scrollTop = contentY * absoluteLiveScale - (midpointY - containerRect.top);
                                
                                rafPending = false;
                            });
                        }
                    }
                }
            }, { passive: false });

            container.addEventListener('touchend', async (e) => {
                if (isPinching) {
                    isPinching = false;
                    
                    // Requirement: Sync zoom selector to show "Custom" or empty
                    if (zoomSelect) zoomSelect.value = '';

                    // Final physical scale
                    const finalScale = Math.min(Math.max(initialScale * liveScale, 0.5), 5.0);
                    
                    // Requirement: Store current scroll position before removing transform
                    const finalScrollLeft = container.scrollLeft;
                    const finalScrollTop = container.scrollTop;

                    // Remove visual transform
                    scaleWrapper.style.transform = '';
                    
                    // Requirement: Update currentScale and RE-RENDER sharp version
                    currentScale = finalScale;
                    
                    // IMPORTANT: We must await the render so the container handles the new canvas size 
                    // before we restore the scroll position, preventing clamping/scrolling to top.
                    await renderPage(currentPage, true);
                    
                    // Requirement: Restore scroll position precisely
                    container.scrollLeft = finalScrollLeft;
                    container.scrollTop = finalScrollTop;
                    
                    touchStartDist = 0;
                    liveScale = 1.0;
                }
            }, { passive: true });

            mobPrev.addEventListener('click', () => { prevButton.click(); });
            mobNext.addEventListener('click', () => { nextButton.click(); });
            mobZoomIn.addEventListener('click', () => { zoomInButton.click(); });
            mobZoomOut.addEventListener('click', () => { zoomOutButton.click(); });
        })();

        // beforeunload removed — viewer is read-only, no changes to save
    </script>
</body>
</html>
