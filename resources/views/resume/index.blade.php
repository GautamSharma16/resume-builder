@extends('layouts.app')

@section('content')
@php
    $requiresPlanForDownload = auth()->check() && ! auth()->user()->activeSubscription?->hasDownloadsRemaining();
@endphp
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-950">My Resumes</h1>
            <p class="text-gray-600 mt-1">Create, edit, preview, and download your saved resumes.</p>
        </div>
        <a href="{{ route('resume.create') }}" class="inline-flex items-center justify-center rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-800">Create CV</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($resumes as $resume)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="h-44 bg-slate-50 overflow-hidden border-b border-gray-100">
                    @if(!empty($previews[$resume->id]))
                        <div class="w-[794px] min-h-[1123px] origin-top-left scale-[0.155] pointer-events-none">
                            {!! $previews[$resume->id] !!}
                        </div>
                    @endif
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 truncate">{{ $resume->title }}</h3>
                    <p class="text-sm text-gray-500 truncate">{{ ucfirst($resume->source) }} • {{ $resume->updated_at->format('d M Y') }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $resume->template->name ?? 'Resume Template' }}</p>
                    <div class="mt-3 flex flex-wrap items-center gap-3 text-sm">
                        <a class="text-teal-700 font-semibold" href="{{ route('resume.edit', $resume) }}">Edit</a>
                        <a class="text-gray-700 font-semibold" href="{{ route('resume.preview', $resume) }}">Preview</a>
                        <button type="button" class="text-blue-700 font-semibold js-rename-resume" data-id="{{ $resume->id }}" data-title="{{ $resume->title }}">Rename</button>
                        <a class="text-gray-900 font-semibold" href="{{ route('resume.download', [$resume, 'pdf']) }}" @if($requiresPlanForDownload && ! $resume->is_paid) data-open-plan-modal @endif>PDF</a>
                        <a class="text-gray-900 font-semibold" href="{{ route('resume.download', [$resume, 'doc']) }}" @if($requiresPlanForDownload && ! $resume->is_paid) data-open-plan-modal @endif>DOC</a>
                        <a class="text-gray-900 font-semibold" href="{{ route('resume.download', [$resume, 'ppt']) }}" @if($requiresPlanForDownload && ! $resume->is_paid) data-open-plan-modal @endif>PPT</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-lg border border-dashed border-gray-300 bg-white px-6 py-12 text-center text-gray-500">
                No resumes yet. Start with Create CV.
            </div>
        @endforelse
    </div>

    <form id="resume-rename-form" method="POST" class="hidden">
        @csrf
        @method('PATCH')
        <input type="hidden" name="title" id="resume-rename-title">
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('resume-rename-form');
    const titleInput = document.getElementById('resume-rename-title');
    if (!form || !titleInput) return;

    document.querySelectorAll('.js-rename-resume').forEach((btn) => {
        btn.addEventListener('click', () => {
            const current = btn.dataset.title || '';
            const next = window.prompt('Rename resume:', current);
            if (next === null) return;
            const trimmed = next.trim();
            if (!trimmed) return;

            form.action = `/resume/${btn.dataset.id}/rename`;
            titleInput.value = trimmed;
            form.submit();
        });
    });
});
</script>
@endsection
