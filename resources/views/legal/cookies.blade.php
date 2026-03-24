<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Cookies — Punjab Idiomas</title>
    <meta name="description" content="Política de Cookies de Punjab Idiomas. Información sobre los tipos de cookies utilizadas en el sitio web conforme a la normativa española.">
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class', theme: { extend: {} } }</script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        html { scroll-behavior: smooth; }
        .legal-section h2 { font-size: 1.25rem; font-weight: 700; color: #7c2d12; margin-top: 2rem; margin-bottom: 0.5rem; }
        .dark .legal-section h2 { color: #fb923c; }
        .legal-section p { margin-bottom: 0.75rem; line-height: 1.75; }
        .legal-section ul { list-style: disc; padding-left: 1.5rem; margin-bottom: 0.75rem; }
        .legal-section ul li { margin-bottom: 0.25rem; line-height: 1.75; }
    </style>
</head>
<body class="font-sans text-slate-800 bg-slate-50 dark:bg-slate-900 dark:text-slate-200 min-h-screen transition-colors duration-300">

    {{-- Navbar --}}
    <nav class="bg-white dark:bg-slate-800 shadow-md sticky top-0 z-40 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ url('/') }}" class="flex-shrink-0 flex items-center no-underline">
                    <img src="{{ asset('images/logo.png') }}" alt="Punjab Idiomas Logo" class="h-12 w-auto mr-3 rounded-sm">
                    <div>
                        <span class="font-extrabold text-xl text-orange-900 dark:text-orange-500 leading-none block">PUNJAB</span>
                        <span class="font-semibold text-xs text-yellow-500 uppercase tracking-wider block">Idiomas</span>
                    </div>
                </a>
                <div class="flex items-center space-x-4">
                    <a href="{{ url('/') }}" class="flex items-center text-slate-600 dark:text-slate-300 hover:text-orange-700 dark:hover:text-yellow-400 font-semibold transition-colors text-sm">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>Volver al inicio
                    </a>
                    <button id="theme-toggle" type="button" class="text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 focus:outline-none rounded-lg text-sm p-2.5 transition-colors">
                        <i id="theme-toggle-dark-icon" data-lucide="moon" class="hidden w-5 h-5"></i>
                        <i id="theme-toggle-light-icon" data-lucide="sun" class="hidden w-5 h-5 text-yellow-400"></i>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    {{-- Page Header --}}
    <div class="bg-orange-900 dark:bg-slate-950 py-12 transition-colors duration-300">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl md:text-4xl font-extrabold text-white">Política de Cookies</h1>
            <p class="text-orange-200 mt-2 text-sm">Información sobre el uso de cookies conforme a la normativa española</p>
        </div>
    </div>

    {{-- Content --}}
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-8 md:p-12 legal-section">

            <p>Este sitio web utiliza cookies conforme a la normativa española.</p>

            <h2>¿Qué son las cookies?</h2>
            <p>Son archivos que se descargan en el dispositivo del usuario al acceder a determinadas páginas web.</p>

            <h2>Tipos de cookies utilizadas</h2>
            <ul>
                <li><strong>Técnicas:</strong> necesarias para el funcionamiento del sitio y acceso al área de alumnos</li>
                <li><strong>De análisis:</strong> permiten medir el uso del sitio web</li>
                <li><strong>De sesión:</strong> necesarias para mantener el login activo</li>
            </ul>

            <h2>Cookies en área privada</h2>
            <p>Las cookies son esenciales para permitir el acceso seguro a la plataforma de estudiantes y garantizar el correcto funcionamiento del sistema de autenticación.</p>

            <h2>Gestión de cookies</h2>
            <p>El usuario puede configurar su navegador para aceptar o rechazar cookies.</p>

            <h2>Consentimiento</h2>
            <p>Al acceder al sitio web, el usuario acepta el uso de cookies mediante el banner correspondiente.</p>

        </div>

        {{-- Back Link + Other Legal Pages --}}
        <div class="mt-8 flex flex-col sm:flex-row gap-4 items-start">
            <a href="{{ url('/') }}" class="inline-flex items-center text-orange-700 dark:text-orange-400 hover:underline font-semibold text-sm">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Volver al inicio
            </a>
            <div class="flex flex-wrap gap-4 text-sm">
                <a href="{{ url('/legal-notice') }}" class="text-slate-500 dark:text-slate-400 hover:text-orange-700 dark:hover:text-yellow-400 transition-colors">Aviso Legal</a>
                <a href="{{ url('/privacy-policy') }}" class="text-slate-500 dark:text-slate-400 hover:text-orange-700 dark:hover:text-yellow-400 transition-colors">Política de Privacidad</a>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="mt-12 py-8 bg-slate-900 dark:bg-black text-slate-400 text-center text-sm border-t border-slate-800">
        <p>© <span id="year"></span> Punjab Idiomas. Todos los derechos reservados. · Barcelona, España</p>
    </footer>

    <script>
        lucide.createIcons();
        document.getElementById('year').textContent = new Date().getFullYear();
        const darkIcon = document.getElementById('theme-toggle-dark-icon');
        const lightIcon = document.getElementById('theme-toggle-light-icon');
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            lightIcon.classList.remove('hidden'); document.documentElement.classList.add('dark');
        } else {
            darkIcon.classList.remove('hidden'); document.documentElement.classList.remove('dark');
        }
        document.getElementById('theme-toggle').addEventListener('click', () => {
            darkIcon.classList.toggle('hidden'); lightIcon.classList.toggle('hidden');
            if (localStorage.getItem('color-theme') === 'light') {
                document.documentElement.classList.add('dark'); localStorage.setItem('color-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark'); localStorage.setItem('color-theme', 'light');
            }
        });
    </script>
</body>
</html>
