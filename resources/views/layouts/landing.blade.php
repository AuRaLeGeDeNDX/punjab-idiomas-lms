<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Punjab Idiomas | Escuela de Español en Barcelona')</title>

    <!-- SEO & Meta Tags -->
    <meta name="description" content="@yield('description', 'Aprende español en Barcelona con Punjab Idiomas. Cursos A1-B2, preparación oficial DELE y plataforma LMS avanzada.')">
    <meta name="keywords" content="Barcelona Spanish School, Learn Spanish Barcelona, DELE Preparation, Spanish Courses Spain, Punjab Idiomas">
    <meta name="author" content="Punjab Idiomas">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('title', 'Punjab Idiomas | Escuela de Español en Barcelona')">
    <meta property="og:description" content="Domina el español con nuestros cursos especializados. Preparación DELE y soporte LMS 24/7.">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">

    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('title', 'Punjab Idiomas | Escuela de Español en Barcelona')">
    <meta property="twitter:description" content="Domina el español con nuestros cursos especializados. Preparación DELE y soporte LMS 24/7.">
    <meta property="twitter:image" content="{{ asset('images/logo.png') }}">

    <!-- Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
      "{{ '@' }}context": "https://schema.org",
      "{{ '@' }}type": "EducationalOrganization",
      "name": "Punjab Idiomas",
      "url": "{{ url('/') }}",
      "logo": "{{ asset('logo.jpeg') }}",
      "description": "Premium Spanish language school in Barcelona specialized in DELE preparation.",
      "address": {
        "{{ '@' }}type": "PostalAddress",
        "streetAddress": "Calle Padilla 391",
        "addressLocality": "Barcelona",
        "postalCode": "08025",
        "addressCountry": "ES"
      },
      "contactPoint": {
        "{{ '@' }}type": "ContactPoint",
        "telephone": "+34612455057",
        "contactType": "customer service"
      }
    }
    </script>

    <!-- Tailwind 4 -->
    <script src="https://unpkg.com/{{ '@' }}tailwindcss/browser@4"></script>

    <!-- GSAP / Anime.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/animejs/3.2.1/anime.min.js"></script>
    
    <!-- tsParticles -->
    <script src="https://cdn.jsdelivr.net/npm/tsparticles-confetti@2.12.0/tsparticles.confetti.bundle.min.js"></script>

    <style>
        :root {
            --primary: #ff6a00;
            --primary-dark: #e65c00;
            --secondary: #0f0f14;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--secondary);
            color: white;
            overflow-x: hidden;
        }

        .font-orbitron { font-family: 'Orbitron', sans-serif; }

        #cursor-dot, #cursor-outline {
            position: fixed; top: 0; left: 0;
            transform: translate(-50%, -50%);
            pointer-events: none; z-index: 9999;
            border-radius: 50%;
        }
        #cursor-dot { width: 8px; height: 8px; background: var(--primary); }
        #cursor-outline {
            width: 40px; height: 40px;
            border: 2px solid var(--primary);
            transition: .15s ease-out;
        }

        .light-mode {
            background: #f8fafc;
            color: #1e293b;
        }
        .light-mode header { background: rgba(255, 255, 255, 0.8); }

        @yield('styles')
    </style>
</head>
<body class="antialiased">
    <div id="tsparticles" class="fixed inset-0 -z-10 pointer-events-none"></div>

    <div id="cursor-dot"></div>
    <div id="cursor-outline"></div>

    @yield('content')

    <!-- Global Scripts -->
    <script>
        const dot = document.getElementById('cursor-dot');
        const outline = document.getElementById('cursor-outline');
        window.addEventListener('mousemove', e => {
            dot.style.left = e.clientX + 'px';
            dot.style.top = e.clientY + 'px';
            outline.style.left = e.clientX + 'px';
            outline.style.top = e.clientY + 'px';
        });

        function toggleMode() {
            document.body.classList.toggle('light-mode');
            const icon = document.getElementById('mode-icon');
            if (icon) {
                if (document.body.classList.contains('light-mode')) {
                    icon.classList.replace('fa-moon', 'fa-sun');
                } else {
                    icon.classList.replace('fa-sun', 'fa-moon');
                }
            }
        }
    </script>
    @yield('scripts')
</body>
</html>
