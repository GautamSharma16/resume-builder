import axios from 'axios';

const app = document.getElementById('improve-cv-app');

if (app) {
    const routes = {
        analyze: app.dataset.analyzeUrl,
        improve: app.dataset.improveUrl,
        grammar: app.dataset.grammarUrl,
        save: app.dataset.saveUrl,
        download: app.dataset.downloadUrl,
        paymentOrder: app.dataset.paymentOrderUrl,
        paymentVerify: app.dataset.paymentVerifyUrl,
    };

    const els = {
        form: document.getElementById('resume-upload-form'),
        analyzeButton: document.getElementById('analyze-button'),
        improveButton: document.getElementById('improve-again-button'),
        grammarButton: document.getElementById('grammar-fix-button'),
        downloadButton: document.getElementById('download-button'),
        status: document.getElementById('app-status'),
        dashboard: document.getElementById('analysis-dashboard'),
        workspace: document.getElementById('resume-workspace'),
        scoreValue: document.getElementById('score-value'),
        scoreBar: document.getElementById('score-bar'),
        strengths: document.getElementById('strengths-list'),
        weaknesses: document.getElementById('weaknesses-list'),
        keywords: document.getElementById('keywords-list'),
        suggestions: document.getElementById('suggestions-list'),
        name: document.getElementById('resume-name'),
        summary: document.getElementById('resume-summary'),
        skills: document.getElementById('resume-skills'),
        experience: document.getElementById('experience-editor'),
        education: document.getElementById('education-editor'),
        addExperience: document.getElementById('add-experience'),
        addEducation: document.getElementById('add-education'),
        projects: document.getElementById('projects-editor'),
        addProjects: document.getElementById('add-projects'),
        preview: document.getElementById('resume-preview'),
        modal: document.getElementById('payment-modal'),
        closeModal: document.getElementById('close-payment-modal'),
        payButton: document.getElementById('pay-button'),
        applyToResumeMaker: document.getElementById('apply-to-resume-maker'),
    };

    let analysisId = null;
    let isPaid = false;

    const state = {
        name: '',
        summary: '',
        skills: [],
        experience: [],
        education: [],
        projects: [],
    };

    const setBusy = (busy, message = '') => {
        els.analyzeButton.disabled = busy;
        els.improveButton.disabled = busy;
        els.grammarButton.disabled = busy;
        els.downloadButton.disabled = busy;
        els.payButton.disabled = busy;
        els.status.textContent = message;
        [els.analyzeButton, els.improveButton, els.grammarButton, els.downloadButton, els.payButton].forEach((button) => {
            button.classList.toggle('opacity-60', busy);
            button.classList.toggle('cursor-not-allowed', busy);
        });
    };

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const splitList = (value) => String(value)
        .split(',')
        .map((item) => item.trim())
        .filter(Boolean);

    const normalizeResume = (resume = {}) => ({
        name: String(resume.name ?? ''),
        summary: String(resume.summary ?? ''),
        skills: Array.isArray(resume.skills) ? resume.skills.map(String).filter(Boolean) : [],
        experience: Array.isArray(resume.experience) ? resume.experience.map((item) => ({
            company: String(item.company ?? ''),
            role: String(item.role ?? ''),
            points: Array.isArray(item.points) ? item.points.map(String).filter(Boolean) : [],
        })) : [],
        education: Array.isArray(resume.education) ? resume.education.map(String).filter(Boolean) : [],
        projects: Array.isArray(resume.projects) ? resume.projects.map(String).filter(Boolean) : [],
    });

    const replaceState = (resume) => {
        Object.assign(state, normalizeResume(resume));
        if (!state.experience.length) {
            state.experience.push({ company: '', role: '', points: [''] });
        }
        if (!state.education.length) {
            state.education = [''];
        }
        if (!state.projects.length) {
            state.projects = [''];
        }
        renderEditor();
        renderPreview();
    };

    const renderDashboard = (payload) => {
        const score = Math.max(0, Math.min(100, Number(payload.score || 0)));
        els.scoreValue.textContent = score;
        els.scoreBar.style.width = `${score}%`;

        renderList(els.strengths, payload.strengths);
        renderList(els.weaknesses, payload.weaknesses);
        renderList(els.keywords, payload.missing_keywords);
        renderList(els.suggestions, payload.suggestions);

        els.dashboard.classList.remove('hidden');
    };

    const renderList = (target, items = []) => {
        const values = Array.isArray(items) ? items : [];
        target.innerHTML = values.length
            ? values.map((item) => `<li class="flex gap-2"><span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-teal-600"></span><span>${escapeHtml(item)}</span></li>`).join('')
            : '<li class="text-gray-400">No items yet.</li>';
    };

    const renderEditor = () => {
        els.name.value = state.name;
        els.summary.value = state.summary;
        els.skills.value = state.skills.join(', ');

        els.experience.innerHTML = state.experience.map((item, index) => `
            <div class="rounded-lg border border-gray-200 p-4" data-experience-index="${index}">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Company</label>
                        <input type="text" value="${escapeHtml(item.company)}" data-exp-field="company" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1">Role</label>
                        <input type="text" value="${escapeHtml(item.role)}" data-exp-field="role" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                    </div>
                </div>
                <div class="mt-3 space-y-2">
                    <div class="flex items-center justify-between gap-3">
                        <label class="block text-xs font-semibold text-gray-600">Bullet points</label>
                        <button type="button" data-action="add-point" class="text-sm font-semibold text-teal-700 hover:text-teal-900">Add point</button>
                    </div>
                    ${(item.points.length ? item.points : ['']).map((point, pointIndex) => `
                        <div class="flex gap-2" data-point-index="${pointIndex}">
                            <input type="text" value="${escapeHtml(point)}" data-exp-field="point" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                            <button type="button" data-action="remove-point" class="rounded-md border border-gray-300 px-3 text-gray-600 hover:bg-gray-50" aria-label="Remove point">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14"/></svg>
                            </button>
                        </div>
                    `).join('')}
                </div>
                <div class="mt-4 flex justify-end">
                    <button type="button" data-action="remove-experience" class="inline-flex items-center gap-2 rounded-md border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/></svg>
                        Remove
                    </button>
                </div>
            </div>
        `).join('');

        els.education.innerHTML = (state.education.length ? state.education : ['']).map((item, index) => `
            <div class="flex gap-2" data-education-index="${index}">
                <input type="text" value="${escapeHtml(item)}" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                <button type="button" data-action="remove-education" class="rounded-md border border-gray-300 px-3 text-gray-600 hover:bg-gray-50" aria-label="Remove education">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14"/></svg>
                </button>
            </div>
        `).join('');

        els.projects.innerHTML = (state.projects.length ? state.projects : ['']).map((item, index) => `
            <div class="flex gap-2" data-project-index="${index}">
                <input type="text" value="${escapeHtml(item)}" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-teal-600 focus:ring-teal-600">
                <button type="button" data-action="remove-project" class="rounded-md border border-gray-300 px-3 text-gray-600 hover:bg-gray-50" aria-label="Remove project">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M5 12h14"/></svg>
                </button>
            </div>
        `).join('');
    };

    const renderPreview = () => {
        const skillHtml = state.skills.length
            ? state.skills.map((skill) => `<span class="inline-flex rounded-md bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-800">${escapeHtml(skill)}</span>`).join('')
            : '<span class="text-sm text-gray-400">Add skills in the editor.</span>';

        const experienceHtml = state.experience.length
            ? state.experience.map((item) => `
                <div class="mb-5">
                    <div class="flex flex-wrap items-baseline justify-between gap-2">
                        <h3 class="text-base font-bold text-gray-950">${escapeHtml(item.role || 'Role')}</h3>
                        <p class="text-sm font-medium text-gray-500">${escapeHtml(item.company)}</p>
                    </div>
                    ${item.points.length ? `<ul class="mt-2 list-disc space-y-1 pl-5 text-sm leading-6 text-gray-700">${item.points.map((point) => `<li>${escapeHtml(point)}</li>`).join('')}</ul>` : ''}
                </div>
            `).join('')
            : '<p class="text-sm text-gray-400">Add experience in the editor.</p>';

        const educationHtml = state.education.length
            ? `<ul class="list-disc space-y-1 pl-5 text-sm leading-6 text-gray-700">${state.education.map((item) => `<li>${escapeHtml(item)}</li>`).join('')}</ul>`
            : '<p class="text-sm text-gray-400">Add education in the editor.</p>';

        const projectsHtml = state.projects.length
            ? `<ul class="list-disc space-y-1 pl-5 text-sm leading-6 text-gray-700">${state.projects.map((item) => `<li>${escapeHtml(item)}</li>`).join('')}</ul>`
            : '<p class="text-sm text-gray-400">Add projects in the editor.</p>';

        els.preview.innerHTML = `
            <header class="border-b-2 border-gray-950 pb-5">
                <h1 class="text-3xl font-bold uppercase text-gray-950">${escapeHtml(state.name || 'Your Name')}</h1>
            </header>

            <section class="mt-6">
                <h2 class="text-xs font-bold uppercase tracking-wide text-teal-700">Summary</h2>
                <p class="mt-2 text-sm leading-6 text-gray-700">${escapeHtml(state.summary || 'Add a concise professional summary tailored to the job role.')}</p>
            </section>

            <section class="mt-6">
                <h2 class="text-xs font-bold uppercase tracking-wide text-teal-700">Skills</h2>
                <div class="mt-3 flex flex-wrap gap-2">${skillHtml}</div>
            </section>

            <section class="mt-6">
                <h2 class="text-xs font-bold uppercase tracking-wide text-teal-700">Experience</h2>
                <div class="mt-3">${experienceHtml}</div>
            </section>

            <section class="mt-6">
                <h2 class="text-xs font-bold uppercase tracking-wide text-teal-700">Education</h2>
                <div class="mt-3">${educationHtml}</div>
            </section>

            <section class="mt-6">
                <h2 class="text-xs font-bold uppercase tracking-wide text-teal-700">Projects</h2>
                <div class="mt-3">${projectsHtml}</div>
            </section>
        `;
    };

    const applyAnalysisPayload = (payload) => {
        analysisId = payload.analysis_id;
        isPaid = Boolean(payload.is_paid);
        renderDashboard(payload);
        replaceState(payload.improved_resume);
        els.workspace.classList.remove('hidden');
        if (els.applyToResumeMaker) {
            els.applyToResumeMaker.classList.remove('hidden');
        }
    };

    const saveCurrentResume = async () => {
        if (!analysisId) {
            throw new Error('Analyze a resume before saving.');
        }

        await axios.post(routes.save, {
            analysis_id: analysisId,
            resume: state,
        });
    };

    const downloadPdf = async () => {
        await saveCurrentResume();

        const response = await axios.get(routes.download, {
            params: { analysis_id: analysisId },
            responseType: 'blob',
            validateStatus: (status) => status < 500,
        });

        if (response.status === 402) {
            showPaymentModal();
            return;
        }

        if (response.status !== 200) {
            throw new Error('Download could not be generated.');
        }

        const blobUrl = URL.createObjectURL(response.data);
        const link = document.createElement('a');
        link.href = blobUrl;
        link.download = `${(state.name || 'resume').toLowerCase().replace(/[^a-z0-9]+/g, '-')}-improved-resume.pdf`;
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(blobUrl);
    };

    const showPaymentModal = () => els.modal.classList.remove('hidden');
    const hidePaymentModal = () => els.modal.classList.add('hidden');

    els.form.addEventListener('submit', async (event) => {
        event.preventDefault();
        setBusy(true, 'Analyzing resume with Gemini...');

        try {
            const response = await axios.post(routes.analyze, new FormData(els.form), {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            applyAnalysisPayload(response.data);
            setBusy(false, 'Analysis complete.');
        } catch (error) {
            setBusy(false, error.response?.data?.message || 'Resume analysis failed.');
        }
    });

    [els.name, els.summary, els.skills].forEach((input) => {
        input.addEventListener('input', (event) => {
            const field = event.target.dataset.field;
            state[field] = field === 'skills' ? splitList(event.target.value) : event.target.value;
            renderPreview();
        });
    });

    els.experience.addEventListener('input', (event) => {
        const block = event.target.closest('[data-experience-index]');
        if (!block) return;

        const index = Number(block.dataset.experienceIndex);
        const field = event.target.dataset.expField;

        if (field === 'point') {
            const point = event.target.closest('[data-point-index]');
            state.experience[index].points[Number(point.dataset.pointIndex)] = event.target.value;
            state.experience[index].points = state.experience[index].points.filter((item, itemIndex, items) => item || itemIndex === items.length - 1);
        } else if (field) {
            state.experience[index][field] = event.target.value;
        }

        renderPreview();
    });

    els.experience.addEventListener('click', (event) => {
        const action = event.target.closest('[data-action]')?.dataset.action;
        const block = event.target.closest('[data-experience-index]');
        if (!action || !block) return;

        const index = Number(block.dataset.experienceIndex);

        if (action === 'add-point') {
            state.experience[index].points.push('');
        }

        if (action === 'remove-point') {
            const point = event.target.closest('[data-point-index]');
            state.experience[index].points.splice(Number(point.dataset.pointIndex), 1);
        }

        if (action === 'remove-experience') {
            state.experience.splice(index, 1);
        }

        renderEditor();
        renderPreview();
    });

    els.education.addEventListener('input', (event) => {
        const row = event.target.closest('[data-education-index]');
        if (!row) return;

        state.education[Number(row.dataset.educationIndex)] = event.target.value;
        state.education = state.education.filter((item, index, items) => item || index === items.length - 1);
        renderPreview();
    });

    els.education.addEventListener('click', (event) => {
        const action = event.target.closest('[data-action]')?.dataset.action;
        const row = event.target.closest('[data-education-index]');
        if (action === 'remove-education' && row) {
            state.education.splice(Number(row.dataset.educationIndex), 1);
            renderEditor();
            renderPreview();
        }
    });

    els.addExperience.addEventListener('click', () => {
        state.experience.push({ company: '', role: '', points: [''] });
        renderEditor();
        renderPreview();
    });

    els.addEducation.addEventListener('click', () => {
        state.education.push('');
        renderEditor();
        renderPreview();
    });

    els.projects.addEventListener('input', (event) => {
        const row = event.target.closest('[data-project-index]');
        if (!row) return;
        state.projects[Number(row.dataset.projectIndex)] = event.target.value;
        state.projects = state.projects.filter((item, index, items) => item || index === items.length - 1);
        renderPreview();
    });

    els.projects.addEventListener('click', (event) => {
        const action = event.target.closest('[data-action]')?.dataset.action;
        const row = event.target.closest('[data-project-index]');
        if (action === 'remove-project' && row) {
            state.projects.splice(Number(row.dataset.projectIndex), 1);
            renderEditor();
            renderPreview();
        }
    });

    els.addProjects.addEventListener('click', () => {
        state.projects.push('');
        renderEditor();
        renderPreview();
    });

    els.improveButton.addEventListener('click', async () => {
        setBusy(true, 'Refining the edited resume...');

        try {
            const response = await axios.post(routes.improve, {
                analysis_id: analysisId,
                resume: state,
            });
            applyAnalysisPayload(response.data);
            setBusy(false, 'Resume refined.');
        } catch (error) {
            setBusy(false, error.response?.data?.message || 'Refinement failed.');
        }
    });

    els.grammarButton.addEventListener('click', async () => {
        setBusy(true, 'Fixing grammar and consistency...');

        try {
            const response = await axios.post(routes.grammar, {
                analysis_id: analysisId,
                resume: state,
            });
            applyAnalysisPayload(response.data);
            setBusy(false, 'Grammar fixed.');
        } catch (error) {
            setBusy(false, error.response?.data?.message || 'Grammar fix failed.');
        }
    });

    els.downloadButton.addEventListener('click', async () => {
        setBusy(true, 'Preparing download...');

        try {
            await downloadPdf();
            setBusy(false, isPaid ? 'Download ready.' : '');
        } catch (error) {
            setBusy(false, error.message || 'Download failed.');
        }
    });

    els.closeModal.addEventListener('click', hidePaymentModal);

    els.payButton.addEventListener('click', async () => {
        if (!window.Razorpay) {
            setBusy(false, 'Razorpay checkout could not load.');
            return;
        }

        setBusy(true, 'Creating secure payment order...');

        try {
            await saveCurrentResume();
            const { data } = await axios.post(routes.paymentOrder, { analysis_id: analysisId });

            if (data.is_paid) {
                isPaid = true;
                hidePaymentModal();
                await downloadPdf();
                setBusy(false, 'Download ready.');
                return;
            }

            const checkout = new window.Razorpay({
                key: data.key,
                amount: data.amount,
                currency: data.currency,
                name: data.name,
                description: data.description,
                order_id: data.order_id,
                handler: async (payment) => {
                    setBusy(true, 'Verifying payment...');
                    await axios.post(routes.paymentVerify, {
                        analysis_id: analysisId,
                        razorpay_order_id: payment.razorpay_order_id,
                        razorpay_payment_id: payment.razorpay_payment_id,
                        razorpay_signature: payment.razorpay_signature,
                    });
                    isPaid = true;
                    hidePaymentModal();
                    await downloadPdf();
                    setBusy(false, 'Payment verified. Download ready.');
                },
                modal: {
                    ondismiss: () => setBusy(false, ''),
                },
                theme: {
                    color: '#0f766e',
                },
            });

            checkout.open();
        } catch (error) {
            setBusy(false, error.response?.data?.message || error.message || 'Payment failed.');
        }
    });

    renderPreview();

    if (els.applyToResumeMaker) {
        els.applyToResumeMaker.addEventListener('click', () => {
            if (!analysisId) return;
            // Redirect with prefilled analysis content
            window.location.href = `/resume/create?analysis_id=${encodeURIComponent(analysisId)}`;
        });
    }
}
