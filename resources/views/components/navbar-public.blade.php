<!-- Public Navbar (Unauthenticated Users) -->
<nav x-data="{ open: false }" class="bg-white shadow">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="{{ route('home') }}" class="text-2xl font-bold text-blue-600">
                    Cvbliss
                </a>
            </div>

            <!-- Navigation Links -->
            <div class="hidden lg:flex space-x-8">
                <a href="{{ route('resume-maker') }}" class="text-gray-700 hover:text-blue-600 transition">Resume Maker</a>
                <a href="{{ route('enhance-cv') }}" class="text-gray-700 hover:text-blue-600 transition">Enhance CV</a>
                <a href="{{ route('templates') }}" class="text-gray-700 hover:text-blue-600 transition">Templates</a>
                <a href="{{ route('cover-letter') }}" class="text-gray-700 hover:text-blue-600 transition">Cover Letter</a>
                <a href="{{ route('interview') }}" class="text-gray-700 hover:text-blue-600 transition">Interview Tips</a>
                <a href="{{ route('contact') }}" class="text-gray-700 hover:text-blue-600 transition">Contact Us</a>
            </div>

            <!-- Auth Buttons -->
            <div class="hidden lg:flex items-center space-x-4">
                <a href="{{ route('login') }}" class="text-gray-700 hover:text-blue-600 transition">Login / Register</a>
            </div>
            <button type="button" @click="open = !open" class="lg:hidden inline-flex h-10 w-10 items-center justify-center rounded-md border border-gray-200 text-gray-700" aria-label="Open navigation">
                <svg x-show="!open" class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="open" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div x-show="open" class="lg:hidden border-t border-gray-100 py-3 space-y-1">
            <a href="{{ route('resume-maker') }}" class="block px-2 py-2 text-gray-700 hover:text-blue-600">Resume Maker</a>
            <a href="{{ route('enhance-cv') }}" class="block px-2 py-2 text-gray-700 hover:text-blue-600">Enhance CV</a>
            <a href="{{ route('templates') }}" class="block px-2 py-2 text-gray-700 hover:text-blue-600">Templates</a>
            <a href="{{ route('cover-letter') }}" class="block px-2 py-2 text-gray-700 hover:text-blue-600">Cover Letter</a>
            <a href="{{ route('interview') }}" class="block px-2 py-2 text-gray-700 hover:text-blue-600">Interview Tips</a>
            <a href="{{ route('contact') }}" class="block px-2 py-2 text-gray-700 hover:text-blue-600">Contact Us</a>
            <a href="{{ route('login') }}" class="block px-2 py-2 font-semibold text-blue-600">Login / Register</a>
        </div>
    </div>
</nav>
