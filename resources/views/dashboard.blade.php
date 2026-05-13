<x-app-layout>
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');

    :root {
        --primary: #0f766e;
        --primary-glow: rgba(15, 118, 110, 0.15);
        --primary-soft: rgba(15, 118, 110, 0.1);
        --surface: #0b1326;
        --on-surface: #dae2fd;
        --glass: rgba(255, 255, 255, 0.05);
        --glass-border: rgba(255, 255, 255, 0.12);
        --glass-hover: rgba(255, 255, 255, 0.08);
        --text-muted: #bdc9c6;
        --radius-lg: 24px;
        --radius-md: 16px;
        --radius-sm: 12px;
    }

    .dash-root {
        font-family: 'Plus Jakarta Sans', sans-serif;
        background-color: var(--surface);
        background-image: 
            radial-gradient(circle at 0% 0%, var(--primary-glow) 0%, transparent 40%),
            radial-gradient(circle at 100% 100%, var(--primary-glow) 0%, transparent 40%);
        min-height: 100vh;
        color: var(--on-surface);
        padding: 40px 20px;
        position: relative;
        overflow-x: hidden;
    }

    /* Grid Overlay */
    .dash-root::before {
        content: '';
        position: absolute;
        inset: 0;
        background-image: linear-gradient(var(--glass-border) 1px, transparent 1px),
                          linear-gradient(90deg, var(--glass-border) 1px, transparent 1px);
        background-size: 50px 50px;
        mask-image: radial-gradient(circle at center, black 30%, transparent 80%);
        opacity: 0.2;
        pointer-events: none;
    }

    .container {
        max-width: 1200px;
        margin: 0 auto;
        position: relative;
        z-index: 1;
    }

    /* Glass Card Base */
    .glass-card {
        background: var(--glass);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid var(--glass-border);
        border-radius: var(--radius-md);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .glass-card:hover {
        background: var(--glass-hover);
        border-color: rgba(255, 255, 255, 0.2);
        transform: translateY(-4px);
    }

    /* Welcome Section */
    .welcome-section {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        margin-bottom: 48px;
        gap: 24px;
        flex-wrap: wrap;
    }

    .greeting-group h1 {
        font-size: clamp(32px, 5vw, 48px);
        font-weight: 800;
        letter-spacing: -0.03em;
        line-height: 1.1;
        margin-bottom: 12px;
    }

    .greeting-group .name-highlight {
        color: var(--primary);
        position: relative;
        display: inline-block;
    }

    .greeting-group .sub {
        font-size: 18px;
        color: var(--text-muted);
        font-weight: 400;
    }

    /* Score Gauge */
    .score-gauge-container {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 24px;
        background: var(--glass);
        border-radius: 99px;
        border: 1px solid var(--glass-border);
    }

    .circular-progress {
        position: relative;
        height: 60px;
        width: 60px;
        border-radius: 50%;
        background: conic-gradient(var(--primary) 306deg, var(--glass-border) 0deg);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .circular-progress::before {
        content: "";
        position: absolute;
        height: 48px;
        width: 48px;
        border-radius: 50%;
        background-color: #111a2e;
    }

    .score-value {
        position: relative;
        font-weight: 700;
        font-size: 18px;
        color: var(--primary);
    }

    .score-info .label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--text-muted);
        font-weight: 600;
    }

    .score-info .status {
        font-size: 14px;
        font-weight: 500;
        color: var(--on-surface);
    }

    /* Quick Actions */
    .quick-actions {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 48px;
    }

    .action-card {
        padding: 32px;
        display: flex;
        flex-direction: column;
        gap: 16px;
        text-decoration: none;
        color: inherit;
    }

    .action-icon {
        width: 48px;
        height: 48px;
        background: var(--primary-soft);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--primary);
        margin-bottom: 8px;
    }

    .action-card h3 {
        font-size: 20px;
        font-weight: 700;
        margin: 0;
    }

    .action-card p {
        font-size: 14px;
        color: var(--text-muted);
        line-height: 1.5;
        margin: 0;
    }

    /* Main Grid */
    .main-grid {
        display: grid;
        grid-template-columns: 1fr 340px;
        gap: 32px;
    }

    @media (max-width: 1024px) {
        .main-grid {
            grid-template-columns: 1fr;
        }
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }

    .section-header h2 {
        font-size: 24px;
        font-weight: 700;
        letter-spacing: -0.01em;
    }

    .view-all {
        font-size: 14px;
        color: var(--primary);
        text-decoration: none;
        font-weight: 600;
    }

    /* Document Grid */
    .docs-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
        gap: 20px;
    }

    .doc-card {
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .doc-preview {
        height: 180px;
        background: #111a2e;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        border-bottom: 1px solid var(--glass-border);
    }

    .doc-preview img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0.6;
        transition: 0.3s;
    }

    .doc-card:hover .doc-preview img {
        opacity: 0.8;
        transform: scale(1.05);
    }

    .doc-overlay {
        position: absolute;
        inset: 0;
        background: rgba(11, 19, 38, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        opacity: 0;
        transition: 0.3s;
    }

    .doc-card:hover .doc-overlay {
        opacity: 1;
    }

    .overlay-btn {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--primary);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        transition: 0.2s;
    }

    .overlay-btn:hover {
        transform: scale(1.1);
        box-shadow: 0 0 15px var(--primary);
    }

    .doc-info {
        padding: 16px;
    }

    .doc-title {
        font-weight: 600;
        font-size: 15px;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .doc-meta {
        font-size: 12px;
        color: var(--text-muted);
    }

    /* Sidebar Cards */
    .sidebar {
        display: flex;
        flex-direction: column;
        gap: 24px;
    }

    .plan-card {
        padding: 24px;
        background: linear-gradient(135deg, rgba(15, 118, 110, 0.2), rgba(15, 118, 110, 0.05));
        border: 1px solid var(--primary);
    }

    .plan-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .plan-badge {
        padding: 4px 12px;
        background: var(--primary);
        border-radius: 99px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .plan-stats {
        margin-bottom: 24px;
    }

    .stat-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .stat-label { color: var(--text-muted); }
    .stat-val { font-weight: 600; }

    .upgrade-btn {
        display: block;
        width: 100%;
        padding: 12px;
        background: var(--primary);
        color: white;
        text-align: center;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: 0.2s;
    }

    .upgrade-btn:hover {
        filter: brightness(1.1);
        box-shadow: 0 8px 20px var(--primary-glow);
    }

    .ai-insights {
        padding: 24px;
    }

    .insight-item {
        display: flex;
        gap: 12px;
        margin-bottom: 16px;
        font-size: 13px;
        line-height: 1.4;
    }

    .insight-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: var(--primary);
        margin-top: 6px;
        flex-shrink: 0;
    }

    .empty-state {
        grid-column: 1 / -1;
        padding: 60px;
        text-align: center;
        color: var(--text-muted);
    }

    .empty-state svg {
        margin-bottom: 16px;
        opacity: 0.5;
    }

    /* Mobile adjustments */
    @media (max-width: 640px) {
        .welcome-section {
            flex-direction: column;
            align-items: flex-start;
        }
        .score-gauge-container {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="dash-root">
    <div class="container">
        
        {{-- Welcome Header --}}
        <div class="welcome-section">
            <div class="greeting-group">
                <h1>Welcome back,<br><span class="name-highlight">{{ Auth::user()->name }}</span></h1>
                <p class="sub">Your career tools are ready for action.</p>
            </div>

            <div class="score-gauge-container">
                <div class="circular-progress" id="ats-gauge">
                    <span class="score-value">85</span>
                </div>
                <div class="score-info">
                    <div class="label">Resume Strength</div>
                    <div class="status">Great Performance</div>
                </div>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="quick-actions">
            <a href="{{ route('resume-maker') }}" class="glass-card action-card">
                <div class="action-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M12 4v16m8-8H4"/></svg>
                </div>
                <div>
                    <h3>Create New Resume</h3>
                    <p>Start with a premium template and AI-guided content.</p>
                </div>
            </a>
            
            <a href="{{ route('enhance-cv') }}" class="glass-card action-card">
                <div class="action-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <h3>Enhance Existing</h3>
                    <p>Upload your current CV and let AI optimize it for ATS.</p>
                </div>
            </a>

            <a href="{{ route('cover-letter') }}" class="glass-card action-card">
                <div class="action-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
                <div>
                    <h3>New Cover Letter</h3>
                    <p>Generate a tailored cover letter for any job role.</p>
                </div>
            </a>
        </div>

        <div class="main-grid">
            
            {{-- Left Column: Documents --}}
            <div class="content-left">
                <div class="section-header">
                    <h2>Recent Resumes</h2>
                    <a href="{{ route('resume.index') }}" class="view-all">View all →</a>
                </div>

                <div class="docs-grid">
                    @forelse($recentResumes as $resume)
                        <div class="glass-card doc-card">
                            <div class="doc-preview">
                                @if($resume->template && $resume->template->thumbnail)
                                    <img src="{{ asset('storage/' . $resume->template->thumbnail) }}" alt="{{ $resume->title }}">
                                @else
                                    <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                @endif
                                <div class="doc-overlay">
                                    <a href="{{ route('resume.edit', $resume) }}" class="overlay-btn" title="Edit">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    <a href="{{ route('resume.download', $resume) }}" class="overlay-btn" title="Download">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                </div>
                            </div>
                            <div class="doc-info">
                                <div class="doc-title">{{ $resume->title }}</div>
                                <div class="doc-meta">{{ $resume->created_at->format('M d, Y') }} • {{ $resume->template->name ?? 'Standard' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1"><path d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"/></svg>
                            <h3>No resumes yet</h3>
                            <p>Create your first professional resume in minutes.</p>
                            <a href="{{ route('resume-maker') }}" class="view-all">Get Started →</a>
                        </div>
                    @endforelse
                </div>

                <div class="section-header" style="margin-top: 48px;">
                    <h2>Cover Letters</h2>
                    <a href="{{ route('dashboard.cover-letters') }}" class="view-all">View all →</a>
                </div>

                <div class="docs-grid">
                    @forelse($recentCoverLetters as $letter)
                        <div class="glass-card doc-card">
                            <div class="doc-preview">
                                <svg width="48" height="48" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <div class="doc-overlay">
                                    <a href="{{ route('cover-letter') }}?edit={{ $letter->id }}" class="overlay-btn" title="Edit">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    </a>
                                    <a href="{{ route('cover-letter.download', $letter) }}" class="overlay-btn" title="Download">
                                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                    </a>
                                </div>
                            </div>
                            <div class="doc-info">
                                <div class="doc-title">{{ $letter->data['job_title'] ?? 'Cover Letter' }}</div>
                                <div class="doc-meta">{{ $letter->created_at->format('M d, Y') }} • {{ $letter->data['company'] ?? 'General' }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <h3>No cover letters yet</h3>
                            <p>Generate a winning cover letter with AI.</p>
                            <a href="{{ route('cover-letter') }}" class="view-all">Create New →</a>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Right Column: Sidebar --}}
            <div class="sidebar">
                
                {{-- Subscription Card --}}
                <div class="glass-card plan-card">
                    <div class="plan-header">
                        <span class="plan-badge">{{ $activeSubscription ? ($activeSubscription->plan->name ?? 'Premium') : 'Free Explorer' }}</span>
                        @if($activeSubscription)
                            <svg width="20" height="20" fill="var(--primary)" viewBox="0 0 20 20"><path d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"/></svg>
                        @endif
                    </div>
                    
                    <div class="plan-stats">
                        <div class="stat-item">
                            <span class="stat-label">Status</span>
                            <span class="stat-val" style="color: {{ $activeSubscription ? '#10b981' : '#f59e0b' }}">{{ $activeSubscription ? 'Active' : 'Limited' }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Downloads</span>
                            <span class="stat-val">{{ $activeSubscription ? 'Unlimited' : '0 Remaining' }}</span>
                        </div>
                        @if($activeSubscription)
                        <div class="stat-item">
                            <span class="stat-label">Renews</span>
                            <span class="stat-val">{{ \Carbon\Carbon::parse($activeSubscription->expiry_date)->format('M d, Y') }}</span>
                        </div>
                        @endif
                    </div>

                    @if(!$activeSubscription)
                        <a href="{{ route('plans') }}" class="upgrade-btn">Upgrade to Emerald</a>
                    @else
                        <a href="{{ route('plans') }}" class="upgrade-btn" style="background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border);">Manage Plan</a>
                    @endif
                </div>

                {{-- AI Insights --}}
                <div class="glass-card ai-insights">
                    <div class="section-header" style="margin-bottom: 16px;">
                        <h3 style="font-size: 16px; margin: 0;">AI Career Insights</h3>
                        <svg width="18" height="18" fill="none" stroke="var(--primary)" viewBox="0 0 24 24" stroke-width="2"><path d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    
                    <div class="insight-item">
                        <div class="insight-dot"></div>
                        <p>Your "Executive CV" is missing key action verbs in the Experience section.</p>
                    </div>
                    <div class="insight-item">
                        <div class="insight-dot"></div>
                        <p>Market trends show a 15% increase in demand for your "Full Stack" skills.</p>
                    </div>
                    <div class="insight-item">
                        <div class="insight-dot"></div>
                        <p>Add a dedicated "Projects" section to increase your ATS score by up to 10 points.</p>
                    </div>

                    <a href="{{ route('enhance-cv') }}" class="view-all" style="display: block; text-align: center; margin-top: 8px;">Run Full Audit →</a>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Simple gauge animation if we wanted one
        const gaugeValue = 85;
        const gauge = document.getElementById('ats-gauge');
        if (gauge) {
            gauge.style.background = `conic-gradient(var(--primary) ${gaugeValue * 3.6}deg, var(--glass-border) 0deg)`;
        }
    });
</script>
</x-app-layout>
