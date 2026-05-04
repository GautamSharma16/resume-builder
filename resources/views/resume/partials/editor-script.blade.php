@push('scripts')
<script>
(() => {
    const app = document.getElementById('create-cv-app');
    if (!app) return;

    /* ── Helpers ──────────────────────────────────────────── */
    const readJson = (id, fallback) => {
        const node = document.getElementById(id);
        if (!node) return fallback;
        try { return JSON.parse(node.textContent || ''); } catch { return fallback; }
    };
    const $ = (id) => document.getElementById(id);
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    const toList = (v) => String(v).split(',').map(x => x.trim()).filter(Boolean);
    const ensureArray = (v) => Array.isArray(v) ? v : [];

    /* ── State ────────────────────────────────────────────── */
    // Updated defaults to include project description
    const defaults = { name:'', email:'', mobile:'', location:'', social_links:[], contact:'', address:'', summary:'', skills:[], experience:[], education:[], projects:[] };
    const state = Object.assign({}, defaults, readJson('resume-initial-json', {}));
    const templates = readJson('resume-templates-json', {});
    let source = 'manual';
    let selectedTemplateId = app.dataset.selectedTemplate || '';
    let currentStep = 1;
    let savedResumeId = null;

    /* ── DOM refs ─────────────────────────────────────────── */
    const cvPreviewEl       = $('cv-preview');
    const expEditorEl       = $('exp-editor');
    const eduEditorEl       = $('edu-editor');
    const projectEditorEl   = $('project-editor');
    const templateIdEl      = $('template-id');
    const saveBtnEl         = $('save-cv');
    const statusEl          = $('cv-status');
    const autofillFileEl    = $('resume-autofill-file');
    const autofillBtnEl     = $('resume-autofill-button');
    const autofillStatusEl  = $('resume-autofill-status');
    const fileNameEl        = $('rp-file-name');
    const previewZoomInEl   = $('preview-zoom-in');
    const previewZoomOutEl  = $('preview-zoom-out');
    const previewZoomLvlEl  = $('preview-zoom-level');
    let previewZoom = 75;

    /* ── Guards ───────────────────────────────────────────── */
    if (!cvPreviewEl || !templateIdEl || !expEditorEl || !eduEditorEl || !saveBtnEl) {
        console.error('Resume maker: missing required DOM elements.'); return;
    }

    /* ── Normalise incoming data ──────────────────────────── */
    const normalise = (r = {}) => ({
        name:         String(r.name ?? ''),
        email:        String(r.email ?? ''),
        mobile:       String(r.mobile ?? r.contact ?? ''),
        location:     String(r.location ?? r.address ?? ''),
        social_links: ensureArray(r.social_links).map(String),
        contact:      String(r.contact ?? ''),
        address:      String(r.address ?? ''),
        summary:      String(r.summary ?? ''),
        skills:       ensureArray(r.skills).map(String),
        experience:   ensureArray(r.experience).map(e => ({
            company: String(e?.company ?? ''),
            role:    String(e?.role ?? ''),
            period:  String(e?.period ?? ''),
            points:  ensureArray(e?.points).map(String),
        })),
        education: ensureArray(r.education).map(String),
        // Updated to handle project description
        projects:  ensureArray(r.projects).map(p => typeof p === 'string' ? { name: p, description: '' } : { name: String(p?.name ?? ''), description: String(p?.description ?? '') }),
    });

    Object.assign(state, normalise(state));

    const ensureDefaults = () => {
        if (!state.experience.length) state.experience.push({ company:'', role:'', period:'', points:[''] });
        if (!state.education.length)  state.education.push('');
        if (!state.projects.length)   state.projects.push({ name: '', description: '' });
    };
    ensureDefaults();

    /* ── Template selection default ───────────────────────── */
    const templateKeys = Object.keys(templates);
    if (!selectedTemplateId && templateKeys.length) {
        selectedTemplateId = templateKeys[0];
        templateIdEl.value = selectedTemplateId;
    } else if (selectedTemplateId) {
        templateIdEl.value = selectedTemplateId;
    }

    /* ── Sync legacy contact/address fields ───────────────── */
    function syncLegacy() {
        state.contact = [state.email, state.mobile, ...state.social_links].filter(Boolean).join(' | ');
        state.address = state.location || '';
    }

    /* ── Render helpers ───────────────────────────────────── */
    function renderSkills() {
        return state.skills.map(s => `<span class="tpl-badge">${esc(s)}</span>`).join('');
    }
    function renderExperience() {
        return state.experience.map(e => {
            if (!e.company && !e.role && !e.period && !e.points.some(Boolean)) return '';
            const pts = e.points.filter(Boolean).map(p => `<li>${esc(p)}</li>`).join('');
            return `<div class="tpl-role"><div class="tpl-role-head"><strong>${esc(e.role)}</strong><span>${esc(e.period)}</span></div><p>${esc(e.company)}</p><ul>${pts}</ul></div>`;
        }).join('');
    }
    function renderList(arr) {
        // Handle both old format (strings) and new format (objects with name/description)
        const items = arr.filter(Boolean).map(i => {
            if (typeof i === 'string') {
                return `<li>${esc(i)}</li>`;
            }
            const name = esc(i?.name || '');
            const desc = esc(i?.description || '');
            return desc ? `<li><strong>${name}</strong><span class="tpl-description">${desc}</span></li>` : `<li>${name}</li>`;
        }).join('');
        return `<ul>${items}</ul>`;
    }
    function replaceToken(html, key, value) {
        return html
            .replace(new RegExp('\\{\\{\\s*' + key + '\\s*\\}\\}', 'g'), value)
            .split('[[' + key + ']]').join(value);
    }

    function renderTemplateHtml(template) {
        syncLegacy();
        let output = String(template?.html || '');
        const hasProjectsToken = /\{\{\s*projects\s*\}\}/.test(output) || output.includes('[[projects]]');

        const values = {
            name:         esc(state.name || 'Your Name'),
            email:        esc(state.email || 'email@example.com'),
            mobile:       esc(state.mobile || '+91 98765 43210'),
            location:     esc(state.location || 'City, Country'),
            contact:      esc(state.contact || [state.email || 'email@example.com', state.mobile || '+91 98765 43210'].filter(Boolean).join(' | ')),
            address:      esc(state.address || state.location || 'City, Country'),
            summary:      esc(state.summary || 'A focused professional summary that highlights your strongest experience, skills, and career direction.'),
            social_links: esc(state.social_links.join(' | ')),
            skills:       state.skills.length ? renderSkills() : '<span class="tpl-badge">Leadership</span><span class="tpl-badge">Communication</span><span class="tpl-badge">Project Management</span>',
            experience:   state.experience.some(e => e.company || e.role || e.period || e.points.some(Boolean))
                ? renderExperience()
                : '<div class="tpl-role"><div class="tpl-role-head"><strong>Job Title</strong><span>2023 - Present</span></div><p>Company Name</p><ul><li>Describe a measurable achievement or responsibility.</li></ul></div>',
            education:    state.education.some(Boolean) ? renderList(state.education) : '<ul><li>Degree or Certification, Institution</li></ul>',
            projects:     state.projects.some(Boolean) ? renderList(state.projects) : '<ul><li><strong>Project Name</strong><span class="tpl-description">Short project description or tech stack.</span></li></ul>',
        };

        Object.entries(values).forEach(([k, v]) => { output = replaceToken(output, k, v); });

        if (!hasProjectsToken && state.projects.some(Boolean)) {
            const section = `<h2>Projects</h2>${renderList(state.projects)}`;
            const lastDiv = output.lastIndexOf('</div>');
            output = lastDiv !== -1
                ? output.slice(0, lastDiv) + section + output.slice(lastDiv)
                : output + section;
        }

        return output;
    }

    /* ── Preview render ───────────────────────────────────── */
    function renderBasicPreview() {
        const header = [state.email, state.mobile, state.location].filter(Boolean).join(' · ');
        const socials = state.social_links.join(' · ');
        
        // Render projects with descriptions
        const projectsHtml = state.projects.filter(Boolean).map(p => {
            const name = typeof p === 'string' ? p : (p?.name || '');
            const desc = typeof p === 'string' ? '' : (p?.description || '');
            return desc ? `<li style="font-size:11px;overflow-wrap:anywhere;word-break:break-word;"><strong>${esc(name)}</strong><br><span style="color:#6b7280;font-size:10.5px;overflow-wrap:anywhere;word-break:break-word;">${esc(desc)}</span></li>` : `<li style="font-size:11px;overflow-wrap:anywhere;word-break:break-word;">${esc(name)}</li>`;
        }).join('');
        
        cvPreviewEl.innerHTML = `
            <div style="padding:40px 44px;font-family:Georgia,serif;background:#fff;overflow-wrap:anywhere;word-break:break-word;">
                <div style="border-bottom:2.5px solid #111;padding-bottom:10px;margin-bottom:18px;">
                    <h1 style="margin:0 0 6px;font-size:26px;letter-spacing:.5px;text-transform:uppercase;">${esc(state.name || 'Your Name')}</h1>
                    ${header ? `<p style="margin:0;font-size:11px;color:#4b5563;">${esc(header)}</p>` : ''}
                    ${socials ? `<p style="margin:2px 0 0;font-size:11px;color:#4b5563;">${esc(socials)}</p>` : ''}
                </div>
                ${state.summary ? `<h2 style="color:#0f766e;font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Summary</h2><p style="font-size:11.5px;margin:0 0 14px;">${esc(state.summary)}</p>` : ''}
                ${state.skills.length ? `<h2 style="color:#0f766e;font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Skills</h2><p style="font-size:11px;margin:0 0 14px;">${esc(state.skills.join(', '))}</p>` : ''}
                ${state.experience.some(e=>e.company||e.role) ? `<h2 style="color:#0f766e;font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 8px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Experience</h2>${state.experience.map(e => e.company||e.role ? `<div style="margin-bottom:12px;"><strong style="font-size:11.5px;">${esc(e.role)}</strong>${e.period?` <span style="float:right;color:#6b7280;font-size:10px;">${esc(e.period)}</span>`:''}<br><span style="color:#4b5563;font-size:10.5px;">${esc(e.company)}</span><ul style="margin:4px 0 0 14px;padding:0;">${e.points.filter(Boolean).map(p=>`<li style="font-size:11px;margin-bottom:2px;">${esc(p)}</li>`).join('')}</ul></div>` : '').join('')}` : ''}
                ${state.education.some(Boolean) ? `<h2 style="color:#0f766e;font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Education</h2><ul style="margin:0 0 14px 14px;padding:0;">${state.education.filter(Boolean).map(e=>`<li style="font-size:11px;">${esc(e)}</li>`).join('')}</ul>` : ''}
                ${projectsHtml ? `<h2 style="color:#0f766e;font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Projects</h2><ul style="margin:0 0 0 14px;padding:0;">${projectsHtml}</ul>` : ''}
            </div>`;
    }

    function renderTemplatePreview() {
        syncLegacy();
        if (!selectedTemplateId || !templates[selectedTemplateId]) {
            renderBasicPreview(); return;
        }

        const template = templates[selectedTemplateId];
        const output = renderTemplateHtml(template);
        cvPreviewEl.innerHTML = `<div class="resume-preview-stage"><div class="resume-sheet-preview shadow-md">${output}</div></div>`;
    }

    /* ── Editor render ────────────────────────────────────── */
    function renderEditor() {
        $('cv-name').value     = state.name;
        $('cv-email').value    = state.email;
        $('cv-mobile').value   = state.mobile;
        $('cv-location').value = state.location;
        $('cv-social').value   = state.social_links.join(', ');
        $('cv-summary').value  = state.summary;
        $('cv-skills').value   = state.skills.join(', ');

        expEditorEl.innerHTML = state.experience.map((e, i) => `
            <div class="rp-exp-card" data-exp="${i}">
                <div class="rp-input-grid">
                    <input class="rp-input" data-k="company" value="${esc(e.company)}" placeholder="Company / Organisation">
                    <input class="rp-input" data-k="role"    value="${esc(e.role)}"    placeholder="Job title / Role">
                </div>
                <input class="rp-input" data-k="period" value="${esc(e.period)}" placeholder="Period  e.g. Jan 2022 – Present">
                <textarea class="rp-input" data-k="points" rows="3" placeholder="One bullet point per line…">${esc(e.points.join('\n'))}</textarea>
                <button type="button" data-remove-exp class="rp-btn-remove">✕ Remove</button>
            </div>`).join('');

        eduEditorEl.innerHTML = state.education.map((e, i) => `
            <div style="display:flex;gap:8px;" data-edu="${i}">
                <input class="rp-input" style="flex:1;" value="${esc(e)}" placeholder="e.g. B.Sc. Computer Science, MIT, 2021">
                <button type="button" data-remove-edu class="rp-btn-sm-remove" title="Remove">–</button>
            </div>`).join('');

        if (projectEditorEl) {
            projectEditorEl.innerHTML = state.projects.map((p, i) => `
                <div class="rp-exp-card" data-project="${i}">
                    <input class="rp-input" data-k="name" value="${esc(p?.name || p || '')}" placeholder="Project name e.g. Open-source Markdown editor">
                    <textarea class="rp-input" data-k="description" rows="2" placeholder="Project description, impact, or technologies used">${esc(p?.description || '')}</textarea>
                    <button type="button" data-remove-project class="rp-btn-remove">✕ Remove</button>
                </div>`).join('');
        }
    }

    /* ── Preview zoom ─────────────────────────────────────── */
    function setZoom(z) {
        previewZoom = Math.min(130, Math.max(50, z));
        cvPreviewEl.style.transform = `scale(${previewZoom / 100})`;
        cvPreviewEl.style.transformOrigin = 'top center';
        if (previewZoomLvlEl) previewZoomLvlEl.textContent = `${previewZoom}%`;
    }

    /* ── Source toggle helpers ────────────────────────────── */
    const setSourceState = (src) => {
        document.querySelectorAll('.source-btn').forEach(b => {
            b.classList.toggle('active', b.dataset.source === src);
        });
    };

    /* ── Apply autofill result ────────────────────────────── */
    const applyResumeData = (resume) => {
        Object.assign(state, defaults, normalise(resume));
        ensureDefaults();
        renderEditor();
        renderTemplatePreview();
    };

    /* ── Event listeners ──────────────────────────────────── */

    // Live field sync
    document.querySelectorAll('.cv-field').forEach(input => {
        input.addEventListener('input', e => {
            const f = e.target.dataset.field;
            state[f] = ['skills','social_links'].includes(f) ? toList(e.target.value) : e.target.value;
            renderTemplatePreview();
        });
    });

    expEditorEl.addEventListener('input', e => {
        const block = e.target.closest('[data-exp]');
        if (!block) return;
        const i = Number(block.dataset.exp);
        const k = e.target.dataset.k;
        state.experience[i][k] = k === 'points'
            ? e.target.value.split('\n').map(x => x.trim()).filter(Boolean)
            : e.target.value;
        renderTemplatePreview();
    });

    eduEditorEl.addEventListener('input', e => {
        const row = e.target.closest('[data-edu]');
        if (!row) return;
        state.education[Number(row.dataset.edu)] = e.target.value;
        renderTemplatePreview();
    });

    projectEditorEl?.addEventListener('input', e => {
        const row = e.target.closest('[data-project]');
        if (!row) return;
        const i = Number(row.dataset.project);
        const k = e.target.dataset.k;
        if (!state.projects[i]) state.projects[i] = { name: '', description: '' };
        state.projects[i][k] = e.target.value;
        renderTemplatePreview();
    });

    templateIdEl.addEventListener('change', e => {
        selectedTemplateId = String(e.target.value || '');
        renderTemplatePreview();
    });

    // Delegated button clicks
    app.addEventListener('click', e => {
        const btn = e.target.closest('button');
        if (!btn) return;

        // Source toggle
        if (btn.classList.contains('source-btn')) {
            source = btn.dataset.source;
            setSourceState(source);
            const panel = $('existing-resume-panel');
            if (panel) panel.classList.toggle('visible', source === 'upload');
            return;
        }

        // Add buttons
        if (btn.id === 'add-exp')     { state.experience.push({ company:'', role:'', period:'', points:[''] }); renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'add-edu')     { state.education.push('');  renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'add-project') { state.projects.push({ name: '', description: '' });   renderEditor(); renderTemplatePreview(); }

        // Remove buttons
        if (btn.dataset.removeExp !== undefined) {
            state.experience.splice(Number(btn.closest('[data-exp]').dataset.exp), 1);
            if (!state.experience.length) state.experience.push({ company:'', role:'', period:'', points:[''] });
            renderEditor(); renderTemplatePreview();
        }
        if (btn.dataset.removeEdu !== undefined) {
            state.education.splice(Number(btn.closest('[data-edu]').dataset.edu), 1);
            if (!state.education.length) state.education.push('');
            renderEditor(); renderTemplatePreview();
        }
        if (btn.dataset.removeProject !== undefined) {
            state.projects.splice(Number(btn.closest('[data-project]').dataset.project), 1);
            if (!state.projects.length) state.projects.push({ name: '', description: '' });
            renderEditor(); renderTemplatePreview();
        }
    });

    // Save
    saveBtnEl.addEventListener('click', async () => {
        try {
            syncLegacy();
            statusEl.textContent = 'Saving…';
            statusEl.style.color = '';
            const url      = app.dataset.updateUrl || app.dataset.storeUrl;
            const method   = app.dataset.updateUrl ? 'patch' : 'post';
            const templateId = templateIdEl?.value || null;
            const payload  = app.dataset.updateUrl
                ? { resume: state, template_id: templateId }
                : { source, template_id: templateId, resume: state };
            const res = await axios[method](url, payload);
            if (res.data.redirect) { window.location.href = res.data.redirect; return; }
            if (res.data.resume?.id) { savedResumeId = res.data.resume.id; }
            statusEl.textContent = '✓ Saved';
            
            // Show completion panel
            goToStep(5);
            
            setTimeout(() => { statusEl.textContent = ''; }, 3000);
        } catch (err) {
            statusEl.textContent = err.response?.data?.message || 'Save failed.';
            statusEl.style.color = '#c0392b';
        }
    });

    // File autofill
    if (autofillBtnEl && autofillFileEl) {
        autofillFileEl.addEventListener('change', () => {
            const f = autofillFileEl.files?.[0];
            if (fileNameEl) fileNameEl.textContent = f ? f.name : 'No file chosen';
        });

        const doAutofill = async () => {
            const file = autofillFileEl.files?.[0];
            if (!file) {
                if (autofillStatusEl) { autofillStatusEl.textContent = 'Please choose a file first.'; autofillStatusEl.style.color = '#c0392b'; }
                return;
            }
            try {
                autofillBtnEl.disabled = true;
                autofillBtnEl.style.opacity = '.6';
                if (autofillStatusEl) { autofillStatusEl.textContent = 'Reading your resume with AI…'; autofillStatusEl.style.color = ''; }
                const fd = new FormData();
                fd.append('resume', file);
                const res = await axios.post(app.dataset.analyzeUrl, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
                applyResumeData(res.data.improved_resume || {});
                source = 'upload';
                setSourceState('upload');
                if (autofillStatusEl) autofillStatusEl.textContent = '✓ Resume imported — edit freely, preview updates live.';
            } catch (err) {
                if (autofillStatusEl) { autofillStatusEl.textContent = err.response?.data?.message || 'Could not read this file. Try a text-based PDF or DOCX.'; autofillStatusEl.style.color = '#c0392b'; }
            } finally {
                autofillBtnEl.disabled = false;
                autofillBtnEl.style.opacity = '';
            }
        };

        autofillBtnEl.addEventListener('click', doAutofill);
        autofillFileEl.addEventListener('change', doAutofill);
    }

    // Zoom controls
    previewZoomOutEl?.addEventListener('click', () => setZoom(previewZoom - 10));
    previewZoomInEl?.addEventListener('click',  () => setZoom(previewZoom + 10));

    /* ── Multi-step Navigation ────────────────────────────── */
    function goToStep(step) {
        currentStep = step;
        
        // Update step indicators
        document.querySelectorAll('.rp-step').forEach((el, idx) => {
            const stepNum = idx + 1;
            el.classList.remove('active', 'completed');
            if (stepNum < step) el.classList.add('completed');
            if (stepNum === step) el.classList.add('active');
        });
        
        // Show/hide step content
        document.querySelectorAll('.rp-step-content').forEach((el, idx) => {
            el.classList.toggle('active', idx + 1 === step);
        });
        
        // Show/hide completion panel
        const completionPanel = $('completion-panel');
        const formFooter = $('form-footer');
        if (completionPanel) {
            completionPanel.classList.toggle('visible', step > 4);
        }
        if (formFooter) {
            formFooter.style.display = step > 4 ? 'none' : 'flex';
        }
    }
    
    // Step navigation buttons
    document.getElementById('next-step-1')?.addEventListener('click', () => goToStep(2));
    document.getElementById('next-step-2')?.addEventListener('click', () => goToStep(3));
    document.getElementById('next-step-3')?.addEventListener('click', () => goToStep(4));
    document.getElementById('prev-step-2')?.addEventListener('click', () => goToStep(1));
    document.getElementById('prev-step-3')?.addEventListener('click', () => goToStep(2));
    document.getElementById('prev-step-4')?.addEventListener('click', () => goToStep(3));
    
    // Step click navigation
    document.querySelectorAll('.rp-step').forEach(stepEl => {
        stepEl.addEventListener('click', () => {
            const step = parseInt(stepEl.dataset.step);
            if (step < currentStep || step === 1) {
                goToStep(step);
            }
        });
    });

    /* ── Template Popup ───────────────────────────────────── */
    const templatePopup = $('template-popup');
    const templateGrid = $('template-grid');
    const changeTemplateBtn = $('change-template-btn');
    const closeTemplatePopup = $('close-template-popup');
    
    function openTemplatePopup() {
        if (!templatePopup || !templateGrid) return;
        
        // Build template grid
        templateGrid.innerHTML = Object.entries(templates).map(([id, t]) => `
            <div class="rp-template-card ${id === selectedTemplateId ? 'selected' : ''}" data-template-id="${id}">
                <div class="rp-template-card-preview">
                    <div class="preview-content">${renderTemplateHtml(t)}</div>
                </div>
                <div class="rp-template-card-info">
                    <h4>${esc(t.name || 'Untitled')}</h4>
                    <p>${esc(t.category || 'Resume template')}</p>
                </div>
            </div>
        `).join('');
        
        // Add click handlers
        templateGrid.querySelectorAll('.rp-template-card').forEach(card => {
            card.addEventListener('click', () => {
                selectedTemplateId = card.dataset.templateId;
                templateIdEl.value = selectedTemplateId;
                renderTemplatePreview();
                closeTemplatePopupFunc();
            });
        });
        
        templatePopup.classList.add('visible');
    }
    
    function closeTemplatePopupFunc() {
        templatePopup?.classList.remove('visible');
    }
    
    changeTemplateBtn?.addEventListener('click', openTemplatePopup);
    closeTemplatePopup?.addEventListener('click', closeTemplatePopupFunc);
    templatePopup?.addEventListener('click', (e) => {
        if (e.target === templatePopup) closeTemplatePopupFunc();
    });

    /* ── Download PDF & Completion ───────────────────────── */
    const downloadPdfBtn = $('download-pdf');
    const editResumeBtn = $('edit-resume');
    
    downloadPdfBtn?.addEventListener('click', () => {
        if (app.dataset.authenticated === '1' && app.dataset.downloadRequiresPlan === '1') {
            window.openPlanDownloadModal?.();
            return;
        }

        if (savedResumeId) {
            window.location.href = `/resume/${savedResumeId}/download/pdf`;
            return;
        }

        const loginUrl = app.dataset.loginUrl;
        if (loginUrl) {
            window.location.href = loginUrl + '?redirect=' + encodeURIComponent(window.location.href);
        }
    });
    
    editResumeBtn?.addEventListener('click', () => {
        goToStep(1);
    });

    /* ── Bootstrap ────────────────────────────────────────── */
    setZoom(previewZoom);
    setSourceState('manual');
    renderEditor();
    renderTemplatePreview();
})();
</script>
@endpush
