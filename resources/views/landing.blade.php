<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punjab Idiomas</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: {} } }
    </script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <style>
        html { scroll-behavior: smooth; }
        #custom-cursor {
            width: 20px; height: 20px;
            background: rgba(194, 65, 12, 0.2);
            border: 2px solid #c2410c;
            border-radius: 50%; position: fixed;
            pointer-events: none; z-index: 9999;
            transition: transform 0.1s ease-out, width 0.3s, height 0.3s, background 0.3s;
            display: none;
        }
        .dark #custom-cursor { background: rgba(250, 204, 21, 0.2); border-color: #facc15; }
        @media (min-width: 768px) {
            #custom-cursor { display: block; }
            body { cursor: none; }
            a, button, select, input, textarea, .card-3d, .faq-btn { cursor: none; }
        }
        .cursor-active { width: 50px !important; height: 50px !important; background: rgba(194, 65, 12, 0.1) !important; border-color: #fbbf24 !important; }
        .dark .cursor-active { background: rgba(250, 204, 21, 0.1) !important; border-color: #c2410c !important; }
        .perspective-1000 { perspective: 1000px; }
        .card-3d { transition: transform 0.1s ease-out, box-shadow 0.3s ease, background-color 0.3s ease, border-color 0.3s ease; transform-style: preserve-3d; will-change: transform; }

        /* Refined Contact Map */
        .contact-map-wrapper {
            width: auto;
            height: 320px;
            overflow: hidden;
            margin: 24px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .dark .contact-map-wrapper {
            border: 1px solid rgba(255,255,255,0.05);
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        }

        @media (max-width: 767px) {
            .contact-map-wrapper {
                height: 250px;
                margin: 16px;
                border-radius: 12px;
            }
        }
    </style>
</head>
<body class="font-sans text-slate-800 bg-slate-50 dark:bg-slate-900 dark:text-slate-200 min-h-screen transition-colors duration-300 overflow-x-hidden">

    <div id="custom-cursor"></div>

    {{-- Top Info Bar --}}
    <div class="bg-orange-900 dark:bg-orange-950 text-white py-2 px-4 hidden md:flex justify-between items-center text-sm transition-colors duration-300">
        <div class="max-w-7xl mx-auto w-full flex justify-between items-center">
            <div class="flex space-x-6">
                <span class="flex items-center"><i data-lucide="mail" class="w-4 h-4 mr-2 text-yellow-400"></i> idiomaspunjab@gmail.com</span>
                <span class="flex items-center"><i data-lucide="phone" class="w-4 h-4 mr-2 text-yellow-400"></i> +34 612 45 50 57</span>
                <span class="flex items-center"><i data-lucide="map-pin" class="w-4 h-4 mr-2 text-yellow-400"></i> Calle Padilla 391, Barcelona</span>
            </div>
            <div class="flex space-x-4">
                <a href="#" class="hover:text-yellow-400 transition-colors"><i data-lucide="instagram" class="w-4 h-4"></i></a>
                <a href="#" class="hover:text-yellow-400 transition-colors"><i data-lucide="facebook" class="w-4 h-4"></i></a>
                <a href="#" class="hover:text-yellow-400 transition-colors"><i data-lucide="youtube" class="w-4 h-4"></i></a>
            </div>
        </div>
    </div>

    {{-- Navbar --}}
    <nav class="bg-white dark:bg-slate-800 shadow-md sticky top-0 z-40 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20 items-center">
                <a href="#inicio" class="flex-shrink-0 flex items-center cursor-pointer no-underline">
                    <img src="{{ asset('images/logo.png') }}" alt="Punjab Idiomas Logo" class="h-20 w-auto mr-3 rounded-sm">
                    <div>
                        <span class="font-extrabold text-2xl text-orange-900 dark:text-orange-500 leading-none block">PUNJAB</span>
                        <span class="font-semibold text-sm text-yellow-500 uppercase tracking-wider block">Idiomas</span>
                    </div>
                </a>

                <div class="hidden md:flex space-x-6 lg:space-x-8 items-center">
                    <a href="#inicio" class="text-slate-700 dark:text-slate-200 hover:text-orange-700 dark:hover:text-yellow-400 font-semibold transition-colors">Inicio</a>
                    <a href="#nosotros" class="text-slate-700 dark:text-slate-200 hover:text-orange-700 dark:hover:text-yellow-400 font-semibold transition-colors">Sobre Nosotros</a>
                    <a href="#servicios" class="text-slate-700 dark:text-slate-200 hover:text-orange-700 dark:hover:text-yellow-400 font-semibold transition-colors">Servicios</a>
                    <a href="#faq" class="text-slate-700 dark:text-slate-200 hover:text-orange-700 dark:hover:text-yellow-400 font-semibold transition-colors">FAQ</a>
                    
                    <!-- Language Selector -->
                    <div class="relative group">
                        <button class="flex items-center text-slate-700 dark:text-slate-200 hover:text-orange-700 dark:hover:text-yellow-400 font-semibold transition-colors focus:outline-none">
                            <i data-lucide="globe" class="w-5 h-5 mr-1"></i>
                            <span class="uppercase">{{ app()->getLocale() }}</span>
                            <i data-lucide="chevron-down" class="w-4 h-4 ml-1"></i>
                        </button>
                        <div class="absolute right-0 mt-2 w-32 bg-white dark:bg-slate-800 rounded-md shadow-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 border border-slate-100 dark:border-slate-700">
                            <ul class="py-1 text-sm text-slate-700 dark:text-slate-200 text-center">
                                <li><a href="#" onclick="setLanguage('es', event)" class="block px-4 py-2 hover:bg-orange-50 dark:hover:bg-slate-700 hover:text-orange-700 dark:hover:text-yellow-400">Español</a></li>
                                <li><a href="#" onclick="setLanguage('ca', event)" class="block px-4 py-2 hover:bg-orange-50 dark:hover:bg-slate-700 hover:text-orange-700 dark:hover:text-yellow-400">Català</a></li>
                                <li><a href="#" onclick="setLanguage('en', event)" class="block px-4 py-2 hover:bg-orange-50 dark:hover:bg-slate-700 hover:text-orange-700 dark:hover:text-yellow-400">English</a></li>
                                <li><a href="#" onclick="setLanguage('hi', event)" class="block px-4 py-2 hover:bg-orange-50 dark:hover:bg-slate-700 hover:text-orange-700 dark:hover:text-yellow-400">हिन्दी</a></li>
                                <li><a href="#" onclick="setLanguage('pa', event)" class="block px-4 py-2 hover:bg-orange-50 dark:hover:bg-slate-700 hover:text-orange-700 dark:hover:text-yellow-400">ਪੰਜਾਬੀ</a></li>
                                <li><a href="#" onclick="setLanguage('ur', event)" class="block px-4 py-2 hover:bg-orange-50 dark:hover:bg-slate-700 hover:text-orange-700 dark:hover:text-yellow-400">اردو</a></li>
                            </ul>
                        </div>
                    </div>

                    <button id="theme-toggle" type="button" class="text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 focus:outline-none rounded-lg text-sm p-2.5 transition-colors">
                        <i id="theme-toggle-dark-icon" data-lucide="moon" class="hidden w-5 h-5"></i>
                        <i id="theme-toggle-light-icon" data-lucide="sun" class="hidden w-5 h-5 text-yellow-400"></i>
                    </button>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="flex items-center text-orange-700 dark:text-yellow-400 border-2 border-orange-700 dark:border-yellow-400 hover:bg-orange-50 dark:hover:bg-slate-700 px-4 py-2 rounded-md font-bold transition-colors shadow-sm">
                            <i data-lucide="layout-dashboard" class="w-4 h-4 mr-2"></i> Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="flex items-center text-orange-700 dark:text-yellow-400 border-2 border-orange-700 dark:border-yellow-400 hover:bg-orange-50 dark:hover:bg-slate-700 px-4 py-2 rounded-md font-bold transition-colors shadow-sm">
                            <i data-lucide="user" class="w-4 h-4 mr-2"></i> Iniciar Sesión
                        </a>
                    @endauth
                    <a href="#contacto" class="bg-yellow-400 hover:bg-yellow-500 text-orange-900 px-6 py-2.5 rounded-md font-bold transition-colors shadow-sm">
                        Inscríbete
                    </a>
                </div>

                <div class="md:hidden flex items-center space-x-2">
                    <button id="theme-toggle-mobile" type="button" class="text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-700 focus:outline-none rounded-lg text-sm p-2 transition-colors">
                        <i id="theme-toggle-dark-icon-mobile" data-lucide="moon" class="hidden w-5 h-5"></i>
                        <i id="theme-toggle-light-icon-mobile" data-lucide="sun" class="hidden w-5 h-5 text-yellow-400"></i>
                    </button>
                    <button id="mobile-menu-btn" class="text-slate-700 dark:text-slate-200 focus:outline-none p-2 hover:bg-slate-100 dark:hover:bg-slate-700 rounded-lg">
                        <i data-lucide="menu" class="w-7 h-7" id="menu-icon"></i>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden bg-white dark:bg-slate-800 border-t border-gray-100 dark:border-slate-700 absolute w-full shadow-lg transition-colors duration-300">
            <div class="px-4 pt-2 pb-6 space-y-1">
                <a href="#inicio" class="mobile-link block w-full text-left px-3 py-3 text-base font-medium text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-orange-700 dark:hover:text-yellow-400 transition-colors">Inicio</a>
                <a href="#nosotros" class="mobile-link block w-full text-left px-3 py-3 text-base font-medium text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-orange-700 dark:hover:text-yellow-400 transition-colors">Sobre Nosotros</a>
                <a href="#servicios" class="mobile-link block w-full text-left px-3 py-3 text-base font-medium text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-orange-700 dark:hover:text-yellow-400 transition-colors">Servicios</a>
                <a href="#faq" class="mobile-link block w-full text-left px-3 py-3 text-base font-medium text-slate-700 dark:text-slate-200 hover:bg-gray-50 dark:hover:bg-slate-700 hover:text-orange-700 dark:hover:text-yellow-400 transition-colors">Preguntas Frecuentes</a>
                
                <div class="px-3 py-2">
                    <label class="block text-sm font-medium text-slate-500 dark:text-slate-400 mb-2">Idioma</label>
                    <div class="grid grid-cols-2 gap-2">
                        <a href="#" onclick="setLanguage('es', event)" class="text-center px-2 py-1.5 border border-slate-200 dark:border-slate-600 rounded-md text-sm {{ app()->getLocale() == 'es' ? 'bg-orange-50 dark:bg-slate-700 text-orange-700 dark:text-yellow-400 font-bold border-orange-300 dark:border-yellow-500' : 'text-slate-600 dark:text-slate-300' }}">Español</a>
                        <a href="#" onclick="setLanguage('ca', event)" class="text-center px-2 py-1.5 border border-slate-200 dark:border-slate-600 rounded-md text-sm {{ app()->getLocale() == 'ca' ? 'bg-orange-50 dark:bg-slate-700 text-orange-700 dark:text-yellow-400 font-bold border-orange-300 dark:border-yellow-500' : 'text-slate-600 dark:text-slate-300' }}">Català</a>
                        <a href="#" onclick="setLanguage('en', event)" class="text-center px-2 py-1.5 border border-slate-200 dark:border-slate-600 rounded-md text-sm {{ app()->getLocale() == 'en' ? 'bg-orange-50 dark:bg-slate-700 text-orange-700 dark:text-yellow-400 font-bold border-orange-300 dark:border-yellow-500' : 'text-slate-600 dark:text-slate-300' }}">English</a>
                        <a href="#" onclick="setLanguage('hi', event)" class="text-center px-2 py-1.5 border border-slate-200 dark:border-slate-600 rounded-md text-sm {{ app()->getLocale() == 'hi' ? 'bg-orange-50 dark:bg-slate-700 text-orange-700 dark:text-yellow-400 font-bold border-orange-300 dark:border-yellow-500' : 'text-slate-600 dark:text-slate-300' }}">हिन्दी</a>
                        <a href="#" onclick="setLanguage('pa', event)" class="col-span-2 text-center px-2 py-1.5 border border-slate-200 dark:border-slate-600 rounded-md text-sm {{ app()->getLocale() == 'pa' ? 'bg-orange-50 dark:bg-slate-700 text-orange-700 dark:text-yellow-400 font-bold border-orange-300 dark:border-yellow-500' : 'text-slate-600 dark:text-slate-300' }}">ਪੰਜਾਬੀ</a>
                    </div>
                </div>

                <div class="pt-4 pb-2 border-t border-slate-100 dark:border-slate-700 mt-2 space-y-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="mobile-link flex justify-center items-center w-full border-2 border-orange-700 dark:border-yellow-400 text-orange-700 dark:text-yellow-400 px-3 py-3 rounded-md font-bold hover:bg-orange-50 dark:hover:bg-slate-700 transition-colors">
                            <i data-lucide="layout-dashboard" class="w-5 h-5 mr-2"></i> Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="mobile-link flex justify-center items-center w-full border-2 border-orange-700 dark:border-yellow-400 text-orange-700 dark:text-yellow-400 px-3 py-3 rounded-md font-bold hover:bg-orange-50 dark:hover:bg-slate-700 transition-colors">
                            <i data-lucide="user" class="w-5 h-5 mr-2"></i> Iniciar Sesión
                        </a>
                    @endauth
                    <a href="#contacto" class="mobile-link block w-full text-center bg-yellow-400 hover:bg-yellow-500 text-orange-900 px-3 py-3 rounded-md font-bold transition-colors">
                        Contáctanos
                    </a>
                </div>
            </div>
        </div>
    </nav>

    {{-- Hero --}}
    <section id="inicio" class="relative pt-20 pb-32 flex items-center min-h-[80vh] overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1522202176988-66273c2fd55f?auto=format&fit=crop&q=80&w=2000" alt="Estudiantes aprendiendo" class="w-full h-full object-cover" />
            <div class="absolute inset-0 bg-orange-900/80 mix-blend-multiply dark:bg-slate-900/90 transition-colors duration-300"></div>
            <div class="absolute inset-0 bg-gradient-to-r from-orange-900/90 dark:from-slate-900/90 to-transparent transition-colors duration-300"></div>
        </div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full" data-aos="fade-up" data-aos-duration="1000">
            <div class="max-w-2xl">
                <span class="inline-block py-1 px-3 rounded-full bg-yellow-400/20 text-yellow-300 font-semibold text-sm mb-6 border border-yellow-400/30">
                    Centro de Español en Barcelona
                </span>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-extrabold text-white leading-tight mb-6">
                    Domina el idioma.<br />
                    <span class="text-yellow-400">Gana confianza.</span>
                </h1>
                <p class="text-lg md:text-xl text-slate-200 mb-8 leading-relaxed">
                    Te ayudamos a aprender el idioma con seguridad y prepararte con éxito para los exámenes oficiales. Desde el nivel A1 hasta B2, con una enseñanza estructurada y clara.
                </p>
                <div class="flex flex-col sm:flex-row space-y-4 sm:space-y-0 sm:space-x-4">
                    <a href="#servicios" class="inline-block bg-yellow-400 hover:bg-yellow-500 text-orange-900 px-8 py-3.5 rounded-md font-bold text-lg transition-all text-center shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Nuestros Cursos
                    </a>
                    <a href="#contacto" class="inline-block bg-transparent border-2 border-white hover:bg-white hover:text-orange-900 text-white px-8 py-3.5 rounded-md font-bold text-lg transition-all text-center">
                        Pedir Información
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Founder --}}
<section id="founder" class="py-20 bg-orange-50 dark:bg-slate-800/50 transition-colors duration-300 overflow-hidden">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col lg:flex-row items-center gap-16 lg:gap-24">
            
            <div class="w-full lg:w-1/2 relative" data-aos="fade-right">
                <div class="absolute -top-10 -left-10 w-64 h-64 bg-yellow-400/20 rounded-full blur-3xl"></div>
                
                <div class="relative flex items-center justify-center">
                    <div class="relative z-10 w-2/3 shadow-2xl rounded-2xl overflow-hidden border-8 border-white dark:border-slate-900 transform -rotate-3 hover:rotate-0 transition-transform duration-500">
                        <img src="{{ asset('founder_pic/priya1.jpeg') }}" alt="Priyal Kalra Main" class="w-full h-full object-cover">
                    </div>

                    <div class="absolute -bottom-10 -right-4 z-20 w-1/2 shadow-2xl rounded-2xl overflow-hidden border-8 border-white dark:border-slate-900 transform rotate-6 hover:rotate-0 transition-transform duration-500">
                        <img src="{{ asset('founder_pic/priya2.jpeg') }}" alt="Priyal Kalra Profile" class="w-full h-full object-cover">
                    </div>
                </div>

                <div class="absolute top-0 right-0 bg-orange-700 text-white p-4 rounded-xl z-30 shadow-lg hidden md:block">
                    <p class="text-sm font-bold uppercase">Enfoque</p>
                    <p class="text-xs opacity-80 italic">Cultural y Práctico</p>
                </div>
            </div>

            <div class="w-full lg:w-1/2" data-aos="fade-left">
                <span class="text-orange-600 dark:text-orange-500 font-bold tracking-widest uppercase text-sm block mb-2">Fundadora</span>
                <h2 class="text-4xl md:text-5xl font-extrabold text-slate-900 dark:text-white mb-6">Priyal Kalra</h2>
                
                <h3 class="text-xl font-semibold text-orange-800 dark:text-yellow-400 mb-6">
                    Cofundadora de Punjab Idiomas
                </h3>
                
                <div class="space-y-6 text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
                    <p class="border-l-4 border-orange-500 pl-4 bg-orange-100/30 dark:bg-orange-900/10 py-2">
                        Un centro especializado en la enseñanza de español para la comunidad punjabi y estudiantes internacionales.
                    </p>
                    <p>
                        Con una visión enfocada en la **integración y el empoderamiento**, impulsa un aprendizaje práctico y cercano, creando un entorno culturalmente familiar que ayuda a sus alumnos a ganar confianza y desenvolverse con seguridad en España.
                    </p>
                </div>

                <div class="mt-12 pt-8 border-t border-slate-200 dark:border-slate-700 flex flex-wrap gap-8">
                    <div>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">Confianza</p>
                        <p class="text-sm text-slate-500">Garantizada en clase</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-slate-900 dark:text-white">Empoderamiento</p>
                        <p class="text-sm text-slate-500">Para vivir en España</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>    

    {{-- About --}}
    <section id="nosotros" class="py-20 bg-white dark:bg-slate-900 transition-colors duration-300 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div data-aos="fade-right" data-aos-duration="800">
                    <h2 class="text-base text-orange-600 dark:text-orange-500 font-bold tracking-wide uppercase">Sobre Nosotros</h2>
                    <h3 class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">Punjab Idiomas</h3>
                    <p class="mt-6 text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
                        Punjab Idiomas es un centro de enseñanza de español en Barcelona dedicado a ayudarte a aprender el idioma con confianza y a prepararte con éxito para los exámenes oficiales.
                    </p>
                    <p class="mt-4 text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
                        Ofrecemos una formación estructurada, clara y sólida, basada en fundamentos teóricos que garantizan un aprendizaje profundo y progresivo. Nuestro programa está diseñado para acompañarte paso a paso, desde el nivel A1 hasta el B2, proporcionando una guía completa y bien organizada para que avances con seguridad.
                    </p>
                   <p class="mt-4 text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
                   Además, somos una de las primeras plataformas informativas en Barcelona que, más allá de impartir clases, brinda un apoyo integral al estudiante: recursos didácticos, materiales de estudio actualizados y una preparación estratégica enfocada específicamente en los exámenes DELE.
                    </p>

                     <p class="mt-4 text-lg text-slate-600 dark:text-slate-400 leading-relaxed">
                     En Punjab Idiomas, no solo aprendes español, construyes una base sólida para alcanzar tus metas académicas y profesionales.

                    </p>
                    <p class="mt-4 text-lg text-slate-800 dark:text-slate-200 font-semibold bg-orange-50 dark:bg-slate-800 p-4 border-l-4 border-orange-600 dark:border-orange-500 rounded-r-md transition-colors duration-300">
                        Tanto si estás empezando desde cero como si deseas prepararte para los exámenes oficiales DELE, nuestro objetivo es que te sientas acompañado, bien preparado y con confianza en cada etapa de tu aprendizaje de idiomas.
                    </p>
                </div>
                <div class="space-y-6" data-aos="fade-left" data-aos-duration="800" data-aos-delay="200">
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-6 md:p-8 shadow-sm border border-slate-100 dark:border-slate-700 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <div class="bg-orange-100 dark:bg-orange-900/50 p-3 rounded-lg text-orange-700 dark:text-orange-400 mr-4">
                                <i data-lucide="target" class="w-7 h-7"></i>
                            </div>
                            <h4 class="text-2xl font-bold text-slate-900 dark:text-white">Nuestra Misión</h4>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Formar estudiantes con bases sólidas, confianza y apoyo constante para que puedan aprobar con éxito los exámenes DELE A1 a B2, con material completo y enseñanza de alta calidad.
                        </p>
                    </div>
                    <div class="bg-slate-50 dark:bg-slate-800 rounded-xl p-6 md:p-8 shadow-sm border border-slate-100 dark:border-slate-700 hover:shadow-md transition-all duration-300">
                        <div class="flex items-center mb-4">
                            <div class="bg-yellow-100 dark:bg-yellow-900/30 p-3 rounded-lg text-yellow-600 dark:text-yellow-400 mr-4">
                                <i data-lucide="eye" class="w-7 h-7"></i>
                            </div>
                            <h4 class="text-2xl font-bold text-slate-900 dark:text-white">Nuestra Visión</h4>
                        </div>
                        <p class="text-slate-600 dark:text-slate-400 leading-relaxed">
                            Convertirnos en una de las escuelas más confiables de España en enseñanza teórica del español, reconocida por la calidad, el acompañamiento y un enfoque centrado en el estudiante.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    


    {{-- Services --}}
    <section id="servicios" class="py-20 bg-slate-50 dark:bg-slate-900/50 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16" data-aos="fade-up">
                <h2 class="text-base text-orange-600 dark:text-orange-500 font-bold tracking-wide uppercase">Nuestros Servicios</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">Cursos Adaptados a Tus Necesidades</p>
                <p class="mt-4 text-xl text-slate-600 dark:text-slate-400">Ofrecemos una variedad de programas para asegurar que alcances tus metas con el idioma español, sea cual sea tu nivel inicial.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

                <div class="perspective-1000" data-aos="zoom-in" data-aos-delay="100">
                    <div class="card-3d bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 md:p-8 flex flex-col h-full" onmousemove="handle3D(event, this)" onmouseleave="reset3D(this)">
                        <i data-lucide="book-open" class="text-yellow-500 w-10 h-10 mb-4"></i>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Cursos de Español (A1 a B2)</h3>
                        <p class="text-slate-600 dark:text-slate-400 mb-6 flex-grow">Ofrecemos cursos completos para todos los niveles (A1, A2, B1 y B2) para que avances con seguridad.</p>
                        <ul class="space-y-2 mt-auto">
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Clases estructuradas y gramática clara</span></li>
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Práctica oral y ejercicios guiados</span></li>
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Material de estudio completo incluido</span></li>
                        </ul>
                    </div>
                </div>

                <div class="perspective-1000" data-aos="zoom-in" data-aos-delay="200">
                    <div class="card-3d bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 md:p-8 flex flex-col h-full" onmousemove="handle3D(event, this)" onmouseleave="reset3D(this)">
                        <i data-lucide="graduation-cap" class="text-yellow-500 w-10 h-10 mb-4"></i>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Preparación Examen DELE (A1 a B2)</h3>
                        <p class="text-slate-600 dark:text-slate-400 mb-6 flex-grow">Preparamos a nuestros estudiantes con un método enfocado al examen y resultados reales.</p>
                        <ul class="space-y-2 mt-auto">
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Explicación completa del formato DELE</span></li>
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Práctica con modelos y exámenes anteriores</span></li>
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Preparación intensiva de expresión oral y escrita</span></li>
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Simulacros y entrenamiento tipo examen</span></li>
                        </ul>
                    </div>
                </div>

                <div class="perspective-1000" data-aos="zoom-in" data-aos-delay="300">
                    <div class="card-3d bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 md:p-8 flex flex-col h-full" onmousemove="handle3D(event, this)" onmouseleave="reset3D(this)">
                        <i data-lucide="message-square" class="text-yellow-500 w-10 h-10 mb-4"></i>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Preparación Práctica DELE A2</h3>
                        <p class="text-slate-600 dark:text-slate-400 mb-6 flex-grow">Formación especial para quienes necesitan dominar la parte práctica del DELE A2.</p>
                        <ul class="space-y-2 mt-auto">
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Práctica oral guiada</span></li>
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Roleplays reales como en el examen</span></li>
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Corrección, vocabulario y fluidez</span></li>
                        </ul>
                    </div>
                </div>

                <div class="perspective-1000" data-aos="zoom-in" data-aos-delay="400">
                    <div class="card-3d bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 md:p-8 flex flex-col h-full" onmousemove="handle3D(event, this)" onmouseleave="reset3D(this)">
                        <i data-lucide="car" class="text-yellow-500 w-10 h-10 mb-4"></i>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Español B1 para Taxistas</h3>
                        <p class="text-slate-600 dark:text-slate-400 mb-6 flex-grow">Curso específico para taxistas que necesitan el nivel B1, con un enfoque práctico y profesional.</p>
                        <ul class="space-y-2 mt-auto">
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Vocabulario y situaciones reales del trabajo</span></li>
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Comunicación clara con clientes</span></li>
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Preparación completa para los requisitos B1</span></li>
                        </ul>
                    </div>
                </div>

                <div class="perspective-1000" data-aos="zoom-in" data-aos-delay="500">
                    <div class="card-3d bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 md:p-8 flex flex-col h-full" onmousemove="handle3D(event, this)" onmouseleave="reset3D(this)">
                        <i data-lucide="users" class="text-yellow-500 w-10 h-10 mb-4"></i>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Clases de Refuerzo para Niños</h3>
                        <p class="text-slate-600 dark:text-slate-400 mb-6 flex-grow">Clases de apoyo para niños, ideales para mejorar su rendimiento escolar y su nivel de español.</p>
                        <ul class="space-y-2 mt-auto">
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Gramática y vocabulario</span></li>
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Apoyo con deberes</span></li>
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Lectura y escritura</span></li>
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Mejora de confianza al hablar</span></li>
                        </ul>
                    </div>
                </div>

                <div class="perspective-1000" data-aos="zoom-in" data-aos-delay="600">
                    <div class="card-3d bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6 md:p-8 flex flex-col h-full" onmousemove="handle3D(event, this)" onmouseleave="reset3D(this)">
                        <i data-lucide="landmark" class="text-yellow-500 w-10 h-10 mb-4"></i>
                        <h3 class="text-xl font-bold text-slate-900 dark:text-white mb-3">Preparación Nacionalidad Española</h3>
                        <p class="text-slate-600 dark:text-slate-400 mb-6 flex-grow">Preparación integral para personas que solicitan la nacionalidad española.</p>
                        <ul class="space-y-2 mt-auto">
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Formación para las pruebas de los Diplomas de Español como Lengua Extranjera (DELE)</span></li>
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Preparación para las pruebas de Conocimientos Constitucionales y Socioculturales (CCSE)</span></li>
                            <li class="flex items-start text-sm text-slate-700 dark:text-slate-300"><i data-lucide="check-circle" class="text-orange-500 mr-2 shrink-0 mt-0.5 w-4 h-4"></i><span>Apoyo guiado durante los requisitos del examen</span></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- FAQ --}}
    <section id="faq" class="py-20 bg-white dark:bg-slate-900 transition-colors duration-300">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8" data-aos="fade-up">
            <div class="text-center mb-12">
                <h2 class="text-base text-orange-600 dark:text-orange-500 font-bold tracking-wide uppercase">Dudas Comunes</h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl">Preguntas Frecuentes</p>
            </div>
            <div class="space-y-4" id="faq-container">
                @php
                $faqs = [
                    ['q' => '1. ¿Tienen clases para principiantes?', 'a' => 'Sí. Nuestro curso A1 está diseñado para personas que comienzan desde cero.'],
                    ['q' => '2. ¿Incluyen material de estudio?', 'a' => 'Sí. Proporcionamos todo el material necesario y recursos de práctica.'],
                    ['q' => '3. ¿Preparan para los exámenes DELE?', 'a' => 'Sí. Preparamos DELE A1, A2, B1 y B2 con acompañamiento completo.'],
                    ['q' => '4. ¿Ofrecen preparación para la parte práctica del DELE A2?', 'a' => 'Sí. Incluye práctica oral, roleplays y entrenamiento real.'],
                    ['q' => '5. ¿Ofrecen clases B1 para taxistas?', 'a' => 'Sí. Contamos con un programa específico con vocabulario y práctica profesional.'],
                    ['q' => '6. ¿Tienen clases para niños?', 'a' => 'Sí. Ofrecemos clases de refuerzo para mejorar su nivel y su rendimiento escolar.'],
                    ['q' => '7. ¿Las clases son presenciales u online?', 'a' => 'Puedes ofrecer modalidad presencial, online o ambas, según tu disponibilidad.'],
                    ['q' => '8. ¿Cómo puedo inscribirme?', 'a' => 'Puedes registrarte contactándonos por teléfono, correo electrónico o a través del formulario de la web.'],
                ];
                @endphp
                @foreach($faqs as $faq)
                <div class="border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden bg-white dark:bg-slate-800 hover:border-orange-300 dark:hover:border-orange-500 transition-colors duration-300">
                    <button class="faq-btn w-full px-6 py-5 text-left flex justify-between items-center focus:outline-none" onclick="toggleFaq(this)">
                        <span class="font-semibold text-slate-900 dark:text-white pr-4">{{ $faq['q'] }}</span>
                        <i data-lucide="chevron-down" class="faq-icon text-slate-400 dark:text-slate-500 flex-shrink-0 w-5 h-5 transition-transform duration-200"></i>
                    </button>
                    <div class="faq-answer hidden px-6 pb-5">
                        <p class="text-slate-600 dark:text-slate-400">{{ $faq['a'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- Contact --}}
    <section id="contacto" class="py-20 bg-orange-900 dark:bg-slate-950 relative transition-colors duration-300">
        <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 20px 20px;"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10" data-aos="fade-up">
            <div class="bg-white dark:bg-slate-800 rounded-2xl shadow-2xl overflow-hidden transition-colors duration-300">
                <div class="flex flex-col lg:flex-row">

                <div class="bg-orange-800 dark:bg-orange-900 text-white p-6 md:p-10 lg:w-2/5 flex flex-col justify-between relative overflow-hidden transition-colors duration-300 rounded-2xl">
                    <div class="absolute top-0 right-0 -mt-16 -mr-16 w-64 h-64 bg-orange-700 dark:bg-orange-800 rounded-full opacity-50 blur-3xl transition-colors duration-300"></div>
                    <div class="relative z-10">
                        <h3 class="text-3xl font-bold mb-2 text-yellow-400">Contáctanos</h3>
                        <p class="text-orange-100 mb-8">Estamos aquí para ayudarte. Si deseas inscribirte, conocer los cursos o recibir orientación sobre tu preparación DELE, contáctanos. Te responderemos lo antes posible.</p>
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <i data-lucide="map-pin" class="text-yellow-400 mr-4 mt-1 w-6 h-6"></i>
                                <div><h4 class="font-semibold text-lg">Ubicación</h4><p class="text-orange-100 mt-1">Calle Padilla 391<br/>08025 Barcelona</p></div>
                            </div>
                            <div class="flex items-start">
                                <i data-lucide="clock" class="text-yellow-400 mr-4 mt-1 w-6 h-6"></i>
                                <div><h4 class="font-semibold text-lg">Horario</h4><p class="text-orange-100 mt-1">Lunes a sábado: 10:00 AM - 8:00 PM<br/>Domingo: Cerrado</p></div>
                            </div>
                            <div class="flex items-start">
                                <i data-lucide="mail" class="text-yellow-400 mr-4 mt-1 w-6 h-6"></i>
                                <div><h4 class="font-semibold text-lg">Correo Electrónico</h4><p class="text-orange-100 mt-1">idiomaspunjab@gmail.com</p></div>
                            </div>
                            <div class="flex items-start">
                                <i data-lucide="phone" class="text-yellow-400 mr-4 mt-1 w-6 h-6"></i>
                                <div><h4 class="font-semibold text-lg">Teléfono / WhatsApp</h4><p class="text-orange-100 mt-1">+34 612 45 50 57</p></div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-12 relative z-10">
                        <p class="font-semibold mb-4 text-sm uppercase tracking-wider text-orange-200">Síguenos en</p>
                        <div class="flex space-x-4">
                            <a href="#" class="bg-orange-700 dark:bg-orange-800 p-3 rounded-full hover:bg-yellow-400 hover:text-orange-900 transition-all"><i data-lucide="instagram" class="w-5 h-5"></i></a>
                            <a href="#" class="bg-orange-700 dark:bg-orange-800 p-3 rounded-full hover:bg-yellow-400 hover:text-orange-900 transition-all"><i data-lucide="facebook" class="w-5 h-5"></i></a>
                            <a href="#" class="bg-orange-700 dark:bg-orange-800 p-3 rounded-full hover:bg-yellow-400 hover:text-orange-900 transition-all"><i data-lucide="youtube" class="w-5 h-5"></i></a>
                        </div>
                    </div>
                </div>

                <div class="p-6 md:p-10 lg:w-3/5 bg-white dark:bg-slate-800 transition-colors duration-300">
                    <h3 class="text-2xl font-bold text-slate-900 dark:text-white mb-6">Envíanos un mensaje</h3>
                    <form id="contact-form" class="space-y-6" method="POST" action="{{ route('contact.submit') }}">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Nombre completo</label>
                                <input type="text" id="name" name="name" required class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-colors" placeholder="Juan Pérez" />
                            </div>
                            <div>
                                <label for="email" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Correo electrónico</label>
                                <input type="email" id="email" name="email" required class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-colors" placeholder="juan@ejemplo.com" />
                            </div>
                        </div>
                        <div>
                            <label for="course" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Curso de interés</label>
                            <select id="course" name="course" class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-colors">
                                <option value="">Selecciona una opción...</option>
                                <option value="a1-b2">Cursos de Español (A1 a B2)</option>
                                <option value="dele">Preparación Examen DELE</option>
                                <option value="dele-a2">Preparación Práctica DELE A2</option>
                                <option value="taxistas">Español B1 para Taxistas</option>
                                <option value="ninos">Clases de Refuerzo para Niños</option>
                                <option value="nacionalidad">Preparación Nacionalidad Española</option>
                                <option value="other">Otro / Duda general</option>
                            </select>
                        </div>
                        <div>
                            <label for="message" class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-2">Mensaje</label>
                            <textarea id="message" name="message" rows="4" required class="w-full bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-600 text-slate-900 dark:text-white rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-orange-500 transition-colors resize-none" placeholder="¿En qué podemos ayudarte?"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-4 px-8 rounded-md transition-colors shadow-md text-lg">
                            Enviar Mensaje
                        </button>
                    </form>
                </div>
            </div>

            {{-- Full Width Map --}}
            <div class="contact-map-wrapper">
                <iframe
                    src="https://www.google.com/maps?q=Calle+Padilla+391,+08025+Barcelona&output=embed"
                    width="100%"
                    height="100%"
                    style="border:0; filter: grayscale(0.2) contrast(1.1);"
                    allowfullscreen=""
                    loading="lazy">
                </iframe>
            </div>
        </div>
    </div>
    </section>

    {{-- Footer --}}
    <footer class="bg-slate-900 dark:bg-black text-slate-300 py-12 border-t border-slate-800 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-3 gap-8">
            <div>
                <div class="flex items-center mb-4">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="h-8 w-auto mr-2 rounded-sm">
                    <span class="font-extrabold text-xl text-white tracking-wider">PUNJAB Idiomas</span>
                </div>
                <p class="text-sm text-slate-400 mb-4 max-w-xs">
                    Tu centro de confianza en Barcelona para dominar el español y prepararte para los exámenes oficiales.
                </p>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Enlaces Rápidos</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#inicio" class="hover:text-yellow-400 transition-colors">Inicio</a></li>
                    <li><a href="#nosotros" class="hover:text-yellow-400 transition-colors">Sobre Nosotros</a></li>
                    <li><a href="#servicios" class="hover:text-yellow-400 transition-colors">Nuestros Cursos</a></li>
                    <li><a href="#faq" class="hover:text-yellow-400 transition-colors">Preguntas Frecuentes</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-bold mb-4 uppercase text-sm tracking-wider">Legal</h4>
                <ul class="space-y-2 text-sm text-slate-400">
                    <li><a href="#" class="hover:text-white transition-colors">Aviso Legal</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Política de Privacidad</a></li>
                    <li><a href="#" class="hover:text-white transition-colors">Política de Cookies</a></li>
                </ul>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-12 pt-8 border-t border-slate-800 text-sm text-center md:text-left flex flex-col md:flex-row justify-between items-center">
            <p>© <span id="year"></span> Punjab Idiomas. Todos los derechos reservados.</p>
            <p class="mt-2 md:mt-0 text-slate-500">Barcelona, España</p>
        </div>
    </footer>

    {{-- WhatsApp Button --}}
    <a href="https://wa.me/34612455057" target="_blank" rel="noopener noreferrer"
        class="fixed bottom-4 right-4 md:bottom-6 md:right-6 bg-[#25D366] text-white p-3 md:p-4 rounded-full shadow-2xl hover:bg-[#1ebd59] transition-all transform hover:scale-110 z-50 flex items-center justify-center"
        aria-label="Contactar por WhatsApp">
        <i data-lucide="message-circle" class="w-7 h-7"></i>
    </a>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        lucide.createIcons();
        AOS.init({ once: true, duration: 800 });
        document.getElementById('year').textContent = new Date().getFullYear();

        // Dark Mode
        const themeToggleDarkIcon = document.getElementById('theme-toggle-dark-icon');
        const themeToggleLightIcon = document.getElementById('theme-toggle-light-icon');
        const themeToggleDarkIconMobile = document.getElementById('theme-toggle-dark-icon-mobile');
        const themeToggleLightIconMobile = document.getElementById('theme-toggle-light-icon-mobile');

        function setInitialIcons() {
            if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                themeToggleLightIcon.classList.remove('hidden');
                themeToggleLightIconMobile.classList.remove('hidden');
                document.documentElement.classList.add('dark');
            } else {
                themeToggleDarkIcon.classList.remove('hidden');
                themeToggleDarkIconMobile.classList.remove('hidden');
                document.documentElement.classList.remove('dark');
            }
        }
        setInitialIcons();

        function handleThemeToggle() {
            themeToggleDarkIcon.classList.toggle('hidden');
            themeToggleLightIcon.classList.toggle('hidden');
            themeToggleDarkIconMobile.classList.toggle('hidden');
            themeToggleLightIconMobile.classList.toggle('hidden');
            if (localStorage.getItem('color-theme') === 'light') {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            }
        }
        document.getElementById('theme-toggle').addEventListener('click', handleThemeToggle);
        document.getElementById('theme-toggle-mobile').addEventListener('click', handleThemeToggle);

        // Custom Cursor (Only for fine pointers/mice)
        if (window.matchMedia("(pointer: fine)").matches) {
            const cursor = document.getElementById('custom-cursor');
            document.addEventListener('mousemove', (e) => {
                cursor.style.transform = `translate3d(${e.clientX - 10}px, ${e.clientY - 10}px, 0)`;
            });
            document.querySelectorAll('a, button, .card-3d, input, select, textarea, .faq-btn').forEach(el => {
                el.addEventListener('mouseenter', () => cursor.classList.add('cursor-active'));
                el.addEventListener('mouseleave', () => cursor.classList.remove('cursor-active'));
            });
        }

        // Mobile Menu
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const menuIcon = document.getElementById('menu-icon');
        let isMenuOpen = false;
        mobileMenuBtn.addEventListener('click', () => {
            isMenuOpen = !isMenuOpen;
            mobileMenu.classList.toggle('hidden', !isMenuOpen);
            menuIcon.setAttribute('data-lucide', isMenuOpen ? 'x' : 'menu');
            lucide.createIcons();
        });
        document.querySelectorAll('.mobile-link').forEach(link => {
            link.addEventListener('click', () => {
                isMenuOpen = false;
                mobileMenu.classList.add('hidden');
                menuIcon.setAttribute('data-lucide', 'menu');
                lucide.createIcons();
            });
        });

        // FAQ Accordion
        function toggleFaq(button) {
            const answer = button.nextElementSibling;
            const icon = button.querySelector('.faq-icon');
            const isHidden = answer.classList.contains('hidden');
            document.querySelectorAll('.faq-answer').forEach(el => el.classList.add('hidden'));
            document.querySelectorAll('.faq-icon').forEach(el => {
                el.classList.remove('text-orange-600', 'rotate-180');
                el.classList.add('text-slate-400');
            });
            if (isHidden) {
                answer.classList.remove('hidden');
                icon.classList.remove('text-slate-400');
                icon.classList.add('text-orange-600', 'rotate-180');
            }
        }

        // 3D Tilt
        function handle3D(e, card) {
            if (window.innerWidth < 768) return;
            const rect = card.getBoundingClientRect();
            const rotateX = (((e.clientY - rect.top) / rect.height) - 0.5) * -20;
            const rotateY = (((e.clientX - rect.left) / rect.width) - 0.5) * 20;
            card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(1.05, 1.05, 1.05)`;
            card.style.boxShadow = `${-rotateY}px ${rotateX}px 30px rgba(0,0,0,${document.documentElement.classList.contains('dark') ? 0.6 : 0.15})`;
        }
        function reset3D(card) {
            card.style.transform = 'rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)';
            card.style.boxShadow = 'none';
        }

        // Contact Form AJAX
        const contactForm = document.getElementById('contact-form');
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                fetch(contactForm.action, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '' },
                    body: new FormData(contactForm)
                })
                .then(res => res.json())
                .then(data => {
                    alert(data.message || '¡Mensaje enviado!');
                    contactForm.reset();
                })
                .catch(() => alert('Error al enviar. Inténtalo de nuevo.'));
            });
        }
    </script>
    @include('partials.language-switcher-js')
</body>
</html>
