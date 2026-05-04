<!-- Footer Component -->
<footer class="bg-white border-t border-gray-100 pt-12 pb-6 shadow-inner" x-data="{ year: new Date().getFullYear() }">
    <div class="max-w-full mx-auto px-4 sm:px-10 lg:px-16">
        <!-- Top Footer Grid: Logo + Social + Quick Links -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-8 lg:gap-12">
            
            <!-- Left Column: Logo & Branding (same style as navbar) -->
            <div class="md:col-span-4 space-y-4">
                <div class="group inline-block">
                    <a href="{{ route('home') }}" class="flex items-center gap-2">
                        <!-- Same logo image with hover animation -->
                        <img src="{{ asset('Logo.png') }}" 
                             alt="Cvbliss Logo"
                             class="h-20 w-30 lg:h-20 transition-all duration-500 group-hover:scale-110 group-hover:rotate-3">
                    </a>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed max-w-sm">
                    Cvbliss empowers job seekers with modern resume tools, AI enhancement, and expert interview strategies. Land your dream job with confidence.
                </p>
                <!-- Social Media Links (no newsletter) -->
                <div class="flex items-center gap-4 pt-2">
                    <span class="text-sm font-medium text-gray-700">Follow us:</span>
                    <div class="flex space-x-3">
                        <!-- Facebook -->
                        <a href="https://facebook.com/cvbliss" target="_blank" rel="noopener noreferrer" 
                           class="group relative flex items-center justify-center w-9 h-9 rounded-full bg-gray-100 text-gray-600 hover:bg-gradient-to-r hover:from-blue-600 hover:to-indigo-600 hover:text-white transition-all duration-300 hover:shadow-md hover:scale-110">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="absolute -top-8 opacity-0 group-hover:opacity-100 bg-gray-800 text-white text-xs px-2 py-1 rounded-md pointer-events-none transition whitespace-nowrap">Facebook</span>
                        </a>
                        <!-- Twitter/X -->
                        <a href="https://twitter.com/cvbliss" target="_blank" rel="noopener noreferrer" 
                           class="group relative flex items-center justify-center w-9 h-9 rounded-full bg-gray-100 text-gray-600 hover:bg-gradient-to-r hover:from-blue-600 hover:to-indigo-600 hover:text-white transition-all duration-300 hover:scale-110">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
                            </svg>
                            <span class="absolute -top-8 opacity-0 group-hover:opacity-100 bg-gray-800 text-white text-xs px-2 py-1 rounded-md pointer-events-none transition">Twitter</span>
                        </a>
                        <!-- LinkedIn -->
                        <a href="https://linkedin.com/company/cvbliss" target="_blank" rel="noopener noreferrer" 
                           class="group relative flex items-center justify-center w-9 h-9 rounded-full bg-gray-100 text-gray-600 hover:bg-gradient-to-r hover:from-blue-600 hover:to-indigo-600 hover:text-white transition-all duration-300 hover:scale-110">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"></path>
                            </svg>
                            <span class="absolute -top-8 opacity-0 group-hover:opacity-100 bg-gray-800 text-white text-xs px-2 py-1 rounded-md pointer-events-none transition">LinkedIn</span>
                        </a>
                        <!-- Instagram -->
                        <a href="https://instagram.com/cvbliss" target="_blank" rel="noopener noreferrer" 
                           class="group relative flex items-center justify-center w-9 h-9 rounded-full bg-gray-100 text-gray-600 hover:bg-gradient-to-r hover:from-pink-500 hover:to-orange-500 hover:text-white transition-all duration-300 hover:scale-110">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="absolute -top-8 opacity-0 group-hover:opacity-100 bg-gray-800 text-white text-xs px-2 py-1 rounded-md pointer-events-none transition">Instagram</span>
                        </a>
                        <!-- YouTube -->
                        <a href="https://youtube.com/cvbliss" target="_blank" rel="noopener noreferrer" 
                           class="group relative flex items-center justify-center w-9 h-9 rounded-full bg-gray-100 text-gray-600 hover:bg-gradient-to-r hover:from-red-600 hover:to-red-700 hover:text-white transition-all duration-300 hover:scale-110">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill-rule="evenodd" d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.42 4.814a2.504 2.504 0 0 1-1.768 1.768c-1.56.419-7.812.419-7.812.419s-6.252 0-7.812-.419a2.505 2.505 0 0 1-1.768-1.768C2 15.255 2 12 2 12s0-3.255.42-4.814a2.505 2.505 0 0 1 1.768-1.768c1.56-.419 7.812-.419 7.812-.419s6.252 0 7.812.419ZM10 15.464l5.203-3.464L10 8.536v6.928Z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="absolute -top-8 opacity-0 group-hover:opacity-100 bg-gray-800 text-white text-xs px-2 py-1 rounded-md pointer-events-none transition">YouTube</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Middle column: Quick Links (Explore) -->
            <div class="md:col-span-3">
                <h3 class="text-base font-semibold text-gray-800 tracking-wide uppercase border-b border-gray-200 pb-2 inline-block">Explore</h3>
                <ul class="mt-4 space-y-2.5">
                    <li>
                        <a href="{{ route('resume-maker') }}" class="text-gray-500 hover:text-blue-600 transition-all duration-200 flex items-center gap-2 group">
                            <span class="w-1 h-1 bg-gray-300 rounded-full group-hover:bg-blue-500 transition"></span> 
                            Resume Maker
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('enhance-cv') }}" class="text-gray-500 hover:text-blue-600 transition-all duration-200 flex items-center gap-2 group">
                            <span class="w-1 h-1 bg-gray-300 rounded-full group-hover:bg-blue-500 transition"></span> 
                            Enhance CV (AI)
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('templates') }}" class="text-gray-500 hover:text-blue-600 transition-all duration-200 flex items-center gap-2 group">
                            <span class="w-1 h-1 bg-gray-300 rounded-full group-hover:bg-blue-500 transition"></span> 
                            Professional Templates
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('cover-letter') }}" class="text-gray-500 hover:text-blue-600 transition-all duration-200 flex items-center gap-2 group">
                            <span class="w-1 h-1 bg-gray-300 rounded-full group-hover:bg-blue-500 transition"></span> 
                            Cover Letter Builder
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('interview') }}" class="text-gray-500 hover:text-blue-600 transition-all duration-200 flex items-center gap-2 group">
                            <span class="w-1 h-1 bg-gray-300 rounded-full group-hover:bg-blue-500 transition"></span> 
                            Interview Preparation
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Support column -->
            <div class="md:col-span-3">
                <h3 class="text-base font-semibold text-gray-800 tracking-wide uppercase border-b border-gray-200 pb-2 inline-block">Support</h3>
                <ul class="mt-4 space-y-2.5">
                    <li>
                        <a href="{{ route('contact') }}" class="text-gray-500 hover:text-blue-600 transition-all duration-200 flex items-center gap-2 group">
                            <span class="w-1 h-1 bg-gray-300 rounded-full group-hover:bg-blue-500 transition"></span> 
                            Contact Us
                        </a>
                    </li>
                    <li>
                       
                            <span class="w-1 h-1 bg-gray-300 rounded-full group-hover:bg-blue-500 transition"></span> 
                            FAQ / Help Center
                        </a>
                    </li>
                    <li>
                        
                            <span class="w-1 h-1 bg-gray-300 rounded-full group-hover:bg-blue-500 transition"></span> 
                            Privacy Policy
                        </a>
                    </li>
                    <li>
                        
                            <span class="w-1 h-1 bg-gray-300 rounded-full group-hover:bg-blue-500 transition"></span> 
                            Terms of Service
                        </a>
                    </li>
                    <li>
                        
                            Cookie Settings
                        </a>
                    </li>
                </ul>
            </div>

            <!-- Contact column -->
            <div class="md:col-span-2">
                <h3 class="text-base font-semibold text-gray-800 tracking-wide uppercase border-b border-gray-200 pb-2 inline-block">Get in Touch</h3>
                <ul class="mt-4 space-y-3">
                    <li class="flex items-start gap-2 text-gray-500 text-sm">
                        <svg class="w-4 h-4 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span>hello@cvbliss.com</span>
                    </li>
                    <li class="flex items-start gap-2 text-gray-500 text-sm">
                        <svg class="w-4 h-4 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <span>+1 (800) 123-4567</span>
                    </li>
                    <li class="flex items-start gap-2 text-gray-500 text-sm">
                        <svg class="w-4 h-4 text-blue-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span>San Francisco, CA<br>Remote Global</span>
                    </li>
                </ul>
                <!-- Trust badge -->
                <div class="mt-5">
                    <div class="flex items-center gap-2 text-xs text-gray-400">
                        <svg class="w-4 h-4 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <span>Secure & Trusted platform</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Divider + Copyright -->
        <div class="mt-12 pt-6 border-t border-gray-200 flex flex-col md:flex-row justify-between items-center gap-4">
            <p class="text-gray-500 text-sm">&copy; {{ date('Y') }} Cvbliss. All rights reserved. Elevate your career journey.</p>
            <!-- Subtle social icons in copyright row -->
            <div class="flex space-x-4">
                <a href="https://facebook.com/cvbliss" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-blue-500 transition-all">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"></path>
                    </svg>
                </a>
                <a href="https://twitter.com/cvbliss" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-blue-500 transition-all">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"></path>
                    </svg>
                </a>
                <a href="https://linkedin.com/company/cvbliss" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-indigo-500 transition-all">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"></path>
                    </svg>
                </a>
                <a href="https://instagram.com/cvbliss" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-pink-500 transition-all">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63z"></path>
                    </svg>
                </a>
                <a href="https://youtube.com/cvbliss" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-red-500 transition-all">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19.812 5.418c.861.23 1.538.907 1.768 1.768C21.998 8.746 22 12 22 12s0 3.255-.42 4.814a2.504 2.504 0 01-1.768 1.768c-1.56.419-7.812.419-7.812.419s-6.252 0-7.812-.419a2.505 2.505 0 01-1.768-1.768C2 15.255 2 12 2 12s0-3.255.42-4.814a2.505 2.505 0 011.768-1.768c1.56-.419 7.812-.419 7.812-.419s6.252 0 7.812.419zM10 15.464l5.203-3.464L10 8.536v6.928z"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</footer>

<style>
    [x-cloak] { display: none !important; }
    
    /* Custom hover animations */
    .footer-hover-transition {
        transition: all 0.2s ease;
    }
</style>