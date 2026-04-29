@extends('layouts.app')

@section('content')
<div id="cover-letter-app" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" data-generate-url="{{ route('cover-letter.generate') }}">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-950">Cover Letter</h1>
        <p class="text-gray-600 mt-2">Generate with AI, edit structured fields, and preview instantly.</p>
    </div>
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm space-y-4">
            <select id="cl-resume" class="w-full rounded-md border-gray-300 text-sm">
                <option value="">No saved resume / enter manually</option>
                @foreach($resumes as $resume)
                    <option value="{{ $resume->id }}">{{ $resume->title }}</option>
                @endforeach
            </select>
            <input id="cl-name" class="rounded-md border-gray-300 text-sm w-full" placeholder="Your name">
            <input id="cl-company" class="rounded-md border-gray-300 text-sm w-full" placeholder="Company">
            <input id="cl-role" class="rounded-md border-gray-300 text-sm w-full" placeholder="Job role">
            <textarea id="cl-description" rows="4" class="rounded-md border-gray-300 text-sm w-full" placeholder="Job description"></textarea>
            <button id="generate-letter" type="button" class="rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Generate Letter</button>
            <textarea id="cl-body" rows="14" class="rounded-md border-gray-300 text-sm w-full" placeholder="Generated letter body"></textarea>
            <p id="cl-status" class="text-sm text-gray-600"></p>
        </div>
        <article id="cl-preview" class="bg-white border border-gray-200 rounded-lg p-8 shadow-sm min-h-[720px]"></article>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const app = document.getElementById('cover-letter-app');
    const state = {name:'', company:'', job_role:'', body:''};
    const $ = id => document.getElementById(id);
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    const render = () => $('cl-preview').innerHTML = `<h1 class="text-3xl font-bold text-gray-950">${esc(state.name || 'Your Name')}</h1><p class="mt-2 text-sm text-gray-500">${esc(state.job_role)} ${state.company ? '· '+esc(state.company) : ''}</p><div class="mt-8 whitespace-pre-line text-sm leading-7 text-gray-700">${esc(state.body || 'Your cover letter preview appears here.')}</div>`;
    ['cl-name','cl-company','cl-role','cl-body'].forEach(id => $(id).addEventListener('input', () => { state.name=$('cl-name').value; state.company=$('cl-company').value; state.job_role=$('cl-role').value; state.body=$('cl-body').value; render(); }));
    $('generate-letter').addEventListener('click', async () => {
        $('cl-status').textContent='Generating...';
        const {data} = await axios.post(app.dataset.generateUrl, {resume_id:$('cl-resume').value || null, name:$('cl-name').value, company:$('cl-company').value, job_role:$('cl-role').value, job_description:$('cl-description').value});
        Object.assign(state, data.letter); $('cl-name').value=state.name; $('cl-company').value=state.company; $('cl-role').value=state.job_role; $('cl-body').value=state.body; render(); $('cl-status').textContent='Generated.';
    });
    render();
})();
</script>
@endpush
@endsection
