{{-- Smart Navbar - Displays based on Authentication State and Role --}}

@guest
    {{-- Show Public Navbar for Guests --}}
    @include('components.navbar-public')
@endguest

@auth
    @if(Auth::user()->isAdmin())
        @unless(request()->routeIs('admin.*'))
            @include('components.navbar-admin')
        @endunless
    @else
        {{-- Show User Navbar for Regular Users --}}
        @include('components.navbar-user')
    @endif
@endauth
