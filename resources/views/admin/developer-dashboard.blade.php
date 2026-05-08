@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-indigo-950 p-10 shadow-2xl mb-10">
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-8">
            <div>
                <span class="inline-flex items-center px-4 py-1.5 rounded-full bg-indigo-500/10 text-indigo-400 text-xs font-bold uppercase tracking-widest mb-6 border border-indigo-500/20">
                    Developer Control Center
                </span>
                <h1 class="text-4xl md:text-5xl font-bold text-white tracking-tight mb-4">
                    System <span class="text-indigo-400">Architecture</span>.
                </h1>
                <p class="text-slate-400 text-lg max-w-2xl leading-relaxed">
                    Monitor system health, manage technical components, and track technical performance metrics to ensure a seamless experience for all users.
                </p>
            </div>
            <div class="shrink-0 flex gap-4">
                <a href="{{ route('admin.templates.index') }}" class="inline-flex items-center px-6 py-3.5 rounded-2xl bg-white text-indigo-950 font-bold hover:bg-slate-100 transition-all shadow-xl">
                    Manage Templates
                </a>
                <a href="{{ route('admin.payments') }}" class="inline-flex items-center px-6 py-3.5 rounded-2xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 transition-all shadow-xl">
                    Pricing Logic
                </a>
            </div>
        </div>
    </div>

    {{-- Technical Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3  gap-6 mb-10">
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 group">
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Registered Users</p>
            <h3 class="text-3xl font-bold text-slate-900">{{ $totalUsers }}</h3>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 group">
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">System Templates</p>
            <h3 class="text-3xl font-bold text-slate-900">{{ $totalTemplates }}</h3>
        </div>

        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 group">
            <p class="text-slate-500 text-xs font-bold uppercase tracking-widest mb-1">Today's Traffic</p>
            <h3 class="text-3xl font-bold text-slate-900">{{ $todayVisits }}</h3>
        </div>

      
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        {{-- Recent Logs (Simplified) --}}
        <div class="bg-white rounded-[2.5rem] border border-slate-100 p-8 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h2 class="text-xl font-bold text-slate-900">Recent Technical Events</h2>
                <a href="{{ route('admin.visits') }}" class="text-indigo-600 font-bold text-sm hover:underline">View All Traffic</a>
            </div>
            <div class="space-y-4">
                @php
                    $recentVisits = \App\Models\VisitorLog::latest()->take(5)->get();
                @endphp
                @foreach($recentVisits as $visit)
                <div class="flex items-center justify-between p-4 rounded-2xl hover:bg-slate-50 transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="w-2 h-2 bg-indigo-500 rounded-full"></div>
                        <div>
                            <p class="font-mono text-[11px] text-slate-900 font-bold">{{ $visit->path }}</p>
                            <p class="text-[10px] text-slate-400 font-mono">{{ $visit->ip_address }} • {{ $visit->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-1 bg-slate-100 rounded text-slate-500">GET</span>
                </div>
                @endforeach
            </div>
        </div>

       
</div>
@endsection
