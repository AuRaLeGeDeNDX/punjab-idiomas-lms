<section class="py-16 md:py-32 bg-gray-950 overflow-hidden relative">
    <div class="absolute top-0 left-0 w-full h-full opacity-10 pointer-events-none">
        <div class="absolute top-1/2 left-1/4 w-[300px] md:w-[500px] h-[300px] md:h-[500px] bg-orange-500 rounded-full blur-[80px] md:blur-[120px]"></div>
    </div>

    <div class="container mx-auto px-6 md:px-8 relative z-10">
        <div class="text-center mb-16 md:mb-20">
            <h4 class="text-orange-500 font-bold uppercase tracking-widest mb-4 reveal text-xs md:text-sm">Historias de Éxito</h4>
            <h2 class="text-3xl md:text-5xl font-orbitron font-black text-white reveal leading-tight">LO QUE DICEN NUESTROS ALUMNOS</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            @php
                $testimonials = [
                    ['name' => 'John Doe', 'role' => 'Estudiante A2', 'text' => '"La mejor decisión para aprender español en Barcelona. El soporte LMS es increíble."', 'img' => 'https://i.pravatar.cc/150?img=32'],
                    ['name' => 'Sarah Smith', 'role' => 'Estudiante B2', 'text' => '"Pasé mi examen DELE B2 a la primera gracias a los simulacros de Punjab Idiomas."', 'img' => 'https://i.pravatar.cc/150?img=44'],
                    ['name' => 'Carlos Ruíz', 'role' => 'Ex-alumno', 'text' => '"Profesores pacientes y una comunidad acogedora. ¡Altamente recomendado!"', 'img' => 'https://i.pravatar.cc/150?img=12'],
                ];
            @endphp

            @foreach($testimonials as $t)
                <div class="bg-white/5 p-12 rounded-[40px] border border-white/10 hover:bg-white/10 transition-all duration-500 reveal group" style="transition-delay: {{ $loop->index * 200 }}ms">
                    <div class="text-orange-500 text-5xl mb-8 group-hover:scale-110 transition-transform"><i class="fas fa-quote-left"></i></div>
                    <p class="text-white text-lg italic leading-relaxed mb-10">{{ $t['text'] }}</p>
                    <div class="flex items-center gap-4">
                        <img src="{{ $t['img'] }}" class="w-14 h-14 rounded-full border-2 border-orange-500 p-1" alt="Student testimonial: {{ $t['name'] }}" loading="lazy">
                        <div>
                            <h4 class="font-bold text-white">{{ $t['name'] }}</h4>
                            <p class="text-orange-500 text-xs font-bold uppercase tracking-widest">{{ $t['role'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
