@extends('layouts.app')

@section('title', 'ATS Checker - CVBliss')

@section('content')
<style>
    .ats-page { background:#f8fafc; min-height:100vh; color:#0f172a; }
    .ats-wrap { max-width:1180px; margin:0 auto; padding:48px 18px 72px; }
    .ats-hero { display:grid; grid-template-columns:minmax(0, .9fr) minmax(320px, 1.1fr); gap:28px; align-items:stretch; }
    .ats-title { font-size:clamp(38px, 5vw, 64px); line-height:1; letter-spacing:0; font-weight:850; margin:12px 0 14px; }
    .ats-copy { color:#475569; font-size:17px; line-height:1.7; max-width:560px; }
    .ats-kicker { display:inline-flex; border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; border-radius:999px; padding:7px 12px; font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; }
    .ats-card { background:#fff; border:1px solid #e2e8f0; border-radius:8px; box-shadow:0 24px 70px rgba(15,23,42,.08); padding:24px; }
    .ats-form-grid { display:grid; gap:14px; }
    .ats-drop { border:2px dashed #bfdbfe; border-radius:8px; padding:28px; background:#eff6ff; cursor:pointer; text-align:center; }
    .ats-drop strong { display:block; font-size:18px; }
    .ats-drop span { color:#64748b; font-size:14px; }
    .ats-field { display:flex; flex-direction:column; gap:7px; }
    .ats-field label { font-size:13px; font-weight:800; color:#334155; }
    .ats-field input, .ats-field textarea { border:1px solid #cbd5e1; border-radius:8px; padding:12px; font:inherit; outline:none; }
    .ats-field textarea { min-height:110px; resize:vertical; }
    .ats-submit { border:0; border-radius:8px; background:#0f172a; color:#fff; min-height:48px; font-weight:800; cursor:pointer; }
    .ats-submit:disabled { opacity:.6; cursor:not-allowed; }
    .ats-results { display:none; margin-top:28px; grid-template-columns:260px 1fr; gap:18px; }
    .ats-results.active { display:grid; }
    .score-panel { background:#0f172a; color:#fff; border-radius:8px; padding:24px; }
    .score-num { font-size:64px; line-height:1; font-weight:850; }
    .score-track { height:10px; background:rgba(255,255,255,.14); border-radius:999px; overflow:hidden; margin:18px 0; }
    .score-fill { height:100%; width:0%; background:#22c55e; border-radius:999px; transition:width .4s ease; }
    .insight-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
    .insight { background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:20px; }
    .insight h3 { font-size:16px; margin-bottom:12px; }
    .insight ul { margin:0; padding-left:18px; color:#334155; line-height:1.7; }
    .keywords { display:flex; flex-wrap:wrap; gap:8px; }
    .kw { display:inline-flex; border-radius:999px; background:#fef3c7; color:#92400e; padding:7px 11px; font-size:13px; font-weight:800; }
    .ats-status { margin-top:10px; min-height:22px; color:#475569; font-size:14px; }
    @media (max-width:900px) { .ats-hero, .ats-results { grid-template-columns:1fr; } .insight-grid { grid-template-columns:1fr; } }
    @media (max-width:560px) { .ats-wrap { padding:34px 14px 56px; } .ats-card { padding:18px; } }
</style>

<main class="ats-page">
    <div class="ats-wrap">
        <section class="ats-hero">
            <div>
                <span class="ats-kicker">ATS Checker</span>
                <h1 class="ats-title">Score your resume before recruiters do.</h1>
                <p class="ats-copy">Upload a PDF, DOC, DOCX, PPT, or PPTX resume and get a dynamic ATS score, missing keyword analysis, and suggestions based on the content you actually provide.</p>
            </div>
            <form id="atsForm" class="ats-card ats-form-grid">
                @csrf
                <input id="atsFile" name="resume" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx" hidden required>
                <div id="atsDrop" class="ats-drop">
                    <strong id="atsFileName">Upload your resume</strong>
                    <span>PDF, DOC, DOCX, PPT, PPTX up to 10 MB</span>
                </div>
                <div class="ats-field">
                    <label for="atsRole">Target role</label>
                    <input id="atsRole" name="job_role" placeholder="Web Developer, Data Analyst, Product Designer">
                </div>
                <div class="ats-field">
                    <label for="atsJob">Job description or keywords</label>
                    <textarea id="atsJob" name="job_description" placeholder="Paste the job description to compare against the resume."></textarea>
                </div>
                <button id="atsSubmit" class="ats-submit" type="submit">Analyze Resume</button>
                <div id="atsStatus" class="ats-status" role="status"></div>
            </form>
        </section>

        <section id="atsResults" class="ats-results">
            <aside class="score-panel">
                <div class="text-sm font-bold uppercase tracking-widest opacity-60">ATS Score</div>
                <div><span id="atsScore" class="score-num">0</span><span class="opacity-60">/100</span></div>
                <div class="score-track"><div id="atsScoreFill" class="score-fill"></div></div>
                <div id="atsVerdict" class="font-bold">Upload a resume to begin</div>
            </aside>
            <div class="insight-grid">
                <div class="insight"><h3>Strengths</h3><ul id="atsStrengths"></ul></div>
                <div class="insight"><h3>Weaknesses</h3><ul id="atsWeaknesses"></ul></div>
                <div class="insight"><h3>Missing Keywords</h3><div id="atsKeywords" class="keywords"></div></div>
                <div class="insight"><h3>Suggestions</h3><ul id="atsSuggestions"></ul></div>
            </div>
        </section>
    </div>
</main>

<script>
(() => {
    const form = document.getElementById('atsForm');
    const file = document.getElementById('atsFile');
    const drop = document.getElementById('atsDrop');
    const submit = document.getElementById('atsSubmit');
    const status = document.getElementById('atsStatus');
    const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
    const list = (id, items) => { document.getElementById(id).innerHTML = (items || []).length ? items.map(i => `<li>${esc(i)}</li>`).join('') : '<li>No issues found.</li>'; };
    drop.addEventListener('click', () => file.click());
    file.addEventListener('change', () => { document.getElementById('atsFileName').textContent = file.files[0]?.name || 'Upload your resume'; });
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!file.files[0]) { status.textContent = 'Please upload your resume first.'; return; }
        submit.disabled = true;
        status.textContent = 'Analyzing resume...';
        const fd = new FormData(form);
        try {
            const res = await fetch('{{ route('resume.analyze') }}', { method:'POST', body:fd, headers:{ 'Accept':'application/json' } });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data.success) throw new Error(data.message || 'Could not analyze this resume.');
            const score = Math.max(0, Math.min(100, Number(data.score || 0)));
            document.getElementById('atsResults').classList.add('active');
            document.getElementById('atsScore').textContent = score;
            document.getElementById('atsScoreFill').style.width = score + '%';
            document.getElementById('atsVerdict').textContent = score >= 80 ? 'Strong ATS fit' : (score >= 60 ? 'Good base, needs tuning' : 'Needs focused improvements');
            list('atsStrengths', data.strengths);
            list('atsWeaknesses', data.weaknesses);
            list('atsSuggestions', data.suggestions);
            document.getElementById('atsKeywords').innerHTML = (data.missing_keywords || []).length ? data.missing_keywords.map(k => `<span class="kw">${esc(k)}</span>`).join('') : '<span class="text-slate-500 text-sm">No major missing keywords detected.</span>';
            status.textContent = 'Analysis complete.';
        } catch (error) {
            status.textContent = error.message;
        } finally {
            submit.disabled = false;
        }
    });
})();
</script>
@endsection
