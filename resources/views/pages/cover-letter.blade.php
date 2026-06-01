{{-- resources/views/pages/cover-letter/index.blade.php --}}
@extends('layouts.app')

@section('title', 'AI Cover Letter Builder | Cvbliss')

@section('content')

<!-- Quill Rich Text Editor -->
<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>


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

        /* Premium Glassmorphic Tokens */
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.4);
        --glass-blur: blur(12px);
        --emerald: #0f766e;
        --emerald-glow: rgba(15, 118, 110, 0.15);
        --navy-deep: #0f172a;
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
    @keyframes scanMove {
        0% { top: -5px; opacity: 1; }
        90% { top: 100%; opacity: 1; }
        100% { top: 100%; opacity: 0; }
    }
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.95); }
        to   { opacity: 1; transform: scale(1); }
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
        overflow-x: hidden;
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
        aspect-ratio: 210 / 297;
        height: auto;
        background: var(--surface-2);
        overflow: hidden;
        position: relative;
    }

    .template-scaler {
        position: absolute;
        top: 0;
        left: 50%;
        width: 794px;
        min-height: 1123px;
        transform-origin: top center;
        pointer-events: none;
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
        padding: 1.5rem;
        max-width: 1480px;
        margin: 0 auto;
        width: 100%;
        overflow-x: hidden;
    }
    @media (min-width: 1024px) {
        .builder-main { grid-template-columns: minmax(340px, 420px) minmax(0, 1fr); }
    }

    /* Sidebar Cards (same style as homepage feature cards) */
    .builder-sidebar {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .input-card {
        background: var(--glass-bg);
        backdrop-filter: var(--glass-blur);
        -webkit-backdrop-filter: var(--glass-blur);
        border-radius: var(--r-xl);
        border: 1px solid var(--glass-border);
        padding: 1.5rem;
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.07);
        transition: all 0.3s var(--ease-spring);
        position: relative;
        overflow: hidden;
    }
    .input-card::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,0.1), transparent);
        pointer-events: none;
    }
    .input-card:hover {
        box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.12);
        border-color: rgba(15, 118, 110, 0.3);
        transform: translateY(-2px);
    }
    .input-card h2 {
        font-size: 0.85rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        color: var(--emerald);
        margin-bottom: 1.5rem;
        text-transform: uppercase;
        display: flex;
        align-items: center;
        gap: 0.6rem;
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

    .resume-upload-box {
        border: 1.5px dashed rgba(37,99,235,0.28);
        border-radius: var(--r-lg);
        background: linear-gradient(135deg, var(--blue-light), #ffffff);
        padding: 1rem;
        cursor: pointer;
        transition: all 0.25s var(--ease-spring);
    }
    .resume-upload-box:hover {
        border-color: var(--blue);
        transform: translateY(-2px);
        box-shadow: 0 10px 24px rgba(37,99,235,0.08);
    }
    .resume-upload-box input {
        display: none;
    }
    .resume-upload-content {
        display: flex;
        align-items: center;
        gap: 0.85rem;
    }
    .resume-upload-icon {
        width: 40px;
        height: 40px;
        border-radius: var(--r-md);
        background: white;
        color: var(--blue);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        box-shadow: 0 4px 14px rgba(37,99,235,0.12);
    }
    .resume-upload-text strong {
        display: block;
        font-size: 0.85rem;
        color: var(--navy);
    }
    .resume-upload-text span {
        display: block;
        font-size: 0.72rem;
        color: var(--muted);
        margin-top: 0.15rem;
    }

    .btn-generate {
        width: 100%;
        padding: 0.9rem;
        background: linear-gradient(135deg, var(--emerald), #115e59);
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
        box-shadow: 0 4px 12px var(--emerald-glow);
    }
    .btn-generate:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(15, 118, 110, 0.3);
    }

    /* Preview Panel */
    .builder-preview {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }
    .toolbar-card {
        background: var(--glass-bg);
        backdrop-filter: var(--glass-blur);
        -webkit-backdrop-filter: var(--glass-blur);
        border-radius: var(--r-xl);
        border: 1px solid var(--glass-border);
        padding: 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
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
    #btn-change-tmpl {
        background: linear-gradient(135deg, var(--blue), var(--blue-dark));
        color: #fff;
        border: none;
        box-shadow: 0 4px 12px rgba(37,99,235,0.28);
    }
    #btn-change-tmpl:hover {
        color: #fff;
        border: none;
        background: linear-gradient(135deg, var(--blue-dark), var(--blue));
        box-shadow: 0 7px 18px rgba(37,99,235,0.4);
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
        background: linear-gradient(180deg, #f8fafc, #eef4fb);
        border-radius: var(--r-xl);
        padding: 1.75rem 1rem;
        overflow-x: hidden;
        overflow-y: auto;
        display: flex;
        justify-content: center;
        align-items: flex-start;
        min-height: calc(100vh - 150px);
        width: 100%;
        position: relative;
    }
    .preview-a4 {
        width: 794px !important;
        max-width: none !important;
        flex: 0 0 794px;
        min-height: 1123px;
        background: white;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        border-radius: 2px;
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-family: 'EB Garamond', serif;
        color: var(--navy-deep);
        margin-bottom: 32px;
        overflow: hidden;
    }
    .preview-fallback {
        min-height: 1123px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 3rem;
        text-align: center;
        color: var(--muted);
        background: linear-gradient(180deg, #ffffff, #f8fafc);
    }
    .preview-fallback strong {
        display: block;
        font-size: 1.1rem;
        color: var(--navy);
        margin-bottom: 0.5rem;
    }

    /* Loading Overlay (same scan loader as Enhance CV) */
    .loading-overlay {
        position: fixed;
        inset: 0;
        background: rgba(11,18,33,0.72);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 10000;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .loading-overlay.active { display: flex; }

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
        animation: scaleIn 0.22s var(--ease-out) both;
    }

    .scan-paper {
        width: 150px;
        height: 190px;
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
        left: 0;
        right: 0;
        top: -8px;
        height: 4px;
        background: linear-gradient(90deg, transparent, var(--blue), var(--purple), transparent);
        box-shadow: 0 0 18px rgba(37,99,235,0.45);
        animation: scanMove 1.6s ease-in-out infinite;
    }

    .scan-paper-head {
        height: 18px;
        width: 58%;
        border-radius: var(--r-sm);
        background: linear-gradient(135deg, var(--blue), var(--purple));
        margin-bottom: 1rem;
    }

    .scan-paper-line {
        height: 7px;
        border-radius: var(--r-full);
        background: #dbe3ef;
        margin-bottom: 0.55rem;
    }
    .scan-paper-line.wide { width: 100%; }
    .scan-paper-line.mid { width: 82%; }
    .scan-paper-line.short { width: 62%; }
    .scan-paper-line.tiny { width: 46%; }

    .scan-header h2 {
        font-family: var(--font-body);
        font-size: 16px;
        font-weight: 700;
        color: var(--navy);
        text-align: center;
    }
    .scan-header p {
        color: var(--muted);
        font-size: 12px;
        text-align: center;
        margin-top: 0.25rem;
    }

    .scan-progress-bar-wrap {
        width: 100%;
        background: var(--surface-2);
        border-radius: var(--r-full);
        height: 6px;
        overflow: hidden;
    }
    .scan-progress-fill {
        height: 100%;
        width: 0%;
        background: linear-gradient(90deg, var(--blue), #818cf8);
        border-radius: var(--r-full);
        transition: width 0.3s ease;
    }

    .scan-steps {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .scan-step {
        display: flex;
        align-items: center;
        gap: 0.55rem;
        font-size: 12px;
        color: var(--soft);
    }
    .scan-step.active { color: var(--blue); }
    .scan-step.done { color: var(--green); }
    .scan-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: currentColor;
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
        max-width: 1240px;
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
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.2rem;
        overflow-y: auto;
    }
    .resume-upload-box {
        display: block;
        border: 2px dashed var(--border);
        border-radius: var(--r-lg);
        padding: 3rem 2rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: var(--surface);
        position: relative;
    }
    .resume-upload-box:hover {
        border-color: var(--blue);
        background: var(--white);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
    }
    .resume-upload-box.has-file {
        border-color: var(--green);
        background: var(--green-light);
        border-style: solid;
    }
    .resume-upload-box:active {
        transform: scale(0.98);
    }
    .resume-upload-content {
        pointer-events: none;
    }
    .modal-tmpl-card {
        border: 2px solid #dbe3ef;
        border-radius: var(--r-lg);
        overflow: hidden;
        cursor: pointer;
        transition: all 0.2s ease;
        background: var(--white);
        box-shadow: var(--shadow-md);
        display: flex;
        flex-direction: column;
    }
    .modal-tmpl-card:hover {
        border-color: var(--blue);
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.12);
    }
    .modal-tmpl-card.active {
        border-color: var(--blue);
        box-shadow: 0 0 0 1px rgba(37, 99, 235, 0.12), 0 14px 30px rgba(15, 23, 42, 0.12);
    }
    .modal-thumb {
        aspect-ratio: 210 / 297;
        height: auto;
        background: var(--surface);
        position: relative;
        overflow: hidden;
        border-bottom: 1px solid #e5e7eb;
    }
    .modal-scaler {
        position: absolute;
        top: 0;
        left: 50%;
        transform-origin: top center;
        width: 794px;
        pointer-events: none;
    }
    .modal-paper {
        width: 794px;
        min-height: 1123px;
        background: #fff;
    }
    .modal-tmpl-footer {
        min-height: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.05rem;
        font-weight: 700;
        color: #334155;
        background: #f8fafc;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }
        .builder-main {
            grid-template-columns: 1fr;
            padding: 0.75rem;
            width: 100vw;
            max-width: 100%;
        }
        .field-grid {
            grid-template-columns: 1fr;
            gap: 0.75rem;
        }
        .input-card {
            padding: 1.25rem 1rem;
        }
        .builder-sidebar {
            display: block;
            width: 100%;
            overflow-x: hidden;
        }
        .builder-sidebar.hidden-mobile {
            display: none;
        }
        .builder-preview.hidden-mobile {
            display: none;
        }
        .field-full {
            grid-column: span 1 !important;
        }
        .toolbar-card {
            display: flex;
            position: sticky;
            top: 8px;
            z-index: 20;
        }
        .preview-canvas {
            padding: 0.75rem;
            background: #fff;
            min-height: calc(100vh - 132px);
            width: 100%;
            max-width: 100%;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .preview-a4 {
            box-shadow: none;
            border-radius: 0;
            margin-bottom: 0;
        }
    }

    .mobile-toggle {
        display: none;
        position: fixed;
        bottom: 2rem;
        left: 50%;
        transform: translateX(-50%);
        background: #0f172a;
        color: white;
        padding: 0.5rem;
        border-radius: 100px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        z-index: 2000;
        gap: 0.5rem;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .mobile-toggle button {
        padding: 0.6rem 1.4rem;
        border-radius: var(--r-full);
        border: none;
        background: transparent;
        color: rgba(255,255,255,0.7);
        font-weight: 700;
        font-size: 0.8rem;
        cursor: pointer;
        transition: all 0.25s var(--ease-out);
        white-space: nowrap;
    }
    .mobile-toggle button.active {
        background: var(--emerald);
        color: white;
        box-shadow: 0 4px 12px rgba(15, 118, 110, 0.4);
    }
    @media (max-width: 1024px) {
        .mobile-toggle { display: flex; }
    }

    /* Quill Styles Override */
    .ql-container.ql-snow {
        border-radius: 0 0 var(--r-md) var(--r-md);
        border-color: var(--border) !important;
        background: var(--surface);
        font-family: var(--font-body);
        font-size: 0.9rem;
    }
    .ql-toolbar.ql-snow {
        border-radius: var(--r-md) var(--r-md) 0 0;
        border-color: var(--border) !important;
        background: white;
    }
    #cl-body-editor {
        min-height: 350px;
    }

    @media (max-width: 768px) {
        #step-pick { padding: 1.75rem 0.75rem 6rem; width: 100%; max-width: 100%; }
        .pick-header { margin-bottom: 1.75rem; }
        .template-grid { grid-template-columns: 1fr; gap: 1rem; width: 100%; }
        .template-card { border-radius: 18px; }
        .template-footer { padding: 1rem; gap: 0.75rem; }
        .template-name { font-size: 1rem; }
        .builder-main { padding: 0.75rem; gap: 1rem; }
        .toolbar-card { flex-direction: column; align-items: stretch; }
        .preview-canvas { padding: 0.75rem; }
        .btn-toolbar { justify-content: center; }
        .modal-grid { grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); }
        .modal-tmpl-footer { font-size: 0.95rem; min-height: 40px; }
    }

    /* Small phones */
    @media (max-width: 480px) {
        .mobile-toggle { bottom: 1rem; }
        .mobile-toggle button { padding: 0.55rem 1rem; font-size: 0.78rem; }
        .modal { padding: 0.75rem; }
        .modal-content { max-height: 92vh; border-radius: 16px; }
        .modal-grid { grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); padding: 1rem; gap: 0.85rem; }
        .modal-tmpl-footer { font-size: 0.82rem; min-height: 36px; }
        .preview-canvas { padding: 0.5rem; }
    }
    #cl-content {
        width: 100%;
        overflow-wrap: break-word;
    }
    .tpl-cover p { margin: 0 0 16px; }
    .tpl-cover main p:last-child { margin-bottom: 0; }
    .preview-canvas { max-width: 100%; overflow-x: hidden; }
    .preview-a4, #cl-content, #cl-content * { overflow-wrap: anywhere; word-break: break-word; }
    @media (max-width: 1024px) {
        .builder-preview { min-width: 0; }
        .builder-preview, .builder-sidebar, .toolbar-card, .input-card { max-width: 100%; }
        .preview-canvas { align-items: flex-start; justify-content: center; }
    }
</style>

{{-- STEP 1: PICKER (matching homepage section styling) --}}
<div id="step-pick">
    {{-- Decorative blobs --}}
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

{{-- STEP 2: ONBOARDING CHOICE --}}
<div id="step-onboarding" style="display: none; padding: 6rem 2rem; max-width: 900px; margin: 0 auto; text-align: center;">
    <div class="section-label">Getting Started</div>
    <h1 class="section-heading">How would you like to <em>begin</em>?</h1>
    <p style="color: var(--muted); margin-bottom: 3rem;">Upload your resume for AI-tailored content or start from scratch with a professional layout.</p>
    
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 2rem;">
        <div class="input-card" onclick="chooseOnboarding('upload')" style="cursor: pointer; padding: 3rem 2rem; border: 2px solid var(--border); transition: all 0.3s var(--ease-spring);">
            <div style="width: 60px; height: 60px; background: var(--blue-light); color: var(--blue); border-radius: var(--r-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">Upload Resume</h3>
            <p style="font-size: 0.9rem; color: var(--muted);">We'll extract your details and match them to the job description using AI.</p>
        </div>

        <div class="input-card" onclick="chooseOnboarding('scratch')" style="cursor: pointer; padding: 3rem 2rem; border: 2px solid var(--border); transition: all 0.3s var(--ease-spring);">
            <div style="width: 60px; height: 60px; background: var(--purple-light); color: var(--purple); border-radius: var(--r-lg); display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem;">
                <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 700; margin-bottom: 0.75rem;">Start from Scratch</h3>
            <p style="font-size: 0.9rem; color: var(--muted);">Manually enter your details and write your own letter with our premium editor.</p>
        </div>
    </div>

    <div style="margin-top: 3rem;">
        <button onclick="goBack('pick')" class="btn-outline">Back to Templates</button>
    </div>
</div>

{{-- STEP 3: RESUME & OPPORTUNITY --}}
<div id="step-resume" style="display: none; padding: 2rem 1rem; max-width: 700px; margin: 0 auto;">
    <style>
        @media (min-width: 768px) {
            #step-resume { padding: 4rem 2rem; }
        }
    </style>
    <div class="input-card" style="margin-bottom: 2rem;">
        <h2 style="text-align: center; font-size: 1.5rem; margin-bottom: 2rem;">Setup your <em>AI Writer</em></h2>
        
        <div class="field-group" style="margin-bottom: 2.5rem;">
            <label style="font-size: 1rem; margin-bottom: 0.75rem;">Upload Resume</label>
            <label class="resume-upload-box" for="cl-resume-file-main" style="padding: 2.5rem;">
                <input type="file" id="cl-resume-file-main" accept=".pdf,.doc,.docx" style="opacity: 0; position: absolute; inset: 0; cursor: pointer; z-index: 1;">
                <span class="resume-upload-content" style="flex-direction: column; text-align: center; gap: 1rem;">
                    <span class="resume-upload-icon" style="width: 54px; height: 54px;">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6M12 18v-6M9 15l3 3 3-3"/></svg>
                    </span>
                    <span class="resume-upload-text">
                        <strong id="cl-resume-file-name-main" style="font-size: 1rem;">Click to upload your resume</strong>
                        <span style="font-size: 0.8rem;">PDF, DOC, or DOCX up to 10MB</span>
                    </span>
                </span>
            </label>
        </div>

        <div style="padding-top: 1rem; border-top: 1px solid var(--border); margin-bottom: 2rem;">
            <h3 style="font-size: 0.75rem; font-weight: 700; color: var(--blue); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 1.5rem;">The Opportunity (Optional)</h3>
            <div class="field-grid">
                <div class="field-group">
                    <label>Company Name</label>
                    <input type="text" id="cl-setup-company" class="form-input" placeholder="e.g. Acme Corp">
                </div>
                <div class="field-group">
                    <label>Job Title</label>
                    <input type="text" id="cl-setup-role" class="form-input" placeholder="e.g. Senior Designer">
                </div>
            </div>
            <div class="field-group">
                <label>Job Description</label>
                <textarea id="cl-setup-description" class="form-input" placeholder="Paste the job requirements here for better AI tailoring..."></textarea>
            </div>
        </div>

        <button id="generate-letter-main" class="btn-generate" style="padding: 1rem; font-size: 1rem;">
            Generate My Cover Letter
        </button>

        <div style="text-align: center; margin-top: 1.5rem;">
            <button onclick="goBack('onboarding')" class="btn-outline" style="border: none; color: var(--muted);">Back</button>
        </div>
    </div>
</div>

{{-- STEP 4: BUILDER --}}
<div id="step-build" style="display: none;">
    <div class="builder-main">
        {{-- Sidebar --}}
        <aside class="builder-sidebar">
            {{-- 1. Letter Body (PRIORITY 1) --}}
            <div class="input-card" id="edit-section">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <h2 style="margin: 0;">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        Letter Content
                    </h2>
                    <button id="regenerate-letter" class="btn-generate" style="margin-top: 0; padding: 0.6rem 1.2rem; font-size: 0.85rem; width: auto; border-radius: var(--r-full);">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="margin-right: 4px;"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Regenerate
                    </button>
                </div>
                <div id="resume-status-badge" class="hidden" style="margin-bottom: 1.5rem;">
                    <span style="background: rgba(16, 185, 129, 0.1); color: var(--green); padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; border: 1px solid rgba(16, 185, 129, 0.2);">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                        RESUME ATTACHED
                    </span>
                </div>
                <div class="field-group">
                    <div id="cl-body-editor"></div>
                    <textarea id="cl-body" style="display:none;"></textarea>
                </div>
            </div>

            {{-- 2. About You (PRIORITY 2) --}}
            <div class="input-card">
                <h2>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Your Details
                </h2>
                <div class="field-grid">
                    <div class="field-group">
                        <label>Full Name</label>
                        <input type="text" id="cl-name" class="form-input" placeholder="John Doe">
                    </div>
                    <div class="field-group">
                        <label>Email Address</label>
                        <input type="email" id="cl-email" class="form-input" placeholder="john@example.com">
                    </div>
                    <div class="field-group">
                        <label>Phone Number</label>
                        <input type="text" id="cl-mobile" class="form-input" placeholder="+91 98765 43210">
                    </div>
                    <div class="field-group">
                        <label>Location</label>
                        <input type="text" id="cl-location" class="form-input" placeholder="Bengaluru">
                    </div>
                </div>
            </div>

            {{-- 3. The Opportunity (PRIORITY 3) --}}
            <div class="input-card">
                <h2>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Job Opportunity
                </h2>
                <div class="field-grid">
                    <div class="field-group">
                        <label>Company Name</label>
                        <input type="text" id="cl-company" class="form-input" placeholder="Acme Corp">
                    </div>
                    <div class="field-group">
                        <label>Job Title</label>
                        <input type="text" id="cl-role" class="form-input" placeholder="Senior Product Designer">
                    </div>
                </div>
                <div class="field-group">
                    <label>Key Skills</label>
                    <input type="text" id="cl-skills" class="form-input" placeholder="UI/UX, React, Figma">
                </div>
                <div class="field-group">
                    <label>Job Description</label>
                    <textarea id="cl-description" class="form-input" placeholder="Paste the job requirements..." style="min-height: 120px;"></textarea>
                </div>
            </div>
        </aside>

        {{-- Preview --}}
        <main class="builder-preview">
            <div class="toolbar-card">
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <button class="btn-toolbar" id="btn-change-tmpl">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 5a1 1 0 01.3-.7l7-7a1 1 0 011.4 0l7 7a1 1 0 01.3.7v14a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/></svg>
                        Template
                    </button>
                    <div style="padding-left: 0.5rem; border-left: 1px solid var(--border);">
                        <span id="active-tmpl-name" style="font-size: 0.8rem; font-weight: 600; color: var(--muted);">Modern</span>
                    </div>
                </div>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <button class="btn-toolbar" id="save-letter">Save</button>
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
    <div class="mobile-toggle">
        <button id="toggle-edit" class="active">Edit Details</button>
        <button id="toggle-preview">View Preview</button>
    </div>
</div>

{{-- MODALS & LOADING --}}
<div id="loading-overlay" class="loading-overlay">
    <div class="scan-card">
        <div class="scan-header">
            <h2>AI is Writing...</h2>
            <p id="coverScanStageLabel">Creating a tailored cover letter</p>
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
            <div class="scan-progress-fill" id="coverScanProgressFill"></div>
        </div>
        <div class="scan-steps">
            <div class="scan-step" id="coverScanStep1"><div class="scan-dot"></div>Reading job details...</div>
            <div class="scan-step" id="coverScanStep2"><div class="scan-dot"></div>Matching your skills...</div>
            <div class="scan-step" id="coverScanStep3"><div class="scan-dot"></div>Drafting your letter...</div>
        </div>
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
                        <div class="modal-paper">
                            {!! $renderedTemplates[$template->id] !!}
                        </div>
                    </div>
                </div>
                <div class="modal-tmpl-footer">{{ $template->name }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<script>
    (function() {
        'use strict';

        const selectedTemplateIdFromUrl = @json($selectedTemplateId ?? null);
        const editingCoverLetter = @json($editingCoverLetter ?? null);
        const downloadRequiresPlan = @json(auth()->check() && ! auth()->user()->activeSubscription?->isActive());
        const isAuthenticated = @json(auth()->check());
        const plansUrl = @json(route('plans'));
        const tplHtml = @json($templates->mapWithKeys(fn($t) => [$t->id => $t->html]));
        const renderedTplHtml = @json($renderedTemplates);
        const tplNames = @json($templates->mapWithKeys(fn($t) => [$t->id => $t->name]));

        function scaleTemplatePickers() {
            document.querySelectorAll('.template-thumb, .modal-thumb').forEach((thumb) => {
                const scaler = thumb.querySelector('.template-scaler, .modal-scaler');
                if (!scaler) return;
                const width = thumb.clientWidth || thumb.offsetWidth || 0;
                const height = thumb.clientHeight || thumb.offsetHeight || 0;
                if (width <= 0 || height <= 0) return;
                const scale = Math.max(0.05, Math.min(width / 794, height / 1123) * 0.995);
                scaler.style.transform = `translateX(-50%) scale(${scale})`;
            });
        }
        function scheduleTemplatePickerScale() {
            requestAnimationFrame(() => {
                scaleTemplatePickers();
                setTimeout(scaleTemplatePickers, 60);
                setTimeout(scaleTemplatePickers, 180);
            });
        }
        
        let state = {
            id: editingCoverLetter?.id || null,
            templateId: null,
            name: @json($prefill['name']),
            email: @json($prefill['email']),
            mobile: @json($prefill['mobile']),
            location: @json($prefill['location']),
            company: @json($prefill['company']),
            job_role: @json($prefill['job_role']),
            skills: @json($prefill['skills']),
            job_description: @json($prefill['job_description'] ?? ''),
            body: @json($prefill['body']),
            resume_uploaded: @json($prefill['resume_uploaded'] ?? false)
        };

        let quill = null;

        const $ = id => document.getElementById(id);
        const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
        const nl2br = v => esc(v).replace(/\n/g, '<br>');
        function notify(message, type = 'info') {
            let toast = document.getElementById('cl-toast');
            if (!toast) {
                toast = document.createElement('div');
                toast.id = 'cl-toast';
                toast.style.cssText = 'position:fixed;right:24px;bottom:24px;z-index:10050;max-width:min(360px,calc(100vw - 32px));border-radius:16px;padding:13px 16px;font-weight:800;font-size:13px;box-shadow:0 18px 45px rgba(15,23,42,.18);transition:opacity .2s ease,transform .2s ease;opacity:0;transform:translateY(10px);';
                document.body.appendChild(toast);
            }
            toast.textContent = message;
            toast.style.background = type === 'error' ? '#fef2f2' : '#eff6ff';
            toast.style.color = type === 'error' ? '#b91c1c' : '#1d4ed8';
            toast.style.border = type === 'error' ? '1px solid #fecaca' : '1px solid #bfdbfe';
            requestAnimationFrame(() => {
                toast.style.opacity = '1';
                toast.style.transform = 'translateY(0)';
            });
            clearTimeout(toast._timer);
            toast._timer = setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
            }, 2800);
        }
        const coverScanStages = [
            { label: 'Reading job details...', step: 0, pct: 25 },
            { label: 'Matching your skills to the role...', step: 1, pct: 62 },
            { label: 'Drafting your cover letter...', step: 2, pct: 90 },
            { label: 'Finalising your letter...', step: -1, pct: 100 }
        ];
        let coverScanTimer = null;

        function showCoverScanOverlay() {
            const overlay = $('loading-overlay');
            overlay?.classList.add('active');
            document.body.style.overflow = 'hidden';
            startCoverScanAnimation();
        }

        function hideCoverScanOverlay() {
            $('loading-overlay')?.classList.remove('active');
            document.body.style.overflow = '';
            if (coverScanTimer) clearTimeout(coverScanTimer);
            coverScanTimer = null;
        }

        function startCoverScanAnimation() {
            const fill = $('coverScanProgressFill');
            const stageLabel = $('coverScanStageLabel');
            const items = [$('coverScanStep1'), $('coverScanStep2'), $('coverScanStep3')];
            let stageIdx = 0;
            if (fill) fill.style.width = '0%';
            items.forEach(i => { if (i) i.className = 'scan-step'; });

            function nextStage() {
                if (stageIdx >= coverScanStages.length) return;
                const stage = coverScanStages[stageIdx++];
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
                    items.forEach(i => {
                        if (!i) return;
                        i.classList.remove('active');
                        i.classList.add('done');
                    });
                }
                coverScanTimer = setTimeout(nextStage, 800);
            }

            nextStage();
        }

        const enforceSignatureBreak = (input) => String(input || '')
            .replace(/(Sincerely,)(?!\s*(?:\n|<br\b|<\/p>))\s*/gi, '$1\n')
            .replace(/(Sincerely,)(?:&nbsp;|\u00a0)+/gi, '$1\n');

        const normalizeBodyHtml = (input) => {
                const normalizedInput = enforceSignatureBreak(input);
                const raw = String(normalizedInput || '').trim();
                if (!raw) return '';
                if (/<[a-z][\s\S]*>/i.test(raw)) return raw.replace(/(Sincerely,)\s*\n\s*/gi, '$1<br>');
                return raw
                    .split(/\n{2,}/)
                    .map(p => p.trim())
                    .filter(Boolean)
                    .map(p => `<p>${nl2br(p)}</p>`)
                    .join('');
        };
        const bodyToEditorHtml = (input) => normalizeBodyHtml(input);
        const previewFallbackHtml = (message = 'Preview unavailable right now.') => `
            <div class="preview-fallback">
                <strong>Template preview unavailable</strong>
                <p>${esc(message)}</p>
            </div>
        `;
        function schedulePreviewScale() {
            requestAnimationFrame(() => {
                scalePreview();
                setTimeout(scalePreview, 60);
                setTimeout(scalePreview, 180);
            });
        }

        function render() {
            const content = $('cl-content');
            if (!content) return;
            if (!state.templateId) {
                content.innerHTML = previewFallbackHtml('Choose a template to see your cover letter preview.');
                return;
            }

            let html = String(tplHtml[state.templateId] || '').trim();
            state.body = enforceSignatureBreak(state.body);
            const bodyHtml = normalizeBodyHtml(state.body);
            const tokens = {
                name: esc(state.name),
                email: esc(state.email),
                mobile: esc(state.mobile),
                location: esc(state.location),
                company: esc(state.company),
                company_name: esc(state.company),
                job_role: esc(state.job_role),
                skills: esc(state.skills),
                body: bodyHtml
            };

            if (!html) {
                const fallbackHtml = String(renderedTplHtml[state.templateId] || '').trim();
                content.innerHTML = fallbackHtml || previewFallbackHtml('We could not load this template. Please try another template.');
                schedulePreviewScale();
                return;
            }

            html = html.replace(/<p>\s*(\{\{\s*body\s*\}\}|\[\[\s*body\s*\]\])\s*<\/p>/gi, '$1');

            Object.entries(tokens).forEach(([key, val]) => {
                const reg = new RegExp('\\{\\{\\s*'+key+'\\s*\\}\\}|\\[\\[\\s*'+key+'\\s*\\]\\]', 'gi');
                html = html.replace(reg, val);
            });

            // Cleanup empty separators like " |  | " or " •  • " if some tokens were empty
            html = html.replace(/\s*[•|·]\s*(?=\s*[•|·]|$)/g, '');
            html = html.replace(/[•|·]\s*$/g, '');
            html = html.replace(/^\s*[•|·]\s*/g, '');

            if (!html.trim()) {
                content.innerHTML = previewFallbackHtml('This template did not return any visible content.');
            } else {
                content.innerHTML = html;
            }
            
            const badge = $('resume-status-badge');
            if (badge) {
                badge.classList.toggle('hidden', !state.resume_uploaded);
            }
            
            // Re-sync scroll height for better mobile experience
            schedulePreviewScale();
        }

        function scalePreview() {
            const canvas = $('preview-canvas');
            const a4 = $('preview-a4');
            if (!canvas || !a4) return;
            
            const isMobile = window.innerWidth <= 1024;
            const containerW = canvas.clientWidth;
            if (containerW === 0) {
                // If hidden, try to use the parent's width or window width
                const parentW = canvas.parentElement.clientWidth;
                if (parentW > 0) {
                    scaleWithWidth(parentW, a4, isMobile);
                }
                return;
            }
            
            scaleWithWidth(containerW, a4, isMobile);
        }

        function scaleWithWidth(containerW, a4, isMobile) {
            const canvas = $('preview-canvas');
            const canvasStyles = canvas ? window.getComputedStyle(canvas) : null;
            const horizontalPadding = canvasStyles
                ? parseFloat(canvasStyles.paddingLeft) + parseFloat(canvasStyles.paddingRight)
                : 0;
            const padding = isMobile ? horizontalPadding : Math.max(horizontalPadding, 72);
            const availW = Math.max(0, containerW - padding);
            const baseWidth = 794;
            
            let scale = availW / baseWidth;
            
            if (!isMobile) {
                scale = Math.min(scale, 0.92);
                if (scale > 0.88) scale = 0.88;
            } else {
                scale = Math.min(1, containerW / baseWidth);
            }

            a4.style.transform = `scale(${scale})`;
            a4.style.transformOrigin = 'top center';
            a4.style.setProperty('width', `${baseWidth}px`, 'important');
            a4.style.setProperty('max-width', 'none', 'important');
            a4.style.minHeight = '1123px';
            a4.style.height = '';

            const rawHeight = Math.max(1123, a4.scrollHeight);
            a4.style.marginBottom = `-${rawHeight * (1 - scale)}px`;
        }

        // Deep-link support: /cover-letter?template_id=123
        document.addEventListener('DOMContentLoaded', () => {
            if (editingCoverLetter) {
                Object.assign(state, editingCoverLetter.data || {});
                state.id = editingCoverLetter.id;
                state.templateId = String(editingCoverLetter.template_id || state.template_id || state.templateId || selectedTemplateIdFromUrl || '');
                state.body = enforceSignatureBreak(state.body);
                const name = tplNames[state.templateId] || '';
                if (name && $('active-tmpl-name')) $('active-tmpl-name').textContent = name;
                syncStateToFields();
                setActiveModalTemplate(state.templateId);
                switchStep('build');
                render();
            } else if (selectedTemplateIdFromUrl) {
                state.templateId = String(selectedTemplateIdFromUrl);
                const name = tplNames[state.templateId] || '';
                if (name && $('active-tmpl-name')) $('active-tmpl-name').textContent = name;
                switchStep('onboarding');
            }
        });

        window.pickTemplate = function(id) {
            state.templateId = id;
            $('active-tmpl-name').textContent = tplNames[id];
            setActiveModalTemplate(id);
            switchStep('onboarding');
        };

        window.chooseOnboarding = function(type) {
            if (type === 'scratch') {
                // Initialize with dummy data from state (already populated by $prefill)
                syncStateToFields();
                switchStep('build');
                render();
            } else {
                switchStep('resume');
            }
        };

        window.switchStep = function(stepId) {
            ['step-pick', 'step-onboarding', 'step-resume', 'step-build'].forEach(id => {
                $(id).style.display = (id === 'step-' + stepId) ? 'block' : 'none';
                if (id === 'step-build' && id === 'step-' + stepId) $(id).style.display = 'flex';
            });
            if (stepId === 'build') {
                schedulePreviewScale();
            } else if (stepId === 'pick') {
                scheduleTemplatePickerScale();
            }
            window.scrollTo(0,0);
        };

        window.goBack = function(stepId) {
            switchStep(stepId);
        };

        window.applyTemplate = function(id) {
            state.templateId = id;
            $('active-tmpl-name').textContent = tplNames[id];
            setActiveModalTemplate(id);
            render();
            closeModal();
        };

        window.closeModal = () => $('tmpl-modal').classList.remove('open');
        $('btn-change-tmpl').addEventListener('click', () => {
            setActiveModalTemplate(state.templateId);
            $('tmpl-modal').classList.add('open');
            scheduleTemplatePickerScale();
        });

        function setActiveModalTemplate(id) {
            document.querySelectorAll('.modal-tmpl-card').forEach((card) => {
                card.classList.toggle('active', String(card.dataset.id) === String(id));
            });
        }

        function syncStateToFields() {
            $('cl-name').value = state.name;
            $('cl-email').value = state.email;
            $('cl-mobile').value = state.mobile;
            $('cl-location').value = state.location;
            $('cl-company').value = state.company;
            $('cl-role').value = state.job_role;
            $('cl-skills').value = state.skills;
            $('cl-description').value = state.job_description;
            if (quill) {
                quill.root.innerHTML = bodyToEditorHtml(state.body);
            } else {
                $('cl-body').value = state.body;
            }
        }

        // Input Sync for Builder
        const fields = ['cl-name', 'cl-email', 'cl-mobile', 'cl-location', 'cl-company', 'cl-role', 'cl-skills', 'cl-description'];
        fields.forEach(id => {
            const el = $(id);
            if (!el) return;
            el.addEventListener('input', e => {
                let key = id.replace('cl-', '').replace('-', '_');
                if (key === 'role') key = 'job_role';
                if (key === 'description') key = 'job_description';
                state[key] = e.target.value;
                render();
            });
        });

        // Initialize Quill
        function initQuill() {
            if (!$('cl-body-editor')) return;
            quill = new Quill('#cl-body-editor', {
                theme: 'snow',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                        ['clean']
                    ]
                },
                placeholder: 'Write your cover letter here...'
            });

            quill.on('text-change', () => {
                state.body = enforceSignatureBreak(quill.root.innerHTML);
                render();
            });

            // Initial content
            quill.root.innerHTML = bodyToEditorHtml(state.body);
        }

        // Mobile Toggles
        $('toggle-edit')?.addEventListener('click', () => {
            document.querySelector('.builder-sidebar')?.classList.remove('hidden-mobile');
            document.querySelector('.builder-preview')?.classList.add('hidden-mobile');
            $('toggle-edit')?.classList.add('active');
            $('toggle-preview')?.classList.remove('active');
        });

        $('toggle-preview')?.addEventListener('click', () => {
            document.querySelector('.builder-sidebar')?.classList.add('hidden-mobile');
            document.querySelector('.builder-preview')?.classList.remove('hidden-mobile');
            $('toggle-edit')?.classList.remove('active');
            $('toggle-preview')?.classList.add('active');
            
            // Multiple triggers to handle layout transitions
            schedulePreviewScale();
        });

        // Ensure proper display on init
        function initResponsive() {
            if (window.innerWidth <= 1024) {
                document.querySelector('.builder-preview')?.classList.add('hidden-mobile');
            }
            setTimeout(schedulePreviewScale, 500);
        }
        initResponsive();
        initQuill();

        // Resume file label sync
        $('cl-resume-file-main')?.addEventListener('change', e => {
            const file = e.target.files?.[0];
            const nameEl = $('cl-resume-file-name-main');
            const box = e.target.closest('.resume-upload-box');
            
            if (file) {
                nameEl.textContent = 'Selected: ' + file.name;
                box?.classList.add('has-file');
                nameEl.style.color = 'var(--green)';
                state.resume_uploaded = true;
                render();
            } else {
                nameEl.textContent = 'Click to upload your resume';
                box?.classList.remove('has-file');
                nameEl.style.color = '';
                state.resume_uploaded = false;
                render();
            }
        });

        // Generate flow
        $('generate-letter-main').addEventListener('click', () => triggerGeneration(true));
        $('regenerate-letter').addEventListener('click', () => triggerGeneration(false));

        async function triggerGeneration(isFirst) {
            if (!state.templateId) {
                notify('Please select a template first.', 'error');
                switchStep('pick');
                return;
            }

            showCoverScanOverlay();
            try {
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('template_id', state.templateId);
                
                if (isFirst) {
                    const company = $('cl-setup-company').value;
                    const role = $('cl-setup-role').value;
                    const desc = $('cl-setup-description').value;
                    const file = $('cl-resume-file-main').files[0];
                    
                    if (company) formData.append('company_name', company);
                    if (role) formData.append('job_role', role);
                    if (desc) formData.append('job_description', desc);
                    if (file) formData.append('resume_file', file);
                } else {
                    formData.append('name', state.name || '');
                    formData.append('email', state.email || '');
                    formData.append('mobile', state.mobile || '');
                    formData.append('location', state.location || '');
                    formData.append('company_name', state.company || '');
                    formData.append('job_role', state.job_role || '');
                    formData.append('skills', state.skills || '');
                    formData.append('job_description', state.job_description || '');
                    if (state.id) formData.append('cover_letter_id', state.id);
                }

                const response = await fetch('{{ route("cover-letter.generate") }}', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                });

                const data = await response.json();
                if (data.success) {
                    state.id = data.cover_letter_id || state.id;
                    if (data.letter) {
                        const oldId = state.id;
                        Object.assign(state, data.letter);
                        state.id = oldId;
                    }
                    syncStateToFields();
                    switchStep('build');
                    render();
                } else {
                    notify(data.message || 'Generation failed.', 'error');
                }
            } catch (err) {
                notify('Connection error.', 'error');
            } finally {
                hideCoverScanOverlay();
            }
        }

        // Save & Download
        async function saveCoverLetter(downloadFormat = null) {
            try {
                const isNew = !state.id;
                const url = isNew ? '/cover-letter' : `/cover-letter/${state.id}`;
                const method = isNew ? 'POST' : 'PATCH';
                
                const response = await fetch(url, {
                    method: method,
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ 
                        letter: state,
                        template_id: state.templateId,
                        download_format: downloadFormat || undefined
                    })
                });

                const data = await response.json();
                if (response.ok) {
                    if (isNew && data.cover_letter_id) {
                        state.id = data.cover_letter_id;
                    }
                    if (data.redirect) {
                        window.location.href = data.redirect;
                        return false;
                    }
                    notify('Saved successfully.');
                    return true;
                } else {
                    notify('Save failed.', 'error');
                    return false;
                }
            } catch (err) { notify('Save failed.', 'error'); return false; }
        }

        $('save-letter').addEventListener('click', () => {
            saveCoverLetter();
        });

        $('download-btn').addEventListener('click', () => {
            if (!state.id) { notify('Please generate the letter first.', 'error'); return; }
            if (isAuthenticated && downloadRequiresPlan) { window.location.href = plansUrl; return; }

            const doDownload = async (format) => {
                const saved = await saveCoverLetter(format);
                if (!saved) return;

                window.location.href = `/cover-letter/${state.id}/download/${format}`;
            };

            if (window.openFormatDownloadModal) {
                window.openFormatDownloadModal(doDownload);
            } else {
                doDownload('pdf');
            }
        });

        window.addEventListener('resize', () => {
            schedulePreviewScale();
            scheduleTemplatePickerScale();
        });
        document.addEventListener('DOMContentLoaded', () => {
            scheduleTemplatePickerScale();
        });
    })();
</script>

@include('components.format-download-modal')

@endsection
