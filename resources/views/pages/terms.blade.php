@extends('layouts.app')

@section('title', 'Terms of Use - CVBliss')

@section('content')

<style>
    :root {
        --emerald: #10b981; 
        --emerald-dark: #059669; 
        --emerald-light: #ecfdf5;
        --navy: #0b1221; 
        --ink: #1e293b; 
        --muted: #64748b; 
        --soft: #94a3b8;
        --surface: #f8fafc; 
        --border: rgba(16, 185, 129, 0.1); 
        --white: #ffffff;
        --font-display: 'DM Serif Display', serif;
        --font-body: 'Bricolage Grotesque', sans-serif;
        --r-2xl: 24px;
        --r-3xl: 32px;
    }

    .legal-container {
        background: #fbfdfc;
        min-height: 100vh;
        font-family: var(--font-body);
        color: var(--ink);
        position: relative;
        overflow: hidden;
    }

    /* Background Orbs */
    .orb { position: absolute; border-radius: 50%; pointer-events: none; filter: blur(80px); opacity: 0.4; z-index: 0; }
    .orb-1 { width: 500px; height: 500px; background: rgba(16, 185, 129, 0.1); top: -100px; right: -100px; }
    .orb-2 { width: 400px; height: 400px; background: rgba(37, 99, 235, 0.08); bottom: 100px; left: -100px; }

    .noise {
        position: fixed; inset: 0; pointer-events: none; z-index: 1; opacity: 0.015;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.65' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
    }

    /* Hero Section */
    .legal-hero {
        padding: 100px 5% 60px;
        max-width: 1300px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 60px;
        align-items: center;
        position: relative;
        z-index: 2;
    }

    .hero-text h1 {
        font-family: var(--font-display);
        font-size: clamp(2.5rem, 6vw, 4rem);
        color: var(--navy);
        line-height: 1.1;
        margin-bottom: 24px;
    }

    .hero-text h1 span {
        color: var(--emerald);
        position: relative;
    }

    .hero-text h1 span::after {
        content: '';
        position: absolute;
        bottom: 10px;
        left: 0;
        width: 100%;
        height: 8px;
        background: rgba(16, 185, 129, 0.1);
        z-index: -1;
    }

    .hero-text p {
        font-size: 1.2rem;
        color: var(--muted);
        line-height: 1.6;
        max-width: 500px;
    }

    .hero-image {
        position: relative;
        animation: float 6s ease-in-out infinite;
    }

    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }

    .hero-image img {
        width: 100%;
        height: auto;
        border-radius: var(--r-3xl);
        filter: drop-shadow(0 20px 50px rgba(16, 185, 129, 0.15));
    }

    /* Main Content Layout */
    .legal-main {
        max-width: 1300px;
        margin: 0 auto;
        padding: 0 5% 120px;
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 60px;
        position: relative;
        z-index: 2;
    }

    /* Sticky Sidebar Navigation */
    .legal-sidebar {
        position: sticky;
        top: 100px;
        height: fit-content;
    }

    .sidebar-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid var(--border);
        border-radius: var(--r-2xl);
        padding: 24px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.03);
    }

    .sidebar-card h3 {
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: var(--soft);
        margin-bottom: 20px;
        font-weight: 700;
    }

    .toc-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .toc-item a {
        display: block;
        padding: 10px 0;
        font-size: 0.95rem;
        color: var(--muted);
        text-decoration: none;
        transition: all 0.2s;
        border-left: 2px solid transparent;
        padding-left: 15px;
    }

    .toc-item a:hover {
        color: var(--emerald);
        padding-left: 20px;
    }

    .toc-item a.active {
        color: var(--emerald);
        font-weight: 600;
        border-left-color: var(--emerald);
    }

    /* Content Styling */
    .legal-content-wrap {
        display: flex;
        flex-direction: column;
        gap: 40px;
    }

    .content-section {
        background: var(--white);
        border-radius: var(--r-3xl);
        padding: 50px;
        border: 1px solid var(--border);
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1), 
                    box-shadow 0.3s ease, 
                    opacity 0.6s ease,
                    filter 0.6s ease;
        opacity: 0;
        transform: translateY(30px);
        filter: blur(10px);
    }

    .content-section.is-visible {
        opacity: 1;
        transform: translateY(0);
        filter: blur(0);
    }

    .content-section:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(16, 185, 129, 0.06);
    }

    .section-icon {
        width: 48px;
        height: 48px;
        background: var(--emerald-light);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
        color: var(--emerald);
    }

    .content-section h2 {
        font-family: var(--font-display);
        font-size: 2rem;
        color: var(--navy);
        margin-bottom: 20px;
        font-weight: 500;
    }

    .content-section p {
        font-size: 1.1rem;
        line-height: 1.8;
        color: var(--ink);
        margin-bottom: 20px;
        opacity: 0.9;
    }

    .content-section ul {
        list-style: none;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .content-section li {
        padding-left: 28px;
        position: relative;
        line-height: 1.6;
    }

    .content-section li::before {
        content: '✓';
        position: absolute;
        left: 0;
        color: var(--emerald);
        font-weight: bold;
    }

    .last-updated-badge {
        display: inline-block;
        background: #f1f5f9;
        color: #475569;
        padding: 6px 14px;
        border-radius: 99px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-bottom: 40px;
    }

    @media (max-width: 1024px) {
        .legal-main { grid-template-columns: 1fr; }
        .legal-sidebar { display: none; }
        .legal-hero { grid-template-columns: 1fr; text-align: center; padding-top: 60px; }
        .hero-text p { margin: 0 auto; }
        .content-section { padding: 30px; }
    }
</style>

<div class="legal-container">
    <div class="noise"></div>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>

    {{-- HERO --}}
    <header class="legal-hero">
        <div class="hero-text">
            <div class="last-updated-badge">Last Updated: May 7, 2024</div>
            <h1>Clear <span>Terms</span> for a better experience.</h1>
            <p>Please read these terms carefully before using CVBliss. They explain your rights and responsibilities when using our platform.</p>
        </div>
        <div class="hero-image">
            <img src="{{ asset('images/terms-legal.png') }}" alt="Terms Illustration">
        </div>
    </header>

    <main class="legal-main">
        {{-- SIDEBAR TOC --}}
        <aside class="legal-sidebar">
            <div class="sidebar-card">
                <h3>Terms Navigation</h3>
                <ul class="toc-list">
                    <li class="toc-item"><a href="#agreement" class="active">1. Agreement</a></li>
                    <li class="toc-item"><a href="#license">2. Use License</a></li>
                    <li class="toc-item"><a href="#disclaimer">3. Disclaimer</a></li>
                    <li class="toc-item"><a href="#limitations">4. Limitations</a></li>
                    <li class="toc-item"><a href="#revisions">5. Revisions</a></li>
                    <li class="toc-item"><a href="#governing-law">6. Governing Law</a></li>
                </ul>
            </div>
        </aside>

        {{-- CONTENT --}}
        <div class="legal-content-wrap">
            <section id="agreement" class="content-section">
                <div class="section-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h2>1. Agreement to Terms</h2>
                <p>By accessing or using CVBliss, you agree to be bound by these Terms of Use and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using or accessing this site.</p>
                <p>These terms constitute a legally binding agreement between you and CVBliss regarding your use of our resume building and career tools.</p>
            </section>

            <section id="license" class="content-section">
                <div class="section-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </div>
                <h2>2. Use License</h2>
                <p>Permission is granted to temporarily download one copy of the materials (information or software) on CVBliss's website for personal, non-commercial transitory viewing only.</p>
                <p>This is the grant of a license, not a transfer of title, and under this license you may not:</p>
                <ul>
                    <li>Modify or copy the materials;</li>
                    <li>Use the materials for any commercial purpose;</li>
                    <li>Attempt to decompile or reverse engineer any software;</li>
                    <li>Remove any copyright or other proprietary notations;</li>
                    <li>Transfer the materials to another person or "mirror" the materials.</li>
                </ul>
            </section>

            <section id="disclaimer" class="content-section">
                <div class="section-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <h2>3. Disclaimer</h2>
                <p>The materials on CVBliss's website are provided on an 'as is' basis. CVBliss makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties including, without limitation, implied warranties or conditions of merchantability, fitness for a particular purpose, or non-infringement of intellectual property.</p>
                <p>Further, CVBliss does not warrant or make any representations concerning the accuracy, likely results, or reliability of the use of the materials on its website.</p>
            </section>

            <section id="limitations" class="content-section">
                <div class="section-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                </div>
                <h2>4. Limitations</h2>
                <p>In no event shall CVBliss or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on CVBliss's website.</p>
                <p>Because some jurisdictions do not allow limitations on implied warranties, or limitations of liability for consequential or incidental damages, these limitations may not apply to you.</p>
            </section>

            <section id="revisions" class="content-section">
                <div class="section-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                </div>
                <h2>5. Revisions and Errata</h2>
                <p>The materials appearing on CVBliss's website could include technical, typographical, or photographic errors. CVBliss does not warrant that any of the materials on its website are accurate, complete or current.</p>
                <p>CVBliss may make changes to the materials contained on its website at any time without notice. However CVBliss does not make any commitment to update the materials.</p>
            </section>

            <section id="governing-law" class="content-section">
                <div class="section-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                </div>
                <h2>6. Governing Law</h2>
                <p>Any claim relating to CVBliss's website shall be governed by the laws of the State of Haryana, India without regard to its conflict of law provisions.</p>
                <div style="background: var(--emerald-light); padding: 20px; border-radius: 16px; border: 1px dashed var(--emerald);">
                    <p style="margin: 0; font-weight: 600; color: var(--emerald-dark);">Legal Department</p>
                    <p style="margin: 5px 0 0; color: var(--muted);">Email: terms@cvbliss.in</p>
                </div>
            </section>
        </div>
    </main>
</div>

<script>
    // Intersection Observer for reveal animations
    const observerOptions = {
        threshold: 0.15,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('is-visible');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.content-section').forEach(section => {
        observer.observe(section);
    });

    // Smooth scroll for TOC
    document.querySelectorAll('.toc-item a').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            window.scrollTo({
                top: target.offsetTop - 120,
                behavior: 'smooth'
            });
        });
    });

    // Active TOC link on scroll
    window.addEventListener('scroll', () => {
        let current = '';
        const sections = document.querySelectorAll('.content-section');
        const navLinks = document.querySelectorAll('.toc-item a');

        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (pageYOffset >= sectionTop - 150) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href').includes(current)) {
                link.classList.add('active');
            }
        });
    });
</script>

@endsection
