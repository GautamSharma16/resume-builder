@extends('layouts.app')

@section('title', 'Contact Us - CVBliss')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,500;12..96,600;12..96,700;12..96,800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">

<style>
    :root {
        --blue: #2563eb; --blue-dark: #1d4ed8; --blue-light: #eff6ff;
        --navy: #0b1221; --ink: #1e293b; --muted: #64748b; --soft: #94a3b8;
        --surface: #f8fafc; --border: rgba(0,0,0,0.07); --white: #ffffff;
        --green: #10b981; --green-light: #d1fae5; --purple: #8b5cf6;
        --font-display: 'DM Serif Display', serif;
        --font-body: 'Bricolage Grotesque', sans-serif;
        --r-full: 999px;
        --ease-spring: cubic-bezier(0.175, 0.885, 0.32, 1.275);
        --ease-out: cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
        font-family: var(--font-body);
        color: var(--ink);
        background: var(--white);
        -webkit-font-smoothing: antialiased;
        overflow-x: hidden;
    }

    @keyframes fadeUp { from { opacity:0; transform:translateY(32px) } to { opacity:1; transform:translateY(0) } }
    @keyframes floatY { 0%,100% { transform:translateY(0) } 50% { transform:translateY(-6px) } }
    @keyframes pulse-ring { 0% { box-shadow:0 0 0 0 rgba(37,99,235,0.4) } 70% { box-shadow:0 0 0 12px rgba(37,99,235,0) } 100% { box-shadow:0 0 0 0 rgba(37,99,235,0) } }
    @keyframes gradient-shift { 0%,100% { background-position:0% 50% } 50% { background-position:100% 50% } }
    @keyframes spin { from { transform:rotate(0deg) } to { transform:rotate(360deg) } }
    @keyframes slideDown { from { opacity:0; transform:translateY(-8px) } to { opacity:1; transform:translateY(0) } }

    /* ── NOISE OVERLAY ── */
    .noise-overlay {
        position: fixed; inset: 0; pointer-events: none; z-index: 9999; opacity: 0.02;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        background-size: 256px;
    }

    /* ── PAGE ── */
    .contact-page {
        min-height: 100vh;
        padding: 40px 7% 70px;
        position: relative;
        overflow: hidden;
        background: linear-gradient(150deg, #ffffff 0%, #fafcff 50%, #f5f7ff 100%);
    }

    .orb { position: absolute; border-radius: 50%; pointer-events: none; }
    .orb-1 { width: 640px; height: 640px; background: radial-gradient(circle, rgba(37,99,235,0.06) 0%, transparent 65%); top: -280px; left: -200px; }
    .orb-2 { width: 500px; height: 500px; background: radial-gradient(circle, rgba(139,92,246,0.05) 0%, transparent 65%); bottom: -200px; right: -150px; }

    .contact-grid-bg {
        position: absolute; inset: 0; pointer-events: none;
        background-image: linear-gradient(rgba(37,99,235,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(37,99,235,0.03) 1px, transparent 1px);
        background-size: 48px 48px;
        mask-image: radial-gradient(ellipse 75% 80% at 65% 45%, black 20%, transparent 85%);
    }

    .contact-wrap { max-width: 1260px; margin: 0 auto; position: relative; z-index: 2; }

    /* ── HEADER ── */
    .contact-header { text-align: center; margin-bottom: 56px; }

    .contact-badge {
        display: inline-flex; align-items: center; gap: 8px;
        background: rgba(37,99,235,0.08); border: 1px solid rgba(37,99,235,0.2);
        border-radius: var(--r-full); padding: 6px 16px 6px 10px;
        font-size: 0.7rem; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase;
        color: var(--blue); margin-bottom: 24px;
        animation: fadeUp 0.5s var(--ease-out) 0.05s both;
    }
    .badge-dot { width: 8px; height: 8px; background: var(--blue); border-radius: 50%; animation: pulse-ring 2s infinite; }

    .contact-header h1 {
        font-family: var(--font-display); font-size: clamp(2.4rem, 4.5vw, 3.8rem);
        font-weight: 400; color: var(--navy); line-height: 1.1; margin-bottom: 14px;
        animation: fadeUp 0.6s var(--ease-out) 0.15s both;
    }
    .contact-header h1 em {
        font-style: italic;
        background: linear-gradient(135deg, var(--blue), var(--purple), #ec4899);
        -webkit-background-clip: text; background-clip: text; color: transparent;
        background-size: 200% 200%; animation: gradient-shift 5s ease infinite;
    }
    .contact-header > p {
        font-size: 1.05rem; color: var(--muted); max-width: 520px; margin: 0 auto;
        line-height: 1.65; animation: fadeUp 0.6s var(--ease-out) 0.25s both;
    }

    /* ── MAIN CARD (form overlaid on image) ── */
    .contact-card {
        border-radius: 40px;
        overflow: hidden;
        margin-bottom: 80px;
        animation: fadeUp 0.7s var(--ease-out) 0.3s both;
        position: relative;
    }

    .contact-card-inner {
        display: grid;
        grid-template-columns: 1fr 1fr;
        min-height: 580px;
        position: relative;
    }

    /* ── LEFT: FORM (on transparent background) ── */
    .form-side {
        padding: 48px 44px;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(2px);
        border-radius: 0;
        position: relative;
        z-index: 2;
    }

    /* ── RIGHT: IMAGE (no background, just the image) ── */
    .image-side {
        position: relative;
        overflow: hidden;
        padding: 0;
        margin: 0;
        background: transparent;
    }
    .image-side img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
    }

    /* Fallback if image doesn't load */
    .image-side::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(135deg, #eef4ff, #e0e7ff);
        z-index: 1;
    }
    .image-side img {
        position: relative;
        z-index: 2;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Remove any overlay that adds background color */
    .image-side::after {
        display: none;
    }

    .form-header {
        margin-bottom: 32px;
    }
    .form-header h2 {
        font-family: var(--font-display);
        font-size: 1.8rem;
        font-weight: 500;
        color: var(--navy);
        margin-bottom: 8px;
    }
    .form-header p {
        font-size: 0.85rem;
        color: var(--muted);
    }

    .success-msg {
        display: none;
        background: var(--green-light);
        border: 1.5px solid var(--green);
        border-radius: 14px;
        padding: 14px 18px;
        margin-bottom: 24px;
        color: #065f46;
        font-size: 0.84rem;
        font-weight: 600;
        text-align: center;
    }
    .success-msg.show { display: block; animation: slideDown 0.4s ease; }

    .form-field { margin-bottom: 22px; }
    .form-field label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: var(--muted);
        margin-bottom: 8px;
        letter-spacing: 0.04em;
    }
    .req { color: var(--blue); margin-left: 3px; }

    .form-field input,
    .form-field textarea,
    .topic-select {
        width: 100%;
        border: 1.5px solid rgba(0,0,0,0.08);
        border-radius: 14px;
        padding: 14px 18px;
        font-family: var(--font-body);
        font-size: 0.9rem;
        color: var(--ink);
        background: rgba(248,250,252,0.9);
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
    }
    .form-field input:focus,
    .form-field textarea:focus,
    .topic-select:focus {
        border-color: var(--blue);
        box-shadow: 0 0 0 3px rgba(37,99,235,0.08);
        background: #fff;
    }
    .form-field input::placeholder,
    .form-field textarea::placeholder { color: var(--soft); }
    .form-field textarea { min-height: 120px; resize: vertical; }
    .topic-select { cursor: pointer; }

    .submit-btn {
        width: 100%;
        border: none;
        border-radius: var(--r-full);
        padding: 16px 24px;
        font-family: var(--font-body);
        font-size: 0.95rem;
        font-weight: 700;
        color: #fff;
        cursor: pointer;
        transition: all 0.3s var(--ease-spring);
        background: linear-gradient(135deg, var(--blue), var(--blue-dark));
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 8px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(37,99,235,0.35);
    }
    .submit-btn::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.55s ease;
    }
    .submit-btn:hover::after { left: 100%; }
    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(37,99,235,0.45);
    }
    .submit-btn svg {
        width: 18px;
        height: 18px;
        stroke: #fff;
        fill: none;
        stroke-width: 2.2;
    }

    /* ── FAQ SECTION ── */
    .faq-section {
        margin-top: 20px;
    }
    .faq-header { text-align: center; margin-bottom: 48px; }
    .faq-header h2 {
        font-family: var(--font-display);
        font-size: 2.2rem;
        font-weight: 400;
        color: var(--navy);
        margin-bottom: 10px;
    }
    .faq-header h2 em {
        font-style: italic;
        background: linear-gradient(135deg, var(--blue), var(--purple));
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
    }
    .faq-header p { color: var(--muted); font-size: 0.95rem; }

    .faq-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
        max-width: 1000px;
        margin: 0 auto;
    }
    .faq-item {
        background: rgba(255,255,255,0.9);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        padding: 24px;
        border: 1px solid rgba(37,99,235,0.08);
        transition: all 0.3s var(--ease-spring);
        cursor: pointer;
    }
    .faq-item:hover {
        transform: translateY(-4px);
        border-color: rgba(37,99,235,0.2);
        box-shadow: 0 12px 28px rgba(0,0,0,0.06);
    }
    .faq-question {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-weight: 700;
        font-size: 1rem;
        color: var(--navy);
        gap: 16px;
    }
    .faq-question svg {
        width: 20px;
        height: 20px;
        stroke: var(--blue);
        transition: transform 0.3s ease;
        flex-shrink: 0;
    }
    .faq-item.open .faq-question svg {
        transform: rotate(180deg);
    }
    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.85rem;
        color: var(--muted);
        line-height: 1.6;
    }
    .faq-item.open .faq-answer {
        max-height: 200px;
        margin-top: 16px;
    }

    /* Contact info cards (below main card) */
    .contact-info-row {
        display: flex;
        gap: 20px;
        justify-content: center;
        margin-bottom: 60px;
        flex-wrap: wrap;
    }
    .contact-info-card {
        background: rgba(255,255,255,0.88);
        backdrop-filter: blur(12px);
        border-radius: 20px;
        padding: 18px 28px;
        border: 1px solid rgba(37,99,235,0.08);
        display: flex;
        align-items: center;
        gap: 14px;
        transition: all 0.3s var(--ease-spring);
        flex: 1;
        min-width: 240px;
        justify-content: center;
    }
    .contact-info-card:hover {
        transform: translateY(-3px);
        border-color: rgba(37,99,235,0.2);
        background: rgba(255,255,255,0.96);
    }
    .contact-info-icon {
        width: 44px;
        height: 44px;
        border-radius: 14px;
        background: rgba(37,99,235,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .contact-info-icon svg {
        width: 20px;
        height: 20px;
        stroke: var(--blue);
    }
    .contact-info-text h4 {
        font-size: 0.85rem;
        font-weight: 700;
        color: var(--navy);
        margin-bottom: 2px;
    }
    .contact-info-text p {
        font-size: 0.75rem;
        color: var(--muted);
    }
    .contact-info-text a,
    .contact-info-text strong {
        font-size: 0.8rem;
        font-weight: 600;
        color: var(--blue);
        text-decoration: none;
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .contact-page { padding: 40px 5% 60px; }
        .contact-card-inner { grid-template-columns: 1fr; }
        .image-side { min-height: 300px; position: relative; }
        .image-side img { position: relative; height: auto; }
        .faq-grid { grid-template-columns: 1fr; }
        .contact-info-row { flex-direction: column; align-items: stretch; }
    }
    @media (max-width: 768px) {
        .form-side { padding: 32px 24px; }
        .form-header h2 { font-size: 1.5rem; }
        .contact-info-card { padding: 14px 20px; }
        .faq-header h2 { font-size: 1.8rem; }
    }
    @media (max-width: 480px) {
        .contact-page { padding: 32px 20px 56px; }
        .form-side { padding: 24px 20px; }
    }
</style>

<div class="noise-overlay"></div>

<div class="contact-page">
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="contact-grid-bg"></div>

    <div class="contact-wrap">

        {{-- HEADER --}}
        <div class="contact-header">
            <div class="contact-badge">
                <span class="badge-dot"></span>
                Contact us
            </div>
            <h1>Let's <em>connect</em> —<br>we're here to help</h1>
            <p>If you need assistance with our service or have any questions, don't hesitate to get in touch with us.</p>
        </div>

        {{-- MERGED FORM + IMAGE CARD --}}
        <div class="contact-card">
            <div class="contact-card-inner">
                {{-- LEFT: FORM --}}
                <div class="form-side">
                    <div class="form-header">
                        <h2>Send us a message</h2>
                        <p>We'll get back to you within 24 hours</p>
                    </div>

                    <div id="successMsg" class="success-msg">
                        ✓ Message sent! We'll respond shortly.
                    </div>

                  

                        <div class="form-field">
                            <label>Name <span class="req">*</span></label>
                            <input type="text" name="name" placeholder="Your name" required>
                        </div>

                        <div class="form-field">
                            <label>Email address <span class="req">*</span></label>
                            <input type="email" name="email" placeholder="gautamrajaneexpert@gmail.com" required>
                        </div>

                        <div class="form-field">
                            <label>Topic</label>
                            <select name="topic" class="topic-select">
                                <option value="General inquiry">General inquiry</option>
                                <option value="Resume help">Resume help</option>
                                <option value="Bug report">Bug report</option>
                                <option value="Billing">Billing</option>
                                <option value="Partnership">Partnership</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="form-field">
                            <label>Message <span class="req">*</span></label>
                            <textarea name="message" placeholder="Type your message here..." required></textarea>
                        </div>

                        <button type="submit" class="submit-btn" id="submitBtn">
                            <svg viewBox="0 0 24 24" fill="none">
                                <line x1="22" y1="2" x2="11" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                <polygon points="22 2 15 22 11 13 2 9 22 2" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                            </svg>
                            Submit
                        </button>
                    </form>
                </div>

                {{-- RIGHT: IMAGE (no extra background) --}}
                <div class="image-side">
                    <img src="{{ asset('contact-support.jpg') }}" alt="Customer support" onerror="this.src='https://placehold.co/600x800/eef4ff/2563eb?text=Support+Team'">
                </div>
            </div>
        </div>

        {{-- CONTACT INFO CARDS --}}
        <div class="contact-info-row">
            <div class="contact-info-card">
                <div class="contact-info-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-linecap="round"/>
                        <polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="contact-info-text">
                    <h4>Email us</h4>
                    <p>For resume help & support</p>
                    <a href="mailto:support@cvbliss.in">support@cvbliss.in</a>
                </div>
            </div>

            <div class="contact-info-card">
                <div class="contact-info-icon">
                    <svg viewBox="0 0 24 24" fill="none">
                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 12a19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 3.58 1h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L7.91 8.5a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z" stroke="currentColor" stroke-linecap="round"/>
                    </svg>
                </div>
                <div class="contact-info-text">
                    <h4>Call us</h4>
                    <p>Mon–Sat, 10 AM – 7 PM</p>
                    <strong>+91 98765 43210</strong>
                </div>
            </div>
        </div>

        {{-- FAQ SECTION --}}
        <div class="faq-section">
            <div class="faq-header">
                <h2>Frequently asked <em>questions</em></h2>
                <p>Everything you need to know about our service</p>
            </div>

            <div class="faq-grid">
                <div class="faq-item">
                    <div class="faq-question">
                        <span>How do I create a resume?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                    <div class="faq-answer">
                        Simply choose a template, fill in your details, and our AI will help optimize your content. You can download your resume as PDF or DOCX instantly.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Is my data secure?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                    <div class="faq-answer">
                        Absolutely. We use bank-level encryption to protect your personal information. Your data is never shared with third parties.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>Can I edit my resume after downloading?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                    <div class="faq-answer">
                        Yes! Your resumes are saved in your account. You can access, edit, and download them anytime.
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question">
                        <span>What payment methods do you accept?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </div>
                    <div class="faq-answer">
                        We accept all major credit cards, UPI, and net banking. All payments are processed securely.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Independent FAQ toggle - each FAQ works completely independently
    document.querySelectorAll('.faq-item').forEach(item => {
        item.addEventListener('click', function(e) {
            // Stop event from bubbling up
            e.stopPropagation();
            // Toggle ONLY this FAQ item
            this.classList.toggle('open');
        });
    });

    // Form submission
    const contactForm = document.getElementById('contactForm');
    const successMsg = document.getElementById('successMsg');
    const submitBtn = document.getElementById('submitBtn');

    if (contactForm) {
        contactForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const originalHTML = submitBtn.innerHTML;
            submitBtn.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" style="animation:spin 0.8s linear infinite">
                    <circle cx="12" cy="12" r="10" stroke="white" stroke-width="2" stroke-dasharray="30 10"/>
                </svg>
                Sending...
            `;
            submitBtn.disabled = true;

            try {
                const response = await fetch(contactForm.action, {
                    method: 'POST',
                    body: new FormData(contactForm),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    successMsg.classList.add('show');
                    contactForm.reset();
                    setTimeout(() => successMsg.classList.remove('show'), 5000);
                } else {
                    alert('Something went wrong. Please try again.');
                }
            } catch (err) {
                console.error(err);
                alert('Network error. Please try again.');
            } finally {
                submitBtn.innerHTML = originalHTML;
                submitBtn.disabled = false;
            }
        });
    }
</script>

@endsection