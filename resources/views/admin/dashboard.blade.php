@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Main Header Section --}}
    <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 p-10 shadow-2xl mb-10 border border-white/5">
        <div class="absolute -top-24 -right-24 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl animate-pulse"></div>
        <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-indigo-500/10 rounded-full blur-3xl"></div>
        
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-10">
            <div class="max-w-2xl">
               
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white tracking-tight mb-6 leading-[1.1]">
                    Operational <span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 to-indigo-400">Intelligence</span>.
                </h1>
                <p class="text-slate-400 text-lg leading-relaxed mb-8">
                    Monitor global platform activity, analyze growth vectors, and manage core system components through a unified executive interface.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('admin.visits') }}" class="px-6 py-3.5 rounded-2xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition-all shadow-xl shadow-blue-900/20 flex items-center gap-2">
                        View Analytics
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M13 7l5 5m0 0l-5 5m5-5H6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                    </a>
                    <a href="{{ route('home') }}" target="_blank" class="px-6 py-3.5 rounded-2xl bg-white/5 text-white font-bold hover:bg-white/10 transition-all border border-white/10 backdrop-blur-sm">
                        Public Site
                    </a>
                </div>
            </div>
            
            {{-- Real-time Pulse (Decorative) --}}
            <div class="hidden lg:block shrink-0">
                <div class="w-64 h-64 relative flex items-center justify-center">
                    <div class="absolute inset-0 border-2 border-dashed border-blue-500/20 rounded-full animate-[spin_20s_linear_infinite]"></div>
                    <div class="absolute inset-8 border-2 border-dashed border-indigo-500/20 rounded-full animate-[spin_15s_linear_infinite_reverse]"></div>
                    <div class="w-32 h-32 rounded-3xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shadow-2xl shadow-blue-500/40 rotate-12 group-hover:rotate-0 transition-transform duration-500">
                        <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path d="M13 10V3L4 14h7v7l9-11h-7z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4 gap-6 mb-10">
        {{-- Total Users --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 group relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <svg class="w-24 h-24 text-blue-600" fill="currentColor" viewBox="0 0 20 20"><path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3.005 3.005 0 013.75-2.906z"></path></svg>
            </div>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-2">Total Users</p>
            <h3 class="text-4xl font-black text-slate-900 mb-2" id="totalUsers">{{ $totalUsers ?? 0 }}</h3>
            <div class="flex items-center gap-1.5">
                <span class="text-[11px] font-bold text-emerald-500">+12%</span>
                <span class="text-[10px] text-slate-400">from last month</span>
            </div>
        </div>

        {{-- Total Resumes --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 group relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <svg class="w-24 h-24 text-amber-600" fill="currentColor" viewBox="0 0 20 20"><path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path><path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"></path></svg>
            </div>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-2">Total Resumes</p>
            <h3 class="text-4xl font-black text-slate-900 mb-2" id="totalResumes">{{ $totalResumes ?? 0 }}</h3>
            <div class="flex items-center gap-1.5">
                <span class="text-[11px] font-bold text-blue-500">{{ $totalTemplates }}</span>
                <span class="text-[10px] text-slate-400">active templates</span>
            </div>
        </div>

        {{-- Total Purchases --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 group relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <svg class="w-24 h-24 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path d="M4 4a2 2 0 00-2 2v1h16V6a2 2 0 00-2-2H4z"></path><path fill-rule="evenodd" d="M18 9H2v5a2 2 0 002 2h12a2 2 0 002-2V9zM4 13a1 1 0 011-1h1a1 1 0 110 2H5a1 1 0 01-1-1zm5-1a1 1 0 100 2h1a1 1 0 100-2H9z" clip-rule="evenodd"></path></svg>
            </div>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-2">Revenue</p>
            <h3 class="text-4xl font-black text-slate-900 mb-2" id="totalPurchases">{{ $totalPurchases ?? 0 }}</h3>
            <div class="flex items-center gap-1.5">
                <span class="text-[11px] font-bold text-emerald-500">Paid</span>
                <span class="text-[10px] text-slate-400">successful transactions</span>
            </div>
        </div>

        {{-- Total Visitors --}}
        <div class="bg-white p-8 rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-2xl transition-all duration-300 group relative overflow-hidden">
            <div class="absolute top-0 right-0 p-4 opacity-5 group-hover:opacity-10 transition-opacity">
                <svg class="w-24 h-24 text-indigo-600" fill="currentColor" viewBox="0 0 20 20"><path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"></path></svg>
            </div>
            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest mb-2">Global Traffic</p>
            <h3 class="text-4xl font-black text-slate-900 mb-2" id="totalVisitors">{{ $totalVisitors ?? 0 }}</h3>
            <div class="flex items-center gap-1.5">
                <span class="text-[11px] font-bold text-indigo-500">{{ $todayVisitors }}</span>
                <span class="text-[10px] text-slate-400">unique today</span>
            </div>
        </div>

       
    </div>

    {{-- Shortcuts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 grid grid-cols-1 sm:grid-cols-2 gap-6">
            <a href="{{ route('admin.users.index') }}" class="p-8 bg-white rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 group">
                <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 mb-6 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" stroke-width="2"></path></svg>
                </div>
                <h4 class="text-xl font-bold text-slate-900 mb-2">Team Governance</h4>
                <p class="text-sm text-slate-500 leading-relaxed mb-6">Manage administrative roles and staff access permissions across the board.</p>
                <span class="text-blue-600 font-bold text-sm flex items-center gap-2 group-hover:gap-3 transition-all">
                    Configure Team
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </span>
            </a>

            <a href="{{ route('admin.payments') }}" class="p-8 bg-white rounded-[2.5rem] border border-slate-100 shadow-sm hover:shadow-xl transition-all duration-300 group">
                <div class="w-12 h-12 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 mb-6 group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" stroke-width="2"></path></svg>
                </div>
                <h4 class="text-xl font-bold text-slate-900 mb-2">Pricing Engine</h4>
                <p class="text-sm text-slate-500 leading-relaxed mb-6">Adjust subscription tiers, pricing points, and discount features.</p>
                <span class="text-emerald-600 font-bold text-sm flex items-center gap-2 group-hover:gap-3 transition-all">
                    Modify Plans
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"></path></svg>
                </span>
            </a>
        </div>
                <a href="{{ route('admin.templates.index') }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center justify-between gap-3">
                        <div class="rounded-2xl bg-slate-100 p-3 text-slate-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Templates</span>
                    </div>
                    <h2 class="mt-6 text-xl font-semibold text-slate-950">Manage templates</h2>
                    <p class="mt-3 text-sm text-slate-500">Create, edit and preview resume or cover letter templates that power the builder.</p>
                    <div class="mt-6 flex items-center gap-2 text-sm font-semibold text-blue-600">
                        Open templates
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </div>
                </a>
  <a href="{{ route('admin.articles.index') }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center justify-between gap-3">
                        <div class="rounded-2xl bg-slate-100 p-3 text-slate-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 4H5m14-8H5" />
                            </svg>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Articles</span>
                    </div>
                    <h2 class="mt-6 text-xl font-semibold text-slate-950">Manage articles</h2>
                    <p class="mt-3 text-sm text-slate-500">Edit and publish blog content, interview tips and career advice pages.</p>
                    <div class="mt-6 flex items-center gap-2 text-sm font-semibold text-blue-600">
                        Open articles
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </div>
                </a>               

       

<script>
document.addEventListener('DOMContentLoaded', function() {
    function updateDashboardData() {
        fetch('{{ route("admin.dashboard.data") }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('totalUsers').textContent = data.totalUsers;
            document.getElementById('totalResumes').textContent = data.totalResumes;
            document.getElementById('totalTemplates').textContent = data.totalTemplates;
            document.getElementById('totalPurchases').textContent = data.totalPurchases;
            document.getElementById('totalVisitors').textContent = data.totalVisitors;
        })
        .catch(error => console.error('Error fetching dashboard data:', error));
    }

    // Initial update
    updateDashboardData();
    // Poll every 30s
    setInterval(updateDashboardData, 30000);
});
</script>
@endsection
