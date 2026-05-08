{{--
    Drop this file at: resources/views/resume/partials/_entry-card-fix.blade.php
    OR paste the <style> block into your main resume-maker blade
    AND replace the step-tab click handler in editor-script with the JS below.
--}}

<style>
/* ══════════════════════════════════════════════════════════
   1.  ENTRY CARD — work experience / education / project
       Fix: fields were flush against each other with no gap
   ══════════════════════════════════════════════════════════ */

/* The wrapper div rendered by the JS editor */
.entry-card {
    background: var(--white, #fff);
    border: 1.5px solid rgba(0,0,0,0.1);
    border-radius: 18px;
    padding: 1.5rem 1.5rem 1.25rem;
    margin-bottom: 1.25rem;
    position: relative;
    transition: box-shadow 0.2s, border-color 0.2s;
}
.entry-card:hover {
    border-color: rgba(37,99,235,0.25);
    box-shadow: 0 4px 16px rgba(0,0,0,0.06);
}

/* Every <input> and <textarea> inside an entry card */
.entry-card input,
.entry-card textarea,
.entry-card .rp-input {
    display: block;
    width: 100%;
    padding: 0.75rem 1rem;
    margin-bottom: 0.875rem;          /* ← the key gap between fields  */
    border-radius: 12px;
    border: 1.5px solid rgba(0,0,0,0.1);
    background: #f8fafc;
    font-family: var(--font-body, 'Bricolage Grotesque', sans-serif);
    font-size: 0.9rem;
    color: #0b1221;
    line-height: 1.5;
    outline: none;
    transition: border-color 0.18s, box-shadow 0.18s, background 0.18s;
}
/* Remove margin from the very last field so the card bottom padding does the job */
.entry-card input:last-of-type,
.entry-card textarea:last-of-type {
    margin-bottom: 0;
}
.entry-card input::placeholder,
.entry-card textarea::placeholder {
    color: #94a3b8;
}
.entry-card input:focus,
.entry-card textarea:focus {
    border-color: #2563eb;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
}
.entry-card textarea {
    resize: vertical;
    min-height: 100px;
}

/* Two-column row inside entry cards (company + dates side by side) */
.entry-card .entry-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0.75rem;
    margin-bottom: 0.875rem;
}
.entry-card .entry-row input {
    margin-bottom: 0; /* row handles the spacing */
}
@media (max-width: 560px) {
    .entry-card .entry-row { grid-template-columns: 1fr; }
}

/* Remove button */
.entry-card .entry-remove {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    margin-top: 0.875rem;
    padding: 0.4rem 0.875rem;
    border-radius: 999px;
    font-size: 0.78rem;
    font-weight: 700;
    color: #64748b;
    background: #f1f5f9;
    border: 1.5px solid rgba(0,0,0,0.07);
    cursor: pointer;
    transition: all 0.2s;
    font-family: var(--font-body, sans-serif);
}
.entry-card .entry-remove:hover {
    background: #fee2e2;
    color: #dc2626;
    border-color: rgba(220,38,38,0.2);
}

/* ══════════════════════════════════════════════════════════
   2.  STEP NAV — active indicator must follow the current step
   ══════════════════════════════════════════════════════════ */
</style>

<script>
(function () {
    /* ── helpers ── */
    function setActiveStep(step) {
        const n = parseInt(step);

        /* update tab indicators */
        document.querySelectorAll('.rp-step-tab').forEach(tab => {
            const t = parseInt(tab.dataset.step);
            tab.classList.remove('active', 'completed');
            if (t === n)      tab.classList.add('active');
            else if (t < n)   tab.classList.add('completed');

            /* swap icon to check-mark when completed */
            const icon = tab.querySelector('.rp-step-icon');
            if (icon) {
                if (t < n) {
                    icon.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>`;
                } else {
                    icon.textContent = t; /* reset to number */
                }
            }
        });

        /* show / hide step content panels */
        document.querySelectorAll('.rp-step-content').forEach(panel => {
            panel.classList.toggle('active', parseInt(panel.dataset.step) === n);
        });
    }

    /* ── wire step tabs (clicking a completed tab goes back) ── */
    document.querySelectorAll('.rp-step-tab').forEach(tab => {
        tab.addEventListener('click', () => {
            const target = parseInt(tab.dataset.step);
            /* only allow jumping to already-visited steps or current */
            const current = parseInt(document.querySelector('.rp-step-tab.active')?.dataset.step || 1);
            if (target <= current) setActiveStep(target);
        });
    });

    /* ── wire next / prev buttons ── */
    function wireNav(btnId, direction) {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        btn.addEventListener('click', () => {
            const current = parseInt(document.querySelector('.rp-step-tab.active')?.dataset.step || 1);
            setActiveStep(current + direction);
        });
    }

    wireNav('next-step-1',  +1);
    wireNav('next-step-2',  +1);
    wireNav('next-step-3',  +1);
    wireNav('prev-step-2',  -1);
    wireNav('prev-step-3',  -1);
    wireNav('prev-step-4',  -1);

    /* ── "Back to Edit" from completion panel ── */
    document.getElementById('edit-resume')?.addEventListener('click', () => {
        document.getElementById('completion-panel').style.display = 'none';
        setActiveStep(4);
    });

    /* ── initialise on load ── */
    setActiveStep(1);
})();
</script>