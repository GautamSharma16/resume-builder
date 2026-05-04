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
            text-align: left;
            margin-bottom: 3rem;
            position: relative;
        }

        .rp-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: var(--primary-light);
            color: var(--primary);
            padding: 0.35rem 1rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1.5rem;
            border: 1px solid rgba(79, 70, 229, 0.1);
        }

        .rp-hero h1 {
            font-family: var(--font-display);
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            line-height: 1.1;
            margin-bottom: 1rem;
            color: var(--slate-900);
            font-weight: 400;
        }

        .rp-hero h1 span {
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .rp-hero p {
            color: var(--slate-500);
            font-size: 1.125rem;
            max-width: 600px;
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

        /* ── STEPS ────────────────────────────────────────────── */
        .rp-steps {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2.5rem;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }

        .rp-step {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--slate-500);
            background: var(--slate-100);
            border: 1px solid var(--slate-200);
            white-space: nowrap;
            transition: all 0.2s;
            cursor: pointer;
        }

        .rp-step.active {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.15);
            font-weight: 600;
        }

        .rp-step.completed {
            background: var(--emerald-500);
            color: white;
            border-color: var(--emerald-500);
        }

        .rp-step-number {
            width: 20px;
            height: 20px;
            background: rgba(0,0,0,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
        }

        .rp-step-content {
            display: none;
        }

        .rp-step-content.active,
        #completion-panel.visible {
            display: block !important;
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

        @media (max-width: 640px) {
            .rp-input-grid {
                grid-template-columns: 1fr;
            }
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
            box-shadow: var(--shadow-lg);
        }

        .rp-btn-outline {
            background: white;
            border-color: var(--slate-200);
            color: var(--slate-700);
        }

        .rp-btn-outline:hover {
            background: var(--slate-50);
            border-color: var(--slate-300);
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
            background: white;
            padding: 0.75rem 1.25rem;
            border-radius: var(--radius-xl);
            border: 1px solid var(--slate-200);
            box-shadow: var(--shadow-sm);
        }

        .rp-viewport {
            background: var(--slate-200);
            border-radius: var(--radius-2xl);
            padding: 3rem 2rem;
            height: 80vh;
            overflow: auto;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            box-shadow: inset 0 2px 10px rgba(0,0,0,0.05);
        }

        #cv-preview {
            width: 794px;
            background: white;
            box-shadow: var(--shadow-lg);
            border-radius: 2px;
            transform-origin: top center;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ── SOURCE TOGGLE ────────────────────────────────────── */
        .rp-source-box {
            background: var(--primary-light);
            padding: 1.5rem;
            border-radius: var(--radius-xl);
            border: 1px solid rgba(79, 70, 229, 0.1);
            margin-bottom: 2rem;
        }

        .source-group {
            display: flex;
            background: white;
            padding: 0.25rem;
            border-radius: 0.75rem;
            border: 1px solid var(--slate-200);
            width: fit-content;
        }

        .source-btn {
            padding: 0.5rem 1.25rem;
            border-radius: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--slate-500);
            cursor: pointer;
            transition: all 0.2s;
            border: none;
            background: transparent;
        }

        .source-btn.active {
            background: var(--primary);
            color: white;
            box-shadow: var(--shadow-sm);
        }

        /* ── ENTRY CARDS ──────────────────────────────────────── */
        .entry-card {
            background: var(--slate-50);
            border: 1px solid var(--slate-200);
            border-radius: 1rem;
            padding: 1.25rem;
            margin-bottom: 1rem;
            position: relative;
            transition: all 0.2s;
        }

        .entry-card:hover {
            border-color: var(--primary);
            background: white;
            box-shadow: var(--shadow-sm);
        }

        .rp-upload-panel {
            display: none;
            margin-top: 1.5rem;
        }
        .rp-upload-panel.visible {
            display: block;
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

        .rp-popup-head {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid var(--slate-100);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .rp-popup-body {
            padding: 2rem;
            overflow-y: auto;
        }

        .rp-template-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.5rem;
        }

        .tpl-card {
            border: 2px solid var(--slate-100);
            border-radius: 1rem;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.2s;
        }

        .tpl-card:hover { border-color: var(--primary); }
        .tpl-card.selected { border-color: var(--primary); background: var(--primary-light); }

        .tpl-thumb {
            height: 220px;
            background: var(--slate-50);
            display: flex;
            justify-content: center;
            overflow: hidden;
        }

        /* ── TEMPLATE POPUP CARDS ────────────────────────────── */
        .rp-template-card {
            background: white;
            border-radius: 1.25rem;
            border: 2px solid var(--slate-100);
            overflow: hidden;
            cursor: pointer;
            transition: all 0.2s;
        }

        .rp-template-card:hover {
            border-color: var(--primary);
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .rp-template-card.selected {
            border-color: var(--primary);
            background: var(--primary-light);
        }

        .rp-template-card-preview {
            height: 280px;
            overflow: hidden;
            background: var(--slate-50);
            position: relative;
            display: flex;
            justify-content: center;
        }

        .rp-template-card-preview .preview-content {
            width: 794px;
            background: white;
            transform: scale(0.3);
            transform-origin: top center;
            position: absolute;
            top: 1rem;
            pointer-events: none;
        }

        .rp-template-card-info {
            padding: 1.25rem;
            text-align: center;
        }

        .rp-template-card-info h4 {
            font-weight: 600;
            font-size: 0.9375rem;
            margin: 0 0 0.25rem;
        }

        .rp-template-card-info p {
            font-size: 0.8rem;
            color: var(--slate-500);
            margin: 0;
        }
    </style>

    <div class="rp-page">
        {{-- Hero --}}
        <header class="rp-hero">
            <div class="rp-hero-badge">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                Resume Maker v2.0
            </div>
            <h1>Build your <span>dream resume</span></h1>
            <p>A professional, ATS-friendly resume helps you stand out. Build yours in minutes with our intuitive editor.</p>
        </header>

        <div class="rp-grid">
            {{-- FORM --}}
            <section class="rp-card">
                <div class="rp-card-head">
                    <h2>Edit Resume Details</h2>
                </div>
                <div class="rp-card-body">
                    {{-- Hidden select required by script --}}
                    <select id="template-id" style="display:none">
                        @foreach($templates as $template)
                            <option value="{{ $template->id }}" @selected($selectedTemplateId === $template->id)>{{ $template->name }}</option>
                        @endforeach
                    </select>

                    {{-- Steps --}}
                    <nav class="rp-steps">
                        <div class="rp-step active" data-step="1">
                            <span class="rp-step-number">1</span> Basic Info
                        </div>
                        <div class="rp-step" data-step="2">
                            <span class="rp-step-number">2</span> Experience
                        </div>
                        <div class="rp-step" data-step="3">
                            <span class="rp-step-number">3</span> Education
                        </div>
                        <div class="rp-step" data-step="4">
                            <span class="rp-step-number">4</span> Projects
                        </div>
                    </nav>

                    {{-- Step 1 --}}
                    <div class="rp-step-content active" data-step="1">
                        <div class="rp-source-box">
                            <label class="field-label" style="color: var(--primary);">Already have a resume?</label>
                            <div class="source-group">
                                <button type="button" data-source="upload" class="source-btn">Upload Existing</button>
                                <button type="button" data-source="manual" class="source-btn active">Start Fresh</button>
                            </div>
                            
                            <div id="existing-resume-panel" class="rp-upload-panel">
                                <div style="background: white; padding: 1.25rem; border-radius: 1rem; border: 1px dashed var(--primary);">
                                    <p style="font-size: 0.85rem; color: var(--slate-600); margin-bottom: 1rem;">Upload PDF/DOCX to autofill using AI.</p>
                                    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                                        <label for="resume-autofill-file" class="rp-btn rp-btn-outline" style="padding: 0.5rem 1rem; font-size: 0.85rem;">Choose File</label>
                                        <input id="resume-autofill-file" type="file" accept=".pdf,.doc,.docx">
                                        <span id="rp-file-name" style="font-size: 0.85rem; color: var(--slate-400);">No file chosen</span>
                                        <button id="resume-autofill-button" class="rp-btn rp-btn-primary" style="padding: 0.5rem 1rem; font-size: 0.85rem; margin-left: auto;">Autofill Now</button>
                                    </div>
                                    <p id="resume-autofill-status" style="font-size: 0.8rem; margin-top: 0.75rem;"></p>
                                </div>
                            </div>
                        </div>

                        <div class="rp-input-grid">
                            <div class="field-group">
                                <label class="field-label">Full Name</label>
                                <input id="cv-name" class="rp-input cv-field" placeholder="John Doe" data-field="name">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Email</label>
                                <input id="cv-email" class="rp-input cv-field" placeholder="john@example.com" data-field="email">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Phone</label>
                                <input id="cv-mobile" class="rp-input cv-field" placeholder="+91 98765 43210" data-field="mobile">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Location</label>
                                <input id="cv-location" class="rp-input cv-field" placeholder="City, Country" data-field="location">
                            </div>
                            <div class="field-group" style="grid-column: span 2;">
                                <label class="field-label">Social Links (comma separated)</label>
                                <input id="cv-social" class="rp-input cv-field" placeholder="LinkedIn, GitHub, Portfolio" data-field="social_links">
                            </div>
                            <div class="field-group" style="grid-column: span 2;">
                                <label class="field-label">Summary</label>
                                <textarea id="cv-summary" class="rp-input cv-field" rows="4" placeholder="Briefly describe your career goals..." data-field="summary"></textarea>
                            </div>
                            <div class="field-group" style="grid-column: span 2;">
                                <label class="field-label">Skills (comma separated)</label>
                                <input id="cv-skills" class="rp-input cv-field" placeholder="Python, React, UI/UX" data-field="skills">
                            </div>
                        </div>

                        <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                            <button id="next-step-1" class="rp-btn rp-btn-primary">
                                Next: Experience
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Step 2 --}}
                    <div class="rp-step-content" data-step="2">
                        <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <h3 style="font-weight: 700; color: var(--slate-800);">Work Experience</h3>
                            <button id="add-exp" class="rp-btn rp-btn-ghost" style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 4v16m8-8H4"/></svg>
                                Add Position
                            </button>
                        </div>
                        <div id="exp-editor"></div>

                        <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
                            <button id="prev-step-2" class="rp-btn rp-btn-outline">Back</button>
                            <button id="next-step-2" class="rp-btn rp-btn-primary">Next: Education</button>
                        </div>
                    </div>

                    {{-- Step 3 --}}
                    <div class="rp-step-content" data-step="3">
                        <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <h3 style="font-weight: 700; color: var(--slate-800);">Education</h3>
                            <button id="add-edu" class="rp-btn rp-btn-ghost" style="padding: 0.5rem 1rem; font-size: 0.875rem;">+ Add Education</button>
                        </div>
                        <div id="edu-editor"></div>

                        <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
                            <button id="prev-step-3" class="rp-btn rp-btn-outline">Back</button>
                            <button id="next-step-3" class="rp-btn rp-btn-primary">Next: Projects</button>
                        </div>
                    </div>

                    {{-- Step 4 --}}
                    <div class="rp-step-content" data-step="4">
                        <div style="display:flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <h3 style="font-weight: 700; color: var(--slate-800);">Projects</h3>
                            <button id="add-project" class="rp-btn rp-btn-ghost" style="padding: 0.5rem 1rem; font-size: 0.875rem;">+ Add Project</button>
                        </div>
                        <div id="project-editor"></div>

                        <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
                            <button id="prev-step-4" class="rp-btn rp-btn-outline">Back</button>
                            <button id="save-cv" class="rp-btn rp-btn-primary">
                                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                                Finalize Resume
                            </button>
                        </div>
                    </div>

                    {{-- Completion --}}
                    <div id="completion-panel" style="display:none; text-align: center; padding: 2rem;">
                        <div style="width: 64px; height: 64px; background: var(--emerald-500); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                            <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <h2 style="font-family: var(--font-display); font-size: 2rem; margin-bottom: 0.5rem;">Ready to Download!</h2>
                        <p style="color: var(--slate-500); margin-bottom: 2rem;">Your resume has been saved to your account.</p>
                        <div style="display:flex; gap: 1rem; justify-content: center;">
                            <button id="download-pdf" class="rp-btn rp-btn-primary" style="padding: 1rem 2rem;">Download PDF</button>
                            <button id="edit-resume" class="rp-btn rp-btn-outline" style="padding: 1rem 2rem;">Back to Edit</button>
                        </div>
                    </div>
                </div>

                <div class="rp-card-head" style="border-top: 1px solid var(--slate-100); border-bottom: none; background: transparent;">
                    <p id="cv-status" style="font-size: 0.8rem;"></p>
                </div>
            </section>

            {{-- PREVIEW --}}
            <section class="rp-preview-panel">
                <div class="rp-preview-toolbar">
                    <div style="display:flex; gap: 0.5rem; align-items: center;">
                        <button id="change-template-btn" class="rp-btn rp-btn-outline" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                            Switch Style
                        </button>
                        <span style="height: 20px; width: 1px; background: var(--slate-200);"></span>
                        <span style="font-size: 0.75rem; font-weight: 700; color: var(--slate-400); text-transform: uppercase;">Live Preview</span>
                    </div>
                    <div style="display:flex; align-items: center; gap: 0.25rem; background: var(--slate-100); padding: 0.2rem; border-radius: 0.5rem;">
                        <button id="preview-zoom-out" class="source-btn" style="padding: 0.25rem 0.5rem;">-</button>
                        <span id="preview-zoom-level" style="font-size: 0.75rem; width: 30px; text-align: center;">75%</span>
                        <button id="preview-zoom-in" class="source-btn" style="padding: 0.25rem 0.5rem;">+</button>
                    </div>
                </div>

                <div class="rp-viewport" id="preview-viewport">
                    <article id="cv-preview" class="resume-maker-preview"></article>
                </div>
            </section>
        </div>
    </div>

    {{-- Template Popup --}}
    <div class="rp-popup-overlay" id="template-popup">
        <div class="rp-popup">
            <div class="rp-popup-head">
                <h3 style="font-weight: 700;">Choose a Design</h3>
                <button id="close-template-popup" style="background: none; border: none; cursor: pointer; color: var(--slate-400);">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="rp-popup-body">
                <div class="rp-template-grid" id="template-grid">
                    @foreach($templates as $template)
                    <div class="tpl-card" data-id="{{ $template->id }}">
                        <div class="tpl-thumb">
                            <div class="tpl-scaler">
                                <div style="width: 794px; min-height: 1123px; background: white;">
                                    {{-- Render logic handled by JS --}}
                                </div>
                            </div>
                        </div>
                        <div style="padding: 1rem; font-size: 0.875rem; font-weight: 600; text-align: center;">{{ $template->name }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Adjust scalePreview to cap at 0.85
    window.addEventListener('load', () => {
        const viewport = document.getElementById('preview-viewport');
        const preview = document.getElementById('cv-preview');
        
        function updateScale() {
            if(!viewport || !preview) return;
            const padding = 64;
            const availW = viewport.clientWidth - padding;
            const scale = Math.min(availW / 794, 0.85);
            preview.style.transform = `scale(${scale})`;
            preview.style.marginBottom = `-${1123 * (1 - scale)}px`;
            document.getElementById('preview-zoom-level').textContent = Math.round(scale * 100) + '%';
        }

        window.addEventListener('resize', updateScale);
        updateScale();
        
        // Watch for changes in preview content
        const observer = new MutationObserver(updateScale);
        observer.observe(preview, { childList: true, subtree: true, characterData: true });
    });
</script>

@include('resume.partials.editor-script')
@endsection
