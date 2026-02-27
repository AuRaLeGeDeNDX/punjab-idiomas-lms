<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name') }} - @yield('title', 'Learning Management System')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    
    <!-- SEO Meta Tags -->
    <meta name="description" content="@yield('description', config('app.name') . ' - Modern Learning Management System')">
    <meta name="keywords" content="LMS, Learning, Education, {{ config('app.name') }}">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="{{ config('app.name') }}">
    <meta property="og:description" content="Modern Learning Management System">
    <meta property="og:image" content="{{ asset('images/punjabidiomas.jpg') }}">
    <meta property="og:type" content="website">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ config('app.name') }}">
    <meta name="twitter:description" content="Modern Learning Management System">
    <meta name="twitter:image" content="{{ asset('images/punjabidiomas.jpg') }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Creative Professional Design -->
    @vite(['resources/css/creative-professional.css'])
    
    <!-- Custom CSS -->
    <style>
        :root {
            /* Orange Theme Harmonization */
            --student-color: #fb923c; /* Orange-400 */
            --teacher-color: #f97316; /* Orange-500 */
            --admin-color: #ea580c;   /* Orange-600 */
        }
        
        .navbar-brand {
            font-weight: bold;
            display: flex;
            align-items: center;
        }
        
        .navbar-logo {
            height: 60px;
            width: auto;
            margin-right: 12px;
        }
        
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #f8f9fa;
            border-right: 1px solid #dee2e6;
        }
        
        .sidebar .nav-link {
            color: #495057;
            padding: 0.75rem 1rem;
            border-radius: 0;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: #e9ecef;
            color: #212529;
        }
        
        .main-content {
            min-height: calc(100vh - 56px);
            padding: 2rem;
        }

        @media screen and (max-width: 767px) {
            .main-content {
                min-height: auto !important;
                padding: 1rem !important;
            }
            .sidebar {
                min-height: auto !important;
            }
        }
        
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        
        .notification-badge {
            position: relative;
        }
        
        .notification-badge .badge {
            position: absolute;
            top: -8px;
            right: -8px;
        }
        
        .student-theme .navbar { background-color: var(--student-color) !important; }
        .teacher-theme .navbar { background-color: var(--teacher-color) !important; }
        .admin-theme .navbar { background-color: var(--admin-color) !important; }
        
        .loading-spinner {
            display: none;
        }
        
        .loading .loading-spinner {
            display: inline-block;
        }
        
        @media (max-width: 768px) {
            .navbar-logo {
                height: 32px;
            }
            
            .sidebar {
                min-height: auto;
            }
            
            .main-content {
                padding: 1rem;
            }
        }

        /* ==========================================
           MOBILE NAVBAR / SIDEBAR STYLES
           ========================================== */
        @media (max-width: 991px) {
            /* Collapse container: styled panel */
            #navbarNav {
                background: rgba(15, 23, 42, 0.97);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border-radius: 0 0 16px 16px;
                margin: 0.5rem -0.75rem 0;
                padding: 0.75rem 1rem 1rem;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                max-height: 80vh;
                overflow-y: auto;
            }

            /* All nav items: consistent styling */
            #navbarNav .navbar-nav {
                gap: 2px;
            }

            #navbarNav .nav-item {
                border-radius: 10px;
            }

            /* Force all d-flex alignments to block on mobile */
            #navbarNav .nav-item.d-flex {
                display: block !important;
            }

            #navbarNav .nav-item .me-2,
            #navbarNav .nav-item .me-1 {
                margin-right: 0.5rem !important;
            }

            /* All nav links: left aligned, full width */
            #navbarNav .nav-link {
                padding: 0.7rem 1rem;
                border-radius: 10px;
                color: #e2e8f0;
                font-size: 0.95rem;
                font-weight: 500;
                transition: all 0.2s ease;
                display: flex !important;
                align-items: center;
                gap: 0.5rem;
                text-align: left;
                width: 100%;
                justify-content: flex-start !important;
            }

            #navbarNav .nav-link:hover,
            #navbarNav .nav-link:focus {
                background: rgba(255, 255, 255, 0.1);
                color: #ffffff;
            }

            #navbarNav .nav-link:active {
                background: rgba(255, 255, 255, 0.15);
                transform: scale(0.98);
            }

            /* Divider between main nav and utility nav */
            #navbarNav .navbar-nav + .navbar-nav {
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                margin-top: 0.5rem;
                padding-top: 0.5rem;
            }

            /* Dropdown menus inside mobile nav */
            #navbarNav .dropdown-menu {
                background: rgba(30, 41, 59, 0.98);
                border: 1px solid rgba(255, 255, 255, 0.1);
                border-radius: 10px;
                margin: 0.25rem 0 0.5rem 1rem;
                padding: 0.5rem;
                box-shadow: none;
                position: static !important;
                float: none;
                width: calc(100% - 1rem);
            }

            #navbarNav .dropdown-item {
                color: #cbd5e1;
                border-radius: 8px;
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
                transition: all 0.2s ease;
                text-align: left;
            }

            #navbarNav .dropdown-item:hover,
            #navbarNav .dropdown-item:focus {
                background: rgba(255, 255, 255, 0.1);
                color: #ffffff;
            }

            #navbarNav .dropdown-header {
                color: #94a3b8;
                font-size: 0.75rem;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                padding: 0.5rem 1rem;
                text-align: left;
            }

            #navbarNav .dropdown-divider {
                border-color: rgba(255, 255, 255, 0.08);
                margin: 0.25rem 0;
            }

            /* Notification dropdown: full width */
            #navbarNav .dropdown-menu[style*="300px"] {
                width: calc(100% - 1rem) !important;
            }

            /* Theme toggle button: full width, left aligned */
            #navbarNav #theme-toggle {
                justify-content: flex-start !important;
                padding: 0.7rem 1rem;
                width: 100%;
                text-decoration: none;
                border-radius: 10px;
            }

            /* Notification bell: add label space */
            #navbarNav .notification-badge {
                display: flex !important;
                align-items: center;
                width: 100%;
            }

            /* Toggler button: cleaner look */
            .navbar-toggler {
                border: 1px solid rgba(255, 255, 255, 0.2);
                padding: 0.4rem 0.6rem;
                border-radius: 8px;
                transition: all 0.2s ease;
            }

            .navbar-toggler:hover {
                background: rgba(255, 255, 255, 0.1);
                border-color: rgba(255, 255, 255, 0.3);
            }

            .navbar-toggler:focus {
                box-shadow: 0 0 0 2px rgba(255, 255, 255, 0.15);
            }
        }

        /* Mobile Sidebar Toggle & Panel */
        .mobile-sidebar-toggle {
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.12);
            color: #e2e8f0;
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.6rem 1rem;
            border-radius: 10px;
            transition: all 0.2s ease;
        }

        .mobile-sidebar-toggle:hover,
        .mobile-sidebar-toggle:focus {
            background: rgba(15, 23, 42, 0.95);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.2);
        }

        .mobile-sidebar-toggle[aria-expanded="true"] .mobile-sidebar-chevron {
            transform: rotate(180deg);
        }

        .mobile-sidebar-chevron {
            transition: transform 0.3s ease;
            font-size: 0.75rem;
        }

        .mobile-sidebar-panel {
            background: rgba(15, 23, 42, 0.95);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 0.75rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }

        .mobile-sidebar-panel .nav {
            gap: 2px;
        }

        .mobile-sidebar-panel .nav-link {
            color: #cbd5e1;
            padding: 0.65rem 1rem;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
        }

        .mobile-sidebar-panel .nav-link:hover,
        .mobile-sidebar-panel .nav-link:focus {
            background: rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .mobile-sidebar-panel .nav-link.active {
            background: rgba(99, 102, 241, 0.2);
            color: #a5b4fc;
            border-left: 3px solid #6366f1;
        }

        .mobile-sidebar-panel .nav-link i {
            width: 20px;
            text-align: center;
        }

        /* Dashboard Redesign: Landing Page Match */
        html, body {
            scroll-behavior: smooth;
            overflow-x: hidden;
        }

        /* Animated Gradient Background */
        .gradient-bg {
            background: linear-gradient(-45deg, var(--student-color, #fb923c), #facc15, #f97316, #ea580c);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        .dark .gradient-bg {
            background: linear-gradient(-45deg, #0f172a, #1e293b, #334155, #0f172a);
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Glassmorphism */
        .glass-panel {
            background: rgba(255, 255, 255, 0.85) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3) !important;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05) !important;
        }

        .dark .glass-panel {
            background: rgba(30, 41, 59, 0.85) !important; /* Slate 800 */
            border: 1px solid rgba(255, 255, 255, 0.05) !important;
            color: #f8f9fa;
        }

        .dark .creative-card-header,
        .dark .creative-card-footer {
            background-color: rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.05);
        }

        .dark .table,
        .dark .text-muted {
            color: #cbd5e1 !important;
        }

        .dark .table td, .dark .table th {
            border-color: rgba(255, 255, 255, 0.1);
        }

        /* Global Dark Mode: CSS Variable Overrides */
        .dark {
            --bg-card: #1e293b;
            --bg-subtle: #0f172a;
            --bg-hover: #334155;
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --border-color: rgba(255, 255, 255, 0.1);
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --color-surface: #1e293b;
            --color-background: #0f172a;
            --color-text: #f8fafc;
            --color-text-muted: #94a3b8;
            --color-border: rgba(255, 255, 255, 0.1);
            --color-gray-50: #1e293b;
            --color-gray-100: #334155;
            --color-gray-200: #475569;
            --color-gray-300: #64748b;
            --color-gray-400: #94a3b8;
            --color-gray-500: #cbd5e1;
            --color-gray-600: #e2e8f0;
            --color-gray-700: #f1f5f9;
            --color-gray-800: #f8fafc;
            --color-gray-900: #ffffff;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.4);
        }

        /* Global Dark Mode: Bootstrap Cards */
        .dark .card {
            background: #1e293b !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #f8fafc !important;
        }
        .dark .card-header {
            background: rgba(0, 0, 0, 0.2) !important;
            border-bottom-color: rgba(255, 255, 255, 0.1) !important;
            color: #f8fafc !important;
        }
        .dark .card-body {
            background: transparent !important;
            color: #f8fafc !important;
        }
        .dark .card-footer {
            background: rgba(0, 0, 0, 0.15) !important;
            border-top-color: rgba(255, 255, 255, 0.1) !important;
            color: #cbd5e1 !important;
        }

        /* Global Dark Mode: Form Controls */
        .dark .form-control,
        .dark .form-select {
            background-color: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
            color: #f8fafc !important;
        }
        .dark .form-control:focus,
        .dark .form-select:focus {
            background-color: #0f172a !important;
            border-color: #f97316 !important;
            color: #f8fafc !important;
        }
        .dark .form-control::placeholder {
            color: #64748b !important;
        }
        .dark .form-label,
        .dark label {
            color: #cbd5e1 !important;
        }
        .dark .form-text {
            color: #94a3b8 !important;
        }

        /* Global Dark Mode: Text & Headings */
        .dark h1, .dark h2, .dark h3, .dark h4, .dark h5, .dark h6,
        .dark .h1, .dark .h2, .dark .h3, .dark .h4, .dark .h5, .dark .h6 {
            color: #f8fafc !important;
        }
        .dark .text-dark {
            color: #f8fafc !important;
        }
        .dark .bg-white {
            background-color: #1e293b !important;
        }
        .dark .bg-light {
            background-color: #0f172a !important;
        }

        /* Global Dark Mode: List Groups & Modals */
        .dark .list-group-item {
            background-color: #1e293b !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #f8fafc !important;
        }
        .dark .modal-content {
            background-color: #1e293b !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #f8fafc !important;
        }
        .dark .modal-header {
            border-bottom-color: rgba(255, 255, 255, 0.1) !important;
        }
        .dark .modal-footer {
            border-top-color: rgba(255, 255, 255, 0.1) !important;
        }

        /* Custom Cursor */
        #custom-cursor {
            width: 20px;
            height: 20px;
            background: rgba(194, 65, 12, 0.2);
            border: 2px solid #c2410c;
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            transition: transform 0.1s ease-out, width 0.3s, height 0.3s, background 0.3s;
            display: none;
        }

        .dark #custom-cursor {
            background: rgba(250, 204, 21, 0.2);
            border-color: #facc15;
        }

        @media (min-width: 768px) {
            #custom-cursor { display: block; }
            body { cursor: none; }
            a, button, select, input, textarea, .glass-panel, .btn { cursor: none; }
        }

        .cursor-active {
            width: 50px !important;
            height: 50px !important;
            background: rgba(194, 65, 12, 0.1) !important;
            border-color: #fbbf24 !important;
        }

        .dark .cursor-active {
            background: rgba(250, 204, 21, 0.1) !important;
            border-color: #c2410c !important;
        }

        /* General dark mode adjustments */
        .dark body {
            background-color: #0f172a;
            color: #e2e8f0;
        }
        .dark .sidebar {
            background-color: #1e293b;
            border-right-color: #334155;
        }
        .dark .sidebar .nav-link {
            color: #cbd5e1;
        }
        .dark .sidebar .nav-link:hover,
        .dark .sidebar .nav-link.active {
            background-color: #334155;
            color: #f8fafc;
        }
    </style>
    
    <script>
        // Set initial dark mode state before rendering to prevent flash
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @stack('styles')

    {{-- Dark mode overrides MUST load AFTER @stack('styles') to override Vite CSS variables --}}
    <style>
        .dark {
            --bg-card: #1e293b !important;
            --bg-subtle: #0f172a !important;
            --bg-hover: #334155 !important;
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --border-color: rgba(255, 255, 255, 0.1) !important;
            --text-primary: #f8fafc;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --color-surface: #1e293b;
            --color-background: #0f172a;
            --color-text: #f8fafc;
            --color-text-muted: #94a3b8;
            --color-border: rgba(255, 255, 255, 0.1);
            --color-gray-50: #1e293b;
            --color-gray-100: #334155;
            --color-gray-200: #475569;
            --color-gray-300: #64748b;
            --color-gray-400: #94a3b8;
            --color-gray-500: #cbd5e1;
            --color-gray-600: #e2e8f0;
            --color-gray-700: #f1f5f9;
            --color-gray-800: #f8fafc;
            --color-gray-900: #ffffff;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.3);
            --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.4);
        }
        .dark .card {
            background: #1e293b !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #f8fafc !important;
        }
        .dark .card-header {
            background: rgba(0, 0, 0, 0.2) !important;
            border-bottom-color: rgba(255, 255, 255, 0.1) !important;
            color: #f8fafc !important;
        }
        .dark .card-body {
            background: transparent !important;
            color: #f8fafc !important;
        }
        .dark .form-control,
        .dark .form-select {
            background-color: #0f172a !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
            color: #f8fafc !important;
        }
        .dark .form-label,
        .dark label {
            color: #cbd5e1 !important;
        }

        /* Content Builder & Preview: Always white canvas */
        .dark .content-builder-scroll-area .card,
        .dark .content-preview-card {
            background: #ffffff !important;
            background-color: #ffffff !important;
            border-color: #e9ecef !important;
            color: #1a1a2e !important;
        }
        .dark .content-builder-scroll-area .card-body,
        .dark .content-preview-card .card-body {
            background: #ffffff !important;
            background-color: #ffffff !important;
            color: #1a1a2e !important;
        }
        .dark .content-builder-container {
            background: #ffffff !important;
        }
    </style>
</head>
<body class="{{ auth()->user()->hasRole('Student') ? 'student-theme student-view' : (auth()->user()->hasRole('Teacher') ? 'teacher-theme teacher-view' : 'admin-theme') }} gradient-bg">
    <div id="custom-cursor"></div>
    
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark glass-panel border-bottom-0" style="background: rgba(0,0,0,0.6) !important; position: relative; z-index: 1050;">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route(auth()->user()->hasRole('Admin') ? 'admin.dashboard' : (auth()->user()->hasRole('Teacher') ? 'teacher.dashboard' : 'student.dashboard')) }}">
                <img src="{{ asset('images/logo.png') }}" 
                     alt="{{ config('app.name') }}" 
                     class="navbar-logo rounded-sm">
                <div class="d-none d-md-flex flex-column ms-2">
                    <span class="fw-bold fs-5 text-white leading-1">PUNJAB</span>
                    <span class="fs-6 text-warning uppercase" style="font-size: 0.7rem !important; letter-spacing: 1px;">Idiomas</span>
                </div>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    @yield('nav-items')
                </ul>
                
                <ul class="navbar-nav">
                    <!-- Language Selector -->
                    <li class="nav-item dropdown d-flex align-items-center me-2">
                        <a class="nav-link text-uppercase fw-semibold d-flex align-items-center" href="#" id="languageDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i data-lucide="globe" class="me-1" style="width: 20px; height: 20px;"></i>
                            {{ app()->getLocale() }}
                            <i data-lucide="chevron-down" class="ms-1" style="width: 16px; height: 16px;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm" aria-labelledby="languageDropdown">
                            <li><a class="dropdown-item px-4 py-2" href="#" onclick="setLanguage('es', event)">Español</a></li>
                            <li><a class="dropdown-item px-4 py-2" href="#" onclick="setLanguage('ca', event)">Català</a></li>
                            <li><a class="dropdown-item px-4 py-2" href="#" onclick="setLanguage('en', event)">English</a></li>
                            <li><a class="dropdown-item px-4 py-2" href="#" onclick="setLanguage('hi', event)">हिन्दी</a></li>
                            <li><a class="dropdown-item px-4 py-2" href="#" onclick="setLanguage('pa', event)">ਪੰਜਾਬੀ</a></li>
                        </ul>
                    </li>
                    <!-- Theme Toggle -->
                    <li class="nav-item d-flex align-items-center me-2">
                        <button id="theme-toggle" type="button" class="btn btn-link nav-link px-2 d-flex align-items-center" aria-label="Toggle Dark Mode">
                            <i id="theme-toggle-dark-icon" data-lucide="moon" class="d-none" style="width: 20px; height: 20px;"></i>
                            <i id="theme-toggle-light-icon" data-lucide="sun" class="d-none text-warning" style="width: 20px; height: 20px;"></i>
                        </button>
                    </li>
                    <!-- Notifications -->
                    <li class="nav-item dropdown">
                        <a class="nav-link notification-badge" href="#" id="notificationDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bell"></i>
                            @if(auth()->user()->unreadNotifications->count() > 0)
                                <span class="badge bg-danger">{{ auth()->user()->unreadNotifications->count() }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                            <li><h6 class="dropdown-header">Notifications</h6></li>
                            @forelse(auth()->user()->unreadNotifications->take(5) as $notification)
                                <li>
                                    <a class="dropdown-item" href="#" onclick="markAsRead('{{ $notification->id }}')">
                                        <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                        <div>{{ $notification->data['message'] ?? 'New notification' }}</div>
                                    </a>
                                </li>
                            @empty
                                <li><span class="dropdown-item text-muted">No new notifications</span></li>
                            @endforelse
                            @if(auth()->user()->unreadNotifications->count() > 5)
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-center" href="#">View all notifications</a></li>
                            @endif
                        </ul>
                    </li>
                    
                    <!-- User Menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>{{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><h6 class="dropdown-header">{{ auth()->user()->getRoleNames()->first() }}</h6></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('messages.index') }}"><i class="fas fa-envelope me-2"></i>Messages</a></li>
                            <li><a class="dropdown-item" href="{{ route('notifications.preferences') }}"><i class="fas fa-bell me-2"></i>Notification Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            @hasSection('sidebar')
                {{-- Mobile sidebar toggle --}}
                <div class="d-md-none w-100 px-3 pt-2">
                    <button class="btn btn-sm w-100 d-flex align-items-center justify-content-center gap-2 mobile-sidebar-toggle" 
                            type="button" 
                            data-bs-toggle="collapse" 
                            data-bs-target="#mobileSidebar" 
                            aria-expanded="false" 
                            aria-controls="mobileSidebar">
                        <i class="fas fa-bars"></i>
                        <span>Menu</span>
                        <i class="fas fa-chevron-down ms-auto mobile-sidebar-chevron"></i>
                    </button>
                </div>
                {{-- Mobile sidebar (collapsed by default) --}}
                <nav class="d-md-none collapse w-100 px-2" id="mobileSidebar">
                    <div class="mobile-sidebar-panel mt-2 mb-3">
                        @yield('sidebar')
                    </div>
                </nav>
                {{-- Desktop sidebar (always visible) --}}
                <nav class="col-md-3 col-lg-2 d-none d-md-block sidebar">
                    <div class="position-sticky pt-3">
                        @yield('sidebar')
                    </div>
                </nav>
                <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
            @else
                <main class="col-12 main-content">
            @endif
                
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                <!-- Main Content -->
                @yield('content')
            </main>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Toastr for notifications -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <script>
        // CSRF token setup for AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Mark notification as read
        function markAsRead(notificationId) {
            $.post('/notifications/' + notificationId + '/read', function() {
                location.reload();
            });
        }
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut();
        }, 5000);
        
        // Loading states for buttons
        $(document).on('click', '.btn-loading', function() {
            $(this).addClass('loading').prop('disabled', true);
        });
        
        // Toastr configuration
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "5000"
        };
    </script>
    
    <!-- Dashboard Theme & Cursor JS -->
    <script>
        // Initialize Lucide Icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }

        // Theme Toggle Logic
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');

        if (themeToggleDarkIcon && themeToggleLightIcon) {
            function setInitialIcons() {
                if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    themeToggleLightIcon.classList.remove('d-none');
                } else {
                    themeToggleDarkIcon.classList.remove('d-none');
                }
            }
            setInitialIcons();

            document.getElementById('theme-toggle').addEventListener('click', function() {
                themeToggleDarkIcon.classList.toggle('d-none');
                themeToggleLightIcon.classList.toggle('d-none');

                if (localStorage.getItem('color-theme') === 'light') {
                    document.documentElement.classList.add('dark');
                    localStorage.setItem('color-theme', 'dark');
                } else {
                    document.documentElement.classList.remove('dark');
                    localStorage.setItem('color-theme', 'light');
                }
            });
        }

        // Custom Cursor Logic (Only for fine pointers/mice)
        if (window.matchMedia("(pointer: fine)").matches) {
            const cursor = document.getElementById('custom-cursor');
            if (cursor) {
                document.addEventListener('mousemove', (e) => {
                    cursor.style.transform = `translate3d(${e.clientX - 10}px, ${e.clientY - 10}px, 0)`;
                });

                document.querySelectorAll('a, button, .btn, .creative-card, input, select, textarea').forEach(el => {
                    el.addEventListener('mouseenter', () => cursor.classList.add('cursor-active'));
                    el.addEventListener('mouseleave', () => cursor.classList.remove('cursor-active'));
                });
            }
        }
    </script>
    
    @stack('modals')
    @stack('scripts')
    @include('partials.language-switcher-js')
</body>
</html>
