<section id="faq" class="py-16 md:py-32 bg-gray-900 relative">
    <div class="container mx-auto px-6 md:px-8 max-w-4xl">
        <div class="text-center mb-16 md:mb-20">
            <h4 class="text-orange-500 font-bold uppercase tracking-widest mb-4 reveal text-xs md:text-sm">Dudas Comunes</h4>
            <h2 class="text-3xl md:text-5xl font-orbitron font-black text-white reveal leading-tight">PREGUNTAS FRECUENTES</h2>
        </div>

        <div class="space-y-6">
            @php
                $faqs = [
                    ['q' => '¿Tienen clases para principiantes?', 'a' => 'Sí, nuestro nivel A1 está diseñado específicamente para quienes parten de cero.'],
                    ['q' => '¿Incluyen material de estudio?', 'a' => 'Por supuesto. Proporcionamos acceso completo a nuestra plataforma LMS y materiales físicos.'],
                    ['q' => '¿Cuál es la duración de los cursos?', 'a' => 'Varía según el nivel, pero generalmente los niveles A1-A2 duran de 3 a 4 meses.'],
                    ['q' => '¿Puedo tomar clases online?', 'a' => 'Sí, ofrecemos una modalidad híbrida con lo mejor de lo presencial y lo digital.'],
                ];
            @endphp

            @foreach($faqs as $item)
                <details class="group bg-gray-800/50 rounded-3xl p-8 border border-white/5 cursor-pointer hover:border-orange-500/30 transition-all reveal">
                    <summary class="flex items-center justify-between font-bold text-xl text-white list-none">
                        <span>{{ $item['q'] }}</span>
                        <span class="w-10 h-10 bg-orange-500/10 rounded-full flex items-center justify-center text-orange-500 group-open:rotate-180 transition-transform">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                    </summary>
                    <div class="mt-6 text-gray-400 leading-relaxed overflow-hidden">
                        <p>{{ $item['a'] }}</p>
                    </div>
                </details>
            @endforeach
        </div>
    </div>
</section>
