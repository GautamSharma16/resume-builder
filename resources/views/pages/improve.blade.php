@extends('layouts.app')

@section('title', 'Enhance Your Resume - AI-Powered Optimization')

@section('content')


<style>
    /* ─── TOKENS (EXACT MATCH TO HOME PAGE) ─────────────────── */
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
        --font-body:    'Inter', sans-serif;

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

    /* Reset only for main content area to avoid impacting header/footer */
    #heroSection, #dashboardSection, #workspaceSection {
        box-sizing: border-box;
    }
    #heroSection *, #dashboardSection *, #workspaceSection * {
        box-sizing: border-box;
    }

    /* Ensure footer/navbar lists don't get double bullets */
    footer ul, footer li {
        list-style: none !important;
        list-style-type: none !important;
        margin: 0;
        padding: 0;
    }
    footer li::marker {
        content: none !important;
    }
    footer li::before {
        content: none !important;
    }

    html, body {
        margin: 0 !important;
        padding: 0 !important;
        width: 100%;
        min-height: 100%;
    }

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
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(60px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    @keyframes scanMove {
        0% { top: -5px; opacity: 1; }
        90% { top: 100%; opacity: 1; }
        100% { top: 100%; opacity: 0; }
    }

    /* ─── SHARED COMPONENTS ──────────────────────────────────── */
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

    /* ─── HERO SECTION ───────────────────────────────────────── */
    .enhance-hero {
        width: 100%;
        min-height: auto;
        display: grid;
        grid-template-columns: 1fr 1fr;
        align-items: center;
        gap: 3rem;
        padding: 1rem 4% 2rem;
        background: linear-gradient(135deg, #ffffff 0%, #fafcff 100%);
        position: relative;
        overflow: hidden;
    }

    .hero-orb-1 {
        position: absolute;
        width: 500px; height: 500px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(37,99,235,0.07), transparent 70%);
        top: -200px; left: -150px;
        animation: spin-slow 25s linear infinite;
        pointer-events: none;
    }
    .hero-orb-2 {
        position: absolute;
        width: 400px; height: 400px;
        border-radius: 50%;
        background: radial-gradient(circle, rgba(139,92,246,0.05), transparent 70%);
        bottom: -150px; right: -100px;
        animation: spin-reverse 30s linear infinite;
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
        mask-image: radial-gradient(ellipse 80% 80% at 60% 50%, black 30%, transparent 90%);
    }

    .enhance-hero-content {
        position: relative;
        z-index: 2;
        animation: fadeUp 0.7s var(--ease-out) 0.1s both;
    }

    .eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--blue-light);
        padding: 0.2rem 0.9rem 0.2rem 0.65rem;
        border-radius: var(--r-full);
        font-size: 0.7rem;
        font-weight: 800;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--blue);
        margin-bottom: 1rem;
    }
    .eyebrow-dot {
        width: 8px; height: 8px;
        background: var(--blue);
        border-radius: 50%;
        animation: pulse-ring 1.8s infinite;
    }

    .enhance-hero h1 {
        font-family: var(--font-display);
        font-size: clamp(2.2rem, 4vw, 3.2rem);
        color: var(--navy);
        font-weight: 400;
        line-height: 1.1;
        margin-bottom: 1rem;
    }
    .enhance-hero h1 em {
        font-style: italic;
        background: linear-gradient(135deg, var(--blue), var(--purple));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        animation: gradient-shift 6s ease infinite;
        background-size: 200% 200%;
    }

    .enhance-hero-sub {
        font-size: 1rem;
        color: var(--muted);
        max-width: 480px;
        margin-bottom: 2rem;
        line-height: 1.7;
    }

    .hero-trust-row {
        display: flex;
        flex-wrap: wrap;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    .hero-trust-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.8rem;
        color: var(--muted);
        font-weight: 500;
    }
    .hero-trust-item .check {
        width: 18px; height: 18px;
        background: var(--green-light);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .hero-trust-item .check svg { color: var(--green); }

    .badge-group {
        display: flex;
        gap: 0.6rem;
        flex-wrap: wrap;
    }
    .badge-item {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        background: white;
        border: 1px solid var(--border);
        border-radius: var(--r-full);
        padding: 0.4rem 1rem;
        font-size: 0.7rem;
        font-weight: 600;
        color: var(--muted);
        transition: all 0.3s var(--ease-spring);
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    .badge-item:hover {
        border-color: var(--blue);
        color: var(--blue);
        transform: translateY(-2px);
    }

    /* Hero right: upload card */
    .enhance-hero-right {
        position: relative;
        z-index: 2;
        animation: slideInRight 0.8s var(--ease-spring) 0.2s both;
    }

    /* ─── UPLOAD CARD ─────────────────────────────────────────── */
    .upload-card {
        background: var(--white);
        border-radius: var(--r-2xl);
        border: 1px solid var(--border);
        box-shadow: 0 20px 60px rgba(0,0,0,0.07), 0 0 0 1px rgba(37,99,235,0.05);
        position: relative;
        overflow: hidden;
    }
    .upload-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--blue), var(--purple), var(--pink));
    }

    .upload-card-header {
        padding: 1.5rem 1.8rem 1rem;
        border-bottom: 1px solid var(--border);
    }
    .upload-card-header h3 {
        font-family: var(--font-display);
        font-size: 1.3rem;
        color: var(--navy);
        font-weight: 400;
    }
    .upload-card-header p {
        font-size: 0.8rem;
        color: var(--muted);
        margin-top: 0.2rem;
    }

    .upload-card-body {
        padding: 1.5rem 1.8rem;
    }

    .dropzone {
        border: 2px dashed rgba(37,99,235,0.25);
        border-radius: var(--r-xl);
        background: linear-gradient(135deg, #f0fdfa, #f8fafc);
        padding: 1.4rem;
        cursor: pointer;
        transition: all 0.3s var(--ease-spring);
        margin-bottom: 1rem;
    }
    .dropzone:hover {
        border-color: var(--blue);
        background: linear-gradient(135deg, var(--blue-light), #e0f2fe);
        transform: scale(1.01);
    }
    .dropzone-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    .dropzone-icon {
        width: 48px; height: 48px;
        background: var(--blue-light);
        border-radius: var(--r-md);
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        transition: transform 0.3s var(--ease-spring);
    }
    .dropzone:hover .dropzone-icon { transform: scale(1.1) rotate(-5deg); }

    .dropzone-text { font-weight: 700; font-size: 0.9rem; color: var(--navy); }
    .dropzone-sub { font-size: 0.72rem; color: var(--muted); margin-top: 0.2rem; }

    .format-pills { display: flex; gap: 0.4rem; flex-wrap: wrap; margin-bottom: 1.2rem; }
    .format-pill {
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.2rem 0.6rem;
        background: var(--blue-light);
        border-radius: var(--r-full);
        color: var(--blue);
    }

    .upload-actions { display: flex; align-items: center; gap: 1rem; flex-wrap: wrap; }

    .btn-enhance {
        background: linear-gradient(135deg, var(--blue), var(--blue-dark));
        color: white;
        border: none;
        border-radius: var(--r-full);
        padding: 0.8rem 2rem;
        font-weight: 700;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        cursor: pointer;
        transition: all 0.3s var(--ease-spring);
        box-shadow: 0 4px 15px rgba(37,99,235,0.3);
        position: relative;
        overflow: hidden;
    }
    .btn-enhance::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transform: translateX(-100%);
        transition: transform 0.6s;
    }
    .btn-enhance:hover::before { transform: translateX(100%); }
    .btn-enhance:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(37,99,235,0.4);
    }
    .btn-enhance:disabled { opacity: 0.6; cursor: not-allowed; }

    .status-line {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--muted);
    }
    .status-dot {
        width: 7px; height: 7px;
        background: var(--blue);
        border-radius: 50%;
        animation: pulse-ring 1.5s infinite;
        flex-shrink: 0;
    }

    /* ─── SCAN OVERLAY ────────────────────────────────────────── */
    .scan-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(11,18,33,0.72);
        backdrop-filter: blur(8px);
        z-index: 10000;
        align-items: center;
        justify-content: center;
    }
    .scan-overlay.active { display: flex; }

    .scan-card {
        background: white;
        border-radius: var(--r-xl);
        padding: 2rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1rem;
        min-width: 300px;
        max-width: 360px;
        width: calc(100% - 2rem);
        box-shadow: 0 28px 72px rgba(0,0,0,0.22);
    }
    .scan-paper {
        width: 150px; height: 190px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r-md);
        padding: 1rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 12px 32px rgba(15,23,42,0.12);
    }
    .scan-paper::before {
        content: '';
        position: absolute;
        left: 0; right: 0; top: -8px;
        height: 4px;
        background: linear-gradient(90deg, transparent, var(--blue), var(--purple), transparent);
        box-shadow: 0 0 18px rgba(37,99,235,0.45);
        animation: scanMove 1.6s ease-in-out infinite;
    }
    .scan-paper-head {
        height: 18px; width: 58%;
        border-radius: var(--r-sm);
        background: linear-gradient(135deg, var(--blue), var(--purple));
        margin-bottom: 1rem;
    }
    .scan-paper-line { height: 7px; border-radius: var(--r-full); background: #dbe3ef; margin-bottom: 0.55rem; }
    .scan-paper-line.wide { width: 100%; }
    .scan-paper-line.mid { width: 82%; }
    .scan-paper-line.short { width: 62%; }
    .scan-paper-line.tiny { width: 46%; }
    .scan-header h2 { font-family: var(--font-body); font-size: 16px; font-weight: 700; color: var(--navy); text-align: center; }
    .scan-header p { color: var(--muted); font-size: 12px; text-align: center; margin-top: 0.25rem; }
    .scan-progress-bar-wrap { width: 100%; background: var(--surface-2); border-radius: var(--r-full); height: 6px; overflow: hidden; }
    .scan-progress-fill { height: 100%; width: 0%; background: linear-gradient(90deg, var(--blue), #818cf8); border-radius: var(--r-full); transition: width 0.3s ease; }
    .scan-steps { width: 100%; display: flex; flex-direction: column; gap: 0.5rem; }
    .scan-step { display: flex; align-items: center; gap: 0.55rem; font-size: 12px; color: var(--soft); }
    .scan-step.active { color: var(--blue); }
    .scan-step.done { color: var(--green); }
    .scan-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }

    /* ─── DASHBOARD SECTION ───────────────────────────────────── */
    .results-section {
        width: 100%;
        display: none;
        background: linear-gradient(180deg, var(--surface) 0%, var(--white) 100%);
        padding: 1rem 4%;
        position: relative;
    }
    .results-section.active {
        display: block;
        animation: fadeUp 0.5s var(--ease-out);
    }

    .results-section-header {
        text-align: center;
        max-width: 560px;
        margin: 0 auto 3rem;
    }
    .results-section-header h2 {
        font-family: var(--font-display);
        font-size: clamp(1.8rem, 3vw, 2.6rem);
        color: var(--navy);
        font-weight: 400;
    }
    .results-section-header h2 em {
        font-style: italic;
        background: linear-gradient(135deg, var(--blue), var(--purple));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    .results-section-header p { color: var(--muted); margin-top: 0.6rem; font-size: 0.9rem; }

    .score-row {
        display: grid;
        grid-template-columns: 220px 1fr;
        gap: 1.5rem;
        margin-bottom: 0;
    }
    @media (max-width: 900px) { .score-row { grid-template-columns: 1fr; } }

    .score-card {
        background: linear-gradient(145deg, var(--navy), #0f172a);
        border-radius: var(--r-2xl);
        padding: 2rem 1.5rem;
        text-align: center;
        color: white;
        position: relative;
        overflow: hidden;
    }
    .score-card::before {
        content: '';
        position: absolute; inset: 0;
        background: radial-gradient(circle at 50% 0%, rgba(37,99,235,0.2), transparent 70%);
    }
    .score-label { font-size: 0.65rem; letter-spacing: 0.12em; text-transform: uppercase; opacity: 0.5; margin-bottom: 0.5rem; }
    .score-number { font-size: 3.5rem; font-weight: 800; font-family: var(--font-display); line-height: 1; }
    .score-denom { font-size: 0.9rem; opacity: 0.4; margin-bottom: 0.8rem; }

    .insight-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
    }
    @media (max-width: 640px) { .insight-grid { grid-template-columns: 1fr; } }

    .insight-card {
        background: white;
        border-radius: var(--r-xl);
        border: 1px solid var(--border);
        padding: 1.2rem;
        transition: all 0.3s var(--ease-spring);
        opacity: 0;
        transform: translateY(10px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.02);
    }
    .insight-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.06); border-color: rgba(37,99,235,0.15); }
    .insight-card.show { animation: fadeUp 0.4s forwards; }
    .insight-card-title { font-weight: 700; color: var(--navy); font-size: 0.85rem; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem; }

    .keyword-pill {
        display: inline-block;
        background: var(--gold-light);
        color: #92400e;
        font-size: 0.7rem;
        font-weight: 700;
        padding: 0.25rem 0.7rem;
        border-radius: var(--r-full);
        margin: 0.2rem;
    }

    /* ─── WORKSPACE SECTION ───────────────────────────────────── */
    .workspace-section {
        display: none;
        padding: 0 5% 4rem;
        background: var(--white);
    }
    .workspace-section.active {
        display: block;
        animation: fadeUp 0.5s var(--ease-out) 0.1s both;
    }

    .workspace-section-header {
        max-width: 800px;
        margin: 0 0 2.5rem;
        padding-top: 1rem;
    }
    .workspace-section-header h2 {
        font-family: var(--font-display);
        font-size: clamp(2rem, 4vw, 3rem);
        color: var(--navy);
        font-weight: 400;
        line-height: 1.2;
        text-transform: none;
    }
    .workspace-section-header h2 em {
        font-style: italic;
        background: linear-gradient(135deg, var(--blue), var(--purple));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    .workspace-section-header p { color: var(--muted); font-size: 0.9rem; margin-top: 0.5rem; }

    .workspace-grid {
        display: grid;
        grid-template-columns: 1fr 1.3fr;
        gap: 2rem;
    }
    @media (max-width: 900px) { .workspace-grid { grid-template-columns: 1fr; } }

    .editor-card, .preview-card {
        background: white;
        border-radius: var(--r-2xl);
        border: 1px solid var(--border);
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    }

    .card-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        background: var(--surface);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    .card-header h3 { font-family: var(--font-display); color: var(--navy); font-size: 1.1rem; font-weight: 400; }

    .selected-template-card { padding: 1.2rem; }
    .selected-template-preview {
        height: 260px;
        background: #ffffff;
        border: 1px solid var(--border);
        border-radius: var(--r-lg);
        overflow: hidden;
        display: flex;
        justify-content: center;
        position: relative;
    }

    /* Force visibility in small preview */
    .selected-template-preview *,
    .selected-template-preview .tpl-resume,
    .selected-template-preview .tpl-resume * {
        color: #1e293b !important;
        opacity: 1 !important;
        visibility: visible !important;
    }
    .selected-template-preview .tpl-thumb-scaler {
        width: 794px;
        transform: scale(0.22);
        transform-origin: top center;
        pointer-events: none;
    }
    .selected-template-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-top: 1rem;
    }
    .selected-template-name { font-size: 0.9rem; font-weight: 800; color: var(--navy); }

    .card-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border);
        background: var(--surface);
    }
    .card-footer p { font-size: 0.75rem; color: var(--muted); line-height: 1.5; }

    .preview-content {
        padding: 1.5rem;
        min-height: 600px;
        background: white;
        overflow: auto;
        display: flex;
        justify-content: center;
        position: relative;
    }

    /* ─── AGGRESSIVE PREVIEW VISIBILITY FIX ─── */
    .preview-content *, 
    .preview-content .tpl-resume,
    .preview-content .tpl-resume * {
        color: #0f172a !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    /* Exceptions for headers/accents if needed */
    .preview-content .tpl-resume h1,
    .preview-content .tpl-resume .name-header {
        color: #000000 !important;
        font-weight: 800 !important;
    }
    .preview-content .tpl-resume {
        transform: scale(0.72);
        transform-origin: top center;
        margin-bottom: -300px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.1);
        border: 1px solid var(--border);
    }
    @media (max-width: 640px) {
        .preview-content { justify-content: flex-start; padding: 0.75rem; }
        .preview-content .tpl-resume { transform: scale(0.48); transform-origin: top left; margin-bottom: -560px; }
    }

    /* ─── BUTTONS ─────────────────────────────────────────────── */
    .btn-sm {
        background: transparent;
        border: 1.5px solid var(--border);
        border-radius: var(--r-full);
        padding: 0.4rem 1rem;
        font-size: 0.7rem;
        font-weight: 700;
        cursor: pointer;
        transition: 0.2s;
        font-family: var(--font-body);
    }
    .btn-sm-primary {
        background: var(--blue-light);
        border-color: var(--blue);
        color: var(--blue);
    }
    .btn-sm-primary:hover:not(:disabled) {
        background: var(--blue);
        color: white;
        transform: translateY(-2px);
    }
    .btn-sm:disabled { opacity: 0.5; cursor: not-allowed; }

    .btn-download {
        background: linear-gradient(135deg, var(--blue), var(--blue-dark));
        color: white;
        border: none;
        border-radius: var(--r-full);
        padding: 0.5rem 1.3rem;
        font-size: 0.78rem;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        transition: all 0.3s var(--ease-spring);
        box-shadow: 0 4px 12px rgba(37,99,235,0.25);
        font-family: var(--font-body);
    }
    .btn-download:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,99,235,0.35); }

    .btn-reset {
        display: none;
        height: 44px;
        padding: 0 1.5rem;
        border: 1.5px solid var(--blue);
        color: var(--blue);
        border-radius: var(--r-full);
        font-weight: 700;
        font-size: 0.8rem;
        align-items: center;
        gap: 0.4rem;
        background: transparent;
        cursor: pointer;
        transition: all 0.3s var(--ease-spring);
        font-family: var(--font-body);
    }
    .btn-reset:hover { background: var(--blue-light); }

    /* ─── TEMPLATE MODAL ──────────────────────────────────────── */
    .template-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 10001;
        background: rgba(11,18,33,0.62);
        backdrop-filter: blur(8px);
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .template-modal.open { display: flex; }
    .template-modal-box {
        width: min(1120px, 100%);
        max-height: 88vh;
        overflow: hidden;
        background: white;
        border-radius: var(--r-xl);
        box-shadow: 0 28px 72px rgba(0,0,0,0.22);
        display: flex;
        flex-direction: column;
    }
    .template-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.2rem 1.4rem;
        border-bottom: 1px solid var(--border);
    }
    .template-modal-header h2 { font-family: var(--font-display); font-weight: 400; color: var(--navy); font-size: 1.7rem; }
    .template-modal-header p { color: var(--muted); font-size: 0.8rem; margin-top: 0.15rem; }
    .template-modal-close {
        width: 36px; height: 36px;
        border-radius: var(--r-full);
        border: 1px solid var(--border);
        background: white;
        display: inline-flex; align-items: center; justify-content: center;
        cursor: pointer;
    }
    .template-modal-grid {
        overflow: auto;
        padding: 1.2rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 1rem;
    }
    .tpl-item {
        border: 1px solid var(--border);
        border-radius: var(--r-lg);
        padding: 0.65rem;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
        background: white;
    }
    .tpl-item:hover { border-color: var(--blue); background: var(--blue-light); }
    .tpl-item.active {
        border-color: var(--blue);
        background: var(--blue-light);
        box-shadow: 0 0 0 4px var(--blue-glow);
    }
    .tpl-thumb {
        height: 210px;
        background: #ffffff;
        border-radius: var(--r-md);
        margin-bottom: 0.5rem;
        display: flex; align-items: flex-start; justify-content: center;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.04);
    }
    .tpl-thumb-scaler {
        width: 794px;
        min-height: 1123px;
        transform: scale(0.19);
        transform-origin: top center;
        pointer-events: none;
    }
    .tpl-name { font-size: 0.75rem; font-weight: 700; color: var(--navy); }

    /* ─── PAYMENT MODAL ───────────────────────────────────────── */
    .payment-modal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 9999;
        align-items: center;
        justify-content: center;
        background: rgba(0,0,0,0.5);
        backdrop-filter: blur(4px);
    }
    .payment-modal.active { display: flex; }
    .modal-container {
        background: white;
        border-radius: var(--r-2xl);
        max-width: 400px;
        width: 90%;
        padding: 1.8rem;
        text-align: center;
        position: relative;
    }
    .modal-container::before {
        content: '';
        position: absolute; top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--blue), var(--purple));
        border-radius: var(--r-2xl) var(--r-2xl) 0 0;
    }

    /* ─── HOW IT WORKS STRIP ──────────────────────────────────── */
    .how-it-works {
        background: linear-gradient(135deg, var(--navy), #0f172a);
        padding: 2.5rem 4%;
        position: relative;
        overflow: hidden;
    }
    .how-it-works::before {
        content: '';
        position: absolute; inset: 0;
        background: linear-gradient(135deg, rgba(37,99,235,0.15), transparent 70%);
        pointer-events: none;
    }
    .how-it-works::after {
        content: '';
        position: absolute; inset: 0;
        background-image: radial-gradient(rgba(255,255,255,0.03) 1px, transparent 1px);
        background-size: 30px 30px;
        pointer-events: none;
    }
    .hiw-inner { position: relative; z-index: 2; }
    .hiw-header { text-align: center; margin-bottom: 3rem; }
    .hiw-header .section-label { background: rgba(255,255,255,0.08); color: rgba(255,255,255,0.7); }
    .hiw-header .section-label::before { background: var(--blue); }
    .hiw-header h2 { font-family: var(--font-display); font-size: clamp(1.8rem, 3vw, 2.4rem); color: white; font-weight: 400; }
    .hiw-header h2 em {
        font-style: italic;
        background: linear-gradient(135deg, var(--blue), var(--purple));
        -webkit-background-clip: text; background-clip: text; color: transparent;
    }
    .hiw-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 2rem;
        position: relative;
    }
    .hiw-grid::before {
        display: none;
    }
    .hiw-step { text-align: center; }
    .hiw-step-num {
        width: 56px; height: 56px;
        background: linear-gradient(135deg, rgba(37,99,235,0.3), rgba(139,92,246,0.2));
        border: 1px solid rgba(37,99,235,0.3);
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 1.2rem;
        font-family: var(--font-display);
        font-size: 1.4rem;
        color: white;
        position: relative;
        z-index: 1;
        transition: all 0.3s var(--ease-spring);
    }
    .hiw-step:hover .hiw-step-num {
        background: linear-gradient(135deg, rgba(37,99,235,0.5), rgba(139,92,246,0.4));
        transform: scale(1.1);
        box-shadow: 0 0 20px rgba(37,99,235,0.3);
    }
    .hiw-step-title { font-weight: 700; color: white; font-size: 0.9rem; margin-bottom: 0.4rem; }
    .hiw-step-desc { font-size: 0.78rem; color: rgba(255,255,255,0.45); line-height: 1.5; }

    @media (max-width: 768px) { .hiw-grid { grid-template-columns: repeat(2, 1fr); } .hiw-grid::before { display: none; } }
    @media (max-width: 480px) { .hiw-grid { grid-template-columns: 1fr; } }

    /* ─── RESPONSIVE ──────────────────────────────────────────── */
    @media (max-width: 1024px) {
        .enhance-hero { grid-template-columns: 1fr; padding: 1.5rem 5% 2rem; }
        .enhance-hero-content { text-align: center; }
        .hero-trust-row { justify-content: center; }
        .badge-group { justify-content: center; }
        .enhance-hero-right { max-width: 520px; margin: 0 auto; }
    }
    @media (max-width: 768px) {
        .enhance-hero { padding: 1rem 4% 2rem; gap: 2rem; }
        .results-section, .workspace-section { padding-left: 4%; padding-right: 4%; }
        .how-it-works { padding: 2rem 4%; }
    }
</style>

{{-- ═══════════════════════════════════════════════════════
     SECTION 1: HERO — HEADLINE + UPLOAD CARD
═══════════════════════════════════════════════════════ --}}
<section class="enhance-hero" id="heroSection">
    <div class="hero-orb-1"></div>
    <div class="hero-orb-2"></div>
    <div class="hero-grid"></div>

    {{-- Left: Copy --}}
    <div class="enhance-hero-content">
        <div class="eyebrow">
            <span class="eyebrow-dot"></span>
            AI Resume Optimizer
        </div>
        <h1>Elevate your<br><em>Resume</em> with AI</h1>
        <p class="enhance-hero-sub">
            Upload your resume and let AI restructure every section for maximum ATS compatibility. Get a polished, interview-ready PDF in seconds.
        </p>
        <div class="hero-trust-row">
            <div class="hero-trust-item">
                <span class="check"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                ATS-optimized output
            </div>
            <div class="hero-trust-item">
                <span class="check"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                AI keyword enhancement
            </div>
            <div class="hero-trust-item">
                <span class="check"><svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                Professional PDF export
            </div>
        </div>
        <div class="badge-group">
            <span class="badge-item">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                PDF / DOCX
            </span>
            <span class="badge-item">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                ATS Scoring
            </span>
            <span class="badge-item">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                AI Rewrite
            </span>
            <span class="badge-item">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                PDF Export
            </span>
        </div>
    </div>

    {{-- Right: Upload Card --}}
    <div class="enhance-hero-right">
        <div class="upload-card" id="uploadCardEl">
            <div class="upload-card-header">
                <h3>Upload Your Resume</h3>
                <p>We'll analyze and enhance it with AI instantly</p>
            </div>
            <div class="upload-card-body">
                <form id="resumeForm">
                    <div class="dropzone" id="dropzoneEl">
                        <input type="file" id="resumeFile" name="resume" accept=".pdf,.doc,.docx" style="display:none" required>
                        <div class="dropzone-content">
                            <div class="dropzone-icon">
                                <svg width="24" height="24" fill="none" stroke="var(--blue)" stroke-width="1.8" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            </div>
                            <div>
                                <div class="dropzone-text">Click to upload or drag &amp; drop</div>
                                <div id="fileNameDisplay" class="dropzone-sub">PDF, DOC, DOCX up to 10 MB</div>
                            </div>
                        </div>
                    </div>
                    <div class="format-pills">
                        <span class="format-pill">PDF</span>
                        <span class="format-pill">DOC</span>
                        <span class="format-pill">DOCX</span>
                        <span class="format-pill">Max 10 MB</span>
                    </div>
                    <div class="upload-actions">
                        <button type="submit" id="enhanceBtn" class="btn-enhance">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                            Enhance with AI
                        </button>
                        <button type="button" id="resetBtn" class="btn-reset">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            New Resume
                        </button>
                        <div class="status-line" id="statusMsg"></div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     SECTION 2: HOW IT WORKS (always visible)
═══════════════════════════════════════════════════════ --}}
<section class="how-it-works">
    <div class="hiw-inner">
        <div class="hiw-header">
            <div class="section-label">Simple Process</div>
            <h2>How It <em>Works</em></h2>
        </div>
        <div class="hiw-grid">
            <div class="hiw-step">
                <div class="hiw-step-num">1</div>
                <div class="hiw-step-title">Upload Resume</div>
                <div class="hiw-step-desc">Upload your existing PDF or DOCX resume. We accept any format.</div>
            </div>
            <div class="hiw-step">
                <div class="hiw-step-num">2</div>
                <div class="hiw-step-title">AI Analysis</div>
                <div class="hiw-step-desc">Our AI scans for ATS compatibility, keywords, and structure issues.</div>
            </div>
            <div class="hiw-step">
                <div class="hiw-step-num">3</div>
                <div class="hiw-step-title">Smart Rewrite</div>
                <div class="hiw-step-desc">Every section is rewritten with stronger language and optimized keywords.</div>
            </div>
            <div class="hiw-step">
                <div class="hiw-step-num">4</div>
                <div class="hiw-step-title">Download PDF</div>
                <div class="hiw-step-desc">Pick a professional template and export a polished, job-ready PDF.</div>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     SECTION 3: ATS SCORE & INSIGHTS (shown after analysis)
═══════════════════════════════════════════════════════ --}}
<section id="dashboardSection" class="results-section">
    <div class="results-section-header">
        <div class="section-label">Analysis Complete</div>
        <h2>Your Resume <em>Score</em></h2>
        <p>Here's how your resume performs and what we improved.</p>
    </div>
    <div class="score-row">
        <div class="score-card">
            <div class="score-label">ATS Score</div>
            <div class="score-number" id="atsScore">0</div>
            <div class="score-denom">/100</div>
            <span id="gradeBadge" style="background: rgba(255,255,255,0.15); padding: 0.2rem 0.9rem; border-radius: 999px; font-size: 0.7rem; font-weight: 700; display: inline-block; margin-bottom: 0.5rem;">—</span>
            <div id="verdictText" style="font-size: 0.72rem; opacity: 0.6; line-height: 1.4;">Waiting for analysis</div>
        </div>
        <div class="insight-grid" id="insightGrid">
            <div class="insight-card">
                <div class="insight-card-title">
                    <svg width="14" height="14" fill="none" stroke="var(--green)" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Strengths
                </div>
                <ul id="strengthsList" style="font-size: 0.78rem; padding-left: 1.1rem; color: var(--ink);"></ul>
            </div>
            <div class="insight-card">
                <div class="insight-card-title">
                    <svg width="14" height="14" fill="none" stroke="var(--pink)" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Weaknesses
                </div>
                <ul id="weaknessesList" style="font-size: 0.78rem; padding-left: 1.1rem; color: var(--ink);"></ul>
            </div>
            <div class="insight-card">
                <div class="insight-card-title">
                    <svg width="14" height="14" fill="none" stroke="var(--gold)" stroke-width="2" viewBox="0 0 24 24"><path d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A2 2 0 013 12V7a4 4 0 014-4z"/></svg>
                    Missing Keywords
                </div>
                <div id="keywordsContainer" style="margin-top: 0.4rem;"></div>
            </div>
            <div class="insight-card">
                <div class="insight-card-title">
                    <svg width="14" height="14" fill="none" stroke="var(--blue)" stroke-width="2" viewBox="0 0 24 24"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    AI Suggestions
                </div>
                <ul id="suggestionsList" style="font-size: 0.78rem; padding-left: 1.1rem; color: var(--ink);"></ul>
            </div>
        </div>
    </div>
</section>

{{-- ═══════════════════════════════════════════════════════
     SECTION 4: TEMPLATE + LIVE PREVIEW WORKSPACE
═══════════════════════════════════════════════════════ --}}
<div id="workspaceSection" class="workspace-section">
    <div class="workspace-section-header">
        <div class="section-label">Your Enhanced Resume</div>
        <h2>Choose a <em>Template</em> &amp; Download</h2>
        <p>AI has optimized your content. Pick a layout and export your polished resume.</p>
    </div>
    <div class="workspace-grid">
        {{-- Left: Template selector --}}
        <div class="editor-card">
            <div class="card-header">
                <h3>Template</h3>
                <button id="improveAgainBtn" class="btn-sm btn-sm-primary" disabled>
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-right:3px;vertical-align:middle;"><path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    Improve Again
                </button>
            </div>
            <div class="selected-template-card">
                @if($templates->isNotEmpty())
                    @php($defaultTemplate = $templates->first())
                    <div class="selected-template-preview">
                        <div class="tpl-thumb-scaler">
                            <div id="selectedTemplateThumb">{!! $renderedTemplates[$defaultTemplate->id] ?? '' !!}</div>
                        </div>
                    </div>
                    <div class="selected-template-meta">
                        <div>
                            <div class="selected-template-name" id="selectedTemplateName">{{ $defaultTemplate->name }}</div>
                            <div id="selectedTemplateCategory" style="font-size: 0.75rem; color: var(--muted);">{{ ucfirst($defaultTemplate->category) }} resume template</div>
                        </div>
                        <button type="button" id="changeTemplateBtn" class="btn-sm btn-sm-primary">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:3px;"><path d="M4 7h16M4 12h16M4 17h16"/></svg>
                            Change
                        </button>
                    </div>
                @else
                    <p style="font-size: 0.85rem; color: var(--muted);">No active resume templates found.</p>
                @endif
            </div>
            <div class="card-footer">
                <p>
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px;"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    AI has optimized your content for ATS compatibility. Choose a layout that reflects your professional style.
                </p>
            </div>
        </div>

        {{-- Right: Live preview --}}
        <div class="preview-card">
            <div class="card-header">
                <h3>Live Preview</h3>
                <button id="downloadBtn" class="btn-download">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Download PDF
                </button>
            </div>
            <div class="preview-wrapper" style="position:relative;">
                <div id="livePreview" class="preview-content">
                    <div style="padding: 2rem; color: var(--muted); text-align: center; width: 100%; display: flex; flex-direction: column; align-items: center; gap: 0.5rem; padding-top: 4rem;">
                        <svg width="40" height="40" fill="none" stroke="var(--soft)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <div style="font-size:0.85rem;">Preview will appear after AI enhancement.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     SCAN OVERLAY (Loader)
═══════════════════════════════════════════════════════ --}}
<div id="scanOverlay" class="scan-overlay">
    <div class="scan-card">
        <div class="scan-header">
            <h2>AI is Scanning...</h2>
            <p id="scanStageLabel">Analysing and improving your resume</p>
        </div>
        <div class="scan-paper" aria-hidden="true">
            <div class="scan-paper-head"></div>
            <div class="scan-paper-line wide"></div>
            <div class="scan-paper-line mid"></div>
            <div class="scan-paper-line wide"></div>
            <div class="scan-paper-line short"></div>
            <div class="scan-paper-line mid"></div>
            <div class="scan-paper-line tiny"></div>
        </div>
        <div class="scan-progress-bar-wrap">
            <div class="scan-progress-fill" id="scanProgressFill"></div>
        </div>
        <div class="scan-steps">
            <div class="scan-step" id="scanStep1"><div class="scan-dot"></div>Parsing your resume...</div>
            <div class="scan-step" id="scanStep2"><div class="scan-dot"></div>Optimising ATS keywords...</div>
            <div class="scan-step" id="scanStep3"><div class="scan-dot"></div>Building your template preview...</div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     TEMPLATE SELECTOR MODAL
═══════════════════════════════════════════════════════ --}}
<div id="resumeTemplateModal" class="template-modal">
    <div class="template-modal-box">
        <div class="template-modal-header">
            <div>
                <h2>Choose a different style</h2>
                <p>Your enhanced resume content stays the same.</p>
            </div>
            <button type="button" id="closeTemplateModal" class="template-modal-close">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="template-modal-grid">
            @foreach($templates as $template)
            <div class="tpl-item @if($loop->first) active @endif" data-template-id="{{ $template->id }}">
                <div class="tpl-thumb">
                    <div class="tpl-thumb-scaler">
                        {!! $renderedTemplates[$template->id] ?? '' !!}
                    </div>
                </div>
                <div class="tpl-name">{{ $template->name }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     PAYMENT MODAL
═══════════════════════════════════════════════════════ --}}
<div id="paymentModal" class="payment-modal">
    <div class="modal-container">
        <h3 style="font-family: var(--font-display); color: var(--navy); font-size: 1.5rem; margin-bottom: 0.5rem;">Unlock Export</h3>
        <p style="margin: 0.8rem 0; color: var(--muted); font-size: 0.85rem;">One-time payment for professional PDF</p>
        <div style="font-size: 2.5rem; font-weight: 800; font-family: var(--font-display); color: var(--navy); margin: 1rem 0;">₹49</div>
        <button id="payConfirmBtn" style="background: linear-gradient(135deg, var(--blue), var(--blue-dark)); color: white; border: none; border-radius: 999px; padding: 0.7rem 1.5rem; width: 100%; cursor: pointer; font-weight: 700; font-size: 0.9rem; font-family: var(--font-body);">Pay &amp; Download</button>
        <button id="closeModalBtn" style="margin-top: 0.7rem; background: none; border: none; font-size: 0.72rem; cursor: pointer; color: var(--muted); font-family: var(--font-body);">Cancel</button>
    </div>
</div>

<script>
(function() {
    // DOM
    const form          = document.getElementById('resumeForm');
    const fileInput     = document.getElementById('resumeFile');
    const dropzone      = document.getElementById('dropzoneEl');
    const fileNameDisplay = document.getElementById('fileNameDisplay');
    const enhanceBtn    = document.getElementById('enhanceBtn');
    const resetBtn      = document.getElementById('resetBtn');
    const statusMsg     = document.getElementById('statusMsg');
    const scanOverlay   = document.getElementById('scanOverlay');
    const dashboard     = document.getElementById('dashboardSection');
    const workspace     = document.getElementById('workspaceSection');
    const improveAgainBtn = document.getElementById('improveAgainBtn');
    const heroSection   = document.getElementById('heroSection');
    const resumeTemplates = @json($templates->keyBy('id'));
    const loginUrl      = @json(route('login'));

    let currentAnalysisId = null;
    let resumeData = { name: '', summary: '', skills: [], experience: [], education: [], projects: [] };
    let selectedTemplateId = Object.keys(resumeTemplates)[0] || null;

    // ── Scan animation ──
    const stages = [
        { label: 'Parsing your resume...', step: 0, pct: 25 },
        { label: 'Optimising ATS keywords...', step: 1, pct: 62 },
        { label: 'Building your template preview...', step: 2, pct: 90 },
        { label: 'Finalising your enhanced resume...', step: -1, pct: 100 }
    ];

    function showScanOverlay() {
        if (scanOverlay && scanOverlay.parentElement !== document.body) document.body.appendChild(scanOverlay);
        scanOverlay?.classList.add('active');
        document.body.style.overflow = 'hidden';
        startScanAnimation();
        return new Promise(resolve => requestAnimationFrame(() => setTimeout(resolve, 120)));
    }

    function hideScanOverlay() {
        scanOverlay?.classList.remove('active');
        document.body.style.overflow = '';
    }

    function startScanAnimation() {
        const fill = document.getElementById('scanProgressFill');
        const stageLabel = document.getElementById('scanStageLabel');
        const items = [document.getElementById('scanStep1'), document.getElementById('scanStep2'), document.getElementById('scanStep3')];
        let stageIdx = 0;
        if (fill) fill.style.width = '0%';
        items.forEach(i => { if (i) i.className = 'scan-step'; });
        function nextStage() {
            if (stageIdx >= stages.length) return;
            const s = stages[stageIdx++];
            if (stageLabel) stageLabel.textContent = s.label;
            if (fill) fill.style.width = s.pct + '%';
            if (s.step >= 0) {
                const item = items[s.step];
                if (item) item.classList.add('active');
                if (s.step > 0) { items[s.step - 1]?.classList.remove('active'); items[s.step - 1]?.classList.add('done'); }
            } else {
                items.forEach(i => { if (!i) return; i.classList.remove('active'); i.classList.add('done'); });
            }
            setTimeout(nextStage, stageIdx === 1 ? 600 : (stageIdx < stages.length ? 900 : 400));
        }
        nextStage();
    }

    function populateInsights(data) {
        document.getElementById('atsScore').innerText = data.score || 0;
        const grade = (data.score >= 80) ? 'Excellent' : (data.score >= 60) ? 'Good' : 'Needs Work';
        document.getElementById('gradeBadge').innerText = grade;
        document.getElementById('verdictText').innerText = (data.score >= 70) ? 'Strong ATS compatibility' : 'Improve keywords for higher score';
        document.getElementById('strengthsList').innerHTML  = (data.strengths || []).map(s => `<li style="margin-bottom:0.3rem;">${s}</li>`).join('');
        document.getElementById('weaknessesList').innerHTML = (data.weaknesses || []).map(w => `<li style="margin-bottom:0.3rem;">${w}</li>`).join('');
        document.getElementById('suggestionsList').innerHTML = (data.suggestions || []).map(s => `<li style="margin-bottom:0.3rem;">${s}</li>`).join('');
        const kwDiv = document.getElementById('keywordsContainer');
        kwDiv.innerHTML = (data.keywords || data.missing_keywords || []).map(k => `<span class="keyword-pill">${k}</span>`).join('');
        document.querySelectorAll('.insight-card').forEach((card, idx) => { setTimeout(() => card.classList.add('show'), idx * 80); });
    }

    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    const ensureArray = v => Array.isArray(v) ? v : [];

    function renderSkillsForTemplate(data) {
        return ensureArray(data.skills).map(s => `<span class="tpl-badge">${esc(s)}</span>`).join('');
    }
    function renderListForTemplate(items) {
        return `<ul>${ensureArray(items).map(item => {
            if (typeof item === 'string') return `<li>${esc(item)}</li>`;
            const name = esc(item?.name || item?.degree || '');
            const meta = esc(item?.institution || item?.tech || '');
            const desc = esc(item?.description || item?.year || '');
            return `<li>${name ? `<strong>${name}</strong>` : ''}${meta ? ` ${meta}` : ''}${desc ? `<span class="tpl-description">${desc}</span>` : ''}</li>`;
        }).join('')}</ul>`;
    }
    function renderExperienceForTemplate(data) {
        return ensureArray(data.experience).map(exp => {
            const points = ensureArray(exp?.points).map(p => `<li>${esc(p)}</li>`).join('');
            return `<div class="tpl-role"><div class="tpl-role-head"><strong>${esc(exp?.role||'')}</strong><span>${esc(exp?.period||'')}</span></div><p>${esc(exp?.company||'')}</p><ul>${points}</ul></div>`;
        }).join('');
    }
    function replaceToken(html, key, value) {
        return String(html||'')
            .replace(new RegExp('\\{\\{\\s*'+key+'\\s*\\}\\}','gi'), value)
            .replace(new RegExp('\\[\\[\\s*'+key+'\\s*\\]\\]','gi'), value);
    }

    function renderTemplatePreview(data) {
        const previewDiv = document.getElementById('livePreview');
        const template   = resumeTemplates[selectedTemplateId] || Object.values(resumeTemplates)[0];
        if (!previewDiv) return;
        if (!template) { previewDiv.innerHTML = '<div style="color:var(--muted);padding:2rem;">No templates found.</div>'; return; }
        const normalized = { ...data, skills: ensureArray(data.skills), experience: ensureArray(data.experience), education: ensureArray(data.education), projects: ensureArray(data.projects) };
        const contact = [normalized.email, normalized.mobile, normalized.location].filter(Boolean).join(' | ');
        let html = template.html || '';
        const values = {
            name: esc(normalized.name || 'Your Name'),
            email: esc(normalized.email || ''), mobile: esc(normalized.mobile || ''),
            location: esc(normalized.location || ''), contact: esc(contact), address: esc(normalized.location || ''),
            summary: esc(normalized.summary || ''), social_links: ensureArray(normalized.social_links).map(esc).join(' | '),
            skills: renderSkillsForTemplate(normalized), experience: renderExperienceForTemplate(normalized),
            education: renderListForTemplate(normalized.education), projects: renderListForTemplate(normalized.projects),
        };
        const hasProjectsToken = /\{\{\s*projects\s*\}\}/i.test(html) || /\[\[\s*projects\s*\]\]/i.test(html);
        Object.entries(values).forEach(([key, val]) => { html = replaceToken(html, key, val); });
        if (!hasProjectsToken && normalized.projects.length) {
            const last = html.lastIndexOf('</div>');
            const section = `<h2>Projects</h2>${values.projects}`;
            html = last !== -1 ? html.slice(0, last) + section + html.slice(last) : html + section;
        }
        previewDiv.innerHTML = html;
        const selectedName     = document.getElementById('selectedTemplateName');
        const selectedCategory = document.getElementById('selectedTemplateCategory');
        const selectedThumb    = document.getElementById('selectedTemplateThumb');
        if (selectedName) selectedName.textContent = template.name || 'Selected template';
        if (selectedCategory) selectedCategory.textContent = `${template.category || 'Resume'} resume template`;
        if (selectedThumb) selectedThumb.innerHTML = html;
    }

    function fillEditor(data) {
        const normalizedData = {
            ...data,
            skills:     Array.isArray(data.skills)     ? data.skills     : (data.skills||'').split(',').map(s=>s.trim()).filter(Boolean),
            experience: Array.isArray(data.experience) ? data.experience : [],
            education:  Array.isArray(data.education)  ? data.education  : [],
            projects:   Array.isArray(data.projects)   ? data.projects   : [],
        };
        resumeData = normalizedData;
        renderTemplatePreview(normalizedData);
    }

    // ── Form submit ──
    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const file = fileInput.files[0];
        if (!file) { statusMsg.innerHTML = '<span class="status-dot"></span> Please select a file.'; return; }
        enhanceBtn.disabled = true;
        statusMsg.innerHTML = '<span class="status-dot"></span> AI is processing your resume...';
        await showScanOverlay();
        const formData = new FormData();
        formData.append('resume', file);
        formData.append('_token', '{{ csrf_token() }}');
        try {
            const response = await fetch('{{ route("resume.analyze") }}', { method: 'POST', body: formData });
            const data = await response.json();
            if (!response.ok || !data.success) throw new Error(data.message || 'Analysis failed');
            hideScanOverlay();
            // Collapse hero to just the actions row
            document.getElementById('uploadCardEl').style.display = 'none';
            resetBtn.style.display = 'inline-flex';
            enhanceBtn.style.display = 'none';
            dashboard.classList.add('active');
            workspace.classList.add('active');
            populateInsights(data);
            fillEditor(data.improved_resume);
            currentAnalysisId = data.analysis_id;
            statusMsg.innerHTML = '<span class="status-dot"></span> Resume enhanced successfully!';
            improveAgainBtn.disabled = false;
            // Scroll to results
            setTimeout(() => dashboard.scrollIntoView({ behavior: 'smooth', block: 'start' }), 200);
        } catch (error) {
            hideScanOverlay();
            statusMsg.innerHTML = `<span style="color:#ef4444;">Error: ${error.message}</span>`;
        } finally {
            enhanceBtn.disabled = false;
        }
    });

    // ── Reset ──
    resetBtn.addEventListener('click', () => {
        document.getElementById('uploadCardEl').style.display = 'block';
        resetBtn.style.display = 'none';
        enhanceBtn.style.display = 'inline-flex';
        enhanceBtn.disabled = false;
        dashboard.classList.remove('active');
        workspace.classList.remove('active');
        statusMsg.innerHTML = '';
        fileInput.value = '';
        fileNameDisplay.textContent = 'PDF, DOC, DOCX up to 10 MB';
        currentAnalysisId = null;
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });

    // ── Improve Again ──
    improveAgainBtn.addEventListener('click', async () => {
        if (!currentAnalysisId) return;
        improveAgainBtn.disabled = true;
        await showScanOverlay();
        try {
            const response = await fetch('{{ route("resume.improve") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ analysis_id: currentAnalysisId, resume: resumeData })
            });
            const data = await response.json();
            hideScanOverlay();
            if (data.success) { populateInsights(data); fillEditor(data.improved_resume); statusMsg.innerHTML = '<span class="status-dot"></span> Resume refined!'; }
        } catch (error) {
            hideScanOverlay();
            statusMsg.innerHTML = '<span style="color:#ef4444;">Improvement failed</span>';
        } finally {
            improveAgainBtn.disabled = false;
        }
    });

    // ── Template modal ──
    const templateModal     = document.getElementById('resumeTemplateModal');
    const changeTemplateBtn = document.getElementById('changeTemplateBtn');
    const closeTemplateModal = document.getElementById('closeTemplateModal');

    function syncActiveTemplateCards() {
        document.querySelectorAll('.tpl-item[data-template-id]').forEach(card => {
            card.classList.toggle('active', card.dataset.templateId === String(selectedTemplateId));
        });
    }

    document.querySelectorAll('.tpl-item[data-template-id]').forEach(item => {
        item.addEventListener('click', () => {
            selectedTemplateId = item.dataset.templateId;
            syncActiveTemplateCards();
            renderTemplatePreview(resumeData);
            templateModal?.classList.remove('open');
        });
    });

    changeTemplateBtn?.addEventListener('click', () => templateModal?.classList.add('open'));
    closeTemplateModal?.addEventListener('click', () => templateModal?.classList.remove('open'));
    templateModal?.addEventListener('click', e => { if (e.target === templateModal) templateModal.classList.remove('open'); });

    // ── File input ──
    dropzone.addEventListener('click', () => fileInput.click());
    fileInput.addEventListener('change', e => {
        if (e.target.files[0]) fileNameDisplay.textContent = e.target.files[0].name;
    });
    dropzone.addEventListener('dragover', e => { e.preventDefault(); dropzone.style.borderColor = 'var(--blue)'; });
    dropzone.addEventListener('dragleave', () => { dropzone.style.borderColor = 'rgba(37,99,235,0.25)'; });
    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        const dt = e.dataTransfer.files;
        if (dt.length) { fileInput.files = dt; fileNameDisplay.textContent = dt[0].name; }
        dropzone.style.borderColor = 'rgba(37,99,235,0.25)';
    });

    // ── Download ──
    document.getElementById('downloadBtn').addEventListener('click', () => { window.location.href = loginUrl; });
})();
</script>

@endsection

@section('footer')
    @include('components.footer')
@endsection