@extends('layouts.app')

@section('title', 'Edit Resume | Cvbliss')

@section('content')
@php
    $requiresPlanForDownload = auth()->check() && ! auth()->user()->activeSubscription?->hasDownloadsRemaining() && ! $resume->is_paid;
@endphp

<div id="create-cv-app"
    class="min-h-screen rp-root"
    data-update-url="{{ route('resume.update', $resume) }}"
    data-resume-id="{{ $resume->id }}"
    data-authenticated="1"
    data-plans-url="{{ route('plans') }}"
    data-download-requires-plan="{{ $requiresPlanForDownload ? '1' : '0' }}"
    data-ai-text-url="{{ route('resume.ai-text') }}"
    @if($resume->template_id) data-selected-template="{{ $resume->template_id }}" @endif>

    <script type="application/json" id="resume-templates-json">@json($templates->keyBy('id'))</script>
    <script type="application/json" id="resume-initial-json">@json($resume->data)</script>

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #eff6ff;
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: #cbd5e1;
            --slate-400: #94a3b8;
            --slate-500: #64748b;
            --slate-600: #475569;
            --slate-700: #334155;
            --slate-800: #1e293b;
            --slate-900: #0b1221;
            --emerald-500: #10b981;
            --emerald-600: #059669;
            
            --font-sans: var(--font-body);
            --font-display: var(--font-display);
            
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --radius-xl: 1rem;
            --radius-2xl: 1.5rem;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--slate-50);
            color: var(--slate-900);
            font-weight: 400;
        }

        .rp-root {
            background: radial-gradient(circle at top right, rgba(79, 70, 229, 0.04), transparent),
                        radial-gradient(circle at bottom left, rgba(139, 92, 246, 0.04), transparent);
        }

        .rp-page {
            max-width: 1440px;
            margin: 0 auto;
            padding: 2rem 1.5rem 5rem;
        }

        /* ── HERO ─────────────────────────────────────────────── */
        .rp-hero {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 3rem;
            flex-wrap: wrap;
            gap: 2rem;
        }

        .rp-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--emerald-500);
            color: white;
            padding: 0.35rem 1rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.5rem;
        }

        .rp-hero h1 {
            font-family: var(--font-display);
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.1;
            margin: 0;
            color: var(--slate-900);
            font-weight: 400;
        }

        .rp-hero h1 span {
            font-style: italic;
            color: var(--primary);
        }

        .rp-dl-group {
            display: flex;
            gap: 0.75rem;
            align-items: center;
        }

        .rp-dl-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.25rem;
            border-radius: 0.75rem;
            font-weight: 700;
            font-size: 0.875rem;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid var(--slate-200);
            background: white;
            color: var(--slate-700);
        }

        .rp-dl-btn:hover {
            border-color: var(--primary);
            color: var(--primary);
            background: var(--primary-light);
            transform: translateY(-1px);
        }

        .rp-dl-btn.primary {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: var(--shadow-md);
        }

        .rp-dl-btn.primary:hover {
            background: var(--primary-dark);
            box-shadow: var(--shadow-lg);
        }

        /* ── GRID ─────────────────────────────────────────────── */
        .rp-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2.5rem;
            align-items: start;
        }

        @media (min-width: 1024px) {
            .rp-grid {
                grid-template-columns: 1fr 1.2fr;
            }
        }

        /* ── CARDS ────────────────────────────────────────────── */
        .rp-card {
            background: white;
            border-radius: var(--radius-2xl);
            border: 1px solid var(--slate-200);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }

        .rp-card-head {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--slate-100);
            background: var(--slate-50);
        }

        .rp-card-head h2 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--slate-900);
        }

        .rp-card-body {
            padding: 2rem;
        }

        /* ── INPUTS ───────────────────────────────────────────── */
        .field-group {
            margin-bottom: 1.5rem;
        }

        .field-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--slate-700);
            margin-bottom: 0.5rem;
        }

        .rp-input {
            width: 100%;
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid var(--slate-200);
            background: var(--slate-50);
            font-family: var(--font-sans);
            font-size: 0.9375rem;
            color: var(--slate-900);
            transition: all 0.2s;
        }

        .rp-input:focus {
            outline: none;
            border-color: var(--primary);
            background: white;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .rp-input-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.25rem;
        }

        /* ── BUTTONS ──────────────────────────────────────────── */
        .rp-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .rp-btn-primary {
            background: var(--primary);
            color: white;
            box-shadow: var(--shadow-md);
        }

        .rp-btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
        }

        .rp-btn-ghost {
            background: var(--primary-light);
            color: var(--primary);
            border: none;
        }

        /* ── PREVIEW ──────────────────────────────────────────── */
        .rp-preview-panel {
            position: sticky;
            top: 2rem;
        }

        .rp-preview-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding: 0.5rem 0;
        }

        .rp-viewport {
            height: 85vh;
            overflow: auto;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 0;
        }

        #cv-preview {
            width: 794px;
            background: white;
            box-shadow: var(--shadow-lg);
            border-radius: 2px;
            transform-origin: top center;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .entry-card {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 1rem;
            padding: 1.25rem;
            margin-bottom: 1rem;
        }

        /* ── POPUP ────────────────────────────────────────────── */
        .rp-popup-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(8px);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .rp-popup-overlay.visible { display: flex; }

        .rp-popup {
            background: white;
            border-radius: 2rem;
            width: 100%;
            max-width: 1000px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
        }
    </style>

    <div class="rp-page">
        {{-- Hero --}}
        <header class="rp-hero">
            <div>
                <div class="rp-hero-badge">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    Resume Editor
                </div>
                <h1>Editing <span>{{ $resume->title }}</span></h1>
            </div>
            <div class="rp-dl-group">
                <a class="rp-dl-btn primary" href="{{ route('resume.download', [$resume, 'pdf']) }}" @if($requiresPlanForDownload) data-open-plan-modal @endif>PDF</a>
                <a class="rp-dl-btn" href="{{ route('resume.download', [$resume, 'doc']) }}" @if($requiresPlanForDownload) data-open-plan-modal @endif>DOC</a>
                <a class="rp-dl-btn" href="{{ route('resume.download', [$resume, 'ppt']) }}" @if($requiresPlanForDownload) data-open-plan-modal @endif>PPT</a>
            </div>
        </header>

        <div class="rp-grid">
            {{-- FORM --}}
            <section class="rp-card">
                <div class="rp-card-head">
                    <h2>Resume Content</h2>
                </div>
                <div class="rp-card-body">
                    {{-- Hidden template button for script consistency --}}
                    <button id="change-template-btn" style="display:none"></button>
                    {{-- Hidden popup elements for script consistency --}}
                    <div id="template-popup" style="display:none"><div id="template-grid"></div><button id="close-template-popup"></button></div>
                    
                    <div class="field-group">
                        <label class="field-label">Template</label>
                        <select id="template-id" class="rp-input">
                            <option value="">Classic ATS Resume</option>
                            @foreach($templates as $template)
                                <option value="{{ $template->id }}" @selected($resume->template_id === $template->id)>{{ $template->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Resume Color</label>
                        <div style="display: flex; gap: 0.75rem; align-items: center; margin-top: 0.5rem;">
                            @php
                                $colors = [
                                    '#2563eb' => 'Blue',
                                    '#10b981' => 'Emerald',
                                    '#475569' => 'Slate',
                                    '#e11d48' => 'Rose',
                                    '#4f46e5' => 'Indigo',
                                ];
                                $savedColor = $resume->data['primary_color'] ?? '';
                                $hasCustomColor = (($resume->data['primary_color_customized'] ?? false) && $savedColor !== '')
                                    || ($savedColor !== '' && $savedColor !== '#2563eb');
                                $currentColor = $hasCustomColor ? $savedColor : '';
                            @endphp
                            <button type="button"
                                class="color-option {{ $currentColor === '' ? 'active' : '' }}"
                                data-color=""
                                title="Original"
                                style="height: 32px; border-radius: 999px; background-color: white; border: 3px solid {{ $currentColor === '' ? 'var(--slate-900)' : '#e5e7eb' }}; color: var(--slate-600); font-size: 12px; font-weight: 700; cursor: pointer; transition: all 0.2s; padding: 0 10px;">
                                Original
                            </button>
                            @foreach($colors as $hex => $name)
                                <button type="button" 
                                    class="color-option {{ $currentColor === $hex ? 'active' : '' }}" 
                                    data-color="{{ $hex }}"
                                    title="{{ $name }}"
                                    style="width: 32px; height: 32px; border-radius: 50%; background-color: {{ $hex }}; border: 3px solid {{ $currentColor === $hex ? 'var(--slate-900)' : 'transparent' }}; cursor: pointer; transition: all 0.2s; padding: 0;">
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <hr style="border: none; border-top: 1px solid var(--slate-100); margin: 2rem 0;">

                    {{-- Sections --}}
                    <div class="field-group">
                        <h3 style="font-weight: 700; margin-bottom: 1.25rem;">Personal Information</h3>
                        <div class="rp-input-grid">
                            <div class="field-group">
                                <label class="field-label">Full Name</label>
                                <input id="cv-name" class="rp-input cv-field" placeholder="Full name" data-field="name">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Email</label>
                                <input id="cv-email" class="rp-input cv-field" placeholder="Email" data-field="email">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Phone</label>
                                <input id="cv-mobile" class="rp-input cv-field" placeholder="Mobile" data-field="mobile">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Location</label>
                                <input id="cv-location" class="rp-input cv-field" placeholder="City, Country" data-field="location">
                            </div>
                            <div class="field-group" style="grid-column: span 2;">
                                <label class="field-label">Social Links (comma separated)</label>
                                <input id="cv-social" class="rp-input cv-field" data-field="social_links">
                            </div>
                        </div>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Professional Summary</label>
                        <textarea id="cv-summary" class="rp-input cv-field" rows="4" data-field="summary"></textarea>
                    </div>

                    <div class="field-group">
                        <label class="field-label">Skills (comma separated)</label>
                        <input id="cv-skills" class="rp-input cv-field" data-field="skills">
                    </div>

                    <div class="field-group">
                        <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h3 style="font-weight: 700;">Experience</h3>
                            <button id="add-exp" class="rp-btn rp-btn-ghost" style="padding: 0.4rem 0.8rem; font-size: 0.875rem;">+ Add Entry</button>
                        </div>
                        <div id="exp-editor"></div>
                    </div>

                    <div class="field-group">
                        <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h3 style="font-weight: 700;">Education</h3>
                            <button id="add-edu" class="rp-btn rp-btn-ghost" style="padding: 0.4rem 0.8rem; font-size: 0.875rem;">+ Add Entry</button>
                        </div>
                        <div id="edu-editor"></div>
                    </div>

                    <div class="field-group">
                        <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <h3 style="font-weight: 700;">Projects</h3>
                            <button id="add-project" class="rp-btn rp-btn-ghost" style="padding: 0.4rem 0.8rem; font-size: 0.875rem;">+ Add Entry</button>
                        </div>
                        <div id="project-editor"></div>
                    </div>

                </div>
                <div class="rp-card-head" style="border-top: 1px solid var(--slate-100); border-bottom: none; background: transparent; display: flex; justify-content: space-between; align-items: center;">
                    <button id="save-cv" class="rp-btn rp-btn-primary">Save Changes</button>
                    <p id="cv-status" style="font-size: 0.8rem;"></p>
                </div>
            </section>

            {{-- PREVIEW --}}
            <section class="rp-preview-panel">
                <div class="rp-preview-toolbar">
                    <span style="font-size: 0.75rem; font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Live Preview</span>
                    <div style="display:flex; align-items: center; gap: 0.25rem; background: var(--slate-100); padding: 0.2rem; border-radius: 0.5rem;">
                        <button id="preview-zoom-out" class="rp-btn" style="padding: 0.2rem 0.5rem; background: transparent; border: none;">-</button>
                        <span id="preview-zoom-level" style="font-size: 0.75rem; width: 30px; text-align: center;">75%</span>
                        <button id="preview-zoom-in" class="rp-btn" style="padding: 0.2rem 0.5rem; background: transparent; border: none;">+</button>
                    </div>
                </div>
                <div class="rp-viewport" id="preview-viewport">
                    <article id="cv-preview" class="resume-maker-preview"></article>
                </div>
            </section>
        </div>
    </div>
</div>

<script>
(function () {
    /* ── helpers ── */
    function setActiveStep(step) {
        const n = parseInt(step);
 
        /* update tab indicators */
        document.querySelectorAll('.rp-step-tab').forEach(tab => {
            const t = parseInt(tab.dataset.step);
            tab.classList.remove('active', 'completed');
            if (t === n)      tab.classList.add('active');
            else if (t < n)   tab.classList.add('completed');
 
            /* swap icon to check-mark when completed */
            const icon = tab.querySelector('.rp-step-icon');
            if (icon) {
                if (t < n) {
                    icon.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>`;
                } else {
                    icon.textContent = t; /* reset to number */
                }
            }
        });
 
        /* show / hide step content panels */
        document.querySelectorAll('.rp-step-content').forEach(panel => {
            panel.classList.toggle('active', parseInt(panel.dataset.step) === n);
        });
    }
 
    /* ── wire step tabs (clicking a completed tab goes back) ── */
    document.querySelectorAll('.rp-step-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const target = parseInt(tab.dataset.step);
            /* only allow jumping to already-visited steps or current */
            const current = parseInt(document.querySelector('.rp-step-tab.active')?.dataset.step || 1);
            if (target <= current) setActiveStep(target);
        });
    });
 
    /* ── wire next / prev buttons ── */
    function wireNav(btnId, direction) {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        btn.addEventListener('click', () => {
            const current = parseInt(document.querySelector('.rp-step-tab.active')?.dataset.step || 1);
            setActiveStep(current + direction);
        });
    }
 
    wireNav('next-step-1',  +1);
    wireNav('next-step-2',  +1);
    wireNav('next-step-3',  +1);
    wireNav('prev-step-2',  -1);
    wireNav('prev-step-3',  -1);
    wireNav('prev-step-4',  -1);
 
    /* ── "Back to Edit" from completion panel ── */
    document.getElementById('edit-resume')?.addEventListener('click', () => {
        document.getElementById('completion-panel').style.display = 'none';
        setActiveStep(4);
    });
 
    /* ── initialise on load ── */
    setActiveStep(1);
})();
</script>

@include('resume.partials.editor-script')
@endsection
