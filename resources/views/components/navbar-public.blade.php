<!-- Public Navbar (Unauthenticated Users) - Enhanced Design -->
<nav x-data="{ open: false, scrolled: false }" 
     x-init="window.addEventListener('scroll', () => { scrolled = window.scrollY > 20 })"
     :class="scrolled ? 'bg-white/95 backdrop-blur-md shadow-lg py-2' : 'bg-white shadow-md py-2'"
     class=" top-0 z-50 transition-all duration-300 w-full">
    <div class="max-w-full mx-auto px-4 sm:px-10 lg:px-16">
        <div class="flex justify-between items-center h-16">
            <!-- Logo with gradient effect -->
            <div class="flex-shrink-0 group">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                      <!-- Logo Image with Animation -->
                    <img src="{{ asset('Logo.png') }}" 
                         alt="Cvbliss Logo"
                         class="h-20 w-50 lg:h-15 transition-all duration-500 group-hover:scale-110 group-hover:rotate-3">
            </div>

            <!-- Desktop Navigation Links -->
            <div class="hidden lg:flex items-center space-x-1">
                <a href="{{ route('resume-maker') }}" 
                   class="relative px-4 py-2 text-gray-600 hover:text-blue-600 transition-all duration-300 font-medium group">
                    Resume Maker
                    <span class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-blue-600 to-indigo-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
                </a>
                <a href="{{ route('enhance-cv') }}" 
                   class="relative px-4 py-2 text-gray-600 hover:text-blue-600 transition-all duration-300 font-medium group">
                    Enhance CV
                    <span class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-blue-600 to-indigo-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
                </a>
                <a href="{{ route('templates') }}" 
                   class="relative px-4 py-2 text-gray-600 hover:text-blue-600 transition-all duration-300 font-medium group">
                    Templates
                    <span class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-blue-600 to-indigo-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
                </a>
                <a href="{{ route('cover-letter') }}" 
                   class="relative px-4 py-2 text-gray-600 hover:text-blue-600 transition-all duration-300 font-medium group">
                    Cover Letter
                    <span class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-blue-600 to-indigo-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
                </a>
                <a href="{{ route('interview') }}" 
                   class="relative px-4 py-2 text-gray-600 hover:text-blue-600 transition-all duration-300 font-medium group">
                    Interview Tips
                    <span class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-blue-600 to-indigo-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
                </a>
                <a href="{{ route('contact') }}" 
                   class="relative px-4 py-2 text-gray-600 hover:text-blue-600 transition-all duration-300 font-medium group">
                    Contact Us
                    <span class="absolute inset-x-0 bottom-0 h-0.5 bg-gradient-to-r from-blue-600 to-indigo-600 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-300"></span>
                </a>
            </div>

            <!-- Auth Buttons - Improved Login Button -->
            <div class="hidden lg:flex items-center space-x-3">
                <a href="{{ route('login') }}" 
                   class="group relative px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold text-sm transition-all duration-300 hover:shadow-lg hover:shadow-blue-500/25 hover:scale-105 active:scale-95 overflow-hidden">
                    <span class="relative z-10 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Login
                    </span>
                    <div class="absolute inset-0 -translate-x-full group-hover:translate-x-0 transition-transform duration-300 bg-gradient-to-r from-blue-500 to-indigo-500"></div>
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <button type="button" @click="open = !open" 
                    class="lg:hidden relative w-10 h-10 rounded-xl bg-gray-50 hover:bg-gray-100 transition-all duration-300 flex items-center justify-center"
                    aria-label="Open navigation">
                <svg x-show="!open" class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                <svg x-show="open" x-cloak class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" class="lg:hidden py-4 space-y-2 border-t border-gray-100 mt-2">
            <a href="{{ route('resume-maker') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Resume Maker
            </a>
            <a href="{{ route('enhance-cv') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Enhance CV
            </a>
            <a href="{{ route('templates') }}" class="flex items-center gap-3 px-4 py-3 {{ request()->routeIs('templates') ? 'text-blue-600 bg-blue-50 font-semibold' : 'text-gray-700 hover:text-blue-600 hover:bg-blue-50' }} rounded-xl transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                Templates
            </a>
            <a href="{{ route('cover-letter') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Cover Letter
            </a>
            <a href="{{ route('interview') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                </svg>
                Interview Tips
            </a>
            <a href="{{ route('contact') }}" class="flex items-center gap-3 px-4 py-3 text-gray-700 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Contact Us
            </a>
            <div class="pt-3 mt-3 border-t border-gray-100">
                <a href="{{ route('login') }}" 
                   class="flex items-center justify-center gap-2 w-full px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-xl font-semibold transition-all duration-300 hover:shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Get Started
                </a>
            </div>
        </div>
    </div>
</nav>

<style>
    [x-cloak] { display: none !important; }
</style>
