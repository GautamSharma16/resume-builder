{{-- resources/views/pages/cover-letter/index.blade.php --}}
@extends('layouts.app')

@section('title', 'AI Cover Letter Builder | Cvbliss')

@section('content')


<style>
    /* ─── TOKENS - MATCHING HOMEPAGE STYLES ─────────────────── */
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
        --font-body:    var(--font-body);

        --r-sm:  6px;
        --r-md:  12px;
        --r-lg:  18px;
        --r-xl:  28px;
        --r-2xl: 36px;
        --r-full: 999px;

        --ease-spring: cubic-bezier(0.175, 0.885, 0.32, 1.275);
        --ease-out: cubic-bezier(0.25, 0.46, 0.45, 0.94);
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    }

    body {
        font-family: var(--font-body);
        background-color: var(--surface);
        color: var(--ink);
        line-height: 1.6;
        -webkit-font-smoothing: antialiased;
    }

    /* ─── SECTION LABEL (same as homepage) ───────────────────── */
    .section-label {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 0.14em;
        text-transform: uppercase;
        color: var(--blue);
        margin-bottom: 1rem;
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
        font-size: clamp(2rem, 4vw, 3rem);
        font-weight: 400;
        color: var(--navy);
        line-height: 1.2;
        margin-bottom: 1rem;
    }
    .section-heading em {
        font-style: italic;
        background: linear-gradient(135deg, var(--blue), var(--purple));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    /* ─── BUTTONS (matching homepage) ────────────────────────── */
    .btn-primary {
        display: inline-flex; align-items: center; gap: 0.6rem;
        background: linear-gradient(135deg, var(--blue), var(--blue-dark));
        color: #fff;
        padding: 0.7rem 1.6rem;
        border-radius: var(--r-full);
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s var(--ease-spring);
        box-shadow: 0 4px 12px rgba(37,99,235,0.35);
        border: none;
        cursor: pointer;
    }
    .btn-primary:hover { 
        background: linear-gradient(135deg, var(--blue-dark), var(--blue));
        transform: translateY(-3px); 
        box-shadow: 0 8px 25px rgba(37,99,235,0.5); 
    }

    .btn-outline {
        display: inline-flex; align-items: center; gap: 0.6rem;
        background: transparent;
        color: var(--ink);
        padding: 0.7rem 1.6rem;
        border-radius: var(--r-full);
        font-size: 0.875rem;
        font-weight: 600;
        text-decoration: none;
        border: 2px solid rgba(0,0,0,0.08);
        transition: all 0.3s var(--ease-spring);
        cursor: pointer;
    }
    .btn-outline:hover { 
        border-color: var(--blue); 
        background: var(--blue-light); 
        color: var(--blue); 
        transform: translateY(-3px);
    }

    /* ─── ANIMATIONS ─────────────────────────────────────────── */
    @keyframes pulse-ring {
        0%   { box-shadow: 0 0 0 0 rgba(37,99,235,0.4); }
        70%  { box-shadow: 0 0 0 12px rgba(37,99,235,0); }
        100% { box-shadow: 0 0 0 0 rgba(37,99,235,0); }
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(25px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(40px); }
        to   { opacity: 1; transform: translateX(0); }
    }

    /* ─── STEP 1: PICKER (matching homepage template grid) ───── */
    #step-pick {
        max-width: 1300px;
        margin: 0 auto;
        padding: 4rem 2rem;
        position: relative;
    }

    .pick-header {
        text-align: center;
        margin-bottom: 4rem;
    }

    .template-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 2rem;
    }

    .template-card {
        background: var(--white);
        border-radius: var(--r-xl);
        border: 1px solid var(--border);
        overflow: hidden;
        transition: all 0.4s var(--ease-spring);
        cursor: pointer;
        box-shadow: var(--shadow-sm);
    }
    .template-card:hover {
        transform: translateY(-8px);
        border-color: var(--blue);
        box-shadow: var(--shadow-lg);
    }

    .template-thumb {
        height: 380px;
        background: var(--surface-2);
        overflow: hidden;
        position: relative;
        display: flex;
        justify-content: center;
        align-items: flex-start;
    }

    .template-scaler {
        width: 794px;
        min-height: 1123px;
        transform: scale(0.35);
        transform-origin: top center;
        pointer-events: none;
        margin-bottom: -730px;
    }
    .template-scaler > div {
        background: white;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
    }

    .template-overlay {
        position: absolute;
        inset: 0;
        background: rgba(37,99,235,0.08);
        opacity: 0;
        transition: opacity 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .template-card:hover .template-overlay { opacity: 1; }

    .btn-select {
        background: var(--blue);
        color: white;
        padding: 0.7rem 1.5rem;
        border-radius: var(--r-full);
        font-weight: 600;
        transform: translateY(10px);
        transition: transform 0.3s;
    }
    .template-card:hover .btn-select { transform: translateY(0); }

    .template-footer {
        padding: 1.25rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-top: 1px solid var(--border);
    }
    .template-name { font-weight: 700; color: var(--navy); }
    .template-badge {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--green);
        background: var(--green-light);
        padding: 0.25rem 0.8rem;
        border-radius: var(--r-full);
    }

    /* ─── STEP 2: BUILDER ───────────────────────────────────── */
    #step-build {
        display: none;
        min-height: 100vh;
        background: linear-gradient(135deg, #ffffff 0%, #fafcff 100%);
    }

    .builder-main {
        display: grid;
        grid-template-columns: 1fr;
        gap: 2rem;
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
        width: 100%;
    }
    @media (min-width: 1024px) {
        .builder-main { grid-template-columns: 400px 1fr; }
    }

    /* Sidebar Cards (same style as homepage feature cards) */
    .builder-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .input-card {
        background: var(--white);
        border-radius: var(--r-xl);
        border: 1px solid var(--border);
        padding: 1.5rem;
        box-shadow: var(--shadow-sm);
        transition: all 0.3s var(--ease-spring);
    }
    .input-card:hover {
        box-shadow: var(--shadow-md);
        border-color: rgba(37,99,235,0.2);
    }
    .input-card h2 {
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        color: var(--blue);
        margin-bottom: 1.25rem;
        text-transform: uppercase;
    }

    .field-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }
    .field-full { grid-column: span 2; }
    .field-group { margin-bottom: 1rem; }
    .field-group label {
        display: block;
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--navy);
        margin-bottom: 0.35rem;
    }
    .form-input {
        width: 100%;
        padding: 0.7rem 1rem;
        border-radius: var(--r-md);
        border: 1px solid var(--border);
        font-family: var(--font-body);
        font-size: 0.9rem;
        color: var(--ink);
        transition: all 0.2s;
        background: var(--surface);
    }
    .form-input:focus {
        outline: none;
        border-color: var(--blue);
        background: var(--white);
        box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
    }
    textarea.form-input {
        min-height: 120px;
        resize: vertical;
    }

    .btn-generate {
        width: 100%;
        padding: 0.9rem;
        background: linear-gradient(135deg, var(--blue), var(--blue-dark));
        color: white;
        border: none;
        border-radius: var(--r-md);
        font-weight: 600;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s var(--ease-spring);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.6rem;
        margin-top: 0.5rem;
    }
    .btn-generate:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37,99,235,0.3);
    }

    /* Preview Panel */
    .builder-preview {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .toolbar-card {
        background: var(--white);
        border-radius: var(--r-xl);
        border: 1px solid var(--border);
        padding: 0.75rem 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.75rem;
    }
    .btn-toolbar {
        padding: 0.45rem 1rem;
        border-radius: var(--r-full);
        font-weight: 600;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.2s;
        border: 1px solid var(--border);
        background: var(--white);
        color: var(--navy);
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    .btn-toolbar:hover {
        background: var(--surface);
        border-color: var(--blue);
        color: var(--blue);
    }
    .btn-save {
        background: var(--green);
        color: white;
        border: none;
    }
    .btn-save:hover {
        background: #059669;
        color: white;
    }

    .preview-canvas {
        background: var(--surface-2);
        border-radius: var(--r-xl);
        padding: 2.5rem 1.5rem;
        overflow: auto;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: 800px;
        border: 1px solid var(--border);
    }
    .preview-a4 {
        width: 794px;
        min-height: 1123px;
        background: white;
        box-shadow: var(--shadow-lg);
        border-radius: 4px;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Loading Overlay (same as homepage style) */
    .loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(8px);
        z-index: 1000;
        display: none;
        align-items: center;
        justify-content: center;
    }
    .loading-overlay.active { display: flex; }
    .loading-card {
        background: var(--white);
        padding: 2rem;
        border-radius: var(--r-2xl);
        text-align: center;
        max-width: 360px;
        width: 90%;
        box-shadow: var(--shadow-lg);
    }
    .spinner {
        width: 48px;
        height: 48px;
        border: 4px solid var(--surface-2);
        border-top-color: var(--blue);
        border-radius: 50%;
        animation: spin 0.8s linear infinite;
        margin: 0 auto 1.2rem;
    }

    /* Modal for template switching */
    .modal {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(6px);
        z-index: 1100;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1.5rem;
    }
    .modal.open { display: flex; }
    .modal-content {
        background: var(--white);
        width: 100%;
        max-width: 1000px;
        max-height: 85vh;
        border-radius: var(--r-2xl);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }
    .modal-header {
        padding: 1.25rem 1.8rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .modal-grid {
        padding: 1.5rem;
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1.2rem;
        overflow-y: auto;
    }
    .modal-tmpl-card {
        border: 2px solid var(--border);
        border-radius: var(--r-lg);
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s;
        background: var(--white);
    }
    .modal-tmpl-card:hover {
        border-color: var(--blue);
        transform: translateY(-4px);
        box-shadow: var(--shadow-md);
    }
    .modal-tmpl-card.active {
        border-color: var(--blue);
        background: var(--blue-light);
    }
    .modal-thumb {
        height: 200px;
        background: var(--surface);
        display: flex;
        justify-content: center;
        overflow: hidden;
    }
    .modal-scaler {
        transform: scale(0.22);
        transform-origin: top center;
        width: 794px;
    }

    /* Responsive */
    @media (max-width: 768px) {
        #step-pick { padding: 2rem 1rem; }
        .template-grid { gap: 1rem; }
        .builder-main { padding: 1rem; gap: 1rem; }
        .toolbar-card { flex-direction: column; align-items: stretch; }
        .preview-canvas { padding: 1rem; }
        .btn-toolbar { justify-content: center; }
    }
</style>

{{-- STEP 1: PICKER (matching homepage section styling) --}}
<div id="step-pick">
    {{-- Decorative blobs (optional but matches homepage vibe) --}}
    <div style="position: absolute; top: -80px; left: -80px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(37,99,235,0.08), transparent); border-radius: 50%; pointer-events: none; z-index: -1;"></div>
    <div style="position: absolute; bottom: 0; right: -60px; width: 260px; height: 260px; background: radial-gradient(circle, rgba(139,92,246,0.06), transparent); border-radius: 50%; pointer-events: none; z-index: -1;"></div>

    <div class="pick-header">
        <div class="section-label">Professional Designs</div>
        <h1 class="section-heading">Choose your <em>template</em></h1>
        <p style="color: var(--muted); max-width: 550px; margin: 0 auto;">Pick a professional design to start your cover letter. You can switch styles anytime.</p>
    </div>

    <div class="template-grid">
        @foreach($templates as $template)
        <div class="template-card" onclick="pickTemplate('{{ $template->id }}')">
            <div class="template-thumb">
                <div class="template-scaler">
                    <div>{!! $renderedTemplates[$template->id] !!}</div>
                </div>
                <div class="template-overlay">
                    <div class="btn-select">Use This Style</div>
                </div>
            </div>
            <div class="template-footer">
                <span class="template-name">{{ $template->name }}</span>
                <span class="template-badge">Professional</span>
            </div>
        </div>
        @endforeach
    </div>
</div>

{{-- STEP 2: BUILDER --}}
<div id="step-build">
    <div class="builder-main">
        {{-- Sidebar --}}
        <aside class="builder-sidebar">
            <div class="input-card">
                <h2>About You</h2>
                <div class="field-grid">
                    <div class="field-group">
                        <label>Full Name</label>
                        <input type="text" id="cl-name" class="form-input" placeholder="John Doe" value="{{ $prefill['name'] }}">
                    </div>
                    <div class="field-group">
                        <label>Email Address</label>
                        <input type="email" id="cl-email" class="form-input" placeholder="john@example.com" value="{{ $prefill['email'] }}">
                    </div>
                    <div class="field-group">
                        <label>Phone Number</label>
                        <input type="text" id="cl-mobile" class="form-input" placeholder="+91 98765 43210" value="{{ $prefill['mobile'] }}">
                    </div>
                    <div class="field-group">
                        <label>Location</label>
                        <input type="text" id="cl-location" class="form-input" placeholder="Bengaluru" value="{{ $prefill['location'] }}">
                    </div>
                </div>
            </div>

            <div class="input-card">
                <h2>The Opportunity</h2>
                <div class="field-group">
                    <label>Company Name</label>
                    <input type="text" id="cl-company" class="form-input" placeholder="Acme Corp" value="{{ $prefill['company'] }}">
                </div>
                <div class="field-group">
                    <label>Job Title</label>
                    <input type="text" id="cl-role" class="form-input" placeholder="Senior Product Designer" value="{{ $prefill['job_role'] }}">
                </div>
                <div class="field-group">
                    <label>Key Skills</label>
                    <input type="text" id="cl-skills" class="form-input" placeholder="UI/UX, React, Figma" value="{{ $prefill['skills'] }}">
                </div>
                <div class="field-group">
                    <label>Job Description</label>
                    <textarea id="cl-description" class="form-input" placeholder="Paste the job requirements here for better AI tailoring..."></textarea>
                </div>
                <button id="generate-letter" class="btn-generate">
                    
                    Generate Cover Letter
                </button>
            </div>
            
            <div class="input-card" id="edit-section">
                <h2>Letter Body</h2>
                <div class="field-group">
                    <textarea id="cl-body" class="form-input" rows="12">{{ $prefill['body'] }}</textarea>
                </div>
            </div>
        </aside>

        {{-- Preview --}}
        <main class="builder-preview">
            <div class="toolbar-card">
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <button class="btn-toolbar" id="btn-change-tmpl">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 5a1 1 0 01.3-.7l7-7a1 1 0 011.4 0l7 7a1 1 0 01.3.7v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/></svg>
                        Change Template
                    </button>
                    <div style="padding-left: 0.5rem; border-left: 1px solid var(--border);">
                        <span id="active-tmpl-name" style="font-size: 0.8rem; font-weight: 600; color: var(--muted);">Modern</span>
                    </div>
                </div>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <button class="btn-toolbar" id="save-letter">Save Letter</button>
                    <button class="btn-toolbar btn-save" id="download-btn">Download PDF</button>
                </div>
            </div>

            <div class="preview-canvas" id="preview-canvas">
                <div class="preview-a4" id="preview-a4">
                    <div id="cl-content"></div>
                </div>
            </div>
        </main>
    </div>
</div>

{{-- MODALS & LOADING --}}
<div id="loading-overlay" class="loading-overlay">
    <div class="loading-card">
        <div class="spinner"></div>
        <h3 style="font-weight: 700; font-size: 1.2rem; margin-bottom: 0.4rem;">AI is Writing...</h3>
        <p style="color: var(--muted); font-size: 0.85rem;">Creating a tailored cover letter for your dream role.</p>
    </div>
</div>

<div id="tmpl-modal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 style="font-weight: 700; font-size: 1.2rem;">Select Template</h2>
            <button onclick="closeModal()" style="background: none; border: none; cursor: pointer; color: var(--soft);">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="modal-grid">
            @foreach($templates as $template)
            <div class="modal-tmpl-card" data-id="{{ $template->id }}" onclick="applyTemplate('{{ $template->id }}')">
                <div class="modal-thumb">
                    <div class="modal-scaler">
                        <div style="width: 794px; min-height: 1123px; background: white;">
                            {!! $renderedTemplates[$template->id] !!}
                        </div>
                    </div>
                </div>
                <div style=" font-size: 0.75rem; font-weight: 600; text-align: center;">{{ $template->name }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict';

        const downloadRequiresPlan = @json(auth()->check() && ! auth()->user()->activeSubscription?->hasDownloadsRemaining());
        const isAuthenticated = @json(auth()->check());
        const tplHtml = @json($templates->mapWithKeys(fn($t) => [$t->id => $t->html]));
        const tplNames = @json($templates->mapWithKeys(fn($t) => [$t->id => $t->name]));
        
        let state = {
            id: null,
            templateId: null,
            name: '{{ addslashes($prefill['name']) }}',
            email: '{{ addslashes($prefill['email']) }}',
            mobile: '{{ addslashes($prefill['mobile']) }}',
            location: '{{ addslashes($prefill['location']) }}',
            company: '{{ addslashes($prefill['company']) }}',
            job_role: '{{ addslashes($prefill['job_role']) }}',
            skills: '{{ addslashes($prefill['skills']) }}',
            body: `{!! addslashes($prefill['body']) !!}`
        };

        const $ = id => document.getElementById(id);
        const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        const nl2br = v => esc(v).replace(/\n/g, '<br>');

        function render() {
            if (!state.templateId) return;
            let html = tplHtml[state.templateId] || '';
            const tokens = {
                name: esc(state.name),
                email: esc(state.email),
                mobile: esc(state.mobile),
                location: esc(state.location),
                company: esc(state.company),
                company_name: esc(state.company),
                job_role: esc(state.job_role),
                skills: esc(state.skills),
                body: nl2br(state.body)
            };

            Object.entries(tokens).forEach(([key, val]) => {
                const reg = new RegExp('\\{\\{\\s*'+key+'\\s*\\}\\}|\\[\\[\\s*'+key+'\\s*\\]\\]', 'gi');
                html = html.replace(reg, val);
            });

            $('cl-content').innerHTML = html;
            scalePreview();
        }

        function scalePreview() {
            const canvas = $('preview-canvas');
            const a4 = $('preview-a4');
            if (!canvas || !a4) return;
            const padding = 64;
            const availW = canvas.clientWidth - padding;
            const scale = Math.min(availW / 794, 0.85);
            a4.style.transform = `scale(${scale})`;
            a4.style.marginBottom = `-${1123 * (1 - scale)}px`;
        }

        window.pickTemplate = function(id) {
            state.templateId = id;
            $('active-tmpl-name').textContent = tplNames[id];
            $('step-pick').style.display = 'none';
            $('step-build').style.display = 'flex';
            render();
            window.scrollTo(0,0);
        };

        window.applyTemplate = function(id) {
            state.templateId = id;
            $('active-tmpl-name').textContent = tplNames[id];
            document.querySelectorAll('.modal-tmpl-card').forEach(c => c.classList.toggle('active', c.dataset.id === id));
            render();
            closeModal();
        };

        window.closeModal = () => $('tmpl-modal').classList.remove('open');
        $('btn-change-tmpl').addEventListener('click', () => $('tmpl-modal').classList.add('open'));

        // Input Sync
        const fields = ['cl-name', 'cl-email', 'cl-mobile', 'cl-location', 'cl-company', 'cl-role', 'cl-skills', 'cl-body'];
        fields.forEach(id => {
            $(id).addEventListener('input', e => {
                const key = id.replace('cl-', '').replace('-', '_');
                state[key] = e.target.value;
                render();
            });
        });

        // AI Generation
        $('generate-letter').addEventListener('click', async () => {
            const overlay = $('loading-overlay');
            overlay.classList.add('active');
            
            try {
                const response = await fetch('{{ route("cover-letter.generate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        name: state.name,
                        email: state.email,
                        mobile: state.mobile,
                        location: state.location,
                        company_name: state.company,
                        job_role: state.job_role,
                        skills: state.skills,
                        job_description: $('cl-description').value,
                        template_id: state.templateId
                    })
                });

                const data = await response.json();
                if (data.success) {
                    state.id = data.cover_letter_id;
                    state.body = data.letter.body;
                    $('cl-body').value = state.body;
                    render();
                } else {
                    alert(data.message || 'Generation failed.');
                }
            } catch (err) {
                console.error(err);
                alert('Connection error.');
            } finally {
                overlay.classList.remove('active');
            }
        });

        // Save
        $('save-letter').addEventListener('click', async () => {
            if (!state.id) {
                alert('Please generate the letter first.');
                return;
            }
            try {
                const response = await fetch(`/cover-letter/${state.id}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ letter: state })
                });
                if (response.ok) alert('Saved successfully!');
                else alert('Save failed.');
            } catch (err) { alert('Save failed.'); }
        });

        // Download
        $('download-btn').addEventListener('click', () => {
            if (!state.id) {
                alert('Please generate the letter first.');
                return;
            }
            if (isAuthenticated && downloadRequiresPlan) {
                window.openPlanDownloadModal?.();
                return;
            }
            window.location.href = `/cover-letter/${state.id}/download/pdf`;
        });

        window.addEventListener('resize', scalePreview);
        setTimeout(scalePreview, 100);
    })();
</script>

@endsection
