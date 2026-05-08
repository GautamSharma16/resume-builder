@push('scripts')
<script>
(() => {
    const app = document.getElementById('create-cv-app');
    if (!app) return;

    /* ── Helpers ── */
    const readJson = (id, fallback) => {
        const node = document.getElementById(id);
        if (!node) return fallback;
        try { return JSON.parse(node.textContent || ''); } catch { return fallback; }
    };
    const $ = (id) => document.getElementById(id);
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    const toList = (v) => String(v).split(',').map(x => x.trim()).filter(Boolean);
    const ensureArray = (v) => Array.isArray(v) ? v : [];

    /* ── State ── */
    const defaults = { name:'', email:'', mobile:'', location:'', social_links:[], contact:'', address:'', summary:'', skills:[], experience:[], education:[], projects:[], primary_color: '', primary_color_customized: false };
    const state = Object.assign({}, defaults, readJson('resume-initial-json', {}));
    
    // Prioritize color from URL if present (e.g. from template preview page)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('primary_color')) {
        state.primary_color = urlParams.get('primary_color');
        state.primary_color_customized = true;
    }

    const templates = readJson('resume-templates-json', {});
    let source = 'manual';
    let selectedTemplateId = app.dataset.selectedTemplate || '';
    let currentStep = 1;
    let savedResumeId = null;

    /* ── DOM refs ── */
    const cvPreviewEl     = $('cv-preview');
    const expEditorEl     = $('exp-editor');
    const eduEditorEl     = $('edu-editor');
    const projectEditorEl = $('project-editor');
    const templateIdEl    = $('template-id');
    const saveBtnEl       = $('save-cv');
    const statusEl        = $('cv-status');
    const autofillFileEl  = $('resume-autofill-file');
    const autofillBtnEl   = $('resume-autofill-button');
    const autofillStatusEl= $('resume-autofill-status');
    const fileNameEl      = $('rp-file-name');
    const zoomInEl        = $('preview-zoom-in');
    const zoomOutEl       = $('preview-zoom-out');
    const zoomLvlEl       = $('preview-zoom-level');
    let previewZoom = 75;
    let uploadInProgress = false;

    if (!cvPreviewEl || !templateIdEl || !expEditorEl || !eduEditorEl || !saveBtnEl) {
        console.error('Resume maker: missing required DOM elements.'); return;
    }

    /* ── Normalise ── */
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
        projects:  ensureArray(r.projects).map(p =>
            typeof p === 'string'
                ? { name: p, description: '' }
                : { name: String(p?.name ?? ''), description: String(p?.description ?? '') }
        ),
        primary_color: String(r.primary_color ?? ''),
        primary_color_customized: Boolean(r.primary_color_customized ?? (r.primary_color && r.primary_color !== '#2563eb')),
    });

    Object.assign(state, normalise(state));

    const ensureDefaults = () => {
        if (!state.experience.length) state.experience.push({ company:'', role:'', period:'', points:[''] });
        if (!state.education.length)  state.education.push('');
        if (!state.projects.length)   state.projects.push({ name:'', description:'' });
    };
    ensureDefaults();

    /* ── Default template ── */
    const templateKeys = Object.keys(templates);
    selectedTemplateId = selectedTemplateId || templateIdEl.value || templateKeys[0] || '';
    if (selectedTemplateId) templateIdEl.value = selectedTemplateId;

    /* ── Legacy sync ── */
    function syncLegacy() {
        state.contact = [state.email, state.mobile, ...state.social_links].filter(Boolean).join(' | ');
        state.address = state.location || '';
    }

    /* ── Render helpers ── */
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
        const items = arr.filter(Boolean).map(i => {
            if (typeof i === 'string') return `<li>${esc(i)}</li>`;
            const name = esc(i?.name || '');
            const desc = esc(i?.description || '');
            return desc
                ? `<li><strong>${name}</strong><span class="tpl-description">${desc}</span></li>`
                : `<li>${name}</li>`;
        }).join('');
        return `<ul>${items}</ul>`;
    }
    function replaceToken(html, key, value) {
        return html
            .replace(new RegExp('\\{\\{\\s*' + key + '\\s*\\}\\}', 'g'), value)
            .split('[[' + key + ']]').join(value);
    }
    function resumeAccentStyle(color) {
        const accent = String(color || '');
        if (!/^#[0-9a-f]{6}$/i.test(accent)) return '';

        return `<style>
            .resume-sheet-preview, .rp-tpl-thumb-inner, .resume-maker-preview { --primary: ${accent}; }
            .resume-sheet-preview .tpl-resume, .rp-tpl-thumb-inner .tpl-resume {
                border-color: var(--primary) !important;
                border-top-color: var(--primary) !important;
                border-right-color: var(--primary) !important;
                border-bottom-color: var(--primary) !important;
                border-left-color: var(--primary) !important;
            }
            .resume-sheet-preview .tpl-resume h1,
            .resume-sheet-preview .tpl-resume h2,
            .resume-sheet-preview .tpl-resume h3,
            .resume-sheet-preview .tpl-resume a,
            .resume-sheet-preview .tpl-role-head strong,
            .rp-tpl-thumb-inner .tpl-resume h1,
            .rp-tpl-thumb-inner .tpl-resume h2,
            .rp-tpl-thumb-inner .tpl-resume h3,
            .rp-tpl-thumb-inner .tpl-resume a,
            .rp-tpl-thumb-inner .tpl-role-head strong {
                color: var(--primary) !important;
                border-color: var(--primary) !important;
            }
            .resume-sheet-preview .tpl-badge,
            .rp-tpl-thumb-inner .tpl-badge {
                background: var(--primary) !important;
                border-color: var(--primary) !important;
                color: #fff !important;
            }
            .resume-sheet-preview .tpl-rule,
            .resume-sheet-preview .tpl-accentbox header > div,
            .resume-sheet-preview .tpl-two aside,
            .resume-sheet-preview .tpl-carded header,
            .resume-sheet-preview .tpl-band header,
            .resume-sheet-preview .tpl-resume > header[style*="background"],
            .resume-sheet-preview .tpl-resume h2[style*="background"],
            .rp-tpl-thumb-inner .tpl-rule,
            .rp-tpl-thumb-inner .tpl-accentbox header > div,
            .rp-tpl-thumb-inner .tpl-two aside,
            .rp-tpl-thumb-inner .tpl-carded header,
            .rp-tpl-thumb-inner .tpl-band header,
            .rp-tpl-thumb-inner .tpl-resume > header[style*="background"],
            .rp-tpl-thumb-inner .tpl-resume h2[style*="background"] {
                background: var(--primary) !important;
                color: #fff !important;
            }
            .resume-sheet-preview .tpl-rule *,
            .resume-sheet-preview .tpl-accentbox header > div *,
            .resume-sheet-preview .tpl-two aside *,
            .resume-sheet-preview .tpl-carded header *,
            .resume-sheet-preview .tpl-band header *,
            .resume-sheet-preview .tpl-resume > header[style*="background"] *,
            .resume-sheet-preview .tpl-resume h2[style*="background"],
            .rp-tpl-thumb-inner .tpl-rule *,
            .rp-tpl-thumb-inner .tpl-accentbox header > div *,
            .rp-tpl-thumb-inner .tpl-two aside *,
            .rp-tpl-thumb-inner .tpl-carded header *,
            .rp-tpl-thumb-inner .tpl-band header *,
            .rp-tpl-thumb-inner .tpl-resume > header[style*="background"] *,
            .rp-tpl-thumb-inner .tpl-resume h2[style*="background"] {
                color: #fff !important;
                border-color: rgba(255,255,255,0.45) !important;
            }
        </style>`;
    }
    function renderTemplateHtml(template) {
        syncLegacy();
        let output = String(template?.html || '');
        const hasProjectsToken = /\{\{\s*projects\s*\}\}/.test(output) || output.includes('[[projects]]');
        const values = {
            name:         esc(state.name || 'Alex Johnson'),
            email:        esc(state.email || 'alex@example.com'),
            mobile:       esc(state.mobile || '+91 98765 43210'),
            location:     esc(state.location || 'Mumbai, India'),
            contact:      esc(state.contact || [state.email, state.mobile].filter(Boolean).join(' | ') || 'alex@example.com | +91 98765 43210'),
            address:      esc(state.address || state.location || 'Mumbai, India'),
            summary:      esc(state.summary || 'Experienced professional with a strong background in product development, cross-functional leadership, and building reliable user-focused systems.'),
            social_links: esc(state.social_links.join(' | ') || 'linkedin.com/in/alex | github.com/alex'),
            skills:       state.skills.length ? renderSkills() : '<span class="tpl-badge">Leadership</span><span class="tpl-badge">React</span><span class="tpl-badge">Python</span><span class="tpl-badge">Product Strategy</span>',
            experience:   state.experience.some(e => e.company || e.role || e.period || e.points.some(Boolean))
                ? renderExperience()
                : '<div class="tpl-role"><div class="tpl-role-head"><strong>Senior Engineer</strong><span>2021-Present</span></div><p>TechCorp</p><ul><li>Led a team of 6 engineers across product and platform initiatives.</li><li>Reduced API latency by 40% through profiling and service optimization.</li></ul></div>',
            education:    state.education.some(Boolean) ? renderList(state.education) : '<ul><li>B.Sc. Computer Science, MIT, 2019</li></ul>',
            projects:     state.projects.some(p => p?.name || typeof p === 'string') ? renderList(state.projects) : '<ul><li><strong>Open Resume</strong><span class="tpl-description">Built a resume builder with React and Node.js.</span></li></ul>',
        };
        Object.entries(values).forEach(([k, v]) => { output = replaceToken(output, k, v); });
        if (!hasProjectsToken) {
            const section = `<h2>Projects</h2>${values.projects}`;
            const lastDiv = output.lastIndexOf('</div>');
            output = lastDiv !== -1 ? output.slice(0, lastDiv) + section + output.slice(lastDiv) : output + section;
        }
        output = resumeAccentStyle(state.primary_color_customized ? state.primary_color : '') + output;
        return output;
    }

    /* ── Preview ── */
    function renderBasicPreview() {
        const primaryColor = state.primary_color || '#2563eb';
        const header = [state.email, state.mobile, state.location].filter(Boolean).join(' · ');
        const socials = state.social_links.join(' · ');
        const projectsHtml = state.projects.filter(Boolean).map(p => {
            const name = typeof p === 'string' ? p : (p?.name || '');
            const desc = typeof p === 'string' ? '' : (p?.description || '');
            return desc
                ? `<li style="font-size:11px;"><strong>${esc(name)}</strong><br><span style="color:#6b7280;font-size:10.5px;">${esc(desc)}</span></li>`
                : `<li style="font-size:11px;">${esc(name)}</li>`;
        }).join('');
        cvPreviewEl.innerHTML = `
            <div style="padding:40px 44px;font-family:Georgia,serif;background:#fff;--primary:${primaryColor};">
                <div style="border-bottom:2.5px solid ${primaryColor};padding-bottom:10px;margin-bottom:18px;">
                    <h1 style="margin:0 0 6px;font-size:26px;letter-spacing:.5px;text-transform:uppercase;color:${primaryColor};">${esc(state.name || 'Your Name')}</h1>
                    ${header ? `<p style="margin:0;font-size:11px;color:#4b5563;">${esc(header)}</p>` : ''}
                    ${socials ? `<p style="margin:2px 0 0;font-size:11px;color:#4b5563;">${esc(socials)}</p>` : ''}
                </div>
                ${state.summary ? `<h2 style="color:${primaryColor};font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Summary</h2><p style="font-size:11.5px;margin:0 0 14px;">${esc(state.summary)}</p>` : ''}
                ${state.skills.length ? `<h2 style="color:${primaryColor};font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Skills</h2><p style="font-size:11px;margin:0 0 14px;">${esc(state.skills.join(', '))}</p>` : ''}
                ${state.experience.some(e=>e.company||e.role) ? `<h2 style="color:${primaryColor};font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 8px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Experience</h2>${state.experience.map(e => e.company||e.role ? `<div style="margin-bottom:12px;"><strong style="font-size:11.5px;color:${primaryColor};">${esc(e.role)}</strong>${e.period?` <span style="float:right;color:#6b7280;font-size:10px;">${esc(e.period)}</span>`:''}<br><span style="color:#4b5563;font-size:10.5px;">${esc(e.company)}</span><ul style="margin:4px 0 0 14px;padding:0;">${e.points.filter(Boolean).map(p=>`<li style="font-size:11px;margin-bottom:2px;">${esc(p)}</li>`).join('')}</ul></div>` : '').join('')}` : ''}
                ${state.education.some(Boolean) ? `<h2 style="color:${primaryColor};font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Education</h2><ul style="margin:0 0 14px 14px;padding:0;">${state.education.filter(Boolean).map(e=>`<li style="font-size:11px;">${esc(e)}</li>`).join('')}</ul>` : ''}
                ${projectsHtml ? `<h2 style="color:${primaryColor};font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Projects</h2><ul style="margin:0 0 0 14px;padding:0;">${projectsHtml}</ul>` : ''}
            </div>`;
    }

    function renderTemplatePreview() {
        syncLegacy();
        if (!selectedTemplateId || !templates[selectedTemplateId]) { renderBasicPreview(); return; }
        const output = renderTemplateHtml(templates[selectedTemplateId]);
        cvPreviewEl.innerHTML = `<div class="resume-preview-stage"><div class="resume-sheet-preview">${output}</div></div>`;
    }

    function refreshTemplatePopupThumbs() {
        if (typeof templateGrid === 'undefined' || !templateGrid || !templatePopup?.classList.contains('visible')) return;

        const selectedId = templateIdEl?.value || selectedTemplateId || '';
        templateGrid.innerHTML = '';
        Object.entries(templates).forEach(([id, tpl]) => {
            const card = buildTemplateCard(id, tpl, id === selectedId);
            bindTemplateCard(card, id);
            templateGrid.appendChild(card);
        });
    }

    /* ── Editor render — uses design-system classes ── */
    function renderEditor() {
        $('cv-name').value     = state.name;
        $('cv-email').value    = state.email;
        $('cv-mobile').value   = state.mobile;
        $('cv-location').value = state.location;
        $('cv-social').value   = state.social_links.join(', ');
        $('cv-summary').value  = state.summary;
        $('cv-skills').value   = state.skills.join(', ');

        /* ── Experience cards ── */
        expEditorEl.innerHTML = state.experience.map((e, i) => `
            <div class="rp-entry-card" data-exp="${i}">
                <div class="rp-entry-row">
                    <div class="rp-entry-field">
                        <label class="rp-entry-label">Company / Organisation</label>
                        <input class="rp-input" data-k="company" value="${esc(e.company)}" placeholder="e.g. Google, Accenture">
                    </div>
                    <div class="rp-entry-field">
                        <label class="rp-entry-label">Job Title / Role</label>
                        <input class="rp-input" data-k="role" value="${esc(e.role)}" placeholder="e.g. Product Designer">
                    </div>
                </div>
                <div class="rp-entry-field">
                    <label class="rp-entry-label">Period</label>
                    <input class="rp-input" data-k="period" value="${esc(e.period)}" placeholder="e.g. Jan 2022 – Present">
                </div>
                <div class="rp-entry-field">
                    <label class="rp-entry-label">Key responsibilities <span class="rp-entry-hint">(one bullet per line)</span></label>
                    <textarea class="rp-input rp-input-ta" data-k="points" rows="4" placeholder="• Led a team of 5 engineers&#10;• Reduced load time by 40%">${esc(e.points.join('\n'))}</textarea>
                </div>
                <button type="button" data-remove-exp class="rp-entry-remove">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    Remove
                </button>
            </div>`).join('');

        /* ── Education rows ── */
        eduEditorEl.innerHTML = state.education.map((e, i) => `
            <div class="rp-edu-row" data-edu="${i}">
                <input class="rp-input" value="${esc(e)}" placeholder="e.g. B.Sc. Computer Science, MIT, 2021">
                <button type="button" data-remove-edu class="rp-edu-remove" title="Remove">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>`).join('');

        /* ── Project cards ── */
        if (projectEditorEl) {
            projectEditorEl.innerHTML = state.projects.map((p, i) => `
                <div class="rp-entry-card" data-project="${i}">
                    <div class="rp-entry-field">
                        <label class="rp-entry-label">Project Name</label>
                        <input class="rp-input" data-k="name" value="${esc(p?.name || p || '')}" placeholder="e.g. Open-source Markdown editor">
                    </div>
                    <div class="rp-entry-field">
                        <label class="rp-entry-label">Description <span class="rp-entry-hint">(impact, tech stack)</span></label>
                        <textarea class="rp-input rp-input-ta" data-k="description" rows="2" placeholder="Built with React and Node.js. Reduced build time by 30%.">${esc(p?.description || '')}</textarea>
                    </div>
                    <button type="button" data-remove-project class="rp-entry-remove">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        Remove
                    </button>
                </div>`).join('');
        }
    }

    /* ── Zoom ── */
    function setZoom(z) {
        previewZoom = Math.min(130, Math.max(50, z));
        cvPreviewEl.style.transform = `scale(${previewZoom / 100})`;
        cvPreviewEl.style.transformOrigin = 'top center';
        if (zoomLvlEl) zoomLvlEl.textContent = `${previewZoom}%`;
    }

    /* ── Source toggle ── */
    const setSourceState = (src) => {
        document.querySelectorAll('.source-btn').forEach(b => b.classList.toggle('active', b.dataset.source === src));
    };

    function applyColorSelection(color) {
        state.primary_color = color || '';
        state.primary_color_customized = state.primary_color !== '';

        document.querySelectorAll('.color-option').forEach(b => {
            const buttonColor = b.dataset.color || '';
            const active = buttonColor === state.primary_color;
            b.classList.toggle('active', active);
            b.style.borderColor = active
                ? 'var(--navy, var(--slate-900, #0b1221))'
                : (buttonColor ? 'transparent' : '#e5e7eb');
        });

        renderTemplatePreview();
        refreshTemplatePopupThumbs();
    }

    /* ── Apply autofill ── */
    const applyResumeData = (resume) => {
        const keepColor = {
            primary_color: state.primary_color,
            primary_color_customized: state.primary_color_customized,
        };
        Object.assign(state, defaults, normalise(resume));
        Object.assign(state, keepColor);
        ensureDefaults();
        renderEditor();
        applyColorSelection(state.primary_color_customized ? state.primary_color : '');
    };

    /* ═══════════════════════════════════════════
       STEP NAVIGATION — uses .rp-step-tab
    ═══════════════════════════════════════════ */
    function goToStep(step) {
        currentStep = Math.max(1, Math.min(step, 4));

        /* Update tab indicators */
        document.querySelectorAll('.rp-step-tab').forEach(tab => {
            const t = parseInt(tab.dataset.step);
            tab.classList.remove('active', 'completed');
            if (t === currentStep) tab.classList.add('active');
            else if (t < currentStep) tab.classList.add('completed');

            /* Swap icon: checkmark if completed, number otherwise */
            const icon = tab.querySelector('.rp-step-icon');
            if (icon) {
                if (t < currentStep) {
                    icon.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>`;
                } else {
                    icon.textContent = t;
                }
            }
        });

        /* Show/hide step panels */
        document.querySelectorAll('.rp-step-content').forEach(panel => {
            panel.classList.toggle('active', parseInt(panel.dataset.step) === currentStep);
        });

        /* Handle completion panel */
        const completionPanel = $('completion-panel');
        if (completionPanel) {
            if (step > 4) {
                /* Show completion, hide all step content */
                document.querySelectorAll('.rp-step-content').forEach(p => p.classList.remove('active'));
                completionPanel.style.display = 'block';
            } else {
                completionPanel.style.display = 'none';
            }
        }
    }

    /* Wire step tabs — allow clicking back to completed steps */
    document.querySelectorAll('.rp-step-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const target = parseInt(tab.dataset.step);
            if (target <= currentStep) goToStep(target);
        });
    });

    /* Wire next / prev buttons */
    $('next-step-1')?.addEventListener('click', () => goToStep(currentStep + 1));
    $('next-step-2')?.addEventListener('click', () => goToStep(currentStep + 1));
    $('next-step-3')?.addEventListener('click', () => goToStep(currentStep + 1));
    $('prev-step-2')?.addEventListener('click', () => goToStep(currentStep - 1));
    $('prev-step-3')?.addEventListener('click', () => goToStep(currentStep - 1));
    $('prev-step-4')?.addEventListener('click', () => goToStep(currentStep - 1));

    $('edit-resume')?.addEventListener('click', () => goToStep(1));

    /* ── Event listeners ── */
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
        if (!state.projects[i]) state.projects[i] = { name:'', description:'' };
        state.projects[i][k] = e.target.value;
        renderTemplatePreview();
    });

    templateIdEl.addEventListener('change', e => {
        selectedTemplateId = String(e.target.value || '');
        renderTemplatePreview();
    });

    document.querySelectorAll('.color-option').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            e.stopPropagation();
            applyColorSelection(btn.dataset.color || '');
        });
    });

    /* Delegated button clicks */
    app.addEventListener('click', e => {
        const btn = e.target.closest('button');
        if (!btn) return;

        if (btn.classList.contains('source-btn')) {
            source = btn.dataset.source;
            setSourceState(source);
            const panel = $('existing-resume-panel');
            if (panel) panel.classList.toggle('visible', source === 'upload');
            return;
        }

        if (btn.classList.contains('color-option')) {
            applyColorSelection(btn.dataset.color || '');
            return;
        }

        if (btn.id === 'add-exp')     { state.experience.push({ company:'', role:'', period:'', points:[''] }); renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'add-edu')     { state.education.push(''); renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'add-project') { state.projects.push({ name:'', description:'' }); renderEditor(); renderTemplatePreview(); }

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
            if (!state.projects.length) state.projects.push({ name:'', description:'' });
            renderEditor(); renderTemplatePreview();
        }
    });

    /* Save */
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
            if (res.data.resume?.id) savedResumeId = res.data.resume.id;
            statusEl.textContent = '✓ Saved';
            goToStep(5); /* show completion */
            setTimeout(() => { statusEl.textContent = ''; }, 3000);
        } catch (err) {
            statusEl.textContent = err.response?.data?.message || 'Save failed.';
            statusEl.style.color = '#c0392b';
        }
    });

    /* File autofill */
    if (autofillBtnEl && autofillFileEl) {
        autofillFileEl.addEventListener('change', () => {
            const f = autofillFileEl.files?.[0];
            if (fileNameEl) fileNameEl.textContent = f ? f.name : 'Click to upload your resume';
            if (autofillStatusEl) {
                autofillStatusEl.textContent = f ? 'File selected. Click Autofill to import it.' : '';
                autofillStatusEl.style.color = '';
            }
        });

        const doAutofill = async () => {
            if (uploadInProgress) return;
            const file = autofillFileEl.files?.[0];
            if (!file) {
                if (autofillStatusEl) { autofillStatusEl.textContent = 'Please choose a file first.'; autofillStatusEl.style.color = '#c0392b'; }
                return;
            }
            try {
                uploadInProgress = true;
                autofillBtnEl.disabled = true;
                autofillBtnEl.style.opacity = '.6';
                if (autofillStatusEl) { autofillStatusEl.textContent = 'Reading your resume with AI…'; autofillStatusEl.style.color = ''; }
                const fd = new FormData();
                fd.append('resume', file);
                fd.append('mode', 'autofill');
                let data;
                if (window.axios) {
                    const res = await window.axios.post(app.dataset.analyzeUrl, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
                    data = res.data;
                } else {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const res = await fetch(app.dataset.analyzeUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: fd,
                    });
                    data = await res.json().catch(() => ({}));
                    if (!res.ok) {
                        const error = new Error(data.message || 'Could not import this resume.');
                        error.response = { data };
                        throw error;
                    }
                }
                if (!data.success) {
                    throw new Error(data.message || 'Could not import this resume.');
                }
                applyResumeData(data.improved_resume || {});
                source = 'upload';
                setSourceState('upload');
                if (autofillStatusEl) autofillStatusEl.textContent = '✓ Resume imported — edit freely, preview updates live.';
            } catch (err) {
                if (autofillStatusEl) { autofillStatusEl.textContent = err.response?.data?.message || err.message || 'Could not read this file. Try a text-based PDF or DOCX.'; autofillStatusEl.style.color = '#c0392b'; }
            } finally {
                uploadInProgress = false;
                autofillBtnEl.disabled = false;
                autofillBtnEl.style.opacity = '';
            }
        };

        autofillBtnEl.addEventListener('click', doAutofill);
    }

    /* Dropzone click handler */
    $('rp-dropzone-trigger')?.addEventListener('click', e => {
        if (!e.target.closest('#resume-autofill-button')) autofillFileEl?.click();
    });

    /* Zoom */
    zoomOutEl?.addEventListener('click', () => setZoom(previewZoom - 10));
    zoomInEl?.addEventListener('click',  () => setZoom(previewZoom + 10));

    /* Template popup */
    const templatePopup    = $('template-popup');
    const templateGrid     = $('template-grid');
    const changeTemplateBtn = $('change-template-btn');
    const closePopupBtn    = $('close-template-popup');

    function buildTemplateCard(id, template, isSelected) {
        const card = document.createElement('div');
        card.className = 'rp-tpl-card' + (isSelected ? ' selected' : '');
        card.dataset.templateId = id;

        const thumb = document.createElement('div');
        thumb.className = 'rp-tpl-thumb';

        const check = document.createElement('div');
        check.className = 'rp-tpl-check';
        check.innerHTML = `<svg width="14" height="14" fill="none" stroke="white" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>`;
        thumb.appendChild(check);

        const inner = document.createElement('div');
        inner.className = 'rp-tpl-thumb-inner';

        const html = String(template?.html || '');
        const sampleData = {
            name: 'Alex Johnson',
            email: 'alex@example.com',
            mobile: '+91 98765 43210',
            location: 'Mumbai, India',
            contact: 'alex@example.com | +91 98765 43210',
            address: 'Mumbai, India',
            summary: 'Experienced professional with a strong background in product development.',
            social_links: 'linkedin.com/in/alex',
            skills: '<span class="tpl-badge">Leadership</span><span class="tpl-badge">React</span><span class="tpl-badge">Python</span>',
            experience: `<div class="tpl-role"><div class="tpl-role-head"><strong>Senior Engineer</strong><span>2021-Present</span></div><p>TechCorp</p><ul><li>Led a team of 6 engineers</li><li>Reduced API latency by 40%</li></ul></div>`,
            education: '<ul><li>B.Sc. Computer Science, MIT, 2019</li></ul>',
            projects: '<ul><li><strong>Open Resume</strong><span class="tpl-description">Built with React &amp; Node.js</span></li></ul>',
        };

        let filled = html;
        Object.entries(sampleData).forEach(([k, v]) => {
            filled = filled.replace(new RegExp('\\{\\{\\s*' + k + '\\s*\\}\\}', 'g'), v);
            filled = filled.split('[[' + k + ']]').join(v);
        });

        inner.innerHTML = resumeAccentStyle(state.primary_color_customized ? state.primary_color : '') + filled;
        thumb.appendChild(inner);

        const name = document.createElement('div');
        name.className = 'rp-tpl-name';
        name.textContent = template?.name || 'Untitled';

        card.appendChild(thumb);
        card.appendChild(name);
        return card;
    }

    function openTemplatePopup() {
        if (!templatePopup || !templateGrid) return;

        const selectedId = templateIdEl?.value || selectedTemplateId || '';

        templateGrid.innerHTML = '';
        Object.entries(templates).forEach(([id, tpl]) => {
            const card = buildTemplateCard(id, tpl, id === selectedId);
            bindTemplateCard(card, id);
            templateGrid.appendChild(card);
        });

        templatePopup.classList.add('visible');
    }

    function bindTemplateCard(card, id) {
        card.addEventListener('click', () => {
            templateGrid.querySelectorAll('.rp-tpl-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');

            if (templateIdEl) {
                selectedTemplateId = id;
                templateIdEl.value = id;
                templateIdEl.dispatchEvent(new Event('change'));
            }

            applyColorSelection(state.primary_color_customized ? state.primary_color : '');
            setTimeout(() => templatePopup.classList.remove('visible'), 160);
        });
    }

    changeTemplateBtn?.addEventListener('click', openTemplatePopup);
    closePopupBtn?.addEventListener('click', () => templatePopup?.classList.remove('visible'));
    templatePopup?.addEventListener('click', e => { if (e.target === templatePopup) templatePopup.classList.remove('visible'); });

    /* Download */
    $('download-pdf')?.addEventListener('click', () => {
        if (app.dataset.authenticated === '1' && app.dataset.downloadRequiresPlan === '1') {
            window.openPlanDownloadModal?.(); return;
        }
        if (savedResumeId) { window.location.href = `/resume/${savedResumeId}/download/pdf`; return; }
        const loginUrl = app.dataset.loginUrl;
        if (loginUrl) window.location.href = loginUrl + '?redirect=' + encodeURIComponent(window.location.href);
    });

    /* ── Bootstrap ── */
    setZoom(previewZoom);
    setSourceState('manual');
    applyColorSelection(state.primary_color_customized ? state.primary_color : '');
    renderEditor();
    renderTemplatePreview();
    goToStep(1);
})();
</script>

{{-- ── Entry card & edu row styles injected here so they apply to JS-rendered HTML ── --}}
<style>
/* ═══════════════════════════════════════════════════════
   ENTRY CARDS  (experience & projects)
═══════════════════════════════════════════════════════ */
.rp-entry-card {
    background: var(--white, #fff);
    border: 1.5px solid rgba(0,0,0,0.09);
    border-radius: 16px;
    padding: 1.375rem 1.5rem 1.125rem;
    margin-bottom: 1.125rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.rp-entry-card:hover {
    border-color: rgba(37,99,235,0.22);
    box-shadow: 0 4px 18px rgba(0,0,0,0.05);
}

/* 2-column row within a card */
.rp-entry-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.875rem;
    margin-bottom: 0.875rem;
}
@media (max-width: 560px) { .rp-entry-row { grid-template-columns: 1fr; } }

/* Individual field block inside a card */
.rp-entry-field {
    display: flex;
    flex-direction: column;
    gap: 0.3rem;
    margin-bottom: 0.875rem;
}
.rp-entry-field:last-of-type { margin-bottom: 0; }

.rp-entry-label {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--ink, #1e293b);
    letter-spacing: 0.01em;
}
.rp-entry-hint {
    font-weight: 400;
    color: var(--soft, #94a3b8);
    font-size: 0.72rem;
}

/* Textarea variant */
.rp-input-ta {
    resize: vertical;
    min-height: 90px;
    line-height: 1.6;
}

/* Remove button */
.rp-entry-remove {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    margin-top: 0.75rem;
    padding: 0.375rem 0.875rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--muted, #64748b);
    background: var(--surface-2, #f1f5f9);
    border: 1.5px solid rgba(0,0,0,0.07);
    cursor: pointer;
    transition: all 0.2s;
    font-family: var(--font-body, sans-serif);
}
.rp-entry-remove:hover {
    background: #fee2e2;
    color: #dc2626;
    border-color: rgba(220,38,38,0.2);
}

/* ═══════════════════════════════════════════════════════
   EDUCATION ROWS
═══════════════════════════════════════════════════════ */
.rp-edu-row {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    margin-bottom: 0.75rem;
}
.rp-edu-row .rp-input { flex: 1; }
.rp-edu-remove {
    flex-shrink: 0;
    width: 34px; height: 34px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%;
    border: 1.5px solid rgba(0,0,0,0.09);
    background: var(--surface-2, #f1f5f9);
    color: var(--muted, #64748b);
    cursor: pointer;
    transition: all 0.2s;
}
.rp-edu-remove:hover {
    background: #fee2e2;
    color: #dc2626;
    border-color: rgba(220,38,38,0.2);
}
</style>
@endpush
