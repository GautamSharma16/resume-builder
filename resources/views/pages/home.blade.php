{{-- resources/views/pages/home.blade.php --}}
@extends('layouts.app')

@section('title', 'Cvbliss - Build a Resume That Commands Attention')

@section('content')


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
    /* .section-label::before {
        content: '';
        display: block;
        width: 8px;
        height: 8px;
        background: var(--blue);
        border-radius: 50%;
       
    } */
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
        display: none; /* Disabled for performance */
    }

    /* ─── HERO ───────────────────────────────────────────────── */
    .hero {
        min-height: 90vh;
        display: grid;
        grid-template-columns: 1fr 0.9fr;
        align-items: center;
        gap: 4rem;
        padding: 1rem 6% 2rem;
        background: linear-gradient(135deg, #ffffff 0%, #fafcff 100%);
        position: relative;
        overflow: hidden;
        contain: layout style;
    }

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
        font-size: clamp(3.5rem, 3vw, 3rem);
        font-weight: 400;
        color: var(--navy);
        line-height: 1.1;
        margin-bottom: 1.5rem;
        animation: fadeUp 0.7s var(--ease-out) 0s both; /* Reduced delay from 0.2s to 0s */
    }
    .hero-headline .gradient-text {
        background: linear-gradient(135deg, var(--blue), var(--purple), var(--pink));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        background-size: 200% 200%;
        animation: gradient-shift 6s ease infinite;
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

    .preview-ring {
        position: absolute;
        inset: -25px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--blue), var(--purple), transparent);
        opacity: 0.15;
        z-index: -1;
        animation: spin-slow 20s linear infinite;
    }

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
        padding: 3.5rem 4%;
        background: var(--white);
        overflow: hidden;
        position: relative;
        width: 100%;
    }
    .templates-section::before,
    .templates-section::after {
        content: "";
        position: absolute;
        pointer-events: none;
        border-radius: 999px;
        filter: blur(4px);
        opacity: 0.72;
        z-index: 0;
    }
    .templates-section::before {
        width: 280px;
        height: 280px;
        top: 30px;
        right: 7%;
        background: rgba(37, 99, 235, 0.12);
    }
    .templates-section::after {
        width: 220px;
        height: 220px;
        left: 4%;
        bottom: 34px;
        background: rgba(20, 184, 166, 0.10);
    }
    .templates-section--resume {
        background:
            radial-gradient(circle at 86% 16%, rgba(59, 130, 246, 0.16), transparent 32%),
            linear-gradient(180deg, #f8fbff 0%, #eef6ff 52%, #ffffff 100%);
    }
    .templates-section--cover {
        padding-top: 4rem;
        background:
            radial-gradient(circle at 10% 18%, rgba(245, 158, 11, 0.14), transparent 30%),
            radial-gradient(circle at 86% 82%, rgba(20, 184, 166, 0.14), transparent 34%),
            linear-gradient(180deg, #fffaf0 0%, #f0fdfa 58%, #ffffff 100%);
    }
    .templates-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 3rem;
        flex-wrap: wrap;
        gap: 1rem;
        position: relative;
        z-index: 2;
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

    /* ─── TEMPLATE CAROUSEL ──────────────────────────────────── */
    .ts-stage {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        padding: 40px 0 60px;
        position: relative;
        min-height: 620px;
        perspective: 1200px;
    }
    .ts-card {
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid var(--border);
        background: var(--white);
        cursor: pointer;
        transition: all 0.5s cubic-bezier(0.25, 1, 0.5, 1);
        flex-shrink: 0;
        position: relative;
        transform-style: preserve-3d;
    }
    
    /* Center Card - Increased Size */
    .ts-card.center {
        width: 360px;
        height: 500px;
        opacity: 1;
        transform: scale(1) translateZ(50px);
        z-index: 10;
        box-shadow: 0 25px 60px rgba(0,0,0,0.18);
    }
    
    /* Level 1 Side */
    .ts-card.side-1 {
        width: 240px;
        height: 330px;
        opacity: 0.85;
        transform: scale(0.9) translateZ(0);
        z-index: 5;
    }
    
    /* Level 2 Side */
    .ts-card.side-2 {
        width: 190px;
        height: 260px;
        opacity: 0.55;
        transform: scale(0.8) translateZ(-50px);
        z-index: 3;
    }
    
    /* Level 3 Side */
    .ts-card.side-3 {
        width: 150px;
        height: 210px;
        opacity: 0.25;
        transform: scale(0.7) translateZ(-100px);
        z-index: 1;
    }

    .ts-card:not(.center) .ts-hover-overlay { display: none !important; }

    .ts-resume-inner {
        width: 100%;
        height: 100%;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        position: relative;
    }

    .ts-hover-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0,0,0,0.5);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        opacity: 0;
        transition: opacity 0.25s ease;
        border-radius: 17px;
        z-index: 10;
    }
    .ts-card:hover .ts-hover-overlay { opacity: 1; }

    .ts-template-name {
        color: #fff;
        font-size: 15px;
        font-weight: 600;
        letter-spacing: 0.01em;
    }

    .ts-use-btn {
        background: #fff;
        color: #1e293b;
        border: none;
        padding: 11px 26px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: transform 0.2s, box-shadow 0.2s;
        margin-top: 4px;
        text-decoration: none;
    }
    .ts-use-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        color: var(--blue);
    }

    .ts-dots {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-top: 18px;
    }
    .ts-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: rgba(0,0,0,0.15);
        cursor: pointer;
        transition: background 0.2s, transform 0.2s;
    }
    .ts-dot.active {
        background: var(--blue);
        transform: scale(1.35);
    }

    .ts-nav {
        display: flex;
        justify-content: center;
        gap: 12px;
        margin-top: 22px;
    }
    .ts-arrow {
        width: 44px; height: 44px;
        border-radius: 50%;
        border: 1px solid var(--border);
        background: var(--white);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer;
        transition: all 0.2s var(--ease-spring);
        color: var(--muted);
        font-size: 1.1rem;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
    }
    .ts-arrow:hover {
        background: var(--blue);
        color: white;
        border-color: var(--blue);
        transform: scale(1.05);
        box-shadow: 0 6px 16px rgba(37,99,235,0.25);
    }

    /* Scaling for Real Templates */
    .ts-resume-inner.is-real {
        position: relative;
        width: 100%;
        height: 100%;
        background: white;
    }
    .ts-resume-inner.is-real > * {
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        transform-origin: top center;
        width: 794px; /* A4 Width */
        pointer-events: none;
    }

    /* Card Specific Scales */
    .ts-card.center .ts-resume-inner.is-real > * { transform: translateX(-50%) scale(0.45); }
    .ts-card.side-1 .ts-resume-inner.is-real > * { transform: translateX(-50%) scale(0.30); }
    .ts-card.side-2 .ts-resume-inner.is-real > * { transform: translateX(-50%) scale(0.24); }
    .ts-card.side-3 .ts-resume-inner.is-real > * { transform: translateX(-50%) scale(0.19); }

    /* Responsive Scaling */
    @media (max-width: 1024px) {
        .ts-stage { min-height: 500px; gap: 10px; }
        .ts-card.center { width: 280px; height: 390px; }
        .ts-card.side-1 { width: 180px; height: 250px; }
        .ts-card.side-2 { width: 140px; height: 195px; }
        .ts-card.side-3 { display: none; }
        .ts-card.center .ts-resume-inner.is-real > * { transform: translateX(-50%) scale(0.35); }
    }
    @media (max-width: 768px) {
        .ts-stage { min-height: 420px; gap: 8px; }
        .ts-card.center { width: 220px; height: 310px; }
        .ts-card.side-1 { width: 130px; height: 182px; }
        .ts-card.side-2 { display: none; }
        .ts-card.center .ts-resume-inner.is-real > * { transform: translateX(-50%) scale(0.28); }
    }

    /* Resume mockup shared */
    .rm { width: 100%; height: 100%; display: flex; flex-direction: column; }

    /* Template A - Modern Blue */
    .rm-a .rm-header { background: #1e3a5f; padding: 16px 14px 12px; }
    .rm-a .rm-name { background: rgba(255,255,255,0.9); height: 10px; width: 65%; border-radius: 5px; margin-bottom: 5px; }
    .rm-a .rm-title { background: rgba(255,255,255,0.4); height: 6px; width: 40%; border-radius: 3px; }
    .rm-a .rm-body { padding: 12px 14px; flex: 1; background: #fff; }
    .rm-a .rm-section-title { background: #1e3a5f; height: 5px; width: 40%; border-radius: 2px; margin-bottom: 7px; margin-top: 10px; }
    .rm-a .rm-line { background: #e2e8f0; height: 4px; border-radius: 2px; margin-bottom: 5px; }
    .rm-a .rm-line.short { width: 60%; }
    .rm-a .rm-line.xshort { width: 40%; }

    /* Template B - Executive Dark */
    .rm-b .rm-header { background: #0f172a; padding: 16px 14px 12px; display: flex; gap: 10px; align-items: center; }
    .rm-b .rm-avatar { width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.15); flex-shrink: 0; }
    .rm-b .rm-header-text { flex: 1; }
    .rm-b .rm-name { background: rgba(255,255,255,0.85); height: 9px; width: 70%; border-radius: 4px; margin-bottom: 5px; }
    .rm-b .rm-title { background: rgba(255,255,255,0.3); height: 5px; width: 45%; border-radius: 3px; }
    .rm-b .rm-body { display: flex; flex: 1; }
    .rm-b .rm-sidebar { width: 30%; background: #1e293b; padding: 10px 8px; }
    .rm-b .rm-main-col { flex: 1; padding: 10px 12px; background: #fff; }
    .rm-b .rm-sb-line { background: rgba(255,255,255,0.2); height: 4px; border-radius: 2px; margin-bottom: 6px; }
    .rm-b .rm-sb-line.short { width: 60%; }
    .rm-b .rm-section-title { background: #0f172a; height: 5px; width: 50%; border-radius: 2px; margin: 8px 0 6px; }
    .rm-b .rm-line { background: #e2e8f0; height: 4px; border-radius: 2px; margin-bottom: 5px; }
    .rm-b .rm-line.short { width: 55%; }

    /* Template C - Minimal Clean */
    .rm-c .rm-header { padding: 16px 14px 10px; border-bottom: 2px solid #e2e8f0; background: #fff; }
    .rm-c .rm-name { background: #1e293b; height: 10px; width: 55%; border-radius: 4px; margin-bottom: 6px; }
    .rm-c .rm-title { background: #94a3b8; height: 5px; width: 35%; border-radius: 3px; }
    .rm-c .rm-contact-row { display: flex; gap: 7px; margin-top: 7px; }
    .rm-c .rm-contact-pill { background: #f1f5f9; height: 5px; border-radius: 2px; width: 28%; }
    .rm-c .rm-body { padding: 10px 14px; flex: 1; background: #fff; }
    .rm-c .rm-section-title { background: #334155; height: 5px; width: 35%; border-radius: 2px; margin-bottom: 7px; margin-top: 10px; }
    .rm-c .rm-accent { width: 24px; height: 3px; background: #2563eb; border-radius: 2px; margin-bottom: 6px; }
    .rm-c .rm-line { background: #e2e8f0; height: 4px; border-radius: 2px; margin-bottom: 5px; }
    .rm-c .rm-line.short { width: 60%; }

    /* Template D - Creative Purple */
    .rm-d .rm-header { background: linear-gradient(135deg, #7c3aed, #4f46e5); padding: 16px 14px 12px; }
    .rm-d .rm-name { background: rgba(255,255,255,0.95); height: 10px; width: 62%; border-radius: 5px; margin-bottom: 5px; }
    .rm-d .rm-title { background: rgba(255,255,255,0.45); height: 5px; width: 42%; border-radius: 3px; }
    .rm-d .rm-tags { display: flex; gap: 5px; margin-top: 7px; }
    .rm-d .rm-tag { background: rgba(255,255,255,0.2); height: 6px; width: 22%; border-radius: 3px; }
    .rm-d .rm-body { padding: 12px 14px; flex: 1; background: #faf5ff; }
    .rm-d .rm-section-title { background: #7c3aed; height: 5px; width: 40%; border-radius: 2px; margin-bottom: 7px; margin-top: 10px; }
    .rm-d .rm-line { background: #ddd6fe; height: 4px; border-radius: 2px; margin-bottom: 5px; }
    .rm-d .rm-line.short { width: 58%; }

    /* Template E - Teal Modern */
    .rm-e .rm-header { background: #0d9488; padding: 16px 14px 12px; }
    .rm-e .rm-name { background: rgba(255,255,255,0.9); height: 10px; width: 60%; border-radius: 5px; margin-bottom: 5px; }
    .rm-e .rm-title { background: rgba(255,255,255,0.4); height: 5px; width: 38%; border-radius: 3px; }
    .rm-e .rm-body { display: flex; flex: 1; }
    .rm-e .rm-sidebar { width: 28%; background: #f0fdfa; padding: 10px 8px; }
    .rm-e .rm-main-col { flex: 1; padding: 10px 12px; background: #fff; }
    .rm-e .rm-sb-line { background: #99f6e4; height: 4px; border-radius: 2px; margin-bottom: 6px; }
    .rm-e .rm-section-title { background: #0d9488; height: 5px; width: 45%; border-radius: 2px; margin: 8px 0 6px; }
    .rm-e .rm-line { background: #e2e8f0; height: 4px; border-radius: 2px; margin-bottom: 5px; }
    .rm-e .rm-line.short { width: 60%; }

    /* Template F - Rose Pink */
    .rm-f .rm-header { background: #be185d; padding: 16px 14px 12px; }
    .rm-f .rm-name { background: rgba(255,255,255,0.9); height: 10px; width: 65%; border-radius: 5px; margin-bottom: 5px; }
    .rm-f .rm-title { background: rgba(255,255,255,0.4); height: 5px; width: 40%; border-radius: 3px; }
    .rm-f .rm-body { padding: 12px 14px; flex: 1; background: #fff9fb; }
    .rm-f .rm-section-title { background: #be185d; height: 5px; width: 38%; border-radius: 2px; margin-bottom: 7px; margin-top: 10px; }
    .rm-f .rm-line { background: #fce7f3; height: 4px; border-radius: 2px; margin-bottom: 5px; }
    .rm-f .rm-line.short { width: 55%; }

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
        .ts-stage { gap: 12px; min-height: 460px; }
        .ts-card.center { width: 250px; height: 350px; }
        .ts-card.side { width: 150px; height: 210px; }
        .ts-card.far-side { width: 110px; height: 155px; }
    }
    @media (max-width: 768px) {
        .features-grid { grid-template-columns: repeat(2, 1fr); gap: 1rem; }
        .hero { padding: 3rem 5% 3rem; }
        .hero-headline { font-size: 2.2rem; }
        .stats-band { grid-template-columns: repeat(2, 1fr); }
        .templates-header { flex-direction: column; align-items: flex-start; }
        .plan-card { padding: 2rem 1.5rem; }
        .ts-stage { gap: 8px; min-height: 400px; }
        .ts-card.center { width: 210px; height: 295px; }
        .ts-card.side { width: 120px; height: 168px; }
        .ts-card.far-side { display: none; }
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

    .templates-section .section-heading {
        font-size: clamp(2rem, 3.2vw, 3.3rem);
        line-height: 1.08;
        overflow-wrap: anywhere;
    }
    .templates-section .section-label {
        background: rgba(255, 255, 255, 0.74);
        border: 1px solid rgba(37, 99, 235, 0.10);
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.06);
        backdrop-filter: blur(12px);
    }
    .templates-section--cover .section-label {
        border-color: rgba(20, 184, 166, 0.16);
    }

    .ts-stage {
        position: relative;
        min-height: 450px !important;
        overflow: hidden;
        padding: 12px 0 24px;
        display: block !important;
        width: 100%;
        z-index: 1;
    }
    .ts-track {
        display: flex;
        align-items: start;
        gap: 22px;
        width: max-content;
        padding-inline: 12px;
        will-change: transform;
        animation-duration: 42s;
        animation-timing-function: linear;
        animation-iteration-count: infinite;
    }
    .ts-stage--resume .ts-track {
        animation-name: tsMarqueeRtl;
        animation-direction: reverse;
    }
    .ts-stage--cover .ts-track {
        animation-name: tsMarqueeRtl;
        animation-direction: normal;
    }
    .ts-stage:hover .ts-track { animation-play-state: paused; }
    .ts-card {
        width: 300px !important;
        height: auto !important;
        aspect-ratio: 210 / 297;
        opacity: 1 !important;
        transform: none !important;
        z-index: 1 !important;
        border-radius: 16px;
        overflow: hidden;
        border: 1px solid var(--border);
        background: #fff;
        box-shadow: 0 16px 42px rgba(15, 23, 42, 0.10);
        flex-shrink: 0;
        position: relative;
        cursor: pointer;
        contain: layout paint;
    }
    .templates-section--cover .ts-card {
        box-shadow: 0 18px 48px rgba(15, 118, 110, 0.12);
    }
    .ts-resume-inner {
        background: #f8fafc;
        position: absolute;
        inset: 0;
        overflow: hidden;
    }
    .ts-resume-inner > * {
        width: 794px !important;
        min-width: 794px !important;
        min-height: 1123px !important;
        position: absolute !important;
        top: 0;
        left: 50%;
        transform-origin: top center !important;
        pointer-events: none;
        will-change: transform;
    }
    .ts-resume-inner:not(.is-real) > * {
        inset: 0;
        left: 0;
        width: 100% !important;
        min-width: 0 !important;
        min-height: 100% !important;
        transform: none !important;
        position: absolute !important;
    }
    .ts-resume-inner .tpl-resume,
    .ts-resume-inner .tpl-cover {
        box-shadow: none !important;
        pointer-events: none;
    }
    .ts-stage .ts-card .ts-hover-overlay {
        display: flex !important;
        opacity: 0;
        z-index: 50;
        pointer-events: none;
        background: rgba(2, 6, 23, 0.55);
    }
    .ts-stage .ts-card:not(.center) .ts-hover-overlay { display: flex !important; }
    .ts-stage .ts-card:hover .ts-hover-overlay { opacity: 1; }
    .ts-dots, .ts-nav { display: none !important; }
    @keyframes tsMarqueeRtl {
        from { transform: translateX(0); }
        to { transform: translateX(-50%); }
    }
    @media (max-width: 768px) {
        .templates-section {
            padding-inline: 1rem;
        }
        .templates-header {
            margin-bottom: 1.5rem;
        }
        .ts-stage {
            overflow-x: auto;
            overflow-y: hidden;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 14px;
            margin-inline: -1rem;
            min-height: 420px !important;
        }
        .ts-stage::-webkit-scrollbar {
            display: none;
        }
        .ts-track {
            display: flex;
            gap: 14px;
            width: max-content;
            padding-inline: 1rem;
            animation: none !important;
        }
        .ts-card {
            width: min(82vw, 290px) !important;
            scroll-snap-align: center;
        }
        .ts-stage--cover .ts-card {
            width: min(88vw, 320px) !important;
        }
        .templates-section .section-heading { font-size: clamp(1.8rem, 8vw, 2.5rem); }
        .ts-use-btn { padding: 9px 16px; font-size: 12px; }
    }
</style>

<div class="noise-overlay"></div>

{{-- HERO SECTION --}}
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
            <a href="/enhance-cv" class="btn-outline">
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
                <img src="{{ asset('resume.png') }}" alt="Resume Preview"
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

{{-- STATS BAND --}}
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

{{-- FEATURES STRIP --}}
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

{{-- TEMPLATES --}}
<section class="templates-section templates-section--resume">
    <div class="templates-header">
        <div>
            <div class="section-label">Professional Templates</div>
            <h2 class="section-heading">Stand Out with <em>Pro Designs</em></h2>
        </div>
        <a href="/templates">View All Templates →</a>
    </div>
    <div class="ts-stage ts-stage--resume" id="ts-stage"></div>
</section>

<section class="templates-section templates-section--cover">
    <div class="templates-header">
        <div>
            <div class="section-label">Cover Letter Templates</div>
            <h2 class="section-heading">Write Better with <em>Pro Cover Letters</em></h2>
        </div>
        <a href="/cover-letter">View All Cover Letters →</a>
    </div>
    <div class="ts-stage ts-stage--cover" id="cl-stage"></div>
</section>

{{-- PRICING --}}
<section class="pricing-section">
    <div class="pricing-glow"></div>
    <div class="pricing-glow-2"></div>

    <div class="pricing-header">
        <div class="section-label">Simple Pricing</div>
        <h2 class="section-heading">Choose the Plan<br>That Works for <em>You</em></h2>
        <p>No hidden fees. Cancel anytime. Start with a free preview.</p>
    </div>

    <div class="pricing-cards">
        @foreach($plans as $index => $plan)
        <div class="plan-card {{ $index === 1 ? 'featured' : '' }}">
            @if($index === 1)
                <div class="plan-badge"> Most Popular</div>
            @endif

            <div class="plan-name">{{ $plan->name }}</div>
            <div class="plan-price">
                <span class="currency">₹</span>{{ number_format($plan->price_paise / 100, 0) }}
            </div>
            <div class="plan-period">valid for {{ $plan->duration_days }} days</div>
            <div class="plan-divider"></div>
            <ul class="plan-features">
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>{{ $plan->resume_limit ?: 'Unlimited' }} Resumes</li>
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>{{ (is_null($plan->cover_letter_limit) || $plan->slug === 'silver') ? 'Unlimited' : $plan->cover_letter_limit }} Cover Letters</li>
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>{{ $plan->ai_enabled ? 'Advanced AI Features' : 'Basic Features' }}</li>
                <li><span class="pf-icon"><svg fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>{{ $plan->downloads_allowed ?: 'Unlimited' }} Downloads</li>
            </ul>
            <a href="{{ auth()->check() ? route('plans.checkout', $plan) : route('login', ['redirect' => route('plans.checkout', $plan)]) }}" class="btn-plan {{ $index === 1 ? 'btn-plan-primary' : 'btn-plan-dark' }}">
                {{ $index === 1 ? 'Choose ' . $plan->name . ' →' : 'Get Started' }}
            </a>
        </div>
        @endforeach
    </div>

    <p class="pricing-footnote"> All plans include a free preview. No credit card needed to start.</p>
</section>

{{-- CTA --}}
<section class="cta-section">
    <div class="cta-inner">
        <h2>Ready to Build Your<br><em>Dream Career?</em></h2>
        <p>Join 15,000+ professionals who landed their dream jobs with CvBliss.</p>
        <div class="cta-buttons">
            <a href="/templates" class="btn-primary">
                Create My Resume Now
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
            </a>
            <a href="/enhance-cv" class="btn-outline">Check ATS Score</a>
        </div>
        <div style="margin-top: 2rem; font-size: 0.8rem; color: var(--muted);">
             Free forever plan available • No commitment required
        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>


const carouselTemplates = [
    @forelse($professionalTemplates as $tpl)
    {
        name: @json($tpl->name),
        url:  "{{ route('resume.create', ['template_id' => $tpl->id]) }}",
        html: @json($rendered[$tpl->id] ?? null),
        isReal: true
    },
    @empty
    @endforelse
];

const coverLetterTemplates = [
    @forelse($professionalCoverTemplates ?? [] as $tpl)
    {
        name: @json($tpl->name),
        url: "{{ route('cover-letter', ['template_id' => $tpl->id]) }}",
        html: @json($renderedCover[$tpl->id] ?? null),
        isReal: true
    },
    @empty
    @endforelse
];

/* Built-in mockup HTML strings */
const mockupHTML = {
    'Modern Blue': `<div class="rm rm-a"><div class="rm-header"><div class="rm-name"></div><div class="rm-title"></div></div><div class="rm-body"><div class="rm-section-title"></div><div class="rm-line"></div><div class="rm-line short"></div><div class="rm-line"></div><div class="rm-section-title"></div><div class="rm-line"></div><div class="rm-line short"></div><div class="rm-line xshort"></div><div class="rm-section-title"></div><div class="rm-line"></div><div class="rm-line short"></div></div></div>`,
    'Executive':   `<div class="rm rm-b"><div class="rm-header"><div class="rm-avatar"></div><div class="rm-header-text"><div class="rm-name"></div><div class="rm-title"></div></div></div><div class="rm-body"><div class="rm-sidebar"><div class="rm-sb-line"></div><div class="rm-sb-line short"></div><div class="rm-sb-line"></div><div class="rm-sb-line short"></div><div class="rm-sb-line"></div><div class="rm-sb-line short"></div></div><div class="rm-main-col"><div class="rm-section-title"></div><div class="rm-line"></div><div class="rm-line short"></div><div class="rm-section-title"></div><div class="rm-line"></div><div class="rm-line short"></div><div class="rm-line"></div></div></div></div>`,
    'Minimal':     `<div class="rm rm-c"><div class="rm-header"><div class="rm-name"></div><div class="rm-title"></div><div class="rm-contact-row"><div class="rm-contact-pill"></div><div class="rm-contact-pill"></div><div class="rm-contact-pill"></div></div></div><div class="rm-body"><div class="rm-accent"></div><div class="rm-section-title"></div><div class="rm-line"></div><div class="rm-line short"></div><div class="rm-line"></div><div class="rm-accent"></div><div class="rm-section-title"></div><div class="rm-line"></div><div class="rm-line short"></div></div></div>`,
    'Creative':    `<div class="rm rm-d"><div class="rm-header"><div class="rm-name"></div><div class="rm-title"></div><div class="rm-tags"><div class="rm-tag"></div><div class="rm-tag"></div><div class="rm-tag"></div></div></div><div class="rm-body"><div class="rm-section-title"></div><div class="rm-line"></div><div class="rm-line short"></div><div class="rm-line"></div><div class="rm-section-title"></div><div class="rm-line"></div><div class="rm-line short"></div></div></div>`,
    'Teal Pro':    `<div class="rm rm-e"><div class="rm-header"><div class="rm-name"></div><div class="rm-title"></div></div><div class="rm-body"><div class="rm-sidebar"><div class="rm-sb-line"></div><div class="rm-sb-line"></div><div class="rm-sb-line"></div><div class="rm-sb-line"></div></div><div class="rm-main-col"><div class="rm-section-title"></div><div class="rm-line"></div><div class="rm-line short"></div><div class="rm-section-title"></div><div class="rm-line"></div><div class="rm-line short"></div><div class="rm-line"></div></div></div></div>`,
    'Rose':        `<div class="rm rm-f"><div class="rm-header"><div class="rm-name"></div><div class="rm-title"></div></div><div class="rm-body"><div class="rm-section-title"></div><div class="rm-line"></div><div class="rm-line short"></div><div class="rm-line"></div><div class="rm-section-title"></div><div class="rm-line"></div><div class="rm-line short"></div></div></div>`
};

const defaultTemplates = [
    { name: 'Modern Blue', url: '/templates', html: mockupHTML['Modern Blue'], isReal: false },
    { name: 'Executive',   url: '/templates', html: mockupHTML['Executive'], isReal: false },
    { name: 'Minimal',     url: '/templates', html: mockupHTML['Minimal'], isReal: false },
    { name: 'Creative',    url: '/templates', html: mockupHTML['Creative'], isReal: false },
    { name: 'Teal Pro',    url: '/templates', html: mockupHTML['Teal Pro'], isReal: false },
    { name: 'Rose',        url: '/templates', html: mockupHTML['Rose'], isReal: false }
];

const defaultCoverTemplates = defaultTemplates.map((t) => ({
    ...t,
    url: '/cover-letter'
}));

const mockupKeys = Object.keys(mockupHTML);
const templates = (carouselTemplates.length >= 3)
    ? carouselTemplates.map((t, i) => ({
        name: t.name,
        url:  t.url,
        html: t.html || mockupHTML[t.name] || mockupHTML[mockupKeys[i % mockupKeys.length]],
        isReal: !!t.html
      }))
    : defaultTemplates;

const coverTemplates = (coverLetterTemplates.length >= 3)
    ? coverLetterTemplates.map((t, i) => ({
        name: t.name,
        url: t.url,
        html: t.html || mockupHTML[mockupKeys[i % mockupKeys.length]],
        isReal: !!t.html
      }))
    : defaultCoverTemplates;

function buildMarquee(stageId, items) {
    const stage = document.getElementById(stageId);
    if (!stage || !items.length) return;

    const track = document.createElement('div');
    track.className = 'ts-track';

    const renderCard = (item) => {
        const card = document.createElement('div');
        card.className = 'ts-card';
        card.innerHTML = `
            <div class="ts-resume-inner ${item.isReal ? 'is-real' : ''}">${item.html}</div>
            <div class="ts-hover-overlay">
                <span class="ts-template-name">${item.name}</span>
                <span class="ts-use-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    Use this template
                </span>
            </div>`;
        card.addEventListener('click', () => { window.location.href = item.url; });
        return card;
    };

    [...items, ...items].forEach((item) => track.appendChild(renderCard(item)));
    stage.innerHTML = '';
    stage.appendChild(track);
    requestAnimationFrame(() => fitTemplatePreview(stage));
}

function fitTemplatePreview(stage) {
    stage.querySelectorAll('.ts-card').forEach((card) => {
        const doc = card.querySelector('.ts-resume-inner > *');
        if (!doc) return;
        const cardW = card.clientWidth;
        const cardH = card.clientHeight;
        const scale = Math.min(cardW / 794, cardH / 1123) * 0.995;
        doc.style.transform = `translateX(-50%) scale(${scale})`;
    });
}

buildMarquee('ts-stage', templates);
buildMarquee('cl-stage', coverTemplates);
let previewResizeFrame = null;
window.addEventListener('resize', () => {
    if (previewResizeFrame) cancelAnimationFrame(previewResizeFrame);
    previewResizeFrame = requestAnimationFrame(() => {
    const resumeStage = document.getElementById('ts-stage');
    const coverStage = document.getElementById('cl-stage');
    if (resumeStage) fitTemplatePreview(resumeStage);
    if (coverStage) fitTemplatePreview(coverStage);
    });
});


/* ─── INTERSECTION OBSERVER: reveal on scroll ─────────────────────── */
const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (e.isIntersecting) {
            const delay = e.target.dataset.delay || 0;
            setTimeout(() => e.target.classList.add('visible'), delay);
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

/* ─── STAT COUNTER ANIMATION ──────────────────────────────────────── */
const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if (!e.isIntersecting) return;
        counterObserver.unobserve(e.target);
        const el     = e.target;
        const target = parseInt(el.dataset.count);
        if (!target) return;
        const duration = 1800;
        const start    = performance.now();
        function update(now) {
            const t      = Math.min((now - start) / duration, 1);
            const eased  = 1 - Math.pow(1 - t, 3);
            el.textContent = Math.round(eased * target).toLocaleString();
            if (t < 1) requestAnimationFrame(update);
            else el.textContent = target.toLocaleString();
        }
        requestAnimationFrame(update);
    });
}, { threshold: 0.5 });

document.querySelectorAll('.stat-number[data-count]').forEach(el => counterObserver.observe(el));

/* ─── RESUME PREVIEW PARALLAX ─────────────────────────────────────── */
const preview = document.querySelector('.resume-preview');
if (preview) {
    preview.addEventListener('mousemove', e => {
        const rect = preview.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width - 0.5;
        const y = (e.clientY - rect.top)  / rect.height - 0.5;
        preview.style.transform = `perspective(1000px) rotateY(${x * 10}deg) rotateX(${-y * 8}deg) translateY(-8px)`;
    });
    preview.addEventListener('mouseleave', () => {
        preview.style.transform = '';
        preview.style.transition = 'transform 0.5s var(--ease-spring)';
        setTimeout(() => preview.style.transition = '', 500);
    });
}

/* ─── SMOOTH SCROLL ───────────────────────────────────────────────── */
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
</script>
@endpush
