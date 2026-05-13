@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-950">My Cover Letters</h1>
            <p class="text-gray-600 mt-1">Browse, edit, and download your saved cover letters.</p>
        </div>
        <a href="{{ route('cover-letter') }}" class="inline-flex items-center justify-center rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-800">Create Cover Letter</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($letters as $letter)
            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="h-44 bg-slate-50 overflow-hidden">
                    @if(!empty($previews[$letter->id]))
                        <div class="w-[794px] min-h-[1123px] origin-top-left scale-[0.155] pointer-events-none">
                            {!! $previews[$letter->id] !!}
                        </div>
                    @endif
                </div>
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 truncate">{{ $letter->job_role ?: 'General Application' }}</h3>
                    <p class="text-sm text-gray-500 truncate">{{ $letter->company ?: 'N/A' }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $letter->template->name ?? 'Cover Letter Template' }}</p>
                    <div class="mt-3 flex items-center gap-3 text-sm">
                        <a class="text-gray-900 font-semibold" href="{{ route('cover-letter.download', [$letter, 'pdf']) }}">Download PDF</a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-lg border border-dashed border-gray-300 bg-white px-6 py-12 text-center text-gray-500">
                No cover letters yet. Create your first one.
            </div>
        @endforelse
    </div>

    <div class="mt-8">
        {{ $letters->links() }}
    </div>
</div>
@endsection
