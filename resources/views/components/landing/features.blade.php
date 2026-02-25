<section id="courses" class="py-16 md:py-32 bg-gray-950">
    <div class="container mx-auto px-6 md:px-8">
        <div class="text-center mb-16 md:mb-20 max-w-3xl mx-auto">
            <h4 class="text-orange-500 font-bold uppercase tracking-widest mb-4 reveal text-xs md:text-sm">Excelencia Académica</h4>
            <h2 class="text-3xl md:text-6xl font-orbitron font-black text-white hover:text-orange-500 transition-colors duration-500 reveal">
                NUESTROS CURSOS
            </h2>
            <div class="w-20 md:w-24 h-1 md:h-1.5 bg-orange-500 mx-auto mt-6 md:mt-8 rounded-full reveal"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            @php
                $courses = [
                    ['level' => 'A1', 'title' => 'Principiante Absoluto', 'desc' => 'Inicia tu viaje desde cero con bases sólidas.', 'icon' => 'fa-seedling'],
                    ['level' => 'A2', 'title' => 'Básico Fundamental', 'desc' => 'Domina situaciones cotidianas con confianza.', 'icon' => 'fa-hiking'],
                    ['level' => 'B1', 'title' => 'Intermedio Independiente', 'desc' => 'Comunicación fluida en viajes y trabajo.', 'icon' => 'fa-mountain'],
                    ['level' => 'B2', 'title' => 'Intermedio Avanzado', 'desc' => 'Domina el idioma con fluidez técnica.', 'icon' => 'fa-trophy'],
                ];
            @endphp

            @foreach($courses as $course)
                <div class="group relative bg-[#1a1a24] p-10 rounded-3xl border border-white/5 hover:border-orange-500/50 transition-all duration-500 hover:-translate-y-4 reveal" style="transition-delay: {{ $loop->index * 100 }}ms">
                    <div class="absolute -top-6 -right-6 w-24 h-24 bg-orange-500/10 rounded-full blur-2xl group-hover:bg-orange-500/30 transition-all"></div>
                    
                    <div class="text-6xl font-orbitron font-black text-white/5 absolute top-4 right-8 group-hover:text-orange-500/20 transition-all">
                        {{ $course['level'] }}
                    </div>

                    <div class="w-16 h-16 bg-white/5 rounded-2xl flex items-center justify-center text-3xl text-orange-500 mb-8 group-hover:bg-orange-500 group-hover:text-white transition-all transform group-hover:rotate-[360deg] duration-700">
                        <i class="fas {{ $course['icon'] }}"></i>
                    </div>

                    <h3 class="text-2xl font-bold text-white mb-4 group-hover:text-orange-500 transition-colors">{{ $course['title'] }}</h3>
                    <p class="text-gray-400 leading-relaxed">{{ $course['desc'] }}</p>

                    <div class="mt-8 flex items-center gap-2 text-orange-500 font-bold tracking-widest text-sm opacity-0 group-hover:opacity-100 transition-all">
                        <span>DETALLES DEL CURSO</span>
                        <i class="fas fa-arrow-right animate-pulse"></i>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
