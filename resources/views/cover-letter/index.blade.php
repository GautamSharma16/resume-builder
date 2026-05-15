@extends('layouts.app')

@section('content')
<style>
    .cl-card {
        transition: transform 260ms ease, box-shadow 260ms ease, border-color 260ms ease;
    }
    .cl-card:hover {
        transform: translateY(-6px);
    }
    .cl-title-edit {
        display: none;
        grid-template-columns: minmax(0, 1fr) auto auto;
        gap: 0.5rem;
        align-items: center;
    }
    .cl-title-edit.is-active {
        display: grid;
    }
    .cl-title-input {
        min-width: 0;
        height: 2.5rem;
        border-radius: 0.9rem;
        border: 1px solid rgba(15, 23, 42, 0.12);
        background: rgba(255, 255, 255, 0.92);
        padding: 0 0.85rem;
        font-size: 0.95rem;
        font-weight: 700;
        color: #0f172a;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.06);
        outline: none;
        transition: border-color 200ms ease, box-shadow 200ms ease;
    }
    .cl-title-input:focus {
        border-color: rgba(16, 185, 129, 0.55);
        box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.12), 0 10px 28px rgba(15, 23, 42, 0.08);
    }
    .cl-edit-action {
        height: 2.5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.9rem;
        padding: 0 0.85rem;
        font-size: 0.78rem;
        font-weight: 800;
        transition: transform 180ms ease, background 180ms ease, border-color 180ms ease;
    }
    .cl-edit-action:hover { transform: translateY(-1px); }
    @media (max-width: 480px) {
        .cl-title-edit {
            grid-template-columns: 1fr 1fr;
        }
        .cl-title-input {
            grid-column: 1 / -1;
        }
    }
</style>
<div class="min-h-screen bg-slate-50 relative pb-20">
    <!-- Premium background elements -->
    <div class="absolute inset-0 z-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-[20%] -left-[10%] w-[70%] h-[70%] rounded-full bg-emerald-50/40 blur-3xl"></div>
        <div class="absolute top-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-blue-100/40 blur-3xl"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-10">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold text-slate-900 tracking-tight" style="font-family: 'Instrument Sans', sans-serif;">My Cover Letters</h1>
                <p class="text-slate-500 mt-2 text-lg">Browse, edit, and download your saved cover letters.</p>
            </div>
            <a href="{{ route('cover-letter') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-6 py-3.5 text-sm font-semibold text-white shadow-lg shadow-slate-900/20 hover:bg-slate-800 hover:shadow-slate-900/30 transition-all active:scale-95">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                Create Cover Letter
            </a>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse($letters as $letter)
                <div class="cl-card group bg-white/70 backdrop-blur-xl border border-white/60 rounded-3xl shadow-sm hover:shadow-xl hover:border-slate-200 overflow-hidden flex flex-col">
                    <!-- Preview Area -->
                    <div class="relative w-full overflow-hidden bg-gradient-to-b from-slate-50 to-slate-100/50 flex justify-center pt-8 border-b border-slate-100/80 transition-colors group-hover:from-emerald-50/30 group-hover:to-slate-50" style="height: 340px;">
                        @if(!empty($previews[$letter->id]))
                            <div class="absolute top-6 left-1/2 -translate-x-1/2 w-[794px] h-[1123px] origin-top scale-[0.32] pointer-events-none rounded-md overflow-hidden bg-white shadow-[0_8px_30px_rgba(0,0,0,0.08)] group-hover:shadow-[0_12px_40px_rgba(0,0,0,0.12)] transition-shadow duration-300 ring-1 ring-slate-900/5">
                                {!! $previews[$letter->id] !!}
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center h-full text-slate-400">
                                <svg class="w-12 h-12 mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span class="text-sm font-medium">No Preview Available</span>
                            </div>
                        @endif
                        
                        <!-- Overlay actions on hover -->
                        <div class="absolute inset-0 bg-slate-900/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center gap-3">
                            <!-- We don't have a direct full-screen preview route for cover letters in the original code, so we omit the preview eye icon if it doesn't exist, or we can just keep the edit/download actions -->
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="p-6 flex flex-col flex-grow relative bg-white/40">
                        <div class="flex justify-between items-start gap-3 mb-1">
                            <div class="min-w-0 flex-1">
                                <h3 class="js-title-display text-xl font-bold text-slate-900 truncate pr-4" style="font-family: 'Instrument Sans', sans-serif;">{{ $letter->job_role ?: 'General Application' }}</h3>
                                <form method="POST" action="{{ route('cover-letter.rename', $letter) }}" class="cl-title-edit js-inline-rename-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="title" class="cl-title-input js-inline-title-input" value="{{ $letter->job_role ?: 'General Application' }}" maxlength="160" required>
                                    <button type="submit" class="cl-edit-action bg-emerald-600 text-white shadow-sm shadow-emerald-600/20">Save</button>
                                    <button type="button" class="cl-edit-action js-inline-rename-cancel border border-slate-200 bg-white text-slate-600">Cancel</button>
                                </form>
                            </div>
                            <div class="flex-shrink-0 bg-emerald-50 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded uppercase tracking-wider">
                                {{ $letter->template->name ?? 'Standard' }}
                            </div>
                        </div>
                        <p class="text-sm text-slate-500 font-medium mb-1 truncate">{{ $letter->company ?: 'No Company Specified' }}</p>
                        <p class="text-xs text-slate-400 font-medium mb-6">Updated {{ $letter->updated_at->format('M d, Y') }}</p>
                        
                        <!-- Actions -->
                        <div class="mt-auto grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <a href="{{ route('cover-letter.download', [$letter, 'pdf']) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-900 text-white rounded-xl text-sm font-semibold hover:bg-slate-800 transition-colors shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                PDF
                            </a>
                            <a href="{{ route('cover-letter.download', [$letter, 'doc']) }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:border-slate-300 transition-colors shadow-sm">
                                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h10M7 11h10M7 15h6M5 3h10l4 4v14H5V3z"></path></svg>
                                DOC
                            </a>
                            
                            <button type="button" class="js-rename-letter w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-white border border-slate-200 text-slate-700 rounded-xl text-sm font-semibold hover:bg-slate-50 hover:border-slate-300 transition-colors shadow-sm">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                Rename
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full rounded-3xl border-2 border-dashed border-slate-200 bg-white/50 backdrop-blur-sm px-6 py-16 text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-slate-100 text-slate-500 mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-900 mb-2">No cover letters yet</h3>
                    <p class="text-slate-500 max-w-sm mx-auto mb-6">You haven't created any cover letters yet. Start writing personalized letters to boost your application.</p>
                    <a href="{{ route('cover-letter') }}" class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-6 py-3 text-sm font-semibold text-white hover:bg-slate-800 shadow-md transition-colors">
                        Create First Cover Letter
                    </a>
                </div>
            @endforelse
        </div>

        @if($letters->hasPages())
        <div class="mt-12">
            {{ $letters->links() }}
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-rename-letter').forEach((btn) => {
        btn.addEventListener('click', () => {
            const card = btn.closest('.cl-card');
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
            const card = btn.closest('.cl-card');
            const title = card?.querySelector('.js-title-display');
            const form = card?.querySelector('.js-inline-rename-form');
            const input = card?.querySelector('.js-inline-title-input');
            const rename = card?.querySelector('.js-rename-letter');
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
                input.closest('.cl-card')?.querySelector('.js-inline-rename-cancel')?.click();
            }
        });
    });
});
</script>
@endsection
