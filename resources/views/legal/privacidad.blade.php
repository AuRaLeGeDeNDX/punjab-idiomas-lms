<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Política de Privacidad — Punjab Idiomas</title>
    <meta name="description" content="Política de Privacidad de Punjab Idiomas. Información sobre tratamiento de datos personales conforme al RGPD y la LOPDGDD.">
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
            <h1 class="text-3xl md:text-4xl font-extrabold text-white">Política de Privacidad</h1>
            <p class="text-orange-200 mt-2 text-sm">Tratamiento de datos personales conforme al RGPD y la LOPDGDD</p>
        </div>
    </div>

    {{-- Content --}}
    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-8 md:p-12 legal-section">

            <p class="text-slate-500 dark:text-slate-400 text-sm mb-8">Punjab Idiomas Institute cumple con el Reglamento (UE) 2016/679 (RGPD) y la Ley Orgánica 3/2018 (LOPDGDD).</p>

            <h2>Responsable del tratamiento</h2>
            <div class="p-6 bg-slate-50 dark:bg-slate-700/50 rounded-lg border border-slate-200 dark:border-slate-600 mb-4">
                <ul>
                    <li><strong>Entidad:</strong> PUNJAB NANAK SL</li>
                    <li><strong>NIF/CIF:</strong> B55463772</li>
                    <li><strong>Correo electrónico:</strong> Punjabidiomas040@gmail.com</li>
                    <li><strong>Sitio web:</strong> www.punjabidiomas.com</li>
                </ul>
            </div>

            <h2>Datos recopilados</h2>
            <p>Se podrán recoger los siguientes datos:</p>
            <ul>
                <li>Nombre y apellidos</li>
                <li>Correo electrónico</li>
                <li>Datos académicos</li>
                <li>Credenciales de acceso a la plataforma</li>
            </ul>

            <h2>Finalidad</h2>
            <p>Los datos se utilizan para:</p>
            <ul>
                <li>Gestionar la inscripción en cursos</li>
                <li>Proporcionar acceso a la plataforma de alumnos</li>
                <li>Facilitar materiales educativos</li>
                <li>Comunicaciones relacionadas con el servicio</li>
            </ul>

            <h2>Legitimación</h2>
            <ul>
                <li>Ejecución de un contrato (inscripción en cursos)</li>
                <li>Consentimiento del usuario</li>
            </ul>

            <h2>Conservación</h2>
            <p>Los datos se conservarán mientras dure la relación contractual o el tiempo legal necesario.</p>

            <h2>Derechos</h2>
            <p>El usuario puede ejercer sus derechos de acceso, rectificación, supresión, oposición, limitación y portabilidad enviando una solicitud a: <a href="mailto:Punjabidiomas040@gmail.com" class="text-orange-700 dark:text-orange-400 hover:underline">Punjabidiomas040@gmail.com</a></p>
            <p>Asimismo, el usuario tiene derecho a presentar una reclamación ante la Agencia Española de Protección de Datos (AEPD) — <a href="https://www.aepd.es" target="_blank" class="text-orange-700 dark:text-orange-400 hover:underline">www.aepd.es</a> — si considera que el tratamiento de sus datos no es adecuado.</p>

            <h2>Seguridad</h2>
            <p>Se aplican medidas técnicas para proteger los datos, especialmente en el acceso al área privada de estudiantes.</p>

            <h2>Cesión de datos</h2>
            <p>No se cederán datos a terceros salvo obligación legal.</p>

        </div>

        {{-- Back Link + Other Legal Pages --}}
        <div class="mt-8 flex flex-col sm:flex-row gap-4 items-start">
            <a href="{{ url('/') }}" class="inline-flex items-center text-orange-700 dark:text-orange-400 hover:underline font-semibold text-sm">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i> Volver al inicio
            </a>
            <div class="flex flex-wrap gap-4 text-sm">
                <a href="{{ url('/legal-notice') }}" class="text-slate-500 dark:text-slate-400 hover:text-orange-700 dark:hover:text-yellow-400 transition-colors">Aviso Legal</a>
                <a href="{{ url('/cookies-policy') }}" class="text-slate-500 dark:text-slate-400 hover:text-orange-700 dark:hover:text-yellow-400 transition-colors">Política de Cookies</a>
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
