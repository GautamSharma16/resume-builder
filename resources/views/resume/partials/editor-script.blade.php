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
    const defaults = { name:'', last_name:'', job_title:'', email:'', mobile:'', location:'', social_links:[], contact:'', address:'', summary:'', skills:[], experience:[], education:[], projects:[], primary_color: '', primary_color_customized: false, profile_image: '' };
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
    const expEditorEl     = $('rp-exp-editor');
    const eduEditorEl     = $('rp-edu-editor');
    const projectEditorEl = $('project-editor');
    const templateIdEl    = $('template-id');
    const saveBtnEl       = $('save-cv-btn');
    const statusEl        = $('cv-status') || { textContent: '', style: {} };
    const autofillFileEl  = $('resume-autofill-file');
    const autofillBtnEl   = $('resume-autofill-button');
    const autofillStatusEl= $('resume-autofill-status');
    const fileNameEl      = $('rp-file-name');
    const zoomInEl        = $('preview-zoom-in');
    const zoomOutEl       = $('preview-zoom-out');
    const zoomLvlEl       = $('preview-zoom-level');
    let previewZoom = 100;
    let uploadInProgress = false;

    if (!cvPreviewEl || !templateIdEl || !expEditorEl || !eduEditorEl || !saveBtnEl) {
        console.error('Resume maker: missing required DOM elements.'); return;
    }

    /* ── Normalise ── */
    const educationToText = (item) => {
        if (typeof item === 'string') return item;
        if (!item || typeof item !== 'object') return String(item ?? '');
        return [
            item.degree,
            item.institution,
            item.school,
            item.university,
            item.year,
            item.duration,
            item.cgpa ? `CGPA: ${item.cgpa}` : '',
        ].map(v => String(v ?? '').trim()).filter(Boolean).join(', ');
    };

    const normalise = (r = {}) => ({
        name:         String(r.name ?? ''),
        last_name:    String(r.last_name ?? ''),
        job_title:    String(r.job_title ?? ''),
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
        education: ensureArray(r.education).map(educationToText).filter(Boolean),
        projects:  ensureArray(r.projects).map(p =>
            typeof p === 'string'
                ? { name: p, description: '' }
                : { name: String(p?.name ?? ''), description: String(p?.description ?? '') }
        ),
        primary_color: String(r.primary_color ?? ''),
        primary_color_customized: Boolean(r.primary_color_customized ?? (r.primary_color && r.primary_color !== '#2563eb')),
        profile_image: String(r.profile_image ?? ''),
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

    function fullName() {
        return [state.name, state.last_name].map(part => String(part || '').trim()).filter(Boolean).join(' ');
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
    function hasResumePlaceholders(html) {
        return /\{\{\s*(name|last_name|job_title|email|mobile|location|contact|address|summary|skills|experience|education|projects|social_links|profile_image)\s*\}\}/i.test(html)
            || /\[\[\s*(name|last_name|job_title|email|mobile|location|contact|address|summary|skills|experience|education|projects|social_links|profile_image)\s*\]\]/i.test(html)
            || /\{\{\s*\$resume|\{\{\s*\$(name|last_name|job_title|email|mobile|location|summary|skills|experience|education|projects|social_links|profile_image)|@@foreach\s*\(\s*\$resume/i.test(html);
    }
    function editableTemplateShell() {
        return `
            <div class="tpl-resume tpl-uploaded-editable" style="font-family:Inter,Arial,sans-serif;color:#172033;padding:42px;line-height:1.45;">
                <header style="border-bottom:3px solid var(--primary, #2563eb);padding-bottom:16px;margin-bottom:22px;display:flex;gap:18px;align-items:flex-start;">
                    <div>@{{profile_image}}</div>
                    <div style="flex:1;">
                        <h1 style="margin:0 0 6px;font-size:30px;letter-spacing:.04em;text-transform:uppercase;color:var(--primary, #2563eb);">@{{name}}</h1>
                        <p style="margin:0 0 4px;font-size:14px;color:#334155;">@{{job_title}}</p>
                        <p style="margin:0;font-size:12px;color:#475569;">@{{email}} | @{{mobile}} | @{{location}}</p>
                        <p style="margin:4px 0 0;font-size:12px;color:#475569;">@{{social_links}}</p>
                    </div>
                </header>
                <section><h2>Professional Summary</h2><p>@{{summary}}</p></section>
                <section><h2>Skills</h2><div class="tpl-badges">@{{skills}}</div></section>
                <section><h2>Experience</h2>@{{experience}}</section>
                <section><h2>Projects</h2>@{{projects}}</section>
                <section><h2>Education</h2>@{{education}}</section>
            </div>`;
    }
    function resumeAccentStyle(color) {
        const accent = String(color || '');
        if (!/^#[0-9a-f]{6}$/i.test(accent)) return '';

        return `<style>
            .resume-sheet-preview, .resume-maker-preview { --primary: ${accent}; }
            .resume-sheet-preview .tpl-resume {
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
            .resume-sheet-preview .tpl-role-head strong {
                color: var(--primary) !important;
                border-color: var(--primary) !important;
            }
            .resume-sheet-preview .tpl-badge {
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
            .resume-sheet-preview .tpl-resume h2[style*="background"] {
                background: var(--primary) !important;
                color: #fff !important;
            }
            .resume-sheet-preview .tpl-rule *,
            .resume-sheet-preview .tpl-accentbox header > div *,
            .resume-sheet-preview .tpl-two aside *,
            .resume-sheet-preview .tpl-carded header *,
            .resume-sheet-preview .tpl-band header *,
            .resume-sheet-preview .tpl-resume > header[style*="background"] *,
            .resume-sheet-preview .tpl-resume h2[style*="background"] {
                color: #fff !important;
                border-color: rgba(255,255,255,0.45) !important;
            }
            .resume-sheet-preview .tpl-profile-img {
                display: block;
                max-width: 150px;
                max-height: 150px;
                margin-bottom: 15px;
                border: 2px solid var(--primary);
                border-radius: 8px;
            }
        </style>`;
    }
    function renderTemplateHtml(template) {
        syncLegacy();
        let output = String(template?.html || '');
        if (!hasResumePlaceholders(output)) {
            output = editableTemplateShell();
        }
        const hasProjectsToken = /\{\{\s*projects\s*\}\}/.test(output) || output.includes('[[projects]]');
        const values = {
            name:         esc(fullName() || state.name || 'Alex Johnson'),
            last_name:    esc(state.last_name || ''),
            job_title:    esc(state.job_title || 'Senior Product Designer'),
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
            profile_image: state.profile_image ? `<img src="${state.profile_image}" class="tpl-profile-img" style="width:100%; height:100%; object-fit:cover;">` : '',
            profile_image_url: state.profile_image || '',
        };
        Object.entries(values).forEach(([k, v]) => { output = replaceToken(output, k, v); });

        /* ── Blade Compatibility Layer (for AI-generated templates) ── */
        if (output.includes('$resume')) {
            // Replace simple tokens
            const bladeMap = {
                'name': values.name, 'last_name': values.last_name, 'job_title': values.job_title, 'email': values.email, 'mobile': values.mobile, 
                'location': values.location, 'summary': values.summary,
                'linkedin': state.social_links[0] || '', 'github': state.social_links[1] || ''
            };
            Object.entries(bladeMap).forEach(([k, v]) => {
                const re = new RegExp('\\{\\{\\s*\\$resume\\[[\'"]' + k + '[\'"]\\]\\s*\\}\\}', 'g');
                output = output.replace(re, v);
            });
            // Replace loops with pre-rendered blocks
            output = output.replace(/@@foreach\s*\(\s*\$resume\[['"]experience['"]\][^)]*\)([\s\S]*?)@@endforeach/g, values.experience);
            output = output.replace(/@@foreach\s*\(\s*\$resume\[['"]skills['"]\][^)]*\)([\s\S]*?)@@endforeach/g, values.skills);
            output = output.replace(/@@foreach\s*\(\s*\$resume\[['"]education['"]\][^)]*\)([\s\S]*?)@@endforeach/g, values.education);
            output = output.replace(/@@foreach\s*\(\s*\$resume\[['"]projects['"]\][^)]*\)([\s\S]*?)@@endforeach/g, values.projects);
            // Cleanup remaining Blade tags
            output = output.replace(/@@if\s*\([^)]*\)|@@endif|@@else/g, '');
        }

        // Auto-fix: If user has uploaded an image, replace common placeholder URLs in the template
        if (state.profile_image) {
            output = output.replace(/src=["']https?:\/\/(?:i\.pravatar\.cc|via\.placeholder\.com|placehold\.co|placehold\.it|avatar\.iran\.liara\.run|ui-avatars\.com)\/[^"']*["']/gi, `src="${state.profile_image}"`);
            
            // Also try to update any img tag with id profile-pic or profile-img if it hasn't been updated yet
            if (!output.includes(state.profile_image)) {
                output = output.replace(/(id=["'](?:profile-pic|profile-img|cv-img|cv-profile-img|user-photo)["'][^>]*src=["'])([^"']*)(["'])/gi, `$1${state.profile_image}$3`);
            }
            
            // Auto-replace base64 images or blob urls (often generated by PDF to HTML conversions)
            if (!output.includes(state.profile_image)) {
                output = output.replace(/src=["'](data:image\/[^;]+;base64,[^"']+|blob:[^"']+)["']/gi, `src="${state.profile_image}"`);
            }
        }

        if (!hasProjectsToken) {
            const section = `<h2>Projects</h2>${values.projects}`;
            const lastDiv = output.lastIndexOf('</div>');
            output = lastDiv !== -1 ? output.slice(0, lastDiv) + section + output.slice(lastDiv) : output + section;
        }

        /* Prevent template <style> tags from breaking the app body layout */
        output = output.replace(/<style[^>]*>([\s\S]*?)<\/style>/gi, function(match, css) {
            let scoped = css.replace(/(^|\}|\s)body\s*\{/gi, '$1.resume-sheet-preview {');
            // Also scope html selectors just in case
            scoped = scoped.replace(/(^|\}|\s)html\s*\{/gi, '$1.resume-sheet-preview {');
            return `<style>${scoped}</style>`;
        });

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
                <div style="display: flex; gap: 25px; align-items: flex-start; border-bottom:2.5px solid ${primaryColor}; padding-bottom:15px; margin-bottom:20px;">
                    ${state.profile_image ? `<div style="width:85px; height:85px; border-radius:12px; overflow:hidden; flex-shrink:0; background:#f8fafc; border:1px solid #e2e8f0;"><img src="${state.profile_image}" style="width:100%; height:100%; object-fit:cover;"></div>` : ''}
                    <div style="flex:1;">
                        <h1 style="margin:0 0 6px;font-size:26px;letter-spacing:.5px;text-transform:uppercase;color:${primaryColor};">${esc(fullName() || 'Your Name')}</h1>
                        ${state.job_title ? `<p style="margin:0 0 4px;font-size:12px;color:#334155;">${esc(state.job_title)}</p>` : ''}
                        ${header ? `<p style="margin:0;font-size:11px;color:#4b5563;">${esc(header)}</p>` : ''}
                        ${socials ? `<p style="margin:2px 0 0;font-size:11px;color:#4b5563;">${esc(socials)}</p>` : ''}
                    </div>
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
        if (typeof templateGrid === 'undefined' || !templateGrid || !(templatePopup?.classList.contains('open') || templatePopup?.classList.contains('visible'))) return;

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
        if ($('cv-last-name')) $('cv-last-name').value = state.last_name;
        if ($('cv-job-title')) $('cv-job-title').value = state.job_title;
        $('cv-email').value    = state.email;
        $('cv-mobile').value   = state.mobile;
        $('cv-location').value = state.location;
        $('cv-social').value   = state.social_links.join(', ');
        $('cv-summary').value  = state.summary;
        $('cv-skills').value   = state.skills.join(', ');

        /* ── Profile Image ── */
        const template = templates[selectedTemplateId];
        const imgSection = $('image-upload-section');
        if (imgSection) {
            imgSection.style.display = template?.has_image ? 'block' : 'none';
            const imgPreview = $('cv-image-preview');
            const placeholder = $('cv-image-placeholder');
            if (state.profile_image) {
                if (imgPreview) { imgPreview.src = state.profile_image; imgPreview.classList.remove('hidden'); }
                if (placeholder) placeholder.classList.add('hidden');
            } else {
                if (imgPreview) imgPreview.classList.add('hidden');
                if (placeholder) placeholder.classList.remove('hidden');
            }
        }

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
                    <div class="rich-text-wrapper" style="margin-top: 0.3rem;">
                        <div class="rich-text-toolbar">
                            <button type="button" class="rt-btn"><b>B</b></button>
                            <button type="button" class="rt-btn"><i>I</i></button>
                            <button type="button" class="rt-btn"><u>U</u></button>
                            <button type="button" class="ai-gen-btn" onclick="generateAIText('experience', this.closest('.rich-text-wrapper').querySelector('textarea'), this)">
                                <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 2zM10 15a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 15zM15 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 0115 10zM6.5 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 016.5 10zM14.61 5.39a.75.75 0 010 1.06l-1.06 1.06a.75.75 0 01-1.06-1.06l1.06-1.06a.75.75 0 011.06 0zM7.51 12.49a.75.75 0 010 1.06l-1.06 1.06a.75.75 0 11-1.06-1.06l1.06-1.06a.75.75 0 011.06 0zM14.61 14.61a.75.75 0 01-1.06 0l-1.06-1.06a.75.75 0 011.06-1.06l1.06 1.06a.75.75 0 010 1.06zM7.51 7.51a.75.75 0 01-1.06 0l-1.06-1.06a.75.75 0 011.06-1.06l1.06 1.06a.75.75 0 010 1.06z"/></svg>
                                Generate with AI
                            </button>
                        </div>
                        <textarea class="rp-input rp-input-ta" data-k="points" rows="4" placeholder="• Led a team of 5 engineers&#10;• Reduced load time by 40%">${esc(e.points.join('\n'))}</textarea>
                    </div>
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
    window.applyResumeData = applyResumeData;

    /* ═══════════════════════════════════════════
       STEP NAVIGATION — uses .rp-step-tab
    ═══════════════════════════════════════════ */
    const stepNames = ["", "Contacts", "Experience", "Education", "Skills", "Summary", "Finalize"];
    function goToStep(step) {
        currentStep = Math.max(1, Math.min(step, 6));

        /* Update tab indicators */
        document.querySelectorAll('.rp-step-tab').forEach(tab => {
            const t = parseInt(tab.dataset.step);
            tab.classList.remove('active', 'completed', 'done');
            if (t === currentStep) tab.classList.add('active');
            else if (t < currentStep) tab.classList.add('done');
        });

        /* Show/hide step panels */
        document.querySelectorAll('.rp-step-pane, .rp-step-content').forEach(panel => {
            panel.classList.toggle('active', parseInt(panel.dataset.step) === currentStep);
        });

        /* Handle step-6 layout shift */
        const builderView = $('rp-builder-view');
        if (builderView) {
            if (currentStep === 6) builderView.classList.add('step-6-active');
            else builderView.classList.remove('step-6-active');
        }

        /* Update footer buttons */
        const btnNext = $('btn-next');
        const btnBack = $('btn-back');
        if (btnBack) {
            btnBack.style.visibility = currentStep > 1 ? 'visible' : 'hidden';
        }
        if (btnNext) {
            if (currentStep < 6) {
                btnNext.textContent = `Next: ${stepNames[currentStep + 1]}`;
                btnNext.style.display = 'block';
            } else {
                btnNext.style.display = 'none'; // hide on finalize
            }
        }
    }

    /* Wire step tabs — allow clicking back to completed steps */
    document.querySelectorAll('.rp-step-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const target = parseInt(tab.dataset.step);
            if (target <= currentStep || (target > currentStep && document.querySelector(`.rp-step-tab[data-step="${target-1}"]`).classList.contains('completed'))) {
                goToStep(target);
            }
        });
    });

    /* Wire next / prev buttons */
    $('btn-next')?.addEventListener('click', () => goToStep(currentStep + 1));
    $('btn-back')?.addEventListener('click', () => goToStep(currentStep - 1));

    $('edit-resume')?.addEventListener('click', () => goToStep(1));

    /* ── Event listeners ── */
    document.querySelectorAll('.cv-field').forEach(input => {
        input.addEventListener('input', e => {
            const f = e.target.dataset.field;
            state[f] = ['skills','social_links'].includes(f) ? toList(e.target.value) : e.target.value;
            renderTemplatePreview();
        });
    });

    /* Image upload listener */
    $('cv-image-input')?.addEventListener('change', e => {
        const file = e.target.files?.[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) { alert('Image too large. Max 2MB.'); return; }
        const reader = new FileReader();
        reader.onload = (rev) => {
            state.profile_image = rev.target.result;
            renderEditor();
            renderTemplatePreview();
        };
        reader.readAsDataURL(file);
    });

    $('remove-image-btn')?.addEventListener('click', () => {
        state.profile_image = '';
        renderEditor();
        renderTemplatePreview();
    });

    $('cv-image-overlay')?.addEventListener('click', () => {
        $('cv-image-input')?.click();
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

        if (btn.id === 'add-exp-btn' || btn.id === 'add-exp') { state.experience.push({ company:'', role:'', period:'', points:[''] }); renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'add-edu-btn' || btn.id === 'add-edu') { state.education.push(''); renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'add-project-btn' || btn.id === 'add-project') { state.projects.push({ name:'', description:'' }); renderEditor(); renderTemplatePreview(); }

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
    saveBtnEl?.addEventListener('click', async () => {
        try {
            syncLegacy();
            const btnOrigText = saveBtnEl.textContent;
            saveBtnEl.textContent = 'Saving…';
            saveBtnEl.style.opacity = '0.7';
            const url      = app.dataset.updateUrl || app.dataset.storeUrl;
            const method   = app.dataset.updateUrl ? 'patch' : 'post';
            const templateId = templateIdEl?.value || null;
            const payload  = app.dataset.updateUrl
                ? { resume: state, template_id: templateId }
                : { source, template_id: templateId, resume: state };
            const res = await axios[method](url, payload);
            if (res.data.redirect) { window.location.href = res.data.redirect; return; }
            if (res.data.resume?.id) savedResumeId = res.data.resume.id;
            saveBtnEl.textContent = '✓ Saved';
            setTimeout(() => { saveBtnEl.textContent = btnOrigText; saveBtnEl.style.opacity = '1'; }, 2000);
            
            // Redirect to download
            if (app.dataset.authenticated === '1' && app.dataset.downloadRequiresPlan === '1') {
                window.openPlanDownloadModal?.(); return;
            }
            if (savedResumeId) { window.location.href = `/resume/${savedResumeId}/download/pdf`; return; }
        } catch (err) {
            alert(err.response?.data?.message || 'Save failed.');
            saveBtnEl.textContent = 'Save & Download PDF';
            saveBtnEl.style.opacity = '1';
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
            profile_image: '<div style="width:80px; height:80px; background:#e2e8f0; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#94a3b8;"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg></div>',
        };

        let filled = hasResumePlaceholders(html) ? html : editableTemplateShell();
        Object.entries(sampleData).forEach(([k, v]) => {
            filled = filled.replace(new RegExp('\\{\\{\\s*' + k + '\\s*\\}\\}', 'g'), v);
            filled = filled.split('[[' + k + ']]').join(v);
        });

        inner.innerHTML = filled;
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

        templatePopup.classList.add('open', 'visible');
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

            applyColorSelection('');
            setTimeout(() => templatePopup.classList.remove('open', 'visible'), 160);
        });
    }

    changeTemplateBtn?.addEventListener('click', openTemplatePopup);
    closePopupBtn?.addEventListener('click', () => templatePopup?.classList.remove('open', 'visible'));
    $('close-template-btn')?.addEventListener('click', () => templatePopup?.classList.remove('open', 'visible'));
    templatePopup?.addEventListener('click', e => { if (e.target === templatePopup) templatePopup.classList.remove('open', 'visible'); });

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
