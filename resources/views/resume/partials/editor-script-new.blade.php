@push('scripts')
<script>
(() => {
    const app = document.getElementById('create-cv-app');
    if (!app) return;
    const state = Object.assign({name:'', email:'', mobile:'', location:'', portfolio:'', link:'', social_links:[], contact:'', address:'', summary:'', skills:[], experience:[{company:'', role:'', points:['']}], education:[''], projects:[]}, JSON.parse(app.dataset.initial || '{}'));
    const templates = JSON.parse(app.dataset.templates || '{}');
    let source = 'manual';
    let selectedTemplateId = app.dataset.selectedTemplate || null;
    const $ = (id) => document.getElementById(id);
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    const list = (v) => String(v).split(',').map(x => x.trim()).filter(Boolean);

    function syncLegacyContact() {
        state.contact = [state.email, state.mobile, state.portfolio || state.link].filter(Boolean).join(' | ');
        state.address = state.location || '';
    }

    function renderSkills() {
        return (state.skills || []).map(skill => `<span class="tpl-badge">${esc(skill)}</span>`).join('');
    }

    function renderExperience() {
        return (state.experience || []).map(e => {
            if (!e.company && !e.role) return '';
            const points = (e.points || []).filter(Boolean).map(p => `<li>${esc(p)}</li>`).join('');
            return `<div class="tpl-role"><div class="tpl-role-head"><strong>${esc(e.role || '')}</strong><span>${esc(e.period || '')}</span></div><p>${esc(e.company || '')}</p><ul>${points}</ul></div>`;
        }).join('');
    }

    function renderEducation() {
        return '<ul>' + (state.education || []).filter(Boolean).map(e => `<li>${esc(e)}</li>`).join('') + '</ul>';
    }

    function renderProjects() {
        return '<ul>' + (state.projects || []).filter(Boolean).map(p => `<li>${esc(p)}</li>`).join('') + '</ul>';
    }

    function renderTemplatePreview() {
        if (!selectedTemplateId || !templates[selectedTemplateId]) {
            renderBasicPreview();
            return;
        }

        const template = templates[selectedTemplateId];
        let html = template.html || '';

        // Replace simple string variables (escaped)
        const simpleData = {
            name: esc(state.name || 'Your Name'),
            email: esc(state.email || ''),
            mobile: esc(state.mobile || ''),
            location: esc(state.location || ''),
            summary: esc(state.summary || ''),
            social_links: esc([state.portfolio || state.link].filter(Boolean).join(' | ')),
        };

        for (const [key, value] of Object.entries(simpleData)) {
            html = html.replace(new RegExp('{{' + key + '}}', 'g'), value);
        }

        // Replace complex data (already formatted as HTML)
         html = html.replace(/\{\{skills\}\}/g, renderSkills());
         html = html.replace(/\{\{experience\}\}/g, renderExperience());
         html = html.replace(/\{\{education\}\}/g, renderEducation());
         html = html.replace(/\{\{projects\}\}/g, renderProjects());

        $('cv-preview').innerHTML = html;
    }

    function renderBasicPreview() {
        syncLegacyContact();
        $('cv-preview').innerHTML = `<header class="border-b-2 border-gray-950 pb-4"><h1 class="text-3xl font-bold uppercase text-gray-950">${esc(state.name || 'Your Name')}</h1><p class="mt-2 text-sm text-gray-600">${esc([state.email, state.mobile, state.location].filter(Boolean).join(' | '))}</p><p class="text-sm text-gray-600">${esc([state.portfolio || state.link].filter(Boolean).join(' | '))}</p></header><section class="mt-6"><h2 class="text-xs font-bold uppercase text-teal-700">Summary</h2><p class="mt-2 text-sm leading-6 text-gray-700">${esc(state.summary)}</p></section><section class="mt-6"><h2 class="text-xs font-bold uppercase text-teal-700">Skills</h2><p class="mt-2 text-sm text-gray-700">${esc((state.skills || []).join(', '))}</p></section><section class="mt-6"><h2 class="text-xs font-bold uppercase text-teal-700">Experience</h2>${(state.experience || []).map(e => `<div class="mt-3"><h3 class="font-bold text-gray-950">${esc(e.role)}</h3><p class="text-sm text-gray-500">${esc(e.company)}</p><ul class="mt-2 list-disc pl-5 text-sm text-gray-700">${(e.points || []).map(p => `<li>${esc(p)}</li>`).join('')}</ul></div>`).join('')}</section><section class="mt-6"><h2 class="text-xs font-bold uppercase text-teal-700">Education</h2><ul class="mt-2 list-disc pl-5 text-sm text-gray-700">${(state.education || []).filter(Boolean).map(e => `<li>${esc(e)}</li>`).join('')}</ul></section>`;
    }

    function renderEditor() {
        $('cv-name').value = state.name || '';
        $('cv-email').value = state.email || '';
        $('cv-mobile').value = state.mobile || '';
        $('cv-location').value = state.location || '';
        if ($('cv-portfolio')) $('cv-portfolio').value = state.portfolio || state.link || '';
        $('cv-summary').value = state.summary || '';
        $('cv-skills').value = (state.skills || []).join(', ');
        $('exp-editor').innerHTML = (state.experience || []).map((e,i) => `<div class="border border-gray-200 rounded-lg p-3" data-exp="${i}"><div class="grid grid-cols-1 md:grid-cols-2 gap-3"><input class="rounded-md border-gray-300 text-sm" data-k="company" value="${esc(e.company)}" placeholder="Company"><input class="rounded-md border-gray-300 text-sm" data-k="role" value="${esc(e.role)}" placeholder="Role"></div><input class="mt-3 rounded-md border-gray-300 text-sm w-full" data-k="period" value="${esc(e.period || '')}" placeholder="Period (e.g., 2022 - Present)"><textarea class="mt-3 w-full rounded-md border-gray-300 text-sm" data-k="points" rows="3" placeholder="One bullet per line">${esc((e.points || []).join('\n'))}</textarea><button type="button" data-remove-exp class="mt-2 text-sm font-semibold text-red-600">Remove</button></div>`).join('');
        $('edu-editor').innerHTML = (state.education || []).map((e,i) => `<div class="flex gap-2" data-edu="${i}"><input class="w-full rounded-md border-gray-300 text-sm" value="${esc(e)}" placeholder="Degree, institution, year"><button type="button" data-remove-edu class="px-3 rounded-md border border-gray-300">-</button></div>`).join('');
    }

    document.querySelectorAll('.cv-field').forEach(input => input.addEventListener('input', e => { const f=e.target.dataset.field; state[f] = ['skills'].includes(f) ? list(e.target.value) : e.target.value; if (f === 'portfolio') state.link = e.target.value; renderTemplatePreview(); }));
    $('exp-editor').addEventListener('input', e => { const box=e.target.closest('[data-exp]'); if(!box) return; const i=+box.dataset.exp; const k=e.target.dataset.k; state.experience[i][k] = k === 'points' ? e.target.value.split('\n').map(x=>x.trim()).filter(Boolean) : e.target.value; renderTemplatePreview(); });
    $('edu-editor').addEventListener('input', e => { const row=e.target.closest('[data-edu]'); if(!row) return; state.education[+row.dataset.edu] = e.target.value; renderTemplatePreview(); });

    $('template-id').addEventListener('change', e => {
        selectedTemplateId = e.target.value || null;
        renderTemplatePreview();
    });

    app.addEventListener('click', e => {
        if(e.target.matches('.source-btn')) {
            source=e.target.dataset.source;
            document.querySelectorAll('.source-btn').forEach(b=>b.classList.remove('bg-teal-700','text-white'));
            e.target.classList.add('bg-teal-700','text-white');
            $('existing-resume-panel')?.classList.toggle('hidden', source !== 'upload');
        }
        if(e.target.id==='add-exp'){ state.experience.push({company:'',role:'',period:'',points:['']}); renderEditor(); renderTemplatePreview(); }
        if(e.target.id==='add-edu'){ state.education.push(''); renderEditor(); renderTemplatePreview(); }
        if(e.target.dataset.removeExp!==undefined){ state.experience.splice(+e.target.closest('[data-exp]').dataset.exp,1); renderEditor(); renderTemplatePreview(); }
        if(e.target.dataset.removeEdu!==undefined){ state.education.splice(+e.target.closest('[data-edu]').dataset.edu,1); renderEditor(); renderTemplatePreview(); }
    });
    $('save-cv').addEventListener('click', async () => { syncLegacyContact(); $('cv-status').textContent='Saving...'; const url=app.dataset.updateUrl || app.dataset.storeUrl; const method=app.dataset.updateUrl ? 'patch' : 'post'; const templateId = $('template-id')?.value || null; const payload=app.dataset.updateUrl ? {resume:state, template_id:templateId} : {source, template_id:templateId, resume:state}; const res=await axios[method](url,payload); if(res.data.redirect) location.href=res.data.redirect; else $('cv-status').textContent='Saved.'; });
    renderEditor(); renderTemplatePreview();
})();
</script>
@endpush
