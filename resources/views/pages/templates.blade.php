@extends('layouts.app')

@section('title', 'Resume Templates - ResuMint')

@section('content')
<style>
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
        --green:       #10b981;
        --purple:      #8b5cf6;
        --teal:        #14b8a6;

        --font-display: 'DM Serif Display', serif;
        --font-body:    'Bricolage Grotesque', sans-serif;

        --r-sm:  6px;
        --r-md:  12px;
        --r-lg:  16px;
        --r-xl:  24px;
        --r-full: 999px;
        
        --ease-out:    cubic-bezier(0.33, 1, 0.68, 1);
        --ease-spring: cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    body {
        background-color: var(--white);
        color: var(--ink);
        font-family: var(--font-body);
        -webkit-font-smoothing: antialiased;
    }

    /* ─── HOME PAGE BACKGROUND STYLE ────────────────────────── */
    .bg-main {
        position: relative;
        background: #fff;
        overflow: hidden;
    }
    .bg-glow-1 {
        position: absolute; top: -10%; right: -10%; width: 50vw; height: 50vw;
        background: radial-gradient(circle, rgba(37,99,235,0.06) 0%, transparent 70%);
        pointer-events: none;
        z-index: 0;
    }
    .bg-glow-2 {
        position: absolute; bottom: -10%; left: -10%; width: 50vw; height: 50vw;
        background: radial-gradient(circle, rgba(139,92,246,0.04) 0%, transparent 70%);
        pointer-events: none;
        z-index: 0;
    }
    .noise-overlay {
        position: absolute; inset: 0; pointer-events: none; z-index: 1; opacity: 0.02;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        background-size: 200px;
    }

    /* ─── HEADER STYLE ──────────────────────────────────────── */
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
    .section-heading {
        font-family: var(--font-display);
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 400;
        color: var(--navy);
        line-height: 1.1;
    }
    .section-heading em {
        font-style: italic;
        background: linear-gradient(135deg, var(--blue), var(--purple));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    /* Tab styles */
    .tab-btn {
        transition: all 0.3s var(--ease-out);
        border: 1px solid var(--border);
        border-radius: var(--r-full);
        background: #fff;
        color: var(--muted);
    }
    .tab-btn.active {
        background: var(--navy);
        color: white;
        border-color: var(--navy);
        box-shadow: 0 10px 20px rgba(11,18,33,0.15);
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    /* QUICK VIEW OVERLAY */
    .qv-overlay {
        position: fixed; inset: 0; background: rgba(11,18,33,0.35); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); z-index: 9999;
        display: none; align-items: center; justify-content: center; padding: 40px; opacity: 0; transition: opacity 0.3s ease;
    }
    .qv-overlay.active { display: flex; opacity: 1; }
    .qv-content-wrapper { position: relative; display: flex; flex-direction: column; align-items: center; gap: 20px; max-width: 100%; max-height: 100%; }
    .qv-content-wrapper::-webkit-scrollbar { display: none; }
    .qv-content-wrapper { -ms-overflow-style: none; scrollbar-width: none; }
    .qv-paper {
        width: 794px; min-height: 1123px; background: white; box-shadow: 0 50px 100px rgba(0,0,0,0.3); transform-origin: top center;
        transition: transform 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); transform: scale(0.9); border-radius: 2px; overflow: hidden; border: 1px solid #000;
    }
    .qv-overlay.active .qv-paper { transform: scale(1); }
    .qv-toolbar {
        position: fixed; bottom: 40px; left: 50%; transform: translateX(-50%); background: rgba(255,255,255,0.95); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
        padding: 10px 20px; border-radius: 999px; display: flex; align-items: center; gap: 20px; box-shadow: 0 15px 50px rgba(0,0,0,0.25); z-index: 10002; border: 1px solid rgba(255,255,255,0.5);
    }
    .qv-close {
        position: fixed; top: 30px; right: 30px; width: 50px; height: 50px; background: white; color: var(--navy); border: none; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 10px 30px rgba(0,0,0,0.2); z-index: 10001; transition: all 0.2s;
    }
    .qv-close:hover { transform: scale(1.1) rotate(90deg); color: var(--blue); }
    .qv-use-btn { background: var(--blue); color: white; padding: 10px 24px; border-radius: 999px; font-weight: 700; font-size: 14px; text-decoration: none; transition: all 0.2s; box-shadow: 0 4px 12px rgba(37,99,235,0.25); }
    .qv-use-btn:hover { background: var(--blue-dark); transform: translateY(-2px); }
</style>

<div class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-gray-50 py-12 px-4 sm:px-6 lg:px-8" x-data="{ 
    tab: 'ats'
}">
    <div class="max-w-7xl mx-auto">

        {{-- HEADER WITH DECORATION (matching home page style) --}}
        <div class="text-center mb-12 relative">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-1/2 w-40 h-40 bg-blue-100 rounded-full blur-3xl opacity-40"></div>
            <div class="absolute bottom-0 right-0 w-32 h-32 bg-indigo-100 rounded-full blur-3xl opacity-30"></div>
            <div class="relative">
                <div class="inline-flex items-center gap-2 bg-white/80 backdrop-blur-sm px-4 py-2 rounded-full border border-gray-200 shadow-sm mb-6">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span class="text-xs font-semibold text-gray-700 tracking-wide">PROFESSIONAL DESIGNS</span>
                </div>
                <h1 class="text-5xl md:text-6xl font-bold text-gray-900 mb-4 tracking-tight" style="font-family: var(--font-display);">
                    Resume 
                    <span class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent">Templates</span>
                </h1>
                <p class="text-base text-gray-500 max-w-2xl mx-auto">Choose from our collection of ATS-friendly and modern designs to create your perfect resume that stands out</p>
            </div>
        </div>

        {{-- CATEGORY TABS WITH BLUEISH THEME --}}
        <div class="relative mb-12">
            <div class="flex flex-wrap gap-2 justify-center bg-white/60 backdrop-blur-sm p-2 rounded-2xl border border-gray-200/80 shadow-sm w-fit mx-auto">
                @php
                    $tabs = [
                        'ats' => 'ATS Optimized',
                        'fresher' => 'Entry Level', 
                        'experienced' => 'Senior Level',
                    ];
                @endphp
                @foreach($tabs as $key => $label)
                    <button
                        @click="tab='{{ $key }}'"
                        :class="tab==='{{ $key }}'
                            ? 'bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/25'
                            : 'bg-transparent text-gray-600 hover:text-blue-700 hover:bg-blue-50'"
                        class="relative px-6 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200">
                        {{ $label }}
                        <span x-show="tab==='{{ $key }}'" class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 bg-blue-500 rounded-full"></span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- TEMPLATES GRID --}}
        @php
            $categories = ['ats', 'fresher', 'experienced'];
            $categoryColors = [
                'ats' => 'blue',
                'fresher' => 'indigo',
                'experienced' => 'purple',
            ];
            $categoryIcons = [
                'ats' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                'fresher' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                'experienced' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
            ];
        @endphp

       
        @foreach($categories as $category)
            <div 
                x-show="tab === '{{ $category }}'" 
                class="transition-all d uration-300"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-cloak
            >
                <div class="flex flex-wrap justify-center gap-12">
                    @php
                        $filteredTemplates = $templates->where('type', 'resume');
                        if($category !== 'all') $filteredTemplates = $filteredTemplates->where('category', $category);
                    @endphp
                    @forelse($filteredTemplates as $template)
                        <div class="group flex flex-col relative animate-fadeUp w-[340px]" style="animation-delay: {{ $loop->index * 0.05 }}s;">
                            {{-- POPULAR BADGE --}}
                            @if($loop->first)
                            <div class="absolute -top-2 -right-2 z-10 bg-gradient-to-r from-amber-400 to-orange-500 text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-lg">
                                POPULAR
                            </div>
                            @endif

                            {{-- PREVIEW THUMBNAIL --}}
                            <div class="relative cursor-pointer group/preview overflow-hidden rounded-2xl border-2 border-black bg-white w-full" style="height: 481px;">
                                <div class="pointer-events-none absolute top-0 left-0 transition-all duration-700 group-hover/preview:scale-[1.05]"
                                     style="width: 794px; transform: scale(0.4282); transform-origin: top left;">
                                    <div class="bg-white" style="width: 794px; min-height: 1123px;">
                                        {!! $rendered[$template->id] ?? '<div class="p-8 text-gray-400">Preview not available</div>' !!}
                                    </div>
                                </div>

                                {{-- HOVER OVERLAY --}}
                                <div class="absolute inset-0 bg-black/40 backdrop-blur-[2px] opacity-0 group-hover:opacity-100 transition-all duration-400 flex flex-col items-center justify-center p-6">
                                    <h3 class="text-white text-xl font-bold mb-6 transform -translate-y-4 group-hover:translate-y-0 transition-transform duration-400 text-center">
                                        {{ $template->name }}
                                    </h3>
                                    
                                    <div class="flex items-center gap-4 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-400">
                                        <button type="button" class="template-preview-btn w-14 h-14 flex items-center justify-center bg-white text-gray-900 rounded-full shadow-2xl hover:bg-blue-600 hover:text-white transition-all duration-300"
                                            onclick="openModal('{{ $template->id }}', '{{ $template->name }}')"
                                            data-template-id="{{ $template->id }}"
                                            data-template-name="{{ $template->name }}">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>

                                        <a href="{{ route('resume.create', ['template_id' => $template->id]) }}" class="w-14 h-14 flex items-center justify-center bg-blue-600 text-white rounded-full shadow-2xl hover:bg-blue-700 transition-all duration-300">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-20 text-center">
                            <p class="text-gray-400 font-medium">No templates in this category yet</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach

        {{-- FOOTER NOTE --}}
        <div class="mt-20 text-center pt-8 border-t border-gray-100">
            <p class="text-xs text-gray-400">All templates are fully customizable. Create your professional resume in minutes.</p>
        </div>

        {{-- QUICK VIEW OVERLAY --}}
        <div id="template-modal" class="qv-overlay">
            <div class="qv-toolbar">
                <div id="template-preview-colors" class="flex items-center gap-2">
                    <button type="button" class="template-preview-color active h-7 px-3 rounded-full border-2 border-gray-900 bg-white text-[11px] font-bold text-gray-600" data-color="" title="Original">Original</button>
                    @foreach(['#2563eb' => 'Blue', '#10b981' => 'Emerald', '#475569' => 'Slate', '#e11d48' => 'Rose', '#4f46e5' => 'Indigo'] as $hex => $name)
                        <button type="button" class="template-preview-color w-7 h-7 rounded-full border-2 border-transparent" data-color="{{ $hex }}" title="{{ $name }}" style="background-color: {{ $hex }};"></button>
                    @endforeach
                </div>
                <div class="w-px h-6 bg-gray-200"></div>
                <a id="modal-apply-btn" href="#" class="qv-use-btn">Use Template</a>
            </div>

            <button id="template-modal-close" class="qv-close">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="qv-content-wrapper overflow-auto">
                <div id="template-modal-body" class="qv-paper"></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const rendered = @json($rendered);
    const applyBase  = "{{ route('resume.create', ['template_id' => '__ID__']) }}";
    const modal      = document.getElementById('template-modal');
    const modalBody  = document.getElementById('template-modal-body');
    const applyBtn   = document.getElementById('modal-apply-btn');
    const closeBtn   = document.getElementById('template-modal-close');
    const colorPicker = document.getElementById('template-preview-colors');
    let currentTemplateId = null;
    let selectedColor = '';

    function updateZoom() {
        const scale = Math.min((window.innerHeight - 150) / 1123, (window.innerWidth - 80) / 794, 1);
        if (modalBody) {
            modalBody.style.transform = `scale(${scale})`;
            modalBody.style.transformOrigin = 'top center';
        }
    }

    function resumeAccentStyle(color) {
        const accent = String(color || '');
        if (!/^#[0-9a-f]{6}$/i.test(accent)) return '';
        return `<style>
            #template-modal-body { --primary: ${accent}; }
            #template-modal-body .tpl-resume { border-color: var(--primary) !important; }
            #template-modal-body .tpl-resume h1, #template-modal-body .tpl-resume h2, #template-modal-body .tpl-resume h3, #template-modal-body .tpl-resume a, #template-modal-body .tpl-role-head strong { color: var(--primary) !important; border-color: var(--primary) !important; }
            #template-modal-body .tpl-badge { background: var(--primary) !important; color: #fff !important; }
            #template-modal-body .tpl-rule, #template-modal-body .tpl-accentbox header > div, #template-modal-body .tpl-two aside, #template-modal-body .tpl-carded header, #template-modal-body .tpl-band header, #template-modal-body .tpl-resume > header[style*="background"], #template-modal-body .tpl-resume h2[style*="background"] { background: var(--primary) !important; color: #fff !important; }
        </style>`;
    }

    function updateColorButtons() {
        colorPicker?.querySelectorAll('.template-preview-color').forEach(btn => {
            const active = (btn.dataset.color || '') === selectedColor;
            btn.classList.toggle('active', active);
            btn.style.borderColor = active ? '#111827' : (btn.dataset.color ? 'transparent' : '#e5e7eb');
        });
    }

    function renderModalPreview() {
        if (!currentTemplateId || !rendered[currentTemplateId]) return;
        modalBody.innerHTML = resumeAccentStyle(selectedColor) + rendered[currentTemplateId];
        if (applyBtn) {
            const url = applyBase.replace('__ID__', currentTemplateId);
            applyBtn.href = selectedColor ? `${url}&primary_color=${encodeURIComponent(selectedColor)}` : url;
        }
        updateColorButtons();
        updateZoom();
    }

    function openModal(templateId, templateName) {
        if (!rendered[templateId]) return;
        currentTemplateId = templateId;
        selectedColor = '';
        updateColorButtons();
        modalBody.innerHTML = '';
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        setTimeout(renderModalPreview, 50);
    }

    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        currentTemplateId = null;
    }

    document.querySelectorAll('.template-preview-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const tid = btn.dataset.templateId;
            const tname = btn.dataset.templateName;
            if (tid) openModal(tid, tname);
        });
    });

    closeBtn?.addEventListener('click', closeModal);
    modal?.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    window.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeModal(); });
    window.addEventListener('resize', updateZoom);

    colorPicker?.addEventListener('click', (e) => {
        const btn = e.target.closest('.template-preview-color');
        if (btn) {
            selectedColor = btn.dataset.color || '';
            renderModalPreview();
        }
    });
});
</script>
@endsection
