{{-- resources/views/templates/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Resume Templates - Cvbliss')

@section('content')


<style>
    /* ─── TOKENS (matching home page theme - blueish) ───────── */
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
        --r-lg:  18px;
        --r-xl:  28px;
        --r-2xl: 36px;
        --r-full: 999px;

        --ease-spring: cubic-bezier(0.175, 0.885, 0.32, 1.275);
        --ease-out: cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    [x-cloak] { display: none !important; }

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

    .section-heading {
        font-family: var(--font-display);
        font-size: clamp(2rem, 4vw, 3.2rem);
        font-weight: 400;
        color: var(--navy);
        line-height: 1.2;
    }
    .section-heading em {
        font-style: italic;
        background: linear-gradient(135deg, var(--blue), var(--purple));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }

    @keyframes pulse-ring {
        0%   { box-shadow: 0 0 0 0 rgba(37,99,235,0.4); }
        70%  { box-shadow: 0 0 0 15px rgba(37,99,235,0); }
        100% { box-shadow: 0 0 0 0 rgba(37,99,235,0); }
    }

    @keyframes fadeUp {
        from { opacity: 0; transform: translateY(40px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.95); }
        to   { opacity: 1; transform: scale(1); }
    }

    /* Custom scrollbar for modal */
    #modal-scroll-container::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    #modal-scroll-container::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
    }
    #modal-scroll-container::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 10px;
    }
    #modal-scroll-container::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    #template-modal-body {
        transition: transform 0.15s ease-out;
        will-change: transform;
    }

    .animate-scale-in {
        animation: scaleIn 0.2s ease-out;
    }

    /* Improved button styles */
    .btn-use-template {
        position: relative;
        overflow: hidden;
        transition: all 0.3s var(--ease-spring);
    }
    .btn-use-template:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37,99,235,0.3);
    }
    .btn-use-template:active {
        transform: translateY(0);
    }
    .btn-use-template::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    .btn-use-template:hover::before {
        width: 300px;
        height: 300px;
    }
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
                        'word' => 'MS Word'
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
            $categories = ['ats', 'fresher', 'experienced', 'word'];
            $categoryColors = [
                'ats' => 'blue',
                'fresher' => 'indigo',
                'experienced' => 'purple',
                'word' => 'blue',
            ];
            $categoryIcons = [
                'ats' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
                'fresher' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
                'experienced' => 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                'word' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
            ];
        @endphp

        @foreach($categories as $category)
            <div 
                x-show="tab === '{{ $category }}'" 
                x-transition.duration.200ms
                x-cloak
            >
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    @php
                        $filteredTemplates = $templates->where('type', 'resume')->where('category', $category);
                    @endphp
                    @forelse($filteredTemplates as $template)
                        <div 
                            class="group bg-white rounded-2xl border border-gray-200 hover:border-{{ $categoryColors[$category] }}-300 hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col relative animate-fadeUp"
                            style="animation: fadeUp 0.5s var(--ease-out) both; animation-delay: {{ $loop->index * 0.05 }}s;"
                        >
                            {{-- POPULAR BADGE --}}
                            @if($loop->first && $category !== 'word')
                            <div class="absolute top-3 right-3 z-10 bg-gradient-to-r from-amber-400 to-orange-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full shadow-md flex items-center gap-1">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                POPULAR
                            </div>
                            @endif

                            {{-- PREVIEW THUMBNAIL --}}
                            <div class="relative overflow-hidden bg-gray-100 cursor-pointer group/preview" style="height: 280px;">
                                <div class="pointer-events-none absolute top-4 left-1/2 transition-all duration-500 group-hover/preview:-translate-y-2 group-hover/preview:shadow-2xl"
                                     style="width: 794px; transform: translateX(-50%) scale(0.34); transform-origin: top center;">
                                    <div class="bg-white shadow-sm" style="width: 794px; min-height: 1123px;">
                                        {!! $rendered[$template->id] ?? '<div class="p-8 text-gray-400">Preview not available</div>' !!}
                                    </div>
                                </div>

                                {{-- HOVER OVERLAY --}}
                                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-end justify-center pb-6">
                                    <button
                                        type="button"
                                        class="template-preview-btn transform translate-y-4 group-hover:translate-y-0 transition-all duration-300 bg-white text-gray-800 border border-gray-200 px-5 py-2.5 rounded-xl text-sm font-semibold shadow-lg hover:shadow-xl"
                                        data-template-id="{{ $template->id }}"
                                        data-template-name="{{ $template->name }}">
                                        <svg class="inline w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Quick Preview
                                    </button>
                                </div>
                            </div>

                            {{-- CARD FOOTER --}}
                            <div class="p-5 flex flex-col gap-4 border-t border-gray-100 bg-white">
                                <div>
                                    <div class="flex items-start justify-between">
                                        <div>
                                            <h3 class="text-lg font-bold text-gray-900 leading-tight group-hover:text-{{ $categoryColors[$category] }}-600 transition-colors">
                                                {{ $template->name }}
                                            </h3>
                                            <!-- <div class="flex items-center gap-2 mt-2">
                                                <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full bg-{{ $categoryColors[$category] }}-50 text-{{ $categoryColors[$category] }}-700">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                    {{ $category === 'ats' ? 'ATS Optimized' : ($category === 'fresher' ? 'Entry Level' : ($category === 'experienced' ? 'Senior Pro' : 'MS Word')) }}
                                                </span>
                                                <span class="text-xs text-gray-400 flex items-center gap-1">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    PDF Ready
                                                </span>
                                            </div> -->
                                        </div>
                                        <!-- <button
                                            type="button"
                                            class="template-preview-btn text-gray-400 hover:text-{{ $categoryColors[$category] }}-600 transition-all p-2 rounded-lg hover:bg-{{ $categoryColors[$category] }}-50"
                                            data-template-id="{{ $template->id }}"
                                            data-template-name="{{ $template->name }}"
                                            title="Preview template">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button> -->
                                    </div>
                                </div>

                                <a href="{{ route('resume.create', ['template_id' => $template->id]) }}"
                                   class="btn-use-template w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold text-center py-3 rounded-xl transition-all duration-200 shadow-md flex items-center justify-center gap-2 group/btn">
                                    <span>Use This Template</span>
                                    <svg class="w-4 h-4 transition-transform duration-200 group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="col-span-full py-20 text-center">
                            <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-2xl flex items-center justify-center">
                                <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            <p class="text-gray-400 font-medium">No templates in this category yet</p>
                            <p class="text-sm text-gray-300 mt-1">Check back soon for new designs</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @endforeach

        {{-- FOOTER NOTE (matching home page style) --}}
        <div class="mt-20 text-center pt-8 border-t border-gray-200">
            <div class="flex justify-center gap-8 mb-6">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    ATS Friendly
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    PDF Ready
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Fully Customizable
                </div>
            </div>
            <p class="text-xs text-gray-400">All templates are fully customizable. Create your professional resume in minutes.</p>
        </div>
    </div>
</div>

{{-- PREVIEW MODAL --}}
<div
    id="template-modal"
    class="fixed inset-0 z-50 hidden"
    @keydown.escape.window="isModalOpen = false"
>
    <div class="absolute inset-0 bg-black/80 backdrop-blur-md transition-opacity"></div>
    
    <div class="relative h-full flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-6xl max-h-[90vh] flex flex-col overflow-hidden border border-white/20 animate-scale-in">
            
            {{-- Modal header --}}
            <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-b border-gray-200 shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </div>
                    <div>
                        <span class="text-xs font-semibold text-blue-700 uppercase tracking-wide">Live Preview</span>
                        <h2 id="template-modal-title" class="text-xl font-bold text-gray-900 leading-tight">Loading...</h2>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div id="modal-loading-indicator" class="hidden">
                        <svg class="animate-spin w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                    <a id="modal-apply-btn"
                       href="#"
                       class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-5 py-2 rounded-xl transition-all shadow-md hover:shadow-lg flex items-center gap-2">
                        <span>Use This Template</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </a>
                    <button id="template-modal-close" class="w-9 h-9 flex items-center justify-center rounded-xl text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-all">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 6L6 18M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Modal body with zoomable content --}}
            <div class="overflow-auto flex-1 bg-gray-100 p-6" id="modal-scroll-container">
                <div id="template-modal-body" class="bg-white mx-auto shadow-xl rounded-lg overflow-hidden transition-transform duration-200" style="width: 794px; max-width: 100%; transform-origin: top center;">
                    <div class="flex items-center justify-center py-20 text-gray-400">
                        <svg class="animate-spin w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Loading preview...
                    </div>
                </div>
            </div>

            {{-- Zoom controls footer --}}
            <div class="flex items-center justify-between px-6 py-3 border-t border-gray-100 bg-gray-50 shrink-0">
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500">Zoom:</span>
                    <div class="flex items-center gap-1 bg-white rounded-lg border border-gray-200 p-1">
                        <button id="zoom-out" class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-gray-100 text-gray-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                        </button>
                        <span id="zoom-level" class="text-xs font-medium text-gray-700 w-12 text-center">100%</span>
                        <button id="zoom-in" class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-gray-100 text-gray-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        </button>
                        <div class="w-px h-6 bg-gray-200 mx-1"></div>
                        <button id="zoom-reset" class="w-8 h-8 flex items-center justify-center rounded-md hover:bg-gray-100 text-gray-500 transition-colors" title="Reset zoom">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                        </button>
                    </div>
                </div>
                <div class="flex items-center gap-2 text-[11px] text-gray-400">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span>Scroll to view • ESC to close</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rendered   = @json($rendered);
    const applyBase  = "{{ route('resume.create', ['template_id' => '__ID__']) }}";
    const modal      = document.getElementById('template-modal');
    const modalBody  = document.getElementById('template-modal-body');
    const modalTitle = document.getElementById('template-modal-title');
    const applyBtn   = document.getElementById('modal-apply-btn');
    const closeBtn   = document.getElementById('template-modal-close');
    const loadingIndicator = document.getElementById('modal-loading-indicator');
    let currentTemplateId = null;

    // Zoom functionality
    let currentZoom = 100;
    const previewContainer = document.getElementById('template-modal-body');
    const zoomLevelSpan = document.getElementById('zoom-level');
    
    function updateZoom(zoom) {
        currentZoom = Math.min(150, Math.max(40, zoom));
        if (previewContainer) {
            previewContainer.style.transform = `scale(${currentZoom / 100})`;
            previewContainer.style.transformOrigin = 'top center';
            zoomLevelSpan.textContent = currentZoom + '%';
        }
    }
    
    const zoomInBtn = document.getElementById('zoom-in');
    const zoomOutBtn = document.getElementById('zoom-out');
    const zoomResetBtn = document.getElementById('zoom-reset');
    
    if (zoomInBtn) zoomInBtn.addEventListener('click', () => updateZoom(currentZoom + 10));
    if (zoomOutBtn) zoomOutBtn.addEventListener('click', () => updateZoom(currentZoom - 10));
    if (zoomResetBtn) zoomResetBtn.addEventListener('click', () => updateZoom(100));

    function openModal(templateId, templateName) {
        if (!rendered[templateId]) {
            console.error('Template not found:', templateId);
            return;
        }
        
        currentTemplateId = templateId;
        modalTitle.textContent = templateName;
        
        if (loadingIndicator) loadingIndicator.classList.remove('hidden');
        if (applyBtn) applyBtn.classList.add('hidden');
        modalBody.innerHTML = `
            <div class="flex items-center justify-center py-20 text-gray-400">
                <svg class="animate-spin w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Loading preview...
            </div>
        `;
        
        modal.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        
        updateZoom(100);
        
        setTimeout(() => {
            modalBody.innerHTML = rendered[templateId];
            if (applyBtn) {
                applyBtn.href = applyBase.replace('__ID__', templateId);
                applyBtn.classList.remove('hidden');
            }
            if (loadingIndicator) loadingIndicator.classList.add('hidden');
            updateZoom(currentZoom);
        }, 50);
    }

    function closeModal() {
        modal.classList.add('hidden');
        document.body.style.overflow = '';
        currentTemplateId = null;
        updateZoom(100);
    }

    function attachPreviewHandlers() {
        document.querySelectorAll('.template-preview-btn').forEach(btn => {
            btn.removeEventListener('click', window._previewHandler);
            window._previewHandler = () => {
                openModal(btn.dataset.templateId, btn.dataset.templateName);
            };
            btn.addEventListener('click', window._previewHandler);
        });
    }
    
    attachPreviewHandlers();
    
    const observer = new MutationObserver(() => attachPreviewHandlers());
    observer.observe(document.body, { childList: true, subtree: true });

    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    modal.addEventListener('click', function (e) {
        if (e.target === modal || e.target.classList.contains('absolute')) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });
});
</script>
@endpush

@endsection