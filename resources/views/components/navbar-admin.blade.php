@php
    $isAdminArea = request()->routeIs('admin.*');
@endphp

<div class="sticky top-0 z-40 flex min-h-[76px] items-center justify-between border-b border-slate-200/80 bg-white/90 px-4 py-3 shadow-[0_14px_40px_rgba(15,23,42,0.08)] backdrop-blur-xl sm:px-6">
    <div class="flex items-center gap-3">
        <button
            type="button"
            class="md:hidden inline-flex h-11 w-11 items-center justify-center rounded-[14px] border border-slate-200 bg-white text-slate-700 shadow-sm transition duration-200 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100"
            @click="sidebarOpen = true"
            aria-label="Open admin menu">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
        @if($isAdminArea)
            <h2 class="text-xl font-bold tracking-tight text-slate-950 sm:text-2xl">Admin Panel</h2>
        @else
            <a href="{{ route('admin.dashboard') }}" class="text-xl font-bold tracking-tight text-slate-950 transition duration-200 hover:text-blue-700 sm:text-2xl">
                Admin Dashboard
            </a>
        @endif
    </div>

    <x-dropdown align="right" width="56" contentClasses="overflow-hidden rounded-2xl border border-slate-200 bg-white py-2 shadow-[0_22px_50px_rgba(15,23,42,0.16)]">
        <x-slot name="trigger">
            <button class="inline-flex max-w-[240px] items-center gap-3 rounded-[16px] border border-slate-200 bg-white px-3 py-2 text-slate-700 shadow-sm transition duration-200 hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-100 sm:px-3.5 sm:py-2.5">
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 text-sm font-bold text-white shadow-sm">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </span>
                <span class="hidden min-w-0 truncate text-base font-semibold sm:inline">{{ Auth::user()->name }}</span>
                <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </x-slot>

        <x-slot name="content">
            <div class="px-4 py-3 border-b border-slate-100">
                <p class="truncate text-sm font-bold text-slate-900">{{ Auth::user()->name }}</p>
                <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-500">Administrator</p>
            </div>

            @unless($isAdminArea)
                <x-dropdown-link :href="route('admin.dashboard')">
                    Dashboard
                </x-dropdown-link>
            @endunless

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-600 hover:bg-red-50 focus:bg-red-50">
                    Logout
                </x-dropdown-link>
            </form>
        </x-slot>
    </x-dropdown>
</div>
