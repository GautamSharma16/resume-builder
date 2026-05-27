@extends('layouts.app')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,700;0,800;1,700;1,800&family=Inter:wght@400;500;600;700&display=swap');

/* ── Reset & Base ─────────────────────────────────── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}

.plans-root{
    min-height:100vh;
    padding:90px 20px 100px;
    font-family:'Inter',sans-serif;
    position:relative;
    overflow:hidden;
    background:#04070f;
}

/* ── Animated starfield canvas ───────────────────── */
#star-canvas{
    position:absolute;
    inset:0;
    pointer-events:none;
    z-index:0;
}

/* ── Glowing orbs (CSS only) ─────────────────────── */
.orb{
    position:absolute;
    border-radius:50%;
    filter:blur(90px);
    opacity:.45;
    pointer-events:none;
    z-index:0;
}
.orb-1{
    width:700px;height:500px;
    top:-120px;left:50%;
    transform:translateX(-50%);
    background:radial-gradient(circle,#3b5bff 0%,transparent 65%);
    animation:orbFloat 9s ease-in-out infinite alternate;
}
.orb-2{
    width:420px;height:420px;
    bottom:-80px;left:-60px;
    background:radial-gradient(circle,#1e3bb5 0%,transparent 65%);
    animation:orbFloat 12s ease-in-out infinite alternate-reverse;
}
.orb-3{
    width:380px;height:380px;
    top:30%;right:-80px;
    background:radial-gradient(circle,#5e28c8 0%,transparent 65%);
    animation:orbFloat 10s ease-in-out infinite alternate;
}
@keyframes orbFloat{
    0%{transform:translateX(-50%) translateY(0) scale(1)}
    100%{transform:translateX(-50%) translateY(30px) scale(1.06)}
}
.orb-2,.orb-3{animation:orbFloatSide ease-in-out infinite alternate}
@keyframes orbFloatSide{
    0%{transform:translateY(0) scale(1)}
    100%{transform:translateY(25px) scale(1.08)}
}

/* ── Inner wrapper ───────────────────────────────── */
.plans-inner{
    max-width:1200px;
    margin:auto;
    position:relative;
    z-index:2;
}

/* ── Header ──────────────────────────────────────── */
.plans-header{
    text-align:center;
    margin-bottom:64px;
    opacity:0;
    transform:translateY(32px);
    animation:fadeUp .8s cubic-bezier(.22,1,.36,1) .1s forwards;
}
.plans-header h1{
    font-family:'Playfair Display',serif;
    font-size:clamp(2.8rem,5.5vw,4.5rem);
    font-weight:800;
    color:#fff;
    line-height:1.1;
    letter-spacing:-.02em;
}
.plans-header h1 em{
    font-style:italic;
    background:linear-gradient(100deg,#7b93ff,#a78bfa);
    -webkit-background-clip:text;
    -webkit-text-fill-color:transparent;
    background-clip:text;
}
.plans-header p{
    margin-top:16px;
    color:#7a8aab;
    font-size:1.05rem;
    letter-spacing:.01em;
}

/* ── Grid ────────────────────────────────────────── */
.plan-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:24px;
    align-items:center;
}

/* ── Card ────────────────────────────────────────── */
.plan-card{
    position:relative;
    border-radius:28px;
    padding:38px 30px 32px;
    display:flex;
    flex-direction:column;
    background:linear-gradient(160deg,rgba(255,255,255,.065) 0%,rgba(255,255,255,.018) 100%);
    border:1px solid rgba(120,140,255,.15);
    backdrop-filter:blur(24px);
    -webkit-backdrop-filter:blur(24px);
    box-shadow:inset 0 1px 0 rgba(255,255,255,.08), 0 2px 40px rgba(0,0,0,.3);
    transition:transform .35s cubic-bezier(.22,1,.36,1), box-shadow .35s ease, border-color .35s ease;

    /* stagger fade-in */
    opacity:0;
    transform:translateY(40px);
}
.plan-card.animate-in{
    animation:fadeUp .72s cubic-bezier(.22,1,.36,1) forwards;
}
.plan-card:nth-child(1){animation-delay:.25s}
.plan-card:nth-child(2){animation-delay:.4s}
.plan-card:nth-child(3){animation-delay:.55s}

.plan-card:hover{
    transform:translateY(-8px);
    box-shadow:0 20px 60px rgba(50,80,200,.22), inset 0 1px 0 rgba(255,255,255,.1);
    border-color:rgba(120,150,255,.35);
}

/* featured */
.plan-card.featured{
    transform:scale(1.04);
    background:linear-gradient(160deg,rgba(60,80,255,.22) 0%,rgba(40,60,200,.1) 100%);
    border:1px solid rgba(100,130,255,.42);
    box-shadow:
        inset 0 1px 0 rgba(255,255,255,.12),
        0 0 60px rgba(70,100,255,.22),
        0 8px 40px rgba(0,0,0,.35);
}
.plan-card.featured:hover{
    transform:scale(1.04) translateY(-8px);
    box-shadow:0 24px 70px rgba(60,100,255,.32), inset 0 1px 0 rgba(255,255,255,.14);
}

/* shimmer sweep on hover */
.plan-card::before{
    content:'';
    position:absolute;
    inset:0;
    border-radius:inherit;
    background:linear-gradient(110deg,transparent 35%,rgba(255,255,255,.07) 50%,transparent 65%);
    background-size:200% 100%;
    background-position:200% 0;
    transition:background-position .6s ease;
    pointer-events:none;
}
.plan-card:hover::before{background-position:-200% 0}

/* ── Badge ───────────────────────────────────────── */
.badge-wrap{
    position:absolute;
    top:-16px;left:50%;
    transform:translateX(-50%);
}
.badge{
    display:inline-flex;
    align-items:center;
    gap:6px;
    background:linear-gradient(90deg,#f59e0b,#ef7c0a);
    color:#fff;
    font-size:.72rem;
    font-weight:700;
    letter-spacing:.1em;
    text-transform:uppercase;
    border-radius:999px;
    padding:7px 18px;
    box-shadow:0 4px 20px rgba(245,158,11,.45);
    white-space:nowrap;
    animation:badgePulse 2.5s ease-in-out infinite;
}
.badge::before{content:'⭐';font-size:.65rem}
@keyframes badgePulse{
    0%,100%{box-shadow:0 4px 20px rgba(245,158,11,.4)}
    50%{box-shadow:0 4px 32px rgba(245,158,11,.7)}
}

/* ── Plan name ───────────────────────────────────── */
.plan-name{
    font-size:.72rem;
    font-weight:700;
    letter-spacing:.18em;
    text-transform:uppercase;
    color:#6c7ea8;
    margin-bottom:20px;
}

/* ── Price ───────────────────────────────────────── */
.plan-price{
    display:flex;
    align-items:flex-start;
    gap:3px;
    line-height:1;
}
.currency{
    font-size:1.5rem;
    font-weight:700;
    color:#cdd5e8;
    margin-top:.5rem;
}
.amount{
    font-family:'Playfair Display',serif;
    font-size:clamp(3.2rem,5vw,4.2rem);
    font-weight:800;
    color:#fff;
    line-height:1;
    /* counter animation handled by JS */
}
.plan-validity{
    margin:8px 0 24px;
    color:#5f6e8e;
    font-size:.875rem;
}

/* ── Divider ─────────────────────────────────────── */
.plan-divider{
    height:1px;
    background:linear-gradient(90deg,transparent,rgba(255,255,255,.09),transparent);
    margin-bottom:26px;
}

/* ── Features ────────────────────────────────────── */
.plan-features{
    list-style:none;
    flex:1;
    display:flex;
    flex-direction:column;
    gap:14px;
    margin-bottom:28px;
}
.plan-features li{
    display:flex;
    align-items:center;
    gap:12px;
    font-size:.97rem;
    color:#c8d0e4;
    font-weight:500;
    opacity:0;
    transform:translateX(-10px);
    transition:opacity .4s ease, transform .4s ease;
}
.plan-card.animate-in .plan-features li{
    opacity:1;
    transform:translateX(0);
}
.plan-features li:nth-child(1){transition-delay:.5s}
.plan-features li:nth-child(2){transition-delay:.6s}
.plan-features li:nth-child(3){transition-delay:.7s}
.plan-features li:nth-child(4){transition-delay:.8s}

.check-icon{
    flex-shrink:0;
    width:22px;height:22px;
    border-radius:50%;
    background:rgba(34,197,120,.14);
    border:1px solid rgba(34,197,120,.25);
    display:flex;align-items:center;justify-content:center;
    transition:transform .25s ease, background .25s ease;
}
.plan-features li:hover .check-icon{
    background:rgba(34,197,120,.28);
    transform:scale(1.15) rotate(10deg);
}
.check-icon svg{
    width:11px;height:11px;
    stroke:#34d399;
    stroke-width:2.8;
    fill:none;
    stroke-linecap:round;stroke-linejoin:round;
}

/* ── Buttons ─────────────────────────────────────── */
.plan-btn{
    display:flex;
    align-items:center;
    justify-content:center;
    gap:8px;
    width:100%;
    text-decoration:none;
    border-radius:999px;
    padding:15px 20px;
    font-size:.95rem;
    font-weight:700;
    transition:transform .25s ease, box-shadow .25s ease, background .25s ease;
    position:relative;
    overflow:hidden;
}
.plan-btn::after{
    content:'';
    position:absolute;
    inset:0;
    background:rgba(255,255,255,.08);
    opacity:0;
    transition:opacity .2s;
}
.plan-btn:hover::after{opacity:1}

.plan-btn-outline{
    background:rgba(255,255,255,.04);
    border:1px solid rgba(255,255,255,.12);
    color:#c8d0e4;
}
.plan-btn-outline:hover{
    transform:translateY(-2px);
    box-shadow:0 6px 20px rgba(0,0,0,.25);
    border-color:rgba(255,255,255,.22);
}

.plan-btn-primary{
    background:linear-gradient(90deg,#4c6bff,#3a52ef);
    color:#fff;
    border:none;
    box-shadow:0 0 28px rgba(76,107,255,.4);
}
.plan-btn-primary:hover{
    transform:translateY(-3px);
    box-shadow:0 8px 36px rgba(76,107,255,.6);
}
/* arrow ripple */
.btn-arrow{
    display:inline-block;
    transition:transform .25s ease;
}
.plan-btn:hover .btn-arrow{transform:translateX(4px)}

/* ── Keyframes ───────────────────────────────────── */
@keyframes fadeUp{
    from{opacity:0;transform:translateY(40px)}
    to{opacity:1;transform:translateY(0)}
}

/* ── Responsive ──────────────────────────────────── */
@media(max-width:960px){
    .plan-grid{grid-template-columns:1fr;max-width:460px;margin:auto}
    .plan-card.featured{transform:none}
    .plan-card.featured:hover{transform:translateY(-8px)}
}
</style>

<div class="plans-root">
    <canvas id="star-canvas"></canvas>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <div class="plans-inner">

        <div class="plans-header">
            <h1>Choose the Plan<br>That Works for <em>You</em></h1>
            <p>No hidden fees. Cancel anytime. Start with a free preview.</p>
            @if(session('status'))
                <p style="margin-top:14px;color:#818cf8;font-weight:600">{{ session('status') }}</p>
            @endif
        </div>

        <div class="plan-grid">
            @foreach($plans as $plan)
                @php $isFeatured = $loop->index === 1; @endphp

                <div class="plan-card {{ $isFeatured ? 'featured' : '' }}">

                    @if($isFeatured)
                        <div class="badge-wrap">
                            <span class="badge">Most Popular</span>
                        </div>
                    @endif

                    <p class="plan-name">{{ $plan->name }}</p>

                    <div class="plan-price">
                        <span class="currency">₹</span>
                        <span class="amount" data-target="{{ (int)($plan->price_paise / 100) }}">{{ number_format($plan->price_paise / 100) }}</span>
                    </div>
                    <p class="plan-validity">valid for {{ $plan->duration_days }} days</p>

                    <div class="plan-divider"></div>

                    <ul class="plan-features">
                        <li>
                            <span class="check-icon"><svg viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg></span>
                            {{ is_null($plan->resume_limit) ? 'Unlimited' : $plan->resume_limit }} Resumes
                        </li>
                        <li>
                            <span class="check-icon"><svg viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg></span>
                            {{ is_null($plan->cover_letter_limit) ? 'Unlimited' : $plan->cover_letter_limit }} Cover Letters
                        </li>
                        <li>
                            <span class="check-icon"><svg viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg></span>
                            {{ $plan->ai_enabled ? 'Advanced AI Features' : 'Basic Features' }}
                        </li>
                        <li>
                            <span class="check-icon"><svg viewBox="0 0 12 12"><polyline points="2,6 5,9 10,3"/></svg></span>
                            {{ is_null($plan->downloads_allowed) ? 'Unlimited' : $plan->downloads_allowed }} Downloads
                        </li>
                    </ul>

                    @auth
                        <a href="{{ route('plans.checkout', $plan) }}"
                           class="plan-btn {{ $isFeatured ? 'plan-btn-primary' : 'plan-btn-outline' }}">
                            {{ $isFeatured ? 'Choose '.$plan->name : 'Get Started' }}
                            <span class="btn-arrow">→</span>
                        </a>
                    @else
                        <a href="{{ route('login', ['redirect' => route('plans.checkout', $plan)]) }}"
                           class="plan-btn {{ $isFeatured ? 'plan-btn-primary' : 'plan-btn-outline' }}">
                            {{ $isFeatured ? 'Choose '.$plan->name : 'Get Started' }}
                            <span class="btn-arrow">→</span>
                        </a>
                    @endauth

                </div>
            @endforeach
        </div>

    </div>
</div>

<script>
/* ── Starfield ─────────────────────────────────────── */
(function(){
    const canvas = document.getElementById('star-canvas');
    const ctx = canvas.getContext('2d');
    let W, H, stars = [];

    function resize(){
        W = canvas.width = window.innerWidth;
        H = canvas.height = window.innerHeight;
    }

    function initStars(n=160){
        stars = Array.from({length:n}, () => ({
            x: Math.random()*W,
            y: Math.random()*H,
            r: Math.random()*1.2+.2,
            a: Math.random(),
            speed: Math.random()*.4+.1,
            dir: Math.random() > .5 ? 1 : -1
        }));
    }

    function draw(){
        ctx.clearRect(0,0,W,H);
        stars.forEach(s => {
            s.a += s.speed * .008 * s.dir;
            if(s.a > 1 || s.a < 0) s.dir *= -1;
            ctx.beginPath();
            ctx.arc(s.x, s.y, s.r, 0, Math.PI*2);
            ctx.fillStyle = `rgba(180,195,255,${s.a * .7})`;
            ctx.fill();
        });
        requestAnimationFrame(draw);
    }

    resize();
    initStars();
    draw();
    window.addEventListener('resize', () => { resize(); initStars(); });
})();

/* ── Intersection Observer → card reveal ───────────── */
const cards = document.querySelectorAll('.plan-card');
const io = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if(e.isIntersecting){
            e.target.classList.add('animate-in');
            io.unobserve(e.target);
        }
    });
}, { threshold: .15 });
cards.forEach(c => io.observe(c));

/* ── Number count-up on reveal ─────────────────────── */
function countUp(el, target, duration=900){
    const start = performance.now();
    const fmt = n => n.toLocaleString('en-IN');
    (function tick(now){
        const p = Math.min((now-start)/duration, 1);
        const ease = 1 - Math.pow(1-p, 3);
        el.textContent = fmt(Math.round(target * ease));
        if(p < 1) requestAnimationFrame(tick);
        else el.textContent = fmt(target);
    })(start);
}

const amountEls = document.querySelectorAll('.amount[data-target]');
const amountIO = new IntersectionObserver((entries) => {
    entries.forEach(e => {
        if(e.isIntersecting){
            countUp(e.target, parseInt(e.target.dataset.target));
            amountIO.unobserve(e.target);
        }
    });
}, { threshold: .5 });
amountEls.forEach(el => amountIO.observe(el));

/* ── Mouse-tilt on cards ───────────────────────────── */
cards.forEach(card => {
    card.addEventListener('mousemove', e => {
        const r = card.getBoundingClientRect();
        const x = (e.clientX - r.left) / r.width  - .5;
        const y = (e.clientY - r.top)  / r.height - .5;
        const isFeatured = card.classList.contains('featured');
        const base = isFeatured ? 'scale(1.04)' : 'scale(1)';
        card.style.transform = `${base} perspective(700px) rotateY(${x*8}deg) rotateX(${-y*8}deg) translateY(-8px)`;
    });
    card.addEventListener('mouseleave', () => {
        const isFeatured = card.classList.contains('featured');
        card.style.transform = isFeatured ? 'scale(1.04)' : '';
    });
});
</script>
@endsection
