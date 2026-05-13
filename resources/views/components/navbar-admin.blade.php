@php
    $isAdminArea = request()->routeIs('admin.*');
@endphp

<div class="bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
    <div class="flex items-center">
        @if($isAdminArea)
            <h2 class="text-xl font-semibold text-gray-900">Admin Panel</h2>
        @else
            <a href="{{ route('admin.dashboard') }}" class="text-xl font-semibold text-gray-900 hover:text-blue-600 transition">
                Admin Dashboard
            </a>
        @endif
    </div>

    <x-dropdown align="right" width="56" contentClasses="py-2 bg-white">
        <x-slot name="trigger">
            <button class="inline-flex items-center gap-2 text-gray-700 hover:text-blue-600 transition focus:outline-none">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
                <span class="font-medium">{{ Auth::user()->name }}</span>
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </x-slot>

        <x-slot name="content">
            <div class="px-4 py-2 border-b border-gray-100">
                <p class="text-sm font-medium text-gray-900">{{ Auth::user()->name }}</p>
                <p class="text-xs text-gray-500">Administrator</p>
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
