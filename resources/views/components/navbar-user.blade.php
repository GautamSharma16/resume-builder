<!-- User Navbar (Authenticated Regular Users) -->
<div x-data="{ open: false, scrolled: false }">
<nav x-init="scrolled = window.scrollY > 12; window.addEventListener('scroll', () => { scrolled = window.scrollY > 12 })"
         :class="scrolled ? 'bg-white/90 shadow-[0_18px_55px_rgba(15,23,42,0.10)] border-slate-200/80' : 'bg-white/95 shadow-[0_8px_30px_rgba(15,23,42,0.04)] border-slate-100'"
         class="sticky top-0 z-50 w-full border-b backdrop-blur-xl transition-all duration-300">
    <div class="mx-auto max-w-[1440px] px-4 sm:px-6 lg:px-10 xl:px-16">
        <div class="flex min-h-[82px] items-center justify-between gap-4 lg:gap-8">
            <!-- Logo -->
            <div class="flex shrink-0 items-center">
                <a href="{{ route('home') }}" class="group flex items-center gap-2" aria-label="Cvbliss home">
                    <img src="{{ asset('Logo.png') }}"
                         alt="Cvbliss Logo"
                         class="cvb-logo cvb-logo-nav transition duration-300 group-hover:scale-[1.03]">
                </a>
            </div>

            <!-- Navigation Links -->
            <div class="hidden min-w-0 flex-1 items-center justify-center gap-1 xl:flex 2xl:gap-2">
                <a href="{{ route('resume-maker') }}" class="group relative whitespace-nowrap rounded-full px-3.5 py-2.5 text-[15px] font-semibold leading-none tracking-[0.01em] transition duration-200 2xl:px-4 2xl:text-base {{ request()->routeIs('resume-maker') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }}">
                    Resume Maker
                    <span class="absolute inset-x-4 -bottom-0.5 h-0.5 origin-center rounded-full bg-blue-600 transition-transform duration-200 {{ request()->routeIs('resume-maker') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
                </a>
                <a href="{{ route('enhance-cv') }}" class="group relative whitespace-nowrap rounded-full px-3.5 py-2.5 text-[15px] font-semibold leading-none tracking-[0.01em] transition duration-200 2xl:px-4 2xl:text-base {{ request()->routeIs('enhance-cv') || request()->routeIs('improve-cv') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }}">
                    Enhance CV
                    <span class="absolute inset-x-4 -bottom-0.5 h-0.5 origin-center rounded-full bg-blue-600 transition-transform duration-200 {{ request()->routeIs('enhance-cv') || request()->routeIs('improve-cv') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
                </a>
                <a href="{{ route('templates') }}" class="group relative whitespace-nowrap rounded-full px-3.5 py-2.5 text-[15px] font-semibold leading-none tracking-[0.01em] transition duration-200 2xl:px-4 2xl:text-base {{ request()->routeIs('templates') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }}">
                    Templates
                    <span class="absolute inset-x-4 -bottom-0.5 h-0.5 origin-center rounded-full bg-blue-600 transition-transform duration-200 {{ request()->routeIs('templates') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
                </a>
                <a href="{{ route('cover-letter') }}" class="group relative whitespace-nowrap rounded-full px-3.5 py-2.5 text-[15px] font-semibold leading-none tracking-[0.01em] transition duration-200 2xl:px-4 2xl:text-base {{ request()->routeIs('cover-letter') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }}">
                    Cover Letter
                    <span class="absolute inset-x-4 -bottom-0.5 h-0.5 origin-center rounded-full bg-blue-600 transition-transform duration-200 {{ request()->routeIs('cover-letter') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
                </a>
                <a href="{{ route('interview') }}" class="group relative whitespace-nowrap rounded-full px-3.5 py-2.5 text-[15px] font-semibold leading-none tracking-[0.01em] transition duration-200 2xl:px-4 2xl:text-base {{ request()->routeIs('interview') || request()->routeIs('blog.show') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }}">
                    Interview Tips
                    <span class="absolute inset-x-4 -bottom-0.5 h-0.5 origin-center rounded-full bg-blue-600 transition-transform duration-200 {{ request()->routeIs('interview') || request()->routeIs('blog.show') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
                </a>
                <a href="{{ route('contact') }}" class="group relative whitespace-nowrap rounded-full px-3.5 py-2.5 text-[15px] font-semibold leading-none tracking-[0.01em] transition duration-200 2xl:px-4 2xl:text-base {{ request()->routeIs('contact') ? 'bg-blue-50 text-blue-700 shadow-sm' : 'text-slate-700 hover:bg-slate-50 hover:text-blue-700' }}">
                    Contact Us
                    <span class="absolute inset-x-4 -bottom-0.5 h-0.5 origin-center rounded-full bg-blue-600 transition-transform duration-200 {{ request()->routeIs('contact') ? 'scale-x-100' : 'scale-x-0 group-hover:scale-x-100' }}"></span>
                </a>
            </div>

            <!-- User Menu -->
            <div class="hidden shrink-0 items-center xl:flex">
                <x-dropdown align="right" width="56" contentClasses="overflow-hidden rounded-2xl border border-slate-200 bg-white py-2 shadow-[0_22px_50px_rgba(15,23,42,0.16)]">
                    <x-slot name="trigger">
                        <button class="inline-flex max-w-[240px] items-center gap-3 rounded-[16px] border border-slate-200 bg-white px-3.5 py-2.5 text-slate-700 shadow-sm transition duration-200 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-sm font-bold text-white shadow-sm">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </span>
                            <span class="min-w-0 truncate text-base font-semibold">{{ Auth::user()->name }}</span>
                            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-3 border-b border-slate-100">
                            <p class="truncate text-sm font-bold text-slate-900">{{ Auth::user()->name }}</p>
                            <p class="truncate text-xs font-medium text-slate-500">{{ Auth::user()->email }}</p>
                        </div>

                        <x-dropdown-link :href="route('dashboard')">
                            Dashboard
                        </x-dropdown-link>

                        <x-dropdown-link :href="route('profile.edit')">
                            Profile
                        </x-dropdown-link>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-600 hover:bg-red-50 focus:bg-red-50">
                                Logout
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <button type="button"
                    @click="open = !open"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[14px] border border-slate-200 bg-white text-slate-700 shadow-sm transition duration-200 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 xl:hidden"
                    :aria-expanded="open.toString()"
                    aria-label="Open navigation">
                <svg x-show="!open" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/>
                </svg>
                <svg x-show="open" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

    </div>
</nav>

<div x-show="open"
     x-cloak
     x-transition.opacity.duration.300ms
     class="fixed inset-0 z-[60] bg-slate-900/55 backdrop-blur-sm xl:hidden"
     @click="open = false"></div>

<aside x-show="open"
       x-cloak
       x-transition:enter="transition ease-in-out duration-300 transform"
       x-transition:enter-start="-translate-x-full"
       x-transition:enter-end="translate-x-0"
       x-transition:leave="transition ease-in-out duration-300 transform"
       x-transition:leave-start="translate-x-0"
       x-transition:leave-end="-translate-x-full"
       class="fixed inset-y-0 left-0 z-[70] flex w-[min(20rem,calc(100vw-2rem))] flex-col border-r border-slate-200 bg-white shadow-[24px_0_60px_rgba(15,23,42,0.18)] xl:hidden">
    <div class="flex h-20 shrink-0 items-center justify-between border-b border-slate-100 px-5">
        <a href="{{ route('home') }}" class="flex min-w-0 items-center" aria-label="Cvbliss home">
            <img src="{{ asset('Logo.png') }}" alt="Cvbliss Logo" class="cvb-logo cvb-logo-drawer">
        </a>
        <button type="button"
                @click="open = false"
                class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[14px] text-slate-400 transition duration-200 hover:bg-slate-50 hover:text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
                aria-label="Close navigation">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    <nav class="admin-sidebar-scroll flex-1 space-y-1 overflow-y-auto px-4 py-6">
        <a href="{{ route('resume-maker') }}" class="group flex min-h-[48px] items-center rounded-xl px-4 py-3 text-base font-medium transition-all duration-200 {{ request()->routeIs('resume-maker') ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600' }}">Resume Maker</a>
        <a href="{{ route('enhance-cv') }}" class="group flex min-h-[48px] items-center rounded-xl px-4 py-3 text-base font-medium transition-all duration-200 {{ request()->routeIs('enhance-cv') || request()->routeIs('improve-cv') ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600' }}">Enhance CV</a>
        <a href="{{ route('templates') }}" class="group flex min-h-[48px] items-center rounded-xl px-4 py-3 text-base font-medium transition-all duration-200 {{ request()->routeIs('templates') ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600' }}">Templates</a>
        <a href="{{ route('cover-letter') }}" class="group flex min-h-[48px] items-center rounded-xl px-4 py-3 text-base font-medium transition-all duration-200 {{ request()->routeIs('cover-letter') ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600' }}">Cover Letter</a>
        <a href="{{ route('interview') }}" class="group flex min-h-[48px] items-center rounded-xl px-4 py-3 text-base font-medium transition-all duration-200 {{ request()->routeIs('interview') || request()->routeIs('blog.show') ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600' }}">Interview Tips</a>
        <a href="{{ route('contact') }}" class="group flex min-h-[48px] items-center rounded-xl px-4 py-3 text-base font-medium transition-all duration-200 {{ request()->routeIs('contact') ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600' }}">Contact Us</a>

        <div class="pt-6 pb-2">
            <p class="px-4 text-[10px] font-bold uppercase tracking-widest text-slate-400">Account</p>
        </div>
        <a href="{{ route('dashboard') }}" class="group flex min-h-[48px] items-center rounded-xl px-4 py-3 text-base font-medium transition-all duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600' }}">Dashboard</a>
        <a href="{{ route('profile.edit') }}" class="group flex min-h-[48px] items-center rounded-xl px-4 py-3 text-base font-medium transition-all duration-200 {{ request()->routeIs('profile.edit') ? 'bg-blue-600 text-white shadow-lg shadow-blue-200' : 'text-slate-600 hover:bg-slate-50 hover:text-blue-600' }}">Profile</a>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="group flex min-h-[48px] w-full items-center rounded-xl px-4 py-3 text-left text-base font-medium text-red-600 transition-all duration-200 hover:bg-red-50">
                Logout
            </button>
        </form>
    </nav>

    <div class="shrink-0 border-t border-slate-100 bg-slate-50/70 p-4">
        <div class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200/70">
            <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-sm font-bold text-white shadow-md">
                {{ substr(Auth::user()->name, 0, 1) }}
            </span>
            <div class="min-w-0">
                <p class="truncate text-sm font-bold text-slate-900">{{ Auth::user()->name }}</p>
                <p class="truncate text-xs font-medium text-slate-500">{{ Auth::user()->email }}</p>
            </div>
        </div>
    </div>
</aside>
</div>
