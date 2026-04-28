@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="text-center mb-10">
        <h1 class="text-3xl md:text-4xl font-bold text-gray-950">Templates</h1>
        <p class="mt-3 text-gray-600">Choose a resume or cover letter template to start editing.</p>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($templates as $template)
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                <div class="h-48 bg-teal-50 flex items-center justify-center">
                    <div class="w-28 h-36 bg-white border border-teal-200 shadow-sm p-3">
                        <div class="h-2 bg-gray-900 mb-3"></div><div class="h-1.5 bg-teal-600 mb-2"></div><div class="h-1 bg-gray-300 mb-1"></div><div class="h-1 bg-gray-300 mb-1"></div><div class="h-1 bg-gray-300 w-2/3"></div>
                    </div>
                </div>
                <div class="p-5">
                    <p class="text-xs font-semibold uppercase text-teal-700">{{ str_replace('_', ' ', $template->type) }}</p>
                    <h2 class="mt-1 text-xl font-bold text-gray-950">{{ $template->name }}</h2>
                    <p class="mt-1 text-sm text-gray-500">{{ ucfirst($template->category) }}</p>
                    <a href="{{ $template->type === 'resume' ? route('resume.create', ['template' => $template->slug]) : route('cover-letter') }}" class="mt-5 inline-flex rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white">Use Template</a>
                </div>
            </div>
        @empty
            <p class="col-span-full text-center text-gray-500">No active templates yet.</p>
        @endforelse
    </div>
</div>
@endsection
