@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="rounded-[2rem] bg-gradient-to-r from-slate-950 via-slate-900 to-slate-950 p-8 shadow-2xl ring-1 ring-white/10 overflow-hidden sm:p-10">
        <div class="sm:flex sm:items-start sm:justify-between gap-6">
            <div class="max-w-3xl">
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-cyan-300">Admin dashboard</p>
                <h1 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">Operations overview</h1>
                <p class="mt-4 text-sm leading-7 text-slate-300">Monitor site performance, track key metrics, and access the most important admin tools from one streamlined control panel.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}" class="inline-flex items-center justify-center rounded-full border border-white/15 bg-white/10 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-white/20">View live site</a>
            </div>
        </div>

        <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
            <div class="rounded-3xl bg-white/10 p-6 ring-1 ring-white/10 backdrop-blur-sm">
                <div class="flex items-center justify-between gap-3">
                    <div class="rounded-2xl bg-cyan-500/15 p-3 text-cyan-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">Active users</span>
                </div>
                <p class="mt-6 text-3xl font-semibold text-white" id="totalUsers">{{ $totalUsers ?? 0 }}</p>
                <p class="mt-2 text-sm text-slate-300">Registered users across the platform.</p>
            </div>
            <div class="rounded-3xl bg-white/10 p-6 ring-1 ring-white/10 backdrop-blur-sm">
                <div class="flex items-center justify-between gap-3">
                    <div class="rounded-2xl bg-amber-500/15 p-3 text-amber-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v2m0 8v2m4-10h2m-10 0H6m12 4h2m-10 0H6" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">Resumes</span>
                </div>
                <p class="mt-6 text-3xl font-semibold text-white" id="totalResumes">{{ $totalResumes ?? 0 }}</p>
                <p class="mt-2 text-sm text-slate-300">Saved builder resumes and uploaded resume analyses.</p>
            </div>
            <div class="rounded-3xl bg-white/10 p-6 ring-1 ring-white/10 backdrop-blur-sm">
                <div class="flex items-center justify-between gap-3">
                    <div class="rounded-2xl bg-sky-500/15 p-3 text-sky-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">Templates</span>
                </div>
                <p class="mt-6 text-3xl font-semibold text-white" id="totalTemplates">{{ $totalTemplates ?? 0 }}</p>
                <p class="mt-2 text-sm text-slate-300">All resume and cover letter templates.</p>
            </div>
            <div class="rounded-3xl bg-white/10 p-6 ring-1 ring-white/10 backdrop-blur-sm">
                <div class="flex items-center justify-between gap-3">
                    <div class="rounded-2xl bg-emerald-500/15 p-3 text-emerald-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 6h18M6 14h9m-9 4h6" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">Purchases</span>
                </div>
                <p class="mt-6 text-3xl font-semibold text-white" id="totalPurchases">{{ $totalPurchases ?? 0 }}</p>
                <p class="mt-2 text-sm text-slate-300">Paid purchases processed successfully.</p>
            </div>
            <div class="rounded-3xl bg-white/10 p-6 ring-1 ring-white/10 backdrop-blur-sm">
                <div class="flex items-center justify-between gap-3">
                    <div class="rounded-2xl bg-violet-500/15 p-3 text-violet-300">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 3v4M5 17v4M19 3v4M19 17v4M9 7h6m-6 10h6" />
                        </svg>
                    </div>
                    <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-300">Visitors</span>
                </div>
                <p class="mt-6 text-3xl font-semibold text-white" id="totalVisitors">{{ $totalVisitors ?? 0 }}</p>
                <p class="mt-2 text-sm text-slate-300">Unique visitor sessions in your analytics.</p>
            </div>
        </div>
    </div>

    <section class="mt-8 grid gap-5 xl:grid-cols-[2fr_1fr]">
        <div class="grid gap-5 sm:grid-cols-2">
            @if(auth()->user()->hasRole(['admin','developer','dev']))
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

                <a href="{{ route('admin.payments') }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center justify-between gap-3">
                        <div class="rounded-2xl bg-slate-100 p-3 text-slate-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v2m0 8v2m4-10h2m-10 0H6" />
                            </svg>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Pricing</span>
                    </div>
                    <h2 class="mt-6 text-xl font-semibold text-slate-950">Pricing plans</h2>
                    <p class="mt-3 text-sm text-slate-500">Adjust plan details and pricing options available to customers.</p>
                    <div class="mt-6 flex items-center gap-2 text-sm font-semibold text-blue-600">
                        Review pricing
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </div>
                </a>
            @endif

            @if(auth()->user()->hasRole(['admin','seo','article','article_writer']))
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
            @endif

            @if(auth()->user()->hasPermission('team'))
                <a href="{{ route('admin.users.index') }}" class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition hover:-translate-y-1 hover:shadow-xl">
                    <div class="flex items-center justify-between gap-3">
                        <div class="rounded-2xl bg-slate-100 p-3 text-slate-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1M9 20H4v-2a4 4 0 014-4h1m0-4a4 4 0 110-8 4 4 0 010 8zm6 0a4 4 0 110-8 4 4 0 010 8z" />
                            </svg>
                        </div>
                        <span class="text-xs font-semibold uppercase tracking-[0.24em] text-slate-500">Team</span>
                    </div>
                    <h2 class="mt-6 text-xl font-semibold text-slate-950">Team management</h2>
                    <p class="mt-3 text-sm text-slate-500">Add or update admin and staff access, and manage team roles.</p>
                    <div class="mt-6 flex items-center gap-2 text-sm font-semibold text-blue-600">
                        Open team tools
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3" />
                        </svg>
                    </div>
                </a>
            @endif
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between gap-3">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-[0.24em] text-slate-500">Quick tips</p>
                    <h2 class="mt-3 text-xl font-semibold text-slate-950">Admin workflow</h2>
                </div>
                <div class="rounded-2xl bg-slate-100 p-3 text-slate-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
            <div class="mt-6 space-y-4 text-sm leading-6 text-slate-600">
                <p>Use the left menu to jump between analytics, transactions, content management and team settings.</p>
                <p>Keep article content fresh, review pricing regularly, and use visitor data to prioritize updates.</p>
                <p class="text-slate-500">Tip: click the site logo at the top-left to return to the public homepage.</p>
            </div>
            <a href="{{ route('admin.visits') }}" class="mt-6 inline-flex items-center gap-2 rounded-full bg-slate-950 px-4 py-2 text-sm font-semibold text-white transition hover:bg-slate-800">See visitor analytics</a>
        </div>
    </section>
</div>

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

    // Update data on page load
    updateDashboardData();

    // Update data every 30 seconds
    setInterval(updateDashboardData, 30000);
});
</script>
@endsection
