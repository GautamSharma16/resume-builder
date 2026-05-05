@extends('layouts.app')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Playfair+Display:ital,wght@0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">

<style>
/* ─── DESIGN SYSTEM ─────────────────────────────────────────────────── */
:root {
    --bg-main:      #faf9f6;
    --bg-card:      #ffffff;
    --border-light: #eaeaea;
    --border-focus: #c1a57b;
    --text-primary: #1a1a1a;
    --text-secondary:#5a5a5a;
    --text-muted:   #8a8a8a;
    --accent:       #c1a57b;
    --accent-dark:  #a8885e;
    --accent-light: #f5efe6;
    --green:        #5a7c5c;
    --green-light:  #eef3ec;
    --navy:         #2c3e4e;
    --navy-light:   #eef2f5;
    --shadow-sm:    0 2px 8px rgba(0,0,0,.04), 0 1px 2px rgba(0,0,0,.02);
    --shadow-md:    0 8px 24px rgba(0,0,0,.06), 0 2px 4px rgba(0,0,0,.02);
    --shadow-lg:    0 20px 40px rgba(0,0,0,.08), 0 4px 12px rgba(0,0,0,.03);
    --radius-sm:    8px;
    --radius-md:    12px;
    --radius-lg:    20px;
    --radius-xl:    28px;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Inter', sans-serif;
    color: var(--text-primary);
    background: var(--bg-main);
    line-height: 1.5;
}

/* ─── LAYOUT ───────────────────────────────────────────────────────── */
.cl-page {
    min-height: 100vh;
    padding: 2rem 2rem 5rem;
}

.cl-container {
    max-width: 1280px;
    margin: 0 auto;
}

/* ─── TYPOGRAPHY ───────────────────────────────────────────────────── */
.page-header {
    margin-bottom: 2.5rem;
    text-align: center;
}

.eyebrow {
    font-size: 0.7rem;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--accent);
    font-weight: 600;
    margin-bottom: 0.75rem;
}

.page-header h1 {
    font-family: 'Playfair Display', serif;
    font-size: 2.8rem;
    font-weight: 500;
    color: var(--text-primary);
    letter-spacing: -0.01em;
    margin-bottom: 0.5rem;
}

.page-header h1 em {
    font-style: italic;
    color: var(--accent);
    font-weight: 400;
}

.page-header p {
    font-size: 0.9rem;
    color: var(--text-secondary);
    max-width: 480px;
    margin: 0 auto;
}

/* ─── STEPPER ──────────────────────────────────────────────────────── */
.stepper-wrap {
    margin-bottom: 2rem;
    overflow-x: auto;
}

.stepper {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    min-width: 520px;
}

.step-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    cursor: pointer;
    transition: all 0.2s ease;
    border-radius: 100px;
    background: transparent;
}

.step-bubble {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
    font-weight: 600;
    background: var(--bg-card);
    border: 1.5px solid var(--border-light);
    color: var(--text-muted);
    transition: all 0.2s ease;
}

.step-text {
    display: flex;
    flex-direction: column;
}

.step-label {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-muted);
}

.step-caption {
    font-size: 0.65rem;
    color: var(--text-muted);
}

.step-item.is-active {
    background: var(--accent-light);
}

.step-item.is-active .step-bubble {
    background: var(--accent);
    border-color: var(--accent);
    color: white;
}

.step-item.is-active .step-label,
.step-item.is-active .step-caption {
    color: var(--accent-dark);
}

.step-item.is-done .step-bubble {
    background: var(--green);
    border-color: var(--green);
    color: white;
}

.step-connector {
    width: 48px;
    height: 2px;
    background: var(--border-light);
    transition: all 0.3s ease;
}

.step-connector.is-done {
    background: var(--green);
}

/* ─── CARDS ───────────────────────────────────────────────────────── */
.card {
    background: var(--bg-card);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    overflow: hidden;
    border: 1px solid var(--border-light);
}

.card-header {
    padding: 1.25rem 1.75rem;
    border-bottom: 1px solid var(--border-light);
    background: var(--bg-card);
}

.card-header h2 {
    font-family: 'Playfair Display', serif;
    font-size: 1.25rem;
    font-weight: 500;
    color: var(--text-primary);
}

.card-header p {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-top: 0.2rem;
}

.card-body {
    padding: 1.75rem;
}

/* ─── STEP 1 - TEMPLATES GRID ─────────────────────────────────────── */
.templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 1.25rem;
    margin-bottom: 1.75rem;
}

.template-card {
    border: 2px solid var(--border-light);
    border-radius: var(--radius-md);
    overflow: hidden;
    cursor: pointer;
    transition: all 0.25s ease;
    background: white;
}

.template-card:hover {
    border-color: var(--accent);
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.template-card.selected {
    border-color: var(--accent);
    background: var(--accent-light);
}

.template-thumb {
    height: 200px;
    background: #f5f2ed;
    overflow: hidden;
    position: relative;
}

.template-thumb-inner {
    transform: scale(0.42);
    transform-origin: top left;
    width: 238%;
    pointer-events: none;
}

.template-name {
    padding: 0.75rem 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    border-top: 1px solid var(--border-light);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.template-badge {
    font-size: 0.6rem;
    font-weight: 600;
    padding: 0.2rem 0.5rem;
    background: var(--green-light);
    color: var(--green);
    border-radius: 100px;
}

/* ─── FORM ELEMENTS ───────────────────────────────────────────────── */
.form-grid {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

.field-wrap {
    display: flex;
    flex-direction: column;
    gap: 0.4rem;
}

.field-label {
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--text-secondary);
}

.field-label .required {
    color: #c17a7a;
}

.field-hint {
    font-size: 0.65rem;
    color: var(--text-muted);
}

.cl-input {
    width: 100%;
    padding: 0.7rem 1rem;
    border: 1.5px solid var(--border-light);
    border-radius: var(--radius-sm);
    font-family: 'Inter', sans-serif;
    font-size: 0.85rem;
    transition: all 0.2s ease;
    background: var(--bg-main);
}

.cl-input:focus {
    outline: none;
    border-color: var(--accent);
    background: white;
    box-shadow: 0 0 0 3px rgba(193,165,123,.1);
}

textarea.cl-input {
    resize: vertical;
    line-height: 1.5;
}

.resume-select-row {
    background: var(--navy-light);
    border: 1px solid rgba(44,62,78,.15);
    border-radius: var(--radius-md);
    padding: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.resume-select-row label {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--navy);
}

.resume-select-row select {
    flex: 1;
    padding: 0.5rem 0.75rem;
    border: 1px solid var(--border-light);
    border-radius: var(--radius-sm);
    font-family: 'Inter', sans-serif;
    font-size: 0.8rem;
    background: white;
}

/* ─── BUTTONS ─────────────────────────────────────────────────────── */
.btn-nav {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.65rem 1.5rem;
    border-radius: 100px;
    font-family: 'Inter', sans-serif;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
    border: none;
}

.btn-back {
    background: transparent;
    color: var(--text-secondary);
    border: 1px solid var(--border-light);
}

.btn-back:hover {
    background: var(--bg-main);
    border-color: var(--accent);
    transform: translateX(-2px);
}

.btn-next {
    background: var(--navy);
    color: white;
}

.btn-next:hover {
    background: #1a2a38;
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.btn-next.teal {
    background: var(--accent);
}

.btn-next.teal:hover {
    background: var(--accent-dark);
}

.btn-generate {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    width: 100%;
    padding: 0.85rem;
    background: var(--accent);
    color: white;
    border: none;
    border-radius: 100px;
    font-family: 'Inter', sans-serif;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.25s ease;
}

.btn-generate:hover {
    background: var(--accent-dark);
    transform: translateY(-1px);
}

.btn-generate:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-save {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.6rem 1.2rem;
    background: transparent;
    border: 1px solid var(--border-light);
    border-radius: 100px;
    font-family: 'Inter', sans-serif;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--text-secondary);
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-save:hover {
    border-color: var(--accent);
    color: var(--accent);
}

.btn-download {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.6rem 1.2rem;
    background: var(--green);
    color: white;
    border: none;
    border-radius: 100px;
    font-family: 'Inter', sans-serif;
    font-size: 0.75rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-download:hover {
    background: #4a6b4c;
    transform: translateY(-1px);
}

.btn-download.hidden {
    display: none;
}

.step-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 1.75rem;
    padding-top: 0.5rem;
}

.action-row {
    display: flex;
    gap: 0.75rem;
    margin-top: 1rem;
    flex-wrap: wrap;
}

/* ─── GENERATE INDICATOR ─────────────────────────────────────────── */
.generating-indicator {
    display: none;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    background: var(--accent-light);
    border-radius: var(--radius-md);
    margin-bottom: 1rem;
}

.generating-indicator.active {
    display: flex;
}

.gen-spinner {
    width: 18px;
    height: 18px;
    border: 2px solid var(--border-light);
    border-top-color: var(--accent);
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* ─── PREVIEW PANEL ──────────────────────────────────────────────── */
.preview-wrap {
    background: #f5f2ed;
    border-radius: var(--radius-md);
    padding: 1.5rem;
    overflow: auto;
    max-height: 70vh;
}

#cl-preview, #cl-preview-s4 {
    width: 100%;
    max-width: 100%;
    background: white;
    border-radius: var(--radius-sm);
    box-shadow: var(--shadow-sm);
    margin: 0 auto;
}

/* ─── STEP 4 - TEMPLATE SWITCHER ────────────────────────────────── */
.change-layout {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 1.5rem;
}

.template-switcher {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}

.template-switcher-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem;
    border: 1px solid var(--border-light);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
}

.template-switcher-item:hover {
    border-color: var(--accent);
    background: var(--accent-light);
}

.template-switcher-item.selected {
    border-color: var(--accent);
    background: var(--accent-light);
}

.tmpl-mini {
    width: 48px;
    height: 56px;
    border-radius: 4px;
    overflow: hidden;
    background: #f5f2ed;
    flex-shrink: 0;
}

.tmpl-mini-inner {
    transform: scale(0.1);
    transform-origin: top left;
    width: 1000%;
    pointer-events: none;
}

.tmpl-info .name {
    font-size: 0.8rem;
    font-weight: 600;
    color: var(--text-primary);
}

.tmpl-info .desc {
    font-size: 0.65rem;
    color: var(--text-muted);
}

#active-template-label {
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--accent);
    background: var(--accent-light);
    padding: 0.25rem 0.75rem;
    border-radius: 100px;
}

/* ─── STATUS CHIP ────────────────────────────────────────────────── */
.cl-status-chip {
    display: none;
    align-items: center;
    gap: 0.5rem;
    padding: 0.4rem 1rem;
    background: var(--green-light);
    color: var(--green);
    border-radius: 100px;
    font-size: 0.7rem;
    font-weight: 600;
}

.cl-status-chip.active {
    display: inline-flex;
}

.cl-status-chip.error {
    background: #fee;
    color: #c17a7a;
}

/* ─── DIVIDERS ───────────────────────────────────────────────────── */
.section-divider {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0.5rem 0;
}

.section-divider span {
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--text-muted);
}

.section-divider::before,
.section-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border-light);
}

/* ─── RESPONSIVE ─────────────────────────────────────────────────── */
@media (max-width: 900px) {
    .change-layout {
        grid-template-columns: 1fr;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .cl-page {
        padding: 1rem;
    }
    
    .page-header h1 {
        font-size: 2rem;
    }
    
    .card-body {
        padding: 1.25rem;
    }
}

@media (max-width: 600px) {
    .stepper {
        justify-content: flex-start;
    }
    
    .step-text {
        display: none;
    }
    
    .templates-grid {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    }
}
</style>

<div id="cover-letter-app" class="cl-page" data-generate-url="{{ route('cover-letter.generate') }}">
    <div class="cl-container">

        {{-- Header --}}
        <header class="page-header">
            <p class="eyebrow">Professional Correspondence</p>
            <h1>Craft a <em>cover letter</em> that tells your story</h1>
            <p>Thoughtfully designed templates paired with AI assistance — edit everything to make it yours.</p>
        </header>

        {{-- Stepper --}}
        <div class="stepper-wrap">
            <div class="stepper" id="stepper">
                <div class="step-item is-active" data-step="1" id="step-tab-1">
                    <div class="step-bubble" id="step-bubble-1">1</div>
                    <div class="step-text">
                        <span class="step-label">Template</span>
                        <span class="step-caption">Choose a design</span>
                    </div>
                </div>
                <div class="step-connector" id="step-conn-1"></div>
                <div class="step-item" data-step="2" id="step-tab-2">
                    <div class="step-bubble" id="step-bubble-2">2</div>
                    <div class="step-text">
                        <span class="step-label">Details</span>
                        <span class="step-caption">Your information</span>
                    </div>
                </div>
                <div class="step-connector" id="step-conn-2"></div>
                <div class="step-item" data-step="3" id="step-tab-3">
                    <div class="step-bubble" id="step-bubble-3">3</div>
                    <div class="step-text">
                        <span class="step-label">Write</span>
                        <span class="step-caption">Generate & edit</span>
                    </div>
                </div>
                <div class="step-connector" id="step-conn-3"></div>
                <div class="step-item" data-step="4" id="step-tab-4">
                    <div class="step-bubble" id="step-bubble-4">4</div>
                    <div class="step-text">
                        <span class="step-label">Refine</span>
                        <span class="step-caption">Change template</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 1: Choose Template --}}
        <div class="step-panel active" id="panel-1">
            <div class="card">
                <div class="card-header">
                    <h2>Select a cover letter style</h2>
                    <p>Each template adapts to your content — you can switch later</p>
                </div>
                <div class="card-body">
                    <div class="templates-grid" id="templates-grid">
                        @foreach($templates as $template)
                        <div class="template-card" data-template-id="{{ $template->id }}" id="tmpl-card-{{ $template->id }}">
                            <div class="template-thumb">
                                <div class="template-thumb-inner">
                                    {!! $renderedTemplates[$template->id] !!}
                                </div>
                            </div>
                            <div class="template-name">
                                {{ $template->name }}
                                <span class="template-badge">Free</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <div class="step-nav">
                        <span id="template-hint" style="font-size:0.75rem;color:var(--text-muted);">
                            Select a template to continue
                        </span>
                        <button class="btn-nav btn-next teal" id="btn-1-next" disabled>
                            Continue
                            <svg width="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 2: Fill Details --}}
        <div class="step-panel" id="panel-2">
            <div class="card">
                <div class="card-header">
                    <h2>Tell us about yourself and the role</h2>
                    <p>The more context you provide, the better the draft</p>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div class="resume-select-row">
                            <svg width="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <label for="cl-resume">Import from saved resume</label>
                            <select id="cl-resume">
                                <option value="">— Manual entry —</option>
                                @foreach($resumes as $resume)
                                    <option value="{{ $resume->id }}">{{ $resume->title }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="section-divider"><span>About you</span></div>

                        <div class="form-row">
                            <div class="field-wrap">
                                <label class="field-label" for="cl-name">Full name <span class="required">*</span></label>
                                <input id="cl-name" class="cl-input" type="text" placeholder="e.g. Sarah Chen" value="{{ $prefill['name'] }}">
                            </div>
                            <div class="field-wrap">
                                <label class="field-label" for="cl-email">Email</label>
                                <input id="cl-email" class="cl-input" type="email" placeholder="sarah@example.com" value="{{ $prefill['email'] }}">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="field-wrap">
                                <label class="field-label" for="cl-mobile">Phone</label>
                                <input id="cl-mobile" class="cl-input" type="tel" placeholder="+1 234 567 8900" value="{{ $prefill['mobile'] }}">
                            </div>
                            <div class="field-wrap">
                                <label class="field-label" for="cl-location">Location</label>
                                <input id="cl-location" class="cl-input" type="text" placeholder="San Francisco, CA" value="{{ $prefill['location'] }}">
                            </div>
                        </div>

                        <div class="section-divider"><span>The opportunity</span></div>

                        <div class="form-row">
                            <div class="field-wrap">
                                <label class="field-label" for="cl-company">Company <span class="required">*</span></label>
                                <input id="cl-company" class="cl-input" type="text" placeholder="e.g. Stripe" value="{{ $prefill['company'] }}">
                            </div>
                            <div class="field-wrap">
                                <label class="field-label" for="cl-role">Job title <span class="required">*</span></label>
                                <input id="cl-role" class="cl-input" type="text" placeholder="e.g. Product Designer" value="{{ $prefill['job_role'] }}">
                            </div>
                        </div>

                        <div class="field-wrap">
                            <label class="field-label" for="cl-skills">Key skills</label>
                            <input id="cl-skills" class="cl-input" type="text" placeholder="UX research, Figma, prototyping, user testing" value="{{ $prefill['skills'] }}">
                            <span class="field-hint">Comma-separated — these will appear naturally in the letter</span>
                        </div>

                        <div class="field-wrap">
                            <label class="field-label" for="cl-description">Job description (optional)</label>
                            <textarea id="cl-description" class="cl-input" rows="4" placeholder="Paste the job description here for a more tailored letter..."></textarea>
                            <span class="field-hint">Helps the AI match your experience to their needs</span>
                        </div>
                    </div>

                    <div class="step-nav">
                        <button class="btn-nav btn-back" id="btn-2-back">
                            <svg width="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                            Back
                        </button>
                        <button class="btn-nav btn-next" id="btn-2-next">
                            Generate draft
                            <svg width="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 3: Generate & Edit --}}
        <div class="step-panel" id="panel-3">
            <div class="card">
                <div class="card-header">
                    <h2>Refine your letter</h2>
                    <p>Edit the text directly — the preview updates as you type</p>
                </div>
                <div class="card-body">
                    <div class="generating-indicator" id="gen-indicator">
                        <div class="gen-spinner"></div>
                        <span>Writing your draft...</span>
                    </div>

                    <div class="gen-layout" style="display:grid; grid-template-columns:1fr 1fr; gap:1.5rem; align-items:start;">
                        <div>
                            <div class="field-wrap">
                                <label class="field-label">Letter content</label>
                                <textarea id="cl-body" class="cl-input" rows="14" placeholder="Your letter will appear here. You can edit it directly."></textarea>
                            </div>
                            <div class="form-row" style="margin-top:1rem;">
                                <div class="field-wrap">
                                    <label class="field-label">Name</label>
                                    <input id="cl-name-s3" class="cl-input" type="text" placeholder="Full name">
                                </div>
                                <div class="field-wrap">
                                    <label class="field-label">Company</label>
                                    <input id="cl-company-s3" class="cl-input" type="text" placeholder="Company name">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="field-wrap">
                                    <label class="field-label">Email</label>
                                    <input id="cl-email-s3" class="cl-input" type="email" placeholder="Email">
                                </div>
                                <div class="field-wrap">
                                    <label class="field-label">Phone</label>
                                    <input id="cl-mobile-s3" class="cl-input" type="tel" placeholder="Phone">
                                </div>
                            </div>
                            <button type="button" class="btn-generate" id="generate-letter" style="margin-top:1rem;">
                                <svg width="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Generate new draft
                            </button>
                            <div class="action-row">
                                <button type="button" class="btn-save" id="save-letter">
                                    <svg width="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                    Save
                                </button>
                                <a id="download-letter" href="#" class="btn-download hidden">
                                    <svg width="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
                                    Download PDF
                                </a>
                                <div class="cl-status-chip" id="cl-status"></div>
                            </div>
                        </div>
                        <div>
                            <div style="margin-bottom:0.5rem;">
                                <span style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);">Preview</span>
                            </div>
                            <div class="preview-wrap">
                                <article id="cl-preview"></article>
                            </div>
                        </div>
                    </div>

                    <div class="step-nav" style="margin-top:1.5rem;">
                        <button class="btn-nav btn-back" id="btn-3-back">
                            <svg width="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                            Back
                        </button>
                        <button class="btn-nav btn-next teal" id="btn-3-next">
                            Choose different template
                            <svg width="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Step 4: Change Template --}}
        <div class="step-panel" id="panel-4">
            <div class="card">
                <div class="card-header">
                    <h2>Try a different style</h2>
                    <p>Click any template below to see how your letter looks — no content lost</p>
                </div>
                <div class="card-body">
                    <div class="change-layout">
                        <div>
                            <div style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);margin-bottom:1rem;">Available templates</div>
                            <div class="template-switcher" id="template-switcher">
                                @foreach($templates as $template)
                                <div class="template-switcher-item" data-template-id="{{ $template->id }}" id="tsw-{{ $template->id }}">
                                    <div class="tmpl-mini">
                                        <div class="tmpl-mini-inner">
                                            {!! $renderedTemplates[$template->id] !!}
                                        </div>
                                    </div>
                                    <div class="tmpl-info">
                                        <div class="name">{{ $template->name }}</div>
                                        <div class="desc">Clean, professional layout</div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;">
                                <span style="font-size:0.7rem;font-weight:600;text-transform:uppercase;letter-spacing:0.1em;color:var(--text-muted);">Preview with selected template</span>
                                <span id="active-template-label" style="font-size:0.7rem;font-weight:600;color:var(--accent);background:var(--accent-light);padding:0.2rem 0.7rem;border-radius:100px;">—</span>
                            </div>
                            <div class="preview-wrap" style="max-height:65vh;">
                                <article id="cl-preview-s4"></article>
                            </div>
                            <div class="action-row" style="margin-top:1rem; justify-content:flex-end;">
                                <button type="button" class="btn-save" id="save-letter-s4">
                                    <svg width="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                                    Save changes
                                </button>
                                <a id="download-letter-s4" href="#" class="btn-download hidden">
                                    <svg width="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 3v12"/><path d="m7 10 5 5 5-5"/><path d="M5 21h14"/></svg>
                                    Download PDF
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="step-nav" style="margin-top:1.5rem;">
                        <button class="btn-nav btn-back" id="btn-4-back">
                            <svg width="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                            Back to editing
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
(() => {
    const app = document.getElementById('cover-letter-app');
    const generateUrl = app.dataset.generateUrl;
    const templates = @json($templates->mapWithKeys(fn($t) => [$t->id => $t->html]));
    const templateNames = @json($templates->mapWithKeys(fn($t) => [$t->id => $t->name]));
    const openT = '{{' . '}}'; const closeT = '}}';

    const state = {
        id: null,
        template_id: null,
        name: '{{ $prefill['name'] }}',
        email: '{{ $prefill['email'] }}',
        mobile: '{{ $prefill['mobile'] }}',
        location: '{{ $prefill['location'] }}',
        company: '{{ $prefill['company'] }}',
        job_role: '{{ $prefill['job_role'] }}',
        skills: '{{ $prefill['skills'] }}',
        body: `{!! addslashes($prefill['body']) !!}`,
    };

    const $ = id => document.getElementById(id);
    const esc = v => String(v ?? '').replace(/[&<>]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;'}[c]));
    const nl2br = v => esc(v).replace(/\n/g, '<br>');

    function replaceToken(html, key, value) {
        return html.split('{{' + key + '}}').join(value).split('[[' + key + ']]').join(value);
    }

    function buildRendered(templateId) {
        if (!templates[templateId]) return '';
        let html = templates[templateId];
        const vals = {
            name: esc(state.name),
            email: esc(state.email),
            mobile: esc(state.mobile),
            location: esc(state.location),
            company: esc(state.company),
            company_name: esc(state.company),
            job_role: esc(state.job_role),
            skills: esc(state.skills),
            body: nl2br(state.body),
        };
        Object.entries(vals).forEach(([k, v]) => { html = replaceToken(html, k, v); });
        return html;
    }

    function render() {
        if (!state.template_id) return;
        const html = buildRendered(state.template_id);
        if ($('cl-preview')) $('cl-preview').innerHTML = html;
        if ($('cl-preview-s4')) $('cl-preview-s4').innerHTML = html;
    }

    function syncFromFields() {
        state.name = $('cl-name')?.value || '';
        state.email = $('cl-email')?.value || '';
        state.mobile = $('cl-mobile')?.value || '';
        state.location = $('cl-location')?.value || '';
        state.company = $('cl-company')?.value || '';
        state.job_role = $('cl-role')?.value || '';
        state.skills = $('cl-skills')?.value || '';
        state.body = $('cl-body')?.value || '';
        if ($('cl-name-s3')) $('cl-name-s3').value = state.name;
        if ($('cl-company-s3')) $('cl-company-s3').value = state.company;
        if ($('cl-email-s3')) $('cl-email-s3').value = state.email;
        if ($('cl-mobile-s3')) $('cl-mobile-s3').value = state.mobile;
        render();
    }

    // Bind inputs
    ['cl-name','cl-email','cl-mobile','cl-location','cl-company','cl-role','cl-skills'].forEach(id => {
        $(id)?.addEventListener('input', syncFromFields);
    });
    $('cl-body')?.addEventListener('input', () => { state.body = $('cl-body').value; render(); });
    ['cl-name-s3','cl-company-s3','cl-email-s3','cl-mobile-s3'].forEach(id => {
        $(id)?.addEventListener('input', e => {
            const map = {'cl-name-s3':'name','cl-company-s3':'company','cl-email-s3':'email','cl-mobile-s3':'mobile'};
            state[map[id]] = e.target.value;
            const mirror = {'cl-name-s3':'cl-name','cl-company-s3':'cl-company','cl-email-s3':'cl-email','cl-mobile-s3':'cl-mobile'};
            if ($(mirror[id])) $(mirror[id]).value = e.target.value;
            render();
        });
    });

    // Navigation
    let currentStep = 1;
    function goToStep(n) {
        if (n === 2 && !state.template_id) {
            showStatus('Please select a template first', true);
            return;
        }
        $(`panel-${currentStep}`)?.classList.remove('active');
        currentStep = n;
        $(`panel-${n}`)?.classList.add('active');
        for (let i = 1; i <= 4; i++) {
            const tab = $(`step-tab-${i}`);
            const bubble = $(`step-bubble-${i}`);
            tab.classList.remove('is-active', 'is-done');
            if (i < n) {
                tab.classList.add('is-done');
                bubble.innerHTML = `<svg width="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>`;
            } else if (i === n) {
                tab.classList.add('is-active');
                bubble.textContent = i;
            } else {
                bubble.textContent = i;
            }
            const conn = $(`step-conn-${i}`);
            if (conn) conn.classList.toggle('is-done', i < n);
        }
        if (n === 3 && !state.id) triggerGenerate();
        if (n === 4) syncStep4Template(state.template_id);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    $('btn-1-next')?.addEventListener('click', () => goToStep(2));
    $('btn-2-back')?.addEventListener('click', () => goToStep(1));
    $('btn-2-next')?.addEventListener('click', () => goToStep(3));
    $('btn-3-back')?.addEventListener('click', () => goToStep(2));
    $('btn-3-next')?.addEventListener('click', () => goToStep(4));
    $('btn-4-back')?.addEventListener('click', () => goToStep(3));

    // Template selection step 1
    document.querySelectorAll('.template-card').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.template-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            state.template_id = card.dataset.templateId;
            $('btn-1-next').disabled = false;
            $('template-hint').innerHTML = `✓ ${templateNames[state.template_id]} selected`;
            $('template-hint').style.color = 'var(--green)';
            render();
        });
    });

    // Generate
    async function triggerGenerate() {
        const indicator = $('gen-indicator');
        const btn = $('generate-letter');
        indicator.classList.add('active');
        if (btn) btn.disabled = true;
        syncFromFields();
        try {
            const res = await fetch(generateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    template_id: state.template_id,
                    resume_id: $('cl-resume')?.value || null,
                    name: state.name,
                    email: state.email,
                    mobile: state.mobile,
                    location: state.location,
                    company_name: state.company,
                    job_role: state.job_role,
                    skills: state.skills,
                    job_description: $('cl-description')?.value || '',
                })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                state.id = data.cover_letter_id;
                Object.assign(state, data.letter);
                $('cl-name').value = state.name || '';
                $('cl-email').value = state.email || '';
                $('cl-mobile').value = state.mobile || '';
                $('cl-location').value = state.location || '';
                $('cl-company').value = state.company || '';
                $('cl-role').value = state.job_role || '';
                $('cl-skills').value = state.skills || '';
                $('cl-body').value = state.body || '';
                if ($('cl-name-s3')) $('cl-name-s3').value = state.name || '';
                if ($('cl-company-s3')) $('cl-company-s3').value = state.company || '';
                if ($('cl-email-s3')) $('cl-email-s3').value = state.email || '';
                if ($('cl-mobile-s3')) $('cl-mobile-s3').value = state.mobile || '';
                const dlUrl = `/cover-letter/${state.id}/download/pdf`;
                $('download-letter').href = dlUrl;
                $('download-letter').classList.remove('hidden');
                $('download-letter-s4').href = dlUrl;
                $('download-letter-s4').classList.remove('hidden');
                render();
                showStatus('Draft generated');
            } else {
                showStatus('Generation failed', true);
            }
        } catch (err) {
            showStatus('Network error', true);
        } finally {
            indicator.classList.remove('active');
            if (btn) btn.disabled = false;
        }
    }
    $('generate-letter')?.addEventListener('click', triggerGenerate);

    async function saveLetter() {
        if (!state.id) { showStatus('Generate a draft first', true); return; }
        syncFromFields();
        await fetch(`/cover-letter/${state.id}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            body: JSON.stringify({ letter: state })
        });
        showStatus('Saved');
    }
    $('save-letter')?.addEventListener('click', saveLetter);
    $('save-letter-s4')?.addEventListener('click', saveLetter);

    function syncStep4Template(templateId) {
        if (!templateId) return;
        document.querySelectorAll('.template-switcher-item').forEach(item => {
            item.classList.toggle('selected', item.dataset.templateId === String(templateId));
        });
        $('active-template-label').textContent = templateNames[templateId] || '—';
        state.template_id = templateId;
        render();
    }
    document.querySelectorAll('.template-switcher-item').forEach(item => {
        item.addEventListener('click', () => syncStep4Template(item.dataset.templateId));
    });

    let statusTimer;
    function showStatus(msg, isError = false) {
        const chip = $('cl-status');
        chip.textContent = msg;
        chip.className = 'cl-status-chip active' + (isError ? ' error' : '');
        clearTimeout(statusTimer);
        statusTimer = setTimeout(() => chip.classList.remove('active'), 3000);
    }

    // Initial sync
    syncFromFields();
    if ($('cl-name-s3')) $('cl-name-s3').value = state.name;
    if ($('cl-company-s3')) $('cl-company-s3').value = state.company;
    if ($('cl-email-s3')) $('cl-email-s3').value = state.email;
    if ($('cl-mobile-s3')) $('cl-mobile-s3').value = state.mobile;
})();
</script>
@endpush

@endsection