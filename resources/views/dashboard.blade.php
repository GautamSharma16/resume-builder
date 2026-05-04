<x-app-layout>
    <div class="min-h-screen bg-[#f8fafc] py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            <!-- Header with Glassmorphism -->
            <div class="relative overflow-hidden rounded-3xl bg-white/40 backdrop-blur-xl border border-white/20 p-8 sm:p-12 shadow-2xl mb-12">
                <div class="absolute top-0 right-0 -mt-20 -mr-20 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl"></div>
                <div class="absolute bottom-0 left-0 -mb-20 -ml-20 w-64 h-64 bg-purple-500/10 rounded-full blur-3xl"></div>
                
                <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                    <div>
                        <h1 class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight">
                            Welcome, <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">{{ Auth::user()->name }}</span>! 
                        </h1>
                        <p class="mt-4 text-lg text-slate-600 font-medium max-w-xl">
                            Ready to take the next step in your career? Let's build a resume that gets you hired.
                        </p>
                    </div>
                    <div class="flex gap-4">
                        <a href="{{ route('resume.create') }}" 
                           class="inline-flex items-center px-6 py-3 rounded-2xl bg-blue-600 text-white font-bold shadow-lg shadow-blue-200 hover:bg-blue-700 hover:scale-105 transition-all duration-300">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Create Resume
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 mb-12">
                <!-- Resumes Count -->
                <div class="group bg-white rounded-3xl p-8 border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-50 rounded-2xl group-hover:bg-blue-600 group-hover:text-white transition-colors duration-300 text-blue-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active</span>
                    </div>
                    <h3 class="text-slate-500 font-bold text-sm">Resumes Created</h3>
                    <p class="text-4xl font-black text-slate-900 mt-1">0</p>
                </div>

                <!-- Downloads -->
                <div class="group bg-white rounded-3xl p-8 border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-green-50 rounded-2xl group-hover:bg-green-600 group-hover:text-white transition-colors duration-300 text-green-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        </div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">All Time</span>
                    </div>
                    <h3 class="text-slate-500 font-bold text-sm">Downloads</h3>
                    <p class="text-4xl font-black text-slate-900 mt-1">0</p>
                </div>

                <!-- Progress -->
                <div class="group bg-white rounded-3xl p-8 border border-slate-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-purple-50 rounded-2xl group-hover:bg-purple-600 group-hover:text-white transition-colors duration-300 text-purple-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Profile</span>
                    </div>
                    <h3 class="text-slate-500 font-bold text-sm mb-3">Completion Status</h3>
                    <div class="relative pt-1">
                        <div class="overflow-hidden h-2 mb-4 text-xs flex rounded-full bg-slate-100">
                            <div style="width:10%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-purple-500 to-purple-600"></div>
                        </div>
                        <div class="flex items-center justify-between text-xs font-bold text-slate-400 uppercase">
                            <span>Getting Started</span>
                            <span class="text-purple-600">10%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Resumes List -->
                <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-100 shadow-sm overflow-hidden">
                    <div class="px-8 py-6 border-b border-slate-50 flex items-center justify-between">
                        <h2 class="text-xl font-black text-slate-900">Your Recent Resumes</h2>
                        <a href="{{ route('resume.index') }}" class="text-sm font-bold text-blue-600 hover:text-blue-700">View All</a>
                    </div>
                    <div class="p-8">
                        <div class="text-center py-12 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200">
                            <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-sm">
                                <svg class="w-10 h-10 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </div>
                            <h3 class="text-lg font-bold text-slate-900 mb-2">No resumes yet</h3>
                            <p class="text-slate-500 mb-6 max-w-xs mx-auto">Create your first professional resume in minutes using our AI-powered builder.</p>
                            <a href="{{ route('resume.create') }}" class="inline-flex items-center font-bold text-blue-600 hover:text-blue-700">
                                Start Building Now
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Side Info / Tips -->
                <div class="space-y-8">
                    <!-- Templates Promo -->
                    <div class="bg-slate-900 rounded-3xl p-8 text-white relative overflow-hidden group">
                        <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-blue-500/20 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                        <h3 class="text-xl font-bold mb-4 relative">Premium Templates</h3>
                        <p class="text-slate-400 text-sm mb-6 relative">Choose from over 50+ hand-crafted, ATS-optimized templates designed by recruitment experts.</p>
                        <a href="{{ route('templates') }}" class="inline-flex items-center text-sm font-bold text-white group-hover:translate-x-1 transition-transform">
                            Browse Gallery
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                    </div>

                    <!-- Career Tips -->
                    <div class="bg-white rounded-3xl p-8 border border-slate-100 shadow-sm">
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Quick Tip</h3>
                        <div class="flex items-start gap-4">
                            <div class="p-2 bg-yellow-50 text-yellow-600 rounded-lg">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <p class="text-sm text-slate-600 leading-relaxed">
                                Always customize your resume for every job application. Mention key skills listed in the job description to pass ATS filters.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
