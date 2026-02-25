<footer class="py-12 md:py-20 bg-black border-t border-white/5 text-gray-500">
    <div class="container mx-auto px-6 md:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 md:gap-16 mb-16 md:mb-20 text-center md:text-left">
            <div class="col-span-1 md:col-span-2 flex flex-col items-center md:items-start">
                <div class="flex items-center gap-3 mb-6 md:mb-8">
                    <img src="{{ asset('logo.jpeg') }}" class="h-10 w-10 md:h-12 md:w-12 rounded-xl" alt="Logo">
                    <span class="font-orbitron font-bold text-xl md:text-2xl tracking-wider text-orange-500">PUNJAB <span class="text-white">IDIOMAS</span></span>
                </div>
                <p class="text-base md:text-lg max-w-md leading-relaxed hidden md:block">
                    Elevamos el estándar de la educación de idiomas en Barcelona a través de la pasión, el rigor académico y la innovación tecnológica.
                </p>
                <p class="text-sm md:hidden max-w-xs">
                    Excelencia académica en idiomas con innovación tecnológica en el corazón de Barcelona.
                </p>
            </div>

            <div>
                <h4 class="text-white font-bold mb-6 md:mb-8 uppercase tracking-widest text-xs md:text-sm">Navegación</h4>
                <ul class="space-y-4 text-xs font-bold uppercase transition-colors">
                    <li><a href="#about" class="hover:text-orange-500">Sobre Nosotros</a></li>
                    <li><a href="#courses" class="hover:text-orange-500">Cursos</a></li>
                    <li><a href="#dele" class="hover:text-orange-500">Exámenes DELE</a></li>
                    <li><a href="#faq" class="hover:text-orange-500">Preguntas Frecuentes</a></li>
                </ul>
            </div>

            <div class="flex flex-col items-center md:items-start">
                <h4 class="text-white font-bold mb-6 md:mb-8 uppercase tracking-widest text-xs md:text-sm">Idioma</h4>
                <div class="flex gap-4">
                    <a href="?lang=es" class="px-4 py-2 bg-orange-500/10 text-orange-500 border border-orange-500/20 rounded-lg text-xs font-bold uppercase">ES</a>
                    <a href="?lang=en" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-lg text-xs font-bold uppercase">EN</a>
                    <a href="?lang=pa" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-lg text-xs font-bold uppercase">PA</a>
                </div>
            </div>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-center pt-12 border-t border-white/5 gap-8">
            <p class="text-xs font-bold tracking-widest uppercase">&copy; {{ date('Y') }} Punjab Idiomas. Todos los derechos reservados.</p>
            <div class="flex gap-10 text-xs font-bold tracking-widest uppercase">
                <a href="#" class="hover:text-orange-500 transition-colors">Privacidad</a>
                <a href="#" class="hover:text-orange-500 transition-colors">Términos</a>
                <a href="#" class="hover:text-orange-500 transition-colors">Cookies</a>
            </div>
        </div>
    </div>
</footer>

<a href="https://wa.me/34612455057" class="fixed bottom-10 right-10 w-16 h-16 bg-green-500 text-white rounded-full flex items-center justify-center text-3xl shadow-2xl hover:scale-110 transition-transform z-[100] animate-pulse">
    <i class="fab fa-whatsapp"></i>
</a>
