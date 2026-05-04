{{-- resources/views/admin/templates/edit.blade.php --}}
@extends('layouts.admin')

@section('title', 'Edit Template')

@section('content')
<div class="mx-auto max-w-3xl py-8 px-4">

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-xl font-bold text-gray-900">Edit Template</h1>

        <div class="flex gap-3">
            {{-- Live preview in new tab --}}
            <a href="{{ route('admin.templates.preview', $template) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7
                             -1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                Preview
            </a>

            {{-- Download current HTML as PDF --}}
            <a href="{{ route('admin.templates.download', $template) }}"
               class="inline-flex items-center gap-1.5 rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Download PDF
            </a>
        </div>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-800">{{ session('error') }}</div>
    @endif

    <form method="POST"
          action="{{ route('admin.templates.update', $template) }}"
          enctype="multipart/form-data"
          class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('admin.templates._form')
    </form>
</div>
@endsection