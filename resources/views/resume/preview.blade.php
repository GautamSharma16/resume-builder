@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-950">Preview</h1>
        <a href="{{ route('resume.edit', $resume) }}" class="text-sm font-semibold text-teal-700">Edit</a>
    </div>
    <div class="overflow-auto bg-slate-100 border border-gray-200 rounded-lg p-6 shadow-sm">
        <div class="mx-auto w-[794px] bg-white shadow-xl">
            @if($renderedTemplate)
                {!! $renderedTemplate !!}
            @else
                <div class="p-8">@include('resume.partials.preview', ['resume' => $resume->data])</div>
            @endif
        </div>
    </div>
</div>
@endsection
