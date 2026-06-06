<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Resume Builder') }} - Admin</title>
        <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <style>
            .admin-sidebar-scroll {
                scrollbar-width: none;
                -ms-overflow-style: none;
            }

            .admin-sidebar-scroll::-webkit-scrollbar {
                display: none;
            }
        </style>
    </head>
    <body class="font-sans antialiased" x-data="{ sidebarOpen: false }">
        <div class="flex h-screen bg-gray-100 overflow-hidden">
            <!-- Mobile Sidebar Overlay -->
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 md:hidden" 
                 @click="sidebarOpen = false"></div>

            <!-- Mobile Sidebar -->
            <div x-show="sidebarOpen"
                 x-transition:enter="transition ease-in-out duration-300 transform"
                 x-transition:enter-start="-translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition ease-in-out duration-300 transform"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="-translate-x-full"
                 class="fixed inset-y-0 left-0 z-50 flex w-[min(20rem,calc(100vw-2rem))] flex-col border-r border-gray-200 bg-white shadow-[24px_0_60px_rgba(15,23,42,0.18)] md:hidden">
                
                <!-- Mobile Logo Area -->
                <div class="flex h-20 shrink-0 items-center justify-between border-b border-slate-100 px-5">
                    <a href="{{ route('home') }}" class="flex min-w-0 items-center">
                        <img src="{{ asset('logo.webp') }}" alt="Cvbliss Logo" class="cvb-logo cvb-logo-drawer">
                    </a>
                    <button @click="sidebarOpen = false" class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-[14px] text-slate-400 transition duration-200 hover:bg-slate-50 hover:text-slate-700 focus:outline-none focus:ring-4 focus:ring-blue-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                <!-- Mobile Nav links -->
                @php $isMobile = true; @endphp
                @include('admin.partials.sidebar-nav')

                <div class="shrink-0 border-t border-slate-100 bg-slate-50/70 p-4">
                    <div class="flex items-center gap-3 rounded-2xl bg-white p-3 shadow-sm ring-1 ring-slate-200/70">
                        <span class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-sm font-bold text-white shadow-md">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-slate-900">{{ Auth::user()->name }}</p>
                            <p class="truncate text-[11px] font-semibold uppercase tracking-[0.12em] text-slate-500">
                                @php
                                    $mobileRoleLabels = [
                                        'admin' => 'Admin',
                                        'team' => 'Team',
                                        'sales' => 'Sales',
                                        'developer' => 'Developer',
                                        'dev' => 'Developer',
                                        'seo' => 'SEO Manager',
                                        'article_writer' => 'Content Strategist',
                                        'article' => 'Content Strategist',
                                    ];
                                @endphp
                                {{ $mobileRoleLabels[Auth::user()->role] ?? ucfirst(Auth::user()->role) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Static Sidebar for Desktop -->
            <div class="hidden md:flex md:flex-shrink-0">
                <div class="flex flex-col w-72 bg-white border-r border-slate-200">
                    <!-- Logo -->
                    <div class="flex items-center px-8 h-20 border-b border-slate-100 shrink-0">
                        <a href="{{ route('home') }}" class="flex items-center">
                            <img src="{{ asset('logo.webp') }}" alt="Cvbliss Logo" class="cvb-logo cvb-logo-admin-sidebar">
                        </a>
                    </div>

                    <!-- Navigation -->
                    @php $isMobile = false; @endphp
                    @include('admin.partials.sidebar-nav')

                    <!-- User Menu (Desktop) -->
                    <div class="p-6 border-t border-slate-100 bg-slate-50/50 shrink-0">
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                <div class="w-11 h-11 bg-gradient-to-tr from-blue-600 to-blue-400 rounded-xl flex items-center justify-center text-white font-bold shadow-md">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 border-white rounded-full"></div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-slate-900 truncate">{{ Auth::user()->name }}</p>
                                <p class="text-[11px] font-medium text-slate-500 uppercase tracking-wider">
                                    @php
                                        $roleLabels = [
                                            'admin' => 'Admin',
                                            'team' => 'Team',
                                            'sales' => 'Sales',
                                            'developer' => 'Developer',
                                            'dev' => 'Developer',
                                            'seo' => 'SEO Manager',
                                            'article_writer' => 'Content Strategist',
                                            'article' => 'Content Strategist',
                                        ];
                                    @endphp
                                    {{ $roleLabels[Auth::user()->role] ?? ucfirst(Auth::user()->role) }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" title="Logout" class="p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="flex-1 flex flex-col overflow-hidden">
                <!-- Top Bar -->
                @include('components.navbar-admin')

                <!-- Page Content -->
                <div class="flex-1 overflow-auto bg-gray-100">
                    <main class="p-4 md:p-8">
                        @yield('content')
                    </main>
                </div>
            </div>
        </div>
    </body>
</html>
