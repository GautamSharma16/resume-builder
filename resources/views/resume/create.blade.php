@extends('layouts.bare') {{-- use a layout with NO navbar/footer --}}

@section('title', 'Resume Maker | Cvbliss')

@section('content')
@php
    $editingResume = $editingResume ?? null;
    $requiresPlanForDownload = auth()->check()
        && $editingResume
        && ! $editingResume->is_paid
        && ! auth()->user()->activeSubscription?->hasDownloadsRemaining();
@endphp

<div id="create-cv-app"
    class="rp-root"
    data-store-url="{{ route('resume.store') }}"
    @if($editingResume) data-update-url="{{ route('resume.update', $editingResume) }}" data-resume-id="{{ $editingResume->id }}" @endif
    data-is-editing="{{ $editingResume ? '1' : '0' }}"
    data-analyze-url="{{ route('resume.analyze') }}"
    data-login-url="{{ route('login') }}"
    data-plans-url="{{ route('plans') }}"
    data-ai-text-url="{{ route('resume.ai-text') }}"
    data-initial-source="{{ $editingResume?->source ?? (!empty($initialResume ?? []) ? 'upload' : 'manual') }}"
    data-authenticated="{{ auth()->check() ? '1' : '0' }}"
    data-download-requires-plan="{{ (!auth()->check() || (!auth()->user()->activeSubscription?->hasDownloadsRemaining() && (!$editingResume || !$editingResume->is_paid))) ? '1' : '0' }}"
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

        .tpl-resume ul { list-style-type: disc; margin-left: 1.5em; }
        .tpl-resume ol { list-style-type: decimal; margin-left: 1.5em; }

        html, body { margin: 0; padding: 0; min-height: 100%; overflow-y: auto; }
        body.is-builder { height: 100vh; overflow: hidden; }
        body.is-builder .rp-topbar,
        body.is-builder .main-footer { display: none !important; }

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
           MINIMAL TOP BAR
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
            display: none;
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
            font-size: clamp(1.5rem, 6vw, 2.75rem);
            color: var(--navy);
            margin-bottom: 0.75rem;
            margin-top: 40px;
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
            display: none;
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
        .field-needs-attention {
            border-color: #f87171 !important;
            box-shadow: 0 0 0 3px rgba(248, 113, 113, 0.18) !important;
        }
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
            display: none;
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

        /* Entry cards */
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

        .rp-accordion {
            margin-top: 2rem;
            border-top: 1px dashed var(--border);
            padding-top: 1.5rem;
        }
        .rp-accordion-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            border: 1.5px solid var(--border);
            border-radius: var(--r-md);
            background: #f8fafc;
            color: var(--navy);
            padding: 0.8rem 0.95rem;
            cursor: pointer;
            font-family: var(--font-body);
            text-align: left;
        }
        .rp-accordion-title {
            display: flex;
            flex-direction: column;
            gap: 0.15rem;
            min-width: 0;
        }
        .rp-accordion-title strong {
            font-size: 0.95rem;
            font-weight: 800;
        }
        .rp-accordion-title span {
            color: var(--muted);
            font-size: 0.76rem;
            line-height: 1.35;
        }
        .rp-accordion-icon {
            flex-shrink: 0;
            transition: transform 0.2s;
        }
        .rp-accordion[open] .rp-accordion-icon {
            transform: rotate(180deg);
        }
        .rp-accordion-body {
            padding-top: 1rem;
        }

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
            min-width: 0;
            gap: 0.75rem;
        }
        .preview-topbar {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            min-width: 0;
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
            flex-shrink: 0;
        }
        .score-pill {
            background: var(--green);
            color: var(--white);
            padding: 0.15rem 0.5rem;
            border-radius: 6px;
            font-size: 0.75rem;
        }
        .score-pill.mid { background: #f59e0b; }
        .score-pill.low { background: #ef4444; }
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
            flex-shrink: 0;
        }

        .preview-topbar .color-selector-wrap {
            flex: 1 1 auto !important;
            min-width: 0 !important;
            justify-content: center;
            margin-left: 0 !important;
            margin-right: 0 !important;
        }


        .rp-viewport {
            flex: 1;
            background: #f8fafc;
            border-radius: 0;
            overflow: auto;
            padding: 1.5rem 1rem;
            min-height: 0;
            min-width: 0;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            position: relative;
            max-width: 100%;
        }
        .rp-viewport::-webkit-scrollbar { width: 6px; }
        .rp-viewport::-webkit-scrollbar-track { background: transparent; }
        .rp-viewport::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.18); border-radius: 999px; }

        #cv-preview {
            background: transparent;
            width: 794px;
            min-height: 1123px;
            box-shadow: none;
            transform-origin: top center;
            flex-shrink: 0;
            margin: 0 auto;
            max-width: none;
        }

        .resume-preview-stage {
            box-shadow: 0 8px 32px rgba(0,0,0,0.12);
            background: var(--white);
            min-width: 0 !important;
            max-width: 100%;
            display: block !important;
        }
        .resume-sheet-preview {
            background: var(--white);
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
        .rp-popup-body { padding: 1.5rem; overflow-y: auto; flex: 1; min-height: 0; }
        .rp-tpl-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.25rem; align-items: start; }
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
        .rp-tpl-thumb {
            position: relative;
            width: 100%;
            height: var(--tpl-thumb-height, 286px);
            overflow: hidden;
            background: var(--surface);
        }
        .rp-tpl-thumb-inner {
            position: absolute; top: 0; left: 0; width: 794px;
            transform: scale(var(--tpl-thumb-scale, 0.255)); transform-origin: top left;
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

        @media (min-width: 1025px) and (max-width: 1440px) {
            .preview-topbar {
                flex-wrap: wrap;
                justify-content: flex-start;
            }
            .preview-topbar .score-badge { order: 1; }
            .preview-topbar .change-tpl-btn {
                order: 2;
                margin-left: auto;
            }
            .preview-topbar .color-selector-wrap {
                order: 3;
                flex-basis: 100% !important;
                overflow-x: auto;
                scrollbar-width: none;
            }
            .preview-topbar .color-selector-wrap::-webkit-scrollbar { display: none; }
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
        }
        .hidden { display: none !important; }

        /* Animations */
        @keyframes rpFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes spin { 100% { transform: rotate(360deg); } }

        /* Scrollbar for popup body */
        .rp-popup-body::-webkit-scrollbar { width: 6px; }
        .rp-popup-body::-webkit-scrollbar-track { background: transparent; }
        .rp-popup-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 999px; }

        /* Loading overlay */
        .loading-overlay {
            position: fixed;
            inset: 0;
            background: rgba(11,18,33,0.72);
            backdrop-filter: blur(8px);
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .loading-overlay.active { display: flex !important; }
        .scan-card {
            background: #fff;
            border-radius: 18px;
            padding: 1.8rem;
            min-width: 300px;
            max-width: 360px;
            width: calc(100% - 2rem);
            box-shadow: 0 28px 72px rgba(0,0,0,0.22);
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
            align-items: center;
        }
        .scan-header h2 { font-size: 16px; font-weight: 700; color: var(--navy); text-align:center; }
        .scan-header p { color: var(--muted); font-size: 12px; text-align:center; margin-top: 0.2rem; }
        .scan-paper { width: 150px; height: 190px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-md); padding: 1rem; position: relative; overflow: hidden; }
        .scan-paper::before { content:''; position:absolute; left:0; right:0; top:-8px; height:4px; background: linear-gradient(90deg, transparent, var(--blue), #818cf8, transparent); animation: scanMove 1.6s ease-in-out infinite; }
        .scan-paper-head { height: 18px; width: 58%; border-radius: var(--r-sm); background: linear-gradient(135deg, var(--blue), #6366f1); margin-bottom: 1rem; }
        .scan-paper-line { height: 7px; border-radius: 999px; background: #dbe3ef; margin-bottom: 0.55rem; }
        .scan-paper-line.wide { width: 100%; } .scan-paper-line.mid { width: 82%; } .scan-paper-line.short { width: 62%; } .scan-paper-line.tiny { width: 46%; }
        .scan-progress-bar-wrap { width: 100%; background: #e2e8f0; border-radius: 999px; height: 6px; overflow: hidden; }
        .scan-progress-fill { height: 100%; width: 0%; background: linear-gradient(90deg, var(--blue), #818cf8); border-radius: 999px; transition: width 0.3s ease; }
        .scan-steps { width: 100%; display:flex; flex-direction:column; gap:0.45rem; }
        .scan-step { display:flex; align-items:center; gap:0.5rem; font-size:12px; color: var(--soft); }
        .scan-step.active { color: var(--blue); }
        .scan-step.done { color: var(--green); }
        .scan-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
        @keyframes scanMove {
            0% { top: -5px; opacity: 1; }
            90% { top: 100%; opacity: 1; }
            100% { top: 100%; opacity: 0; }
        }

        /* ══════════════════════════════════════════
           MOBILE NAV — hidden on desktop
        ══════════════════════════════════════════ */
        .rp-mobile-nav { display: none; }

        /* ══════════════════════════════════════════
           RESPONSIVE — TABLET & MOBILE (≤1024px)
        ══════════════════════════════════════════ */
        @media (max-width: 1024px) {

            /* Builder: single column */
            .rp-builder {
                grid-template-columns: 1fr;
                grid-template-rows: minmax(0, 1fr);
                min-height: 100dvh;
                min-height: 100vh;
                padding: 0;
                gap: 0;
                overflow-x: hidden;
                max-width: 100vw;
                min-width: 0;
            }
            .rp-root { overflow-x: hidden; max-width: 100vw; }

            /* Show only one panel at a time */
            .rp-builder.view-preview .rp-form-panel { display: none; }
            .rp-builder.view-form .rp-preview-panel { display: none; }

            /* Preview panel height */
            .rp-builder.view-preview .rp-preview-panel {
                min-height: 0;
                min-width: 0;
                display: flex;
                flex-direction: column;
                max-height: min(100dvh, calc(100dvh - env(safe-area-inset-top, 0px) - env(safe-area-inset-bottom, 0px) - 5.75rem));
            }

            /* ── PREVIEW TOPBAR: horizontal single row ── */
            .preview-topbar {
                position: sticky;
                top: 0;
                z-index: 40;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                align-items: center !important;
                gap: 6px !important;
                padding: 6px 8px !important;
                background: #fff;
                border-bottom: 1px solid var(--border);
                box-shadow: 0 1px 0 rgba(0,0,0,0.04);
                overflow: visible;
                min-width: 0;
            }

            /* Score badge — compact, left */
            .preview-topbar .score-badge,
            .score-badge {
                flex-shrink: 0;
                padding: 4px 10px;
                font-size: 11px;
                white-space: nowrap;
            }

            /* Color selector — scrollable strip, takes remaining space */
            .preview-topbar .color-selector-wrap,
            .color-selector-wrap {
                flex: 1 1 0% !important;
                min-width: 0 !important;
                display: flex !important;
                flex-direction: row !important;
                flex-wrap: nowrap !important;
                align-items: center !important;
                gap: 6px !important;
                overflow-x: auto !important;
                overflow-y: hidden !important;
                -webkit-overflow-scrolling: touch !important;
                scrollbar-width: none !important;
                padding: 4px 4px !important;
                margin: 0 !important;
                background: transparent !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                border: none !important;
                width: auto !important;
            }
            .color-selector-wrap::-webkit-scrollbar { display: none; }

            /* "Original" pill */
            .color-circle-btn.original {
                padding: 3px 8px !important;
                font-size: 10px !important;
                width: auto !important;
                min-width: 52px !important;
                height: 24px !important;
                white-space: nowrap !important;
                flex-shrink: 0 !important;
                border-radius: 20px !important;
            }

            /* Color dots */
            .color-circle-btn:not(.original) {
                width: 22px !important;
                height: 22px !important;
                min-width: 22px !important;
                min-height: 22px !important;
                flex-shrink: 0 !important;
                border-radius: 50% !important;
            }

            /* Change Template button — compact, right */
            .preview-topbar .change-tpl-btn,
            .change-tpl-btn {
                flex-shrink: 0 !important;
                width: auto !important;
                padding: 5px 10px !important;
                font-size: 11px !important;
                min-height: unset !important;
                white-space: nowrap !important;
                justify-content: center !important;
            }

            /* ── VIEWPORT ── */
            .rp-preview-panel { min-height: 0; }
            .rp-viewport {
                padding: 8px 8px calc(88px + env(safe-area-inset-bottom, 0px)) 8px !important;
                background: #f1f5f9 !important;
                overflow-x: auto !important;
                overflow-y: auto !important;
                -webkit-overflow-scrolling: touch;
                flex: 1;
                min-height: 0;
                min-width: 0;
                max-width: 100vw;
                align-items: flex-start !important;
                justify-content: center !important;
            }

            /* Resume preview scaling — JS (updatePreviewScale) handles the math */
            .resume-preview-stage {
                transform-origin: top left !important;
                min-width: 0 !important;
                max-width: none !important;
            }
            #cv-preview {
                transform-origin: top left !important;
                margin: 0 auto !important;
                flex: 0 0 auto;
                width: auto !important;
                max-width: none !important;
                min-width: 0 !important;
                background: transparent !important;
                display: block !important;
                overflow: hidden !important;
            }

            /* Form panel spacing for fixed dock */
            .rp-form-body { padding-bottom: 190px !important; }
            .rp-form-footer { padding-bottom: calc(1rem + env(safe-area-inset-bottom, 0px) + 84px) !important; }

            /* ── MOBILE NAV DOCK ── */
            .rp-mobile-nav {
                display: flex;
                position: fixed;
                bottom: calc(env(safe-area-inset-bottom, 0px) + 10px);
                left: 50%;
                transform: translateX(-50%);
                background: #0f172a;
                padding: 5px;
                border-radius: 100px;
                box-shadow: 0 8px 24px rgba(0,0,0,0.35);
                z-index: 2000;
                gap: 4px;
                border: 1px solid rgba(255,255,255,0.1);
                width: min(calc(100% - 24px), 340px);
                justify-content: center;
            }
            .rp-mobile-nav button {
                flex: 1;
                padding: 10px 14px;
                border-radius: 100px;
                font-size: 13px;
                font-weight: 600;
                border: none;
                background: transparent;
                color: rgba(255,255,255,0.6);
                cursor: pointer;
                transition: background 0.2s, color 0.2s;
                white-space: nowrap;
                justify-content: center;
            }
            .rp-mobile-nav button.active {
                background: var(--blue);
                color: #fff;
                box-shadow: 0 2px 10px rgba(59,130,246,0.4);
            }
        }

        /* ══════════════════════════════════════════
           RESPONSIVE — SMALL PHONES (≤600px)
        ══════════════════════════════════════════ */
        @media (max-width: 600px) {
            .rp-root { padding: 0; }
            .rp-onboarding { padding: 2rem 1.25rem 4rem !important; justify-content: flex-start !important; }
            .ob-card { padding: 1.25rem; width: 100%; }

            /* Step nav */
            .rp-step-nav { padding: 10px 10px 0; gap: 2px; }
            .rp-step-tab { min-width: 32px; padding-bottom: 12px; }
            .rp-step-dot { width: 12px; height: 12px; }
            .rp-step-name { display: none; }
            .rp-step-tab.active .rp-step-name {
                display: block;
                position: absolute; top: -12px; left: 50%; transform: translateX(-50%);
                font-size: 9px; background: var(--navy); color: #fff;
                padding: 1px 6px; border-radius: 4px; z-index: 10;
            }

            /* Form */
            .rp-form-body { padding: 1rem !important; }
            .rp-form-footer {
                flex-direction: column;
                padding: 0.75rem !important;
                gap: 8px;
                position: fixed;
                left: 0;
                right: 0;
                bottom: calc(62px + env(safe-area-inset-bottom, 0px));
                z-index: 1200;
                background: #fff;
                border-top: 1px solid var(--border);
            }
            .rp-form-footer .btn { width: 100%; justify-content: center; }
            .rp-form-body { padding-bottom: 300px !important; }
            .rp-builder.view-preview .rp-form-footer { display: none !important; }

            /* Topbar */
            .rp-topbar { height: 48px !important; padding: 0 12px !important; }
            .rp-topbar-logo img { height: clamp(38px, 12vw, 46px) !important; }

            /* Preview topbar on very small screens */
            .preview-topbar { padding: 5px 6px !important; gap: 5px !important; }
            .preview-topbar .score-badge,
            .score-badge { display: none !important; }
            .preview-topbar .color-selector-wrap,
            .color-selector-wrap {
                order: 1;
                flex: 1 1 auto !important;
                justify-content: flex-start !important;
                max-width: calc(100vw - 116px) !important;
            }
            .preview-topbar .change-tpl-btn,
            .change-tpl-btn {
                order: 2;
                padding: 5px 8px !important;
                max-width: 106px;
                overflow: hidden;
                text-overflow: ellipsis;
            }
            .color-circle-btn.original {
                min-width: 46px !important;
                height: 22px !important;
                padding: 2px 7px !important;
                font-size: 9px !important;
            }
            .color-circle-btn:not(.original) {
                width: 18px !important;
                height: 18px !important;
                min-width: 18px !important;
                min-height: 18px !important;
            }

            .rp-overlay {
                align-items: stretch;
                justify-content: center;
                padding: 0;
            }
            .rp-popup {
                max-height: 100dvh;
                height: 100dvh;
                width: 100%;
                border-radius: 0;
            }
            .rp-popup-head { padding: 1rem; }
            .rp-popup-body { padding: 1rem; }
            .rp-tpl-grid { grid-template-columns: 1fr; gap: 1rem; }
        }

        /* ══════════════════════════════════════════
           RESPONSIVE — EXTRA SMALL (≤480px)
        ══════════════════════════════════════════ */
        @media (max-width: 480px) {
            /* Single-column form rows */
            .rp-row { grid-template-columns: 1fr !important; }
            .rp-entry-row { grid-template-columns: 1fr !important; }

            /* Viewport padding */
            .rp-viewport { padding: 4px 6px calc(88px + env(safe-area-inset-bottom, 0px)) 6px !important; }

            .resume-sheet-preview {
                max-width: none !important;
                overflow: visible !important;
            }

            /* Mobile nav dock narrower */
            .rp-mobile-nav { width: calc(100% - 1rem); padding: 4px; }
            .rp-mobile-nav button { padding: 9px 10px; font-size: 12px; }
        }

    </style>

    {{-- ══ TOPBAR ══ --}}
    <header class="rp-topbar" style="position: relative; z-index: 100;">
        <a href="{{ route('home') }}" class="rp-topbar-logo">
            <img src="{{ asset('Logo.png') }}" alt="Cvbliss" class="cvb-logo" style="height: clamp(42px, 5vw, 52px); width: auto; max-width: 150px;">
        </a>
        <div style="flex: 1;"></div>
        <div class="rp-topbar-actions" style="display: flex; gap: 0.75rem; align-items: center;">
            <a href="{{ route('home') }}" style="text-decoration: none; font-size: 0.75rem; color: var(--ink); font-weight: 600;">Home</a>
            @guest
                <a href="{{ route('login') }}" class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.75rem; background: var(--navy); color: #fff; border-radius: 8px;">Login</a>
            @endguest
        </div>
    </header>

    {{-- ══ ONBOARDING ══ --}}
    <div id="rp-onboarding-view" class="rp-onboarding" style="padding: 6rem 1.5rem 4rem;">
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

    <div id="resume-scan-overlay" class="loading-overlay" style="display:none;">
        <div class="scan-card">
            <div class="scan-header">
                <h2>AI is Reading...</h2>
                <p id="resumeScanStageLabel">Uploading…</p>
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
                <div class="scan-progress-fill" id="resumeScanProgressFill"></div>
            </div>
            <div class="scan-steps">
                <div class="scan-step" id="resumeScanStep1"><div class="scan-dot"></div>Uploading…</div>
                <div class="scan-step" id="resumeScanStep2"><div class="scan-dot"></div>Parsing resume…</div>
                <div class="scan-step" id="resumeScanStep3"><div class="scan-dot"></div>Building resume…</div>
            </div>
        </div>
    </div>

    {{-- ══ FOOTER ══ --}}
    <div class="main-footer">
        @include('components.footer')
    </div>

    {{-- ══ BUILDER ══ --}}
    <div id="rp-builder-view" class="rp-builder view-form">

        {{-- Mobile Navigation Toggle --}}
        <div class="rp-mobile-nav">
            <button type="button" id="mob-btn-form" class="active" onclick="setMobileView('form')">Edit Details</button>
            <button type="button" id="mob-btn-preview" onclick="setMobileView('preview')">View Resume</button>
        </div>

        {{-- Hidden template selector --}}
        <select id="template-id" style="display:none">
            @foreach($templates as $template)
                <option value="{{ $template->id }}" @selected((string) $selectedTemplateId === (string) $template->id)>{{ $template->name }}</option>
            @endforeach
        </select>

        {{-- ── LEFT PANEL ── --}}
        <section class="rp-form-panel">

            <nav class="rp-step-nav">
                @foreach(['Contacts','Summary','Experience','Education','Skills','Additional','Finalize'] as $i => $label)
                    <button class="rp-step-tab {{ $i === 0 ? 'active' : '' }}" data-step="{{ $i + 1 }}">
                        <span class="rp-step-name">{{ $label }}</span>
                        <div class="rp-step-dot"></div>
                    </button>
                @endforeach
            </nav>

            <div class="rp-form-body" style="padding-bottom: 160px;">

                {{-- ── STEP 1: Contacts ── --}}
                <div class="rp-step-pane active" data-step="1">
                    <div class="step-head">
                        <div>
                            <h2>Contacts</h2>
                            <p>Add your up-to-date contact information so employers can easily reach you.</p>
                        </div>
                    </div>

                    <div class="rp-row">
                        <div class="rp-group" data-template-field="name">
                            <label class="rp-label">First name</label>
                            <div class="rp-input-wrap">
                                <input id="cv-name" class="rp-input cv-field" placeholder="Jane" data-field="name" autocomplete="given-name">
                                <span class="rp-valid"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                        </div>
                        <div class="rp-group" data-template-field="last_name">
                            <label class="rp-label">Last name</label>
                            <div class="rp-input-wrap">
                                <input id="cv-last-name" class="rp-input cv-field" placeholder="Smith" data-field="last_name" autocomplete="family-name">
                                <span class="rp-valid"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                        </div>
                    </div>

                    <div class="rp-row full">
                        <div class="rp-group" data-template-field="designation,job_title">
                            <label class="rp-label">Designation</label>
                            <div class="rp-input-wrap">
                                <input id="cv-designation" class="rp-input cv-field" placeholder="e.g. Senior Product Designer" data-field="designation" data-template-field="designation,job_title">
                                <span class="rp-valid"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                        </div>
                    </div>

                    <div class="rp-row">
                        <div class="rp-group" data-template-field="mobile,contact">
                            <label class="rp-label">Phone</label>
                            <div class="rp-input-wrap">
                                <input id="cv-mobile" class="rp-input cv-field" placeholder="+1 123 456 7890" data-field="mobile">
                                <span class="rp-valid"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                        </div>
                        <div class="rp-group" data-template-field="email,contact">
                            <label class="rp-label">Email</label>
                            <div class="rp-input-wrap">
                                <input id="cv-email" class="rp-input cv-field" type="email" placeholder="you@example.com" data-field="email">
                                <span class="rp-valid"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                        </div>
                    </div>

                    <div class="rp-row full">
                        <div class="rp-group" data-template-field="location,address,contact">
                            <label class="rp-label">Location</label>
                            <div class="rp-input-wrap">
                                <input id="cv-location" class="rp-input cv-field" placeholder="City, State, Country" data-field="location">
                            </div>
                        </div>
                    </div>

                    <div class="rp-row full">
                        <div class="rp-group" data-template-field="portfolio,link">
                            <label class="rp-label">Portfolio URL</label>
                            <div class="rp-input-wrap">
                                <input id="cv-portfolio" class="rp-input cv-field" placeholder="https://your-portfolio.com" data-field="portfolio">
                            </div>
                        </div>
                    </div>
                    
                    <div class="rp-row">
                        <div class="rp-group" data-template-field="linkedin">
                            <label class="rp-label">LinkedIn</label>
                            <div class="rp-input-wrap">
                                <input id="cv-linkedin" class="rp-input cv-field" placeholder="linkedin.com/in/you" data-field="linkedin">
                                <span class="rp-valid"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                        </div>
                        <div class="rp-group" data-template-field="github">
                            <label class="rp-label">GitHub</label>
                            <div class="rp-input-wrap">
                                <input id="cv-github" class="rp-input cv-field" placeholder="github.com/you" data-field="github">
                                <span class="rp-valid"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></span>
                            </div>
                        </div>
                    </div>

                    <div id="image-upload-section" class="hidden" style="margin-top:0.5rem;">
                        <label class="rp-label" style="margin-bottom:0.5rem;display:block;">Profile Photo</label>
                        <div class="img-upload-wrap">
                            <div id="cv-image-placeholder" class="img-preview-circle" style="display: flex; align-items: center; justify-content: center; background: #f1f5f9; color: #94a3b8;">
                                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            </div>
                            <img id="cv-image-preview" class="img-preview-circle hidden" src="" alt="Profile">
                            <input type="file" id="cv-image-input" accept="image/*" style="display:none">
                            <button type="button" class="btn" onclick="document.getElementById('cv-image-input').click()">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                Upload Photo
                            </button>
                            <button type="button" class="btn hidden" id="remove-image-btn">Remove</button>
                        </div>
                    </div>
                </div>

                {{-- ── STEP 2: Summary ── --}}
                <div class="rp-step-pane" data-step="2">
                    <div class="step-head">
                        <div>
                            <h2>Summary <span style="color:#cbd5e1;font-size:0.95rem;">✎</span></h2>
                            <p>Write a short introduction that highlights your experience, key skills, and career goals.</p>
                        </div>
                        <button class="ai-btn" type="button" onclick="generateAISummary(this)" style="align-self: flex-start;">
                            <svg width="13" height="13" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 2zM10 15a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 15zM15 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 0115 10zM6.5 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 016.5 10z"/></svg>
                            <span>Generate with AI</span>
                        </button>
                    </div>
                    <div class="summary-editor-shell">
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

                {{-- ── STEP 3: Experience ── --}}
                <div class="rp-step-pane" data-step="3">
                    <div class="step-head">
                        <div>
                            <h2>Experience</h2>
                            <p>List your work history starting with the most recent role.</p>
                        </div>
                        
                    </div>
                    <div style="margin-bottom:0.85rem; display:flex; justify-content:flex-end;">
                        <button type="button" id="clear-exp-section-btn" class="rp-entry-remove">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                            Delete Entire Experience Section
                        </button>
                    </div>
                    <div id="rp-exp-editor"></div>
                    <button type="button" id="add-exp-btn" class="rp-add-btn">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Experience
                    </button>
                </div>

                {{-- ── STEP 4: Education ── --}}
                <div class="rp-step-pane" data-step="4">
                    <div class="step-head">
                        <div>
                            <h2>Education</h2>
                            <p>Add your educational background.</p>
                        </div>
                    </div>
                    <div style="margin-bottom:0.85rem; display:flex; justify-content:flex-end;">
                        <button type="button" id="clear-edu-section-btn" class="rp-entry-remove">
                            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                            Delete Entire Education Section
                        </button>
                    </div>
                    <div id="rp-edu-editor"></div>
                    <button type="button" id="add-edu-btn" class="rp-add-btn" style="margin-top:0.75rem;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Education
                    </button>
                </div>

                {{-- ── STEP 5: Skills ── --}}
                <div class="rp-step-pane" data-step="5">
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

                {{-- ── STEP 6: Projects, Certifications, Achievements ── --}}
                <div class="rp-step-pane" data-step="6">
                    <div class="step-head">
                        <div>
                            <h2>Additional Sections</h2>
                            <p>Add projects, certifications, languages, and additional information to stand out.</p>
                        </div>
                    </div>

                    {{-- Projects --}}
                    <div id="projects-section" style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border);" data-template-section="projects">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <h3 style="font-size: 1.1rem; font-weight: 700;">Projects</h3>
                            <button type="button" id="clear-project-section-btn" class="rp-entry-remove">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                Delete Section
                            </button>
                        </div>
                        <div id="project-editor"></div>
                        <button type="button" id="add-project-btn" class="rp-add-btn" style="margin-top:0.5rem;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Project
                        </button>
                    </div>

                    {{-- Certifications --}}
                    <div id="certifications-section" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px dashed var(--border);" data-template-section="certifications,certificates">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <h3 style="font-size: 1.1rem; font-weight: 700;">Certifications</h3>
                            <button type="button" id="clear-certification-section-btn" class="rp-entry-remove">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                Delete Section
                            </button>
                        </div>
                        <div id="certification-editor"></div>
                        <button type="button" id="add-certification-btn" class="rp-add-btn" style="margin-top:0.5rem;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Certification
                        </button>
                    </div>

                    {{-- Languages --}}
                    <div id="languages-section" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px dashed var(--border);" data-template-section="languages">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <h3 style="font-size: 1.1rem; font-weight: 700;">Languages</h3>
                            <button type="button" id="clear-language-section-btn" class="rp-entry-remove">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                Delete Section
                            </button>
                        </div>
                        <div id="language-editor"></div>
                        <button type="button" id="add-language-btn" class="rp-add-btn" style="margin-top:0.5rem;">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            Add Language
                        </button>
                    </div>

                    <details id="additional-information-section" class="rp-accordion" data-template-section="additional_information">
                        <summary class="rp-accordion-toggle">
                            <span class="rp-accordion-title">
                                <strong>Additional Information</strong>
                                <span>Achievements, awards, publications, volunteer work, extra activities, business participation, conferences, and other information.</span>
                            </span>
                            <svg class="rp-accordion-icon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 9l6 6 6-6"/></svg>
                        </summary>
                        <div class="rp-accordion-body">
                            <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 0.5rem;">
                                <button type="button" id="clear-additional-information-section-btn" class="rp-entry-remove">
                                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/></svg>
                                    Delete Section
                                </button>
                            </div>
                            <div id="additional-information-editor"></div>
                            <button type="button" id="add-additional-information-btn" class="rp-add-btn" style="margin-top:0.5rem;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                Add Information
                            </button>
                        </div>
                    </details>
                </div>

                {{-- ── STEP 7: Finalize ── --}}
                <div class="rp-step-pane" data-step="7">
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
                        @auth
                            <a href="{{ route('dashboard') }}" class="btn" style="font-size:1rem; padding: 0.9rem 2.5rem; margin-top:0.75rem; text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">
                                Go to Dashboard
                            </a>
                        @endauth
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
                    <span class="score-pill" data-resume-score>0%</span>
                    Your resume score 😍
                </div>
                <div class="color-selector-wrap" style="display: flex; align-items: center; gap: 12px; margin-left: auto; margin-right: 20px; background: #fff; padding: 6px 16px; border-radius: 100px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); border: 1px solid #eef2f6;">
                    <button type="button" class="color-circle-btn original active" onclick="applyColorSelection('')" title="Original Color" style="background: #fff; border: 1.5px solid #0b1221; border-radius: 20px; padding: 4px 12px; font-size: 11px; font-weight: 700; color: #0b1221; cursor: pointer; transition: all 0.2s;">Original</button>
                    <button type="button" class="color-circle-btn" onclick="applyColorSelection('#3b82f6')" title="Blue" style="width: 24px; height: 24px; border-radius: 50%; background: #3b82f6; border: none; cursor: pointer; transition: transform 0.2s;"></button>
                    <button type="button" class="color-circle-btn" onclick="applyColorSelection('#10b981')" title="Green" style="width: 24px; height: 24px; border-radius: 50%; background: #10b981; border: none; cursor: pointer; transition: transform 0.2s;"></button>
                    <button type="button" class="color-circle-btn" onclick="applyColorSelection('#475569')" title="Slate" style="width: 24px; height: 24px; border-radius: 50%; background: #475569; border: none; cursor: pointer; transition: transform 0.2s;"></button>
                    <button type="button" class="color-circle-btn" onclick="applyColorSelection('#e11d48')" title="Rose" style="width: 24px; height: 24px; border-radius: 50%; background: #e11d48; border: none; cursor: pointer; transition: transform 0.2s;"></button>
                    <button type="button" class="color-circle-btn" onclick="applyColorSelection('#6366f1')" title="Indigo" style="width: 24px; height: 24px; border-radius: 50%; background: #6366f1; border: none; cursor: pointer; transition: transform 0.2s;"></button>
                </div>
                <button class="change-tpl-btn" id="change-template-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                    Change Template
                </button>
               
            </div>
            <div class="rp-viewport" id="preview-viewport" style="padding-bottom: 120px;">
                <div id="cv-preview"></div>
            </div>
            <div id="preview-page-nav" class="rp-page-nav" style="display:none;" aria-label="Resume page navigation">
 
    <button type="button" id="preview-page-prev" class="rp-page-btn"
            aria-label="Previous page" title="Previous page">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"
             viewBox="0 0 24 24" aria-hidden="true">
            <polyline points="15 18 9 12 15 6"/>
        </svg>
    </button>
 
    <span id="preview-page-label" class="rp-page-label">1 / 1</span>
 
    <button type="button" id="preview-page-next" class="rp-page-btn"
            aria-label="Next page" title="Next page">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.2"
             viewBox="0 0 24 24" aria-hidden="true">
            <polyline points="9 18 15 12 9 6"/>
        </svg>
    </button>
 
    {{-- Hidden range — JS backward compat ke liye --}}
    <input type="range" id="preview-page-range" min="1" max="1" value="1" step="1"
           style="display:none;" aria-hidden="true">
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
        function startFromScratch() {
            if (window.setResumeMakerSource) window.setResumeMakerSource('manual');
            document.body.classList.add('is-builder');
            document.getElementById('rp-onboarding-view').style.display = 'none';
            document.getElementById('rp-builder-view').classList.add('visible');
            if (typeof goToStep === 'function') goToStep(1);
        }

        function backToOnboarding() {
            document.body.classList.remove('is-builder');
            document.getElementById('rp-builder-view').classList.remove('visible');
            document.getElementById('rp-onboarding-view').style.display = 'flex';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const raw = document.getElementById('resume-initial-json')?.textContent;
            const appEl = document.getElementById('create-cv-app');
            const isEditing = appEl?.dataset?.isEditing === '1';
            let hasData = false;
            try {
                const p = JSON.parse(raw);
                if (p && Object.values(p).some(v => {
                    if (Array.isArray(v)) return v.length > 0;
                    return String(v ?? '').trim() !== '';
                })) hasData = true;
            } catch(e) {}

            const ob = document.getElementById('rp-onboarding-view');
            const bv = document.getElementById('rp-builder-view');

            if (isEditing || hasData) {
                ob.style.display = 'none';
                bv.classList.add('visible');
                document.body.classList.add('is-builder');
            } else {
                ob.style.display = 'flex';
                bv.classList.remove('visible');
                document.body.classList.remove('is-builder');
            }
        });

        const resumeScanStages = [
            { label: 'Uploading…', step: 0, pct: 18 },
            { label: 'Parsing resume…', step: 0, pct: 42 },
            { label: 'Extracting experience…', step: 1, pct: 68 },
            { label: 'Building resume…', step: 2, pct: 90 },
            { label: 'Ready', step: -1, pct: 100 },
        ];
        let resumeScanTimer = null;
        function showResumeScanOverlay() {
            const overlay = document.getElementById('resume-scan-overlay');
            overlay?.classList.add('active');
            document.body.style.overflow = 'hidden';
            const fill = document.getElementById('resumeScanProgressFill');
            const stageLabel = document.getElementById('resumeScanStageLabel');
            const items = [
                document.getElementById('resumeScanStep1'),
                document.getElementById('resumeScanStep2'),
                document.getElementById('resumeScanStep3'),
            ];
            if (fill) fill.style.width = '0%';
            items.forEach(i => { if (i) i.className = 'scan-step'; });
            let stageIdx = 0;
            const tick = () => {
                if (stageIdx >= resumeScanStages.length) return;
                const stage = resumeScanStages[stageIdx++];
                if (stageLabel) stageLabel.textContent = stage.label;
                if (fill) fill.style.width = stage.pct + '%';
                if (stage.step >= 0) {
                    const item = items[stage.step];
                    if (item) item.classList.add('active');
                    if (stage.step > 0) {
                        items[stage.step - 1]?.classList.remove('active');
                        items[stage.step - 1]?.classList.add('done');
                    }
                } else {
                    items.forEach(i => { if (i) { i.classList.remove('active'); i.classList.add('done'); } });
                }
                resumeScanTimer = setTimeout(tick, 800);
            };
            tick();
        }
        function hideResumeScanOverlay() {
            const overlay = document.getElementById('resume-scan-overlay');
            overlay?.classList.remove('active');
            document.body.style.overflow = '';
            if (resumeScanTimer) clearTimeout(resumeScanTimer);
            resumeScanTimer = null;
        }
        window.showResumeScanOverlay = showResumeScanOverlay;
        window.hideResumeScanOverlay = hideResumeScanOverlay;

        function currentResumePayload(options = {}) {
            const aiContext = options.context || '';
            const targetEl = options.targetEl || null;
            const seedText = String(options.seedText || '');
            const read = id => {
                if (id === 'cv-summary' && typeof tinymce !== 'undefined' && tinymce.get(id)) {
                    return tinymce.get(id).getContent();
                }
                return document.getElementById(id)?.value || '';
            };
            const payload = {
                name: read('cv-name'),
                last_name: read('cv-last-name'),
                designation: read('cv-designation'),
                job_title: read('cv-designation'),
                email: read('cv-email'),
                mobile: read('cv-mobile'),
                location: read('cv-location'),
                linkedin: read('cv-linkedin'),
                github: read('cv-github'),
                portfolio: read('cv-portfolio'),
                link: read('cv-portfolio'),
                social_links: [],
                summary: read('cv-summary'),
                skills: read('cv-skills').split(',').map(v => v.trim()).filter(Boolean),
                experience: Array.from(document.querySelectorAll('[data-exp]')).map(block => {
                    const r = k => {
                        const input = block.querySelector(`[data-k="${k}"]`);
                        if (input && input.classList.contains('rp-input-ta') && typeof tinymce !== 'undefined' && input.id && tinymce.get(input.id)) {
                            return tinymce.get(input.id).getContent();
                        }
                        return input ? input.value : '';
                    };
                    const pointsVal = r('points');
                    return {
                        company: r('company'),
                        role: r('role'),
                        period: r('period'),
                        points: (/<[a-z][\s\S]*>/i.test(pointsVal)) ? [pointsVal] : pointsVal.split('\n').map(v => v.trim()).filter(Boolean),
                    };
                }),
                education: Array.from(document.querySelectorAll('[data-edu]')).map(block => ({
                    degree: block.querySelector('[data-k="degree"]')?.value || '',
                    stream: block.querySelector('[data-k="stream"]')?.value || '',
                    institution: block.querySelector('[data-k="institution"]')?.value || '',
                    year: block.querySelector('[data-k="year"]')?.value || '',
                })),
                projects: Array.from(document.querySelectorAll('[data-project]')).map(block => ({
                    name: block.querySelector('[data-k="name"]')?.value || '',
                    tech_stack: block.querySelector('[data-k="tech_stack"]')?.value || '',
                    link: block.querySelector('[data-k="link"]')?.value || '',
                    description: block.querySelector('[data-k="description"]')?.value || '',
                })),
                certifications: Array.from(document.querySelectorAll('[data-certification]')).map(block => ({
                    name: block.querySelector('[data-k="name"]')?.value || '',
                    description: block.querySelector('[data-k="description"]')?.value || '',
                })),
                languages: Array.from(document.querySelectorAll('[data-language]')).map(block => ({
                    name: block.querySelector('[data-k="name"]')?.value || '',
                    level: block.querySelector('[data-k="level"]')?.value || '',
                })),
                additional_information: Array.from(document.querySelectorAll('[data-additional-information]')).map(block => ({
                    name: block.querySelector('[data-k="name"]')?.value || '',
                    description: block.querySelector('[data-k="description"]')?.value || '',
                })),
                achievements: [],
            };

            if (aiContext === 'summary') {
                payload.summary = seedText;
            }

            if (aiContext === 'experience' && targetEl) {
                const expBlock = targetEl.closest('[data-exp]');
                const expIndex = Number(expBlock?.dataset?.exp);
                if (!Number.isNaN(expIndex) && payload.experience[expIndex]) {
                    payload.experience[expIndex].points = seedText
                        ? seedText.split('\n').map(v => v.trim()).filter(Boolean)
                        : [];
                }
            }

            return payload;
        }

        const AI_FAILURE_MESSAGE = "We're unable to process your request right now. Please try again after some time.";
        const showResumeAiFailureAlert = () => {
            window.alert(AI_FAILURE_MESSAGE);
        };
        const resumeAiInFlight = new WeakSet();
        const resumeAiHistory = window.__resumeAiHistory || (window.__resumeAiHistory = {});
        const resumeAiState = window.__resumeAiState || (window.__resumeAiState = {});
        const plainResumeText = (value = '') => {
            const div = document.createElement('div');
            div.innerHTML = String(value || '');
            return (div.textContent || div.innerText || String(value || ''))
                .replace(/\u00a0/g, ' ')
                .replace(/\s+/g, ' ')
                .trim();
        };
        const normalizeAiSeedText = (value = '') => {
            const div = document.createElement('div');
            div.innerHTML = String(value || '')
                .replace(/<br\s*\/?>/gi, '\n')
                .replace(/<\/p>/gi, '\n')
                .replace(/<\/li>/gi, '\n');
            return (div.textContent || div.innerText || String(value || ''))
                .replace(/\u00a0/g, ' ')
                .split(/\n+/)
                .map(line => line.replace(/\s+/g, ' ').trim())
                .map(line => line.replace(/^\s*[-*•]\s*/u, '').trim())
                .filter(Boolean)
                .join('\n')
                .slice(0, 900);
        };
        const normalizeAiHistoryEntry = (value = '') => plainResumeText(value).slice(0, 500);
        const notifyResumeAi = (message, type = 'info') => {
            if (window.resumeMakerNotify) {
                window.resumeMakerNotify(message, type);
                return;
            }
            const statusEl = document.getElementById('resume-autofill-status') || document.getElementById('cv-status');
            if (statusEl) {
                statusEl.textContent = message;
                statusEl.style.color = type === 'error' ? '#dc2626' : '#2563eb';
            }
        };
        const getResumeAiHistoryKey = (context, targetEl) => {
            if (context === 'experience') {
                const block = targetEl?.closest('[data-exp]');
                return `experience:${block?.dataset?.exp || '0'}`;
            }
            return 'summary';
        };
        const getResumeAiMeta = (historyKey) => {
            if (!resumeAiState[historyKey]) {
                resumeAiState[historyKey] = {
                    lastGenerated: '',
                    seedText: '',
                    jobRole: '',
                };
            }

            return resumeAiState[historyKey];
        };
        const getClickedExperienceRole = (targetEl) => {
            const block = targetEl?.closest('[data-exp]');
            return block?.querySelector('[data-k="role"]')?.value?.trim() || '';
        };
        const setAiButtonLoading = (button, isLoading, originalHtml = '') => {
            if (!button) return;
            if (isLoading) {
                button.dataset.originalHtml = originalHtml || button.innerHTML;
                button.classList.add('loading');
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                button.innerHTML = '<span style="display:inline-flex;align-items:center;gap:.4rem;"><span aria-hidden="true" style="width:12px;height:12px;display:inline-block;border:2px solid rgba(255,255,255,.45);border-top-color:#fff;border-radius:999px;animation:rmSpin .8s linear infinite;"></span>Generating...</span>';
            } else {
                button.classList.remove('loading');
                button.disabled = false;
                button.removeAttribute('aria-busy');
                button.innerHTML = button.dataset.originalHtml || originalHtml || button.innerHTML;
                delete button.dataset.originalHtml;
            }
        };

        async function generateAIText(context, targetEl, triggerButton = null) {
            if (!targetEl) return;
            const getEditorForEl = (el) => {
                if (typeof tinymce === 'undefined' || !el) return null;
                if (el.id && tinymce.get(el.id)) return tinymce.get(el.id);
                return (tinymce.editors || []).find((ed) => ed?.targetElm === el) || null;
            };
            const activeEditor = getEditorForEl(targetEl);
            let orig = activeEditor ? activeEditor.getContent() : targetEl.value;
            const plainOrig = plainResumeText(orig);
            const jobRole = context === 'experience' ? getClickedExperienceRole(targetEl) : '';
            const historyKey = getResumeAiHistoryKey(context, targetEl);
            const aiMeta = getResumeAiMeta(historyKey);
            const roleChanged = context === 'experience' && aiMeta.jobRole && aiMeta.jobRole !== jobRole;

            if (roleChanged) {
                aiMeta.lastGenerated = '';
                aiMeta.seedText = '';
                resumeAiHistory[historyKey] = [];
            }

            const plainLastGenerated = plainResumeText(aiMeta.lastGenerated || '');
            const currentLooksGenerated = plainOrig && plainLastGenerated && plainOrig === plainLastGenerated;
            const effectiveSeedText = context === 'experience'
                ? ''
                : normalizeAiSeedText(
                    currentLooksGenerated
                        ? (aiMeta.seedText || '')
                        : plainOrig
                );
            const previousOutputs = (resumeAiHistory[historyKey] || [])
                .map(normalizeAiHistoryEntry)
                .filter(Boolean)
                .slice(-1);

            if (triggerButton && resumeAiInFlight.has(triggerButton)) return;
            const currentSource = window.getResumeMakerSource ? window.getResumeMakerSource() : 'manual';
            if (context === 'summary' && currentSource !== 'upload' && !plainOrig) {
                notifyResumeAi('Please write 2-3 lines about yourself first.', 'error');
                targetEl.closest('.summary-editor-shell')?.classList.add('field-needs-attention');
                setTimeout(() => targetEl.closest('.summary-editor-shell')?.classList.remove('field-needs-attention'), 1200);
                targetEl.focus();
                return;
            }
            if (context === 'experience' && !jobRole) {
                notifyResumeAi('Please enter Job Role first.', 'error');
                const roleInput = targetEl.closest('[data-exp]')?.querySelector('[data-k="role"]');
                roleInput?.classList.add('field-needs-attention');
                setTimeout(() => roleInput?.classList.remove('field-needs-attention'), 1200);
                roleInput?.focus();
                return;
            }

            if (context === 'experience') {
                if (activeEditor) {
                    activeEditor.setContent('');
                    activeEditor.save();
                    targetEl.value = '';
                } else {
                    targetEl.value = '';
                }
                targetEl.dispatchEvent(new Event('input', { bubbles: true }));
            }

            const btnOriginalHtml = triggerButton?.innerHTML || '';
            if (triggerButton) {
                resumeAiInFlight.add(triggerButton);
                setAiButtonLoading(triggerButton, true, btnOriginalHtml);
            }
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const app = document.getElementById('create-cv-app');
                const res = await fetch(app.dataset.aiTextUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({
                        context,
                        text: effectiveSeedText,
                        source: currentSource,
                        job_role: jobRole,
                        resume: currentResumePayload({ context, targetEl, seedText: effectiveSeedText }),
                        previous_outputs: previousOutputs,
                        variation_seed: `${Date.now()}-${Math.random().toString(36).slice(2, 8)}-${window.crypto?.getRandomValues ? window.crypto.getRandomValues(new Uint32Array(1))[0] : Math.random()}`
                    })
                });
                const data = await res.json().catch(() => ({}));
                if (!res.ok || !data.text) throw new Error(AI_FAILURE_MESSAGE);
                const generatedPlainText = normalizeAiHistoryEntry(data.text);

                if (activeEditor) {
                    activeEditor.setContent(data.text);
                    activeEditor.save();
                    targetEl.value = activeEditor.getContent();
                } else {
                    targetEl.value = data.text;
                }
                if (context === 'summary') {
                    targetEl.value = data.text;
                }
                aiMeta.seedText = effectiveSeedText;
                aiMeta.lastGenerated = generatedPlainText;
                aiMeta.jobRole = jobRole;
                resumeAiHistory[historyKey] = generatedPlainText ? [generatedPlainText] : [];
                notifyResumeAi(context === 'summary' ? 'Summary rewritten with a fresh AI variation.' : 'Responsibilities generated with a fresh AI variation.', 'success');
            } catch(e) {
                if (activeEditor) {
                    activeEditor.setContent(orig);
                    activeEditor.save();
                    targetEl.value = activeEditor.getContent();
                } else {
                    targetEl.value = orig;
                }
                console.error('AI generate failed:', e);
                notifyResumeAi(AI_FAILURE_MESSAGE, 'error');
                showResumeAiFailureAlert();
                if (triggerButton) {
                    triggerButton.textContent = 'Try Again';
                    setTimeout(() => { triggerButton.innerHTML = btnOriginalHtml; }, 1300);
                }
            } finally {
                if (triggerButton) {
                    resumeAiInFlight.delete(triggerButton);
                    setAiButtonLoading(triggerButton, false, btnOriginalHtml);
                }
            }

            targetEl.dispatchEvent(new Event('input', { bubbles: true }));
        }
        function generateAISummary(btn) { generateAIText('summary', document.getElementById('cv-summary'), btn); }

        document.querySelectorAll('.summary-suggestion').forEach(button => {
            button.addEventListener('click', () => {
                const target = document.getElementById('cv-summary');
                const newText = button.dataset.summaryTemplate || button.textContent.trim();
                if (typeof tinymce !== 'undefined' && tinymce.get('cv-summary')) {
                    const editor = tinymce.get('cv-summary');
                    editor.setContent(newText);
                    editor.save();
                    target.value = editor.getContent();
                } else {
                    target.value = newText;
                }
                target.dispatchEvent(new Event('input', { bubbles: true }));
            });
        });

        function updateSummaryRoleLabel() {
            const label = document.getElementById('summary-role-label');
            if (!label) return;
            label.textContent = document.getElementById('cv-designation')?.value || 'your designation';
        }
        document.getElementById('cv-designation')?.addEventListener('input', updateSummaryRoleLabel);
        updateSummaryRoleLabel();
    </script>

    @include('components.format-download-modal')
    @include('resume.partials.editor-script')

    <script src="https://cdn.tiny.cloud/1/{{ config('services.tinymce.key', 'no-api-key') }}/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
        function initTinyMCE() {
            tinymce.init({
                selector: '.rich-ta, .rp-input-ta',
                menubar: false,
                statusbar: false,
                plugins: 'lists',
                toolbar: 'bold italic underline | bullist numlist | undo redo',
                setup: function (editor) {
                    editor.on('change keyup', function () {
                        editor.save();
                        editor.getElement().dispatchEvent(new Event('input', { bubbles: true }));
                    });
                }
            });
        }
        document.addEventListener('DOMContentLoaded', initTinyMCE);
    </script>
</div>{{-- /create-cv-app --}}

@endsection
