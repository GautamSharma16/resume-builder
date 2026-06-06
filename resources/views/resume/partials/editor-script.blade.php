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
    const rich = (v) => esc(v)
        .replace(/&lt;(\/?)(strong|b|em|i|u)&gt;/gi, '<$1$2>')
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/__(.*?)__/g, '<u>$1</u>');
    const AI_FAILURE_MESSAGE = "We're unable to process your request right now. Please try again after some time.";
    const RESUME_UPLOAD_SUCCESS_MESSAGE = 'Resume uploaded. Our AI has autofilled your details; please review them thoroughly before downloading.';
    const friendlyAiMessage = (message) => {
        return AI_FAILURE_MESSAGE;
    };
    const showAiFailureAlert = () => {
        window.alert(AI_FAILURE_MESSAGE);
    };

    const notify = (message, type = 'info') => {
        if (statusEl) {
            statusEl.textContent = message;
            statusEl.style.color = type === 'error' ? '#b91c1c' : '#2563eb';
        }
        let toast = document.getElementById('resume-maker-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'resume-maker-toast';
            toast.style.cssText = 'position:fixed;right:24px;bottom:24px;z-index:10050;max-width:min(360px,calc(100vw - 32px));border-radius:16px;padding:13px 16px;font-weight:800;font-size:13px;box-shadow:0 18px 45px rgba(15,23,42,.18);transition:opacity .2s ease,transform .2s ease;opacity:0;transform:translateY(10px);';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.style.background = type === 'error' ? '#fef2f2' : '#eff6ff';
        toast.style.color = type === 'error' ? '#b91c1c' : '#1d4ed8';
        toast.style.border = type === 'error' ? '1px solid #fecaca' : '1px solid #bfdbfe';
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateY(0)';
        });
        clearTimeout(toast._timer);
        clearTimeout(toast._removeTimer);
        toast._timer = setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(10px)';
            toast._removeTimer = setTimeout(() => {
                if (toast.parentNode) toast.parentNode.removeChild(toast);
            }, 260);
        }, type === 'error' ? 4800 : 3600);
    };
    window.resumeMakerNotify = notify;
    const toList = (v) => String(v).split(',').map(x => x.trim()).filter(Boolean);
    const normalizeDedupeKey = (value = '') => String(value || '')
        .toLowerCase()
        .replace(/<[^>]*>/g, ' ')
        .replace(/https?:\/\/(?:www\.)?/g, '')
        .replace(/\s+/g, ' ')
        .trim();
    const uniqueByNormalized = (items = [], keyFn = (item) => item) => {
        const seen = new Set();
        return ensureArray(items).filter((item) => {
            const key = normalizeDedupeKey(keyFn(item));
            if (!key || seen.has(key)) return false;
            seen.add(key);
            return true;
        });
    };
    const normalizeUrl = (value = '') => {
        const url = String(value || '').trim();
        if (!url) return '';
        if (/^(?:https?:\/\/|mailto:|tel:)/i.test(url)) return url;
        if (/^(?:www\.|[a-z0-9-]+\.)+[a-z]{2,}(?:\/[^\s,;]*)?$/i.test(url)) return 'https://' + url;
        return url;
    };
    const isPortfolioUrl = (url = '') => /(?:portfolio|behance\.net|dribbble\.com|github\.io|about\.me|\.me\/|personal|website)/i.test(String(url || ''));
    const validSocials = (items) => ensureArray(items)
        .map(String)
        .map(item => item.trim())
        .filter(item => item && !/(linkedin\.com\/in\/(?:alex|you)|github\.com\/(?:alex|you))/i.test(item));
    const ensureArray = (v) => Array.isArray(v) ? v : [];
    const toText = (value) => {
        if (value == null) return '';
        if (typeof value === 'string') return value;
        if (Array.isArray(value)) return value.map(toText).map(v => String(v).trim()).filter(Boolean).join('\n');
        if (typeof value === 'object') {
            if (typeof value.description === 'string') return value.description;
            if (typeof value.text === 'string') return value.text;
            return Object.values(value).map(toText).map(v => String(v).trim()).filter(Boolean).join(' ');
        }
        return String(value);
    };
    const listify = (value, splitRegex = /[\n,;|]+/) => {
        if (Array.isArray(value)) return value.map(v => toText(v)).map(v => v.trim()).filter(Boolean);
        if (value == null) return [];
        const text = toText(value).replace(/[•●▪◦]/g, '\n');
        return String(text).split(splitRegex).map(v => v.trim()).filter(Boolean);
    };
    const looksLikeCompany = (line = '') => /\b(inc|llc|ltd|pvt|technologies|technology|solutions|systems|labs|corp)\b/i.test(line);
    const looksLikeUrl = (line = '') => /(?:https?:\/\/|www\.|[a-z0-9-]+\.[a-z]{2,})/i.test(line);
    const looksLikeProjectUrl = (line = '') => /^(?:https?:\/\/|www\.)\S+\.\S+$/i.test(String(line || '').trim())
        || /^(?:github|gitlab|bitbucket)\.com\/[^\s]+$/i.test(String(line || '').trim());
    const looksLikeTechStack = (line = '') => /\b(react|node|js|javascript|typescript|spring|boot|java|php|laravel|python|django|flask|postgres|postgresql|mysql|mongodb|sql|html|css|tailwind|bootstrap|api|rest|express|next\.?js|vue|angular)\b/i.test(line);
    const looksLikeLocation = (line = '') => /\b(remote|on[- ]?site|hybrid|india|usa|uk|delhi|mumbai|pune|bengaluru|bangalore|hyderabad|chennai|kolkata)\b/i.test(line);
    const looksLikeRole = (line = '') => /\b(intern|developer|engineer|manager|analyst|designer|consultant|lead|architect)\b/i.test(line);
    const looksLikePeriod = (line = '') => /\b(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec|\d{4}|present)\b/i.test(line);
    const stitchProjectStrings = (items = []) => {
        const lines = items.map(v => String(v || '').trim()).filter(Boolean);
        const stitched = [];
        let current = null;
        for (const line of lines) {
            const isTitle = / - | • |\| /.test(line) && line.length <= 130;
            if (!current || isTitle) {
                if (current) stitched.push(current);
                current = { name: line, tech_stack: '', link: '', description: '' };
                continue;
            }
            if (!current.link && looksLikeUrl(line)) {
                current.link = line;
                continue;
            }
            current.description = [current.description, line].filter(Boolean).join(' ');
        }
        if (current) stitched.push(current);
        return stitched;
    };
    const normalizeExperienceEntry = (entry) => {
        const base = typeof entry === 'string'
            ? { company: '', role: '', period: '', points: listify(entry, /\n+/) }
            : {
                company: String(entry?.company ?? entry?.organization ?? entry?.workExperienceOrganization ?? entry?.employer ?? entry?.company_name ?? ''),
                role: String(entry?.role ?? entry?.title ?? entry?.jobTitle ?? entry?.position ?? entry?.job_title ?? ''),
                period: String(entry?.period ?? entry?.duration ?? entry?.dates ?? entry?.workExperienceDateRange ?? ''),
                points: listify(entry?.points ?? entry?.highlights ?? entry?.responsibilities ?? entry?.description ?? entry?.details ?? entry?.summary ?? '', /[\n•]+/),
            };

        if ((!base.role || !base.company || !base.period) && base.points.length) {
            const kept = [];
            base.points.forEach((line) => {
                if (!base.company && looksLikeCompany(line)) { base.company = line; return; }
                if (!base.role && looksLikeRole(line)) {
                    base.role = line;
                    if (!base.period && looksLikePeriod(line)) {
                        const m = line.match(/((?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)?\s*\d{4}\s*[-–]\s*(?:present|(?:jan|feb|mar|apr|may|jun|jul|aug|sep|oct|nov|dec)?\s*\d{4}))/i);
                        if (m) {
                            base.period = m[1].trim();
                            base.role = line.replace(m[1], '').trim();
                        }
                    }
                    return;
                }
                if (looksLikeUrl(line) || looksLikeLocation(line)) return;
                kept.push(line);
            });
            base.points = kept;
        }

        return base;
    };
    const normalizeProjectEntry = (project) => {
        const item = typeof project === 'string'
            ? { name: project, tech_stack: '', link: '', description: '' }
            : {
                name: String(project?.name ?? ''),
                tech_stack: String(project?.tech_stack ?? project?.tech ?? ''),
                link: String(project?.link ?? project?.url ?? ''),
                description: toText(project?.description ?? project?.highlights ?? listify(project?.points, /\n+/).join('\n') ?? ''),
            };

        if (item.link && !looksLikeProjectUrl(item.link)) {
            if (looksLikeTechStack(item.link)) {
                item.tech_stack = [item.tech_stack, item.link].map(v => String(v || '').trim()).filter(Boolean).join(', ');
            } else {
                item.description = [item.description, item.link].map(v => String(v || '').trim()).filter(Boolean).join(' ');
            }
            item.link = '';
        }

        return item;
    };
    const normalizeNamedItemEntries = (items) => (Array.isArray(items) ? items : listify(items, /[\n,;|]+/)).map(item =>
        typeof item === 'string' ? { name: item, description: '' } : { name: String(item?.name ?? item?.title ?? item?.label ?? ''), description: String(item?.description ?? item?.details ?? '') }
    );

    /* ── Constants ── */
    const A4_W = 794;
    const A4_H = 1123;

    /* ── State ── */
    const defaults = { name:'', last_name:'', job_title:'', designation:'', desired_job_role:'', email:'', mobile:'', location:'', linkedin:'', portfolio:'', link:'', github:'', social_links:[], contact:'', address:'', summary:'', skills:[], experience:[], education:[], projects:[], certifications:[], languages:[], additional_information:[], achievements:[], primary_color: '', primary_color_customized: false, profile_image: '' };
    const state = Object.assign({}, defaults, readJson('resume-initial-json', {}));

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('primary_color')) {
        state.primary_color = urlParams.get('primary_color');
        state.primary_color_customized = true;
    }

    const templates = readJson('resume-templates-json', {});
    let source = app.dataset.initialSource === 'upload' ? 'upload' : 'manual';
    window.getResumeMakerSource = () => source;
    window.setResumeMakerSource = (src) => {
        source = src === 'upload' ? 'upload' : 'manual';
    };
    let selectedTemplateId = app.dataset.selectedTemplate || '';
    let currentStep = 1;
    let savedResumeId = app.dataset.resumeId || null;

    /* ── DOM refs ── */
    const cvPreviewEl     = $('cv-preview');
    const expEditorEl     = $('rp-exp-editor') || $('exp-editor');
    const eduEditorEl     = $('rp-edu-editor') || $('edu-editor');
    const projectEditorEl = $('project-editor');
    const templateIdEl    = $('template-id');
    const saveBtnEl       = $('save-cv-btn') || $('save-cv');
    const statusEl        = $('cv-status') || { textContent: '', style: {} };
    const autofillFileEl  = $('resume-autofill-file');
    const autofillBtnEl   = $('resume-autofill-button');
    const autofillStatusEl= $('resume-autofill-status');
    const fileNameEl      = $('rp-file-name');
    const zoomInEl        = $('preview-zoom-in');
    const zoomOutEl       = $('preview-zoom-out');
    const zoomLvlEl       = $('preview-zoom-level');

    /* ── Pagination state ── */
    let previewZoom = 100;
    let previewPage = 1;
    let previewTotalPages = 1;
    let uploadInProgress = false;
    let uploadRequestToken = 0;

    if (!cvPreviewEl || !templateIdEl || !expEditorEl || !eduEditorEl || !saveBtnEl) {
        console.error('Resume maker: missing required DOM elements.'); return;
    }

    /* ── Normalise ── */
    const educationToObject = (item) => {
        if (typeof item === 'string') {
            const parts = item.split(',').map(part => part.trim()).filter(Boolean);
            return {
                degree: parts[0] || '',
                stream: parts.length > 3 ? parts[1] : '',
                institution: parts.length > 2 ? parts.slice(1, -1).join(', ') : (parts[1] || ''),
                year: parts.length > 1 ? parts[parts.length - 1] : '',
            };
        }
        if (!item || typeof item !== 'object') {
            return { degree: '', stream: '', institution: '', year: '' };
        }
        return {
            degree: String(item.degree ?? item.educationAccreditation ?? item.course ?? ''),
            stream: String(item.stream ?? item.field ?? (Array.isArray(item.educationMajor) ? item.educationMajor.join(', ') : (item.educationMajor ?? '')) ?? item.specialization ?? ''),
            institution: String(item.institution ?? item.educationOrganization ?? item.school ?? item.university ?? item.college ?? ''),
            year: String(item.year ?? item.duration ?? item.period ?? item.educationDateRange ?? ''),
        };
    };

    const looksLikeJobTitle = (value = '') => {
        const text = String(value || '').trim();
        if (!text || text.length > 72) return false;
        return /\b(software|developer|engineer|manager|designer|analyst|consultant|lead|architect|intern|specialist|director|officer|executive|programmer|administrator|coordinator|associate|senior|junior|trainee|devops|fullstack|full\s*stack|front\s*end|back\s*end|data\s*scientist|product\s*manager|sales|marketing|recruiter|relationship|territory|admin|qa|tester|sde|mern|mean|stack)\b/i.test(text);
    };

    const educationLooksLikeExperienceBullet = (edu = {}) => {
        const degree = String(edu.degree || '').trim();
        const stream = String(edu.stream || '').trim();
        const institution = String(edu.institution || '').trim();
        const hay = [degree, stream, institution].filter(Boolean).join(' ').toLowerCase();
        if (!hay) return false;
        if (/^\s*[-•*]\s*/.test(degree)) return true;
        if (/\b(responsible for|accountable for|key deliverables|market exploration|sales targets)\b/i.test(hay)
            && !/\b(b\.?tech|bachelor|master|bsc|msc|mba|university|college|school|institute|cgpa|gpa)\b/i.test(hay)) return true;
        if (institution && !degree && !stream
            && /\b(responsible|sales|manager|deliverables|exploration|development|services|clients)\b/i.test(institution)
            && !/\b(university|college|school|institute|academy)\b/i.test(institution)) return true;
        return /\b(developed|implemented|built|designed|created|maintained|deployed|optimized|led|managed)\b/i.test(hay)
            && !/\b(b\.?tech|bachelor|master|bsc|msc|mba|bca|mca|diploma|ph\.?d|degree|university|college|school|institute|cgpa|gpa|12th|10th)\b/i.test(hay);
    };

    const splitMergedRoleField = (entry = {}) => {
        const role = String(entry.role || '').trim();
        const company = String(entry.company || '').trim();
        if (company || !role) return entry;
        const m = role.match(/^([A-Z][A-Za-z]+(?:\s+[A-Z][A-Za-z]+){0,3})\s+((?:Senior\s+|Junior\s+|Lead\s+)?(?:MERN|MEAN|Full[\s-]*Stack|Software|Frontend|Front[\s-]*End|Backend|Back[\s-]*End|Web|Mobile|DevOps|React|Node\.?js|Java|Python|PHP|\.NET).{3,90})$/i)
            || role.match(/^([A-Z][A-Z\s]{4,40})\s+((?:Senior\s+|Junior\s+)?[A-Za-z][A-Za-z\s\/\-]{4,60}(?:Developer|Engineer|Manager|Analyst|Designer|Tester|Architect|Consultant|Intern))$/);
        if (m) {
            entry.role = m[2].trim();
        }
        return entry;
    };

    const isContactHeaderExperience = (entry = {}) => {
        const blob = [entry.company, entry.role, ...(entry.points || [])].map(v => String(v || '')).join(' ').toLowerCase();
        return /\b(contact|apartment|apartments|flat|golden view|email|phone|mob)\b/.test(blob)
            && !/\b(ltd|limited|pvt|inc|developer|engineer|manager|\d{4})\b/.test(blob);
    };

    const splitIdentityFields = (name = '', lastName = '', designation = '') => {
        let first = String(name || '').trim();
        let last = String(lastName || '').trim();
        let title = String(designation || '').trim();

        if (!title && looksLikeJobTitle(last) && !last.includes(' ')) {
            title = last;
            last = '';
        }
        if (last.includes(' ')) {
            const words = last.split(/\s+/);
            for (let i = 1; i < words.length; i++) {
                const tail = words.slice(i).join(' ');
                if (looksLikeJobTitle(tail)) {
                    if (!title) title = tail;
                    last = words.slice(0, i).join(' ');
                    break;
                }
            }
        }
        if (!last && first.includes(' ')) {
            const words = first.split(/\s+/);
            for (let i = 1; i < words.length; i++) {
                const tail = words.slice(i).join(' ');
                if (looksLikeJobTitle(tail)) {
                    if (!title) title = tail;
                    first = words.slice(0, i).join(' ');
                    last = '';
                    break;
                }
            }
            if (!last && first.includes(' ')) {
                const parts = first.split(/\s+/);
                first = parts.shift() || '';
                last = parts.join(' ');
            }
        }

        return { name: first, last_name: last, designation: title, job_title: title };
    };

    const extractProfileUrls = (rawSocials = [], existing = {}) => {
        const links = validSocials(rawSocials).map(String).map(s => s.trim()).filter(Boolean);
        let linkedin = String(existing.linkedin ?? '').trim();
        let github = String(existing.github ?? '').trim();
        let portfolio = normalizeUrl(String(existing.portfolio ?? existing.link ?? existing.website ?? '').trim());
        const pull = (re, current) => {
            if (current) return current;
            const idx = links.findIndex(u => re.test(u));
            return idx === -1 ? '' : links.splice(idx, 1)[0];
        };
        linkedin = pull(/linkedin\.com/i, linkedin);
        github = pull(/github\.com/i, github);
        portfolio = pull(/(?:behance\.net|dribbble\.com|portfolio|github\.io|about\.me|\.me\/)/i, portfolio);
        if (!portfolio) {
            const idx = links.findIndex(u => !/linkedin\.com/i.test(u));
            if (idx !== -1) portfolio = links.splice(idx, 1)[0];
        }
        return { linkedin: normalizeUrl(linkedin), github: normalizeUrl(github), portfolio: normalizeUrl(portfolio), social_links: [] };
    };

    const normalise = (r = {}) => {
        const profiles = extractProfileUrls(r.social_links, r);
        const identity = splitIdentityFields(
            String(r.name ?? ''),
            String(r.last_name ?? ''),
            String(r.designation ?? r.job_title ?? '')
        );
        return {
        name:         identity.name,
        last_name:    identity.last_name,
        job_title:    identity.designation,
        designation:  identity.designation,
        desired_job_role: '',
        email:        String(r.email ?? ''),
        mobile:       String(r.mobile ?? r.contact ?? ''),
        location:     String(r.location ?? r.address ?? ''),
        linkedin:     String(r.linkedin ?? '').trim() || profiles.linkedin,
        portfolio:    String(r.portfolio ?? r.link ?? '').trim() || profiles.portfolio,
        link:         String(r.link ?? r.portfolio ?? '').trim() || profiles.portfolio,
        github:       String(r.github ?? '').trim() || profiles.github,
        social_links: [],
        contact:      String(r.contact ?? ''),
        address:      String(r.address ?? ''),
        summary:      String(r.summary ?? ''),
        skills:       uniqueByNormalized(listify(r.skills, /[\n,;|]+/)),
        experience:   ensureArray(r.experience)
            .map(normalizeExperienceEntry)
            .map(splitMergedRoleField)
            .filter(e => !isContactHeaderExperience(e))
            .filter((e, i, arr) => i === arr.findIndex(x => normalizeDedupeKey([x.company, x.role, x.period, ...(x.points || [])].join(' ')) === normalizeDedupeKey([e.company, e.role, e.period, ...(e.points || [])].join(' ')))),
        education: ensureArray(r.education)
            .map(educationToObject)
            .filter(e => (e.degree || e.stream || e.institution || e.year) && !educationLooksLikeExperienceBullet(e))
            .filter((e, i, arr) => i === arr.findIndex(x => normalizeDedupeKey([x.degree, x.stream, x.institution, x.year].join(' ')) === normalizeDedupeKey([e.degree, e.stream, e.institution, e.year].join(' ')))),
        projects:  (() => {
            const rawProjects = ensureArray(r.projects);
            if (rawProjects.every(p => typeof p === 'string') && rawProjects.length > 3) {
                return uniqueByNormalized(stitchProjectStrings(rawProjects), p => [p.name, p.tech_stack, p.link, p.description].join(' '));
            }
            return uniqueByNormalized(rawProjects.map(normalizeProjectEntry), p => [p.name, p.tech_stack, p.link, p.description].join(' '));
        })(),
        certifications: uniqueByNormalized(normalizeNamedItemEntries(r.certifications ?? r.certificates), c => [c.name, c.description].join(' ')),
        languages: uniqueByNormalized(ensureArray(r.languages).map(l =>
            typeof l === 'string' ? { name: l, level: '' } : { name: String(l?.name ?? l?.language ?? ''), level: String(l?.level ?? l?.proficiency ?? '') }
        ), l => [l.name, l.level].join(' ')),
        additional_information: uniqueByNormalized(normalizeNamedItemEntries(r.additional_information ?? r.additionalInformation), a => [a.name, a.description].join(' ')),
        achievements: uniqueByNormalized(normalizeNamedItemEntries(r.achievements), a => [a.name, a.description].join(' ')),
        primary_color: String(r.primary_color ?? ''),
        primary_color_customized: Boolean(r.primary_color_customized ?? (r.primary_color && r.primary_color !== '#2563eb')),
        profile_image: String(r.profile_image ?? ''),
    };
    };

    Object.assign(state, normalise(state));

    const ensureDefaults = () => {
        if (!state.experience.length) state.experience.push({ company:'', role:'', period:'', points:[''] });
        else state.experience.forEach((e) => { if (!Array.isArray(e.points) || !e.points.length) e.points = ['']; });
        if (!state.education.length)  state.education.push({ degree:'', stream:'', institution:'', year:'' });
        if (!state.projects.length)   state.projects.push({ name:'', tech_stack:'', link:'', description:'' });
        if (!state.certifications.length) state.certifications.push({ name:'', description:'' });
        if (!state.languages.length) state.languages.push({ name:'', level:'' });
        state.additional_information = ensureArray(state.additional_information);
        state.achievements = ensureArray(state.achievements);
    };
    ensureDefaults();

    /* ── Default template ── */
    const templateKeys = Object.keys(templates);
    selectedTemplateId = selectedTemplateId || templateIdEl.value || templateKeys[0] || '';
    if (selectedTemplateId) templateIdEl.value = selectedTemplateId;

    /* ── Legacy sync ── */
    function syncLegacy() {
        state.contact = [state.email, state.mobile, state.linkedin, state.github, state.portfolio || state.link]
            .filter(Boolean)
            .join(' | ');
        state.address = state.location || '';
    }

    function fullName() {
        return [state.name, state.last_name].map(part => String(part || '').trim()).filter(Boolean).join(' ');
    }
    function renderAll() {
        renderEditor();
        renderTemplatePreview();
    }

    /* ── Render helpers ── */
    function renderSkills() {
        return state.skills.map(s => `<span class="tpl-badge">${esc(s)}</span>`).join('');
    }
    function renderExperience() {
        return state.experience.map(e => {
            if (!e.company && !e.role && !e.period && !e.points.some(Boolean)) return '';
            const isHtml = e.points.length === 1 && /<[a-z][\s\S]*>/i.test(e.points[0]);
            const pts = isHtml
                ? e.points[0]
                : (e.points.filter(Boolean).length ? `<ul>${e.points.filter(Boolean).map(p => `<li>${rich(p)}</li>`).join('')}</ul>` : '');
            return `<div class="tpl-role"><div class="tpl-role-head"><strong>${esc(e.role)}</strong><span>${esc(e.period || e.duration || '')}</span></div><p>${esc(e.company)}</p>${pts}</div>`;
        }).join('');
    }
    function renderList(arr) {
        const items = arr.filter(i => {
            if (!i) return false;
            if (typeof i === 'string') return i.trim() !== '';
            return Object.values(i).some(v => String(v ?? '').trim() !== '');
        }).map(i => {
            if (typeof i === 'string') return `<li>${esc(i)}</li>`;
            if ('degree' in i || 'institution' in i || 'stream' in i || 'year' in i) {
                const title = [i.degree, i.stream].map(v => String(v || '').trim()).filter(Boolean).join(' - ');
                const meta = [
                    i.institution,
                    i.cgpa ? `CGPA: ${i.cgpa}` : '',
                    i.year || i.duration || i.period || ''
                ].map(v => String(v || '').trim()).filter(Boolean).join(', ');
                return `<li>${title ? `<strong>${esc(title)}</strong>` : ''}${meta ? `<span class="tpl-description">${esc(meta)}</span>` : ''}</li>`;
            }
            if ('level' in i || 'proficiency' in i || 'language' in i) {
                const name = String(i.name || i.language || '').trim();
                const level = String(i.level || i.proficiency || '').trim();
                return `<li>${name ? `<strong>${esc(name)}</strong>` : ''}${level ? `<span class="tpl-description">${esc(level)}</span>` : ''}</li>`;
            }
            const name = esc(i?.name || '');
            const tech = esc(i?.tech_stack || i?.tech || '');
            const link = esc(i?.link || i?.url || '');
            const desc = i?.description ? (/<[a-z][\s\S]*>/i.test(i.description) ? i.description : esc(i.description)) : '';
            const title = link ? `<a href="${link}" target="_blank" rel="noopener">${name}</a>` : `<strong>${name}</strong>`;
            const meta = [tech, link].filter(Boolean).join(' | ');
            return `<li>${title}${meta ? `<span class="tpl-description">${meta}</span>` : ''}${desc ? `<span class="tpl-description">${desc}</span>` : ''}</li>`;
        }).join('');
        return items ? `<ul>${items}</ul>` : '';
    }
    function replaceToken(html, key, value) {
        return html
            .replace(new RegExp('\\{\\{\\s*' + key + '\\s*\\}\\}', 'g'), value)
            .split('[[' + key + ']]').join(value);
    }
    function applyHandlebarsIfBlocks(html, values) {
        let output = String(html || '');
        const pattern = /\{\{#if\s+([a-z0-9_.]+)\s*\}\}([\s\S]*?)(?:\{\{else\}\}([\s\S]*?))?\{\{\/if\}\}/gi;
        let loops = 0;
        while (pattern.test(output) && loops < 40) {
            loops += 1;
            output = output.replace(pattern, (_, key, truthy, falsy) => {
                const value = values?.[key];
                const isTruthy = !(value === null || value === undefined || value === '' || value === false || value === 'null');
                return isTruthy ? (truthy || '') : (falsy || '');
            });
        }
        return output;
    }
    function hasResumePlaceholders(html) {
        const tokens = ['name', 'last_name', 'job_title', 'designation', 'email', 'mobile', 'location', 'contact', 'address', 'summary', 'skills', 'experience', 'education', 'projects', 'certifications', 'certificates', 'languages', 'additional_information', 'achievements', 'social_links', 'linkedin', 'portfolio', 'link', 'profile_image', 'profile_image_url', 'profile_image_tag', 'photo'];
        return new RegExp('\\{\\{\\s*(' + tokens.join('|') + ')\\s*\\}\\}', 'i').test(html)
            || new RegExp('\\[\\[\\s*(' + tokens.join('|') + ')\\s*\\]\\]', 'i').test(html)
            || /\{\{#if\s+[a-z0-9_.]+\s*\}\}/i.test(html)
            || /\{\{\s*\$resume|\{\{\s*\$(name|last_name|job_title|designation|email|mobile|location|summary|skills|experience|education|projects|social_links|linkedin|portfolio|link|profile_image)|@@foreach\s*\(\s*\$resume/i.test(html);
    }
    function editableTemplateShell() {
        return `
            <div class="tpl-resume tpl-uploaded-editable" style="font-family:Inter,Arial,sans-serif;color:#172033;padding:42px;line-height:1.45;">
                <table style="width:100%; border-bottom:3px solid var(--primary, #2563eb); padding-bottom:16px; margin-bottom:22px; border-collapse: collapse;">
                    <tr>
                        <td style="vertical-align: top; text-align: left;">
                            <h1 style="margin:0 0 6px; font-size:30px; letter-spacing:.04em; text-transform:uppercase; color:var(--primary, #2563eb); text-align: left;">@{{name}}</h1>
                            <p style="margin:0 0 4px; font-size:14px; color:#334155; text-align: left;">@{{job_title}}</p>
                            <p style="margin:0; font-size:12px; color:#475569; text-align: left;">@{{email}} | @{{mobile}} | @{{location}}</p>
                            <p style="margin:4px 0 0; font-size:12px; color:#475569; text-align: left;">@{{social_links}}</p>
                        </td>
                        <td style="width: 100px; vertical-align: top; text-align: right;">@{{profile_image}}</td>
                    </tr>
                </table>
                <section><h2>Professional Summary</h2><div style="margin-bottom:7px">@{{summary}}</div></section>
                <section><h2>Skills</h2><div class="tpl-badges">@{{skills}}</div></section>
                <section><h2>Experience</h2>@{{experience}}</section>
                <section><h2>Projects</h2>@{{projects}}</section>
                <section><h2>Certifications</h2>@{{certifications}}</section>
                <section><h2>Achievements</h2>@{{achievements}}</section>
                <section><h2>Languages</h2>@{{languages}}</section>
                <section><h2>Additional Information</h2>@{{additional_information}}</section>
                <section><h2>Education</h2>@{{education}}</section>
            </div>`;
    }

    function resumeAccentStyle(color) {
        const accent = String(color || '');
        if (!/^#[0-9a-f]{6}$/i.test(accent)) return '';
        return `<style>
            .resume-sheet-preview, .resume-maker-preview { --primary: ${accent}; }
            .resume-sheet-preview .tpl-resume { border-color: var(--primary) !important; }
            .resume-sheet-preview .tpl-resume h1,
            .resume-sheet-preview .tpl-resume h2,
            .resume-sheet-preview .tpl-resume h3,
            .resume-sheet-preview .tpl-resume a,
            .resume-sheet-preview .tpl-role-head strong { color: var(--primary) !important; border-color: var(--primary) !important; }
            .resume-sheet-preview .tpl-badge { background: var(--primary) !important; border-color: var(--primary) !important; color: #fff !important; }
            .resume-sheet-preview .tpl-rule,
            .resume-sheet-preview .tpl-accentbox header > div,
            .resume-sheet-preview .tpl-two aside,
            .resume-sheet-preview .tpl-carded header,
            .resume-sheet-preview .tpl-band header,
            .resume-sheet-preview .tpl-resume > header[style*="background"],
            .resume-sheet-preview .tpl-resume h2[style*="background"] { background: var(--primary) !important; color: #fff !important; }
            .resume-sheet-preview .tpl-profile-img { display:block; max-width:150px; max-height:150px; margin-bottom:15px; border:2px solid var(--primary); border-radius:8px; }
            .resume-sheet-preview .tpl-resume h1,
            .resume-sheet-preview .tpl-resume h2,
            .resume-sheet-preview .tpl-resume h3 { color: var(--primary) !important; }
            .resume-sheet-preview .tpl-resume hr { border-top: 2px solid var(--primary) !important; }
        </style>`;
    }

    function renderTemplateHtml(template) {
        syncLegacy();
        let output = String(template?.html || '');
        output = stripDemoResumeContent(output);
        if (!hasResumePlaceholders(output)) {
            output = editableTemplateShell();
        }
        const hasProjectsToken = /\{\{\s*projects\s*\}\}/.test(output) || output.includes('[[projects]]');
        const hasAchievementsToken = /\{\{\s*achievements\s*\}\}/.test(output) || output.includes('[[achievements]]');
        const hasAdditionalInformationToken = /\{\{\s*additional_information\s*\}\}/.test(output) || output.includes('[[additional_information]]');
        const values = {
            name:         esc(fullName() || state.name || ''),
            last_name:    esc(state.last_name || ''),
            job_title:    esc(state.job_title || ''),
            designation:  esc(state.designation || state.job_title || ''),
            email:        esc(state.email || ''),
            mobile:       esc(state.mobile || ''),
            location:     esc(state.location || ''),
            contact:      esc(state.contact || [state.email, state.mobile].filter(Boolean).join(' | ')),
            address:      esc(state.address || state.location || ''),
            summary:      state.summary ? (/<[a-z][\s\S]*>/i.test(state.summary) ? state.summary : rich(state.summary)) : '',
            social_links: esc([state.linkedin, state.github, state.portfolio || state.link].filter(Boolean).join(' | ')),
            linkedin:     esc(state.linkedin || ''),
            portfolio:    esc(state.portfolio || state.link || ''),
            link:         esc(state.link || state.portfolio || ''),
            skills:       state.skills.length ? renderSkills() : '',
            experience:   state.experience.some(e => e.company || e.role || e.period || e.points.some(Boolean)) ? renderExperience() : '',
            education:    state.education.some(e => e?.degree || e?.stream || e?.institution || e?.year) ? renderList(state.education) : '',
            projects:     state.projects.some(p => p?.name || typeof p === 'string') ? renderList(state.projects) : '',
            certifications: state.certifications.some(c => c?.name || typeof c === 'string') ? renderList(state.certifications) : '',
            certificates: state.certifications.some(c => c?.name || typeof c === 'string') ? renderList(state.certifications) : '',
            languages: state.languages.some(l => l?.name || typeof l === 'string') ? renderList(state.languages) : '',
            additional_information: state.additional_information.some(a => a?.name || typeof a === 'string') ? renderList(state.additional_information) : '',
            achievements: state.achievements.some(a => a?.name || typeof a === 'string') ? renderList(state.achievements) : '',
            profile_image: state.profile_image || '',
            profile_image_url: state.profile_image || '',
            profile_image_tag: state.profile_image ? `<img src="${state.profile_image}" class="tpl-profile-img" style="width:100%; height:100%; object-fit:cover;">` : '',
            photo: state.profile_image || '',
        };
        output = applyHandlebarsIfBlocks(output, values);
        Object.entries(values).forEach(([k, v]) => { output = replaceToken(output, k, v); });

        ['projects', 'certifications', 'certificates', 'languages', 'additional_information', 'achievements', 'experience', 'education'].forEach(key => {
            if (!values[key] || values[key] === '<ul></ul>' || values[key] === '') {
                const re = new RegExp('<(h2|h3|h4|div)[^>]*>[^<]*?' + key + '[^<]*?<\\/\\1>\\s*(?:<[^>]+>\\s*)*?(\\{\\{\\s*' + key + '\\s*\\}\\}|\\[\\[' + key + '\\]\\])', 'gi');
                output = output.replace(re, '');
                output = replaceToken(output, key, '');
            }
        });

        if (!values.certifications && !values.certificates) {
            output = output
                .replace(/<h[1-6][^>]*>\s*certifications?\s*<\/h[1-6]>/gi, '')
                .replace(/<section[^>]*>\s*<\/section>/gi, '');
        }

        if (output.includes('$resume')) {
            const bladeMap = {
                'name': values.name, 'last_name': values.last_name, 'job_title': values.job_title,
                'designation': values.designation, 'email': values.email, 'mobile': values.mobile,
                'location': values.location, 'summary': values.summary,
                'linkedin': values.linkedin || '',
                'portfolio': values.portfolio, 'link': values.link, 'github': state.github || '',
                'certifications': values.certifications, 'certificates': values.certificates, 'languages': values.languages,
                'additional_information': values.additional_information, 'achievements': values.achievements
            };
            Object.entries(bladeMap).forEach(([k, v]) => {
                const re = new RegExp('\\{\\{\\s*\\$resume\\[[\'"]' + k + '[\'"]\\]\\s*\\}\\}', 'g');
                output = output.replace(re, v);
            });
            output = output.replace(/@@foreach\s*\(\s*\$resume\[['"]experience['"]\][^)]*\)([\s\S]*?)@@endforeach/g, values.experience);
            output = output.replace(/@@foreach\s*\(\s*\$resume\[['"]skills['"]\][^)]*\)([\s\S]*?)@@endforeach/g, values.skills);
            output = output.replace(/@@foreach\s*\(\s*\$resume\[['"]education['"]\][^)]*\)([\s\S]*?)@@endforeach/g, values.education);
            output = output.replace(/@@foreach\s*\(\s*\$resume\[['"]projects['"]\][^)]*\)([\s\S]*?)@@endforeach/g, values.projects);
            output = output.replace(/@@foreach\s*\(\s*\$resume\[['"]certifications['"]\][^)]*\)([\s\S]*?)@@endforeach/g, values.certifications);
            output = output.replace(/@@foreach\s*\(\s*\$resume\[['"]certificates['"]\][^)]*\)([\s\S]*?)@@endforeach/g, values.certificates);
            output = output.replace(/@@foreach\s*\(\s*\$resume\[['"]languages['"]\][^)]*\)([\s\S]*?)@@endforeach/g, values.languages);
            output = output.replace(/@@if\s*\([^)]*\)|@@endif|@@else/g, '');
        }

        if (state.profile_image) {
            output = output.replace(/src=["']https?:\/\/(?:i\.pravatar\.cc|via\.placeholder\.com|placehold\.co|placehold\.it|avatar\.iran\.liara\.run|ui-avatars\.com)\/[^"']*["']/gi, `src="${state.profile_image}"`);
            if (!output.includes(state.profile_image)) {
                output = output.replace(/(id=["'](?:profile-pic|profile-img|cv-img|cv-profile-img|user-photo)["'][^>]*src=["'])([^"']*)(["'])/gi, `$1${state.profile_image}$3`);
            }
            if (!output.includes(state.profile_image)) {
                output = output.replace(/src=["'](data:image\/[^;]+;base64,[^"']+|blob:[^"']+)["']/gi, `src="${state.profile_image}"`);
            }
        }

        if (!hasProjectsToken && values.projects) {
            const section = `<h2>Projects</h2>${values.projects}`;
            const lastDiv = output.lastIndexOf('</div>');
            output = lastDiv !== -1 ? output.slice(0, lastDiv) + section + output.slice(lastDiv) : output + section;
        }

        if (!hasAchievementsToken && values.achievements && !/<(h2|h3|h4|div|strong|b)[^>]*>[^<]*?Achievements[^<]*?<\/\1>/i.test(output)) {
            const section = `<h2>Achievements</h2>${values.achievements}`;
            const certMatch = output.match(/(<h[1-6][^>]*>\s*Certifications?\s*<\/h[1-6]>[\s\S]*?<ul[^>]*>[\s\S]*?<\/ul>)/i);
            if (certMatch && typeof certMatch.index === 'number') {
                const insertAt = certMatch.index + certMatch[0].length;
                output = output.slice(0, insertAt) + section + output.slice(insertAt);
            } else {
                const lastDiv = output.lastIndexOf('</div>');
                output = lastDiv !== -1 ? output.slice(0, lastDiv) + section + output.slice(lastDiv) : output + section;
            }
        }

        if (!hasAdditionalInformationToken && values.additional_information && !/<(h2|h3|h4|div|strong|b)[^>]*>[^<]*?Additional Information[^<]*?<\/\1>/i.test(output)) {
            const section = `<h2>Additional Information</h2>${values.additional_information}`;
            const lastDiv = output.lastIndexOf('</div>');
            output = lastDiv !== -1 ? output.slice(0, lastDiv) + section + output.slice(lastDiv) : output + section;
        }

        // Remove empty sections entirely so clear/delete reflects instantly in preview.
        output = output
            .replace(/<section[^>]*>\s*<h[1-6][^>]*>\s*(Professional Summary|Summary)\s*<\/h[1-6]>\s*(?:<div[^>]*>\s*)?<\/section>/gi, '')
            .replace(/<section[^>]*>\s*<h[1-6][^>]*>\s*(Experience|Education|Projects|Certifications|Certificates|Languages|Achievements|Additional Information)\s*<\/h[1-6]>\s*(?:<ul[^>]*>\s*<\/ul>|<div[^>]*>\s*<\/div>|<p[^>]*>\s*<\/p>|)\s*<\/section>/gi, '')
            .replace(/<h[1-6][^>]*>\s*(Experience|Education|Projects|Certifications|Certificates|Languages|Achievements|Additional Information)\s*<\/h[1-6]>\s*(?:<ul[^>]*>\s*<\/ul>|<div[^>]*>\s*<\/div>|<p[^>]*>\s*<\/p>)/gi, '');

        output = output.replace(/<style[^>]*>([\s\S]*?)<\/style>/gi, function(match, css) {
            let scoped = css.replace(/(^|\}|\s)body\s*\{/gi, '$1.resume-sheet-preview {');
            scoped = scoped.replace(/(^|\}|\s)html\s*\{/gi, '$1.resume-sheet-preview {');
            return `<style>${scoped}</style>`;
        });

        output = resumeAccentStyle(state.primary_color_customized ? state.primary_color : '') + output;
        return output;
    }

    /* ── Preview ── */
    function stripDemoResumeContent(html) {
        const hasRealData = fullName() || state.email || state.mobile || state.summary || state.skills.length
            || state.experience.some(e => e.company || e.role || e.period || e.points.some(Boolean))
            || state.education.some(e => e?.degree || e?.stream || e?.institution || e?.year)
            || state.projects.some(p => p?.name || p?.description || p?.tech_stack || p?.tech || p?.link);

        if (!hasRealData) return html;

        return String(html || '')
            .replace(/Companyvista Inc/gi, '')
            .replace(/my\.companyvista\.com/gi, '')
            .replace(/MERN Stack Developer Intern/gi, '')
            .replace(/Feb 2024\s*[–-]\s*Present/gi, '')
            .replace(/TrimNet\s*[—-]\s*URL Shortener/gi, '')
            .replace(/https?:\/\/trimnet\.vercel\.app/gi, '')
            .replace(/Frontend:\s*React\.js[^<\n]*/gi, '')
            .replace(/Backend:\s*Node\.js[^<\n]*/gi, '')
            .replace(/Databases:\s*MongoDB[^<\n]*/gi, '')
            .replace(/Tools\s*&\s*Technologies:[^<\n]*/gi, '')
            .replace(/AI\s*&\s*Modern Dev:[^<\n]*/gi, '');
    }

    function renderBasicPreview() {
        const primaryColor = state.primary_color || '#2563eb';
        const header = [state.email, state.mobile, state.location].filter(Boolean).join(' · ');
        const socials = [state.linkedin, state.github, state.portfolio || state.link].filter(Boolean).join(' · ');
        const projectsHtml = state.projects.filter(Boolean).map(p => {
            const name = typeof p === 'string' ? p : (p?.name || '');
            const desc = typeof p === 'string' ? '' : (p?.description || '');
            return desc
                ? `<li style="font-size:11px;"><strong>${esc(name)}</strong><br><span style="color:#6b7280;font-size:10.5px;">${esc(desc)}</span></li>`
                : `<li style="font-size:11px;">${esc(name)}</li>`;
        }).join('');
        cvPreviewEl.innerHTML = `
            <div class="resume-preview-stage">
            <div class="resume-sheet-preview">
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
                ${state.summary ? `<h2 style="color:${primaryColor};font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Summary</h2><p style="font-size:11.5px;margin:0 0 14px;">${rich(state.summary)}</p>` : ''}
                ${state.skills.length ? `<h2 style="color:${primaryColor};font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Skills</h2><p style="font-size:11px;margin:0 0 14px;">${esc(state.skills.join(', '))}</p>` : ''}
                ${state.experience.some(e=>e.company||e.role) ? `<h2 style="color:${primaryColor};font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 8px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Experience</h2>${state.experience.map(e => e.company||e.role ? `<div style="margin-bottom:12px;"><strong style="font-size:11.5px;color:${primaryColor};">${esc(e.role)}</strong>${e.period?` <span style="float:right;color:#6b7280;font-size:10px;">${esc(e.period)}</span>`:''}<br><span style="color:#4b5563;font-size:10.5px;">${esc(e.company)}</span>${(e.points.length === 1 && /<[a-z][\s\S]*>/i.test(e.points[0])) ? `<div style="font-size:11px;margin:4px 0 0 14px;">${e.points[0]}</div>` : `<ul style="margin:4px 0 0 14px;padding:0;">${e.points.filter(Boolean).map(p=>`<li style="font-size:11px;margin-bottom:2px;">${rich(p)}</li>`).join('')}</ul>`}</div>` : '').join('')}` : ''}
                ${state.education.some(e => e?.degree || e?.stream || e?.institution || e?.year) ? `<h2 style="color:${primaryColor};font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Education</h2><ul style="margin:0 0 14px 14px;padding:0;">${state.education.filter(e => e?.degree || e?.stream || e?.institution || e?.year).map(e=>`<li style="font-size:11px;"><strong>${esc([e.degree, e.stream].filter(Boolean).join(' - '))}</strong>${[e.institution, e.year].filter(Boolean).length ? `<br><span style="color:#6b7280;font-size:10.5px;">${esc([e.institution, e.year].filter(Boolean).join(', '))}</span>` : ''}</li>`).join('')}</ul>` : ''}
                ${projectsHtml ? `<h2 style="color:${primaryColor};font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Projects</h2><ul style="margin:0 0 14px 14px;padding:0;">${projectsHtml}</ul>` : ''}
                ${state.certifications.some(c => c?.name || typeof c === 'string') ? `<h2 style="color:${primaryColor};font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Certifications</h2><ul style="margin:0 0 14px 14px;padding:0;">${state.certifications.map(c => `<li style="font-size:11px;"><strong>${esc(typeof c === 'string' ? c : (c?.name || ''))}</strong>${(typeof c !== 'string' && c?.description) ? `<br><span style="color:#6b7280;font-size:10.5px;">${esc(c.description)}</span>` : ''}</li>`).join('')}</ul>` : ''}
                ${state.achievements.some(a => a?.name || typeof a === 'string') ? `<h2 style="color:${primaryColor};font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Achievements</h2><ul style="margin:0 0 14px 14px;padding:0;">${state.achievements.map(a => `<li style="font-size:11px;"><strong>${esc(typeof a === 'string' ? a : (a?.name || ''))}</strong>${(typeof a !== 'string' && a?.description) ? `<br><span style="color:#6b7280;font-size:10.5px;">${esc(a.description)}</span>` : ''}</li>`).join('')}</ul>` : ''}
                ${state.languages.some(l => l?.name || typeof l === 'string') ? `<h2 style="color:${primaryColor};font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Languages</h2><ul style="margin:0 0 14px 14px;padding:0;">${state.languages.map(l => `<li style="font-size:11px;"><strong>${esc(typeof l === 'string' ? l : (l?.name || ''))}</strong>${(typeof l !== 'string' && l?.level) ? `<span style="color:#6b7280;font-size:10.5px;"> - ${esc(l.level)}</span>` : ''}</li>`).join('')}</ul>` : ''}
                ${state.additional_information.some(a => a?.name || typeof a === 'string') ? `<h2 style="color:${primaryColor};font-size:10px;text-transform:uppercase;letter-spacing:.12em;margin:0 0 6px;border-bottom:1px solid #d1fae5;padding-bottom:3px;">Additional Information</h2><ul style="margin:0 0 0 14px;padding:0;">${state.additional_information.map(a => `<li style="font-size:11px;"><strong>${esc(typeof a === 'string' ? a : (a?.name || ''))}</strong>${(typeof a !== 'string' && a?.description) ? `<br><span style="color:#6b7280;font-size:10.5px;">${esc(a.description)}</span>` : ''}</li>`).join('')}</ul>` : ''}
            </div>
            </div></div>`;
    }

    function renderTemplatePreview() {
        syncLegacy();
        updateResumeScore();
        if (!selectedTemplateId || !templates[selectedTemplateId]) { renderBasicPreview(); scheduleUpdateScale(); return; }
        if (!templates[selectedTemplateId]?.html) {
            cvPreviewEl.innerHTML = `
                <div class="resume-preview-stage resume-preview--loading">
                    <div class="resume-sheet-preview" style="display:flex;align-items:center;justify-content:center;padding:32px;color:#64748b;">
                        <span style="display:inline-flex;align-items:center;gap:.6rem;">
                            <span aria-hidden="true" style="width:16px;height:16px;display:inline-block;border:2px solid rgba(100,116,139,0.35);border-top-color:#334155;border-radius:999px;animation:rmSpin .8s linear infinite;"></span>
                            Loading template…
                        </span>
                    </div>
                </div>
            `;
            if (!document.getElementById('rm-spin-style')) {
                const st = document.createElement('style');
                st.id = 'rm-spin-style';
                st.textContent = '@keyframes rmSpin{to{transform:rotate(360deg)}}';
                document.head.appendChild(st);
            }
            ensureTemplateHtmlLoaded(String(selectedTemplateId)).then(() => {
                if (String(selectedTemplateId) === String(templates?.[String(selectedTemplateId)]?.id ?? selectedTemplateId)) {
                    renderTemplatePreview();
                }
            });
            return;
        }
        const output = renderTemplateHtml(templates[selectedTemplateId]);
        /* Reset to page 1 whenever content refreshes */
        previewPage = 1;
        cvPreviewEl.innerHTML = `<div class="resume-preview-stage"><div class="resume-sheet-preview">${output}</div></div>`;
        scheduleUpdateScale();
        if (templatePopup?.classList.contains('open')) {
            updateTemplateLargePreview(templatePreviewActiveId || selectedTemplateId, false);
        }
    }

    /* ── Schedule scale update after browser paints ── */
    function scheduleUpdateScale() {
        requestAnimationFrame(() => {
            updatePreviewScale();
            /* Second pass after images / web-fonts settle */
            setTimeout(updatePreviewScale, 120);
        });
    }

    /* ════════════════════════════════════════════════════════════════
       PREVIEW SCALE + PAGINATION  (fully rewritten)
    ════════════════════════════════════════════════════════════════ */
    function updatePreviewScale() {
        const viewport = $('preview-viewport');
        const preview  = $('cv-preview');
        if (!viewport || !preview) return;

        const containerWidth = viewport.clientWidth || viewport.offsetWidth;
        if (containerWidth === 0) { setTimeout(updatePreviewScale, 150); return; }
        const isCompactPreview = containerWidth <= 900;

        /* Loading spinner state — show at natural size */
        if (preview.querySelector('.resume-preview--loading')) {
            preview.style.cssText = 'width:100%;min-height:140px;max-width:none;transform:none;margin:0 auto;display:block;overflow:hidden;';
            updatePreviewPageControls(1, 1);
            return;
        }

        const stage = preview.querySelector('.resume-preview-stage');
        const sheet = preview.querySelector('.resume-sheet-preview');

        /* Scale only when the A4 page cannot fit in the actual scroll viewport. */
        const viewportStyles = window.getComputedStyle(viewport);
        const horizontalPadding =
            (parseFloat(viewportStyles.paddingLeft) || 0) +
            (parseFloat(viewportStyles.paddingRight) || 0);
        const safeGutter = isCompactPreview ? 8 : 12;
        const availableWidth = Math.max(280, containerWidth - horizontalPadding - safeGutter);
        const scale = Math.min(1, Math.max(0.28, availableWidth / A4_W));

        /* Fallback when no .resume-sheet-preview exists */
        if (!sheet) {
            preview.style.transformOrigin = 'top left';
            preview.style.width           = Math.ceil(A4_W * scale) + 'px';
            preview.style.transform       = `scale(${scale})`;
            preview.style.margin          = '0 auto';
            preview.style.overflow        = 'hidden';
            updatePreviewPageControls(1, 1);
            return;
        }

        /* ── Measure real content height ──────────────────────────
           We need the sheet at its natural un-transformed size.
           Temporarily reset any transform, measure, then restore.
        ─────────────────────────────────────────────────────────── */
        const prevTransform = sheet.style.transform;
        const prevWidth = sheet.style.width;
        const prevMinHeight = sheet.style.minHeight;
        const prevHeight = sheet.style.height;
        const prevMarginTop = sheet.style.marginTop;
        sheet.style.transform = 'none';
        sheet.style.width = A4_W + 'px';
        sheet.style.minHeight = '0';
        sheet.style.height = 'auto';
        sheet.style.marginTop = '0';
        const rawH = Math.max(
            sheet.scrollHeight || 0,
            sheet.getBoundingClientRect().height || 0,
            A4_H
        );
        const pageHeight = A4_H;
        const pageTolerance = 2;
        sheet.style.transform = prevTransform;
        sheet.style.width = prevWidth;
        sheet.style.minHeight = prevMinHeight;
        sheet.style.height = prevHeight;
        sheet.style.marginTop = prevMarginTop;

        /* Page count */
        previewTotalPages = Math.max(1, Math.ceil(Math.max(0, rawH - pageTolerance) / pageHeight));
        previewPage       = Math.min(Math.max(1, previewPage), previewTotalPages);

        /* Y offset for current page (un-scaled px) */
        const offsetY = (previewPage - 1) * pageHeight;

        /* Scaled dimensions of a single A4 page */
        const scaledW = Math.round(A4_W * scale);
        const scaledH = Math.round(pageHeight * scale);

        /* ── Style inner sheet ── */
        sheet.style.width           = A4_W + 'px';
        sheet.style.maxWidth        = 'none';
        sheet.style.minHeight       = rawH + 'px';
        sheet.style.boxSizing       = 'border-box';
        sheet.style.transformOrigin = 'top left';
        sheet.style.transform       = `scale(${scale})`;
        sheet.style.marginTop       = `-${Math.round(offsetY * scale)}px`;
        sheet.style.overflow        = 'visible';
        sheet.style.display         = 'block';

        /* ── Style stage — clips to exactly one A4 page ── */
        if (stage) {
            stage.style.width      = scaledW + 'px';
            stage.style.minWidth   = '0';
            stage.style.height     = scaledH + 'px';
            stage.style.overflow   = 'hidden';           /* KEY clip */
            stage.style.position   = 'relative';
            stage.style.margin     = '0';
            stage.style.maxWidth   = 'none';
            stage.style.flexShrink = '0';
            stage.style.boxSizing  = 'border-box';
            stage.style.display    = 'block';
        }

        /* ── Style outer preview container — locked to one page height ── */
        preview.style.transform      = 'none';
        preview.style.width          = scaledW + 'px';
        preview.style.maxWidth       = 'none';
        preview.style.height         = scaledH + 'px';
        preview.style.minHeight      = scaledH + 'px';
        preview.style.margin         = '0 auto';
        preview.style.overflow       = 'hidden';
        preview.style.display        = 'flex';
        preview.style.justifyContent = 'center';
        preview.style.alignItems     = 'flex-start';
        preview.style.boxSizing      = 'border-box';

        updatePreviewPageControls(previewPage, previewTotalPages);

        if (isCompactPreview) {
            viewport.scrollLeft = 0;
        }
    }

    function updatePreviewPageControls(currentPage, totalPages) {
        const navEl   = $('preview-page-nav');
        const labelEl = $('preview-page-label');
        const prevBtn = $('preview-page-prev');
        const nextBtn = $('preview-page-next');
        /* legacy range input kept for any external code that reads it */
        const rangeEl = $('preview-page-range');

        if (!navEl) return;

        const safeTotal   = Math.max(1, totalPages  || 1);
        const safeCurrent = Math.min(Math.max(1, currentPage || 1), safeTotal);
        const show        = safeTotal > 1;

        navEl.style.display = show ? 'flex' : 'none';
        navEl.classList.toggle('active', show);

        if (!show) { previewPage = 1; previewTotalPages = 1; return; }

        if (labelEl) labelEl.textContent = `${safeCurrent} / ${safeTotal}`;
        if (rangeEl) { rangeEl.max = String(safeTotal); rangeEl.value = String(safeCurrent); }
        if (prevBtn) prevBtn.disabled = safeCurrent <= 1;
        if (nextBtn) nextBtn.disabled = safeCurrent >= safeTotal;
    }

    function goToPage(page) {
        previewPage = Math.min(Math.max(1, page), previewTotalPages);
        updatePreviewScale();
    }

    /* ── Wire page-nav buttons ── */
    $('preview-page-prev')?.addEventListener('click', () => goToPage(previewPage - 1));
    $('preview-page-next')?.addEventListener('click', () => goToPage(previewPage + 1));
    $('preview-page-range')?.addEventListener('input', function () { goToPage(parseInt(this.value, 10) || 1); });

    window.addEventListener('resize', updatePreviewScale);

    function updateResumeScore() {
        const checks = [
            Boolean(fullName()),
            Boolean(state.job_title),
            Boolean(state.email),
            Boolean(state.mobile),
            Boolean(state.location),
            Boolean(state.portfolio || state.link || state.linkedin || state.github),
            state.skills.length >= 4,
            String(state.summary || '').trim().split(/\s+/).filter(Boolean).length >= 45,
            state.experience.some(e => e.company && e.role && e.points.filter(Boolean).length >= 2),
            state.education.some(e => e?.degree && e?.institution),
            state.projects.some(p => p?.name || typeof p === 'string'),
        ];
        const score = Math.max(8, Math.round((checks.filter(Boolean).length / checks.length) * 100));
        document.querySelectorAll('[data-resume-score]').forEach(el => {
            el.textContent = `${score}%`;
            el.classList.toggle('low', score < 55);
            el.classList.toggle('mid', score >= 55 && score < 80);
        });
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
        updateTemplateLargePreview(selectedId);
        requestAnimationFrame(updateTemplateThumbScales);
    }

    /* ── Editor render ── */
    function renderEditor() {
        $('cv-name').value     = state.name || '';
        if ($('cv-last-name')) $('cv-last-name').value = state.last_name || '';
        if ($('cv-designation')) $('cv-designation').value = state.designation || state.job_title || '';
        $('cv-email').value    = state.email || '';
        $('cv-mobile').value   = state.mobile || '';
        $('cv-location').value = state.location || '';
        if ($('cv-linkedin')) $('cv-linkedin').value = state.linkedin || '';
        if ($('cv-github')) $('cv-github').value = state.github || '';
        if ($('cv-portfolio')) $('cv-portfolio').value = state.portfolio || state.link || '';
        if ($('cv-summary')) {
            $('cv-summary').value = state.summary || '';
            if (typeof tinymce !== 'undefined' && tinymce.get('cv-summary')) {
                tinymce.get('cv-summary').setContent(state.summary || '');
            }
        }
        $('cv-skills').value   = (state.skills || []).join(', ') || '';
        applyDynamicFormVisibility();

        /* Profile Image */
        const template = templates[selectedTemplateId];
        const imgSection = $('image-upload-section');
        if (imgSection) {
            const templateHtml = String(template?.html || '');
            const usesImageToken = /\{\{\s*(profile_image|profile_image_url|profile_image_tag|photo)\s*\}\}|\[\[\s*(profile_image|profile_image_url|profile_image_tag|photo)\s*\]\]|\{\{#if\s+photo\s*\}\}/i.test(templateHtml);
            imgSection.classList.toggle('hidden', !(template?.has_image || usesImageToken));
            const imgPreview = $('cv-image-preview');
            const placeholder = $('cv-image-placeholder');
            if (state.profile_image) {
                if (imgPreview) { imgPreview.src = state.profile_image; imgPreview.classList.remove('hidden'); }
                if (placeholder) placeholder.classList.add('hidden');
                if ($('remove-image-btn')) $('remove-image-btn').classList.remove('hidden');
            } else {
                if (imgPreview) imgPreview.classList.add('hidden');
                if (placeholder) placeholder.classList.remove('hidden');
                if ($('remove-image-btn')) $('remove-image-btn').classList.add('hidden');
            }
        }

        /* Experience cards */
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
                    <button type="button" class="ai-gen-btn" style="align-self: flex-start; margin-top: 0.3rem;" onclick="generateAIText('experience', this.closest('.rp-entry-field').querySelector('textarea'), this)">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 2zM10 15a.75.75 0 01.75.75v1.5a.75.75 0 01-1.5 0v-1.5A.75.75 0 0110 15zM15 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 0115 10zM6.5 10a.75.75 0 01-.75.75h-1.5a.75.75 0 010-1.5h1.5A.75.75 0 016.5 10zM14.61 5.39a.75.75 0 010 1.06l-1.06 1.06a.75.75 0 01-1.06-1.06l1.06-1.06a.75.75 0 011.06 0zM7.51 12.49a.75.75 0 010 1.06l-1.06 1.06a.75.75 0 11-1.06-1.06l1.06-1.06a.75.75 0 011.06 0zM14.61 14.61a.75.75 0 01-1.06 0l-1.06-1.06a.75.75 0 011.06-1.06l1.06 1.06a.75.75 0 010 1.06zM7.51 7.51a.75.75 0 01-1.06 0l-1.06-1.06a.75.75 0 011.06-1.06l1.06 1.06a.75.75 0 010 1.06z"/></svg>
                        Generate with AI
                    </button>
                    <div style="margin-top: 0.5rem;">
                        <textarea class="rp-input rp-input-ta" data-k="points" rows="4" placeholder="• Led a team of 5 engineers&#10;• Reduced load time by 40%">${esc(e.points.join('\n'))}</textarea>
                    </div>
                </div>
                <button type="button" data-remove-exp class="rp-entry-remove">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    Remove
                </button>
            </div>`).join('');

        /* Education rows */
        eduEditorEl.innerHTML = state.education.map((e, i) => `
            <div class="rp-entry-card" data-edu="${i}">
                <div class="rp-entry-row">
                    <div class="rp-entry-field">
                        <label class="rp-entry-label">Degree / Qualification</label>
                        <input class="rp-input" data-k="degree" value="${esc(e?.degree || '')}" placeholder="e.g. B.Sc.">
                    </div>
                    <div class="rp-entry-field">
                        <label class="rp-entry-label">Stream / Field</label>
                        <input class="rp-input" data-k="stream" value="${esc(e?.stream || '')}" placeholder="e.g. Computer Science">
                    </div>
                </div>
                <div class="rp-entry-row">
                    <div class="rp-entry-field">
                        <label class="rp-entry-label">University / Institution</label>
                        <input class="rp-input" data-k="institution" value="${esc(e?.institution || '')}" placeholder="e.g. MIT">
                    </div>
                    <div class="rp-entry-field">
                        <label class="rp-entry-label">Year / Duration</label>
                        <input class="rp-input" data-k="year" value="${esc(e?.year || '')}" placeholder="e.g. 2019 or 2017 - 2021">
                    </div>
                </div>
                <button type="button" data-remove-edu class="rp-entry-remove">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                    Remove
                </button>
            </div>`).join('');

        /* Project cards */
        if (projectEditorEl) {
            projectEditorEl.innerHTML = state.projects.map((p, i) => `
                <div class="rp-entry-card" data-project="${i}">
                    <div class="rp-entry-row">
                        <div class="rp-entry-field">
                            <label class="rp-entry-label">Project Title</label>
                            <input class="rp-input" data-k="name" value="${esc(typeof p === 'string' ? p : (p?.name || ''))}" placeholder="e.g. Open-source Markdown editor">
                        </div>
                        <div class="rp-entry-field">
                            <label class="rp-entry-label">Tech Stack</label>
                            <input class="rp-input" data-k="tech_stack" value="${esc(p?.tech_stack || p?.tech || '')}" placeholder="e.g. React, Node.js, PostgreSQL">
                        </div>
                    </div>
                    <div class="rp-entry-row">
                        <div class="rp-entry-field" style="grid-column: 1 / -1;">
                            <label class="rp-entry-label">Project Link</label>
                            <input class="rp-input" data-k="link" value="${esc(p?.link || p?.url || '')}" placeholder="https://github.com/you/project">
                        </div>
                    </div>
                    <div class="rp-entry-field">
                        <label class="rp-entry-label">Description <span class="rp-entry-hint">(impact, responsibilities, outcomes)</span></label>
                        <textarea class="rp-input rp-input-ta rich-ta" data-k="description" rows="4" placeholder="Built with React and Node.js. Reduced build time by 30%.">${esc(p?.description || '')}</textarea>
                    </div>
                    <button type="button" data-remove-project class="rp-entry-remove">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        Remove
                    </button>
                </div>`).join('');
        }

        /* Certification cards */
        const certEditorEl = $('certification-editor');
        if (certEditorEl) {
            certEditorEl.innerHTML = state.certifications.map((c, i) => `
                <div class="rp-entry-card" data-certification="${i}">
                    <div class="rp-entry-field">
                        <label class="rp-entry-label">Certification Name</label>
                        <input class="rp-input" data-k="name" value="${esc(c?.name || '')}" placeholder="e.g. AWS Certified Developer">
                    </div>
                    <div class="rp-entry-field">
                        <label class="rp-entry-label">Description / Details</label>
                        <textarea class="rp-input rp-input-ta rich-ta" data-k="description" rows="2" placeholder="Issued by Amazon Web Services, 2023">${esc(c?.description || '')}</textarea>
                    </div>
                    <button type="button" data-remove-certification class="rp-entry-remove">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        Remove
                    </button>
                </div>`).join('');
        }

        /* Achievement cards */
        const achievementEditorEl = $('achievement-editor');
        if (achievementEditorEl) {
            achievementEditorEl.innerHTML = state.achievements.map((a, i) => `
                <div class="rp-entry-card" data-achievement="${i}">
                    <div class="rp-entry-field">
                        <label class="rp-entry-label">Achievement Title</label>
                        <input class="rp-input" data-k="name" value="${esc(a?.name || '')}" placeholder="e.g. Won state-level hackathon">
                    </div>
                    <div class="rp-entry-field">
                        <label class="rp-entry-label">Description / Details</label>
                        <textarea class="rp-input rp-input-ta rich-ta" data-k="description" rows="2" placeholder="Add relevant details, impact, year, or recognition">${esc(a?.description || '')}</textarea>
                    </div>
                    <button type="button" data-remove-achievement class="rp-entry-remove">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        Remove
                    </button>
                </div>`).join('');
        }

        /* Language cards */
        const langEditorEl = $('language-editor');
        if (langEditorEl) {
            langEditorEl.innerHTML = state.languages.map((l, i) => `
                <div class="rp-entry-card" data-language="${i}">
                    <div class="rp-entry-row">
                        <div class="rp-entry-field">
                            <label class="rp-entry-label">Language</label>
                            <input class="rp-input" data-k="name" value="${esc(typeof l === 'string' ? l : (l?.name || ''))}" placeholder="e.g. English">
                        </div>
                        <div class="rp-entry-field">
                            <label class="rp-entry-label">Proficiency</label>
                            <input class="rp-input" data-k="level" value="${esc(typeof l === 'string' ? '' : (l?.level || ''))}" placeholder="e.g. Professional, Native">
                        </div>
                    </div>
                    <button type="button" data-remove-language class="rp-entry-remove">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        Remove
                    </button>
                </div>`).join('');
        }

        /* Additional information cards */
        const achEditorEl = $('additional-information-editor');
        if (achEditorEl) {
            achEditorEl.innerHTML = state.additional_information.map((a, i) => `
                <div class="rp-entry-card" data-additional-information="${i}">
                    <div class="rp-entry-field">
                        <label class="rp-entry-label">Title</label>
                        <input class="rp-input" data-k="name" value="${esc(a?.name || '')}" placeholder="e.g. Awards, Publications, Volunteer Work">
                    </div>
                    <div class="rp-entry-field">
                        <label class="rp-entry-label">Description / Details</label>
                        <textarea class="rp-input rp-input-ta rich-ta" data-k="description" rows="2" placeholder="Add relevant details">${esc(a?.description || '')}</textarea>
                    </div>
                    <button type="button" data-remove-additional-information class="rp-entry-remove">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                        Remove
                    </button>
                </div>`).join('');
        }

        if (typeof tinymce !== 'undefined') {
            tinymce.remove('.rp-input-ta');
            if (typeof initTinyMCE === 'function') initTinyMCE();
        }
    }

    function templateTokensForForm(templateId) {
        const html = String(templates?.[templateId]?.html || '');
        const tokens = new Set();
        html.replace(/\{\{\s*([a-z_][a-z0-9_]*)\s*\}\}|\[\[\s*([a-z_][a-z0-9_]*)\s*\]\]/gi, (_, t1, t2) => {
            tokens.add(String(t1 || t2 || '').toLowerCase()); return '';
        });
        html.replace(/\$resume\[['"]([a-z_][a-z0-9_]*)['"]\]/gi, (_, t) => {
            tokens.add(String(t || '').toLowerCase()); return '';
        });
        return tokens;
    }

    function applyDynamicFormVisibility() {
        const tokens = templateTokensForForm(selectedTemplateId);
        const hasExplicitTokens = tokens.size > 0;
        if (tokens.has('certificates')) tokens.add('certifications');
        if (tokens.has('certifications')) tokens.add('certificates');
        tokens.add('achievements');
        
        const alwaysShowContactFields = new Set([
            'name', 'last_name', 'designation', 'job_title', 'email', 'mobile', 'location',
            'contact', 'address', 'linkedin', 'github', 'portfolio', 'link',
        ]);
        document.querySelectorAll('[data-template-field]').forEach((node) => {
            const required = String(node.getAttribute('data-template-field') || '').split(',').map(v => v.trim().toLowerCase()).filter(Boolean);
            if (!required.length) return;
            const forceShow = required.some(token => alwaysShowContactFields.has(token));
            node.style.display = (forceShow || !hasExplicitTokens || required.some(token => tokens.has(token))) ? '' : 'none';
        });
        document.querySelectorAll('[data-template-section]').forEach((node) => {
            const required = String(node.getAttribute('data-template-section') || '').split(',').map(v => v.trim().toLowerCase()).filter(Boolean);
            if (!required.length) return;
            node.style.display = (!hasExplicitTokens || required.some(token => tokens.has(token))) ? '' : 'none';
        });
    }

    /* ── Zoom (legacy manual zoom kept for toolbar buttons) ── */
    function setZoom(z) {
        const minZoom = window.matchMedia('(max-width: 600px)').matches ? 38 : 50;
        const maxZoom = window.matchMedia('(max-width: 600px)').matches ? 90 : 130;
        previewZoom = Math.min(maxZoom, Math.max(minZoom, z));
        if (zoomLvlEl) zoomLvlEl.textContent = `${previewZoom}%`;
    }

    /* ── Source toggle ── */
    const setSourceState = (src) => {
        if (src) {
            source = src === 'upload' ? 'upload' : 'manual';
        }
        document.querySelectorAll('.source-btn').forEach(b => b.classList.toggle('active', b.dataset.source === src));
    };

    function applyColorSelection(color) {
        state.primary_color = color || '';
        state.primary_color_customized = state.primary_color !== '';
        document.querySelectorAll('.color-circle-btn').forEach(btn => {
            btn.classList.remove('active');
            btn.style.boxShadow = 'none';
            btn.style.borderColor = btn.classList.contains('original') ? '#0b1221' : 'transparent';
            if (btn.classList.contains('original')) btn.style.color = '#0b1221';
        });
        const activeBtns = Array.from(document.querySelectorAll('.color-circle-btn')).filter(btn => {
            if (color === '') return btn.classList.contains('original');
            return btn.title.toLowerCase() === getColorName(color).toLowerCase();
        });
        activeBtns.forEach(activeBtn => {
            activeBtn.classList.add('active');
            if (!activeBtn.classList.contains('original')) {
                activeBtn.style.boxShadow = `0 0 0 2px #fff, 0 0 0 4px ${color}`;
            } else {
                activeBtn.style.borderColor = '#2563eb';
                activeBtn.style.color = '#2563eb';
            }
        });
        renderTemplatePreview();
    }
    window.applyColorSelection = applyColorSelection;

    function getColorName(hex) {
        if (!hex) return 'original color';
        const map = { '#3b82f6': 'blue', '#10b981': 'green', '#475569': 'slate', '#e11d48': 'rose', '#6366f1': 'indigo' };
        return map[hex.toLowerCase()] || '';
    }

    /* ── Apply autofill ── */
    const applyResumeData = (resume) => {
        document.body.classList.add('is-builder');
        const onboarding = $('rp-onboarding-view');
        const builder = $('rp-builder-view');
        if (onboarding) onboarding.style.display = 'none';
        if (builder) builder.classList.add('visible');
        const keepColor = { primary_color: state.primary_color, primary_color_customized: state.primary_color_customized };
        Object.assign(state, defaults);
        Object.assign(state, normalise(resume));
        Object.assign(state, keepColor);
        syncLegacy();
        ensureDefaults();
        renderAll();
        // If TinyMCE isn't initialized yet, init and re-render to populate rich fields
        if (typeof tinymce !== 'undefined' && !tinymce.get('cv-summary') && typeof initTinyMCE === 'function') {
            initTinyMCE();
            setTimeout(() => renderAll(), 300);
        }
        applyColorSelection(state.primary_color_customized ? state.primary_color : '');
        goToStep(1);
    };
    window.applyResumeData = applyResumeData;

    /* ── Step navigation ── */
    const stepNames = ["", "Contacts", "Summary", "Experience", "Education", "Skills", "Additional", "Finalize"];
    function goToStep(step) {
        currentStep = Math.max(1, Math.min(step, 7));
        previewPage = 1;
        scheduleUpdateScale();

        document.querySelectorAll('.rp-step-tab').forEach(tab => {
            const t = parseInt(tab.dataset.step);
            tab.classList.remove('active', 'completed', 'done');
            if (t === currentStep) tab.classList.add('active');
            else if (t < currentStep) tab.classList.add('done');
        });

        document.querySelectorAll('.rp-step-pane, .rp-step-content').forEach(panel => {
            panel.classList.toggle('active', parseInt(panel.dataset.step) === currentStep);
        });

        const builderView = $('rp-builder-view');
        if (builderView) {
            if (currentStep === 7) builderView.classList.add('step-6-active');
            else builderView.classList.remove('step-6-active');
        }

        const btnNext = $('btn-next');
        const btnBack = $('btn-back');
        if (btnBack) {
            btnBack.style.visibility = 'visible';
            btnBack.innerHTML = currentStep > 1
                ? '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Back'
                : '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg> Exit';
        }
        if (btnNext) {
            if (currentStep < 7) { btnNext.textContent = `Next: ${stepNames[currentStep + 1]}`; btnNext.style.display = 'block'; }
            else { btnNext.style.display = 'none'; }
        }
    }

    document.querySelectorAll('.rp-step-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const target = parseInt(tab.dataset.step);
            if (target <= currentStep || (target > currentStep && document.querySelector(`.rp-step-tab[data-step="${target-1}"]`).classList.contains('completed'))) {
                goToStep(target);
            }
        });
    });

    $('btn-next')?.addEventListener('click', () => goToStep(currentStep + 1));
    $('btn-back')?.addEventListener('click', () => {
        if (currentStep > 1) goToStep(currentStep - 1);
        else if (typeof backToOnboarding === 'function') backToOnboarding();
    });

    $('edit-resume')?.addEventListener('click', () => goToStep(1));

    /* ── Field event listeners ── */
    document.querySelectorAll('.cv-field').forEach(input => {
        input.addEventListener('input', e => {
            const f = e.target.dataset.field;
            state[f] = ['skills'].includes(f) ? toList(e.target.value) : e.target.value;
            if (f === 'designation') state.job_title = e.target.value;
            if (f === 'portfolio') state.link = e.target.value;
            renderTemplatePreview();
        });
    });

    $('cv-image-input')?.addEventListener('change', e => {
        const file = e.target.files?.[0];
        if (!file) return;
        if (file.size > 2 * 1024 * 1024) { notify('Image too large. Max 2MB.', 'error'); return; }
        const reader = new FileReader();
        reader.onload = (rev) => { state.profile_image = rev.target.result; renderEditor(); renderTemplatePreview(); };
        reader.readAsDataURL(file);
    });

    $('remove-image-btn')?.addEventListener('click', () => { state.profile_image = ''; renderEditor(); renderTemplatePreview(); });
    $('cv-image-overlay')?.addEventListener('click', () => { $('cv-image-input')?.click(); });

        expEditorEl.addEventListener('input', e => {
        const block = e.target.closest('[data-exp]');
        if (!block) return;
        const i = Number(block.dataset.exp);
        const k = e.target.dataset.k;
        state.experience[i][k] = k === 'points'
            ? (/<[a-z][\s\S]*>/i.test(e.target.value) ? [e.target.value] : listify(e.target.value, /[\n•]+/))
            : e.target.value;
        renderTemplatePreview();
    });

    eduEditorEl.addEventListener('input', e => {
        const row = e.target.closest('[data-edu]');
        if (!row) return;
        const i = Number(row.dataset.edu);
        const k = e.target.dataset.k;
        if (!state.education[i] || typeof state.education[i] !== 'object') state.education[i] = educationToObject(state.education[i]);
        state.education[i][k] = e.target.value;
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

    $('certification-editor')?.addEventListener('input', e => {
        const row = e.target.closest('[data-certification]');
        if (!row) return;
        const i = Number(row.dataset.certification);
        const k = e.target.dataset.k;
        if (!state.certifications[i]) state.certifications[i] = { name:'', description:'' };
        state.certifications[i][k] = e.target.value;
        renderTemplatePreview();
    });

    $('language-editor')?.addEventListener('input', e => {
        const row = e.target.closest('[data-language]');
        if (!row) return;
        const i = Number(row.dataset.language);
        const k = e.target.dataset.k;
        if (!state.languages[i]) state.languages[i] = { name:'', level:'' };
        state.languages[i][k] = e.target.value;
        renderTemplatePreview();
    });

    $('achievement-editor')?.addEventListener('input', e => {
        const row = e.target.closest('[data-achievement]');
        if (!row) return;
        const i = Number(row.dataset.achievement);
        const k = e.target.dataset.k;
        if (!state.achievements[i]) state.achievements[i] = { name:'', description:'' };
        state.achievements[i][k] = e.target.value;
        renderTemplatePreview();
    });

    $('additional-information-editor')?.addEventListener('input', e => {
        const row = e.target.closest('[data-additional-information]');
        if (!row) return;
        const i = Number(row.dataset.additionalInformation);
        const k = e.target.dataset.k;
        if (!state.additional_information[i]) state.additional_information[i] = { name:'', description:'' };
        state.additional_information[i][k] = e.target.value;
        renderTemplatePreview();
    });

    templateIdEl.addEventListener('change', e => {
        selectedTemplateId = String(e.target.value || '');
        renderEditor();
        renderTemplatePreview();
    });

    document.querySelectorAll('.color-option').forEach(btn => {
        btn.addEventListener('click', e => { e.preventDefault(); e.stopPropagation(); applyColorSelection(btn.dataset.color || ''); });
    });

    function applyRichCommand(textarea, command) {
        if (!textarea) return;
        if (typeof tinymce !== 'undefined' && textarea.id && tinymce.get(textarea.id)) {
            tinymce.get(textarea.id).execCommand(command); return;
        }
        const start = textarea.selectionStart ?? textarea.value.length;
        const end   = textarea.selectionEnd ?? start;
        const selected = textarea.value.slice(start, end);
        const fallback = selected || 'text';
        const wrap = (before, after) => { textarea.setRangeText(before + fallback + after, start, end, 'select'); };
        if (command === 'bold')      wrap('<strong>', '</strong>');
        if (command === 'italic')    wrap('<em>', '</em>');
        if (command === 'underline') wrap('<u>', '</u>');
        if (command === 'list') {
            const replacement = (selected || 'List item').split('\n').map(line => line.trim() ? `• ${line.replace(/^[-•]\s*/, '')}` : line).join('\n');
            textarea.setRangeText(replacement, start, end, 'select');
        }
        textarea.focus();
        textarea.dispatchEvent(new Event('input', { bubbles: true }));
    }

    /* ── Delegated button clicks ── */
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

        if (btn.classList.contains('color-option')) { applyColorSelection(btn.dataset.color || ''); return; }

        if (btn.dataset.richCommand) {
            applyRichCommand(btn.closest('.rich-text-wrapper, .summary-editor-shell')?.querySelector('textarea'), btn.dataset.richCommand);
            return;
        }

        if (btn.id === 'add-exp-btn' || btn.id === 'add-exp') { state.experience.push({ company:'', role:'', period:'', points:[''] }); renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'add-edu-btn' || btn.id === 'add-edu') { state.education.push({ degree:'', stream:'', institution:'', year:'' }); renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'add-project-btn' || btn.id === 'add-project') { state.projects.push({ name:'', tech_stack:'', link:'', description:'' }); renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'add-certification-btn') { state.certifications.push({ name:'', description:'' }); renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'add-achievement-btn') { state.achievements.push({ name:'', description:'' }); renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'add-language-btn') { state.languages.push({ name:'', level:'' }); renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'add-additional-information-btn')   { state.additional_information.push({ name:'', description:'' }); renderEditor(); renderTemplatePreview(); }

        if (btn.dataset.removeExp !== undefined) {
            state.experience.splice(Number(btn.closest('[data-exp]').dataset.exp), 1);
            if (!state.experience.length) state.experience.push({ company:'', role:'', period:'', points:[''] });
            renderEditor(); renderTemplatePreview();
        }
        if (btn.dataset.removeEdu !== undefined) {
            state.education.splice(Number(btn.closest('[data-edu]').dataset.edu), 1);
            if (!state.education.length) state.education.push({ degree:'', stream:'', institution:'', year:'' });
            renderEditor(); renderTemplatePreview();
        }
        if (btn.dataset.removeProject !== undefined)       { state.projects.splice(Number(btn.closest('[data-project]').dataset.project), 1); renderEditor(); renderTemplatePreview(); }
        if (btn.dataset.removeCertification !== undefined) { state.certifications.splice(Number(btn.closest('[data-certification]').dataset.certification), 1); renderEditor(); renderTemplatePreview(); }
        if (btn.dataset.removeAchievement !== undefined)   { state.achievements.splice(Number(btn.closest('[data-achievement]').dataset.achievement), 1); renderEditor(); renderTemplatePreview(); }
        if (btn.dataset.removeLanguage !== undefined)      { state.languages.splice(Number(btn.closest('[data-language]').dataset.language), 1); renderEditor(); renderTemplatePreview(); }
        if (btn.dataset.removeAdditionalInformation !== undefined)   { state.additional_information.splice(Number(btn.closest('[data-additional-information]').dataset.additionalInformation), 1); renderEditor(); renderTemplatePreview(); }

        if (btn.id === 'clear-exp-section-btn')           { state.experience = []; renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'clear-edu-section-btn')           { state.education = [];  renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'clear-project-section-btn')       { state.projects = [];   renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'clear-certification-section-btn') { state.certifications = []; renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'clear-achievement-section-btn')   { state.achievements = []; renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'clear-language-section-btn')      { state.languages = []; renderEditor(); renderTemplatePreview(); }
        if (btn.id === 'clear-additional-information-section-btn')   { state.additional_information = [];   renderEditor(); renderTemplatePreview(); }
    });

    /* ── Save ── */
    saveBtnEl?.addEventListener('click', async () => {
        try {
            syncLegacy();
            const btnOrigText = saveBtnEl.textContent;
            saveBtnEl.textContent = 'Saving…';
            saveBtnEl.style.opacity = '0.7';
            const url      = app.dataset.updateUrl || app.dataset.storeUrl;
            const method   = app.dataset.updateUrl ? 'patch' : 'post';
            const templateId = templateIdEl?.value || null;
            const downloadFormat = window.pendingDownloadFormat || 'pdf';
            const payload  = app.dataset.updateUrl
                ? { resume: state, template_id: templateId }
                : { source, template_id: templateId, resume: state, download_format: downloadFormat };
            const res = await axios[method](url, payload);
            if (res.data.redirect) { window.location.href = res.data.redirect; return; }
            if (res.data.resume?.id) savedResumeId = res.data.resume.id;
            saveBtnEl.textContent = '✓ Saved';
            setTimeout(() => { saveBtnEl.textContent = btnOrigText; saveBtnEl.style.opacity = '1'; }, 2000);
            if (app.dataset.authenticated === '1' && app.dataset.downloadRequiresPlan === '1') {
                if (window.openPlanDownloadModal) window.openPlanDownloadModal();
                else window.location.href = app.dataset.plansUrl || '/plans';
                return;
            }
            if (savedResumeId) {
                const fmt = downloadFormat;
                window.pendingDownloadFormat = null;
                window.location.href = `/resume/${savedResumeId}/download/${fmt}`;
                return;
            }
        } catch (err) {
            notify(err.response?.data?.message || 'Save failed.', 'error');
            saveBtnEl.textContent = 'Save & Download';
            saveBtnEl.style.opacity = '1';
        }
    });

    /* ── File autofill ── */
    window.__resumeAutofillHandledByPartial = true;
    if (autofillFileEl) {
        autofillFileEl.addEventListener('change', () => {
            const f = autofillFileEl.files?.[0];
            if (fileNameEl) fileNameEl.textContent = f ? f.name : 'Click to upload your resume';
            if (autofillStatusEl) { autofillStatusEl.textContent = f ? 'File selected. Click Autofill to import it.' : ''; autofillStatusEl.style.color = ''; }
            if (f) {
                setTimeout(() => doAutofill(), 0);
            }
        });

        const doAutofill = async () => {
            if (uploadInProgress) return;
            const requestToken = ++uploadRequestToken;
            const file = autofillFileEl.files?.[0];
            if (!file) {
                if (autofillStatusEl) { autofillStatusEl.textContent = 'Please choose a file first.'; autofillStatusEl.style.color = '#c0392b'; }
                return;
            }
            try {
                uploadInProgress = true;
                if (typeof window.showResumeScanOverlay === 'function') {
                    window.showResumeScanOverlay();
                }
                if (autofillBtnEl) {
                    autofillBtnEl.disabled = true;
                    autofillBtnEl.style.opacity = '.6';
                }
                if (autofillStatusEl) { autofillStatusEl.textContent = 'Uploading and parsing your resume…'; autofillStatusEl.style.color = ''; }
                const fd = new FormData();
                fd.append('resume', file);
                fd.append('mode', 'autofill');
                const currentRole = $('cv-designation')?.value?.trim() || '';
                if (currentRole) fd.append('job_role', currentRole);
                let data;
                if (window.axios) {
                    const res = await window.axios.post(app.dataset.analyzeUrl, fd, { headers: { 'Content-Type': 'multipart/form-data' } });
                    data = res.data;
                } else {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                    const res = await fetch(app.dataset.analyzeUrl, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
                        body: fd,
                    });
                    data = await res.json().catch(() => ({}));
                    if (!res.ok) { const error = new Error(data.message || 'Could not import this resume.'); error.response = { data }; throw error; }
                }
                console.log('upload response', data);
                if (!data.success) throw new Error(data.message || 'Could not import this resume.');
                if (requestToken !== uploadRequestToken) return;
                console.log('parsed resume', data.improved_resume);
                applyResumeData(data.improved_resume || {});
                source = 'upload';
                setSourceState('upload');
                if (autofillStatusEl) {
                    const sourceLabel = (data.parser_source === 'gemini' || data.parser_source === 'gemini+local')
                        ? 'AI'
                        : 'local parser';
                    autofillStatusEl.textContent = RESUME_UPLOAD_SUCCESS_MESSAGE;
                    autofillStatusEl.style.color = data.ai_unavailable ? '#b45309' : '#15803d';
                    setTimeout(() => { if (autofillStatusEl.textContent === RESUME_UPLOAD_SUCCESS_MESSAGE) autofillStatusEl.textContent = ''; }, 10000);
                }
                notify(RESUME_UPLOAD_SUCCESS_MESSAGE, 'info');
            } catch (err) {
                const uploadErr = friendlyAiMessage(err.response?.data?.message || err.message);
                if (autofillStatusEl) { autofillStatusEl.textContent = uploadErr; autofillStatusEl.style.color = '#c0392b'; }
                notify(uploadErr, 'error');
                showAiFailureAlert();
            } finally {
                uploadInProgress = false;
                if (typeof window.hideResumeScanOverlay === 'function') {
                    window.hideResumeScanOverlay();
                }
                if (autofillBtnEl) {
                    autofillBtnEl.disabled = false;
                    autofillBtnEl.style.opacity = '';
                }
            }
        };

        autofillBtnEl?.addEventListener('click', doAutofill);
    }

    $('rp-dropzone-trigger')?.addEventListener('click', e => {
        if (!e.target.closest('#resume-autofill-button')) autofillFileEl?.click();
    });

    /* Zoom toolbar (kept for existing toolbar buttons) */
    zoomOutEl?.addEventListener('click', () => { setZoom(previewZoom - 10); });
    zoomInEl?.addEventListener('click',  () => { setZoom(previewZoom + 10); });

    /* ── Template popup ── */
    const templatePopup     = $('template-popup');
    const templateGrid      = $('template-grid');
    const changeTemplateBtn = $('change-template-btn');
    const closePopupBtn     = $('close-template-popup');
    const templatePreviewPage = $('template-preview-page');
    const templatePreviewName = $('template-preview-name');
    let templatePreviewActiveId = '';

    function updateTemplateThumbScales() {
        if (!templateGrid) return;
        templateGrid.querySelectorAll('.rp-tpl-thumb').forEach(thumb => {
            const width = thumb.clientWidth || thumb.offsetWidth || 0;
            if (!width) return;
            const scale = Math.min(0.64, Math.max(0.28, (width - 2) / A4_W));
            thumb.style.setProperty('--tpl-thumb-scale', scale.toFixed(4));
            thumb.style.setProperty('--tpl-thumb-height', Math.ceil(A4_H * scale) + 'px');
        });
    }

    function hasTemplateChooserUserData() {
        return Boolean(
            fullName() || state.designation || state.job_title || state.email || state.mobile || state.location ||
            state.linkedin || state.github || state.portfolio || state.link || state.summary || state.profile_image ||
            state.skills.some(Boolean) ||
            state.experience.some(e => e?.company || e?.role || e?.period || ensureArray(e?.points).some(Boolean)) ||
            state.education.some(e => e?.degree || e?.stream || e?.institution || e?.year) ||
            state.projects.some(p => p?.name || p?.description || p?.tech_stack || p?.tech || p?.link) ||
            state.certifications.some(c => typeof c === 'string' ? c.trim() : (c?.name || c?.description)) ||
            state.languages.some(l => typeof l === 'string' ? l.trim() : (l?.name || l?.level)) ||
            state.achievements.some(a => typeof a === 'string' ? a.trim() : (a?.name || a?.description)) ||
            state.additional_information.some(a => typeof a === 'string' ? a.trim() : (a?.name || a?.description))
        );
    }

    function templateChooserSampleData() {
        return {
            name: 'James Smith',
            email: 'james.smith@example.com',
            mobile: '+1 (555) 123-4567',
            location: 'Austin, Texas, USA',
            contact: 'james.smith@example.com | +1 (555) 123-4567 | Austin, Texas, USA',
            address: 'Austin, Texas, USA',
            designation: 'Senior Full Stack Developer',
            job_title: 'Senior Full Stack Developer',
            summary: 'Results-driven Senior Full Stack Developer with 6+ years of experience building scalable web applications, cloud solutions, and enterprise SaaS platforms.',
            linkedin: 'linkedin.com/in/jamessmith',
            github: 'github.com/jamessmith',
            portfolio: 'https://jamessmith.dev',
            link: 'https://jamessmith.dev',
            social_links: 'linkedin.com/in/jamessmith | github.com/jamessmith',
            skills: '<span class="tpl-badge">Laravel</span><span class="tpl-badge">PHP</span><span class="tpl-badge">MySQL</span><span class="tpl-badge">React.js</span><span class="tpl-badge">AWS</span>',
            experience: `<div class="tpl-role"><div class="tpl-role-head"><strong>Senior Full Stack Developer</strong><span>2023 - Present</span></div><p>TechNova Solutions</p><ul><li>Led development of enterprise SaaS platform serving 100K+ users.</li><li>Improved API performance by 45%.</li><li>Mentored junior developers.</li></ul></div>`,
            education: '<ul><li><strong>Master of Science in Computer Science</strong><span class="tpl-description">University of Texas, 2018 - 2020</span></li><li><strong>Bachelor of Computer Science</strong><span class="tpl-description">State University, 2014 - 2018</span></li></ul>',
            projects: '<ul><li><strong>Enterprise CRM Platform</strong><span class="tpl-description">Laravel, React.js, MySQL, AWS</span></li><li><strong>AI Resume Builder</strong><span class="tpl-description">Next.js, Node.js, TypeScript, AWS</span></li></ul>',
            certifications: '<ul><li>AWS Certified Solutions Architect</li><li>Google Cloud Associate Engineer</li><li>Meta Frontend Professional Certificate</li></ul>',
            certificates: '<ul><li>AWS Certified Solutions Architect</li><li>Google Cloud Associate Engineer</li><li>Meta Frontend Professional Certificate</li></ul>',
            languages: '<ul><li>English - Native</li><li>Spanish - Professional</li></ul>',
            achievements: '<ul><li>Winner - National Hackathon 2024</li><li>Top Performer Award 2023</li><li>Built platform serving 100K+ users</li></ul>',
            additional_information: '<ul><li>Open Source Contributor</li><li>Tech Conference Speaker</li><li>Mentor for Junior Developers</li></ul>',
            profile_image: '<div style="width:80px; height:80px; background:#e2e8f0; border-radius:50%; display:flex; align-items:center; justify-content:center; color:#94a3b8;"><svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg></div>',
            profile_image_url: '',
            profile_image_tag: '',
            photo: '',
        };
    }

    function renderTemplateChooserSampleHtml(template) {
        const html = String(template?.html || '');
        const sampleData = templateChooserSampleData();
        let filled = hasResumePlaceholders(html) ? html : editableTemplateShell();
        Object.entries(sampleData).forEach(([k, v]) => {
            filled = filled.replace(new RegExp('\\{\\{\\s*' + k + '\\s*\\}\\}', 'g'), v);
            filled = filled.split('[[' + k + ']]').join(v);
        });
        return resumeAccentStyle(state.primary_color_customized ? state.primary_color : '') + filled;
    }

    function renderTemplateChooserHtml(template) {
        if (hasTemplateChooserUserData()) {
            return renderTemplateHtml(template);
        }
        return renderTemplateChooserSampleHtml(template);
    }

    function updateTemplateLargePreview(id, animate = true) {
        if (!templatePreviewPage) return;
        const template = templates[id] || templates[selectedTemplateId] || Object.values(templates)[0];
        if (!template) {
            templatePreviewPage.innerHTML = '<div style="padding:2rem;color:#64748b;">No templates found.</div>';
            return;
        }
        templatePreviewActiveId = String(id || selectedTemplateId || '');
        if (templatePreviewName) templatePreviewName.textContent = 'Resume';
        if (animate) templatePreviewPage.classList.add('is-swapping');
        requestAnimationFrame(() => {
            templatePreviewPage.innerHTML = renderTemplateChooserHtml(template);
            templatePreviewPage.classList.remove('is-swapping');
        });
    }

    function buildTemplateCard(id, template, isSelected) {
        const card = document.createElement('div');
        card.className = 'rp-tpl-card' + (isSelected ? ' selected' : '');
        card.dataset.templateId = id;
        card.tabIndex = 0;
        card.setAttribute('role', 'button');
        const thumb = document.createElement('div');
        thumb.className = 'rp-tpl-thumb';
        const check = document.createElement('div');
        check.className = 'rp-tpl-check';
        check.innerHTML = `<svg width="14" height="14" fill="none" stroke="white" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>`;
        thumb.appendChild(check);
        const inner = document.createElement('div');
        inner.className = 'rp-tpl-thumb-inner';
        inner.innerHTML = renderTemplateChooserSampleHtml(template);
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
        updateTemplateLargePreview(selectedId);
        requestAnimationFrame(updateTemplateThumbScales);
        setTimeout(updateTemplateThumbScales, 120);
    }

    function bindTemplateCard(card, id) {
        card.addEventListener('mouseenter', () => updateTemplateLargePreview(id));
        card.addEventListener('focusin', () => updateTemplateLargePreview(id));
        const selectTemplate = () => {
            templateGrid.querySelectorAll('.rp-tpl-card').forEach(c => c.classList.remove('selected'));
            card.classList.add('selected');
            if (templateIdEl) { selectedTemplateId = id; templateIdEl.value = id; templateIdEl.dispatchEvent(new Event('change')); }
            updateTemplateLargePreview(id);
            setTimeout(() => templatePopup.classList.remove('open', 'visible'), 160);
        };
        card.addEventListener('click', selectTemplate);
        card.addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                selectTemplate();
            }
        });
    }

    changeTemplateBtn?.addEventListener('click', openTemplatePopup);
    closePopupBtn?.addEventListener('click',     () => templatePopup?.classList.remove('open', 'visible'));
    $('close-template-btn')?.addEventListener('click', () => templatePopup?.classList.remove('open', 'visible'));
    templatePopup?.addEventListener('click', e => { if (e.target === templatePopup) templatePopup.classList.remove('open', 'visible'); });
    window.addEventListener('resize', updateTemplateThumbScales);
    if ('ResizeObserver' in window && templateGrid) {
        new ResizeObserver(updateTemplateThumbScales).observe(templateGrid);
    }

    /* ── Download ── */
    $('download-pdf')?.addEventListener('click', () => {
        if (app.dataset.authenticated === '1' && app.dataset.downloadRequiresPlan === '1') { window.openPlanDownloadModal?.(); return; }
        const doDownload = (format) => {
            if (saveBtnEl) { window.pendingDownloadFormat = format; saveBtnEl.click(); }
            else if (savedResumeId) { window.location.href = `/resume/${savedResumeId}/download/${format}`; }
        };
        if (window.openFormatDownloadModal) window.openFormatDownloadModal(doDownload);
        else doDownload('pdf');
    });

    /* ── Mobile view toggle ── */
    window.setMobileView = (view) => {
        const builder = $('rp-builder-view');
        if (!builder) return;
        builder.classList.remove('view-form', 'view-preview');
        builder.classList.add('view-' + view);
        document.getElementById('mob-btn-form')?.classList.toggle('active',    view === 'form');
        document.getElementById('mob-btn-preview')?.classList.toggle('active', view === 'preview');
        if (view === 'preview') {
            requestAnimationFrame(() => {
                renderTemplatePreview();
                setTimeout(updatePreviewScale, 0);
                setTimeout(updatePreviewScale, 200);
            });
        }
    };

    /* ── Bootstrap ── */
    if (window.matchMedia('(max-width: 600px)').matches)       previewZoom = 42;
    else if (window.matchMedia('(max-width: 900px)').matches)  previewZoom = 85;
    else if (window.matchMedia('(max-width: 1300px)').matches) previewZoom = 80;

    setZoom(previewZoom);
    setSourceState(source);
    applyColorSelection(state.primary_color_customized ? state.primary_color : '');
    renderEditor();
    renderTemplatePreview();
    goToStep(1);
    applyColorSelection(state.primary_color_customized ? state.primary_color : '');

})();
</script>

{{-- ── Entry card & edu row styles ── --}}
<style>
/* ═══════════════════════════════════════════════════════
   ENTRY CARDS
═══════════════════════════════════════════════════════ */
.rp-entry-card {
    background: var(--white, #fff);
    border: 1.5px solid rgba(0,0,0,0.09);
    border-radius: 16px;
    padding: 1.375rem 1.5rem 1.125rem;
    margin-bottom: 1.125rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.rp-entry-card:hover { border-color: rgba(37,99,235,0.22); box-shadow: 0 4px 18px rgba(0,0,0,0.05); }
.rp-entry-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.875rem; margin-bottom: 0.875rem; }
@media (max-width: 560px) { .rp-entry-row { grid-template-columns: 1fr; } }
.rp-entry-field { display: flex; flex-direction: column; gap: 0.3rem; margin-bottom: 0.875rem; }
.rp-entry-field:last-of-type { margin-bottom: 0; }
.rp-entry-label { font-size: 0.75rem; font-weight: 700; color: var(--ink, #1e293b); letter-spacing: 0.01em; }
.rp-entry-hint { font-weight: 400; color: var(--soft, #94a3b8); font-size: 0.72rem; }
.rp-input-ta { resize: vertical; min-height: 90px; line-height: 1.6; }
.rp-entry-remove {
    display: inline-flex; align-items: center; gap: 0.375rem; margin-top: 0.75rem;
    padding: 0.375rem 0.875rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700;
    color: var(--muted, #64748b); background: var(--surface-2, #f1f5f9);
    border: 1.5px solid rgba(0,0,0,0.07); cursor: pointer; transition: all 0.2s;
    font-family: var(--font-body, sans-serif);
}
.rp-entry-remove:hover { background: #fee2e2; color: #dc2626; border-color: rgba(220,38,38,0.2); }

/* ═══════════════════════════════════════════════════════
   EDUCATION ROWS
═══════════════════════════════════════════════════════ */
.rp-edu-row { display: flex; align-items: center; gap: 0.625rem; margin-bottom: 0.75rem; }
.rp-edu-row .rp-input { flex: 1; }
.rp-edu-remove {
    flex-shrink: 0; width: 34px; height: 34px; display: flex; align-items: center;
    justify-content: center; border-radius: 50%; border: 1.5px solid rgba(0,0,0,0.09);
    background: var(--surface-2, #f1f5f9); color: var(--muted, #64748b); cursor: pointer; transition: all 0.2s;
}
.rp-edu-remove:hover { background: #fee2e2; color: #dc2626; border-color: rgba(220,38,38,0.2); }

/* ═══════════════════════════════════════════════════════
   PREVIEW STAGE — pagination clip
═══════════════════════════════════════════════════════ */

/* Stage clips to exactly one A4 page */
.resume-preview-stage {
    overflow: hidden !important;
    position: relative;
}

/* Sheet renders at full natural height; JS translateY handles paging */
.resume-sheet-preview {
    overflow: visible !important;
}

.rp-viewport,
.resume-preview-shell,
.resume-preview-wrap {
    max-width: 100%;
    overflow-x: auto;
}

.resume-maker-preview,
.resume-preview-stage,
.resume-sheet-preview,
.resume-sheet-preview * {
    overflow-wrap: anywhere;
    word-break: break-word;
}

@media (max-width: 1024px) {
    .rp-page { padding: 1rem 0.75rem 4.5rem !important; }
    .rp-grid { grid-template-columns: 1fr !important; gap: 1rem !important; }
    .rp-preview-panel { position: static !important; top: auto !important; }
    .rp-viewport { height: auto !important; max-height: 78vh; padding-bottom: 0.75rem; justify-content: flex-start !important; }
    .rp-input-grid, .rp-entry-row { grid-template-columns: 1fr !important; }
    .rp-card-body { padding: 1rem !important; }
    .rp-card-head { padding: 1rem !important; }
}

@media (max-width: 640px) {
    .rp-hero, .rp-preview-toolbar { align-items: stretch !important; }
    .rp-dl-group, .rp-preview-toolbar { flex-wrap: wrap; gap: 0.5rem !important; }
    .rp-btn, .rp-dl-btn { min-height: 42px; }
    .resume-preview-stage { margin-left: 0 !important; margin-right: 0 !important; }
}

/* Page navigation strip */
.rp-page-nav {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 10px 0 4px;
    user-select: none;
}
.rp-page-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 34px; height: 34px; border-radius: 8px;
    border: 1.5px solid rgba(0,0,0,0.12); background: #fff; color: #334155;
    cursor: pointer; transition: background 0.15s, border-color 0.15s; padding: 0; flex-shrink: 0;
    font-family: inherit;
}
.rp-page-btn:hover:not(:disabled)  { background: #f1f5f9; border-color: rgba(0,0,0,0.2); }
.rp-page-btn:active:not(:disabled) { background: #e2e8f0; transform: scale(0.96); }
.rp-page-btn:disabled { opacity: 0.35; cursor: not-allowed; }
.rp-page-label {
    font-size: 13px; font-weight: 600; color: #334155;
    min-width: 48px; text-align: center; letter-spacing: 0.02em;
}
</style>
@endpush
