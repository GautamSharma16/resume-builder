@extends('layouts.app')

@section('content')
@php
    $requiresPlanForDownload = auth()->check() && ! auth()->user()->activeSubscription?->hasDownloadsRemaining();
@endphp
<style>
    .resume-list-card { transition: transform 260ms ease, box-shadow 260ms ease, border-color 260ms ease; }
    .resume-list-card:hover { transform: translateY(-6px); }
    .resume-title-edit { display: none; grid-template-columns: minmax(0, 1fr) auto auto; gap: 0.5rem; align-items: center; }
    .resume-title-edit.is-active { display: grid; }
    .resume-title-input {
        min-width: 0; height: 2.5rem; border-radius: 0.9rem; border: 1px solid rgba(15, 23, 42, 0.12);
        background: rgba(255, 255, 255, 0.92); padding: 0 0.85rem; font-size: 0.95rem; font-weight: 700;
        color: #0f172a; box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06); outline: none;
        transition: border-color 200ms ease, box-shadow 200ms ease;
    }
    .resume-title-input:focus { border-color: rgba(37, 99, 235, 0.55); box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12), 0 10px 28px rgba(15, 23, 42, 0.08); }
    .resume-edit-action {
        height: 2.5rem; display: inline-flex; align-items: center; justify-content: center; border-radius: 0.9rem;
        padding: 0 0.85rem; font-size: 0.78rem; font-weight: 800; transition: transform 180ms ease;
    }
    .resume-edit-action:hover { transform: translateY(-1px); }
    @media (max-width: 480px) {
        .resume-title-edit { grid-template-columns: 1fr 1fr; }
        .resume-title-input { grid-column: 1 / -1; }
    }
</style>
<div class="min-h-screen bg-slate-50 relative pb-20">
    <!-- Premium background elements -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[20%] -right-[10%] w-[70%] h-[70%] rounded-full bg-blue-100/40 blur-3xl"></div>
        <div class="absolute top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-emerald-50/40 blur-3xl"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-10">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-slate-900 tracking-tight" style="font-family: 'Instrument Sans', sans-serif;">My Resumes</h1>
                <p class="text-slate-500 mt-2 text-lg">Manage, edit, and download your professional resumes.</p>
            </div>
            <a href="{{ route('resume.create') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:shadow-blue-500/40 transition-all active:scale-95">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create New Resume
            </a>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($resumes as $resume)
                <div class="resume-list-card group bg-white/70 backdrop-blur-xl border border-white/60 rounded-3xl shadow-sm hover:shadow-xl hover:border-blue-100 overflow-hidden flex flex-col">
                    <!-- Preview Area -->
                    <div class="relative w-full overflow-hidden bg-gradient-to-b from-slate-50 to-slate-100/50 flex justify-center pt-8 border-b border-slate-100/80 transition-colors group-hover:from-blue-50/50 group-hover:to-slate-50" style="height: 340px;">
                        @if(!empty($previews[$resume->id]))
                            <div class="absolute top-6 left-1/2 -translate-x-1/2 w-[794px] h-[1123px] origin-top scale-[0.32] pointer-events-none rounded-md overflow-hidden bg-white shadow-[0_8px_30px_rgba(0,0,0,0.08)] group-hover:shadow-[0_12px_40px_rgba(0,0,0,0.12)] transition-shadow duration-300 ring-1 ring-slate-900/5">
                                {!! $previews[$resume->id] !!}
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-full text-slate-400">
                                <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="text-sm font-medium">No Preview Available</span>
                            </div>
                        @endif
                        
                        <!-- Overlay actions on hover -->
                        <div class="absolute inset-0 bg-slate-900/35 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-3">
                            <a href="{{ route('resume.preview', $resume) }}" class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-white text-slate-900 hover:scale-110 transition-transform shadow-lg" title="Preview Full Screen">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            </a>
                            <a href="{{ route('resume.edit', $resume) }}" class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-blue-600 text-white hover:scale-110 transition-transform shadow-lg shadow-blue-600/30" title="Edit Resume">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                            </a>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6 flex flex-col flex-grow relative bg-white/40">
                        <div class="flex justify-between items-start gap-3 mb-1">
                            <div class="min-w-0 flex-1">
                                <h3 class="js-title-display text-xl font-bold text-slate-900 truncate pr-4" style="font-family: 'Instrument Sans', sans-serif;">{{ $resume->title }}</h3>
                                <form method="POST" action="{{ route('resume.rename', $resume) }}" class="resume-title-edit js-inline-rename-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="title" class="resume-title-input js-inline-title-input" value="{{ $resume->title }}" maxlength="160" required>
                                    <button type="submit" class="resume-edit-action bg-blue-600 text-white shadow-sm shadow-blue-600/20">Save</button>
                                    <button type="button" class="resume-edit-action js-inline-rename-cancel border border-slate-200 bg-white text-slate-600">Cancel</button>
                                </form>
                            </div>
                            <div class="flex-shrink-0 bg-blue-50 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">
                                {{ $resume->template->name ?? 'Standard' }}
                            </div>
                        </div>
                        <p class="text-sm text-slate-500 font-medium mb-6">{{ ucfirst($resume->source) }} • Updated {{ $resume->updated_at->format('M d, Y') }}</p>
                        
                        <!-- Actions -->
                        <div class="mt-auto grid grid-cols-2 gap-3">
                            <!-- Download Options -->
                            <div class="relative group/dl">
                                <button type="button" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-semibold hover:bg-slate-800 transition-colors shadow-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    Download
                                </button>
                                <!-- Dropdown -->
                                <div class="absolute bottom-[calc(100%+0.5rem)] left-0 w-[110%] -translate-x-[4.5%] opacity-0 invisible group-hover/dl:opacity-100 group-hover/dl:visible transition-all duration-200 z-20">
                                    <div class="bg-white border border-slate-200 rounded-xl shadow-[0_10px_40px_rgba(0,0,0,0.1)] overflow-hidden py-1">
                                        <a href="{{ route('resume.download', [$resume, 'pdf']) }}" @if($requiresPlanForDownload && ! $resume->is_paid) data-open-plan-modal @endif class="flex items-center px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                            <span class="w-8 h-8 rounded bg-red-50 text-red-600 flex items-center justify-center mr-3 font-bold text-[10px]">PDF</span>
                                            Download PDF
                                        </a>
                                        <a href="{{ route('resume.download', [$resume, 'doc']) }}" @if($requiresPlanForDownload && ! $resume->is_paid) data-open-plan-modal @endif class="flex items-center px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-blue-600 transition-colors">
                                            <span class="w-8 h-8 rounded bg-blue-50 text-blue-600 flex items-center justify-center mr-3 font-bold text-[10px]">DOC</span>
                                            Download DOCX
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="js-rename-resume w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:border-slate-300 transition-colors shadow-sm">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                Rename
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-3xl border-2 border-dashed border-slate-200 bg-white/50 backdrop-blur-sm px-6 py-16 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 text-blue-500 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">No resumes yet</h3>
                    <p class="text-slate-500 max-w-sm mx-auto mb-6">You haven't created any resumes yet. Start building your professional profile to land your dream job.</p>
                    <a href="{{ route('resume.create') }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-6 py-3 text-sm font-semibold text-white hover:bg-blue-700 shadow-md transition-colors">
                        Create Your First Resume
                    </a>
                </div>
            @endforelse
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-rename-resume').forEach((btn) => {
        btn.addEventListener('click', () => {
            const card = btn.closest('.resume-list-card');
            const title = card?.querySelector('.js-title-display');
            const form = card?.querySelector('.js-inline-rename-form');
            const input = card?.querySelector('.js-inline-title-input');
            if (!card || !title || !form || !input) return;
            title.classList.add('hidden');
            form.classList.add('is-active');
            btn.classList.add('hidden');
            requestAnimationFrame(() => {
                input.focus();
                input.select();
            });
        });
    });

    document.querySelectorAll('.js-inline-rename-cancel').forEach((btn) => {
        btn.addEventListener('click', () => {
            const card = btn.closest('.resume-list-card');
            const title = card?.querySelector('.js-title-display');
            const form = card?.querySelector('.js-inline-rename-form');
            const input = card?.querySelector('.js-inline-title-input');
            const rename = card?.querySelector('.js-rename-resume');
            if (!card || !title || !form || !input || !rename) return;
            input.value = title.textContent.trim();
            title.classList.remove('hidden');
            form.classList.remove('is-active');
            rename.classList.remove('hidden');
        });
    });

    document.querySelectorAll('.js-inline-title-input').forEach((input) => {
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                input.closest('.resume-list-card')?.querySelector('.js-inline-rename-cancel')?.click();
            }
        });
    });
});
</script>
@endsection
