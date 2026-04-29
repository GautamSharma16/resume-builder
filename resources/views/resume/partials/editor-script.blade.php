@push('scripts')
<script>
(() => {
    const app = document.getElementById('create-cv-app');
    if (!app) return;

    const openToken = '{' + '{';
    const closeToken = '}' + '}';
    const defaults = {
        name: '',
        email: '',
        mobile: '',
        location: '',
        social_links: [],
        contact: '',
        address: '',
        summary: '',
        skills: [],
        experience: [],
        education: [],
        projects: [],
    };
    const state = Object.assign({}, defaults, JSON.parse(app.dataset.initial || '{}'));
    const templates = JSON.parse(app.dataset.templates || '{}');
    let source = 'manual';
    let selectedTemplateId = app.dataset.selectedTemplate || '';
    const $ = (id) => document.getElementById(id);
    const cvPreviewEl = $('cv-preview');
    const projectEditorEl = $('project-editor');
    const expEditorEl = $('exp-editor');
    const eduEditorEl = $('edu-editor');
    const templateIdEl = $('template-id');
    const saveBtnEl = $('save-cv');
    const statusEl = $('cv-status');

    if (!cvPreviewEl || !templateIdEl || !expEditorEl || !eduEditorEl || !saveBtnEl || !statusEl) {
        return;
    }

    // Default to the first available template if none is selected (matches UI expectation).
    const templateKeys = Object.keys(templates);
    if (!selectedTemplateId && templateKeys.length) {
        selectedTemplateId = templateKeys[0];
        templateIdEl.value = selectedTemplateId;
    }

    const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[char]));
    const list = (value) => String(value ?? '').split(',').map((item) => item.trim()).filter(Boolean);
    const ensureArray = (value) => Array.isArray(value) ? value : [];

    state.skills = ensureArray(state.skills);
    state.social_links = ensureArray(state.social_links);
    state.education = ensureArray(state.education);
    state.projects = ensureArray(state.projects);
    state.experience = ensureArray(state.experience).map((item) => ({
        company: item?.company || '',
        role: item?.role || '',
        period: item?.period || '',
        points: ensureArray(item?.points).map(String),
    }));

    if (!state.experience.length) {
        state.experience.push({ company: '', role: '', period: '', points: [''] });
    }
    if (!state.education.length) {
        state.education.push('');
    }
    if (!state.projects.length) {
        state.projects.push('');
    }

    const htmlWrap = (body) => `
        <div class="p-2">
            <div class="resume-sheet-preview shadow-md" style="transform: scale(0.5); transform-origin: top left; width: 794px;">
                ${body}
            </div>
        </div>
    `;

    function syncLegacyContact() {
        state.contact = [state.email, state.mobile, ...state.social_links].filter(Boolean).join(' | ');
        state.address = state.location || '';
    }

    function replaceToken(html, key, value) {
        const curlyToken = openToken + key + closeToken;
        const squareToken = '[[' + key + ']]';
        return html.split(curlyToken).join(value).split(squareToken).join(value);
    }

    function renderSkills() {
        return state.skills.map((skill) => `<span class="tpl-badge">${esc(skill)}</span>`).join('');
    }

    function renderExperience() {
        return state.experience.map((item) => {
            if (!item.company && !item.role && !item.period && !item.points.some(Boolean)) {
                return '';
            }
            const points = item.points.filter(Boolean).map((point) => `<li>${esc(point)}</li>`).join('');
            return `<div class="tpl-role"><div class="tpl-role-head"><strong>${esc(item.role)}</strong><span>${esc(item.period)}</span></div><p>${esc(item.company)}</p><ul>${points}</ul></div>`;
        }).join('');
    }

    function renderList(values) {
        const items = values.filter(Boolean).map((item) => `<li>${esc(item)}</li>`).join('');
        return `<ul>${items}</ul>`;
    }

    function renderBasicPreview() {
        const content = `<div class="tpl-resume"><header><h1>${esc(state.name || 'Your Name')}</h1><p>${esc([state.email, state.mobile, state.location].filter(Boolean).join(' | '))}</p><p>${esc(state.social_links.join(' | '))}</p></header><h2>Summary</h2><p>${esc(state.summary)}</p><h2>Skills</h2><p>${esc(state.skills.join(', '))}</p><h2>Experience</h2>${renderExperience()}<h2>Education</h2>${renderList(state.education)}<h2>Projects</h2>${renderList(state.projects)}</div>`;
        cvPreviewEl.innerHTML = htmlWrap(content);
    }

    function renderTemplatePreview() {
        syncLegacyContact();
        if (!selectedTemplateId || !templates[selectedTemplateId]) {
            renderBasicPreview();
            return;
        }

        const template = templates[selectedTemplateId];
        let output = String(template.html || '');
        const hasProjectsToken = output.includes('{{projects}}') || output.includes('[[projects]]');
        const values = {
            name: esc(state.name || 'Your Name'),
            email: esc(state.email),
            mobile: esc(state.mobile),
            location: esc(state.location),
            contact: esc(state.contact),
            address: esc(state.address),
            summary: esc(state.summary),
            social_links: esc(state.social_links.join(' | ')),
            skills: renderSkills(),
            experience: renderExperience(),
            education: renderList(state.education),
            projects: renderList(state.projects),
        };

        Object.entries(values).forEach(([key, value]) => {
            output = replaceToken(output, key, value);
        });

        // Ensure projects are visible even if the selected template doesn't have a {{projects}} placeholder.
        if (!hasProjectsToken && (state.projects || []).length) {
            const projectsSection = `<h2>Projects</h2>${renderList(state.projects)}`;
            const lastDiv = output.lastIndexOf('</div>');
            if (lastDiv !== -1) {
                output = output.slice(0, lastDiv) + projectsSection + output.slice(lastDiv);
            } else {
                output += projectsSection;
            }
        }

        cvPreviewEl.innerHTML = htmlWrap(output);
    }

    function renderEditor() {
        $('cv-name').value = state.name;
        $('cv-email').value = state.email;
        $('cv-mobile').value = state.mobile;
        $('cv-location').value = state.location;
        $('cv-social').value = state.social_links.join(', ');
        $('cv-summary').value = state.summary;
        $('cv-skills').value = state.skills.join(', ');

        expEditorEl.innerHTML = state.experience.map((item, index) => `
            <div class="rounded-lg border border-gray-200 p-3" data-exp="${index}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input class="rounded-md border-gray-300 text-sm" data-k="company" value="${esc(item.company)}" placeholder="Company">
                    <input class="rounded-md border-gray-300 text-sm" data-k="role" value="${esc(item.role)}" placeholder="Role">
                </div>
                <input class="mt-3 w-full rounded-md border-gray-300 text-sm" data-k="period" value="${esc(item.period)}" placeholder="Period (e.g., 2022 - Present)">
                <textarea class="mt-3 w-full rounded-md border-gray-300 text-sm" data-k="points" rows="3" placeholder="One bullet per line">${esc(item.points.join('\n'))}</textarea>
                <button type="button" data-remove-exp class="mt-2 text-sm font-semibold text-red-600">Remove</button>
            </div>
        `).join('');

        eduEditorEl.innerHTML = state.education.map((item, index) => `
            <div class="flex gap-2" data-edu="${index}">
                <input class="w-full rounded-md border-gray-300 text-sm" value="${esc(item)}" placeholder="Degree, institution, year">
                <button type="button" data-remove-edu class="rounded-md border border-gray-300 px-3">-</button>
            </div>
        `).join('');

        if (projectEditorEl) {
            projectEditorEl.innerHTML = state.projects.map((item, index) => `
                <div class="flex gap-2" data-project="${index}">
                    <input class="w-full rounded-md border-gray-300 text-sm" value="${esc(item)}" placeholder="Project title and impact">
                    <button type="button" data-remove-project class="rounded-md border border-gray-300 px-3">-</button>
                </div>
            `).join('');
        }
    }

    document.querySelectorAll('.cv-field').forEach((input) => {
        input.addEventListener('input', (event) => {
            const field = event.target.dataset.field;
            state[field] = ['skills', 'social_links'].includes(field) ? list(event.target.value) : event.target.value;
            renderTemplatePreview();
        });
    });

    $('exp-editor').addEventListener('input', (event) => {
        const block = event.target.closest('[data-exp]');
        if (!block) {
            return;
        }
        const index = Number(block.dataset.exp);
        const key = event.target.dataset.k;
        state.experience[index][key] = key === 'points'
            ? event.target.value.split('\n').map((item) => item.trim()).filter(Boolean)
            : event.target.value;
        renderTemplatePreview();
    });

    $('edu-editor').addEventListener('input', (event) => {
        const row = event.target.closest('[data-edu]');
        if (!row) {
            return;
        }
        state.education[Number(row.dataset.edu)] = event.target.value;
        renderTemplatePreview();
    });

    if (projectEditorEl) {
        projectEditorEl.addEventListener('input', (event) => {
            const row = event.target.closest('[data-project]');
            if (!row) return;
            state.projects[Number(row.dataset.project)] = event.target.value;
            renderTemplatePreview();
        });
    }

    templateIdEl.addEventListener('change', (event) => {
        selectedTemplateId = event.target.value || '';
        renderTemplatePreview();
    });

    app.addEventListener('click', (event) => {
        const clicked = event.target.closest('button');
        if (!clicked) return;

        if (clicked.matches('.source-btn')) {
            source = clicked.dataset.source;
            if (source === 'upload') {
                // Redirect to Enhance CV for AI parsing flow.
                window.location.href = "{{ route('enhance-cv') }}";
                return;
            }
            document.querySelectorAll('.source-btn').forEach((button) => button.classList.remove('bg-teal-700', 'text-white'));
            clicked.classList.add('bg-teal-700', 'text-white');
            $('existing-resume-panel')?.classList.add('hidden');
        }

        if (clicked.id === 'add-exp') {
            state.experience.push({ company: '', role: '', period: '', points: [''] });
            renderEditor();
            renderTemplatePreview();
        }

        if (clicked.id === 'add-edu') {
            state.education.push('');
            renderEditor();
            renderTemplatePreview();
        }

        if (clicked.id === 'add-project') {
            state.projects.push('');
            renderEditor();
            renderTemplatePreview();
        }

        if (clicked.dataset.removeExp !== undefined) {
            state.experience.splice(Number(clicked.closest('[data-exp]').dataset.exp), 1);
            if (!state.experience.length) {
                state.experience.push({ company: '', role: '', period: '', points: [''] });
            }
            renderEditor();
            renderTemplatePreview();
        }

        if (clicked.dataset.removeEdu !== undefined) {
            state.education.splice(Number(clicked.closest('[data-edu]').dataset.edu), 1);
            if (!state.education.length) {
                state.education.push('');
            }
            renderEditor();
            renderTemplatePreview();
        }

        if (clicked.dataset.removeProject !== undefined) {
            state.projects.splice(Number(clicked.closest('[data-project]').dataset.project), 1);
            if (!state.projects.length) {
                state.projects.push('');
            }
            renderEditor();
            renderTemplatePreview();
        }
    });

    saveBtnEl.addEventListener('click', async () => {
        try {
            syncLegacyContact();
            statusEl.textContent = 'Saving...';
            const url = app.dataset.updateUrl || app.dataset.storeUrl;
            const method = app.dataset.updateUrl ? 'patch' : 'post';
            const templateId = $('template-id')?.value || null;
            const payload = app.dataset.updateUrl
                ? { resume: state, template_id: templateId }
                : { source, template_id: templateId, resume: state };
            const response = await axios[method](url, payload);
            if (response.data.redirect) {
                window.location.href = response.data.redirect;
                return;
            }
            statusEl.textContent = 'Saved.';
        } catch (error) {
            statusEl.textContent = error.response?.data?.message || 'Save failed.';
        }
    });

    renderEditor();
    renderTemplatePreview();
})();
</script>
@endpush
