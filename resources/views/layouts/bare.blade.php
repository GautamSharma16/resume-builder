<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <!-- Google Tag Manager -->
    <script async src="https://www.googletagmanager.com/gtm.js?id=GTM-NW5H52MZ"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
    </script>
    <!-- End Google Tag Manager -->
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-S11T6PG3GQ"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-S11T6PG3GQ');
    </script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AI Resume Builder | CvBliss')</title>
    <meta name="description" content="@yield('meta_description', 'Create an ATS-friendly resume with CvBliss.')">
    @hasSection('meta_keywords')
        <meta name="keywords" content="@yield('meta_keywords')">
    @endif
    @if($seoShouldIndex ?? false)
        <link rel="canonical" href="{{ $seoCanonicalUrl }}">
    @else
        <meta name="robots" content="noindex, nofollow">
    @endif
    @stack('structured-data')
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

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
