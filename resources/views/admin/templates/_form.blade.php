{{-- resources/views/admin/templates/form.blade.php --}}
{{-- Shared partial — included by both create.blade.php and edit.blade.php --}}

@if(session('error'))
    <div class="mb-4 rounded-md bg-red-50 border border-red-200 p-3 text-sm text-red-700">
        {{ session('error') }}
    </div>
@endif

@php
    $defaultType = old('type', $template->type ?: request('type', 'resume'));
    $defaultType = in_array($defaultType, ['resume', 'cover_letter'], true) ? $defaultType : 'resume';
    $defaultCategory = $defaultType === 'cover_letter' ? 'professional' : 'ats';
@endphp

<div class="space-y-5">

    {{-- ── Type ──────────────────────────────────────────────────────────── --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
        <select name="type" class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
            <option value="resume"       @selected($defaultType === 'resume')>Resume</option>
            <option value="cover_letter" @selected($defaultType === 'cover_letter')>Cover Letter</option>
        </select>
        @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- ── Name ──────────────────────────────────────────────────────────── --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
        <input name="name"
               value="{{ old('name', $template->name) }}"
               placeholder="e.g. Modern ATS Resume"
               required
               class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- ── Category ────────────────────────────────────────────────────────── --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
        <select name="category" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500 text-sm">
            @foreach([
                'ats'          => 'ATS Resume',
                'fresher'      => 'Fresher Resume',
                'experienced'  => 'Resume for Experienced',
                'word'         => 'MS Word Resume',
                'professional' => 'Professional Cover Letter',
                'modern'       => 'Modern Cover Letter',
                'executive'    => 'Executive Cover Letter',
                'minimal'      => 'Minimal Cover Letter',
            ] as $value => $label)
                <option value="{{ $value }}" @selected(old('category', $template->category ?: $defaultCategory) === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('category')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    {{-- ── PDF Upload ──────────────────────────────────────────────────────── --}}
    <div class="rounded-lg border-2 border-dashed border-teal-300 bg-teal-50 p-4">
        <div class="flex items-start gap-3">
            {{-- Upload icon --}}
            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
            </svg>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-teal-800">Upload PDF → auto-convert to HTML</p>
                <p class="text-xs text-teal-600 mt-0.5">
                    The PDF will be converted and stored as editable HTML.<br>
                    Leave blank to write/paste HTML manually below.
                </p>
                <input type="file"
                       name="pdf_file"
                       accept="application/pdf"
                       class="mt-3 block w-full text-sm text-gray-600
                              file:mr-3 file:rounded file:border-0
                              file:bg-teal-700 file:px-3 file:py-1.5
                              file:text-sm file:font-medium file:text-white
                              hover:file:bg-teal-800 cursor-pointer">
                @error('pdf_file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                @if($template->pdf_path ?? false)
                    <p class="mt-2 text-xs text-gray-500 flex items-center gap-1">
                        <svg class="h-3.5 w-3.5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                        </svg>
                        Current:
                        <a href="{{ Storage::url($template->pdf_path) }}"
                           target="_blank"
                           class="text-teal-700 underline hover:text-teal-900 truncate">
                            {{ basename($template->pdf_path) }}
                        </a>
                    </p>
                @endif
            </div>
        </div>
    </div>

    {{-- ── HTML Editor ─────────────────────────────────────────────────────── --}}
    <div>
        <div class="flex items-center justify-between mb-1">
            <label class="text-sm font-medium text-gray-700">Template HTML</label>

            @if($template->exists && $template->html)
                <div class="flex items-center gap-3">
                    {{-- Toggle inline preview --}}
                    <button type="button"
                            id="toggle-preview-btn"
                            onclick="togglePreview()"
                            class="text-xs text-teal-700 hover:text-teal-900 underline underline-offset-2">
                        Show Preview
                    </button>

                    {{-- Download PDF --}}
                    <a href="{{ route('admin.templates.download', $template) }}"
                       class="inline-flex items-center gap-1 text-xs font-medium text-white bg-teal-700 hover:bg-teal-800 px-2.5 py-1 rounded">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download PDF
                    </a>

                    {{-- Open preview in new tab --}}
                    <a href="{{ route('admin.templates.preview', $template) }}"
                       target="_blank"
                       class="text-xs text-gray-500 hover:text-gray-700 underline underline-offset-2">
                        Full preview ↗
                    </a>
                </div>
            @endif
        </div>

        {{-- Inline iframe (hidden by default, toggled by JS) --}}
        @if($template->exists && $template->html)
            <div id="preview-wrapper" class="hidden mb-3">
                <iframe id="html-preview-frame"
                        src="{{ route('admin.templates.preview', $template) }}"
                        class="w-full rounded-md border border-gray-300 bg-white"
                        style="height: 500px; resize: vertical; overflow: auto;">
                </iframe>
            </div>
        @endif

        <textarea name="html"
                  rows="16"
                  placeholder="Upload a PDF above — HTML will appear here automatically — or paste/write HTML directly."
                  class="w-full rounded-md border-gray-300 font-mono text-xs leading-relaxed shadow-sm focus:border-teal-500 focus:ring-teal-500">{{ old('html', $template->html) }}</textarea>
        @error('html')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

       


    {{-- ── Active Toggle & Features ───────────────────────────────────────── --}}
    <div class="flex flex-col gap-3">
        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
            <input type="checkbox"
                   name="is_active"
                   value="1"
                   @checked(old('is_active', $template->is_active ?? true))
                   class="rounded border-gray-300 text-teal-700 shadow-sm focus:ring-teal-500">
            Active (visible to users)
        </label>

        <label class="flex items-center gap-2 text-sm text-gray-700 cursor-pointer select-none">
            <input type="checkbox"
                   name="has_image"
                   value="1"
                   @checked(old('has_image', $template->has_image ?? false))
                   class="rounded border-gray-300 text-teal-700 shadow-sm focus:ring-teal-500">
            Supports Profile Image (Adds upload field in editor)
        </label>
    </div>

    {{-- ── Submit ──────────────────────────────────────────────────────────── --}}
    <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
        <button type="submit"
                class="rounded-md bg-teal-700 px-6 py-2.5 text-sm font-semibold text-white
                       hover:bg-teal-800 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:ring-offset-2">
            Save Template
        </button>
        <a href="{{ route('admin.templates.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700">
            Cancel
        </a>
    </div>
</div>

{{-- Toggle preview script --}}
<script>
function togglePreview() {
    const wrapper = document.getElementById('preview-wrapper');
    const btn     = document.getElementById('toggle-preview-btn');
    if (!wrapper) return;
    const hidden = wrapper.classList.toggle('hidden');
    btn.textContent = hidden ? 'Show Preview' : 'Hide Preview';
}
</script>