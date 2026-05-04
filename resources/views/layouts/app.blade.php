<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Resume Builder') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&family=DM+Serif+Display:ital@0;1&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --blue:        #2563eb;
            --blue-dark:   #1d4ed8;
            --blue-light:  #eff6ff;
            --navy:        #0b1221;
            --ink:         #1e293b;
            --muted:       #64748b;
            --soft:        #94a3b8;
            --surface:     #f8fafc;
            --surface-2:   #f1f5f9;
            --border:      rgba(0,0,0,0.07);
            --white:       #ffffff;
            
            --font-display: 'DM Serif Display', serif;
            --font-body:    'Bricolage Grotesque', sans-serif;

            --r-md:  12px;
            --r-lg:  18px;
            --r-xl:  28px;
            --r-2xl: 36px;
        }

        body {
            font-family: var(--font-body);
            color: var(--ink);
            background-color: var(--surface);
            -webkit-font-smoothing: antialiased;
        }

        h1, h2, h3, h4, h5, h6, .font-display {
            font-family: var(--font-display);
        }
    </style>
</head>

<body class="bg-gray-50">

    {{-- Smart Navbar (Changes Based on User Role) --}}
    @include('components.navbar')

    {{-- Page Content --}}
    <div class="min-h-screen">
        {{ $slot ?? '' }}
        @yield('content')
    </div>

    {{-- Footer --}}
    @hasSection('footer')
        @yield('footer')
    @else
        @include('components.footer')
    @endif

    @include('components.plan-download-modal')

    @stack('scripts')

</body>
</html>
