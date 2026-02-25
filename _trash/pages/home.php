<?php
/**
 * Home Page
 */
?>

<!-- Hero Section -->
<section id="home" class="hero-bg h-screen flex items-center justify-center text-center px-4 pt-16">
    <div class="max-w-4xl">
        <span class="inline-block py-1 px-3 rounded-full bg-red-500/20 text-red-100 text-sm font-semibold mb-4 border border-red-500/30">
            Escuela de Idiomas en Barcelona
        </span>
        <h1 class="text-5xl md:text-7xl font-bold text-white mb-6 leading-tight">
            Domina el idioma. <br/>
            <span class="text-red-500">Gana confianza.</span>
        </h1>
        <p class="text-lg md:text-xl text-gray-200 mb-10 max-w-2xl mx-auto">
            Enseñanza estructurada y apoyo real para aprobar los exámenes DELE A1 a B2.
            Tu éxito es nuestra misión.
        </p>
        <div class="flex flex-col md:flex-row gap-4 justify-center">
            <a href="<?php echo url('services'); ?>" class="px-8 py-4 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600 transition shadow-xl transform hover:-translate-y-1">
                Ver Cursos
            </a>
            <a href="<?php echo url('contact'); ?>" class="px-8 py-4 bg-white text-gray-900 font-bold rounded-lg hover:bg-gray-100 transition shadow-xl transform hover:-translate-y-1">
                Inscribirse Ahora
            </a>
        </div>
    </div>
</section>

<!-- About Preview -->
<section id="about" class="py-20 bg-white">
    <div class="container mx-auto px-6">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <h4 class="text-red-500 font-bold uppercase tracking-wide mb-2">Sobre Nosotros</h4>
                <h2 class="text-4xl font-bold text-gray-900 mb-6">Aprendizaje seguro y estructurado en Barcelona</h2>
                <p class="text-gray-600 mb-4 leading-relaxed">
                    Punjab Idiomas es un centro de español en Barcelona creado para ayudarte a aprender el idioma con seguridad y prepararte con éxito para los exámenes oficiales.
                </p>
                <p class="text-gray-600 mb-6 leading-relaxed">
                    Ofrecemos una enseñanza estructurada, clara y basada en teoría, ideal para estudiantes que desean avanzar con una guía completa desde el nivel A1 hasta B2. Somos una de las primeras plataformas informativas en Barcelona que ofrece apoyo real al estudiante.
                </p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mt-8">
                    <div class="p-6 bg-gray-50 rounded-xl border border-gray-100">
                        <i class="fas fa-bullseye text-red-500 text-2xl mb-3"></i>
                        <h3 class="font-bold text-xl mb-2">Nuestra Misión</h3>
                        <p class="text-sm text-gray-600">Formar estudiantes con bases sólidas y confianza para aprobar los exámenes DELE A1 a B2 con alta calidad.</p>
                    </div>
                    <div class="p-6 bg-gray-50 rounded-xl border border-gray-100">
                        <i class="fas fa-eye text-red-500 text-2xl mb-3"></i>
                        <h3 class="font-bold text-xl mb-2">Nuestra Visión</h3>
                        <p class="text-sm text-gray-600">Convertirnos en una de las escuelas más confiables de España, reconocida por la calidad y el acompañamiento.</p>
                    </div>
                </div>

                <a href="<?php echo url('about'); ?>" class="inline-block mt-8 px-6 py-3 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600 transition">
                    Conocer más sobre nosotros
                </a>
            </div>
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80" alt="Students learning" class="rounded-2xl shadow-2xl">
                <div class="absolute -bottom-6 -left-6 bg-red-500 text-white p-6 rounded-xl shadow-lg hidden md:block">
                    <p class="text-2xl font-bold">A1 - B2</p>
                    <p class="text-sm opacity-90">Niveles Completos</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Services Preview -->
<section id="services" class="py-20 bg-gray-900 text-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16">
            <h4 class="text-red-500 font-bold uppercase tracking-wide mb-2">Nuestros Servicios</h4>
            <h2 class="text-3xl md:text-4xl font-bold">Cursos diseñados para tu éxito</h2>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
            <div class="bg-gray-800 p-8 rounded-2xl hover:bg-gray-750 transition border border-gray-700 hover:border-red-500 group">
                <div class="w-14 h-14 bg-red-500/20 text-red-500 rounded-full flex items-center justify-center text-2xl mb-6 group-hover:bg-red-500 group-hover:text-white transition">
                    <i class="fas fa-layer-group"></i>
                </div>
                <h3 class="text-xl font-bold mb-4">Cursos de Español (A1-B2)</h3>
                <p class="text-gray-400 mb-4 text-sm">Niveles desde Principiante (A1) hasta Intermedio Alto (B2). Clases estructuradas, gramática clara y práctica oral.</p>
                <a href="<?php echo url('services'); ?>" class="text-red-500 hover:text-red-400 font-bold text-sm inline-block mt-4">Ver más →</a>
            </div>

            <div class="bg-gray-800 p-8 rounded-2xl hover:bg-gray-750 transition border border-gray-700 hover:border-red-500 group">
                <div class="w-14 h-14 bg-red-500/20 text-red-500 rounded-full flex items-center justify-center text-2xl mb-6 group-hover:bg-red-500 group-hover:text-white transition">
                    <i class="fas fa-graduation-cap"></i>
                </div>
                <h3 class="text-xl font-bold mb-4">Preparación DELE</h3>
                <p class="text-gray-400 mb-4 text-sm">Método enfocado al examen con resultados reales.</p>
                <a href="<?php echo url('services'); ?>" class="text-red-500 hover:text-red-400 font-bold text-sm inline-block mt-4">Ver más →</a>
            </div>

            <div class="bg-gray-800 p-8 rounded-2xl hover:bg-gray-750 transition border border-gray-700 hover:border-red-500 group">
                <div class="w-14 h-14 bg-red-500/20 text-red-500 rounded-full flex items-center justify-center text-2xl mb-6 group-hover:bg-red-500 group-hover:text-white transition">
                    <i class="fas fa-comments"></i>
                </div>
                <h3 class="text-xl font-bold mb-4">Práctica DELE A2</h3>
                <p class="text-gray-400 mb-4 text-sm">Formación especial para dominar la parte práctica del examen.</p>
                <a href="<?php echo url('services'); ?>" class="text-red-500 hover:text-red-400 font-bold text-sm inline-block mt-4">Ver más →</a>
            </div>
        </div>

        <div class="text-center mt-12">
            <a href="<?php echo url('services'); ?>" class="px-8 py-4 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600 transition shadow-xl transform hover:-translate-y-1">
                Ver Todos los Cursos
            </a>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-20 bg-gradient-to-r from-red-500 to-red-600 text-white">
    <div class="container mx-auto px-6 text-center">
        <h2 class="text-4xl font-bold mb-6">¿Listo para comenzar tu viaje de aprendizaje?</h2>
        <p class="text-xl opacity-90 mb-8 max-w-2xl mx-auto">Únete a cientos de estudiantes que han alcanzado sus objetivos con Punjab Idiomas.</p>
        <a href="<?php echo url('contact'); ?>" class="px-8 py-4 bg-white text-red-500 font-bold rounded-lg hover:bg-gray-100 transition shadow-xl transform hover:-translate-y-1 inline-block">
            Inscribirse Ahora
        </a>
    </div>
</section>
