@extends('layouts.auth')

@section('title', 'Admin Access | CvBliss Staff Portal')

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
        height: clamp(52px, 7vw, 72px);
        width: auto;
        max-width: min(161px, 70vw);
        object-fit: contain;
        filter: brightness(1.2);
    }

    .lp-headline {
        font-family: var(--font-display);
        font-size: 3rem;
        color: #fff;
        line-height: 1.2;
        margin-bottom: 1.5rem;
        margin-top: 4rem;
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
    .card-img { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 1px solid rgba(255,255,255,0.2); }
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
                <img src="{{ asset('logo.webp') }}" alt="CvBliss Logo" class="cvb-logo">
            </a>

            <h1 class="lp-headline">Administrative Control Center</h1>
            <p class="lp-subtext">
                Secure portal for CvBliss staff. Manage platform analytics, user resumes, and system-wide configurations.
            </p>

            <!-- <div class="space-y-6">
                <div class="glass-card" style="transform: translateX(16px);">
                    <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center border border-blue-500/30">
                        <span class="material-symbols-outlined text-blue-400">shield_person</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-white">Staff Verification</span>
                            <span class="card-badge" style="background: rgba(16, 185, 129, 0.2); color: #34d399; border-color: rgba(52, 211, 153, 0.3);">Required</span>
                        </div>
                        <div class="text-sm text-outline">Multi-factor authentication enabled for all admin accounts.</div>
                    </div>
                </div>
            </div> -->
        </div>

        <div class="lp-stats">
            <div>
                <div class="stat-val">Admin</div>
                <div class="stat-label">System Scope</div>
            </div>
            <div class="stat-sep"></div>
            <div>
                <div class="stat-val">Secure</div>
                <div class="stat-label">SSL Encrypted</div>
            </div>
        </div>
    </div>

    {{-- Right Panel --}}
    <div class="lp-right">
        <div class="form-shell">
            
            <div class="form-header">
                <span class="form-label-top">Staff Portal</span>
                <h2 class="form-title">Admin Access</h2>
                <p class="form-subtitle">Sign in to manage users, content, and site settings securely.</p>
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

            <form method="POST" action="{{ route('admin.login.store') }}">
                @csrf
                <input type="hidden" name="role_scope" value="staff">

                <div class="field-group">
                    <label class="field-label" for="email">Admin Email</label>
                    <div class="input-wrapper">
                        <input class="form-input" id="email" name="email" type="email" value="{{ old('email') }}" placeholder="admin@cvbliss.com" required>
                        <span class="material-symbols-outlined input-icon">admin_panel_settings</span>
                    </div>
                </div>

                <div class="field-group">
                    <label class="field-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <input class="form-input" id="password" name="password" type="password" placeholder="••••••••" required>
                        <span class="material-symbols-outlined input-icon">lock</span>
                    </div>
                </div>

                <button class="btn-submit" type="submit">
                    Authenticate Admin
                    <span class="material-symbols-outlined" style="font-size: 20px;">verified_user</span>
                </button>
            </form>

            <div class="footer-cta">
                Return to <a href="{{ route('home') }}">Homepage</a>
            </div>
        </div>
    </div>
</div>
@endsection
