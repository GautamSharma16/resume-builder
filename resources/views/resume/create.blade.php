@extends('layouts.bare') {{-- use a layout with NO navbar/footer --}}

@section('title', 'Resume Maker | Cvbliss')

@section('content')

<div id="create-cv-app"
    class="rp-root"
    data-store-url="{{ route('resume.store') }}"
    data-analyze-url="{{ route('resume.analyze') }}"
    data-login-url="{{ route('login') }}"
    data-ai-text-url="{{ route('resume.ai-text') }}"
    data-authenticated="{{ auth()->check() ? '1' : '0' }}"
    data-download-requires-plan="{{ auth()->check() && ! auth()->user()->activeSubscription?->hasDownloadsRemaining() ? '1' : '0' }}"
    @if($selectedTemplateId) data-selected-template="{{ $selectedTemplateId }}" @endif>

    <script type="application/json" id="resume-templates-json">@json($templates->keyBy('id'))</script>
    <script type="application/json" id="resume-initial-json">@json($initialResume ?? [])</script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Bricolage+Grotesque:opsz,wght@12..96,300;12..96,400;12..96,500;12..96,600;12..96,700;12..96,800&display=swap');

        :root {
            --blue:         #3b82f6;
            --blue-dark:    #2563eb;
            --blue-light:   #eff6ff;
            --navy:         #0f172a;
            --ink:          #334155;
            --muted:        #64748b;
            --soft:         #94a3b8;
            --surface:      #f8fafc;
            --border:       #e2e8f0;
            --white:        #ffffff;
            --green:        #22c55e;
            --pink:         #ec4899;
            --font-display: 'DM Serif Display', serif;
            --font-body:    'Bricolage Grotesque', sans-serif;
            --r-sm:  6px;
            --r-md:  8px;
            --r-lg:  12px;
            --r-xl:  16px;
            --r-2xl: 24px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            overflow: hidden; /* no page scroll — panels scroll internally */
        }

        body {
            font-family: var(--font-body);
            background: var(--surface);
            color: var(--ink);
            -webkit-font-smoothing: antialiased;
        }

        /* ══════════════════════════════════════════
           ROOT WRAPPER
        ══════════════════════════════════════════ */
        .rp-root {
            height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--surface);
        }

        /* ══════════════════════════════════════════
           MINIMAL TOP BAR (branding only, no nav)
        ══════════════════════════════════════════ */
        .rp-topbar {
            flex-shrink: 0;
            height: 56px;
            background: var(--white);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            gap: 0.75rem;
            z-index: 10;
        }
        .rp-topbar-logo {
            font-family: var(--font-display);
            font-size: 1.25rem;
            color: var(--navy);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .rp-topbar-logo svg { color: var(--blue); }
        .rp-topbar-sep { flex: 1; }
        .rp-topbar-step-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--muted);
            background: var(--blue-light);
            padding: 0.3rem 0.75rem;
            border-radius: 999px;
            display: none; /* shown on mobile */
        }

        /* ══════════════════════════════════════════
           ONBOARDING
        ══════════════════════════════════════════ */
        .rp-onboarding {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            animation: rpFadeIn 0.4s ease-out;
        }
        .rp-onboarding h1 {
            font-family: var(--font-display);
            font-size: clamp(1.75rem, 4vw, 2.75rem);
            color: var(--navy);
            margin-bottom: 0.75rem;
            font-weight: 400;
            text-align: center;
        }
        .rp-onboarding .ob-sub {
            color: var(--muted);
            font-size: 1rem;
            margin-bottom: 3rem;
            text-align: center;
        }
        .ob-cards {
            display: flex;
            gap: 1.5rem;
            flex-wrap: wrap;
            justify-content: center;
            width: 100%;
            max-width: 760px;
        }
        .ob-card {
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: var(--r-2xl);
            padding: 2.5rem 2rem;
            flex: 1;
            min-width: 280px;
            max-width: 340px;
            cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }
        .ob-card:hover {
            border-color: var(--blue);
            box-shadow: 0 12px 32px -4px rgba(59,130,246,0.14);
            transform: translateY(-4px);
        }
        .ob-icon {
            width: 72px; height: 72px;
            background: var(--blue-light);
            border-radius: var(--r-xl);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .ob-card h3 { font-size: 1.05rem; font-weight: 700; color: var(--navy); margin-bottom: 0.5rem; }
        .ob-card p  { font-size: 0.875rem; color: var(--muted); line-height: 1.6; }

        /* ══════════════════════════════════════════
           BUILDER LAYOUT
        ══════════════════════════════════════════ */
        .rp-builder {
            display: none; /* shown via JS */
            flex: 1;
            overflow: hidden;
            padding: 1.25rem 1.5rem;
            gap: 1.5rem;
            grid-template-columns: 1fr 1fr;
        }
        .rp-builder.visible { display: grid; }
        
        .rp-builder.step-6-active .change-tpl-btn {
            display: none !important;
        }

        /* ── LEFT: Form Panel ── */
        .rp-form-panel {
            background: var(--white);
            border-radius: var(--r-xl);
            border: 1px solid var(--border);
            box-shadow: 0 4px 16px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 0;
        }

        /* Step Nav */
        .rp-step-nav {
            flex-shrink: 0;
            display: flex;
            align-items: flex-end;
            padding: 1.25rem 1.75rem 0;
            position: relative;
            background: var(--white);
        }
        .rp-step-nav::after {
            content: '';
            position: absolute;
            bottom: 7px; left: 1.75rem; right: 1.75rem;
            height: 2px;
            background: var(--blue-light);
            z-index: 1;
        }
        .rp-step-tab {
            background: transparent;
            border: none;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            z-index: 2;
            position: relative;
            padding-bottom: 14px;
            flex: 1;
            transition: opacity 0.2s;
        }
        .rp-step-name {
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--soft);
            transition: color 0.2s;
            white-space: nowrap;
        }
        .rp-step-dot {
            width: 14px; height: 14px;
            border-radius: 50%;
            background: var(--white);
            border: 2px solid var(--border);
            transition: all 0.25s;
            position: absolute;
            bottom: 0px;
            box-shadow: 0 0 0 3px var(--white);
        }
        .rp-step-tab.active   .rp-step-name { color: var(--blue); }
        .rp-step-tab.active   .rp-step-dot  { border-color: var(--blue); border-width: 4px; }
        .rp-step-tab.done     .rp-step-name { color: var(--blue); }
        .rp-step-tab.done     .rp-step-dot  { border-color: var(--blue); background: var(--blue); }

        /* Form Body */
        .rp-form-body {
            flex: 1;
            overflow-y: auto;
            padding: 1.75rem 1.75rem 1rem;
            min-height: 0;
        }
        .rp-form-body::-webkit-scrollbar { width: 6px; }
        .rp-form-body::-webkit-scrollbar-track { background: transparent; }
        .rp-form-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 999px; }

        .rp-step-pane { display: none; }
        .rp-step-pane.active { display: block; animation: rpFadeIn 0.25s ease-out; }

        /* Step header */
        .step-head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
            gap: 1rem;
        }
        .step-head h2 {
            font-family: var(--font-display);
            font-size: 1.75rem;
            color: var(--navy);
            font-weight: 400;
            line-height: 1.2;
        }
        .step-head p {
            color: var(--muted);
            font-size: 0.875rem;
            margin-top: 0.3rem;
            line-height: 1.5;
        }

        /* Tips button */
        .tips-btn {
            flex-shrink: 0;
            background: #fffbeb;
            color: #b45309;
            border: 1px solid #fde68a;
            padding: 0.4rem 0.9rem;
            border-radius: var(--r-md);
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-family: var(--font-body);
            transition: background 0.15s;
        }
        .tips-btn:hover { background: #fef3c7; }

        /* Fields */
        .rp-row       { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; margin-bottom: 1.1rem; }
        .rp-row.full  { grid-template-columns: 1fr; }
        .rp-group     { display: flex; flex-direction: column; gap: 0.35rem; }

        .rp-label {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--ink);
            letter-spacing: 0.01em;
        }
        .rp-hint {
            font-size: 0.72rem;
            font-weight: 400;
            color: var(--soft);
        }

        .rp-input-wrap { position: relative; }

        .rp-input {
            width: 100%;
            padding: 0.7rem 2.25rem 0.7rem 0.9rem;
            border: 1.5px solid var(--border);
            border-radius: var(--r-md);
            font-family: var(--font-body);
            font-size: 0.9rem;
            color: var(--navy);
            background: #fcfcfc;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            outline: none;
        }
        .rp-input:focus {
            border-color: var(--blue);
            background: var(--white);
            box-shadow: 0 0 0 3px var(--blue-light);
        }
        .rp-input:not(:placeholder-shown):not(:focus) {
            border-color: #86efac;
            background: #f0fdf4;
        }
        .rp-input:not(:placeholder-shown) ~ .rp-valid { display: flex; }

        .rp-valid {
            position: absolute;
            right: 10px; top: 50%;
            transform: translateY(-50%);
            color: var(--green);
            display: none;
            align-items: center;
            pointer-events: none;
        }

        textarea.rp-input {
            resize: vertical;
            min-height: 100px;
            padding-right: 0.9rem;
            line-height: 1.6;
        }

        /* Rich text wrapper */
        .rich-wrap {
            border: 1.5px solid var(--blue-light);
            border-radius: var(--r-md);
            overflow: hidden;
            background: var(--white);
            box-shadow: 0 0 0 2px var(--blue-light);
        }
        .summary-editor-shell {
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            overflow: hidden;
            background: var(--white);
            box-shadow: 0 1px 4px rgba(15,23,42,0.04);
        }
        .rich-toolbar {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.6rem;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
        }
        .rich-text-wrapper {
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            overflow: hidden;
            background: var(--white);
        }
        .rich-text-toolbar {
            display: flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.45rem 0.6rem;
            border-bottom: 1px solid var(--border);
            background: var(--surface);
        }
        .rt-btn {
            background: transparent;
            border: none;
            color: var(--ink);
            cursor: pointer;
            padding: 0.25rem 0.4rem;
            border-radius: 4px;
            font-size: 0.85rem;
            font-family: var(--font-body);
            transition: background 0.15s;
        }
        .rt-btn:hover { background: var(--border); }
        .ai-btn {
            margin-left: auto;
            background: #818cf8;
            color: white;
            border: none;
            padding: 0.35rem 0.8rem;
            border-radius: var(--r-md);
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-family: var(--font-body);
            transition: background 0.2s, transform 0.15s;
        }
        .ai-btn:hover { background: #6366f1; transform: scale(1.02); }
        .ai-btn:active { transform: scale(0.98); }
        .ai-btn.loading { opacity: 0.75; pointer-events: none; }
        .ai-gen-btn {
            margin-left: auto;
            background: linear-gradient(135deg, #8b5cf6, #38bdf8);
            color: white;
            border: none;
            padding: 0.35rem 0.8rem;
            border-radius: var(--r-md);
            font-size: 0.78rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.35rem;
            font-family: var(--font-body);
            transition: opacity 0.2s, transform 0.15s;
        }
        .ai-gen-btn:hover { transform: scale(1.02); }
        .ai-gen-btn.loading { opacity: 0.75; pointer-events: none; }
        .rich-ta {
            border: none;
            border-radius: 0;
            box-shadow: none !important;
            min-height: 110px;
            padding: 0.9rem;
            background: var(--white);
        }
        .summary-ta {
            min-height: 132px;
            background: #f8fafc;
        }
        .suggested-summary {
            margin-top: 1.1rem;
            border: 1px solid var(--border);
            border-radius: var(--r-md);
            background: #f8fafc;
            padding: 1rem;
        }
        .suggested-summary h4 {
            font-size: 0.86rem;
            color: var(--ink);
            font-weight: 800;
            margin-bottom: 0.15rem;
        }
        .suggested-summary h4 span { color: var(--blue); }
        .suggested-summary p {
            font-size: 0.78rem;
            color: var(--soft);
            margin-bottom: 0.9rem;
        }
        .summary-suggestions-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }
        .summary-suggestion {
            border: 1px dashed #d8e2ef;
            border-radius: var(--r-md);
            background: var(--white);
            padding: 0.75rem 0.85rem;
            text-align: left;
            cursor: pointer;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 0.55rem;
            color: var(--ink);
            font-family: var(--font-body);
            line-height: 1.45;
        }
        .summary-suggestion:hover {
            border-color: var(--blue);
            box-shadow: 0 4px 12px rgba(59,130,246,0.08);
        }
        .summary-suggestion .plus {
            width: 16px;
            height: 16px;
            border-radius: 999px;
            background: var(--blue);
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.82rem;
            font-weight: 800;
            margin-top: 0.1rem;
        }

        /* Entry cards (experience / projects) */
        .rp-entry-card {
            background: var(--white);
            border: 1.5px solid var(--border);
            border-radius: var(--r-xl);
            padding: 1.25rem 1.375rem 1rem;
            margin-bottom: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .rp-entry-card:hover {
            border-color: rgba(59,130,246,0.3);
            box-shadow: 0 4px 18px rgba(0,0,0,0.05);
        }
        .rp-entry-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.875rem;
            margin-bottom: 0.875rem;
        }
        .rp-entry-field {
            display: flex;
            flex-direction: column;
            gap: 0.3rem;
            margin-bottom: 0.875rem;
        }
        .rp-entry-field:last-of-type { margin-bottom: 0; }

        .rp-entry-remove {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            margin-top: 0.75rem;
            padding: 0.35rem 0.875rem;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
            color: var(--muted);
            background: var(--surface);
            border: 1.5px solid var(--border);
            cursor: pointer;
            font-family: var(--font-body);
            transition: all 0.2s;
        }
        .rp-entry-remove:hover { background: #fee2e2; color: #dc2626; border-color: rgba(220,38,38,0.2); }

        /* Education rows */
        .rp-edu-row {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            margin-bottom: 0.75rem;
        }
        .rp-edu-row .rp-input { flex: 1; }
        .rp-edu-del {
            flex-shrink: 0;
            width: 34px; height: 34px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 50%;
            border: 1.5px solid var(--border);
            background: var(--surface);
            color: var(--muted);
            cursor: pointer;
            transition: all 0.2s;
        }
        .rp-edu-del:hover { background: #fee2e2; color: #dc2626; border-color: rgba(220,38,38,0.2); }

        /* Add button */
        .rp-add-btn {
            width: 100%;
            padding: 0.7rem;
            border: 1.5px dashed var(--border);
            border-radius: var(--r-md);
            background: transparent;
            color: var(--muted);
            font-family: var(--font-body);
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
        }
        .rp-add-btn:hover { border-color: var(--blue); color: var(--blue); background: var(--blue-light); }

        /* Form footer */
        .rp-form-footer {
            flex-shrink: 0;
            padding: 1rem 1.75rem;
            border-top: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--white);
        }
        .btn {
            padding: 0.7rem 1.5rem;
            border-radius: var(--r-md);
            font-size: 0.875rem;
            font-weight: 700;
            cursor: pointer;
            font-family: var(--font-body);
            transition: all 0.2s;
            border: 1.5px solid var(--border);
            background: var(--white);
            color: var(--ink);
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        .btn:hover { background: var(--surface); }
        .btn.primary {
            background: var(--blue);
            color: var(--white);
            border-color: var(--blue);
        }
        .btn.primary:hover { background: var(--blue-dark); }
        .btn.primary:active { transform: scale(0.98); }

        /* Finalize CTA */
        .finalize-wrap {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem 2rem;
            text-align: center;
            gap: 1rem;
        }
        .finalize-wrap h3 {
            font-family: var(--font-display);
            font-size: 1.75rem;
            color: var(--navy);
            font-weight: 400;
        }
        .finalize-wrap p { color: var(--muted); font-size: 0.9rem; }

        /* ── RIGHT: Preview Panel ── */
        .rp-preview-panel {
            display: flex;
            flex-direction: column;
            min-height: 0;
            gap: 0.75rem;
        }
        .preview-topbar {
            flex-shrink: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .score-badge {
            background: var(--white);
            border: 1px solid var(--border);
            padding: 0.45rem 1rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--navy);
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
        }
        .score-pill {
            background: var(--green);
            color: var(--white);
            padding: 0.15rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
        }
        .change-tpl-btn {
            background: var(--white);
            border: 1.5px solid var(--border);
            padding: 0.45rem 1rem;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            color: var(--navy);
            font-family: var(--font-body);
            box-shadow: 0 1px 4px rgba(0,0,0,0.06);
            transition: background 0.15s, box-shadow 0.15s;
        }
        .change-tpl-btn:hover { background: var(--surface); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }

        .rp-viewport {
            flex: 1;
            background: #e5e9f0;
            border-radius: var(--r-xl);
            overflow: auto;
            padding: 1.5rem;
            min-height: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        .rp-viewport::-webkit-scrollbar { width: 6px; }
        .rp-viewport::-webkit-scrollbar-track { background: transparent; }
        .rp-viewport::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.18); border-radius: 999px; }

        #cv-preview {
            background: var(--white);
            width: 794px;
            min-height: 1123px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            transform-origin: top center;
            flex-shrink: 0;
            margin: auto; /* safe centering for flex overflow */
        }

        /* ══════════════════════════════════════════
           TEMPLATE POPUP
        ══════════════════════════════════════════ */
        .rp-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15,23,42,0.55);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .rp-overlay.open { display: flex; }
        .rp-popup {
            background: var(--white);
            border-radius: var(--r-2xl);
            width: 100%;
            max-width: 1020px;
            max-height: 88vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 24px 64px rgba(0,0,0,0.18);
        }
        .rp-popup-head {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .rp-popup-head h3 {
            font-family: var(--font-display);
            font-size: 1.4rem;
            color: var(--navy);
            font-weight: 400;
        }
        .rp-popup-close {
            width: 36px; height: 36px;
            border: none;
            background: var(--surface);
            border-radius: 50%;
            cursor: pointer;
            color: var(--muted);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.15s;
        }
        .rp-popup-close:hover { background: var(--border); }
        .rp-popup-body { padding: 1.5rem; overflow-y: auto; flex: 1; }
        .rp-tpl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1.25rem; }
        .rp-tpl-card {
            border: 2px solid var(--border);
            border-radius: var(--r-xl);
            overflow: hidden;
            cursor: pointer;
            background: var(--white);
            display: flex;
            flex-direction: column;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .rp-tpl-card:hover, .rp-tpl-card.selected {
            border-color: var(--blue);
            box-shadow: 0 0 0 3px var(--blue-light);
        }
        .rp-tpl-thumb { position: relative; width: 100%; height: 230px; overflow: hidden; background: var(--surface); }
        .rp-tpl-thumb-inner {
            position: absolute; top: 0; left: 0; width: 794px;
            transform: scale(0.255); transform-origin: top left;
            pointer-events: none;
        }
        .rp-tpl-name {
            padding: 0.7rem;
            font-size: 0.82rem;
            font-weight: 700;
            text-align: center;
            border-top: 1px solid var(--border);
            color: var(--ink);
        }

        /* Image upload */
        .img-upload-wrap {
            display: flex;
            gap: 1rem;
            align-items: center;
            padding: 1rem;
            border: 1.5px dashed var(--border);
            border-radius: var(--r-md);
            background: var(--surface);
        }
        .img-preview-circle {
            width: 56px; height: 56px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--border);
            display: none;
        }

        /* Animations */
        @keyframes rpFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

        /* Scrollbar for popup body */
        .rp-popup-body::-webkit-scrollbar { width: 6px; }
        .rp-popup-body::-webkit-scrollbar-track { background: transparent; }
        .rp-popup-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 999px; }

        /* ══════════════════════════════════════════
           RESPONSIVE
        ══════════════════════════════════════════ */
        @media (max-width: 900px) {
            .rp-builder { grid-template-columns: 1fr; overflow-y: auto; height: auto; }
            html, body { overflow: auto; }
            .rp-preview-panel { min-height: 60vh; }
            .rp-entry-row { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .rp-row { grid-template-columns: 1fr; }
            .rp-step-name { font-size: 0.62rem; }
        }
    </style>

    

    {{-- ══ ONBOARDING ══ --}}
    <div id="rp-onboarding-view" class="rp-onboarding">
        <h1>How will you make your resume?</h1>
        <p class="ob-sub">Choose how you'd like to get started</p>
        <div class="ob-cards">
            <div class="ob-card" onclick="document.getElementById('resume-autofill-file').click()">
                <div class="ob-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="16 16 12 12 8 16"/><line x1="12" y1="12" x2="12" y2="21"/>
                        <path d="M20.39 18.39A5 5 0 0 0 18 9h-1.26A8 8 0 1 0 3 16.3"/>
                    </svg>
                </div>
                <h3>I already have a resume</h3>
                <p>Upload your existing resume to make quick edits</p>
                <input id="resume-autofill-file" type="file" accept=".pdf,.doc,.docx" style="display:none">
            </div>
            <div class="ob-card" onclick="startFromScratch()">
                <div class="ob-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/>
                    </svg>
                </div>
                <h3>Start from scratch</h3>
                <p>Our AI will guide you through creating a resume</p>
            </div>
        </div>
        <p id="resume-autofill-status" style="margin-top:2rem;color:var(--muted);font-size:0.875rem;"></p>
    </div>

    {{-- ══ BUILDER ══ --}}
    <div id="rp-builder-view" class="rp-builder">

        {{-- Hidden template selector --}}
        <select id="template-id" style="display:none">
            @foreach($templates as $template)
                <option value="{{ $template->id }}" @selected((string) $selectedTemplateId === (string) $template->id)>{{ $template->name }}</option>
            @endforeach
        </select>

        {{-- ── LEFT PANEL ── --}}
        <section class="rp-form-panel">

            <nav class="rp-step-nav">
                @foreach(['Contacts','Experience','Education','Skills','Summary','Finalize'] as $i => $label)
                    <button class="rp-step-tab {{ $i === 0 ? 'active' : '' }}" data-step="{{ $i + 1 }}">
                        <span class="rp-step-name">{{ $label }}</span>
                        <div class="rp-step-dot"></div>
                    </button>
                @endforeach
            </nav>

            <div class="rp-form-body">

                {{-- ── STEP 1: Contacts ── --}}
                <div class="rp-step-pane active" data-step="1">
                    <div class="step-head">
                        <div>
                            <h2>Contacts</h2>
                            <p>Add your up-to-date contact information so employers can easily reach you.</p>
                        </div>
                    </div>

                    <div class="rp-row">
                        <div class="rp-group">
                            <label class="rp-label">First name</label>
                            <div class="rp-input-wrap">
                                <input id="cv-name" class="rp-input cv-field" placeholder="Jane" data-field="name">
                                <span class="rp-valid"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                        </div>
                        <div class="rp-group">
                            <label class="rp-label">Last name</label>
                            <div class="rp-input-wrap">
                                <input id="cv-last-name" class="rp-input cv-field" placeholder="Smith" data-field="last_name">
                                <span class="rp-valid"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                        </div>
                    </div>

                    <div class="rp-row full">
                        <div class="rp-group">
                            <label class="rp-label">Desired job title</label>
                            <div class="rp-input-wrap">
                                <input id="cv-job-title" class="rp-input cv-field" placeholder="e.g. Senior Product Designer" data-field="job_title">
                                <span class="rp-valid"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                        </div>
                    </div>

                    <div class="rp-row">
                        <div class="rp-group">
                            <label class="rp-label">Phone</label>
                            <div class="rp-input-wrap">
                                <input id="cv-mobile" class="rp-input cv-field" placeholder="+1 123 456 7890" data-field="mobile">
                                <span class="rp-valid"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                        </div>
                        <div class="rp-group">
                            <label class="rp-label">Email</label>
                            <div class="rp-input-wrap">
                                <input id="cv-email" class="rp-input cv-field" type="email" placeholder="you@example.com" data-field="email">
                                <span class="rp-valid"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                        </div>
                    </div>

                    <div class="rp-row full">
                        <div class="rp-group">
                            <label class="rp-label">Location</label>
                            <div class="rp-input-wrap">
                                <input id="cv-location" class="rp-input cv-field" placeholder="City, State, Country" data-field="location">
                            </div>
                        </div>
                    </div>

                    <div class="rp-row full">
                        <div class="rp-group">
                            <label class="rp-label">Social Links <span class="rp-hint">(comma separated)</span></label>
                            <div class="rp-input-wrap">
                                <input id="cv-social" class="rp-input cv-field" placeholder="linkedin.com/in/you, github.com/you" data-field="social_links">
                            </div>
                        </div>
                    </div>

                    <div id="image-upload-section" style="display:none; margin-top:0.5rem;">
                        <label class="rp-label" style="margin-bottom:0.5rem;display:block;">Profile Photo</label>
                        <div class="img-upload-wrap">
                            <img id="cv-image-preview" class="img-preview-circle" src="" alt="Profile">
                            <input type="file" id="cv-image-input" accept="image/*" style="display:none">
                            <button type="button" class="btn" onclick="document.getElementById('cv-image-input').click()">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                Upload Photo
                            </button>
                            <button type="button" class="btn" id="remove-image-btn">Remove</button>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 2: Experience ── --}}
                <div class="rp-step-pane" data-step="2">
                    <div class="step-head">
                        <div>
                            <h2>Experience</h2>
                            <p>List your work history starting with the most recent role.</p>
                        </div>
                        <button class="tips-btn" type="button">
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M11 3a1 1 0 10-2 0v1a1 1 0 102 0V3zM15.657 5.757a1 1 0 00-1.414-1.414l-.707.707a1 1 0 001.414 1.414l.707-.707zM18 10a1 1 0 01-1 1h-1a1 1 0 110-2h1a1 1 0 011 1zM5.05 6.464A1 1 0 106.464 5.05l-.707-.707a1 1 0 00-1.414 1.414l.707.707zM5 10a1 1 0 01-1 1H3a1 1 0 110-2h1a1 1 0 011 1zM8 16v-1h4v1a2 2 0 11-4 0zM12 14c.015-.34.208-.646.477-.859a4 4 0 10-4.954 0c.27.213.462.519.476.859h4.002z"/></svg>
                            Experience tips
                        </button>
                    </div>
                    <div id="rp-exp-editor"></div>
                    <button type="button" id="add-exp-btn" class="rp-add-btn">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Experience
                    </button>
                </div>

                {{-- ── STEP 3: Education ── --}}
                <div class="rp-step-pane" data-step="3">
                    <div class="step-head">
                        <div>
                            <h2>Education</h2>
                            <p>Add your educational background.</p>
                        </div>
                    </div>
                    <div id="rp-edu-editor"></div>
                    <button type="button" id="add-edu-btn" class="rp-add-btn" style="margin-top:0.75rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Education
                    </button>
                </div>

                {{-- ── STEP 4: Skills ── --}}
                <div class="rp-step-pane" data-step="4">
                    <div class="step-head">
                        <div>
                            <h2>Skills</h2>
                            <p>Highlight your core competencies.</p>
                        </div>
                    </div>
                    <div class="rp-group">
                        <label class="rp-label">Skills <span class="rp-hint">(comma separated)</span></label>
                        <textarea id="cv-skills" class="rp-input cv-field" rows="5" placeholder="React, Node.js, PHP, Python..." data-field="skills"></textarea>
                    </div>
                </div>

                {{-- ── STEP 5: Summary ── --}}
                <div class="rp-step-pane" data-step="5">
                    <div class="step-head">
                        <div>
                            <h2>Summary <span style="color:#cbd5e1;font-size:0.95rem;">✎</span></h2>
                            <p>Write a short introduction that highlights your experience, key skills, and career goals.</p>
                        </div>
                        <button class="tips-btn" type="button">
                            <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 2zM10 15a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 15zM15 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 0115 10zM6.5 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 016.5 10z"/></svg>
                            Summary tips
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
                        </button>
                    </div>
                    <div class="summary-editor-shell">
                        <div class="rich-toolbar">
                            <button type="button" class="rt-btn"><b>B</b></button>
                            <button type="button" class="rt-btn"><i>I</i></button>
                            <button type="button" class="rt-btn"><u>U</u></button>
                            <button type="button" class="rt-btn"><s>S</s></button>
                            <button type="button" class="rt-btn">↗</button>
                            <button type="button" class="rt-btn">≡</button>
                            <button type="button" class="rt-btn">↶</button>
                            <button type="button" class="rt-btn">↷</button>
                            <button type="button" class="ai-btn" onclick="generateAISummary()">
                                <svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 2zM10 15a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 15zM15 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 0115 10zM6.5 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 016.5 10z"/></svg>
                                Generate with AI
                            </button>
                        </div>
                        <textarea id="cv-summary" class="rp-input rich-ta summary-ta cv-field" placeholder="Experienced professional with a strong background in…" data-field="summary"></textarea>
                    </div>
                    <div class="suggested-summary">
                        <h4>Suggested summary structure for <span id="summary-role-label">your role</span></h4>
                        <p>Click an example to insert and customize.</p>
                        <div class="summary-suggestions-grid">
                            <button type="button" class="summary-suggestion" data-summary-template="Detail-oriented professional with hands-on experience in [field]. Skilled in [key skills]. Seeking to contribute to [type of team, company or goal]."><span class="plus">+</span><span>Detail-oriented professional with 3+ years of experience in <b>[field]</b>. Skilled in <b>[key skills]</b>. Seeking to contribute to <b>[type of team, company or goal]</b>.</span></button>
                            <button type="button" class="summary-suggestion" data-summary-template="Motivated recent graduate with a background in [field]. Eager to apply skills in [skill area] and grow within a dynamic organization."><span class="plus">+</span><span>Motivated recent graduate with a background in <b>[field]</b>. Eager to apply skills in <b>[skill area]</b> and grow within a dynamic organization.</span></button>
                            <button type="button" class="summary-suggestion" data-summary-template="Creative thinker with a passion for [field]. Experienced in [tools or platforms]. Looking to bring fresh ideas to a forward-thinking team."><span class="plus">+</span><span>Creative thinker with a passion for <b>[field]</b>. Experienced in <b>[tools or platforms]</b>. Looking to bring fresh ideas to a forward-thinking team.</span></button>
                            <button type="button" class="summary-suggestion" data-summary-template="A(n) [role] experienced in [field/industry], skilled in [top 2-3 skills], and looking to make a meaningful impact."><span class="plus">+</span><span>A(n) <b>[role]</b> experienced in <b>[field/industry]</b>, skilled in <b>[top 2-3 skills]</b>, and looking to make a meaningful impact.</span></button>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 6: Finalize ── --}}
                <div class="rp-step-pane" data-step="6">
                    <div class="step-head"><div><h2>Finalize</h2><p>Review your resume and export it.</p></div></div>
                    <div class="finalize-wrap">
                        <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
                        </svg>
                        <h3>Your resume looks great!</h3>
                        <p>Save it to download as a PDF or continue editing.</p>
                        <button type="button" id="save-cv-btn" class="btn primary" style="font-size:1rem; padding: 0.9rem 2.5rem; margin-top:0.5rem;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                            Save &amp; Download PDF
                        </button>
                    </div>
                </div>

            </div>{{-- /rp-form-body --}}

            <div class="rp-form-footer">
                <button type="button" class="btn" id="btn-back" style="visibility:hidden;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
                    Back
                </button>
                <button type="button" class="btn primary" id="btn-next">
                    Next: Experience
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
            </div>
        </section>

        {{-- ── RIGHT PANEL (PREVIEW) ── --}}
        <section class="rp-preview-panel">
            <div class="preview-topbar">
                <div class="score-badge">
                    <span class="score-pill">90%</span>
                    Your resume score 😍
                </div>
                <button class="change-tpl-btn" id="change-template-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Change Template
                </button>
            </div>
            <div class="rp-viewport" id="preview-viewport">
                <div id="cv-preview"></div>
            </div>
        </section>

    </div>{{-- /rp-builder --}}

    {{-- ══ TEMPLATE POPUP ══ --}}
    <div id="template-popup" class="rp-overlay">
        <div class="rp-popup">
            <div class="rp-popup-head">
                <h3>Choose a Template</h3>
                <button class="rp-popup-close" id="close-template-btn">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="rp-popup-body">
                <div class="rp-tpl-grid" id="template-grid"></div>
            </div>
        </div>
    </div>

    <script>
        /* ── Onboarding: start from scratch ── */
        function startFromScratch() {
            document.getElementById('rp-onboarding-view').style.display = 'none';
            document.getElementById('rp-builder-view').classList.add('visible');
        }

        /* ── Show builder if initial data exists ── */
        document.addEventListener('DOMContentLoaded', () => {
            const raw = document.getElementById('resume-initial-json')?.textContent;
            let hasData = false;
            try {
                const p = JSON.parse(raw);
                if (p && (p.id || p.name || p.email)) hasData = true;
            } catch(e) {}

            const ob = document.getElementById('rp-onboarding-view');
            const bv = document.getElementById('rp-builder-view');

            if (hasData) {
                ob.style.display = 'none';
                bv.classList.add('visible');
            } else {
                ob.style.display = 'flex';
                bv.classList.remove('visible');
            }
        });

        /* ── File autofill (upload card) ── */
        document.getElementById('resume-autofill-file')?.addEventListener('change', async function() {
            const file = this.files?.[0];
            if (!file) return;
            const statusEl = document.getElementById('resume-autofill-status');
            if (statusEl) statusEl.textContent = 'Reading your resume with AI…';

            try {
                const fd = new FormData();
                fd.append('resume', file);
                fd.append('mode', 'autofill');
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                const analyzeUrl = document.getElementById('create-cv-app').dataset.analyzeUrl;
                const res = await fetch(analyzeUrl, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                    body: fd
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.success) throw new Error(data.message || 'Could not import this resume.');

                // Apply data and switch to builder
                if (typeof applyResumeData === 'function') applyResumeData(data.improved_resume || {});
                document.getElementById('rp-onboarding-view').style.display = 'none';
                document.getElementById('rp-builder-view').classList.add('visible');
                if (statusEl) statusEl.textContent = '';
            } catch(err) {
                if (statusEl) { statusEl.textContent = err.message || 'Could not read this file.'; statusEl.style.color = '#dc2626'; }
            }
        });

        /* ── AI text generation helpers ── */
        function currentResumePayload() {
            const read = id => document.getElementById(id)?.value || '';
            return {
                name: read('cv-name'),
                last_name: read('cv-last-name'),
                job_title: read('cv-job-title'),
                email: read('cv-email'),
                mobile: read('cv-mobile'),
                location: read('cv-location'),
                social_links: read('cv-social').split(',').map(v => v.trim()).filter(Boolean),
                summary: read('cv-summary'),
                skills: read('cv-skills').split(',').map(v => v.trim()).filter(Boolean),
            };
        }

        async function generateAIText(context, targetEl, triggerButton = null) {
            if (!targetEl) return;
            const orig = targetEl.value;
            const button = triggerButton || (context === 'summary'
                ? document.querySelector('.rp-step-pane[data-step="5"] .ai-btn')
                : null);
            const oldButtonHtml = button?.innerHTML;
            if (button) {
                button.classList.add('loading');
                button.innerHTML = 'Generating...';
            }
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const app = document.getElementById('create-cv-app');
                const res = await fetch(app.dataset.aiTextUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ context, text: orig, resume: currentResumePayload() })
                });
                const data = await res.json();
                if (!res.ok || !data.text) throw new Error(data.message || 'AI generation failed.');
                targetEl.value = data.text;
            } catch(e) {
                targetEl.value = orig;
                alert(e.message || 'AI generation failed.');
            } finally {
                if (button) {
                    button.classList.remove('loading');
                    button.innerHTML = oldButtonHtml;
                }
            }
            targetEl.dispatchEvent(new Event('input', { bubbles: true }));
        }
        function generateAISummary() { generateAIText('summary', document.getElementById('cv-summary')); }

        document.querySelectorAll('.summary-suggestion').forEach(button => {
            button.addEventListener('click', () => {
                const target = document.getElementById('cv-summary');
                target.value = button.dataset.summaryTemplate || button.textContent.trim();
                target.dispatchEvent(new Event('input', { bubbles: true }));
            });
        });

        function updateSummaryRoleLabel() {
            const label = document.getElementById('summary-role-label');
            if (!label) return;
            label.textContent = document.getElementById('cv-job-title')?.value || 'your role';
        }
        document.getElementById('cv-job-title')?.addEventListener('input', updateSummaryRoleLabel);
        updateSummaryRoleLabel();
    </script>

    @include('resume.partials.editor-script')

</div>{{-- /create-cv-app --}}

@endsection
