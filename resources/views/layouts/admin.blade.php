<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Resume Builder') }} - Admin</title>

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
    <body class="font-sans antialiased">
        <div class="flex h-screen bg-gray-100">
            <!-- Sidebar -->
            <div class="hidden md:flex md:flex-shrink-0">
                <div class="flex flex-col w-64 bg-white border-r border-gray-200">
                    <!-- Logo -->
                    <div class="flex items-center justify-center h-16 border-b border-gray-200 bg-blue-600">
                        <a href="{{ route('home') }}" class="text-2xl font-bold text-white">
                            Cvbliss
                        </a>
                    </div>

                    <!-- Navigation -->
                    <nav class="admin-sidebar-scroll flex-1 overflow-y-auto px-4 py-6 space-y-2">
                        <a href="{{ route('admin.dashboard') }}" class="@if(request()->routeIs('admin.dashboard')) bg-blue-50 text-blue-600 @else text-gray-700 hover:bg-gray-50 @endif px-4 py-2 rounded-lg font-medium transition block">
                             Dashboard
                        </a>
                        @if(Auth::user()->hasPermission('visits'))
                        <a href="{{ route('admin.visits') }}" class="@if(request()->routeIs('admin.visits')) bg-blue-50 text-blue-600 @else text-gray-700 hover:bg-gray-50 @endif px-4 py-2 rounded-lg font-medium transition block">
                             Visits
                        </a>
                        @endif
                        
                        @if(Auth::user()->hasPermission('transactions') || Auth::user()->hasPermission('pricing'))
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <p class="px-4 py-2 text-xs font-semibold text-gray-600 uppercase">Financials</p>
                            @if(Auth::user()->hasPermission('transactions'))
                            <a href="{{ route('admin.transactions') }}" class="@if(request()->routeIs('admin.transactions')) bg-blue-50 text-blue-600 @else text-gray-700 hover:bg-gray-50 @endif px-4 py-2 rounded-lg font-medium transition block">
                                Transactions
                            </a>
                            @endif
                            @if(Auth::user()->hasPermission('pricing'))
                            <a href="{{ route('admin.payments') }}" class="@if(request()->routeIs('admin.payments')) bg-blue-50 text-blue-600 @else text-gray-700 hover:bg-gray-50 @endif px-4 py-2 rounded-lg font-medium transition block">
                                Pricing Plans
                            </a>
                            @endif
                        </div>
                        @endif
                        @if(Auth::user()->hasPermission('templates') || Auth::user()->hasPermission('articles'))
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <p class="px-4 py-2 text-xs font-semibold text-gray-600 uppercase">Content</p>
                            @if(Auth::user()->hasPermission('templates'))
                            <a href="{{ route('admin.templates.index') }}" class="@if(request()->routeIs('admin.templates.*')) bg-blue-50 text-blue-600 @else text-gray-700 hover:bg-gray-50 @endif px-4 py-2 rounded-lg font-medium transition block">
                                 Templates
                            </a>
                            @endif
                            @if(Auth::user()->hasPermission('articles'))
                            <a href="{{ route('admin.articles.index') }}" class="@if(request()->routeIs('admin.articles.*')) bg-blue-50 text-blue-600 @else text-gray-700 hover:bg-gray-50 @endif px-4 py-2 rounded-lg font-medium transition block">
                                 Articles
                            </a>
                            @endif
                        </div>
                        @endif

                        @if(Auth::user()->hasPermission('team'))
                        <div class="pt-4 mt-4 border-t border-gray-200">
                            <p class="px-4 py-2 text-xs font-semibold text-gray-600 uppercase">Administration</p>
                            <a href="{{ route('admin.users.index') }}" class="@if(request()->routeIs('admin.users.*')) bg-blue-50 text-blue-600 @else text-gray-700 hover:bg-gray-50 @endif px-4 py-2 rounded-lg font-medium transition block">
                                Team Management
                            </a>
                        </div>
                        @endif
                    </nav>

                    <!-- User Menu -->
                    <div class="p-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                {{ substr(Auth::user()->name, 0, 1) }}
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500">
                                    @php
                                        $roleLabels = [
                                            'admin' => 'Administrator',
                                            'developer' => 'Developer',
                                            'seo' => 'SEO Manager',
                                            'article_writer' => 'Writer',
                                            'company' => 'Company Staff',
                                        ];
                                    @endphp
                                    {{ $roleLabels[Auth::user()->role] ?? ucfirst(Auth::user()->role) }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" title="Logout" class="text-gray-400 hover:text-gray-600">
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
                <div class="flex-1 overflow-auto">
                    @yield('content')
                </div>
            </div>
        </div>
    </body>
</html>
