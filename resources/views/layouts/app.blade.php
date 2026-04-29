<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Resume Builder') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50">

    {{-- Smart Navbar (Changes Based on User Role) --}}
    @include('components.navbar')

    {{-- Page Content --}}
    <div class="min-h-screen py-2">
        {{ $slot ?? '' }}
        @yield('content')
    </div>

    {{-- Footer --}}
    @include('components.footer')

    @stack('scripts')

</body>
</html>
