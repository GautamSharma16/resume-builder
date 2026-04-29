@extends('layouts.app')

@section('content')
<div id="create-cv-app" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" data-store-url="{{ route('resume.store') }}">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-950">Create CV</h1>
        <p class="text-gray-600 mt-2">Choose manual entry or upload later, select a template, then edit with live preview.</p>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm space-y-5">
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-2">Do you have an existing resume?</label>
                <div class="inline-flex rounded-md border border-gray-300 overflow-hidden">
                    <button type="button" data-source="upload" class="source-btn px-4 py-2 text-sm font-semibold">Yes</button>
                    <button type="button" data-source="manual" class="source-btn px-4 py-2 text-sm font-semibold bg-teal-700 text-white">No</button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-2">Template</label>
                <select id="template-id" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">Classic ATS Resume</option>
                    @foreach($templates as $template)
                        <option value="{{ $template->id }}" @selected($selectedTemplate === $template->slug)>{{ $template->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <input id="cv-name" class="cv-field rounded-md border-gray-300 text-sm" placeholder="Name" data-field="name">
                <input id="cv-contact" class="cv-field rounded-md border-gray-300 text-sm" placeholder="Email · Phone · Links" data-field="contact">
                <input id="cv-address" class="cv-field md:col-span-2 rounded-md border-gray-300 text-sm" placeholder="Address" data-field="address">
            </div>

            <textarea id="cv-summary" rows="4" class="cv-field w-full rounded-md border-gray-300 text-sm" placeholder="Professional summary" data-field="summary"></textarea>
            <input id="cv-skills" class="cv-field w-full rounded-md border-gray-300 text-sm" placeholder="Skills, comma separated" data-field="skills">

            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-950">Experience</h2>
                    <button id="add-exp" type="button" class="text-sm font-semibold text-teal-700">Add</button>
                </div>
                <div id="exp-editor" class="space-y-3"></div>
            </div>

            <div>
                <div class="flex items-center justify-between mb-3">
                    <h2 class="font-semibold text-gray-950">Education</h2>
                    <button id="add-edu" type="button" class="text-sm font-semibold text-teal-700">Add</button>
                </div>
                <div id="edu-editor" class="space-y-3"></div>
            </div>

            <button id="save-cv" type="button" class="w-full rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-800">Save and Continue</button>
            <p id="cv-status" class="text-sm text-gray-600"></p>
        </div>

        <article id="cv-preview" class="bg-white border border-gray-200 rounded-lg p-8 shadow-sm min-h-[850px]"></article>
    </div>
</div>

@include('resume.partials.editor-script')
@endsection
