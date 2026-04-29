@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-12 px-4 sm:px-6 lg:px-8" x-data="{ tab: 'ats' }">
    <div class="max-w-7xl mx-auto">
        <!-- HEADER -->
        <div class="text-center mb-12">
            <h1 class="text-4xl font-bold text-gray-950 mb-3">Resume Templates</h1>
            <p class="text-lg text-gray-600">Choose a professional template and start building your resume</p>
        </div>

        <!-- CATEGORY TABS -->
        <div class="flex flex-wrap gap-2 justify-center mb-12">
            <button @click="tab='ats'"
                :class="tab==='ats' ? 'bg-teal-700 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:border-gray-400'"
                class="px-6 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                ATS
            </button>
            <button @click="tab='fresher'"
                :class="tab==='fresher' ? 'bg-teal-700 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:border-gray-400'"
                class="px-6 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                Fresher
            </button>
            <button @click="tab='experienced'"
                :class="tab==='experienced' ? 'bg-teal-700 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:border-gray-400'"
                class="px-6 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                Experienced
            </button>
            <button @click="tab='word'"
                :class="tab==='word' ? 'bg-teal-700 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:border-gray-400'"
                class="px-6 py-2 rounded-lg text-sm font-semibold transition-all duration-200">
                Word
            </button>
        </div>

        <!-- TEMPLATES GRID -->
        @php
            $categories = ['ats', 'fresher', 'experienced', 'word'];
            $labels = [
                'ats' => 'ATS Optimized',
                'fresher' => 'For Freshers',
                'experienced' => 'For Experienced',
                'word' => 'MS Word Style',
            ];
        @endphp

        @foreach($categories as $category)
            <div x-show="tab === '{{ $category }}'" x-transition.duration.300ms class="w-full">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 auto-rows-max">
                    @foreach($templates->where('type', 'resume')->where('category', $category) as $template)
                        <div class="group bg-white rounded-xl shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden flex flex-col h-full border border-gray-200">

                            <!-- PREVIEW CONTAINER -->
                            <div class="relative w-full bg-gradient-to-b from-gray-50 to-gray-100 flex items-center justify-center" style="height: 520px;">
                                <button type="button"
                                    class="template-preview-btn w-full h-full absolute inset-0 cursor-pointer bg-transparent border-0 p-0 m-0 group-hover:bg-black/5 transition-colors"
                                    data-template-id="{{ $template->id }}"
                                    data-template-name="{{ $template->name }}">
                                </button>

                                <div class="resume-sheet-preview origin-top-left transition-transform duration-300 pointer-events-none" style="transform: scale(0.38); transform-origin: top left;">
                                    <div class="bg-white shadow-xl" style="width: 794px;">
                                        {!! $rendered[$template->id] !!}
                                    </div>
                                </div>
                            </div>

                            <!-- CONTENT -->
                            <div class="p-5 flex flex-col flex-grow">
                                <h3 class="text-sm font-bold text-gray-900 mb-2">
                                    {{ $template->name }}
                                </h3>

                                <p class="text-xs text-gray-500 uppercase tracking-wide mb-4 flex-grow">
                                    {{ $labels[$template->category] ?? '' }}
                                </p>

                                <div class="flex gap-2">
                                    <a href="{{ route('resume.create', ['template_id' => $template->id]) }}"
                                        class="flex-1 bg-teal-700 hover:bg-teal-800 text-white px-4 py-2 rounded-lg text-xs font-semibold text-center transition-colors">
                                        Apply
                                    </a>

                                    <button type="button"
                                        class="template-preview-btn flex-1 border border-gray-300 hover:border-teal-700 text-gray-700 px-4 py-2 rounded-lg text-xs font-semibold transition-colors"
                                        data-template-id="{{ $template->id }}"
                                        data-template-name="{{ $template->name }}">
                                        Preview
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>

<!-- PREVIEW MODAL -->
<div id="template-modal" class="fixed inset-0 z-50 hidden bg-black/70 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col">

        <div class="flex justify-between items-center p-6 border-b border-gray-200">
            <h2 id="template-modal-title" class="font-bold text-xl text-gray-900">Template Preview</h2>
            <button id="template-modal-close" class="text-gray-500 hover:text-gray-700 text-2xl w-8 h-8 flex items-center justify-center">
                ✕
            </button>
        </div>

        <div class="overflow-auto flex-1 bg-gray-100 p-6">
            <div id="template-modal-body" class="bg-white shadow-lg mx-auto" style="width: 794px;"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rendered = @json($rendered);
        const modal = document.getElementById('template-modal');
        const body = document.getElementById('template-modal-body');
        const title = document.getElementById('template-modal-title');
        const closeBtn = document.getElementById('template-modal-close');

        document.querySelectorAll('.template-preview-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const templateId = btn.dataset.templateId;
                const templateName = btn.dataset.templateName;

                if (rendered[templateId]) {
                    title.textContent = templateName + ' - Template Preview';
                    body.innerHTML = rendered[templateId];
                    modal.classList.remove('hidden');
                }
            });
        });

        closeBtn.addEventListener('click', () => modal.classList.add('hidden'));
        modal.addEventListener('click', e => {
            if (e.target === modal) modal.classList.add('hidden');
        });

        // Close on Escape key
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                modal.classList.add('hidden');
            }
        });
    });
</script>
@endpush

@endsection
