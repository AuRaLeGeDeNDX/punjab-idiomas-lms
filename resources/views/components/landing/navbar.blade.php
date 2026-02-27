<nav id="landing-navbar" class="fixed w-full z-50 transition-all duration-300 px-6 py-4 flex justify-between items-center bg-black/60 backdrop-blur-xl border-b border-white/10">
    <div class="flex items-center gap-3 cursor-pointer group" onclick="window.location.href='{{ url('/') }}'">
        <div class="relative overflow-hidden rounded-xl p-1 bg-gradient-to-br from-orange-400 to-orange-600 transition-transform group-hover:scale-110">
            <img src="{{ asset('images/logo.png') }}" class="h-16 w-16 object-cover rounded-lg" alt="Logo">
        </div>
        <span class="font-orbitron font-bold text-xl tracking-wider text-orange-500 group-hover:text-orange-400 transition-colors">
            PUNJAB <span class="text-white group-hover:text-orange-500">IDIOMAS</span>
        </span>
    </div>

    <!-- Desktop Navigation -->
    <div class="hidden lg:flex items-center gap-8">
        <ul class="flex gap-8 text-sm font-medium tracking-wide">
            <li><a href="#about" class="hover:text-orange-500 transition-colors uppercase">{{ __('nav.about') }}</a></li>
            <li><a href="#courses" class="hover:text-orange-500 transition-colors uppercase">{{ __('nav.courses') }}</a></li>
            <li><a href="#dele" class="hover:text-orange-500 transition-colors uppercase">{{ __('nav.dele') }}</a></li>
            <li><a href="#contact" class="hover:text-orange-500 transition-colors uppercase">{{ __('nav.contact') }}</a></li>
        </ul>

        <div class="h-6 w-px bg-white/10 mx-2"></div>

        <div class="flex items-center gap-4">
            <!-- Mode Toggle -->
            <button onclick="toggleMode()" class="p-2 rounded-full hover:bg-white/10 transition-colors text-orange-500" aria-label="Toggle Dark/Light Mode">
                <i id="mode-icon" class="fas fa-moon"></i>
            </button>

            <!-- Language Toggle Dropdown -->
            <div class="relative group">
                <button class="flex items-center gap-2 text-sm font-bold uppercase hover:text-orange-500 transition-colors py-2 px-3 bg-white/5 rounded-lg border border-white/10">
                    <i class="fas fa-globe text-orange-500"></i> {{ strtoupper(app()->getLocale()) }}
                </button>
                <div class="absolute top-full right-0 mt-2 w-32 bg-gray-900 border border-white/10 rounded-xl overflow-hidden opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 shadow-2xl z-50 text-center">
                    <a href="?lang=es" class="block px-4 py-2 hover:bg-orange-600 hover:text-white transition-colors text-xs font-bold @if(app()->getLocale() == 'es') text-orange-500 @endif">ESPAÑOL</a>
                    <a href="?lang=en" class="block px-4 py-2 hover:bg-orange-600 hover:text-white transition-colors text-xs font-bold @if(app()->getLocale() == 'en') text-orange-500 @endif">ENGLISH</a>
                    <a href="?lang=pa" class="block px-4 py-2 hover:bg-orange-600 hover:text-white transition-colors text-xs font-bold @if(app()->getLocale() == 'pa') text-orange-500 @endif">ਪੰਜਾਬੀ</a>
                    <a href="?lang=hi" class="block px-4 py-2 hover:bg-orange-600 hover:text-white transition-colors text-xs font-bold @if(app()->getLocale() == 'hi') text-orange-500 @endif">हिन्दी</a>
                    <a href="?lang=ur" class="block px-4 py-2 hover:bg-orange-600 hover:text-white transition-colors text-xs font-bold @if(app()->getLocale() == 'ur') text-orange-500 @endif">اردو</a>
                </div>
            </div>

            @if(auth()->check())
                <a href="{{ route('dashboard') }}" class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-full transition-all shadow-[0_0_20px_rgba(255,106,0,0.3)] hover:shadow-[0_0_30px_rgba(255,106,0,0.5)]">
                    {{ __('nav.dashboard') }}
                </a>
            @else
                <a href="{{ route('login') }}" class="text-sm font-bold uppercase hover:text-orange-500 transition-colors">{{ __('nav.login') }}</a>
                <a href="#contact" class="px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-full transition-all shadow-[0_0_20px_rgba(255,106,0,0.3)]">
                    {{ __('nav.register') }}
                </a>
            @endif
        </div>
    </div>

    <!-- Mobile Menu Button -->
    <button class="lg:hidden p-2 text-orange-500 text-2xl" onclick="toggleMobileMenu()" aria-label="Open Mobile Menu">
        <i id="menu-icon" class="fas fa-bars"></i>
    </button>

    <!-- Mobile Side Menu -->
    <div id="mobile-menu" class="fixed inset-0 bg-black/95 backdrop-blur-2xl z-[60] flex flex-col items-center justify-center gap-8 transition-all duration-500 translate-x-full lg:hidden">
        <button class="absolute top-6 right-6 text-3xl text-orange-500 p-4" onclick="toggleMobileMenu()" aria-label="Close Mobile Menu">
            <i class="fas fa-times"></i>
        </button>
        <a href="#about" onclick="toggleMobileMenu()" class="text-2xl font-bold uppercase hover:text-orange-500 py-2">{{ __('nav.about') }}</a>
        <a href="#courses" onclick="toggleMobileMenu()" class="text-2xl font-bold uppercase hover:text-orange-500 py-2">{{ __('nav.courses') }}</a>
        <a href="#dele" onclick="toggleMobileMenu()" class="text-2xl font-bold uppercase hover:text-orange-500 py-2">{{ __('nav.dele') }}</a>
        <a href="#contact" onclick="toggleMobileMenu()" class="text-2xl font-bold uppercase hover:text-orange-500 py-2">{{ __('nav.contact') }}</a>
        
        <div class="w-20 h-px bg-orange-500/30 my-4"></div>
        
        <!-- Mobile Language Toggle -->
        <div class="flex gap-4 mb-4">
            <a href="?lang=es" class="px-4 py-2 rounded-lg border border-white/10 text-xs font-bold @if(app()->getLocale() == 'es') bg-orange-500 text-white @else text-gray-400 @endif">ES</a>
            <a href="?lang=en" class="px-4 py-2 rounded-lg border border-white/10 text-xs font-bold @if(app()->getLocale() == 'en') bg-orange-500 text-white @else text-gray-400 @endif">EN</a>
            <a href="?lang=pa" class="px-4 py-2 rounded-lg border border-white/10 text-xs font-bold @if(app()->getLocale() == 'pa') bg-orange-500 text-white @else text-gray-400 @endif">PA</a>
            <a href="?lang=hi" class="px-4 py-2 rounded-lg border border-white/10 text-xs font-bold @if(app()->getLocale() == 'hi') bg-orange-500 text-white @else text-gray-400 @endif">HI</a>
            <a href="?lang=ur" class="px-4 py-2 rounded-lg border border-white/10 text-xs font-bold @if(app()->getLocale() == 'ur') bg-orange-500 text-white @else text-gray-400 @endif">UR</a>
        </div>

        @auth
            <a href="{{ route('dashboard') }}" class="px-12 py-4 bg-orange-500 text-white font-bold rounded-full text-xl shadow-xl transition-transform active:scale-95">{{ __('nav.dashboard') }}</a>
        @else
            <a href="{{ route('login') }}" class="text-xl font-bold uppercase py-2">{{ __('nav.login') }}</a>
            <a href="#contact" onclick="toggleMobileMenu()" class="px-12 py-4 bg-orange-500 text-white font-bold rounded-full text-xl shadow-[0_0_20px_rgba(255,106,0,0.3)] transition-transform active:scale-95">
                {{ __('nav.register') }}
            </a>
        @endauth
    </div>
</nav>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('translate-x-full');
    }

    // Shrink scroll effect
    window.addEventListener('scroll', () => {
        const nav = document.getElementById('landing-navbar');
        if (window.scrollY > 50) {
            nav.classList.add('py-2', 'bg-black/80');
            nav.classList.remove('py-4', 'bg-black/60');
        } else {
            nav.classList.remove('py-2', 'bg-black/80');
            nav.classList.add('py-4', 'bg-black/60');
        }
    });
</script>
