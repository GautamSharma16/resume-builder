@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-slate-900 p-10 shadow-2xl mb-10">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
            <div>
                <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-blue-500/10 text-blue-400 text-xs font-bold uppercase tracking-widest mb-6 border border-blue-500/20">
                    SEO Control Center
                </span>
                <h1 class="text-4xl md:text-5xl font-bold text-white tracking-tight mb-4">
                    Optimizing <span class="text-blue-500">Reach</span>.
                </h1>
                <p class="text-slate-400 text-lg max-w-2xl leading-relaxed">
                    Track your content performance, monitor visitor trends, and manage articles to boost organic growth and search engine visibility.
                </p>
            </div>
            <div class="shrink-0">
                <a href="{{ route('admin.articles.index') }}" class="inline-flex items-center px-6 py-3.5 rounded-2xl bg-white text-slate-900 font-bold hover:bg-slate-100 transition-all shadow-xl">
                    Manage Articles
                    <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M17 8l4 4m0 0l-4 4m4-4H3" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </a>
            </div>
        </div>
    </div>

    {{-- SEO Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 group">
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Total Articles</p>
            <h3 class="text-3xl font-bold text-slate-900">{{ \App\Models\Article::count() }}</h3>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 group">
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Unique Visitors</p>
            <h3 class="text-3xl font-bold text-slate-900">{{ $totalVisitors }}</h3>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 group">
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Today's Visits</p>
            <h3 class="text-3xl font-bold text-slate-900">{{ $todayVisits }}</h3>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 group">
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Last Updated</p>
            <h3 class="text-lg font-bold text-slate-900">{{ now()->format('M d, H:i') }}</h3>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Recent Articles --}}
        <div class="bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-xl font-bold text-slate-900">Recent Articles</h2>
                <a href="{{ route('admin.articles.index') }}" class="text-blue-600 font-bold text-sm hover:underline">View All</a>
            </div>
            <div class="space-y-4">
                @php
                    $recentArticles = \App\Models\Article::latest()->take(5)->get();
                @endphp
                @foreach($recentArticles as $article)
                <div class="flex items-center justify-between p-4 rounded-2xl hover:bg-slate-50 transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-10 h-10 bg-slate-100 rounded-xl flex items-center justify-center text-slate-500">
                            {{ $loop->iteration }}
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 text-sm truncate max-w-[200px]">{{ $article->title }}</p>
                            <p class="text-xs text-slate-400">{{ $article->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <a href="{{ route('admin.articles.edit', $article) }}" class="p-2 text-slate-400 hover:text-blue-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 00-2 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    </a>
                </div>
                @endforeach
            </div>
        </div>

        
    </div>
</div>
@endsection
