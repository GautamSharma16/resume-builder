<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Resume Builder') }}</title>
<link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Serif+Display:wght@400&display=swap" as="style">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=DM+Serif+Display:wght@400&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Google Analytics -->
    @if(config('services.analytics.google_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.analytics.google_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ config('services.analytics.google_id') }}');
        </script>
    @endif

    <!-- Microsoft Clarity -->
    @if(config('services.analytics.clarity_id'))
        <script async defer type="text/javascript">
            (function(c,l,a,r,i,t,y){
                c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
                t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
                y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
            })(window, document, "clarity", "script", "{{ config('services.analytics.clarity_id') }}");
        </script>
    @endif

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
            --font-body:    'Inter', sans-serif;

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
    @hasSection('navbar')
        @yield('navbar')
    @else
        @include('components.navbar')
    @endif

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

    <!-- Custom Delete Confirmation Modal -->
    <div id="delete-confirm-modal" class="fixed inset-0 z-[1250] hidden items-center justify-center bg-slate-950/70 px-4 py-6 backdrop-blur-sm">
        <div class="modal-fade-in w-full max-w-md overflow-hidden rounded-2xl bg-white p-6 shadow-2xl">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-red-50 text-red-600">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </span>
                <div>
                    <h3 id="delete-modal-title" class="text-lg font-bold text-gray-950">Delete Document</h3>
                    <p id="delete-modal-message" class="text-sm text-gray-500 mt-2">Are you sure you want to delete this document? This action cannot be undone.</p>
                </div>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <button type="button" id="delete-modal-cancel" class="rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:border-gray-300 hover:bg-gray-50">Cancel</button>
                <button type="button" id="delete-modal-confirm" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-red-600/20 transition hover:bg-red-700 hover:shadow-red-600/30">Delete</button>
            </div>
        </div>
    </div>
    <script>
    (() => {
        const modal = document.getElementById('delete-confirm-modal');
        if (!modal) return;

        let activeForm = null;

        const open = (form, titleText, messageText) => {
            activeForm = form;
            document.getElementById('delete-modal-title').textContent = titleText || 'Delete Document';
            document.getElementById('delete-modal-message').textContent = messageText || 'Are you sure you want to delete this document? This action cannot be undone.';
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        };

        const close = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
            activeForm = null;
        };

        document.getElementById('delete-modal-cancel').addEventListener('click', close);
        document.getElementById('delete-modal-confirm').addEventListener('click', () => {
            if (activeForm) {
                activeForm.submit();
            }
            close();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) close();
        });

        document.addEventListener('submit', (event) => {
            const form = event.target;
            if (form.hasAttribute('data-delete-confirm')) {
                event.preventDefault();
                const title = form.getAttribute('data-confirm-title');
                const message = form.getAttribute('data-confirm-message');
                open(form, title, message);
            }
        });
    })();
    </script>

    @stack('scripts')
    
    <!--Start of Tawk.to Script-->
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
    <!--End of Tawk.to Script-->

</body>
</html>
