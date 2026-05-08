<x-app-layout>
    <div class="min-h-screen py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">
            
            <!-- Header Section -->
            <div class="mb-10 animate-in fade-in slide-in-from-top-4 duration-700">
                <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                    <div>
                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-wider mb-3">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-600 animate-pulse"></span>
                            User Dashboard
                        </span>
                        <h1 class="text-4xl md:text-5xl font-display text-navy leading-tight">
                            Welcome back, <span class="text-blue-600">{{ Auth::user()->name }}</span>
                        </h1>
                        <p class="mt-3 text-muted text-lg max-w-2xl font-medium">
                            Manage your professional profile, track your applications, and create high-converting career documents.
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('resume.create') }}" class="btn-primary shadow-blue-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            New Resume
                        </a>
                        <a href="{{ route('cover-letter') }}" class="btn-outline">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            Cover Letter
                        </a>
                    </div>
                </div>
            </div>

            <!-- Stats & Plan Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                <!-- Active Plan Card -->
                <div class="bg-navy rounded-[var(--r-2xl)] p-8 text-white relative overflow-hidden group">
                    <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-blue-500/20 rounded-full blur-3xl group-hover:scale-125 transition-transform duration-700"></div>
                    <div class="relative z-10">
                        <h3 class="text-blue-400 font-bold uppercase tracking-widest text-xs mb-6">Current Subscription</h3>
                        @if($activeSubscription)
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-3xl font-display">{{ $activeSubscription->plan->name ?? 'Premium Plan' }}</div>
                                <span class="px-3 py-1 rounded-full bg-green-500/20 text-green-400 text-[10px] font-black uppercase border border-green-500/30">Active</span>
                            </div>
                            <p class="text-slate-400 text-sm mb-6">Renews on {{ \Carbon\Carbon::parse($activeSubscription->expiry_date)->format('M d, Y') }}</p>
                            <a href="{{ route('plans') }}" class="text-white text-xs font-bold underline underline-offset-4 hover:text-blue-300 transition-colors">Manage Subscription</a>
                        @else
                            <div class="flex items-center justify-between mb-4">
                                <div class="text-3xl font-display">Free Explorer</div>
                                <span class="px-3 py-1 rounded-full bg-yellow-500/20 text-yellow-400 text-[10px] font-black uppercase border border-yellow-500/30">Limited</span>
                            </div>
                            <p class="text-slate-400 text-sm mb-6">Upgrade to unlock premium templates and AI features.</p>
                            <a href="{{ route('plans') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 rounded-xl text-white text-xs font-bold hover:bg-blue-700 transition-colors">
                                Upgrade Now
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            </a>
                        @endif
                    </div>
                </div>

                <!-- Resumes Count -->
                <div class="bg-white rounded-[var(--r-2xl)] p-8 border border-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                        <span class="text-[10px] font-black text-muted uppercase tracking-widest">Total Resumes</span>
                    </div>
                    <div>
                        <div class="text-5xl font-display text-navy">{{ $totalResumes }}</div>
                        <p class="text-muted text-sm mt-1 font-medium">Professional resumes created</p>
                    </div>
                </div>

                <!-- Cover Letters Count -->
                <div class="bg-white rounded-[var(--r-2xl)] p-8 border border-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-purple-50 text-purple-600 rounded-2xl">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <span class="text-[10px] font-black text-muted uppercase tracking-widest">Cover Letters</span>
                    </div>
                    <div>
                        <div class="text-5xl font-display text-navy">{{ $totalCoverLetters }}</div>
                        <p class="text-muted text-sm mt-1 font-medium">Personalized cover letters</p>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Recent Resumes List -->
                <div class="lg:col-span-2 space-y-6">
                    <div class="flex items-center justify-between px-2">
                        <h2 class="text-2xl font-display text-navy">Recent Resumes</h2>
                        <a href="{{ route('resume.index') }}" class="text-sm font-bold text-blue-600 hover:underline">View All</a>
                    </div>
                    
                    @if($recentResumes->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($recentResumes as $resume)
                                <div class="bg-white rounded-[var(--r-xl)] border border-border p-5 hover:shadow-lg transition-all duration-300 group">
                                    <div class="flex gap-4">
                                        <div class="w-20 h-28 bg-slate-50 rounded-lg flex-shrink-0 overflow-hidden border border-slate-100 relative">
                                            @if($resume->template && $resume->template->thumbnail)
                                                <img src="{{ asset('storage/' . $resume->template->thumbnail) }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center text-slate-300">
                                                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="flex-1 flex flex-col justify-between">
                                            <div>
                                                <h4 class="font-bold text-navy line-clamp-1 mb-1">{{ $resume->title ?: 'Untitled Resume' }}</h4>
                                                <p class="text-[10px] text-muted font-bold uppercase tracking-wider mb-2">
                                                    {{ $resume->template->name ?? 'Modern Template' }}
                                                </p>
                                                <p class="text-[11px] text-slate-400">Modified {{ $resume->updated_at->diffForHumans() }}</p>
                                            </div>
                                            <div class="flex gap-2 pt-3">
                                                <a href="{{ route('resume.edit', $resume) }}" class="p-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-600 hover:text-white transition-colors" title="Edit">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                                </a>
                                                <a href="{{ route('resume.download', $resume) }}" class="p-2 bg-slate-50 text-slate-600 rounded-lg hover:bg-navy hover:text-white transition-colors" title="Download">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="bg-white rounded-[var(--r-2xl)] border-2 border-dashed border-slate-200 p-12 text-center">
                            <div class="w-16 h-16 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-8 h-8 text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            </div>
                            <h3 class="text-lg font-bold text-navy mb-1">Create your first resume</h3>
                            <p class="text-muted text-sm mb-6">Choose a template and let our AI help you write the perfect content.</p>
                            <a href="{{ route('resume.create') }}" class="btn-primary">Start Building</a>
                        </div>
                    @endif
                </div>

                <!-- Recent Cover Letters Sidebar -->
                <div class="space-y-6">
                    <div class="flex items-center justify-between px-2">
                        <h2 class="text-2xl font-display text-navy">Cover Letters</h2>
                    </div>

                    <div class="bg-white rounded-[var(--r-2xl)] border border-border shadow-sm overflow-hidden">
                        <div class="p-6">
                            @forelse($recentCoverLetters as $cl)
                                <div class="group py-4 @if(!$loop->last) border-b border-slate-50 @endif">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex-1 min-w-0">
                                            <h5 class="font-bold text-navy truncate leading-tight mb-1">{{ $cl->job_role ?: 'General Application' }}</h5>
                                            <p class="text-xs text-muted truncate">{{ $cl->company ?: 'N/A' }}</p>
                                            <p class="text-[10px] text-slate-400 mt-2">{{ $cl->updated_at->format('M d, Y') }}</p>
                                        </div>
                                        <div class="flex gap-1">
                                            <a href="{{ route('cover-letter.download', $cl) }}" class="p-1.5 bg-slate-50 text-slate-400 hover:text-blue-600 rounded-md transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="py-10 text-center">
                                    <p class="text-slate-400 text-sm">No cover letters yet.</p>
                                    <a href="{{ route('cover-letter') }}" class="text-blue-600 text-xs font-bold mt-2 inline-block hover:underline">Create One Now</a>
                                </div>
                            @endforelse
                        </div>
                        @if($recentCoverLetters->count() > 0)
                            <div class="bg-slate-50 px-6 py-3 border-t border-slate-100">
                                <a href="#" class="text-xs font-bold text-muted hover:text-blue-600 flex items-center justify-center gap-1 transition-colors">
                                    Browse All
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Upgrade Teaser -->
                    @if(!$activeSubscription)
                        <div class="bg-gradient-to-br from-purple-600 to-blue-700 rounded-[var(--r-2xl)] p-8 text-white relative overflow-hidden">
                            <div class="absolute top-0 right-0 opacity-10">
                                <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z"/></svg>
                            </div>
                            <div class="relative z-10">
                                <h3 class="text-xl font-display mb-3">Professional Edge</h3>
                                <p class="text-white/70 text-xs leading-relaxed mb-6">Unlock all premium templates and AI-powered grammar enhancement to stand out from the crowd.</p>
                                <a href="{{ route('plans') }}" class="btn-secondary w-full justify-center">Go Premium</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
