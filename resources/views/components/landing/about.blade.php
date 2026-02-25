<section id="about" class="py-16 md:py-32 bg-white text-gray-900 section-about">
    <div class="container mx-auto px-6 md:px-8">
        <div class="flex flex-col lg:flex-row items-center gap-12 md:gap-20">
            <div class="w-full lg:w-1/2 relative reveal mb-10 lg:mb-0" data-origin="left">
                <div class="absolute -top-6 -left-6 md:-top-10 md:-left-10 w-full h-full border-4 border-orange-500 rounded-3xl -z-10 translate-x-2 translate-y-2 md:translate-x-4 md:translate-y-4"></div>
                <img src="https://images.unsplash.com/photo-1524178232363-1fb2b075b655?auto=format&fit=crop&w=1350&q=80" alt="Students in group discussion" loading="lazy" class="rounded-3xl shadow-2xl transition-transform hover:scale-[1.02] duration-500 w-full">
                
                <div class="absolute -bottom-6 -right-6 md:-bottom-10 md:-right-10 bg-orange-600 text-white p-6 md:p-10 rounded-3xl shadow-2xl hidden sm:block animate-bounce-slow">
                    <p class="text-2xl md:text-4xl font-black font-orbitron mb-1">A1 - B2</p>
                    <p class="text-[10px] md:text-sm font-bold tracking-widest uppercase opacity-80">Ruta Certificada</p>
                </div>
            </div>

            <div class="w-full lg:w-1/2 reveal text-center lg:text-left" data-origin="right">
                <h4 class="text-orange-500 font-bold uppercase tracking-widest mb-4 text-xs md:text-sm">Nuestra Historia</h4>
                <h2 class="text-3xl md:text-6xl font-orbitron font-black mb-6 md:mb-8 leading-tight">
                    APRENDIZAJE <span class="text-orange-600">SIN FRONTERAS</span>
                </h2>
                
                <p class="text-gray-600 text-base md:text-lg mb-8 leading-relaxed">
                    Punjab Idiomas es más que una escuela; es tu puerta de entrada a la cultura española. 
                    Nuestra metodología estructurada está diseñada específicamente para aquellos que buscan 
                    resultados tangibles en los exámenes oficiales DELE.
                </p>

                <div class="space-y-6 mb-10 md:mb-12 text-left">
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-orange-100 rounded-xl flex items-center justify-center text-orange-600 shrink-0">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <div>
                            <h3 class="text-lg md:text-xl font-bold mb-1">Misión de Excelencia</h3>
                            <p class="text-gray-500 text-sm md:text-base">Capacitar a cada estudiante con las herramientas lingüísticas necesarias para brillar.</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start gap-4">
                        <div class="w-10 h-10 md:w-12 md:h-12 bg-orange-100 rounded-xl flex items-center justify-center text-orange-600 shrink-0">
                            <i class="fas fa-star"></i>
                        </div>
                        <div>
                            <h3 class="text-lg md:text-xl font-bold mb-1">Visión Innovadora</h3>
                            <p class="text-gray-500 text-sm md:text-base">Liderar la enseñanza de idiomas mediante la integración de tecnología.</p>
                        </div>
                    </div>
                </div>

                <a href="#contact" class="inline-flex items-center justify-center gap-4 w-full sm:w-auto px-8 md:px-10 py-4 md:py-5 bg-gray-950 text-white font-bold rounded-full hover:bg-orange-600 transition-all group active:scale-95">
                    <span>CONÓCENOS MÁS</span>
                    <i class="fas fa-chevron-right group-hover:translate-x-2 transition-transform"></i>
                </a>
            </div>
        </div>
    </div>
</section>

<style>
    @@keyframes bounce-slow {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
    .animate-bounce-slow {
        animation: bounce-slow 4s ease-in-out infinite;
    }
</style>
