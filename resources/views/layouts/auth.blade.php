<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ config('app.name', 'Resume Builder') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

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
        }
        body { font-family: var(--font-body); }
    </style>
</head>
<body class="bg-gray-50">

    <div class="min-h-screen">
        @yield('content')
    </div>

    @stack('scripts')
    
    <!-- Tawk.to Script -->
    <script type="text/javascript">
    if (!['127.0.0.1', 'localhost'].includes(window.location.hostname)) {
    var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
    (function(){
    var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
    s1.async=true;
    s1.src='https://embed.tawk.to/69f9b85c04c2b71c3575813b/1jnrngaim';
    s1.charset='UTF-8';
    s1.setAttribute('crossorigin','*');
    s0.parentNode.insertBefore(s1,s0);
    })();
    }
    </script>
</body>
</html>
