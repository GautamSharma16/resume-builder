@extends('layouts.app')

@section('title', 'Contact Us - CVBliss')

@section('content')
<style>
    .contact-shell { background:#f8fafc; color:#0f172a; }
    .contact-wrap { max-width:1180px; margin:0 auto; padding:56px 20px 72px; }
    .contact-head { display:grid; grid-template-columns:minmax(0, 0.9fr) minmax(320px, 1.1fr); gap:36px; align-items:start; }
    .contact-kicker { display:inline-flex; align-items:center; gap:8px; border:1px solid #bfdbfe; background:#eff6ff; color:#1d4ed8; border-radius:999px; padding:7px 12px; font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.08em; }
    .contact-title { margin:18px 0 14px; font-size:clamp(36px, 5vw, 64px); line-height:1; font-weight:800; letter-spacing:0; }
    .contact-copy { color:#475569; font-size:17px; line-height:1.7; max-width:560px; }
    .contact-points { margin-top:28px; display:grid; gap:14px; }
    .contact-point { display:flex; gap:12px; align-items:flex-start; color:#334155; }
    .contact-icon { width:38px; height:38px; border-radius:8px; display:grid; place-items:center; background:#e0f2fe; color:#0369a1; flex:0 0 auto; }
    .contact-card { background:white; border:1px solid #e2e8f0; border-radius:8px; box-shadow:0 24px 70px rgba(15,23,42,.08); padding:28px; }
    .contact-alert { border-radius:8px; padding:12px 14px; margin-bottom:18px; font-size:14px; }
    .contact-alert.ok { background:#ecfdf5; color:#047857; border:1px solid #a7f3d0; }
    .contact-alert.err { background:#fef2f2; color:#b91c1c; border:1px solid #fecaca; }
    .contact-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .field { display:flex; flex-direction:column; gap:7px; }
    .field.full { grid-column:1 / -1; }
    .field label { font-size:13px; font-weight:800; color:#334155; }
    .field input, .field textarea { width:100%; border:1px solid #cbd5e1; border-radius:8px; padding:12px 13px; font:inherit; color:#0f172a; background:#fff; outline:none; transition:border-color .18s, box-shadow .18s; }
    .field textarea { min-height:150px; resize:vertical; }
    .field input:focus, .field textarea:focus { border-color:#2563eb; box-shadow:0 0 0 3px rgba(37,99,235,.12); }
    .contact-submit { margin-top:18px; width:100%; min-height:48px; border:0; border-radius:8px; background:#0f172a; color:white; font-weight:800; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; gap:9px; }
    .contact-submit:hover { background:#1e293b; }
    .contact-info-row { margin-top:36px; display:grid; grid-template-columns:repeat(3, 1fr); gap:14px; }
    .contact-info { background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:18px; }
    .contact-info strong { display:block; color:#0f172a; margin-bottom:4px; }
    .contact-info span, .contact-info a { color:#64748b; text-decoration:none; font-size:14px; }
    @media (max-width:900px) { .contact-head { grid-template-columns:1fr; } .contact-info-row { grid-template-columns:1fr; } }
    @media (max-width:600px) { .contact-wrap { padding:36px 14px 56px; } .contact-card { padding:18px; } .contact-grid { grid-template-columns:1fr; } }
</style>

<main class="contact-shell">
    <div class="contact-wrap">
        <section class="contact-head">
            <div>
                <span class="contact-kicker">Support and sales</span>
                <h1 class="contact-title">Tell us what you need.</h1>
                <p class="contact-copy">Questions about resumes, templates, billing, ATS scoring, or a broken workflow all land here. We save every message so the team can follow up properly.</p>
                <div class="contact-points">
                    <div class="contact-point"><span class="contact-icon">?</span><span>Product help for resume builder, cover letter, ATS checker, and downloads.</span></div>
                    <div class="contact-point"><span class="contact-icon">!</span><span>Bug reports include your email and phone so support can reproduce and close the loop.</span></div>
                    <div class="contact-point"><span class="contact-icon">$</span><span>Billing and subscription questions are routed to the admin lead queue.</span></div>
                </div>
            </div>

            <form method="POST" action="{{ route('contact.store') }}" class="contact-card">
                @csrf
                <div style="display:none;">
                    <input type="text" name="website" tabindex="-1" autocomplete="off">
                </div>
                @if(session('status'))
                    <div class="contact-alert ok">{{ session('status') }}</div>
                @endif
                @if($errors->any())
                    <div class="contact-alert err">{{ $errors->first() }}</div>
                @endif
                <div class="contact-grid">
                    <div class="field">
                        <label for="name">Name</label>
                        <input id="name" name="name" value="{{ old('name') }}" maxlength="160" required>
                    </div>
                    <div class="field">
                        <label for="email">Email</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="190" required>
                    </div>
                    <div class="field">
                        <label for="mobile">Phone</label>
                        <input id="mobile" name="mobile" type="tel" value="{{ old('mobile') }}" maxlength="10" pattern="[0-9]{10}" inputmode="numeric" required>
                    </div>
                    <div class="field">
                        <label for="subject">Subject</label>
                        <input id="subject" name="subject" value="{{ old('subject') }}" maxlength="190" required>
                    </div>
                    <div class="field full">
                        <label for="message">Message</label>
                        <textarea id="message" name="message" required maxlength="5000">{{ old('message') }}</textarea>
                    </div>
                </div>
                <button class="contact-submit" type="submit">Send Message</button>
            </form>
        </section>

        <section class="contact-info-row" aria-label="Contact details">
            <div class="contact-info"><strong>Email</strong><a href="mailto:support@cvbliss.in">support@cvbliss.in</a></div>
            <div class="contact-info"><strong>Hours</strong><span>Monday to Saturday, 10 AM to 7 PM</span></div>
            <div class="contact-info"><strong>Response</strong><span>Usually within one business day</span></div>
        </section>
    </div>
</main>
@endsection
