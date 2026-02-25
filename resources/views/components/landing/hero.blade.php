<section id="hero" class="relative min-h-screen flex items-center justify-center overflow-hidden">
    <!-- Background Video -->
    <video autoplay muted loop playsinline poster="https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=1920&q=20" class="absolute top-0 left-0 w-full h-full object-cover -z-20">
        <source src="https://cdn.coverr.co/videos/coverr-students-in-classroom-1574/1080p.mp4" type="video/mp4">
    </video>
    
    <!-- Gradient Overlay -->
    <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-t from-gray-950 via-gray-900/40 to-gray-950/80 -z-10"></div>

    <div class="container mx-auto px-6 md:px-8 text-center relative z-10 pt-24 pb-12">
        <h1 id="hero-title" class="font-orbitron text-3xl sm:text-4xl md:text-8xl font-black mb-6 md:mb-8 leading-tight tracking-tighter opacity-0">
            <span class="block text-white">{{ __('hero.title1') }}</span>
            <span class="block bg-gradient-to-r from-orange-400 via-orange-600 to-orange-400 bg-clip-text text-transparent italic drop-shadow-[0_0_20px_rgba(255,106,0,0.4)] animate-gradient-x">
                {{ __('hero.title2') }}
            </span>
        </h1>

        <p id="hero-description" class="text-gray-300 text-base md:text-xl max-w-3xl mx-auto mb-10 md:mb-12 font-light tracking-wide leading-relaxed opacity-0">
            {{ __('hero.description') }}
        </p>

        <div id="hero-cta" class="flex flex-col sm:flex-row items-center justify-center gap-4 md:gap-6 opacity-0">
            <a href="/register" class="w-full sm:w-auto px-8 md:px-10 py-4 md:py-5 bg-orange-500 text-white font-bold rounded-full overflow-hidden shadow-[0_0_30px_rgba(255,106,0,0.5)] transition-transform active:scale-95">
                <span class="relative z-10">{{ __('hero.cta_primary') }}</span>
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/20 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-500"></div>
            </a>
            
            <a href="#about" class="w-full sm:w-auto px-8 md:px-10 py-4 md:py-5 bg-white/5 border border-white/20 text-white font-bold rounded-full backdrop-blur-md hover:bg-white/10 transition-all active:scale-95">
                {{ __('hero.cta_secondary') }}
            </a>
        </div>
    </div>

    <!-- Scroll Down Indicator -->
    <div class="absolute bottom-12 left-1/2 -translate-x-1/2 animate-bounce">
        <a href="#about" class="text-orange-500/50 hover:text-orange-500 text-4xl transition-colors">
            <i class="fas fa-angle-double-down"></i>
        </a>
    </div>
</section>

<style>
    @@keyframes gradient-x {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }
    .animate-gradient-x {
        background-size: 200% 200%;
        animation: gradient-x 15s ease infinite;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Anime.js Title Animation
        const title = document.getElementById('hero-title');
        title.innerHTML = title.textContent.replace(/\S/g, "<span class='letter inline-block'>$&</span>");

        gsap.to('#hero-title', { opacity: 1, duration: 0.1 });
        
        anime.timeline({loop: false})
          .add({
            targets: '#hero-title .letter',
            scale: [4,1],
            opacity: [0,1],
            translateZ: 0,
            easing: "easeOutExpo",
            duration: 950,
            delay: (el, i) => 70 * i
          }).add({
            targets: '#hero-description',
            opacity: [0,1],
            translateY: [20,0],
            duration: 800,
            easing: "easeOutQuad"
          }, '-=400').add({
            targets: '#hero-cta',
            opacity: [0,1],
            scale: [0.9,1],
            duration: 800,
            easing: "easeOutBack"
          }, '-=600');
    });
</script>
