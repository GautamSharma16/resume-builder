<!-- User Navbar (Authenticated Regular Users) -->
<nav x-data="{ open: false }" class="bg-white border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex-shrink-0 group">
                <a href="{{ route('home') }}" class="flex items-center gap-2">
                    <!-- Logo Image with Animation -->
                    <img src="{{ asset('Logo.png') }}" 
                         alt="Cvbliss Logo"
                         class="h-20 w-50 lg:h-15 transition-all duration-500 group-hover:scale-110 group-hover:rotate-3">
                </a>
            </div>

            <!-- Navigation Links -->
            <div class="hidden xl:flex space-x-7">
                <a href="{{ route('resume-maker') }}" class="text-gray-700 hover:text-blue-600 transition py-4">Resume Maker</a>
                <a href="{{ route('enhance-cv') }}" class="text-gray-700 hover:text-blue-600 transition py-4">Enhance CV</a>
                <a href="{{ route('templates') }}" class="text-gray-700 hover:text-blue-600 transition py-4">Templates</a>
                <a href="{{ route('cover-letter') }}" class="text-gray-700 hover:text-blue-600 transition py-4">Cover Letter</a>
                <a href="{{ route('interview') }}" class="text-gray-700 hover:text-blue-600 transition py-4">Interview Tips</a>
                <a href="{{ route('contact') }}" class="text-gray-700 hover:text-blue-600 transition py-4">Contact Us</a>
            </div>

            <!-- User Menu -->
            <div class="hidden xl:flex items-center space-x-4">
                <x-dropdown align="right" width="56" contentClasses="py-2 bg-white">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center gap-2 text-gray-700 hover:text-blue-600 transition focus:outline-none">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ Auth::user()->name }}</span>
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-gray-100">
                            <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
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
            <button type="button" @click="open = !open" class="xl:hidden inline-flex h-10 w-10 items-center justify-center rounded-md border border-gray-200 text-gray-700" aria-label="Open navigation">
                <svg x-show="!open" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="open" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div x-show="open" class="xl:hidden border-t border-gray-100 py-3 space-y-1">
            <a href="{{ route('resume-maker') }}" class="block px-2 py-2 text-gray-700 hover:text-blue-600">Resume Maker</a>
            <a href="{{ route('enhance-cv') }}" class="block px-2 py-2 text-gray-700 hover:text-blue-600">Enhance CV</a>
            <a href="{{ route('templates') }}" class="block px-2 py-2 text-gray-700 hover:text-blue-600">Templates</a>
            <a href="{{ route('cover-letter') }}" class="block px-2 py-2 text-gray-700 hover:text-blue-600">Cover Letter</a>
            <a href="{{ route('interview') }}" class="block px-2 py-2 text-gray-700 hover:text-blue-600">Interview Tips</a>
            <a href="{{ route('contact') }}" class="block px-2 py-2 text-gray-700 hover:text-blue-600">Contact Us</a>
            <a href="{{ route('dashboard') }}" class="block px-2 py-2 text-gray-700 hover:text-blue-600">Dashboard</a>
            <a href="{{ route('profile.edit') }}" class="block px-2 py-2 text-gray-700 hover:text-blue-600">Profile</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button class="block w-full px-2 py-2 text-left text-red-600">Logout</button></form>
        </div>
    </div>
</nav>
