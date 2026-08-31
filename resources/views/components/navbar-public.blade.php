<!-- Public Navbar (Unauthenticated Users) -->
<nav x-data="{ open: false, scrolled: false }"
     x-init="scrolled = window.scrollY > 12; window.addEventListener('scroll', () => { scrolled = window.scrollY > 12 })"
     :class="scrolled ? 'bg-white/90 shadow-[0_18px_55px_rgba(15,23,42,0.10)] border-slate-200/80' : 'bg-white/95 shadow-[0_8px_30px_rgba(15,23,42,0.04)] border-slate-100'"
     class="sticky top-0 z-50 w-full border-b backdrop-blur-xl transition-all duration-300">
    <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-6 xl:px-10 2xl:px-16">
        <div class="flex min-h-[68px] sm:min-h-[76px] lg:min-h-[78px] xl:min-h-[84px] 2xl:min-h-[90px] items-center justify-between gap-3 lg:gap-3 xl:gap-6 2xl:gap-8">
            <!-- Logo -->
            <div class="flex shrink-0 items-center">
                <a href="{{ route('home') }}" class="group flex items-center gap-2" aria-label="Cvbliss home">
                    <img src="{{ asset('Logo.webp') }}"
                         alt="Cvbliss Logo"
                         class="cvb-logo cvb-logo-nav w-auto transition duration-300 group-hover:scale-[1.03]"
                         fetchpriority="high"
                         decoding="async"
                         width="150"
                         height="40">
                </a>
            </div>

            <!-- Desktop Navigation Links -->
            <div class="hidden min-w-0 flex-1 items-center justify-center gap-1 lg:flex xl:gap-1.5 2xl:gap-3 lg:mr-2">
                <a href="{{ route('resume-maker') }}"
                   class="group relative whitespace-nowrap rounded-full px-2.5 py-2.5 text-[14px] font-semibold leading-none tracking-[0.01em] transition duration-200 xl:px-5 xl:py-3 xl:text-[15px] 2xl:px-5 2xl:py-3 2xl:text-base {{ request()->routeIs('resume-maker') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }}">
                    Resume Maker
                    <span class="absolute inset-x-4 -bottom-0.5 h-0.5 origin-center rounded-full bg-blue-600 transition-transform duration-200 {{ request()->routeIs('resume-maker') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
                </a>
                <a href="{{ route('enhance-cv') }}"
                   class="group relative whitespace-nowrap rounded-full px-2.5 py-2.5 text-[14px] font-semibold leading-none tracking-[0.01em] transition duration-200 xl:px-5 xl:py-3 xl:text-[15px] 2xl:px-5 2xl:py-3 2xl:text-base {{ request()->routeIs('enhance-cv') || request()->routeIs('improve-cv') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }}">
                    Enhance CV
                    <span class="absolute inset-x-4 -bottom-0.5 h-0.5 origin-center rounded-full bg-blue-600 transition-transform duration-200 {{ request()->routeIs('enhance-cv') || request()->routeIs('improve-cv') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
                </a>
                <a href="{{ route('templates') }}"
                   class="group relative whitespace-nowrap rounded-full px-2.5 py-2.5 text-[14px] font-semibold leading-none tracking-[0.01em] transition duration-200 xl:px-5 xl:py-3 xl:text-[15px] 2xl:px-5 2xl:py-3 2xl:text-base {{ request()->routeIs('templates') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }}">
                    Templates
                    <span class="absolute inset-x-4 -bottom-0.5 h-0.5 origin-center rounded-full bg-blue-600 transition-transform duration-200 {{ request()->routeIs('templates') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
                </a>
                <a href="{{ route('cover-letter') }}"
                   class="group relative whitespace-nowrap rounded-full px-2.5 py-2.5 text-[14px] font-semibold leading-none tracking-[0.01em] transition duration-200 xl:px-5 xl:py-3 xl:text-[15px] 2xl:px-5 2xl:py-3 2xl:text-base {{ request()->routeIs('cover-letter') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }}">
                    Cover Letter
                    <span class="absolute inset-x-4 -bottom-0.5 h-0.5 origin-center rounded-full bg-blue-600 transition-transform duration-200 {{ request()->routeIs('cover-letter') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
                </a>
                <a href="{{ route('plans') }}"
                   class="group relative whitespace-nowrap rounded-full px-2.5 py-2.5 text-[14px] font-semibold leading-none tracking-[0.01em] transition duration-200 xl:px-5 xl:py-3 xl:text-[15px] 2xl:px-5 2xl:py-3 2xl:text-base {{ request()->routeIs('plans') || request()->routeIs('plans.*') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }}">
                    Pricing
                    <span class="absolute inset-x-4 -bottom-0.5 h-0.5 origin-center rounded-full bg-blue-600 transition-transform duration-200 {{ request()->routeIs('plans') || request()->routeIs('plans.*') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
                </a>
                <a href="{{ route('interview') }}"
                   class="group relative whitespace-nowrap rounded-full px-2.5 py-2.5 text-[14px] font-semibold leading-none tracking-[0.01em] transition duration-200 xl:px-5 xl:py-3 xl:text-[15px] 2xl:px-5 2xl:py-3 2xl:text-base {{ request()->routeIs('interview') || request()->routeIs('blog.show') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }}">
                    Interview Tips
                    <span class="absolute inset-x-4 -bottom-0.5 h-0.5 origin-center rounded-full bg-blue-600 transition-transform duration-200 {{ request()->routeIs('interview') || request()->routeIs('blog.show') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
                </a>
                <a href="{{ route('contact') }}"
                   class="group relative whitespace-nowrap rounded-full px-2.5 py-2.5 text-[14px] font-semibold leading-none tracking-[0.01em] transition duration-200 xl:px-5 xl:py-3 xl:text-[15px] 2xl:px-5 2xl:py-3 2xl:text-base {{ request()->routeIs('contact') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }}">
                    Contact Us
                    <span class="absolute inset-x-4 -bottom-0.5 h-0.5 origin-center rounded-full bg-blue-600 transition-transform duration-200 {{ request()->routeIs('contact') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
                </a>
            </div>

            <!-- Auth Button -->
            <div class="hidden shrink-0 items-center lg:flex">
                <a href="{{ route('login') }}"
                   class="inline-flex h-11 lg:h-11 xl:h-[52px] 2xl:h-14 items-center justify-center gap-2 rounded-[14px] bg-gradient-to-r from-blue-600 to-indigo-600 px-5 lg:px-5 xl:px-7 2xl:px-8 text-[15px] lg:text-[15px] xl:text-lg 2xl:text-lg font-bold text-white shadow-[0_14px_30px_rgba(37,99,235,0.24)] transition duration-200 hover:-translate-y-0.5 hover:shadow-[0_18px_38px_rgba(37,99,235,0.32)] focus:outline-none active:translate-y-0">
                    <svg class="h-5 w-5 xl:h-6 xl:w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                    Login
                </a>
            </div>

            <!-- Mobile Menu Button -->
            <button type="button"
                    @click="open = !open"
                    class="inline-flex h-11 w-11 sm:h-12 sm:w-12 shrink-0 items-center justify-center rounded-[14px] border border-slate-200 bg-white text-slate-700 shadow-sm transition duration-200 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 lg:hidden"
                    :aria-expanded="open.toString()"
                    aria-label="Open navigation">
                <svg x-show="!open" class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
                <svg x-show="open" x-cloak class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div x-show="open"
             x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-3"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-3"
             class="lg:hidden">
            <div class="mb-4 max-h-[calc(100vh-90px)] overflow-y-auto overscroll-contain rounded-[22px] border border-slate-200 bg-white p-2 shadow-[0_18px_45px_rgba(15,23,42,0.10)]">
                <a href="{{ route('resume-maker') }}" class="flex items-center gap-3 rounded-2xl px-4 py-4 text-lg font-semibold {{ request()->routeIs('resume-maker') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }} transition duration-200">
                    <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Resume Maker
                </a>
                <a href="{{ route('enhance-cv') }}" class="flex items-center gap-3 rounded-2xl px-4 py-4 text-lg font-semibold {{ request()->routeIs('enhance-cv') || request()->routeIs('improve-cv') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }} transition duration-200">
                    <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Enhance CV
                </a>
                <a href="{{ route('templates') }}" class="flex items-center gap-3 rounded-2xl px-4 py-4 text-lg font-semibold {{ request()->routeIs('templates') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }} transition duration-200">
                    <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Templates
                </a>
                <a href="{{ route('cover-letter') }}" class="flex items-center gap-3 rounded-2xl px-4 py-4 text-lg font-semibold {{ request()->routeIs('cover-letter') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }} transition duration-200">
                    <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Cover Letter
                </a>
                <a href="{{ route('plans') }}" class="flex items-center gap-3 rounded-2xl px-4 py-4 text-lg font-semibold {{ request()->routeIs('plans') || request()->routeIs('plans.*') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }} transition duration-200">
                    <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                    Pricing
                </a>
                <a href="{{ route('interview') }}" class="flex items-center gap-3 rounded-2xl px-4 py-4 text-lg font-semibold {{ request()->routeIs('interview') || request()->routeIs('blog.show') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }} transition duration-200">
                    <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                    </svg>
                    Interview Tips
                </a>
                <a href="{{ route('contact') }}" class="flex items-center gap-3 rounded-2xl px-4 py-4 text-lg font-semibold {{ request()->routeIs('contact') ? 'bg-blue-50 text-blue-700' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }} transition duration-200">
                    <svg class="h-6 w-6 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Contact Us
                </a>
                <div class="mt-2 border-t border-slate-100 pt-2">
                    <a href="{{ route('login') }}"
                       class="flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-blue-600 to-indigo-600 px-4 py-4 text-lg font-bold text-white shadow-[0_14px_30px_rgba(37,99,235,0.22)] transition duration-200 hover:-translate-y-0.5">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                        </svg>
                        Get Started
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
    [x-cloak] { display: none !important; }
</style>
