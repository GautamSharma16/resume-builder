@extends('layouts.app')

@section('content')
<div id="cover-letter-app" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" data-generate-url="{{ route('cover-letter.generate') }}">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-950">Cover Letter</h1>
        <p class="text-gray-600 mt-2">Generate AI content, switch templates, edit fields, and preview the final formatted letter live.</p>
    </div>
    <div class="grid grid-cols-1 xl:grid-cols-[430px_1fr] gap-6">
        <div class="flex flex-col space-y-5">
            <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm space-y-4 order-2">
                <select id="cl-resume" class="w-full rounded-md border-gray-300 text-sm">
                    <option value="">No saved resume / enter manually</option>
                    @foreach($resumes as $resume)
                        <option value="{{ $resume->id }}">{{ $resume->title }}</option>
                    @endforeach
                </select>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <input id="cl-name" class="rounded-md border-gray-300 text-sm w-full" placeholder="Your name" value="{{ $prefill['name'] }}">
                    <input id="cl-email" class="rounded-md border-gray-300 text-sm w-full" placeholder="Email" value="{{ $prefill['email'] }}">
                    <input id="cl-mobile" class="rounded-md border-gray-300 text-sm w-full" placeholder="Mobile" value="{{ $prefill['mobile'] }}">
                    <input id="cl-location" class="rounded-md border-gray-300 text-sm w-full" placeholder="Location" value="{{ $prefill['location'] }}">
                </div>
                <input id="cl-company" class="rounded-md border-gray-300 text-sm w-full" placeholder="Company name" value="{{ $prefill['company'] }}">
                <input id="cl-role" class="rounded-md border-gray-300 text-sm w-full" placeholder="Job role" value="{{ $prefill['job_role'] }}">
                <input id="cl-skills" class="rounded-md border-gray-300 text-sm w-full" placeholder="Skills, comma separated" value="{{ $prefill['skills'] }}">
                <textarea id="cl-description" rows="4" class="rounded-md border-gray-300 text-sm w-full" placeholder="Job description"></textarea>
                <button id="generate-letter" type="button" class="w-full rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white">Generate with AI</button>
                <textarea id="cl-body" rows="11" class="rounded-md border-gray-300 text-sm w-full" placeholder="Generated letter body">{{ $prefill['body'] }}</textarea>
                <div class="flex flex-wrap gap-3">
                    <button id="save-letter" type="button" class="rounded-md border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-800">Save Edits</button>
                    <a id="download-letter" href="#" class="hidden rounded-md bg-gray-950 px-5 py-3 text-sm font-semibold text-white">Download PDF</a>
                </div>
                <p id="cl-status" class="text-sm text-gray-600"></p>
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm order-1">
                <h2 class="text-sm font-bold text-gray-950 mb-4">Choose template</h2>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($templates as $template)
                        <button type="button" class="cl-template-btn rounded-lg border border-gray-200 bg-slate-50 p-2 text-left hover:shadow-md transition" data-template-id="{{ $template->id }}">
                            <div class="h-40 overflow-hidden bg-white shadow ring-1 ring-gray-200">
                                <div class="resume-sheet-preview pointer-events-none" style="transform: scale(0.45); transform-origin: top left;">
                                    {!! $renderedTemplates[$template->id] !!}
                                </div>
                            </div>
                            <p class="mt-2 text-xs font-semibold text-gray-900">{{ $template->name }}</p>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="bg-slate-100 rounded-lg border border-gray-200 p-4 sm:p-6 overflow-y-auto overflow-x-hidden">
            <article id="cl-preview" class="mx-auto w-[794px] bg-white shadow-xl"></article>
        </div>
    </div>
</div>

@push('scripts')
<script>
(() => {
    const app = document.getElementById('cover-letter-app');
    const templates = @json($templates->mapWithKeys(fn($template) => [$template->id => $template->html]));
    const firstTemplate = Object.keys(templates)[0] || null;
    const openToken = '{' + '{';
    const closeToken = '}' + '}';
    const state = {id:null, template_id:null, name:'', email:'', mobile:'', location:'', company:'', company_name:'', job_role:'', skills:'', body:''};
    const $ = id => document.getElementById(id);
    const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    const nl2br = v => esc(v).replace(/\n/g, '<br>');
    const setEnabled = (enabled) => {
        ['cl-name','cl-email','cl-mobile','cl-location','cl-company','cl-role','cl-skills','cl-body','generate-letter','save-letter'].forEach((id) => {
            const el = $(id);
            if (!el) return;
            el.disabled = !enabled;
            el.classList.toggle('opacity-60', !enabled);
            el.classList.toggle('cursor-not-allowed', !enabled);
        });
    };
    setEnabled(false);
    const sync = () => {
        if (!state.template_id) return;
        state.name=$('cl-name').value; state.email=$('cl-email').value; state.mobile=$('cl-mobile').value; state.location=$('cl-location').value;
        state.company=$('cl-company').value; state.company_name=state.company; state.job_role=$('cl-role').value; state.skills=$('cl-skills').value; state.body=$('cl-body').value;
        render();
    };
    const replaceToken = (html, key, value) => {
        const curlyToken = openToken + key + closeToken;
        const squareToken = '[[' + key + ']]';
        return html.split(curlyToken).join(value).split(squareToken).join(value);
    };
    const render = () => {
        if (!state.template_id || !templates[state.template_id]) {
            $('cl-preview').innerHTML = `<div class="p-10 text-gray-500">Select a cover template to start editing.</div>`;
            return;
        }
        const html = templates[state.template_id];
        const values = {...state, body:nl2br(state.body), company_name:esc(state.company), company:esc(state.company), name:esc(state.name), email:esc(state.email), mobile:esc(state.mobile), location:esc(state.location), job_role:esc(state.job_role), skills:esc(state.skills)};
        let output = html;
        Object.entries(values).forEach(([key, value]) => {
            output = replaceToken(output, key, value);
        });
        $('cl-preview').innerHTML = output;
    };
    ['cl-name','cl-email','cl-mobile','cl-location','cl-company','cl-role','cl-skills','cl-body'].forEach(id => $(id).addEventListener('input', sync));
    document.querySelectorAll('.cl-template-btn').forEach(button => button.addEventListener('click', () => {
        state.template_id = button.dataset.templateId;
        document.querySelectorAll('.cl-template-btn').forEach(item => item.classList.remove('ring-2','ring-teal-600'));
        button.classList.add('ring-2','ring-teal-600');
        setEnabled(true);
        render();
    }));
    $('generate-letter').addEventListener('click', async () => {
        if (!state.template_id) {
            $('cl-status').textContent = 'Select a template first.';
            return;
        }
        sync();
        $('cl-status').textContent='Generating...';
        const {data} = await axios.post(app.dataset.generateUrl, {template_id:state.template_id, resume_id:$('cl-resume').value || null, name:state.name, email:state.email, mobile:state.mobile, location:state.location, company_name:state.company, company:state.company, job_role:state.job_role, skills:state.skills, job_description:$('cl-description').value});
        state.id = data.cover_letter_id;
        Object.assign(state, data.letter);
        $('cl-name').value=state.name; $('cl-email').value=state.email || ''; $('cl-mobile').value=state.mobile || ''; $('cl-location').value=state.location || ''; $('cl-company').value=state.company; $('cl-role').value=state.job_role; $('cl-skills').value=state.skills || ''; $('cl-body').value=state.body;
        $('download-letter').href = `/cover-letter/${state.id}/download/pdf`;
        $('download-letter').classList.remove('hidden');
        render(); $('cl-status').textContent='Generated. Edit the form to update preview instantly.';
    });
    $('save-letter').addEventListener('click', async () => {
        sync();
        if (!state.id) { $('cl-status').textContent='Generate the letter first, then save edits.'; return; }
        await axios.patch(`/cover-letter/${state.id}`, {letter: state});
        $('cl-status').textContent='Saved.';
    });
    $('cl-status').textContent = '';
    render();
})();
</script>
@endpush
@endsection
