<?php $__env->startSection('title', 'Cvbliss - Build a Resume That Commands Attention'); ?>

<?php $__env->startSection('content'); ?>

<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,500;12..96,600;12..96,700;12..96,800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">

<style>
    /* ─── TOKENS ─────────────────────────────────────────────── */
    :root {
        --blue:        #2563eb;
        --blue-dark:   #1d4ed8;
        --blue-light:  #eff6ff;
        --blue-glow:   rgba(37,99,235,0.15);
        --navy:        #0b1221;
        --ink:         #1e293b;
        --muted:       #64748b;
        --soft:        #94a3b8;
        --surface:     #f8fafc;
        --surface-2:   #f1f5f9;
        --border:      rgba(0,0,0,0.07);
        --white:       #ffffff;
        --gold:        #f59e0b;
        --gold-light:  #fef3c7;
        --green:       #10b981;
        --green-light: #d1fae5;
        --purple:      #8b5cf6;
        --purple-light:#ede9fe;
        --pink:        #ec4899;
        --pink-light:  #fdf2f8;

        --font-display: 'DM Serif Display', serif;
        --font-body:    'Bricolage Grotesque', sans-serif;

        --r-sm:  6px;
        --r-md:  12px;
        --r-lg:  18px;
        --r-xl:  28px;
        --r-2xl: 36px;
        --r-full: 999px;

        --ease-spring: cubic-bezier(0.175, 0.885, 0.32, 1.275);
        --ease-out: cubic-bezier(0.25, 0.46, 0.45, 0.94);
        --ease-bounce: cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: var(--font-body);
        font-size: 1rem;
        color: var(--ink);
        background: var(--white);
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
        overflow-x: hidden;
    }

    /* ─── KEYFRAMES ──────────────────────────────────────────── */
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(40px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to   { opacity: 1; }
    }
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.95); }
        to   { opacity: 1; transform: scale(1); }
    }
    @keyframes floatY {
        0%, 100% { transform: translateY(0); }
        50%       { transform: translateY(-12px); }
    }
    @keyframes floatX {
        0%, 100% { transform: translateX(0); }
        50%       { transform: translateX(-8px); }
    }
    @keyframes shimmer {
        0%   { background-position: -400px 0; }
        100% { background-position: 400px 0; }
    }
    @keyframes pulse-ring {
        0%   { box-shadow: 0 0 0 0 rgba(37,99,235,0.4); }
        70%  { box-shadow: 0 0 0 15px rgba(37,99,235,0); }
        100% { box-shadow: 0 0 0 0 rgba(37,99,235,0); }
    }
    @keyframes spin-slow {
        from { transform: rotate(0deg); }
        to   { transform: rotate(360deg); }
    }
    @keyframes spin-reverse {
        from { transform: rotate(360deg); }
        to   { transform: rotate(0deg); }
    }
    @keyframes badge-slide {
        from { opacity: 0; transform: translateX(-30px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes gradient-shift {
        0%, 100% { background-position: 0% 50%; }
        50%       { background-position: 100% 50%; }
    }
    @keyframes glow-pulse {
        0%, 100% { opacity: 0.4; }
        50%       { opacity: 0.8; }
    }
    @keyframes slideInLeft {
        from { opacity: 0; transform: translateX(-60px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(60px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes zoomIn {
        from { opacity: 0; transform: scale(0.8); }
        to   { opacity: 1; transform: scale(1); }
    }
    @keyframes borderGlow {
        0%, 100% { border-color: rgba(37,99,235,0.2); box-shadow: 0 0 0 0 rgba(37,99,235,0.1); }
        50%       { border-color: rgba(37,99,235,0.5); box-shadow: 0 0 20px rgba(37,99,235,0.2); }
    }

    /* ─── SHARED ─────────────────────────────────────────────── */
    .section-label {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--blue);
        margin-bottom: 0.8rem;
        background: var(--blue-light);
        padding: 0.3rem 1rem 0.3rem 0.8rem;
        border-radius: var(--r-full);
    }
    .section-label::before {
        content: '';
        display: block;
        width: 8px;
        height: 8px;
        background: var(--blue);
        border-radius: 50%;
        animation: pulse-ring 2s infinite;
    }
    .section-heading {
        font-family: var(--font-display);
        font-size: clamp(2rem, 4vw, 3.2rem);
        font-weight: 400;
        color: var(--navy);
        line-height: 1.2;
    }
    .section-heading em {
        font-style: italic;
        background: linear-gradient(135deg, var(--blue), var(--purple));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    .btn-primary {
        display: inline-flex; align-items: center; gap: 0.6rem;
        background: linear-gradient(135deg, var(--blue), var(--blue-dark));
        color: #fff;
        padding: 0.9rem 2rem;
        border-radius: var(--r-full);
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s var(--ease-spring);
        box-shadow: 0 4px 20px rgba(37,99,235,0.35);
        position: relative;
        overflow: hidden;
        border: none;
        cursor: pointer;
    }
    .btn-primary::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transform: translateX(-100%);
        transition: transform 0.6s;
    }
    .btn-primary:hover::before { transform: translateX(100%); }
    .btn-primary:hover { 
        background: linear-gradient(135deg, var(--blue-dark), var(--blue));
        transform: translateY(-3px); 
        box-shadow: 0 8px 30px rgba(37,99,235,0.5); 
    }
    .btn-primary:active { transform: translateY(0); }

    .btn-outline {
        display: inline-flex; align-items: center; gap: 0.6rem;
        background: transparent;
        color: var(--ink);
        padding: 0.9rem 2rem;
        border-radius: var(--r-full);
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        border: 2px solid rgba(0,0,0,0.1);
        transition: all 0.3s var(--ease-spring);
        cursor: pointer;
    }
    .btn-outline:hover { 
        border-color: var(--blue); 
        background: var(--blue-light); 
        color: var(--blue); 
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(37,99,235,0.1);
    }

    .btn-secondary {
        display: inline-flex; align-items: center; gap: 0.5rem;
        background: var(--white);
        color: var(--blue);
        padding: 0.7rem 1.5rem;
        border-radius: var(--r-full);
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s var(--ease-spring);
    }
    .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        color: var(--blue-dark);
    }

    /* ─── NOISE TEXTURE OVERLAY ──────────────────────────────── */
    .noise-overlay {
        position: fixed; inset: 0; pointer-events: none; z-index: 9999; opacity: 0.02;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        background-size: 200px;
    }

    /* ─── HERO ───────────────────────────────────────────────── */
    .hero {
        min-height: 90vh;
        display: grid;
        grid-template-columns: 1fr 0.9fr;
        align-items: center;
        gap: 4rem;
        padding: 3rem 8% 2rem;
        background: linear-gradient(135deg, #ffffff 0%, #fafcff 100%);
        position: relative;
        overflow: hidden;
    }

    /* Animated gradient orbs */
    .hero-orb-1 {
        position: absolute;
        width: 500px; height: 500px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(37,99,235,0.08), transparent 70%);
        top: -200px; left: -150px;
        animation: spin-slow 25s linear infinite;
        pointer-events: none;
    }
    .hero-orb-2 {
        position: absolute;
        width: 400px; height: 400px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(139,92,246,0.06), transparent 70%);
        bottom: -150px; right: -100px;
        animation: spin-reverse 30s linear infinite;
        pointer-events: none;
    }
    .hero-orb-3 {
        position: absolute;
        width: 300px; height: 300px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(236,72,153,0.05), transparent 70%);
        top: 40%; right: 20%;
        animation: glow-pulse 4s ease-in-out infinite;
        pointer-events: none;
    }

    /* Dot-grid background */
    .hero-grid {
        position: absolute;
        inset: 0;
        background-image: 
            linear-gradient(rgba(37,99,235,0.03) 1px, transparent 1px),
            linear-gradient(90deg, rgba(37,99,235,0.03) 1px, transparent 1px);
        background-size: 40px 40px;
        pointer-events: none;
        mask-image: radial-gradient(ellipse 80% 80% at 70% 50%, black 30%, transparent 90%);
    }

    .hero-content {
        max-width: 600px;
        position: relative;
        z-index: 2;
    }

    .hero-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        background: rgba(37,99,235,0.1);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(37,99,235,0.2);
        border-radius: var(--r-full);
        padding: 0.5rem 1.2rem;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--blue);
        margin-bottom: 2rem;
        animation: badge-slide 0.6s var(--ease-spring) both;
        animation-delay: 0.1s;
    }
    .hero-badge-pill .dot {
        width: 8px; height: 8px;
        background: var(--blue);
        border-radius: 50%;
        animation: pulse-ring 2s infinite;
    }
    .hero-badge-pill .badge-new {
        background: var(--blue);
        color: white;
        padding: 0.2rem 0.6rem;
        border-radius: var(--r-full);
        font-size: 0.6rem;
        margin-left: 0.3rem;
    }

    .hero-headline {
        font-family: var(--font-display);
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 400;
        color: var(--navy);
        line-height: 1.1;
        margin-bottom: 1.5rem;
        animation: fadeUp 0.7s var(--ease-out) 0.2s both;
    }
    .hero-headline .gradient-text {
        background: linear-gradient(135deg, var(--blue), var(--purple), var(--pink));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        animation: gradient-shift 6s ease infinite;
        background-size: 200% 200%;
    }

    .hero-sub {
        font-size: 1.1rem;
        color: var(--muted);
        line-height: 1.6;
        margin-bottom: 2rem;
        animation: fadeUp 0.7s var(--ease-out) 0.35s both;
    }

    .hero-ctas {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2.5rem;
        animation: fadeUp 0.7s var(--ease-out) 0.45s both;
    }

    .hero-trust {
        display: flex;
        flex-wrap: wrap;
        gap: 1.8rem;
        margin-bottom: 2rem;
        animation: fadeUp 0.7s var(--ease-out) 0.55s both;
    }
    .hero-trust-item {
        display: flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.85rem;
        color: var(--muted);
        font-weight: 500;
    }
    .hero-trust-item .check {
        width: 20px; height: 20px;
        background: var(--green-light);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .hero-trust-item .check svg { color: var(--green); width: 12px; }

    .hero-social-proof {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border);
        animation: fadeUp 0.7s var(--ease-out) 0.65s both;
    }
    .avatar-stack { display: flex; }
    .avatar-stack .av {
        width: 38px; height: 38px;
        border-radius: 50%;
        border: 3px solid white;
        margin-left: -12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 0.65rem;
        font-weight: 700;
        color: var(--blue);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: transform 0.2s var(--ease-spring);
    }
    .avatar-stack .av:hover { transform: translateY(-3px); z-index: 10; }
    .avatar-stack .av:first-child { margin-left: 0; }
    .stars-row { color: var(--gold); font-size: 0.8rem; letter-spacing: 0.08em; margin-bottom: 4px; }
    .social-proof-label { font-size: 0.8rem; color: var(--muted); }
    .social-proof-label strong { color: var(--ink); font-weight: 700; }

    /* ─── RESUME PREVIEW ─────────────────────────────────────── */
    .resume-preview-wrap {
        position: relative;
        z-index: 2;
        animation: slideInRight 0.8s var(--ease-spring) 0.2s both;
    }

    .resume-preview {
        position: relative;
        max-width: 400px;
        margin: 0 auto;
        transition: transform 0.4s var(--ease-spring);
    }

    .resume-preview-card {
        background: white;
        border-radius: 32px;
        padding: 12px;
        box-shadow: 
            0 30px 60px rgba(0,0,0,0.15),
            0 0 0 1px rgba(0,0,0,0.05);
        transition: all 0.4s var(--ease-spring);
        position: relative;
    }
    .resume-preview-card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 40px 80px rgba(0,0,0,0.2);
    }

    .resume-preview-card img {
        width: 100%;
        height: auto;
        display: block;
        border-radius: 24px;
        object-fit: contain;
        background: #fff;
    }

    /* Decorative ring */
    .preview-ring {
        position: absolute;
        inset: -25px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--blue), var(--purple), transparent);
        opacity: 0.15;
        z-index: -1;
        animation: spin-slow 20s linear infinite;
    }

    /* Floating chips */
    .float-chip {
        position: absolute;
        background: white;
        border-radius: var(--r-full);
        padding: 0.6rem 1.2rem;
        display: flex;
        align-items: center;
        gap: 0.7rem;
        box-shadow: 0 12px 40px rgba(0,0,0,0.15), 0 0 0 1px rgba(0,0,0,0.04);
        font-size: 0.75rem;
        font-weight: 600;
        z-index: 4;
        transition: all 0.3s var(--ease-spring);
        backdrop-filter: blur(8px);
        background: rgba(255,255,255,0.95);
    }
    .float-chip:hover { transform: scale(1.08) translateY(-3px); }

    .chip-ats {
        top: 20px; right: -30px;
        color: var(--green);
        animation: badge-slide 0.6s var(--ease-spring) 0.9s both;
    }
    .chip-ats .chip-icon {
        width: 32px; height: 32px;
        background: var(--green-light);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }
    .chip-ats .score-number {
        font-family: var(--font-display);
        font-size: 1.6rem;
        line-height: 1;
        color: var(--green);
        font-weight: 600;
    }

    .chip-ai {
        bottom: 40px; left: -35px;
        color: var(--blue);
        animation: badge-slide 0.6s var(--ease-spring) 1.1s both;
    }
    .chip-ai .chip-icon {
        width: 32px; height: 32px;
        background: var(--blue-light);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
    }

    /* ─── STATS BAND ─────────────────────────────────────────── */
    .stats-band {
        background: linear-gradient(135deg, var(--navy), #0f172a);
        padding: 3rem 8%;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        position: relative;
        overflow: hidden;
    }
    .stats-band::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(37,99,235,0.2), transparent 70%);
        pointer-events: none;
    }
    .stats-band::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
        background-size: 30px 30px;
        pointer-events: none;
    }
    .stat-item {
        text-align: center;
        padding: 1rem;
        position: relative;
        backdrop-filter: blur(10px);
        border-radius: var(--r-lg);
        transition: transform 0.3s var(--ease-spring);
    }
    .stat-item:hover { transform: translateY(-5px); }
    .stat-item:not(:last-child)::after {
        content: '';
        position: absolute;
        right: -1rem; top: 20%; bottom: 20%;
        width: 1px;
        background: linear-gradient(to bottom, transparent, rgba(255,255,255,0.2), transparent);
    }
    .stat-number {
        font-family: var(--font-display);
        font-size: 2.8rem;
        color: white;
        line-height: 1;
        margin-bottom: 0.5rem;
    }
    .stat-number span { 
        background: linear-gradient(135deg, var(--blue), var(--purple));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    .stat-label { 
        font-size: 0.8rem; 
        color: rgba(255,255,255,0.55); 
        font-weight: 500;
        letter-spacing: 0.03em;
    }

    /* ─── FEATURES ───────────────────────────────────────────── */
    .features-strip {
        background: linear-gradient(180deg, var(--white) 0%, var(--surface) 100%);
        padding: 6rem 8%;
        position: relative;
        overflow: hidden;
    }
    .features-strip-header { 
        max-width: 550px; 
        margin: 0 auto 4rem; 
        text-align: center; 
    }
    .features-strip-header p { 
        color: var(--muted); 
        margin-top: 1rem; 
        font-size: 1rem; 
    }

    .features-grid {
        display: grid;
        grid-template-columns: repeat(6, 1fr);
        gap: 1.5rem;
    }
    .feature-item {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: var(--r-xl);
        padding: 1.8rem 1rem 1.5rem;
        text-align: center;
        transition: all 0.4s var(--ease-spring);
        opacity: 0;
        transform: translateY(30px);
        cursor: default;
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .feature-item::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--blue), var(--purple), var(--pink));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s var(--ease-spring);
    }
    .feature-item:hover { 
        transform: translateY(-8px); 
        box-shadow: 0 20px 40px rgba(0,0,0,0.08); 
        border-color: rgba(37,99,235,0.2);
    }
    .feature-item:hover::before { transform: scaleX(1); }
    .feature-item.visible { opacity: 1; transform: translateY(0); }

    .feature-icon {
        width: 56px; height: 56px;
        border-radius: 20px;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1rem;
        transition: all 0.3s var(--ease-spring);
    }
    .feature-item:hover .feature-icon { 
        transform: scale(1.1) rotate(-5deg); 
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .feature-name { 
        font-size: 0.9rem; 
        font-weight: 800; 
        margin-bottom: 0.5rem; 
        color: var(--navy); 
    }
    .feature-desc { 
        font-size: 0.7rem; 
        color: var(--muted); 
        line-height: 1.5; 
    }

    /* ─── TEMPLATES ──────────────────────────────────────────── */
    .templates-section {
        padding: 6rem 8%;
        background: var(--white);
        overflow: hidden;
    }
    .templates-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 3rem;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .templates-header a { 
        color: var(--blue); 
        font-size: 0.85rem; 
        font-weight: 700; 
        text-decoration: none; 
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        transition: gap 0.3s;
    }
    .templates-header a:hover { gap: 0.6rem; text-decoration: none; }

    .templates-scroller {
    display: flex;
    gap: 1.5rem;
    overflow-x: auto;
    padding-bottom: 1.5rem;

    /* Hide scrollbar */
    scrollbar-width: none;      /* Firefox */
    -ms-overflow-style: none;   /* IE & Edge */
}

/* Chrome, Safari */
.templates-scroller::-webkit-scrollbar {
    display: none;
}

.template-card {
    flex-shrink: 0;
    width: 180px;
    border-radius: var(--r-xl);
    border: 2px solid transparent;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.4s var(--ease-spring);
    background: white;
    box-shadow: 0 4px 20px rgba(0,0,0,0.06);
}

.template-card:hover { 
    transform: translateY(-8px); 
    box-shadow: 0 20px 40px rgba(0,0,0,0.12); 
}

.template-card.active { 
    border-color: var(--blue); 
    box-shadow: 0 8px 30px rgba(37,99,235,0.25);
}
    .template-thumb {
        background: linear-gradient(160deg, #f8fafc, #f1f5f9);
        height: 200px;
        padding: 1rem;
        position: relative;
        overflow: hidden;
    }
    .template-thumb::after {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(180deg, transparent 60%, rgba(0,0,0,0.03));
    }
    .template-mini-header { 
        height: 28px; 
        border-radius: 8px; 
        margin-bottom: 12px; 
    }
    .template-mini-line { 
        height: 6px; 
        background: #e2e8f0; 
        border-radius: 3px; 
        margin-bottom: 8px; 
    }
    .template-mini-line.short { width: 65%; }
    .template-mini-line.xshort { width: 45%; }

    .template-label {
        padding: 0.8rem;
        text-align: center;
        font-size: 0.8rem;
        font-weight: 800;
        border-top: 1px solid rgba(0,0,0,0.05);
        color: var(--ink);
        letter-spacing: 0.03em;
    }

    /* ─── PRICING ─────────────────────────────────────────────── */
    .pricing-section {
        padding: 6rem 8%;
        background: linear-gradient(135deg, #0b1221 0%, #0f172a 100%);
        position: relative;
        overflow: hidden;
    }
    .pricing-glow {
        position: absolute;
        width: 600px; height: 600px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(37,99,235,0.15), transparent 70%);
        top: -200px; left: -200px;
        pointer-events: none;
    }
    .pricing-glow-2 {
        position: absolute;
        width: 500px; height: 500px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(139,92,246,0.1), transparent 70%);
        bottom: -150px; right: -150px;
        pointer-events: none;
    }

    .pricing-header {
        text-align: center;
        max-width: 600px;
        margin: 0 auto 4rem;
        position: relative;
        z-index: 2;
    }
    .pricing-header .section-label { 
        background: rgba(255,255,255,0.1); 
        color: var(--blue); 
    }
    .pricing-header .section-label::before { background: var(--blue); }
    .pricing-header h2 { color: white; }
    .pricing-header p { color: rgba(255,255,255,0.5); margin-top: 1rem; }

    .pricing-cards {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 2rem;
        max-width: 1100px;
        margin: 0 auto;
        position: relative;
        z-index: 2;
    }

    .plan-card {
        background: rgba(255,255,255,0.03);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.08);
        border-radius: var(--r-2xl);
        padding: 2.5rem 2rem;
        position: relative;
        transition: all 0.4s var(--ease-spring);
        opacity: 0;
        transform: translateY(30px);
    }
    .plan-card.visible { opacity: 1; transform: translateY(0); }
    .plan-card:hover { 
        border-color: rgba(255,255,255,0.2); 
        transform: translateY(-10px); 
        box-shadow: 0 30px 60px rgba(0,0,0,0.4);
    }

    .plan-card.featured {
        background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.04));
        border: 1px solid rgba(37,99,235,0.4);
        transform: scale(1.02);
    }
    .plan-card.featured.visible { transform: scale(1.02); }
    .plan-card.featured:hover { transform: scale(1.02) translateY(-10px); }

    .plan-badge {
        position: absolute;
        top: -12px; left: 50%;
        transform: translateX(-50%);
        background: linear-gradient(90deg, var(--gold), #f97316);
        color: white;
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        padding: 0.35rem 1.2rem;
        border-radius: var(--r-full);
        white-space: nowrap;
        box-shadow: 0 4px 15px rgba(245,158,11,0.4);
    }

    .plan-icon-wrap {
        width: 50px; height: 50px;
        border-radius: var(--r-lg);
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 1.5rem;
        transition: transform 0.3s var(--ease-spring);
    }
    .plan-card:hover .plan-icon-wrap { transform: scale(1.05); }
    .plan-icon-wrap.light { background: rgba(37,99,235,0.15); }
    .plan-icon-wrap.dark  { background: rgba(255,255,255,0.08); }

    .plan-name {
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.45);
        margin-bottom: 0.5rem;
    }
    .plan-card.featured .plan-name { color: rgba(255,255,255,0.6); }

    .plan-price {
        font-family: var(--font-display);
        font-size: 3.5rem;
        font-weight: 400;
        color: white;
        line-height: 1;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: flex-start;
        gap: 0.2rem;
    }
    .plan-card.featured .plan-price { color: white; }
    .plan-price .currency {
        font-family: var(--font-body);
        font-size: 1.3rem;
        font-weight: 600;
        margin-top: 0.5rem;
        opacity: 0.7;
    }
    .plan-period {
        font-size: 0.8rem;
        color: rgba(255,255,255,0.4);
        margin-bottom: 1.8rem;
        font-weight: 500;
    }

    .plan-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        margin-bottom: 1.8rem;
    }

    .plan-features { list-style: none; margin-bottom: 2rem; }
    .plan-features li {
        display: flex;
        align-items: center;
        gap: 0.8rem;
        font-size: 0.85rem;
        color: rgba(255,255,255,0.6);
        padding: 0.6rem 0;
        font-weight: 500;
    }
    .plan-features li .pf-icon {
        width: 20px; height: 20px;
        border-radius: 50%;
        background: rgba(16,185,129,0.15);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .plan-features li .pf-icon svg { color: var(--green); width: 11px; }

    .btn-plan {
        display: block;
        text-align: center;
        padding: 0.9rem;
        border-radius: var(--r-full);
        font-size: 0.85rem;
        font-weight: 800;
        text-decoration: none;
        transition: all 0.3s var(--ease-spring);
        letter-spacing: 0.02em;
    }
    .btn-plan-dark {
        background: rgba(255,255,255,0.08);
        color: white;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .btn-plan-dark:hover { 
        background: rgba(255,255,255,0.15); 
        transform: translateY(-3px);
        border-color: var(--blue);
    }
    .btn-plan-primary {
        background: linear-gradient(135deg, var(--blue), var(--blue-dark));
        color: white;
        box-shadow: 0 6px 20px rgba(37,99,235,0.4);
    }
    .btn-plan-primary:hover { 
        transform: translateY(-3px); 
        box-shadow: 0 12px 30px rgba(37,99,235,0.5);
    }

    .pricing-footnote {
        text-align: center;
        margin-top: 3rem;
        font-size: 0.8rem;
        color: rgba(255,255,255,0.35);
        position: relative;
        z-index: 2;
    }

    /* ─── CTA ────────────────────────────────────────────────── */
    .cta-section {
        background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%);
        padding: 6rem 8%;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .cta-section::before {
        content: '';
        position: absolute;
        width: 500px; height: 500px;
        background: radial-gradient(circle, rgba(37,99,235,0.1), transparent);
        top: 50%; left: 50%;
        transform: translate(-50%, -50%);
        pointer-events: none;
    }
    .cta-section::after {
        content: '';
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(37,99,235,0.03) 1px, transparent 1px);
        background-size: 30px 30px;
        pointer-events: none;
    }
    .cta-inner {
        position: relative;
        z-index: 2;
        max-width: 650px;
        margin: 0 auto;
    }
    .cta-section h2 {
        font-family: var(--font-display);
        font-size: clamp(2rem, 4vw, 3rem);
        color: var(--navy);
        margin-bottom: 1rem;
        font-weight: 400;
    }
    .cta-section h2 em { 
        background: linear-gradient(135deg, var(--blue), var(--purple));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    .cta-section p { color: var(--muted); margin-bottom: 2rem; font-size: 1.05rem; }
    .cta-buttons { display: flex; gap: 1.2rem; justify-content: center; flex-wrap: wrap; }

    /* ─── RESPONSIVE ─────────────────────────────────────────── */
    @media (max-width: 1200px) {
        .features-grid { grid-template-columns: repeat(3, 1fr); }
    }
    @media (max-width: 1024px) {
        .hero { 
            grid-template-columns: 1fr; 
            gap: 3rem; 
            text-align: center; 
            padding: 4rem 6% 3rem;
        }
        .hero-content { margin: 0 auto; text-align: center; }
        .hero-trust, .hero-ctas, .hero-social-proof { justify-content: center; }
        .resume-preview { max-width: 320px; }
        .stats-band { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .pricing-cards { grid-template-columns: 1fr; max-width: 450px; }
        .plan-card.featured { transform: none; }
        .plan-card.featured.visible { transform: none; }
        .plan-card.featured:hover { transform: translateY(-8px); }
        .features-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .features-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .hero { padding: 3rem 5% 3rem; }
        .hero-headline { font-size: 2.2rem; }
        .stats-band { grid-template-columns: repeat(2, 1fr); }
        .templates-header { flex-direction: column; align-items: flex-start; }
        .plan-card { padding: 2rem 1.5rem; }
    }
    @media (max-width: 640px) {
        .features-grid { grid-template-columns: 1fr; }
        .hero-ctas { flex-direction: column; align-items: center; }
        .hero-ctas .btn-primary, .hero-ctas .btn-outline { width: 100%; justify-content: center; }
        .stats-band { grid-template-columns: 1fr; gap: 1.5rem; }
        .stats-band .stat-item:not(:last-child)::after { display: none; }
        .chip-ats { right: -10px; top: 10px; padding: 0.4rem 0.8rem; }
        .chip-ai { left: -15px; bottom: 20px; padding: 0.4rem 0.8rem; }
    }
</style>

<div class="noise-overlay"></div>


<section class="hero">
    <div class="hero-orb-1"></div>
    <div class="hero-orb-2"></div>
    <div class="hero-orb-3"></div>
    <div class="hero-grid"></div>

    <div class="hero-content">
    

        <h1 class="hero-headline">
            Get Hired Faster<br>with 
            <span class="gradient-text">AI-Optimized</span><br>Resumes
        </h1>

        <p class="hero-sub">
            Create professional, ATS-friendly resumes that actually get you noticed. 
            Build in minutes — not hours — and land your dream job faster.
        </p>

        <div class="hero-ctas">
            <a href="/templates" class="btn-primary">
                Build My Resume 
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <a href="/ats-checker" class="btn-outline">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                Check ATS Score
            </a>
        </div>

        <div class="hero-trust">
            <div class="hero-trust-item">
                <span class="check"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                No credit card required
            </div>
            <div class="hero-trust-item">
                <span class="check"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                AI content suggestions
            </div>
            <div class="hero-trust-item">
                <span class="check"><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                ATS-optimized templates
            </div>
        </div>

        <div class="hero-social-proof">
            <div class="avatar-stack">
                <div class="av" style="background:#dbeafe;">RJ</div>
                <div class="av" style="background:#dcfce7;">MK</div>
                <div class="av" style="background:#fce7f3;">SP</div>
                <div class="av" style="background:#fef3c7;">AL</div>
                <div class="av" style="background:#ede9fe;">+</div>
            </div>
            <div>
                <div class="stars-row">★★★★★</div>
                <div class="social-proof-label"><strong>Trusted by 10,000+</strong> job seekers</div>
                <div class="social-proof-label">4.9 / 5 from 2,500+ reviews</div>
            </div>
        </div>
    </div>

    <div class="resume-preview-wrap">
        <div class="resume-preview">
            <div class="preview-ring"></div>
            <div class="resume-preview-card">
                <img src="<?php echo e(asset('resume.png')); ?>" alt="Resume Preview"
                     onerror="this.src='https://placehold.co/400x520/e2e8f0/64748b?text=Resume+Preview'">
            </div>

            <div class="float-chip chip-ats">
                <div class="chip-icon">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <div style="color:#64748b; font-size:0.65rem; font-weight:700; text-transform:uppercase; letter-spacing:0.08em;">ATS Score</div>
                    <div class="score-number">92<span style="font-size:0.8rem;">%</span></div>
                </div>
            </div>

            <div class="float-chip chip-ai">
                <div class="chip-icon">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                </div>
                <span style="color:var(--blue); font-weight:700;">AI Content Enhanced</span>
            </div>
        </div>
    </div>
</section>


<div class="stats-band">
    <div class="stat-item">
        <div class="stat-number" data-count="15000">15,000<span>+</span></div>
        <div class="stat-label">Resumes Created</div>
    </div>
    <div class="stat-item">
        <div class="stat-number">4.9<span>/5</span></div>
        <div class="stat-label">Average Rating</div>
    </div>
    <div class="stat-item">
        <div class="stat-number" data-count="94">94<span>%</span></div>
        <div class="stat-label">ATS Pass Rate</div>
    </div>
    <div class="stat-item">
        <div class="stat-number">30<span>+</span></div>
        <div class="stat-label">Pro Templates</div>
    </div>
</div>


<section class="features-strip">
    <div class="features-strip-header">
        <div class="section-label">Powerful Features</div>
        <h2 class="section-heading">Everything You Need<br>to <em>Get Hired</em></h2>
        <p>Tools crafted for job seekers who want results, not busywork.</p>
    </div>
    <div class="features-grid">
        <div class="feature-item">
            <div class="feature-icon" style="background:linear-gradient(135deg, #eff6ff, #dbeafe);">
                <svg width="24" stroke="#2563eb" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            </div>
            <div class="feature-name">AI Writer</div>
            <div class="feature-desc">Tailored bullet points based on your role & industry</div>
        </div>
        <div class="feature-item">
            <div class="feature-icon" style="background:linear-gradient(135deg, #f0fdf4, #dcfce7);">
                <svg width="24" stroke="#10b981" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div class="feature-name">ATS Optimization</div>
            <div class="feature-desc">Pass scanners & reach human recruiters</div>
        </div>
        <div class="feature-item">
            <div class="feature-icon" style="background:linear-gradient(135deg, #faf5ff, #ede9fe);">
                <svg width="24" stroke="#7c3aed" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M3 9h18M9 21V9"/></svg>
            </div>
            <div class="feature-name">Smart Templates</div>
            <div class="feature-desc">30+ expert-designed layouts that impress</div>
        </div>
        <div class="feature-item">
            <div class="feature-icon" style="background:linear-gradient(135deg, #fff7ed, #fed7aa);">
                <svg width="24" stroke="#f59e0b" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <div class="feature-name">Resume Analyzer</div>
            <div class="feature-desc">Instant detailed feedback & scoring</div>
        </div>
        <div class="feature-item">
            <div class="feature-icon" style="background:linear-gradient(135deg, #fdf2f8, #fbcfe8);">
                <svg width="24" stroke="#ec4899" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/></svg>
            </div>
            <div class="feature-name">Cover Letter Builder</div>
            <div class="feature-desc">Personalized, compelling letters instantly</div>
        </div>
        <div class="feature-item">
            <div class="feature-icon" style="background:linear-gradient(135deg, #ecfeff, #cffafe);">
                <svg width="24" stroke="#0891b2" fill="none" viewBox="0 0 24 24" stroke-width="1.8"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            </div>
            <div class="feature-name">Export & Share</div>
            <div class="feature-desc">PDF, DOCX & LinkedIn-ready exports</div>
        </div>
    </div>
</section>


<section class="templates-section">
    <div class="templates-header">
        <div>
            <div class="section-label">Professional Templates</div>
            <h2 class="section-heading">Stand Out with <em>Pro Designs</em></h2>
        </div>
        <a href="/templates">View All Templates →</a>
    </div>
    <div class="templates-scroller">
        <div class="template-card active">
            <div class="template-thumb">
                <div class="template-mini-header" style="background:linear-gradient(90deg, var(--blue), var(--purple));"></div>
                <div class="template-mini-line"></div><div class="template-mini-line short"></div><div class="template-mini-line"></div><div class="template-mini-line xshort"></div>
            </div>
            <div class="template-label">Modern</div>
        </div>
        <div class="template-card">
            <div class="template-thumb">
                <div class="template-mini-header" style="background:#2c3e50;"></div>
                <div class="template-mini-line"></div><div class="template-mini-line short"></div><div class="template-mini-line"></div>
            </div>
            <div class="template-label">Classic</div>
        </div>
        <div class="template-card">
            <div class="template-thumb">
                <div class="template-mini-header" style="background:#0f172a;"></div>
                <div class="template-mini-line"></div><div class="template-mini-line short"></div><div class="template-mini-line xshort"></div>
            </div>
            <div class="template-label">Executive</div>
        </div>
        <div class="template-card">
            <div class="template-thumb">
                <div class="template-mini-header" style="background:#94a3b8;"></div>
                <div class="template-mini-line"></div><div class="template-mini-line short"></div>
            </div>
            <div class="template-label">Minimal</div>
        </div>
        <div class="template-card">
            <div class="template-thumb">
                <div class="template-mini-header" style="background:linear-gradient(90deg, #8b5cf6, #ec4899);"></div>
                <div class="template-mini-line"></div><div class="template-mini-line short"></div><div class="template-mini-line"></div>
            </div>
            <div class="template-label">Creative</div>
        </div>
        <div class="template-card">
            <div class="template-thumb">
                <div class="template-mini-header" style="background:#0891b2;"></div>
                <div class="template-mini-line"></div><div class="template-mini-line short"></div><div class="template-mini-line xshort"></div>
            </div>
            <div class="template-label">Technical</div>
        </div>
        <div class="template-card">
            <div class="template-thumb">
                <div class="template-mini-header" style="background:#059669;"></div>
                <div class="template-mini-line"></div><div class="template-mini-line short"></div><div class="template-mini-line"></div>
            </div>
            <div class="template-label">Startup</div>
        </div>
    </div>
</section>


<section class="pricing-section">
    <div class="pricing-glow"></div>
    <div class="pricing-glow-2"></div>
    
    <div class="pricing-header">
        <div class="section-label">Simple Pricing</div>
        <h2 class="section-heading">Choose the Plan<br>That Works for <em>You</em></h2>
        <p>No hidden fees. Cancel anytime. Start with a free preview.</p>
    </div>

    <div class="pricing-cards">
        
        <div class="plan-card">
            <div class="plan-icon-wrap dark">
                            </div>
            <div class="plan-name">Basic</div>
            <div class="plan-price">
                <span class="currency">₹</span>299
            </div>
            <div class="plan-period">valid for 14 days</div>
            <div class="plan-divider"></div>
            <ul class="plan-features">
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>1 Resume</li>
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>Unlimited Cover Letters</li>
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>AI Writing Features</li>
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>PDF & DOCX Export</li>
            </ul>
            <a href="/templates" class="btn-plan btn-plan-dark">Get Started</a>
        </div>

        
        <div class="plan-card featured">
            <div class="plan-badge"> Most Popular</div>
            <div class="plan-icon-wrap light">
                <svg width="22" fill="none" stroke="var(--blue)" stroke-width="1.8" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="plan-name">Silver</div>
            <div class="plan-price">
                <span class="currency">₹</span>699
            </div>
            <div class="plan-period">valid for 45 days</div>
            <div class="plan-divider"></div>
            <ul class="plan-features">
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>3 Resumes</li>
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>Unlimited Cover Letters</li>
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>Advanced AI Writing</li>
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>ATS Score Checker</li>
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>Priority Support</li>
            </ul>
            <a href="/templates" class="btn-plan btn-plan-primary">Choose Silver →</a>
        </div>

        
        <div class="plan-card">
            <div class="plan-icon-wrap dark">
                
            </div>
            <div class="plan-name">Gold</div>
            <div class="plan-price">
                <span class="currency">₹</span>2500
            </div>
            <div class="plan-period">valid for 1 full year</div>
            <div class="plan-divider"></div>
            <ul class="plan-features">
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>Unlimited Resumes</li>
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>Unlimited Cover Letters</li>
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>Advanced AI Features</li>
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>ATS Score + Full Analysis</li>
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>Dedicated Support</li>
            </ul>
            <a href="/templates" class="btn-plan btn-plan-dark">Go Gold</a>
        </div>
    </div>

    <p class="pricing-footnote"> All plans include a free preview. No credit card needed to start.</p>
</section>


<section class="cta-section">
    <div class="cta-inner">
        <h2>Ready to Build Your<br><em>Dream Career?</em></h2>
        <p>Join 15,000+ professionals who landed their dream jobs with CvBliss.</p>
        <div class="cta-buttons">
            <a href="/templates" class="btn-primary">
                Create My Resume Now
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <a href="/ats-checker" class="btn-outline">Check ATS Score</a>
        </div>
        <div style="margin-top: 2rem; font-size: 0.8rem; color: var(--muted);">
             Free forever plan available • No commitment required
        </div>
    </div>
</section>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
/* Intersection Observer: reveal on scroll */
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach((e, i) => {
        if (e.isIntersecting) {
            const delay = e.target.dataset.delay || 0;
            setTimeout(() => {
                e.target.classList.add('visible');
            }, delay);
            revealObserver.unobserve(e.target);
        }
    });
}, { threshold: 0.1, rootMargin: "0px 0px -50px 0px" });

document.querySelectorAll('.feature-item').forEach((el, i) => {
    el.dataset.delay = i * 50;
    revealObserver.observe(el);
});

document.querySelectorAll('.plan-card').forEach((el, i) => {
    el.dataset.delay = i * 100;
    revealObserver.observe(el);
});

/* Template card active toggle */
document.querySelectorAll('.template-card').forEach(c => {
    c.addEventListener('click', () => {
        document.querySelectorAll('.template-card').forEach(t => t.classList.remove('active'));
        c.classList.add('active');
    });
});

/* Stat number counter animation */
const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (!e.isIntersecting) return;
        counterObserver.unobserve(e.target);
        const el = e.target;
        const target = parseInt(el.dataset.count);
        if (!target) return;
        const duration = 1800;
        const start = performance.now();
        function update(now) {
            const t = Math.min((now - start) / duration, 1);
            const eased = 1 - Math.pow(1 - t, 3);
            el.textContent = Math.round(eased * target).toLocaleString();
            if (t < 1) requestAnimationFrame(update);
            else el.textContent = target.toLocaleString();
        }
        requestAnimationFrame(update);
    });
}, { threshold: 0.5 });

document.querySelectorAll('.stat-number[data-count]').forEach(el => counterObserver.observe(el));

/* Smooth hover parallax on resume preview */
const preview = document.querySelector('.resume-preview');
if (preview) {
    preview.addEventListener('mousemove', (e) => {
        const rect = preview.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width - 0.5;
        const y = (e.clientY - rect.top) / rect.height - 0.5;
        preview.style.transform = `perspective(1000px) rotateY(${x * 10}deg) rotateX(${-y * 8}deg) translateY(-8px)`;
    });
    preview.addEventListener('mouseleave', () => {
        preview.style.transform = '';
        preview.style.transition = 'transform 0.5s var(--ease-spring)';
        setTimeout(() => preview.style.transition = '', 500);
    });
}

/* Smooth scroll for anchor links */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});

/* Add animation to stat items on hover */
document.querySelectorAll('.stat-item').forEach(item => {
    item.addEventListener('mouseenter', () => {
        item.style.transform = 'translateY(-5px)';
    });
    item.addEventListener('mouseleave', () => {
        item.style.transform = '';
    });
});
</script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Resume_Builder\resources\views/pages/home.blade.php ENDPATH**/ ?>