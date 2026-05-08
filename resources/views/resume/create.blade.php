@extends('layouts.app')

@section('title', 'Resume Maker | Cvbliss')

@section('content')

<div id="create-cv-app"
    class="min-h-screen rp-root"
    data-store-url="{{ route('resume.store') }}"
    data-analyze-url="{{ route('resume.analyze') }}"
    data-login-url="{{ route('login') }}"
    data-authenticated="{{ auth()->check() ? '1' : '0' }}"
    data-download-requires-plan="{{ auth()->check() && ! auth()->user()->activeSubscription?->hasDownloadsRemaining() ? '1' : '0' }}"
    @if($selectedTemplateId) data-selected-template="{{ $selectedTemplateId }}" @endif>

    <script type="application/json" id="resume-templates-json">@json($templates->keyBy('id'))</script>
    <script type="application/json" id="resume-initial-json">@json($initialResume ?? [])</script>

    <style>
        /* ─── IMPORT same fonts as home page ─── */
        @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Bricolage+Grotesque:opsz,wght@12..96,300;12..96,400;12..96,500;12..96,600;12..96,700;12..96,800&display=swap');

        /* ─── DESIGN TOKENS — identical to home page ─── */
        :root {
            --blue:          #2563eb;
            --blue-dark:     #1d4ed8;
            --blue-light:    #eff6ff;
            --blue-glow:     rgba(37,99,235,0.15);
            --navy:          #0b1221;
            --ink:           #1e293b;
            --muted:         #64748b;
            --soft:          #94a3b8;
            --surface:       #f8fafc;
            --surface-2:     #f1f5f9;
            --border:        rgba(0,0,0,0.07);
            --border-md:     rgba(0,0,0,0.12);
            --white:         #ffffff;
            --green:         #10b981;
            --green-light:   #d1fae5;
            --purple:        #8b5cf6;
            --gold:          #f59e0b;

            /* Typography — same as home */
            --font-display:  'DM Serif Display', serif;
            --font-body:     'Bricolage Grotesque', sans-serif;

            /* Radii — same as home */
            --r-sm:   6px;
            --r-md:   12px;
            --r-lg:   18px;
            --r-xl:   28px;
            --r-2xl:  36px;
            --r-full: 999px;

            /* Easings — same as home */
            --ease-spring: cubic-bezier(0.175, 0.885, 0.32, 1.275);
            --ease-out:    cubic-bezier(0.25, 0.46, 0.45, 0.94);

            /* Shadows */
            --shadow-xs: 0 1px 2px rgba(0,0,0,0.04);
            --shadow-sm: 0 2px 6px rgba(0,0,0,0.06);
            --shadow-md: 0 4px 16px rgba(0,0,0,0.08);
            --shadow-lg: 0 12px 36px rgba(0,0,0,0.1);
            --shadow-xl: 0 24px 56px rgba(0,0,0,0.12);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: var(--font-body);
            font-size: 1rem;
            color: var(--ink);
            background: var(--surface);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ── PAGE BG — matches home gradient orb style ── */
        .rp-root {
            min-height: 100vh;
            background:
                radial-gradient(ellipse 700px 500px at 95% 0%, rgba(37,99,235,0.05) 0%, transparent 70%),
                radial-gradient(ellipse 500px 400px at 0% 100%, rgba(139,92,246,0.04) 0%, transparent 65%),
                var(--surface);
        }

        .rp-page {
            max-width: 1480px;
            margin: 0 auto;
            padding: 2.5rem 2rem 6rem;
        }

        /* ── TOPBAR ── */
        .rp-topbar {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 2.5rem;
            gap: 1rem;
        }

        /* Section label pill — same as home .section-label */
        .rp-section-label {
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.7rem;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: var(--blue);
            margin-bottom: 0.75rem;
            background: var(--blue-light);
            padding: 0.3rem 1rem 0.3rem 0.8rem;
            border-radius: var(--r-full);
        }
        .rp-section-label::before {
            content: '';
            width: 8px; height: 8px;
            background: var(--blue);
            border-radius: 50%;
            
        }

        /* Hero headline — same weight/family as home .hero-headline */
        .rp-hero-title {
            font-family: var(--font-display);
            font-size: clamp(2rem, 3.5vw, 2.8rem);
            font-weight: 400;
            line-height: 1.1;
            color: var(--navy);
            letter-spacing: -0.02em;
        }
        .rp-hero-title em {
            font-style: italic;
            background: linear-gradient(135deg, var(--blue), var(--purple));
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .rp-hero-sub {
            margin-top: 0.6rem;
            font-size: 1rem;
            color: var(--muted);
            font-weight: 400;
            max-width: 480px;
            line-height: 1.65;
        }

        /* Auto-save pill */
        .rp-save-indicator {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.78rem;
            font-weight: 500;
            color: var(--muted);
            background: var(--white);
            padding: 0.5rem 1rem;
            border-radius: var(--r-full);
            border: 1px solid var(--border-md);
            box-shadow: var(--shadow-xs);
            flex-shrink: 0;
        }
        .rp-save-indicator .dot {
            width: 7px; height: 7px;
            background: var(--green);
            border-radius: 50%;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.15);
        }

        /* ── LAYOUT ── */
        .rp-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
            align-items: start;
        }
        @media (min-width: 1100px) {
            .rp-grid { grid-template-columns: 500px 1fr; }
        }

        /* ── FORM PANEL ── */
        .rp-form-panel {
            background: var(--white);
            border-radius: var(--r-xl);
            border: 1px solid var(--border-md);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            animation: fadeUp 0.5s var(--ease-out) both;
        }

        /* ── STEP NAV — underline tab style ── */
        .rp-step-nav {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            background: var(--surface);
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }

        .rp-step-tab {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.35rem;
            padding: 1.1rem 0.5rem;
            font-size: 0.68rem;
            font-weight: 700;
            color: var(--soft);
            text-transform: uppercase;
            letter-spacing: 0.07em;
            cursor: pointer;
            transition: all 0.25s;
            border: none;
            background: transparent;
            font-family: var(--font-body);
            position: relative;
        }
        .rp-step-tab::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 2.5px;
            background: var(--blue);
            border-radius: 2px 2px 0 0;
            transform: scaleX(0);
            transition: transform 0.25s var(--ease-spring);
        }
        .rp-step-tab.active { color: var(--blue); }
        .rp-step-tab.active::after { transform: scaleX(1); }
        .rp-step-tab.completed { color: var(--green); }
        .rp-step-tab.completed::after {
            background: var(--green);
            transform: scaleX(1);
        }

        .rp-step-icon {
            width: 28px; height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            font-weight: 800;
            background: rgba(0,0,0,0.06);
            color: var(--soft);
            transition: all 0.25s var(--ease-spring);
        }
        .rp-step-tab.active .rp-step-icon {
            background: var(--blue);
            color: white;
            box-shadow: 0 4px 12px rgba(37,99,235,0.35);
        }
        .rp-step-tab.completed .rp-step-icon {
            background: var(--green);
            color: white;
        }

        /* ── FORM BODY ── */
        .rp-form-body { padding: 2rem 2rem 1.5rem; }

        .rp-step-content { display: none; }
        .rp-step-content.active { display: block; }
        #completion-panel.visible { display: block !important; }

        /* ── SOURCE TOGGLE BOX ── */
        .rp-source-box {
            background: linear-gradient(135deg, rgba(37,99,235,0.05), rgba(139,92,246,0.03));
            border: 1px solid rgba(37,99,235,0.12);
            border-radius: var(--r-lg);
            padding: 1.25rem 1.5rem;
            margin-bottom: 2rem;
        }
        .rp-source-label {
            display: block;
            font-size: 0.7rem;
            font-weight: 800;
            color: var(--blue);
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-bottom: 0.875rem;
        }
        .source-group {
            display: flex;
            background: white;
            padding: 3px;
            border-radius: var(--r-md);
            border: 1px solid var(--border-md);
            width: fit-content;
            box-shadow: var(--shadow-xs);
        }
        .source-btn {
            padding: 0.45rem 1.1rem;
            border-radius: calc(var(--r-md) - 3px);
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--muted);
            cursor: pointer;
            transition: all 0.2s var(--ease-spring);
            border: none;
            background: transparent;
            font-family: var(--font-body);
            letter-spacing: 0.01em;
        }
        .source-btn.active {
            background: var(--blue);
            color: white;
            box-shadow: 0 2px 10px rgba(37,99,235,0.3);
        }

        /* ── UPLOAD DROPZONE ── */
        .rp-upload-panel { display: none; margin-top: 1rem; }
        .rp-upload-panel.visible { display: block; }
        #resume-autofill-file { display: none; }

        .rp-dropzone {
            border: 1.5px dashed rgba(37,99,235,0.3);
            border-radius: var(--r-md);
            background: white;
            padding: 1.25rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            cursor: pointer;
            transition: all 0.2s;
        }
        .rp-dropzone:hover {
            border-color: var(--blue);
            background: rgba(37,99,235,0.03);
        }
        .rp-dropzone-icon {
            width: 42px; height: 42px;
            background: var(--blue-light);
            border-radius: var(--r-sm);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            color: var(--blue);
        }
        .rp-dropzone-text strong {
            display: block;
            font-size: 0.875rem;
            font-weight: 700;
            color: var(--ink);
        }
        .rp-dropzone-text span {
            font-size: 0.75rem;
            color: var(--soft);
        }
        #resume-autofill-status {
            font-size: 0.75rem;
            margin-top: 0.5rem;
            color: var(--muted);
        }

        /* ─────────────────────────────────────────
           FORM FIELDS — fixed spacing & sizing
        ───────────────────────────────────────── */

        /* Each step has its own padded section */
        .rp-fields-section {
            display: flex;
            flex-direction: column;
            gap: 0; /* spacing handled by field-group margin */
        }

        /* Row of 2 columns */
        .rp-field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem 1.25rem;
            margin-bottom: 1.25rem;
        }
        .rp-field-row.single {
            grid-template-columns: 1fr;
        }
        @media (max-width: 560px) {
            .rp-field-row { grid-template-columns: 1fr; }
        }

        /* Individual field */
        .field-group {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        /* Section separator inside form */
        .rp-fields-divider {
            height: 1px;
            background: var(--border);
            margin: 1.75rem 0;
        }

        /* Field sub-heading */
        .rp-fields-subhead {
            font-size: 0.72rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: var(--soft);
            margin-bottom: 1rem;
        }

        .field-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--ink);
            margin-bottom: 0.45rem;
            letter-spacing: 0.01em;
        }
        .field-label .field-hint {
            font-weight: 400;
            color: var(--soft);
            font-size: 0.75rem;
        }

        /* Inputs — same border-radius scale as home .btn-primary */
        .rp-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: var(--r-md);
            border: 1.5px solid rgba(0,0,0,0.1);
            background: var(--surface);
            font-family: var(--font-body);
            font-size: 0.9rem;
            font-weight: 400;
            color: var(--navy);
            transition: all 0.2s;
            outline: none;
            line-height: 1.5;
        }
        .rp-input::placeholder {
            color: var(--soft);
            font-weight: 400;
        }
        .rp-input:hover {
            border-color: rgba(0,0,0,0.18);
            background: white;
        }
        .rp-input:focus {
            border-color: var(--blue);
            background: white;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        textarea.rp-input {
            resize: vertical;
            min-height: 100px;
            line-height: 1.6;
        }

        /* ── SECTION HEAD (for exp/edu/projects) ── */
        .rp-section-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        .rp-section-head h3 {
            font-family: var(--font-display);
            font-size: 1.3rem;
            font-weight: 400;
            color: var(--navy);
        }

        /* ── BUTTONS — same style as home ── */
        .rp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: var(--r-full);
            font-family: var(--font-body);
            font-weight: 700;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.3s var(--ease-spring);
            border: none;
            text-decoration: none;
            padding: 0.75rem 1.5rem;
            letter-spacing: 0.01em;
        }

        /* Primary — matches home .btn-primary exactly */
        .rp-btn-primary {
            background: linear-gradient(135deg, var(--blue), var(--blue-dark));
            color: white;
            box-shadow: 0 4px 20px rgba(37,99,235,0.35);
            position: relative;
            overflow: hidden;
        }
        .rp-btn-primary::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transform: translateX(-100%);
            transition: transform 0.5s;
        }
        .rp-btn-primary:hover::before { transform: translateX(100%); }
        .rp-btn-primary:hover {
            background: linear-gradient(135deg, var(--blue-dark), var(--blue));
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(37,99,235,0.45);
        }
        .rp-btn-primary:active { transform: translateY(0); }

        /* Outline — matches home .btn-outline */
        .rp-btn-outline {
            background: transparent;
            border: 2px solid rgba(0,0,0,0.1);
            color: var(--ink);
        }
        .rp-btn-outline:hover {
            border-color: var(--blue);
            background: var(--blue-light);
            color: var(--blue);
            transform: translateY(-2px);
        }

        /* Ghost */
        .rp-btn-ghost {
            background: var(--blue-light);
            color: var(--blue);
            border-radius: var(--r-full);
            font-size: 0.8rem;
            padding: 0.5rem 1.1rem;
        }
        .rp-btn-ghost:hover {
            background: rgba(37,99,235,0.15);
            transform: translateY(-1px);
        }

        /* Small variant */
        .rp-btn-sm { padding: 0.5rem 1rem; font-size: 0.8rem; }

        /* Finalize — green gradient */
        .rp-btn-finalize {
            background: linear-gradient(135deg, #059669, var(--green));
            color: white;
            box-shadow: 0 4px 18px rgba(16,185,129,0.35);
        }
        .rp-btn-finalize:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(16,185,129,0.45);
        }

        /* ── STEP FOOTER ── */
        .rp-step-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border);
        }

        /* ── STATUS BAR ── */
        .rp-status-bar {
            padding: 0.65rem 2rem;
            border-top: 1px solid var(--border);
        }
        #cv-status { font-size: 0.75rem; color: var(--soft); }

        /* ── ENTRY CARDS ── */
        .entry-card {
            background: var(--surface);
            border: 1.5px solid var(--border-md);
            border-radius: var(--r-lg);
            padding: 1.25rem 1.5rem;
            margin-bottom: 1rem;
            position: relative;
            transition: all 0.2s var(--ease-spring);
        }
        .entry-card:hover {
            border-color: rgba(37,99,235,0.2);
            background: white;
            box-shadow: var(--shadow-sm);
            transform: translateY(-1px);
        }

        /* ── COMPLETION ── */
        .rp-completion {
            text-align: center;
            padding: 3rem 1rem 2rem;
        }
        .rp-completion-icon {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, #059669, var(--green));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.75rem;
            box-shadow: 0 8px 28px rgba(16,185,129,0.35);
        }
        .rp-completion h2 {
            font-family: var(--font-display);
            font-size: 2rem;
            font-weight: 400;
            color: var(--navy);
            margin-bottom: 0.5rem;
        }
        .rp-completion p { color: var(--muted); margin-bottom: 2rem; font-size: 0.95rem; }
        .rp-completion-actions {
            display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;
        }

        /* ── PREVIEW PANEL ── */
        .rp-preview-panel {
            position: sticky;
            top: 2rem;
            animation: fadeUp 0.5s var(--ease-out) 0.12s both;
        }

        .rp-preview-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 0.875rem;
            background: white;
            padding: 0.7rem 1.2rem;
            border-radius: var(--r-lg);
            border: 1px solid var(--border-md);
            box-shadow: var(--shadow-xs);
        }
        .rp-preview-label {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 0.68rem;
            font-weight: 800;
            color: var(--soft);
            text-transform: uppercase;
            letter-spacing: 0.1em;
        }
        .rp-preview-label-dot {
            width: 7px; height: 7px;
            background: var(--green);
            border-radius: 50%;
            box-shadow: 0 0 0 3px rgba(16,185,129,0.15);
        }

        #change-template-btn {
            display: flex; align-items: center; gap: 0.5rem;
            padding: 0.4rem 0.9rem;
            background: white;
            border: 1.5px solid var(--border-md);
            border-radius: var(--r-full);
            font-family: var(--font-body);
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--ink);
            cursor: pointer;
            transition: all 0.2s var(--ease-spring);
        }
        #change-template-btn:hover {
            border-color: var(--blue);
            color: var(--blue);
            background: var(--blue-light);
        }

        .rp-zoom-controls {
            display: flex;
            align-items: center;
            gap: 2px;
            background: var(--surface-2);
            padding: 3px;
            border-radius: var(--r-sm);
        }
        .rp-zoom-btn {
            width: 26px; height: 26px;
            border: none;
            background: transparent;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--muted);
            display: flex; align-items: center; justify-content: center;
            transition: all 0.15s;
            font-family: var(--font-body);
        }
        .rp-zoom-btn:hover { background: white; color: var(--ink); }
        #preview-zoom-level {
            font-size: 0.7rem;
            font-weight: 800;
            color: var(--muted);
            width: 34px;
            text-align: center;
        }

        .rp-viewport {
            background: #d8dde8;
            border-radius: var(--r-xl);
            padding: 2.5rem 1.5rem;
            height: 82vh;
            overflow: auto;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            box-shadow: inset 0 2px 12px rgba(0,0,0,0.08);
        }
        .rp-viewport::-webkit-scrollbar { width: 5px; }
        .rp-viewport::-webkit-scrollbar-track { background: transparent; }
        .rp-viewport::-webkit-scrollbar-thumb {
            background: rgba(0,0,0,0.15);
            border-radius: 3px;
        }

        #cv-preview {
            width: 794px;
            background: white;
            box-shadow: var(--shadow-xl);
            border-radius: 2px;
            transform-origin: top center;
            transition: transform 0.3s var(--ease-spring);
        }

        /* ── TEMPLATE POPUP ── */
        /* ── Popup overlay ── */
.rp-popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(11,18,33,0.6);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    z-index: 1000;
    display: none;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    animation: popOverlayIn 0.2s ease both;
}
.rp-popup-overlay.visible { display: flex; }
 
@keyframes popOverlayIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}
 
/* ── Popup dialog ── */
.rp-popup {
    background: white;
    border-radius: 24px;
    width: 100%;
    max-width: 1020px;
    max-height: 88vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    box-shadow: 0 32px 80px rgba(0,0,0,0.22);
    animation: popDialogIn 0.25s var(--ease-spring, cubic-bezier(0.175,0.885,0.32,1.275)) both;
}
@keyframes popDialogIn {
    from { opacity: 0; transform: scale(0.95) translateY(12px); }
    to   { opacity: 1; transform: scale(1)    translateY(0); }
}
 
.rp-popup-head {
    padding: 1.375rem 1.75rem;
    border-bottom: 1px solid rgba(0,0,0,0.07);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
}
.rp-popup-head h3 {
    font-family: var(--font-display, serif);
    font-size: 1.5rem;
    font-weight: 400;
    color: var(--navy, #0b1221);
    letter-spacing: -0.02em;
}
.rp-popup-close {
    width: 34px; height: 34px;
    border: none;
    background: #f1f5f9;
    border-radius: 50%;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    color: #64748b;
    transition: background 0.18s, color 0.18s;
    flex-shrink: 0;
}
.rp-popup-close:hover { background: #e2e8f0; color: #0b1221; }
 
.rp-popup-body {
    padding: 1.75rem;
    overflow-y: auto;
    /* nice scrollbar */
    scrollbar-width: thin;
    scrollbar-color: rgba(0,0,0,0.12) transparent;
}
.rp-popup-body::-webkit-scrollbar { width: 5px; }
.rp-popup-body::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.12); border-radius: 3px; }
 
/* ── Template grid ── */
.rp-tpl-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 1.25rem;
}
 
/* ── Individual template card ── */
.rp-tpl-card {
    border: 2px solid rgba(0,0,0,0.08);
    border-radius: 16px;
    overflow: hidden;           /* ← CRITICAL: clips the scaled preview */
    cursor: pointer;
    transition: border-color 0.2s, transform 0.2s, box-shadow 0.2s;
    background: white;
    display: flex;
    flex-direction: column;
}
.rp-tpl-card:hover {
    border-color: var(--blue, #2563eb);
    transform: translateY(-4px);
    box-shadow: 0 12px 32px rgba(37,99,235,0.14);
}
.rp-tpl-card.selected {
    border-color: var(--blue, #2563eb);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
}
 
/* ── Preview thumbnail ── */
.rp-tpl-thumb {
    position: relative;
    width: 100%;
    height: 240px;          /* fixed height — preview is scaled to fit inside */
    overflow: hidden;       /* ← clips anything outside */
    background: #f8fafc;
    flex-shrink: 0;
}
 
/* The actual resume content, scaled down to fit */
.rp-tpl-thumb-inner {
    position: absolute;
    top: 0;
    left: 0;
    width: 794px;           /* A4 width in px */
    /* scale so 794px fits in ~210px card width with a little padding:
       210 / 794 ≈ 0.264 — we use 0.26 for a touch of margin */
    transform: scale(0.26);
    transform-origin: top left;
    pointer-events: none;
    background: white;
    /* prevent template's own fonts / colours leaking */
    font-size: 14px;
    line-height: 1.4;
}
 
/* Selected checkmark badge */
.rp-tpl-check {
    display: none;
    position: absolute;
    top: 10px; right: 10px;
    width: 28px; height: 28px;
    background: var(--blue, #2563eb);
    border-radius: 50%;
    align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(37,99,235,0.35);
    z-index: 2;
}
.rp-tpl-card.selected .rp-tpl-check { display: flex; }
 
/* ── Name footer ── */
.rp-tpl-name {
    padding: 0.875rem 1rem;
    font-size: 0.82rem;
    font-weight: 700;
    color: var(--ink, #1e293b);
    text-align: center;
    border-top: 1px solid rgba(0,0,0,0.06);
    background: white;
    flex-shrink: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: color 0.18s;
}
.rp-tpl-card:hover .rp-tpl-name,
.rp-tpl-card.selected .rp-tpl-name { color: var(--blue, #2563eb); }

        /* ── ANIMATIONS — same names as home ── */
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(37,99,235,0.4); }
            70%  { box-shadow: 0 0 0 8px rgba(37,99,235,0); }
            100% { box-shadow: 0 0 0 0 rgba(37,99,235,0); }
        }
    </style>

    <div class="rp-page">

        {{-- Top Bar --}}
        <div class="rp-topbar">
            <div>
                <div class="rp-section-label">Resume Builder</div>
                <h1 class="rp-hero-title">Build your <em>dream resume</em></h1>
                <p class="rp-hero-sub">ATS-friendly. Professional. Done in minutes.</p>
            </div>
            
        </div>

        <div class="rp-grid">

            {{-- ═══════════════════════════════════
                 FORM PANEL
            ═══════════════════════════════════ --}}
            <section class="rp-form-panel">

                <select id="template-id" style="display:none">
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}" @selected((string) $selectedTemplateId === (string) $template->id)>{{ $template->name }}</option>
                    @endforeach
                </select>

                {{-- Step Nav --}}
                <nav class="rp-step-nav">
                    <button class="rp-step-tab active" data-step="1">
                        <span class="rp-step-icon">1</span>Basics
                    </button>
                    <button class="rp-step-tab" data-step="2">
                        <span class="rp-step-icon">2</span>Experience
                    </button>
                    <button class="rp-step-tab" data-step="3">
                        <span class="rp-step-icon">3</span>Education
                    </button>
                    <button class="rp-step-tab" data-step="4">
                        <span class="rp-step-icon">4</span>Projects
                    </button>
                </nav>

                <div class="rp-form-body">

                    {{-- ────────────── STEP 1 ────────────── --}}
                    <div class="rp-step-content active" data-step="1">

                        {{-- Upload toggle --}}
                        <div class="rp-source-box">
                            <span class="rp-source-label">Already have a resume?</span>
                            <div class="source-group">
                                <button type="button" data-source="upload" class="source-btn">Upload &amp; Autofill</button>
                                <button type="button" data-source="manual" class="source-btn active">Start Fresh</button>
                            </div>
                            <div id="existing-resume-panel" class="rp-upload-panel">
                                <input id="resume-autofill-file" type="file" accept=".pdf,.doc,.docx">
                                <div class="rp-dropzone" id="rp-dropzone-trigger">
                                    <div class="rp-dropzone-icon">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                    </div>
                                    <div class="rp-dropzone-text">
                                        <strong id="rp-file-name">Click to upload your resume</strong>
                                        <span>PDF, DOC, or DOCX — AI will autofill your info</span>
                                    </div>
                                    <button id="resume-autofill-button" class="rp-btn rp-btn-primary rp-btn-sm" style="margin-left:auto;flex-shrink:0;">
                                        Autofill
                                    </button>
                                </div>
                                <p id="resume-autofill-status"></p>
                            </div>
                        </div>

                        {{-- Contact fields --}}
                        <p class="rp-fields-subhead">Contact Information</p>

                        <div class="rp-field-row">
                            <div class="field-group">
                                <label class="field-label">Full Name</label>
                                <input id="cv-name" class="rp-input cv-field" placeholder="Jane Smith" data-field="name">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Email</label>
                                <input id="cv-email" class="rp-input cv-field" type="email" placeholder="jane@example.com" data-field="email">
                            </div>
                        </div>

                        <div class="rp-field-row">
                            <div class="field-group">
                                <label class="field-label">Phone</label>
                                <input id="cv-mobile" class="rp-input cv-field" placeholder="+91 98765 43210" data-field="mobile">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Location</label>
                                <input id="cv-location" class="rp-input cv-field" placeholder="City, Country" data-field="location">
                            </div>
                        </div>

                        <div class="rp-field-row single">
                            <div class="field-group">
                                <label class="field-label">
                                    Social Links
                                    <span class="field-hint">(comma separated)</span>
                                </label>
                                <input id="cv-social" class="rp-input cv-field" placeholder="linkedin.com/in/jane, github.com/jane" data-field="social_links">
                            </div>
                        </div>

                        <div class="rp-fields-divider"></div>

                        {{-- Profile fields --}}
                        <p class="rp-fields-subhead">Profile</p>

                        <div class="rp-field-row single">
                            <div class="field-group">
                                <label class="field-label">Professional Summary</label>
                                <textarea id="cv-summary" class="rp-input cv-field" rows="4" placeholder="Briefly describe your background, key skills, and career goals…" data-field="summary"></textarea>
                            </div>
                        </div>

                        <div class="rp-field-row single">
                            <div class="field-group">
                                <label class="field-label">
                                    Skills
                                    <span class="field-hint">(comma separated)</span>
                                </label>
                                <input id="cv-skills" class="rp-input cv-field" placeholder="Python, Figma, React, SQL, Leadership…" data-field="skills">
                            </div>
                        </div>

                        <div class="rp-step-footer">
                            <span></span>
                            <button id="next-step-1" class="rp-btn rp-btn-primary">
                                Next: Experience
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- ────────────── STEP 2 ────────────── --}}
                    <div class="rp-step-content" data-step="2">
                        <div class="rp-section-head">
                            <h3>Work Experience</h3>
                            <button id="add-exp" class="rp-btn rp-btn-ghost">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                                Add Position
                            </button>
                        </div>
                        <div id="exp-editor"></div>
                        <div class="rp-step-footer">
                            <button id="prev-step-2" class="rp-btn rp-btn-outline">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
                                Back
                            </button>
                            <button id="next-step-2" class="rp-btn rp-btn-primary">
                                Next: Education
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- ────────────── STEP 3 ────────────── --}}
                    <div class="rp-step-content" data-step="3">
                        <div class="rp-section-head">
                            <h3>Education</h3>
                            <button id="add-edu" class="rp-btn rp-btn-ghost">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                                Add Education
                            </button>
                        </div>
                        <div id="edu-editor"></div>
                        <div class="rp-step-footer">
                            <button id="prev-step-3" class="rp-btn rp-btn-outline">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
                                Back
                            </button>
                            <button id="next-step-3" class="rp-btn rp-btn-primary">
                                Next: Projects
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- ────────────── STEP 4 ────────────── --}}
                    <div class="rp-step-content" data-step="4">
                        <div class="rp-section-head">
                            <h3>Projects</h3>
                            <button id="add-project" class="rp-btn rp-btn-ghost">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                                Add Project
                            </button>
                        </div>
                        <div id="project-editor"></div>
                        <div class="rp-step-footer">
                            <button id="prev-step-4" class="rp-btn rp-btn-outline">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15 19l-7-7 7-7"/></svg>
                                Back
                            </button>
                            <button id="save-cv" class="rp-btn rp-btn-finalize">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                                Finalize Resume
                            </button>
                        </div>
                    </div>

                    {{-- Completion --}}
                    <div id="completion-panel" style="display:none;">
                        <div class="rp-completion">
                            <div class="rp-completion-icon">
                                <svg width="32" height="32" fill="none" stroke="white" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <h2>Ready to Download!</h2>
                            <p>Your resume has been saved to your account.</p>
                            <div class="rp-completion-actions">
                                <button id="download-pdf" class="rp-btn rp-btn-primary" style="padding:.9rem 2rem;font-size:1rem;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15l-3-3m0 0l3-3m-3 3h12M3 17v2a2 2 0 002 2h14a2 2 0 002-2v-2"/></svg>
                                    Download PDF
                                </button>
                                <button id="edit-resume" class="rp-btn rp-btn-outline" style="padding:.9rem 2rem;font-size:1rem;">Back to Edit</button>
                            </div>
                        </div>
                    </div>

                </div>{{-- /rp-form-body --}}

                <div class="rp-status-bar">
                    <p id="cv-status"></p>
                </div>
            </section>

            {{-- ═══════════════════════════════════
                 PREVIEW PANEL
            ═══════════════════════════════════ --}}
            <section class="rp-preview-panel">
                <div class="rp-preview-toolbar">
                    <div class="rp-preview-label">
                        
                        Live Preview
                    </div>
                    <div style="display:flex;align-items:center;gap:0.75rem;">
                        <button id="change-template-btn">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                            Switch Style
                        </button>
                        <div class="rp-zoom-controls">
                            <button id="preview-zoom-out" class="rp-zoom-btn">−</button>
                            <span id="preview-zoom-level">75%</span>
                            <button id="preview-zoom-in" class="rp-zoom-btn">+</button>
                        </div>
                    </div>
                </div>

                <div class="rp-viewport" id="preview-viewport">
                    <article id="cv-preview" class="resume-maker-preview"></article>
                </div>
            </section>

        </div>{{-- /rp-grid --}}
    </div>{{-- /rp-page --}}

    {{-- Template Popup --}}
    <div class="rp-popup-overlay" id="template-popup">
        <div class="rp-popup">
            <div class="rp-popup-head">
                <h3>Choose a Design</h3>
                <button id="close-template-popup" class="rp-popup-close" aria-label="Close">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div class="rp-popup-body">
                <div class="rp-tpl-grid" id="template-grid"></div>
            </div>
        </div>
    </div>

    <script>
        // Dropzone click-to-upload
        document.getElementById('rp-dropzone-trigger')?.addEventListener('click', function(e) {
            if (!e.target.closest('#resume-autofill-button')) {
                document.getElementById('resume-autofill-file').click();
            }
        });
        document.getElementById('resume-autofill-file')?.addEventListener('change', function() {
            const name = this.files[0]?.name;
            if (name) document.getElementById('rp-file-name').textContent = name;
        });

        // Scale preview
        window.addEventListener('load', () => {
            const viewport = document.getElementById('preview-viewport');
            const preview  = document.getElementById('cv-preview');
            function updateScale() {
                if (!viewport || !preview) return;
                const scale = Math.min((viewport.clientWidth - 56) / 794, 0.85);
                preview.style.transform    = `scale(${scale})`;
                preview.style.marginBottom = `-${1123 * (1 - scale)}px`;
                document.getElementById('preview-zoom-level').textContent = Math.round(scale * 100) + '%';
            }
            window.addEventListener('resize', updateScale);
            updateScale();
            const obs = new MutationObserver(updateScale);
            if (preview) obs.observe(preview, { childList: true, subtree: true, characterData: true });
        });
    </script>

@include('resume.partials.editor-script')
@endsection
