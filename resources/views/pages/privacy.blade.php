@extends('layouts.app')

@section('title', 'Privacy Policy - CVBliss')

@section('content')

<style>
    :root {
        --blue: #2563eb; 
        --blue-dark: #1d4ed8; 
        --blue-light: #eff6ff;
        --navy: #0b1221; 
        --ink: #1e293b; 
        --muted: #64748b; 
        --soft: #94a3b8;
        --surface: #f8fafc; 
        --border: rgba(37, 99, 235, 0.1); 
        --white: #ffffff;
        --font-display: 'DM Serif Display', serif;
        --font-body: 'Bricolage Grotesque', sans-serif;
        --r-2xl: 24px;
        --r-3xl: 32px;
    }

    .legal-container {
        background: #fafcff;
        min-height: 100vh;
        font-family: var(--font-body);
        color: var(--ink);
        position: relative;
        overflow: hidden;
    }

    /* Background Orbs */
    .orb { position: absolute; border-radius: 50%; pointer-events: none; filter: blur(80px); opacity: 0.4; z-index: 0; }
    .orb-1 { width: 500px; height: 500px; background: rgba(37, 99, 235, 0.15); top: -100px; right: -100px; }
    .orb-2 { width: 400px; height: 400px; background: rgba(20, 184, 166, 0.1); bottom: 100px; left: -100px; }

    .noise {
        position: fixed; inset: 0; pointer-events: none; z-index: 1; opacity: 0; /* Disabled for performance */
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
        color: var(--blue);
        position: relative;
    }

    .hero-text h1 span::after {
        content: '';
        position: absolute;
        bottom: 10px;
        left: 0;
        width: 100%;
        height: 8px;
        background: rgba(37, 99, 235, 0.1);
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
        filter: drop-shadow(0 20px 50px rgba(37, 99, 235, 0.15));
    }

    /* Main Content Layout */
    .legal-main {
        max-width: 1300px;
        margin: 0 auto;
        padding: 0 5% 60px;
        display: grid;
        grid-template-columns: 280px 1fr;
        gap: 60px;
        position: relative;
        z-index: 2;
        height: calc(100vh - 130px);
        min-height: 620px;
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
        color: var(--blue);
        padding-left: 20px;
    }

    .toc-item a.active {
        color: var(--blue);
        font-weight: 600;
        border-left-color: var(--blue);
    }

    /* Content Styling */
    .legal-content-wrap {
        display: flex;
        flex-direction: column;
        gap: 40px;
        overflow-y: auto;
        overscroll-behavior: contain;
        scroll-behavior: smooth;
        padding-right: 10px;
        scroll-padding-top: 8px;
    }
    .legal-content-wrap::-webkit-scrollbar { width: 8px; }
    .legal-content-wrap::-webkit-scrollbar-track { background: #eef2ff; border-radius: 999px; }
    .legal-content-wrap::-webkit-scrollbar-thumb { background: #93c5fd; border-radius: 999px; }

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
        box-shadow: 0 20px 40px rgba(37, 99, 235, 0.06);
    }

    .section-icon {
        width: 48px;
        height: 48px;
        background: var(--blue-light);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
        color: var(--blue);
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
        content: '→';
        position: absolute;
        left: 0;
        color: var(--blue);
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
        .legal-main { grid-template-columns: 1fr; height: auto; min-height: 0; }
        .legal-sidebar { display: none; }
        .legal-content-wrap { overflow: visible; padding-right: 0; }
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
            <h1>Your <span>Privacy</span> is our top priority.</h1>
            <p>At CVBliss, we believe your data belongs to you. Here's how we protect it and keep your information secure.</p>
        </div>
        <div class="hero-image">
            <img src="{{ asset('images/privacy-legal.png') }}" alt="Privacy Illustration">
        </div>
    </header>

    <main class="legal-main">
        {{-- SIDEBAR TOC --}}
        <aside class="legal-sidebar">
            <div class="sidebar-card">
                <h3>On this page</h3>
                <ul class="toc-list">
                    <li class="toc-item"><a href="#introduction" class="active">1. Introduction</a></li>
                    <li class="toc-item"><a href="#data-collection">2. Data Collection</a></li>
                    <li class="toc-item"><a href="#usage">3. How We Use Data</a></li>
                    <li class="toc-item"><a href="#security">4. Data Security</a></li>
                    <li class="toc-item"><a href="#rights">5. Your Legal Rights</a></li>
                    <li class="toc-item"><a href="#contact">6. Contact Us</a></li>
                </ul>
            </div>
        </aside>

        {{-- CONTENT --}}
        <div class="legal-content-wrap">
            <section id="introduction" class="content-section">
                <div class="section-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <h2>1. Introduction</h2>
                <p>Welcome to CVBliss. We respect your privacy and are committed to protecting your personal data. This privacy policy will inform you as to how we look after your personal data when you visit our website and tell you about your privacy rights and how the law protects you.</p>
                <p>By using our services, you agree to the collection and use of information in accordance with this policy. We ensure that our practices are transparent and that your trust in us is well-placed.</p>
            </section>

            <section id="data-collection" class="content-section">
                <div class="section-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                </div>
                <h2>2. The Data We Collect</h2>
                <p>We may collect, use, store and transfer different kinds of personal data about you which we have grouped together as follows:</p>
                <ul>
                    <li><strong>Identity Data:</strong> includes first name, last name, and professional titles.</li>
                    <li><strong>Contact Data:</strong> includes email address and telephone numbers used for communication.</li>
                    <li><strong>Technical Data:</strong> includes IP address, login data, and browser specifications.</li>
                    <li><strong>Profile Data:</strong> includes your username, purchases, and resume preferences.</li>
                    <li><strong>Usage Data:</strong> includes information about how you interact with our resume tools.</li>
                </ul>
            </section>

            <section id="usage" class="content-section">
                <div class="section-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
                <h2>3. How We Use Your Data</h2>
                <p>We will only use your personal data when the law allows us to. Most commonly, we will use your personal data in the following circumstances:</p>
                <ul>
                    <li>To provide and maintain our Service, including to monitor the usage of our Service.</li>
                    <li>To manage Your Account: to manage Your registration as a user of the Service.</li>
                    <li>For the performance of a contract: the development, compliance and undertaking of the purchase contract.</li>
                    <li>To contact You: To contact You by email, telephone calls, SMS, or other equivalent forms of electronic communication.</li>
                </ul>
            </section>

            <section id="security" class="content-section">
                <div class="section-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h2>4. Data Security</h2>
                <p>We have put in place appropriate security measures to prevent your personal data from being accidentally lost, used or accessed in an unauthorized way, altered or disclosed. In addition, we limit access to your personal data to those employees, agents, contractors and other third parties who have a business need to know.</p>
                <p>All data is encrypted using industry-standard SSL technology while in transit and stored in secure servers protected by advanced firewalls.</p>
            </section>

            <section id="rights" class="content-section">
                <div class="section-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <h2>5. Your Legal Rights</h2>
                <p>Under certain circumstances, you have rights under data protection laws in relation to your personal data, including the right to request access, correction, erasure, restriction, transfer, to object to processing, and to withdraw consent.</p>
                <p>If you wish to exercise any of the rights set out above, please contact our data protection team.</p>
            </section>

            <section id="contact" class="content-section">
                <div class="section-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                </div>
                <h2>6. Contact Us</h2>
                <p>If you have any questions about this privacy policy or our privacy practices, please contact us at:</p>
                <div style="background: var(--blue-light); padding: 20px; border-radius: 16px; border: 1px dashed var(--blue);">
                    <p style="margin: 0; font-weight: 600; color: var(--blue);">Email: support@cvbliss.in</p>
                    <p style="margin: 5px 0 0; color: var(--muted);">Available: Mon-Sat, 10 AM - 7 PM IST</p>
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
            const content = document.querySelector('.legal-content-wrap');
            if (target && content && window.matchMedia('(min-width: 1025px)').matches) {
                content.scrollTo({ top: target.offsetTop - content.offsetTop, behavior: 'smooth' });
            } else if (target) {
                window.scrollTo({ top: target.offsetTop - 120, behavior: 'smooth' });
            }
        });
    });

    // Active TOC link on scroll
    const legalScrollRoot = document.querySelector('.legal-content-wrap');
    const updatePrivacyToc = () => {
        let current = '';
        const sections = document.querySelectorAll('.content-section');
        const navLinks = document.querySelectorAll('.toc-item a');

        sections.forEach(section => {
            const sectionTop = window.matchMedia('(min-width: 1025px)').matches
                ? section.offsetTop - legalScrollRoot.offsetTop
                : section.offsetTop;
            const scrollTop = window.matchMedia('(min-width: 1025px)').matches
                ? legalScrollRoot.scrollTop
                : pageYOffset;
            if (scrollTop >= sectionTop - 120) {
                current = section.getAttribute('id');
            }
        });

        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href').includes(current)) {
                link.classList.add('active');
            }
        });
    };
    window.addEventListener('scroll', updatePrivacyToc);
    legalScrollRoot?.addEventListener('scroll', updatePrivacyToc);
</script>

@endsection
