<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'Cvbliss'))</title>

    {{-- Prevent any scrollbars at the HTML level for the full-screen builder --}}
    <style>
        html, body { margin: 0; padding: 0; height: 100%; overflow: hidden; }
    </style>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    @yield('content')
    @stack('scripts')
</body>
</html>