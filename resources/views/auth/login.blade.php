@extends('layouts.auth')

@section('title', 'Sign In | CvBliss - AI Resume Engine')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=Bricolage+Grotesque:wght@300;400;600;700&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

<style>
    /* ─── TOKENS — matched to Stitch design ───────── */
    :root {
        --background: #11131b;
        --surface: #11131b;
        --surface-container: #1d1f27;
        --surface-container-low: #191b23;
        --on-surface: #e1e2ed;
        --on-surface-variant: #c3c6d7;
        --primary: #b4c5ff;
        --primary-container: #2563eb;
        --on-primary-container: #eeefff;
        --outline: #8d90a0;
        --outline-variant: #434655;
        
        --font-display: 'DM Serif Display', serif;
        --font-body: 'Bricolage Grotesque', sans-serif;
    }

    .lp-wrap {
        font-family: var(--font-body);
        min-height: 100vh;
        display: grid;
        grid-template-columns: 1fr 1fr;
        background: var(--background);
        color: var(--on-surface);
        overflow: hidden;
    }

    /* ════════════════════════════════════════════
       LEFT PANEL — Aurora Gradient & Immersive
    ════════════════════════════════════════════ */
    .lp-left {
        background: radial-gradient(circle at top left, #2563eb, transparent 60%),
                    radial-gradient(circle at bottom right, #1e1b4b, transparent 60%),
                    linear-gradient(to bottom, #11131b, #0f172a);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 4rem;
    }

    .grid-overlay {
        position: absolute;
        inset: 0;
        background-image: radial-gradient(circle at 1px 1px, rgba(255,255,255,0.05) 1px, transparent 0);
        background-size: 40px 40px;
        z-index: 0;
    }

    .lp-content { position: relative; z-index: 10; max-width: 480px; }

    .lp-logo {
        display: flex;
        items-center: center;
        gap: 0.75rem;
        margin-bottom: auto;
        text-decoration: none;
    }
    
    .lp-logo img {
        height: 60px;
        width: auto;
        filter: brightness(1.2);
    }

    .lp-headline {
        font-family: var(--font-display);
        font-size: 3rem;
        color: #fff;
        line-height: 1.2;
        margin-bottom: 1.5rem;
        margin-top: 2rem;
    }
    .lp-subtext {
        font-size: 1.15rem;
        color: var(--outline);
        line-height: 1.6;
        margin-bottom: 2.5rem;
    }

    /* — Glass Cards — */
    .glass-card {
        background: rgba(255, 255, 255, 0.03);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        padding: 1.5rem;
        border-radius: 1.25rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        transition: transform 0.3s ease;
    }
    .glass-card:hover { transform: translateX(8px); }
    .card-img { width: 48px; height: 48px; border-radius: 50%; object-cover: cover; border: 1px solid rgba(255,255,255,0.2); }
    .card-badge {
        font-size: 10px;
        background: rgba(37, 99, 235, 0.2);
        color: var(--primary);
        padding: 2px 8px;
        border-radius: 999px;
        border: 1px solid rgba(180, 197, 255, 0.3);
        text-transform: uppercase;
        letter-spacing: 0.1em;
        font-weight: 700;
    }

    /* — Stats — */
    .lp-stats {
        position: relative;
        z-index: 10;
        display: flex;
        gap: 3rem;
        align-items: center;
    }
    .stat-val { font-family: var(--font-display); font-size: 2rem; color: #fff; line-height: 1; }
    .stat-label { font-size: 12px; color: var(--outline); text-transform: uppercase; letter-spacing: 0.05em; margin-top: 4px; font-weight: 600; }
    .stat-sep { width: 1px; height: 40px; background: rgba(255,255,255,0.1); }

    /* ════════════════════════════════════════════
       RIGHT PANEL — Form Section
    ════════════════════════════════════════════ */
    .lp-right {
        background: var(--surface);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
    }
    .form-shell { width: 100%; max-width: 400px; }

    .form-header { text-align: left; margin-bottom: 2rem; }
    .form-label-top {
        display: inline-block;
        font-size: 12px;
        font-weight: 600;
        color: var(--primary-container);
        text-transform: uppercase;
        letter-spacing: 0.2em;
        margin-bottom: 1rem;
    }
    .form-title { font-family: var(--font-display); font-size: 3rem; color: var(--on-surface); line-height: 1.1; margin-bottom: 0.5rem; }
    .form-subtitle { color: var(--outline); font-size: 1rem; }

    /* — Social Auth — */
    .btn-google {
        width: 100%;
        height: 48px;
        background: #fff;
        color: #0f172a;
        font-weight: 600;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        transition: all 0.2s;
        border: 1px solid #e2e8f0;
        text-decoration: none;
    }
    .btn-google:hover { background: #f8fafc; transform: translateY(-1px); }

    .divider {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 1.5rem 0;
        color: var(--outline);
        font-size: 12px;
    }
    .divider::before, .divider::after { content: ""; flex: 1; height: 1px; background: var(--outline-variant); }

    /* — Inputs — */
    .field-group { margin-bottom: 1.5rem; }
    .field-label { display: block; font-size: 12px; font-weight: 600; color: var(--on-surface-variant); margin-bottom: 8px; margin-left: 4px; }
    .input-wrapper { position: relative; }
    .input-icon { position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--outline); transition: color 0.3s; }
    .form-input {
        width: 100%;
        height: 56px;
        background: var(--surface-container);
        border: 1px solid var(--outline-variant);
        border-radius: 12px;
        padding: 0 16px 0 48px;
        color: var(--on-surface);
        transition: all 0.3s;
        font-size: 16px;
    }
    .form-input:focus {
        border-color: var(--primary-container);
        box-shadow: 0 0 0 1px var(--primary-container);
        outline: none;
    }
    .form-input:focus + .input-icon { color: var(--primary-container); }

    .btn-submit {
        width: 100%;
        height: 56px;
        background: var(--primary-container);
        color: var(--on-primary-container);
        font-weight: 600;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        border: none;
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.2);
        cursor: pointer;
    }
    .btn-submit:hover { transform: scale(1.01); }
    .btn-submit:active { transform: scale(0.98); }

    .footer-cta {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid rgba(67, 70, 85, 0.3);
        text-align: center;
        color: var(--outline);
    }
    .footer-cta a { color: var(--primary); font-weight: 600; text-decoration: none; margin-left: 4px; }
    .footer-cta a:hover { text-decoration: underline; }

    /* — AI Indicator — */
    .ai-status {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: 1.5rem;
    }
    .dot-pulse {
        position: relative;
        width: 8px; height: 8px;
        background: var(--primary);
        border-radius: 50%;
    }
    .dot-pulse::after {
        content: "";
        position: absolute;
        inset: 0;
        background: var(--primary);
        border-radius: 50%;
        animation: ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
    }
    @keyframes ping { 75%, 100% { transform: scale(3.5); opacity: 0; } }
    .ai-text { font-size: 11px; font-weight: 700; color: var(--outline); text-transform: uppercase; letter-spacing: 0.15em; }

    @media (max-width: 1024px) {
        .lp-wrap { grid-template-columns: 1fr; }
        .lp-left { display: none; }
    }
</style>

<div class="lp-wrap">
    {{-- Left Panel --}}
    <div class="lp-left">
        <div class="grid-overlay"></div>
        
        <div class="lp-content">
            <a href="{{ route('home') }}" class="lp-logo">
                <img src="{{ asset('Logo.png') }}" alt="CvBliss Logo">
            </a>

            <h1 class="lp-headline">Build resumes that get you hired faster</h1>
            <p class="lp-subtext">
                Leverage industry-leading AI to craft data-driven resumes that bypass ATS filters and land in the hands of top-tier recruiters.
            </p>

            <div class="space-y-6">
                <div class="glass-card" style="transform: translateX(16px);">
                    <img src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=400" class="card-img" alt="Sarah Johnson">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-white">Sarah Johnson</span>
                            <span class="card-badge">Hired</span>
                        </div>
                        <div class="text-sm text-outline">Senior Product Designer, <span class="text-white">ATS Score 94%</span></div>
                    </div>
                </div>
                
                <div class="glass-card" style="transform: translateX(48px); opacity: 0.8;">
                    <img src="https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400" class="card-img" alt="Michael Chen">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-white">Michael Chen</span>
                            <span class="card-badge">Hired</span>
                        </div>
                        <div class="text-sm text-outline">Software Engineering Lead, <span class="text-white">ATS Score 91%</span></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lp-stats">
            <div>
                <div class="stat-val">15k+</div>
                <div class="stat-label">Resumes built</div>
            </div>
            <div class="stat-sep"></div>
            <div>
                <div class="stat-val">98%</div>
                <div class="stat-label">ATS Pass Rate</div>
            </div>
        </div>
    </div>

    {{-- Right Panel --}}
    <div class="lp-right">
        <div class="form-shell">
            
            @php $activeTab = $activeTab ?? 'login'; @endphp

            <div class="form-header">
                <span class="form-label-top">
                    @if($activeTab === 'login') User Login @elseif($activeTab === 'register') Create Account @else Reset Password @endif
                </span>
                <h2 class="form-title">@if($activeTab === 'login') Welcome back @elseif($activeTab === 'register') Get Started @else Forgot Password? @endif</h2>
                <p class="form-subtitle">@if($activeTab === 'login') Access your professional dashboard and AI tools. @elseif($activeTab === 'register') Join 15k+ professionals building their career. @else Enter your email to receive reset instructions. @endif</p>
            </div>

            {{-- Alerts --}}
            @if(session('status'))
                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 14px;">
                    {{ session('status') }}
                </div>
            @endif
            @if($errors->any())
                <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-size: 14px;">
                    {{ $errors->first() }}
                </div>
            @endif

            @if($activeTab === 'login' || $activeTab === 'register')
                <a href="{{ route('auth.google') }}" class="btn-google">
                    <svg class="w-5 h-5" viewBox="0 0 24 24">
                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"></path>
                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"></path>
                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"></path>
                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"></path>
                    </svg>
                    Continue with Google
                </a>

                <div class="divider">or continue with email</div>
            @endif

            @if($activeTab === 'login')
                <form method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <input type="hidden" name="role_scope" value="user">

                    <div class="field-group">
                        <label class="field-label" for="email">Email address</label>
                        <div class="input-wrapper">
                            <input class="form-input" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="name@company.com" required>
                            <span class="material-symbols-outlined input-icon">mail</span>
                        </div>
                    </div>

                    <div class="field-group">
                        <div class="flex justify-between items-center" style="display:flex; justify-content:space-between; margin-bottom:8px; padding:0 4px;">
                            <label class="field-label" style="margin-bottom:0;" for="password">Password</label>
                            <a href="{{ route('password.request') }}" style="color:var(--primary); font-size:12px; font-weight:600; text-decoration:none;">Forgot?</a>
                        </div>
                        <div class="input-wrapper">
                            <input class="form-input" id="password" name="password" type="password" placeholder="••••••••" required>
                            <span class="material-symbols-outlined input-icon">lock</span>
                        </div>
                    </div>

                    <div class="flex items-center gap-3" style="display:flex; align-items:center; gap:12px; margin-bottom:1.5rem; padding:0 4px;">
                        <input type="checkbox" id="remember" style="width:18px; height:18px; border-radius:4px; border:1px solid var(--outline-variant); background:var(--surface-container);">
                        <label for="remember" style="font-size:14px; color:var(--outline);">Keep me logged in</label>
                    </div>

                    <button class="btn-submit" type="submit">
                        Sign in to CvBliss
                        <span class="material-symbols-outlined" style="font-size: 20px;">arrow_forward</span>
                    </button>
                </form>
            @elseif($activeTab === 'register')
                <form method="POST" action="{{ route('register.store') }}">
                    @csrf
                    <div class="field-group">
                        <label class="field-label" for="name">Full Name</label>
                        <div class="input-wrapper">
                            <input class="form-input" id="name" name="name" type="text" value="{{ old('name') }}" placeholder="John Doe" required>
                            <span class="material-symbols-outlined input-icon">person</span>
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="email">Email address</label>
                        <div class="input-wrapper">
                            <input class="form-input" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="name@company.com" required>
                            <span class="material-symbols-outlined input-icon">mail</span>
                        </div>
                    </div>
                    <div class="field-group">
                        <label class="field-label" for="password">Password</label>
                        <div class="input-wrapper">
                            <input class="form-input" id="password" name="password" type="password" placeholder="Min. 8 characters" required>
                            <span class="material-symbols-outlined input-icon">lock</span>
                        </div>
                    </div>
                    <button class="btn-submit" type="submit">Create Account</button>
                </form>
            @else
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="field-group">
                        <label class="field-label" for="email">Email address</label>
                        <div class="input-wrapper">
                            <input class="form-input" id="email" name="email" type="email" placeholder="name@company.com" required>
                            <span class="material-symbols-outlined input-icon">mail</span>
                        </div>
                    </div>
                    <button class="btn-submit" type="submit">Send Reset Link</button>
                </form>
            @endif

            <div class="footer-cta">
                @if($activeTab === 'login')
                    Don't have an account? <a href="{{ route('register') }}">Create free account</a>
                @else
                    Already have an account? <a href="{{ route('login') }}">Sign in instead</a>
                @endif
            </div>

          
        </div>
    </div>
</div>
@endsection