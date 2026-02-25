<section class="py-24 bg-orange-600 relative overflow-hidden">
    <!-- Decorative background elements -->
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
    <div class="absolute -bottom-24 -right-24 w-96 h-96 bg-black/10 rounded-full blur-3xl"></div>

    <div class="container mx-auto px-6 md:px-8 relative z-10">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 md:gap-12 text-center text-white">
            <div class="stat-item reveal">
                <div class="text-4xl sm:text-5xl md:text-7xl font-orbitron font-black mb-2 flex items-center justify-center">
                    <span class="counter" data-target="10">0</span>
                    <span class="text-orange-200">+</span>
                </div>
                <p class="text-orange-100 uppercase tracking-widest font-bold text-[10px] sm:text-sm">Años de Experiencia</p>
            </div>

            <div class="stat-item reveal" style="transition-delay: 100ms">
                <div class="text-4xl sm:text-5xl md:text-7xl font-orbitron font-black mb-2 flex items-center justify-center">
                    <span class="counter" data-target="500">0</span>
                    <span class="text-orange-200">+</span>
                </div>
                <p class="text-orange-100 uppercase tracking-widest font-bold text-[10px] sm:text-sm">Estudiantes Exitosos</p>
            </div>

            <div class="stat-item reveal" style="transition-delay: 200ms">
                <div class="text-4xl sm:text-5xl md:text-7xl font-orbitron font-black mb-2 flex items-center justify-center">
                    <span class="counter" data-target="95">0</span>
                    <span class="text-orange-200">%</span>
                </div>
                <p class="text-orange-100 uppercase tracking-widest font-bold text-[10px] sm:text-sm">Tasa de Aprobado</p>
            </div>

            <div class="stat-item reveal" style="transition-delay: 300ms">
                <div class="text-4xl sm:text-5xl md:text-7xl font-orbitron font-black mb-2 flex items-center justify-center">
                    <span class="counter" data-target="24">0</span>
                    <span class="text-orange-200">/</span>
                    <span class="text-sm self-end pb-3 sm:pb-4 ml-1">7</span>
                </div>
                <p class="text-orange-100 uppercase tracking-widest font-bold text-[10px] sm:text-sm">Soporte LMS</p>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const counters = document.querySelectorAll('.counter');
        const speed = 200;

        const startCounters = () => {
            counters.forEach(counter => {
                const updateCount = () => {
                    const target = +counter.getAttribute('data-target');
                    const count = +counter.innerText;
                    const inc = target / speed;

                    if (count < target) {
                        counter.innerText = Math.ceil(count + inc);
                        setTimeout(updateCount, 1);
                    } else {
                        counter.innerText = target;
                    }
                };
                updateCount();
            });
        };

        // Scroll monitoring using GSAP ScrollTrigger
        ScrollTrigger.create({
            trigger: ".counters",
            onEnter: startCounters,
            once: true
        });
    });
</script>
