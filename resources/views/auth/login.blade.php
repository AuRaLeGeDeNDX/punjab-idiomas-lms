<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión — Punjab Idiomas</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: { extend: {} }
        }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        html { scroll-behavior: smooth; }

        #custom-cursor {
            width: 20px; height: 20px;
            background: rgba(194,65,12,0.2);
            border: 2px solid #c2410c;
            border-radius: 50%;
            position: fixed;
            pointer-events: none;
            z-index: 9999;
            transition: transform 0.1s ease-out, width 0.3s, height 0.3s;
            display: none;
        }
        .dark #custom-cursor { background: rgba(250,204,21,0.2); border-color: #facc15; }

        @@media (min-width: 768px) {
            #custom-cursor { display: block; }
            body { cursor: none; }
            a, button, input { cursor: none; }
        }

        .cursor-active {
            width: 50px !important; height: 50px !important;
            background: rgba(194,65,12,0.1) !important;
            border-color: #fbbf24 !important;
        }

        /* Animated gradient background */
        .gradient-bg {
            background: linear-gradient(135deg, #7c2d12 0%, #9a3412 25%, #c2410c 50%, #7c2d12 75%, #431407 100%);
            background-size: 400% 400%;
            animation: gradientShift 12s ease infinite;
        }
        .dark .gradient-bg {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 25%, #1a1a2e 50%, #0f172a 100%);
            background-size: 400% 400%;
        }
        @@keyframes gradientShift {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        /* Card glass effect */
        .login-card {
            background: rgba(255,255,255,0.97);
            backdrop-filter: blur(20px);
        }
        .dark .login-card {
            background: rgba(30,41,59,0.97);
        }

        /* Input focus ring */
        .input-field {
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .input-field:focus {
            border-color: #c2410c;
            box-shadow: 0 0 0 3px rgba(194,65,12,0.15);
            outline: none;
        }
        .dark .input-field:focus {
            border-color: #facc15;
            box-shadow: 0 0 0 3px rgba(250,204,21,0.15);
        }
    </style>
</head>
<body class="font-sans bg-slate-50 dark:bg-slate-900 min-h-screen transition-colors duration-300">

    <div id="custom-cursor"></div>

    {{-- Top bar matching landing page --}}
    <div class="bg-orange-900 dark:bg-orange-950 text-white py-2 px-4 hidden md:flex justify-between items-center text-sm transition-colors duration-300">
        <div class="max-w-7xl mx-auto w-full flex justify-between items-center">
            <div class="flex space-x-6">
                <span class="flex items-center"><i data-lucide="mail" class="w-4 h-4 mr-2 text-yellow-400"></i> idiomaspunjab@gmail.com</span>
                <span class="flex items-center"><i data-lucide="phone" class="w-4 h-4 mr-2 text-yellow-400"></i> +34 612 45 50 57</span>
            </div>
            <button id="theme-toggle" class="text-slate-300 hover:text-yellow-400 transition-colors p-1.5 rounded-md hover:bg-orange-800 flex items-center gap-2 text-sm">
                <i id="theme-icon-dark" data-lucide="moon" class="hidden w-4 h-4"></i>
                <i id="theme-icon-light" data-lucide="sun" class="hidden w-4 h-4 text-yellow-400"></i>
            </button>
        </div>
    </div>

    {{-- Navbar --}}
    <nav class="bg-white dark:bg-slate-800 shadow-md sticky top-0 z-40 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="{{ url('/') }}" class="flex items-center no-underline">
                    <img src="{{ asset('images/logo.png') }}" alt="Punjab Idiomas Logo" class="h-10 w-auto mr-3 rounded-sm">
                    <div>
                        <span class="font-extrabold text-xl text-orange-900 dark:text-orange-500 leading-none block">PUNJAB</span>
                        <span class="font-semibold text-xs text-yellow-500 uppercase tracking-wider block">Idiomas</span>
                    </div>
                </a>

                <div class="flex items-center gap-3">
                    {{-- Mobile theme toggle --}}
                    <button id="theme-toggle-mobile" class="md:hidden text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 p-2 rounded-lg transition-colors">
                        <i id="theme-icon-dark-mobile" data-lucide="moon" class="hidden w-5 h-5"></i>
                        <i id="theme-icon-light-mobile" data-lucide="sun" class="hidden w-5 h-5 text-yellow-400"></i>
                    </button>
                    <a href="{{ url('/') }}" class="flex items-center gap-2 text-slate-600 dark:text-slate-300 hover:text-orange-700 dark:hover:text-yellow-400 text-sm font-semibold transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Volver al inicio</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Main content --}}
    <main class="min-h-[calc(100vh-9rem)] flex items-center justify-center relative overflow-hidden py-12 px-4">

        {{-- Background --}}
        <div class="gradient-bg absolute inset-0 z-0"></div>
        {{-- Subtle dot pattern --}}
        <div class="absolute inset-0 z-0 opacity-10" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 24px 24px;"></div>
        {{-- Blurred orbs --}}
        <div class="absolute top-1/4 -left-32 w-96 h-96 bg-yellow-400/20 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 -right-32 w-96 h-96 bg-orange-300/20 rounded-full blur-3xl"></div>

        {{-- Login Card --}}
        <div class="login-card relative z-10 w-full max-w-md rounded-2xl shadow-2xl overflow-hidden">

            {{-- Card header --}}
            <div class="bg-orange-800 dark:bg-orange-900 px-8 pt-8 pb-6 text-center relative overflow-hidden">
                <div class="absolute -top-10 -right-10 w-40 h-40 bg-orange-700/50 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-6 -left-6 w-32 h-32 bg-yellow-400/10 rounded-full blur-xl"></div>
                <div class="relative z-10">
                    <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-400/20 border-2 border-yellow-400/40 rounded-full mb-4">
                        <i data-lucide="log-in" class="w-8 h-8 text-yellow-400"></i>
                    </div>
                    <h1 class="text-2xl font-extrabold text-white mb-1">Bienvenido</h1>
                    <p class="text-orange-200 text-sm">Accede a tu cuenta de Punjab Idiomas</p>
                </div>
            </div>

            {{-- Card body --}}
            <div class="px-8 py-8 dark:text-slate-100">

                {{-- Error messages --}}
                @if ($errors->any())
                    <div class="mb-6 flex items-start gap-3 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-red-700 dark:text-red-300 rounded-lg px-4 py-3">
                        <i data-lucide="alert-circle" class="w-5 h-5 mt-0.5 flex-shrink-0"></i>
                        <div>
                            @foreach ($errors->all() as $error)
                                <p class="text-sm">{{ $error }}</p>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Session status (e.g. after logout) --}}
                @if (session('status'))
                    <div class="mb-6 flex items-center gap-3 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 rounded-lg px-4 py-3 text-sm">
                        <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
                        {{ session('status') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                    @csrf

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Correo electrónico
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i data-lucide="mail" class="w-4 h-4 text-slate-400 dark:text-slate-500"></i>
                            </div>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                required
                                autofocus
                                autocomplete="email"
                                placeholder="tu@correo.com"
                                class="input-field w-full pl-10 pr-4 py-3 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm @error('email') border-red-400 dark:border-red-500 @enderror"
                            />
                        </div>
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="password" class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-2">
                            Contraseña
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <i data-lucide="lock" class="w-4 h-4 text-slate-400 dark:text-slate-500"></i>
                            </div>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="input-field w-full pl-10 pr-12 py-3 rounded-lg border border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-white text-sm"
                            />
                            {{-- Toggle password visibility --}}
                            <button type="button" id="toggle-password" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                                <i data-lucide="eye" class="w-4 h-4" id="eye-icon"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Remember me --}}
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            class="w-4 h-4 text-orange-600 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-600 rounded focus:ring-orange-500 focus:ring-2">
                        <label for="remember" class="ml-2 text-sm text-slate-600 dark:text-slate-400">
                            Recordarme
                        </label>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                        class="w-full bg-orange-700 hover:bg-orange-800 active:scale-[0.98] text-white font-bold py-3.5 px-6 rounded-lg transition-all duration-200 shadow-md hover:shadow-lg flex items-center justify-center gap-2 text-base">
                        <i data-lucide="log-in" class="w-5 h-5"></i>
                        Iniciar Sesión
                    </button>
                </form>

                {{-- Divider --}}
                <div class="my-6 flex items-center gap-3">
                    <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
                    <span class="text-xs text-slate-400 dark:text-slate-500 font-medium">¿Nuevo aquí?</span>
                    <div class="h-px flex-1 bg-slate-200 dark:bg-slate-700"></div>
                </div>

                <a href="{{ url('/#contacto') }}"
                    class="block w-full text-center border-2 border-orange-700 dark:border-yellow-400 text-orange-700 dark:text-yellow-400 hover:bg-orange-50 dark:hover:bg-slate-700 font-bold py-3 px-6 rounded-lg transition-all duration-200 text-sm">
                    Contactar para inscribirse
                </a>
            </div>
        </div>
    </main>

    {{-- Footer strip --}}
    <div class="bg-slate-900 text-slate-500 text-xs text-center py-3 border-t border-slate-800">
        © {{ date('Y') }} Punjab Idiomas. Todos los derechos reservados.
    </div>

    <script>
        lucide.createIcons();

        // ── Theme ──
        function setIcons(dark) {
            ['theme-icon-dark','theme-icon-dark-mobile'].forEach(id => document.getElementById(id)?.classList.toggle('hidden', dark));
            ['theme-icon-light','theme-icon-light-mobile'].forEach(id => document.getElementById(id)?.classList.toggle('hidden', !dark));
        }
        const isDark = localStorage.getItem('color-theme') === 'dark'
            || (!localStorage.getItem('color-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches);
        document.documentElement.classList.toggle('dark', isDark);
        setIcons(isDark);

        function toggleTheme() {
            const nowDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('color-theme', nowDark ? 'dark' : 'light');
            setIcons(nowDark);
        }
        document.getElementById('theme-toggle')?.addEventListener('click', toggleTheme);
        document.getElementById('theme-toggle-mobile')?.addEventListener('click', toggleTheme);

        // ── Custom cursor ──
        const cursor = document.getElementById('custom-cursor');
        document.addEventListener('mousemove', e => {
            cursor.style.transform = `translate3d(${e.clientX - 10}px,${e.clientY - 10}px,0)`;
        });
        document.querySelectorAll('a,button,input').forEach(el => {
            el.addEventListener('mouseenter', () => cursor.classList.add('cursor-active'));
            el.addEventListener('mouseleave', () => cursor.classList.remove('cursor-active'));
        });

        // ── Password toggle ──
        const toggleBtn  = document.getElementById('toggle-password');
        const passInput  = document.getElementById('password');
        const eyeIcon    = document.getElementById('eye-icon');
        toggleBtn?.addEventListener('click', () => {
            const show = passInput.type === 'password';
            passInput.type = show ? 'text' : 'password';
            eyeIcon.setAttribute('data-lucide', show ? 'eye-off' : 'eye');
            lucide.createIcons();
        });
    </script>
</body>
</html>
