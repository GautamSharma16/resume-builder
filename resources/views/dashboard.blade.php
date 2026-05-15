<x-app-layout>
<style>
    @import url('https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=Bricolage+Grotesque:wght@300;400;500;600;700;800&display=swap');

    :root {
        --blue:        #2563eb;
        --blue-dark:   #1d4ed8;
        --blue-light:  #eff6ff;
        --blue-glow:   rgba(37,99,235,0.15);
        --navy:        #0b1221;
        --ink:         #1e293b;
        --muted:       #64748b;
        --soft:        #94a3b8;
        --surface:     #f8fafc;
        --surface-2:   #f1f5f9;
        --border:      rgba(0,0,0,0.07);
        --white:       #ffffff;
        --gold:        #f59e0b;
        --green:       #10b981;
        --green-light: #d1fae5;
        --purple:      #8b5cf6;
        --pink:        #ec4899;
        --font-display: 'DM Serif Display', serif;
        --font-body:    'Bricolage Grotesque', sans-serif;
        --r-sm:  6px; --r-md: 12px; --r-lg: 18px; --r-xl: 28px; --r-2xl: 36px; --r-full: 999px;
        --ease-spring: cubic-bezier(0.175, 0.885, 0.32, 1.275);
        --ease-out: cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    .dash-root {
        font-family: var(--font-body);
        background: linear-gradient(135deg, #ffffff 0%, #fafcff 50%, #f5f7ff 100%);
        min-height: 100vh;
        color: var(--ink);
        position: relative;
        overflow-x: hidden;
        -webkit-font-smoothing: antialiased;
    }

    /* Noise overlay */
    .dash-root::after {
        content: '';
        position: fixed; inset: 0; pointer-events: none; z-index: 9999; opacity: 0.02;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        background-size: 200px;
    }

    /* Background orbs */
    .bg-orb {
        position: fixed; border-radius: 50%; pointer-events: none; z-index: 0;
    }
    .bg-orb-1 {
        width: 700px; height: 700px;
        background: radial-gradient(circle, rgba(37,99,235,0.06), transparent 70%);
        top: -300px; left: -200px;
    }
    .bg-orb-2 {
        width: 500px; height: 500px;
        background: radial-gradient(circle, rgba(139,92,246,0.05), transparent 70%);
        bottom: -200px; right: -150px;
    }

    /* Grid bg */
    .dash-grid-bg {
        position: fixed; inset: 0; pointer-events: none; z-index: 0;
        background-image:
            linear-gradient(rgba(37,99,235,0.025) 1px, transparent 1px),
            linear-gradient(90deg, rgba(37,99,235,0.025) 1px, transparent 1px);
        background-size: 48px 48px;
        mask-image: radial-gradient(ellipse 80% 80% at 50% 50%, black 20%, transparent 90%);
    }

    .dash-container {
        max-width: 1280px;
        margin: 0 auto;
        padding: 48px 32px 80px;
        position: relative;
        z-index: 1;
    }

    @keyframes fadeUp { from { opacity: 0; transform: translateY(28px); } to { opacity: 1; transform: translateY(0); } }
    @keyframes pulse-ring { 0% { box-shadow: 0 0 0 0 rgba(37,99,235,0.4); } 70% { box-shadow: 0 0 0 12px rgba(37,99,235,0); } 100% { box-shadow: 0 0 0 0 rgba(37,99,235,0); } }
    @keyframes gradient-shift { 0%,100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
    @keyframes spin-slow { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
    @keyframes floatY { 0%,100% { transform: translateY(0); } 50% { transform: translateY(-8px); } }

    /* ── WELCOME HEADER ── */
    .welcome-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 52px;
        gap: 24px;
        flex-wrap: wrap;
        animation: fadeUp 0.6s var(--ease-out) 0.05s both;
    }

    .greeting-block h1 {
        font-family: var(--font-display);
        font-size: clamp(2.2rem, 4vw, 3.4rem);
        font-weight: 400;
        color: var(--navy);
        line-height: 1.1;
        margin-bottom: 10px;
    }
    .greeting-block h1 .name-em {
        font-style: italic;
        background: linear-gradient(135deg, var(--blue), var(--purple), var(--pink));
        -webkit-background-clip: text; background-clip: text; color: transparent;
        background-size: 200% 200%;
        animation: gradient-shift 6s ease infinite;
    }
    .greeting-block .sub {
        font-size: 1rem;
        color: var(--muted);
        font-weight: 400;
    }

    /* Score pill */
    .score-pill {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 14px 24px;
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(12px);
        border-radius: var(--r-full);
        border: 1px solid rgba(37,99,235,0.15);
        box-shadow: 0 4px 20px rgba(0,0,0,0.06);
    }
    .score-ring {
        position: relative; width: 58px; height: 58px;
    }
    .score-ring svg { position: absolute; inset: 0; transform: rotate(-90deg); }
    .score-ring .ring-track { fill: none; stroke: #e2e8f0; stroke-width: 5; }
    .score-ring .ring-fill  { fill: none; stroke: url(#ringGrad); stroke-width: 5; stroke-linecap: round; stroke-dasharray: 164; stroke-dashoffset: 24; transition: stroke-dashoffset 1s var(--ease-spring); }
    .score-ring .ring-val {
        position: absolute; inset: 0;
        display: flex; align-items: center; justify-content: center;
        font-family: var(--font-display); font-size: 1.15rem; color: var(--blue);
    }
    .score-info .score-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.12em; color: var(--muted); font-weight: 700; margin-bottom: 3px; }
    .score-info .score-status { font-size: 0.88rem; font-weight: 600; color: var(--navy); }

    /* ── QUICK ACTIONS ── */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 52px;
        animation: fadeUp 0.6s var(--ease-out) 0.15s both;
    }

    .action-card {
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(12px);
        border: 1px solid var(--border);
        border-radius: var(--r-xl);
        padding: 28px 24px;
        text-decoration: none;
        color: inherit;
        display: flex;
        align-items: flex-start;
        gap: 18px;
        transition: all 0.35s var(--ease-spring);
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    .action-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 3px;
        background: linear-gradient(90deg, var(--blue), var(--purple), var(--pink));
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.4s var(--ease-spring);
    }
    .action-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.09);
        border-color: rgba(37,99,235,0.2);
        background: rgba(255,255,255,0.96);
    }
    .action-card:hover::before { transform: scaleX(1); }

    .action-icon-wrap {
        width: 50px; height: 50px; flex-shrink: 0;
        background: var(--blue-light);
        border-radius: var(--r-lg);
        display: flex; align-items: center; justify-content: center;
        color: var(--blue);
        transition: all 0.3s var(--ease-spring);
    }
    .action-card:hover .action-icon-wrap {
        transform: scale(1.1) rotate(-5deg);
        background: var(--blue);
        color: white;
        box-shadow: 0 8px 20px rgba(37,99,235,0.3);
    }
    .action-text h3 { font-size: 1rem; font-weight: 700; color: var(--navy); margin-bottom: 5px; }
    .action-text p { font-size: 0.8rem; color: var(--muted); line-height: 1.5; }

    /* ── MAIN LAYOUT ── */
    .main-layout {
        display: grid;
        grid-template-columns: 1fr 320px;
        gap: 36px;
        animation: fadeUp 0.6s var(--ease-out) 0.25s both;
    }

    /* Section header */
    .sec-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    .sec-header h2 {
        font-family: var(--font-display);
        font-size: 1.6rem;
        font-weight: 400;
        color: var(--navy);
    }
    .sec-header h2 em {
        font-style: italic;
        background: linear-gradient(135deg, var(--blue), var(--purple));
        -webkit-background-clip: text; background-clip: text; color: transparent;
    }
    .view-all-link {
        font-size: 0.8rem; color: var(--blue); font-weight: 700;
        text-decoration: none; display: inline-flex; align-items: center; gap: 4px;
        transition: gap 0.25s;
    }
    .view-all-link:hover { gap: 8px; }

    /* ── DOCS GRID ── */
    .docs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 18px;
        margin-bottom: 52px;
    }

    .doc-card {
        background: rgba(255,255,255,0.9);
        border: 1px solid var(--border);
        border-radius: var(--r-xl);
        overflow: hidden;
        transition: all 0.35s var(--ease-spring);
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        cursor: pointer;
    }
    .doc-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        border-color: rgba(37,99,235,0.2);
    }

    /* Resume mini preview */
    .doc-preview-wrap {
        position: relative;
        height: 200px;
        background: #f8fafc;
        overflow: hidden;
        border-bottom: 1px solid var(--border);
    }

    /*
     * Scale-down approach: 794px A4 → ~200px card
     * We scale from top-left then translate to centre it.
     * Scale = cardWidth / 794. Card ~200px wide → scale ≈ 0.252
     * After scale(s) the element visually occupies 794*s px wide.
     * To centre: translateX((cardWidth - 794*s) / 2)
     * At s=0.252, cardWidth=200: (200 - 200.1) / 2 ≈ 0px → almost perfect
     * Use JS to set exact per-card centering; CSS handles the rest.
     */
    .doc-preview-scaler {
        position: absolute;
        top: 0;
        left: 0;
        width: 794px;
        transform-origin: top left;
        transform: scale(0.252);
        pointer-events: none;
    }

    /* Fallback placeholder resume */
    .doc-preview-placeholder {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
    }
    .resume-mock {
        width: 130px; background: white;
        border-radius: 6px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        overflow: hidden;
        flex-shrink: 0;
    }
    .resume-mock .mock-header {
        height: 38px;
        display: flex; flex-direction: column;
        justify-content: center;
        padding: 0 10px;
        gap: 4px;
    }
    .resume-mock .mock-name { height: 7px; border-radius: 3px; background: rgba(255,255,255,0.9); width: 65%; }
    .resume-mock .mock-title { height: 4px; border-radius: 2px; background: rgba(255,255,255,0.5); width: 42%; }
    .resume-mock .mock-body { padding: 8px 10px; }
    .resume-mock .mock-line { height: 3px; border-radius: 2px; background: #e2e8f0; margin-bottom: 4px; }
    .resume-mock .mock-line.s { width: 60%; }
    .resume-mock .mock-line.xs { width: 40%; }
    .resume-mock .mock-section { height: 4px; border-radius: 2px; background: var(--blue); width: 45%; margin: 7px 0 5px; opacity: 0.6; }

    /* Cover letter mock */
    .letter-mock {
        width: 130px; background: white;
        border-radius: 6px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.1);
        padding: 10px;
        overflow: hidden;
    }
    .letter-mock .lm-top { display: flex; justify-content: space-between; margin-bottom: 8px; }
    .letter-mock .lm-logo { width: 24px; height: 24px; border-radius: 4px; background: linear-gradient(135deg, var(--blue), var(--purple)); }
    .letter-mock .lm-date { width: 50px; height: 5px; border-radius: 2px; background: #e2e8f0; margin-top: 10px; }
    .letter-mock .lm-addr { height: 3px; border-radius: 2px; background: #e2e8f0; margin-bottom: 3px; }
    .letter-mock .lm-addr.s { width: 60%; }
    .letter-mock .lm-body { margin-top: 8px; }
    .letter-mock .lm-line { height: 3px; border-radius: 2px; background: #e2e8f0; margin-bottom: 4px; }
    .letter-mock .lm-line.s { width: 65%; }
    .letter-mock .lm-sig { height: 4px; border-radius: 2px; background: #cbd5e1; width: 40%; margin-top: 8px; }

    /* Doc overlay */
    .doc-overlay {
        position: absolute; inset: 0;
        background: rgba(11,18,33,0.55);
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        gap: 10px;
        opacity: 0;
        transition: opacity 0.25s;
        border-radius: 0;
    }
    .doc-card:hover .doc-overlay { opacity: 1; }
    .overlay-actions { display: flex; gap: 10px; }
    .overlay-btn {
        width: 40px; height: 40px;
        background: white; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        text-decoration: none; color: var(--navy);
        transition: all 0.2s var(--ease-spring);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .overlay-btn:hover { transform: scale(1.12); background: var(--blue); color: white; }
    .overlay-btn svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; }

    .doc-meta-row {
        padding: 14px 16px;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }
    .doc-title {
        font-size: 0.88rem; font-weight: 700; color: var(--navy);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        margin-bottom: 4px;
    }
    .doc-date { font-size: 0.72rem; color: var(--muted); margin-bottom: 6px; }
    .doc-rename-btn {
        font-size: 0.7rem; color: var(--blue); font-weight: 700;
        background: none; border: none; cursor: pointer; padding: 0;
        width: fit-content;
        transition: color 0.2s;
    }
    .doc-rename-btn:hover { color: var(--blue-dark); }
    .doc-rename-form {
        display: none;
        margin-top: 4px;
        gap: 7px;
        align-items: center;
        width: 100%;
    }
    .doc-rename-form.active { display: grid; grid-template-columns: minmax(0, 1fr) auto auto; }
    .doc-rename-input {
        flex: 1;
        min-width: 0;
        border: 1px solid rgba(37,99,235,0.22);
        border-radius: 10px;
        padding: 7px 9px;
        font-size: 12px;
        font-weight: 700;
        color: var(--navy);
        background: #fff;
        outline: none;
        box-shadow: 0 6px 16px rgba(15,23,42,0.05);
    }
    .doc-rename-save, .doc-rename-cancel {
        border: 0;
        border-radius: 10px;
        min-height: 32px;
        padding: 0 10px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .doc-rename-save { background: var(--blue); color: #fff; }
    .doc-rename-cancel { background: #eef2ff; color: var(--muted); }
    .doc-card.is-renaming .doc-title,
    .doc-card.is-renaming .doc-date { display: none; }
    @media (max-width: 520px) {
        .doc-rename-form.active { grid-template-columns: 1fr 1fr; }
        .doc-rename-input { grid-column: 1 / -1; }
    }

    /* Empty state */
    .empty-state {
        grid-column: 1 / -1;
        padding: 60px 40px;
        text-align: center;
        background: rgba(255,255,255,0.7);
        border: 1px dashed rgba(37,99,235,0.2);
        border-radius: var(--r-xl);
    }
    .empty-state svg { opacity: 0.3; margin-bottom: 16px; stroke: var(--blue); }
    .empty-state h3 { font-family: var(--font-display); font-size: 1.3rem; color: var(--navy); margin-bottom: 8px; }
    .empty-state p { font-size: 0.85rem; color: var(--muted); margin-bottom: 16px; }
    .empty-state a {
        display: inline-flex; align-items: center; gap: 6px;
        background: linear-gradient(135deg, var(--blue), var(--blue-dark));
        color: white; text-decoration: none;
        padding: 0.7rem 1.5rem; border-radius: var(--r-full);
        font-size: 0.82rem; font-weight: 700;
        box-shadow: 0 4px 16px rgba(37,99,235,0.3);
        transition: all 0.3s var(--ease-spring);
    }
    .empty-state a:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(37,99,235,0.4); }

    /* ── SIDEBAR ── */
    .sidebar { display: flex; flex-direction: column; gap: 24px; }

    /* Plan card */
    .plan-card {
        background: linear-gradient(135deg, var(--navy) 0%, #0f172a 100%);
        border-radius: var(--r-xl);
        padding: 28px 24px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.07);
        box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }
    .plan-card::before {
        content: '';
        position: absolute; top: -100px; right: -80px;
        width: 250px; height: 250px; border-radius: 50%;
        background: radial-gradient(circle, rgba(37,99,235,0.2), transparent 70%);
        pointer-events: none;
    }
    .plan-card::after {
        content: '';
        position: absolute; bottom: -80px; left: -60px;
        width: 200px; height: 200px; border-radius: 50%;
        background: radial-gradient(circle, rgba(139,92,246,0.15), transparent 70%);
        pointer-events: none;
    }
    .plan-card-inner { position: relative; z-index: 1; }

    .plan-top {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 20px;
    }
    .plan-badge-pill {
        display: inline-flex; align-items: center; gap: 6px;
        background: rgba(37,99,235,0.2);
        border: 1px solid rgba(37,99,235,0.4);
        border-radius: var(--r-full);
        padding: 4px 12px;
        font-size: 0.68rem; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase;
        color: #93c5fd;
    }
    .plan-badge-pill .dot { width: 6px; height: 6px; background: #60a5fa; border-radius: 50%; animation: pulse-ring 2s infinite; }

    .plan-stat-list { margin-bottom: 22px; }
    .plan-stat-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid rgba(255,255,255,0.05);
        font-size: 0.82rem;
    }
    .plan-stat-row:last-child { border-bottom: none; }
    .plan-stat-label { color: rgba(255,255,255,0.45); font-weight: 500; }
    .plan-stat-val { color: white; font-weight: 700; }
    .status-active { color: #34d399; }
    .status-limited { color: #fbbf24; }

    .upgrade-btn {
        display: block; width: 100%; text-align: center;
        padding: 12px; border-radius: var(--r-full);
        font-size: 0.85rem; font-weight: 800;
        text-decoration: none; transition: all 0.3s var(--ease-spring);
        letter-spacing: 0.02em;
    }
    .upgrade-btn.primary {
        background: linear-gradient(135deg, var(--blue), var(--blue-dark));
        color: white;
        box-shadow: 0 6px 20px rgba(37,99,235,0.4);
    }
    .upgrade-btn.primary:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(37,99,235,0.5); }
    .upgrade-btn.secondary {
        background: rgba(255,255,255,0.07);
        color: rgba(255,255,255,0.7);
        border: 1px solid rgba(255,255,255,0.12);
    }
    .upgrade-btn.secondary:hover { background: rgba(255,255,255,0.12); color: white; transform: translateY(-2px); }

    /* Insights card */
    .insights-card {
        background: rgba(255,255,255,0.88);
        backdrop-filter: blur(12px);
        border: 1px solid var(--border);
        border-radius: var(--r-xl);
        padding: 24px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    }
    .insights-card-header {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 20px;
    }
    .insights-card-header h3 {
        font-family: var(--font-display); font-size: 1.15rem; font-weight: 400; color: var(--navy);
    }
    .insights-card-header .ai-badge {
        display: flex; align-items: center; gap: 5px;
        font-size: 0.65rem; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase;
        color: var(--blue);
        background: var(--blue-light); border-radius: var(--r-full); padding: 3px 9px;
    }

    .insight-item {
        display: flex; gap: 12px; margin-bottom: 16px;
        padding-bottom: 16px; border-bottom: 1px solid var(--border);
    }
    .insight-item:last-child { margin-bottom: 0; padding-bottom: 0; border-bottom: none; }
    .insight-dot-wrap {
        width: 28px; height: 28px; flex-shrink: 0;
        background: var(--blue-light); border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        margin-top: 1px;
    }
    .insight-dot-wrap svg { width: 13px; height: 13px; stroke: var(--blue); fill: none; stroke-width: 2; }
    .insight-text { font-size: 0.8rem; color: var(--muted); line-height: 1.55; }

    .run-audit-btn {
        display: block; text-align: center; margin-top: 18px;
        padding: 11px; border-radius: var(--r-full);
        font-size: 0.8rem; font-weight: 800;
        background: var(--blue-light); color: var(--blue);
        text-decoration: none;
        transition: all 0.3s var(--ease-spring);
        border: 1px solid rgba(37,99,235,0.15);
    }
    .run-audit-btn:hover { background: var(--blue); color: white; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(37,99,235,0.25); }

    /* Label chip */
    .section-chip {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 0.68rem; font-weight: 800; letter-spacing: 0.12em; text-transform: uppercase;
        color: var(--blue);
        background: var(--blue-light); border-radius: var(--r-full);
        padding: 4px 12px; margin-bottom: 12px;
    }
    .section-chip::before {
        content: ''; display: block; width: 6px; height: 6px;
        background: var(--blue); border-radius: 50%;
    }

    /* Responsive */
    @media (max-width: 1200px) {
        .main-layout { grid-template-columns: 1fr 290px; gap: 28px; }
    }
    @media (max-width: 1024px) {
        .main-layout { grid-template-columns: 1fr; }
        .quick-actions { grid-template-columns: repeat(2, 1fr); }
        .sidebar { display: grid; grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .dash-container { padding: 32px 20px 60px; }
        .welcome-header { flex-direction: column; align-items: flex-start; }
        .score-pill { width: 100%; }
        .quick-actions { grid-template-columns: 1fr; }
        .sidebar { grid-template-columns: 1fr; }
        .docs-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 480px) {
        .docs-grid { grid-template-columns: 1fr; }
    }
</style>

<div class="dash-root">
    <div class="bg-orb bg-orb-1"></div>
    <div class="bg-orb bg-orb-2"></div>
    <div class="dash-grid-bg"></div>

    <div class="dash-container">

        {{-- WELCOME HEADER --}}
        <div class="welcome-header">
            <div class="greeting-block">
                <h1>Welcome back,<br><em class="name-em">{{ Auth::user()->name }}</em></h1>
                <p class="sub">Your career tools are ready for action.</p>
            </div>

        </div>

        {{-- QUICK ACTIONS --}}
        <div class="quick-actions">
            <a href="{{ route('resume-maker') }}" class="action-card">
                <div class="action-icon-wrap">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                </div>
                <div class="action-text">
                    <h3>Create New Resume</h3>
                    <p>Start with a premium template and AI-guided content.</p>
                </div>
            </a>
            <a href="{{ route('enhance-cv') }}" class="action-card">
                <div class="action-icon-wrap">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="action-text">
                    <h3>Enhance Existing CV</h3>
                    <p>Upload your CV and let AI optimize it for ATS.</p>
                </div>
            </a>
            <a href="{{ route('cover-letter') }}" class="action-card">
                <div class="action-icon-wrap">
                    <svg width="22" height="22" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div class="action-text">
                    <h3>New Cover Letter</h3>
                    <p>Generate a tailored cover letter for any job role.</p>
                </div>
            </a>
        </div>

        {{-- MAIN LAYOUT --}}
        <div class="main-layout">

            {{-- LEFT: Documents --}}
            <div class="content-left">

                {{-- Recent Resumes --}}
                <div class="sec-header">
                    <div>
                        <div class="section-chip">Recent Resumes</div>
                        <h2><em>My Resumes</em></h2>
                    </div>
                    <a href="{{ route('resume.index') }}" class="view-all-link">View all →</a>
                </div>

                <div class="docs-grid">
                    @forelse($recentResumes as $resume)
                        <div class="doc-card">
                            <div class="doc-preview-wrap">
                                @if(!empty($recentResumePreviews[$resume->id]))
                                    <div class="doc-preview-scaler">
                                        {!! $recentResumePreviews[$resume->id] !!}
                                    </div>
                                @elseif($resume->template && $resume->template->thumbnail)
                                    <div class="doc-preview-placeholder">
                                        <img src="{{ asset('storage/' . $resume->template->thumbnail) }}" alt="{{ $resume->title }}" style="width:100%;height:100%;object-fit:cover;opacity:0.7;">
                                    </div>
                                @else
                                    <div class="doc-preview-placeholder">
                                        <div class="resume-mock">
                                            <div class="mock-header" style="background:linear-gradient(135deg,#1e3a5f,#2563eb);">
                                                <div class="mock-name"></div>
                                                <div class="mock-title"></div>
                                            </div>
                                            <div class="mock-body">
                                                <div class="mock-section"></div>
                                                <div class="mock-line"></div>
                                                <div class="mock-line s"></div>
                                                <div class="mock-line xs"></div>
                                                <div class="mock-section"></div>
                                                <div class="mock-line"></div>
                                                <div class="mock-line s"></div>
                                                <div class="mock-section"></div>
                                                <div class="mock-line"></div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                <div class="doc-overlay">
                                    <div class="overlay-actions">
                                        <a href="{{ route('resume.edit', $resume) }}" class="overlay-btn" title="Edit">
                                            <svg viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>
                                        <a href="{{ route('resume.download', $resume) }}" class="overlay-btn" title="Download">
                                            <svg viewBox="0 0 24 24"><path d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="doc-meta-row">
                                <div class="doc-title">{{ $resume->title }}</div>
                                <div class="doc-date">{{ $resume->created_at->format('M d, Y') }} · {{ $resume->template->name ?? 'Standard' }}</div>
                                <button class="doc-rename-btn js-rename-resume" data-id="{{ $resume->id }}" data-title="{{ $resume->title }}">Rename</button>
                                <form method="POST" action="{{ route('resume.rename', $resume) }}" class="doc-rename-form js-rename-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="title" class="doc-rename-input" value="{{ $resume->title }}" maxlength="160" required>
                                    <button type="submit" class="doc-rename-save">Save</button>
                                    <button type="button" class="doc-rename-cancel js-rename-cancel">Cancel</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <svg width="56" height="56" fill="none" viewBox="0 0 24 24" stroke-width="1.2"><path d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                            <h3>No resumes yet</h3>
                            <p>Create your first professional resume in minutes.</p>
                            <a href="{{ route('resume-maker') }}">Build My Resume →</a>
                        </div>
                    @endforelse
                </div>

                {{-- Cover Letters --}}
                <div class="sec-header" style="margin-top: 12px;">
                    <div>
                        <div class="section-chip">Cover Letters</div>
                        <h2><em>My Letters</em></h2>
                    </div>
                    <a href="{{ route('dashboard.cover-letters') }}" class="view-all-link">View all →</a>
                </div>

                <div class="docs-grid">
                    @forelse($recentCoverLetters as $letter)
                        <div class="doc-card">
                            <div class="doc-preview-wrap">
                                @if(!empty($recentCoverLetterPreviews[$letter->id]))
                                    <div class="doc-preview-scaler">
                                        {!! $recentCoverLetterPreviews[$letter->id] !!}
                                    </div>
                                @else
                                    <div class="doc-preview-placeholder">
                                        <div class="letter-mock">
                                            <div class="lm-top">
                                                <div class="lm-logo"></div>
                                                <div class="lm-date"></div>
                                            </div>
                                            <div class="lm-addr"></div>
                                            <div class="lm-addr s"></div>
                                            <div class="lm-addr"></div>
                                            <div class="lm-body">
                                                <div class="lm-line"></div>
                                                <div class="lm-line s"></div>
                                                <div class="lm-line"></div>
                                                <div class="lm-line"></div>
                                                <div class="lm-line s"></div>
                                                <div class="lm-line"></div>
                                                <div class="lm-line s"></div>
                                            </div>
                                            <div class="lm-sig"></div>
                                        </div>
                                    </div>
                                @endif
                                <div class="doc-overlay">
                                    <div class="overlay-actions">
                                        <a href="{{ route('cover-letter') }}?edit={{ $letter->id }}" class="overlay-btn" title="Edit">
                                            <svg viewBox="0 0 24 24"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        </a>
                                        <a href="{{ route('cover-letter.download', $letter) }}" class="overlay-btn" title="Download">
                                            <svg viewBox="0 0 24 24"><path d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="doc-meta-row">
                                <div class="doc-title">{{ $letter->job_role ?: ($letter->data['job_role'] ?? 'Cover Letter') }}</div>
                                <div class="doc-date">{{ $letter->created_at->format('M d, Y') }} · {{ $letter->data['company'] ?? 'General' }}</div>
                                <button class="doc-rename-btn js-rename-letter" data-id="{{ $letter->id }}" data-title="{{ $letter->job_role ?: ($letter->data['job_role'] ?? 'Cover Letter') }}">Rename</button>
                                <form method="POST" action="{{ route('cover-letter.rename', $letter) }}" class="doc-rename-form js-rename-form">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="title" class="doc-rename-input" value="{{ $letter->job_role ?: ($letter->data['job_role'] ?? 'Cover Letter') }}" maxlength="160" required>
                                    <button type="submit" class="doc-rename-save">Save</button>
                                    <button type="button" class="doc-rename-cancel js-rename-cancel">Cancel</button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <svg width="56" height="56" fill="none" viewBox="0 0 24 24" stroke-width="1.2"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            <h3>No cover letters yet</h3>
                            <p>Generate a winning cover letter with AI.</p>
                            <a href="{{ route('cover-letter') }}">Create Now →</a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- RIGHT: Sidebar --}}
            <div class="sidebar">

                {{-- Plan Card --}}
                <div class="plan-card">
                    <div class="plan-card-inner">
                        <div class="plan-top">
                            <div class="plan-badge-pill">
                                <span class="dot"></span>
                                {{ $activeSubscription ? ($activeSubscription->plan->name ?? 'Premium') : 'Free Explorer' }}
                            </div>
                            @if($activeSubscription)
                                <svg width="20" height="20" viewBox="0 0 20 20" fill="#60a5fa"><path d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                            @endif
                        </div>
                        <div class="plan-stat-list">
                            <div class="plan-stat-row">
                                <span class="plan-stat-label">Status</span>
                                <span class="plan-stat-val {{ $activeSubscription ? 'status-active' : 'status-limited' }}">
                                    {{ $activeSubscription ? '● Active' : '● Limited' }}
                                </span>
                            </div>
                            <div class="plan-stat-row">
                                <span class="plan-stat-label">Downloads</span>
                                <span class="plan-stat-val">{{ $activeSubscription ? 'Unlimited' : '0 Remaining' }}</span>
                            </div>
                            @if($activeSubscription)
                            <div class="plan-stat-row">
                                <span class="plan-stat-label">Renews</span>
                                <span class="plan-stat-val">{{ \Carbon\Carbon::parse($activeSubscription->expiry_date)->format('M d, Y') }}</span>
                            </div>
                            @endif
                        </div>
                        @if(!$activeSubscription)
                            <a href="{{ route('plans') }}" class="upgrade-btn primary">Upgrade to Emerald ✦</a>
                        @else
                            <a href="{{ route('plans') }}" class="upgrade-btn secondary">Manage Plan →</a>
                        @endif
                    </div>
                </div>

                {{-- AI Insights --}}
                <div class="insights-card">
                    <div class="insights-card-header">
                        <h3>AI Insights</h3>
                        <span class="ai-badge">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Live
                        </span>
                    </div>
                    @forelse($recentBlogs as $blog)
                        <a href="{{ route('blog.show', $blog->slug) }}" class="insight-item" style="text-decoration: none;">
                            <div class="insight-dot-wrap">
                                <svg viewBox="0 0 24 24"><path d="M13.5 4.5L19.5 10.5L10.5 19.5H4.5V13.5L13.5 4.5ZM12 7.5L16.5 12"/></svg>
                            </div>
                            <p class="insight-text">{{ \Illuminate\Support\Str::limit($blog->title, 90) }}</p>
                        </a>
                    @empty
                        <div class="insight-item">
                            <div class="insight-dot-wrap">
                                <svg viewBox="0 0 24 24"><path d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
                            </div>
                            <p class="insight-text">No recent blogs available right now.</p>
                        </div>
                    @endforelse
                    <a href="{{ route('interview') }}" class="run-audit-btn">View All Insights →</a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // Animate score ring
    const fill = document.querySelector('.ring-fill');
    if (fill) {
        const score = 85;
        const circumference = 2 * Math.PI * 26;
        const offset = circumference - (score / 100) * circumference;
        setTimeout(() => { fill.style.strokeDasharray = circumference; fill.style.strokeDashoffset = offset; }, 200);
    }

    // Correctly scale and centre each resume/letter preview
    document.querySelectorAll('.doc-preview-wrap').forEach(wrap => {
        const scaler = wrap.querySelector('.doc-preview-scaler');
        if (!scaler) return;

        const applyScale = () => {
            const cardW = wrap.offsetWidth;
            const a4W   = 794;
            const scale = cardW / a4W;
            const offsetX = 0; // left-aligned after scaling since scaled width = cardW
            scaler.style.transform = `scale(${scale})`;
            scaler.style.left = `${offsetX}px`;
        };

        applyScale();
        // Re-apply on resize
        const ro = new ResizeObserver(applyScale);
        ro.observe(wrap);
    });

    document.querySelectorAll('.js-rename-resume, .js-rename-letter').forEach(btn => {
        btn.addEventListener('click', () => {
            const row = btn.closest('.doc-meta-row');
            const form = row?.querySelector('.js-rename-form');
            const input = form?.querySelector('.doc-rename-input');
            if (!row || !form || !input) return;
            form.classList.add('active');
            row.closest('.doc-card')?.classList.add('is-renaming');
            btn.style.display = 'none';
            input.focus();
            input.select();
        });
    });

    document.querySelectorAll('.js-rename-cancel').forEach(cancelBtn => {
        cancelBtn.addEventListener('click', () => {
            const row = cancelBtn.closest('.doc-meta-row');
            const form = cancelBtn.closest('.js-rename-form');
            const renameBtn = row?.querySelector('.js-rename-resume, .js-rename-letter');
            if (!form || !renameBtn) return;
            form.classList.remove('active');
            row?.closest('.doc-card')?.classList.remove('is-renaming');
            renameBtn.style.display = '';
        });
    });

    document.querySelectorAll('.doc-rename-input').forEach(input => {
        input.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                event.preventDefault();
                input.closest('.js-rename-form')?.querySelector('.js-rename-cancel')?.click();
            }
        });
    });
});
</script>
</x-app-layout>
