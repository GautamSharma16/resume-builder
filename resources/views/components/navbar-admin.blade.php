@php
    $isAdminArea = request()->routeIs('admin.*');
@endphp

<div class="bg-white border-b border-slate-100 h-20 px-8 flex items-center justify-between shrink-0">
    <div class="flex items-center gap-6">
        <!-- Sidebar Toggle (Mobile Only) -->
        <button @click="sidebarOpen = true" class="md:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-50 transition-colors focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
        </button>
     
        <div class="hidden md:flex items-center gap-2">
            <div class="w-1 h-6 bg-blue-600 rounded-full opacity-30"></div>
            @if($isAdminArea)
                <h2 class="text-sm font-bold text-slate-500 uppercase tracking-widest">
                    {{ request()->routeIs('admin.dashboard') ? 'Control Center' : 'Operations' }}
                </h2>
            @else
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-slate-500 hover:text-blue-600 transition tracking-widest uppercase">
                    Back to Control Center
                </a>
            @endif
        </div>
    </div>

    <div class="flex items-center gap-4">
       

        <x-dropdown align="right" width="56" contentClasses="py-2 bg-white rounded-2xl shadow-2xl ring-1 ring-slate-100 mt-2">
            <x-slot name="trigger">
                <button class="flex items-center gap-3 p-1.5 pr-4 rounded-2xl bg-slate-50 hover:bg-slate-100 transition-all duration-200 focus:outline-none group">
                    <div class="w-8 h-8 bg-white rounded-xl flex items-center justify-center text-blue-600 font-bold border border-slate-200 group-hover:scale-105 transition-transform">
                        {{ substr(Auth::user()->name, 0, 1) }}
                    </div>
                    <span class="text-sm font-bold text-slate-700">{{ Auth::user()->name }}</span>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
            </x-slot>

            <x-slot name="content">
                <div class="px-5 py-3 border-b border-slate-50">
                    <p class="text-sm font-bold text-slate-900">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-0.5">
                        {{ Auth::user()->role === 'admin' ? 'Administrator' : 'Staff Member' }}
                    </p>
                </div>

                @unless($isAdminArea)
                    <x-dropdown-link :href="route('admin.dashboard')" class="text-slate-600 hover:text-blue-600 font-medium">
                        Admin Dashboard
                    </x-dropdown-link>
                @endunless

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-rose-600 hover:text-rose-700 hover:bg-rose-50 font-bold">
                        Logout Session
                    </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
</div>
