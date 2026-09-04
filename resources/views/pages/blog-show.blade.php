@extends('layouts.app')

@section('title', $post->title . ' - Cvbliss Blog')

@section('content')

<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400;1,700&family=Instrument+Sans:wght@400;500;600&display=swap" rel="stylesheet">

<style>
/* ─── TOKENS ─────────────────────────────────────────────── */
:root {
    --ink:        #1a1613;
    --ink-soft:   #5c534e;
    --ink-muted:  #9a8f89;
    --paper:      #faf8f5;
    --paper-warm: #f2ede6;
    --rule:       rgba(26,22,19,.10);
    --accent:     #2563eb;
    --accent-2:   #1d3461;
    --serif:      'Playfair Display', Georgia, serif;
    --sans:       'Instrument Sans', system-ui, sans-serif;
    --ease:       cubic-bezier(.25,.46,.45,.94);
}

*, *::before, *::after { box-sizing: border-box; margin: 0; }

body { background: var(--paper); color: var(--ink); }

/* ─── READING PROGRESS ───────────────────────────────────── */
#reading-progress {
    position: fixed; top: 0; left: 0; height: 3px; width: 0%;
    background: var(--accent);
    z-index: 9999;
    transition: width .1s linear;
}

/* ─── PAGE WRAPPER ───────────────────────────────────────── */
.post-root {
    max-width: 1160px;
    margin: 0 auto;
    padding: 0 1.5rem 6rem;
}

/* ─── BREADCRUMB ─────────────────────────────────────────── */
.post-breadcrumb {
    font-family: var(--sans);
    font-size: .78rem;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--ink-muted);
    padding: 2.2rem 0 2rem;
    display: flex;
    gap: .5rem;
    align-items: center;
}
.post-breadcrumb a { color: var(--ink-soft); text-decoration: none; }
.post-breadcrumb a:hover { color: var(--accent); }
.post-breadcrumb .sep { opacity: .4; }

/* ─── HERO ───────────────────────────────────────────────── */
.post-hero {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4rem;
    align-items: center;
    padding: 1rem 0 4rem;
    border-bottom: 1px solid var(--rule);
}

.post-hero-left {}

.post-kicker {
    display: inline-flex;
    align-items: center;
    gap: .45rem;
    font-family: var(--sans);
    font-size: .72rem;
    font-weight: 600;
    letter-spacing: .09em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 1.4rem;
}
.post-kicker::before {
    content: '';
    display: block;
    width: 18px; height: 2px;
    background: var(--accent);
}

.post-title {
    font-family: var(--serif);
    font-size: clamp(2.2rem, 4.5vw, 3.6rem);
    line-height: 1.15;
    color: var(--ink);
    font-weight: 700;
    margin-bottom: 1.6rem;
    letter-spacing: -.01em;
}

.post-excerpt {
    font-family: var(--sans);
    font-size: 1.05rem;
    line-height: 1.75;
    color: var(--ink-soft);
    margin-bottom: 2rem;
    max-width: 46ch;
}

.post-meta-row {
    display: flex;
    align-items: center;
    gap: 1.2rem;
    font-family: var(--sans);
    font-size: .82rem;
    color: var(--ink-muted);
    flex-wrap: wrap;
}
.post-meta-row .dot { opacity: .4; }
.post-meta-row .reading-time {
    background: var(--paper-warm);
    padding: .25rem .75rem;
    border-radius: 2rem;
    border: 1px solid var(--rule);
    font-size: .75rem;
    letter-spacing: .03em;
    color: var(--ink-soft);
}

.post-hero-right { position: relative; }

.post-featured-image {
    width: 100%;
    aspect-ratio: 4 / 3;
    object-fit: cover;
    border-radius: .5rem;
    display: block;
}

.post-hero-right .img-caption {
    font-family: var(--sans);
    font-size: .72rem;
    color: var(--ink-muted);
    margin-top: .8rem;
    letter-spacing: .02em;
    font-style: italic;
}

/* ─── LAYOUT: CONTENT + SIDEBAR ─────────────────────────── */
.post-layout {
    display: grid;
    grid-template-columns: 1fr 280px;
    gap: 5rem;
    margin-top: 4rem;
    align-items: start;
}

/* ─── BODY CONTENT ───────────────────────────────────────── */
.post-body {
    font-family: var(--sans);
    font-size: 1.05rem;
    line-height: 1.85;
    color: var(--ink);
    min-width: 0;
}

.post-body h2 {
    font-family: var(--serif);
    font-size: 1.9rem;
    font-weight: 700;
    color: var(--ink);
    margin: 3rem 0 1rem;
    line-height: 1.25;
    letter-spacing: -.01em;
}

.post-body h3 {
    font-family: var(--serif);
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--ink);
    margin: 2rem 0 .75rem;
    line-height: 1.3;
    font-style: italic;
}

.post-body p { margin-bottom: 1.5rem; }

.post-body a {
    color: var(--accent-2);
    text-decoration: underline;
    text-underline-offset: 3px;
}

.post-body ul, .post-body ol {
    padding-left: 1.5rem;
    margin-bottom: 1.5rem;
}

.post-body li { margin-bottom: .5rem; }

.post-body blockquote {
    border-left: 3px solid var(--accent);
    padding: 1.2rem 1.5rem;
    margin: 2.5rem 0;
    background: var(--paper-warm);
    border-radius: 0 .4rem .4rem 0;
}

.post-body blockquote p {
    font-family: var(--serif);
    font-size: 1.2rem;
    font-style: italic;
    color: var(--ink);
    margin: 0;
    line-height: 1.6;
}

.post-body pre {
    background: #1a1613;
    color: #f2ede6;
    padding: 1.4rem;
    border-radius: .5rem;
    overflow-x: auto;
    font-size: .88rem;
    margin: 2rem 0;
    line-height: 1.65;
}

.post-body code {
    font-size: .88em;
    background: var(--paper-warm);
    padding: .1em .4em;
    border-radius: .25rem;
    font-family: 'Menlo', monospace;
    color: var(--accent);
}

.post-body pre code {
    background: transparent;
    padding: 0;
    color: inherit;
}

.post-body img {
    width: 100%;
    border-radius: .5rem;
    margin: 2rem 0;
}

/* ─── FAQ COMPONENT (unique IDs via data attrs) ──────────── */
.faq-block {
    margin: 2.5rem 0;
    border: 1px solid var(--rule);
    border-radius: .5rem;
    overflow: hidden;
}

.faq-item {
    border-bottom: 1px solid var(--rule);
}
.faq-item:last-child { border-bottom: none; }

.faq-question {
    width: 100%;
    background: none;
    border: none;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.1rem 1.4rem;
    font-family: var(--sans);
    font-size: .97rem;
    font-weight: 600;
    color: var(--ink);
    text-align: left;
    gap: 1rem;
    transition: background .2s var(--ease);
}
.faq-question:hover { background: var(--paper-warm); }

.faq-icon {
    flex-shrink: 0;
    width: 22px; height: 22px;
    border-radius: 50%;
    border: 1.5px solid var(--rule);
    display: flex; align-items: center; justify-content: center;
    transition: transform .3s var(--ease), background .2s;
    color: var(--ink-soft);
    font-size: .9rem;
    font-style: normal;
}

.faq-answer {
    overflow: hidden;
    max-height: 0;
    transition: max-height .35s var(--ease);
}
.faq-answer-inner {
    padding: 0 1.4rem 1.2rem;
    font-family: var(--sans);
    font-size: .95rem;
    line-height: 1.75;
    color: var(--ink-soft);
}

/* open state — toggled by JS per unique data-faq-id */
.faq-item.is-open .faq-answer { max-height: 600px; }
.faq-item.is-open .faq-icon {
    transform: rotate(45deg);
    background: var(--accent);
    border-color: var(--accent);
    color: #fff;
}

/* ─── SIDEBAR ────────────────────────────────────────────── */
.post-sidebar {
   
    top: 2rem;
}

.sidebar-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2.5rem;
    border-bottom: 1px solid var(--rule);
}
.sidebar-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.sidebar-label {
    font-family: var(--sans);
    font-size: .7rem;
    font-weight: 600;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: var(--ink-muted);
    margin-bottom: 1rem;
}

/* Table of Contents */
.toc-list {
    list-style: none;
    padding: 0;
}
.toc-list li {
    margin-bottom: .5rem;
}
.toc-list a {
    font-family: var(--sans);
    font-size: .88rem;
    color: var(--ink-soft);
    text-decoration: none;
    line-height: 1.4;
    display: block;
    padding: .25rem 0 .25rem .75rem;
    border-left: 2px solid transparent;
    transition: all .2s var(--ease);
}
.toc-list a:hover, .toc-list a.active {
    color: var(--accent);
    border-left-color: var(--accent);
}

/* Share buttons */
.share-stack {
    display: flex;
    flex-direction: column;
    gap: .6rem;
}
.share-link {
    display: flex;
    align-items: center;
    gap: .7rem;
    font-family: var(--sans);
    font-size: .85rem;
    color: var(--ink-soft);
    text-decoration: none;
    padding: .55rem .9rem;
    border: 1px solid var(--rule);
    border-radius: .35rem;
    transition: all .2s var(--ease);
    background: transparent;
}
.share-link:hover {
    background: var(--paper-warm);
    border-color: rgba(26,22,19,.18);
    color: var(--ink);
    transform: translateX(3px);
}
.share-link svg {
    width: 16px; height: 16px;
    fill: currentColor;
    flex-shrink: 0;
}

/* ─── DIVIDER ORNAMENT ───────────────────────────────────── */
.ornament {
    text-align: center;
    font-family: var(--serif);
    font-size: 1.2rem;
    letter-spacing: .3em;
    color: var(--ink-muted);
    margin: 3rem 0;
    opacity: .5;
}

/* ─── MORE ARTICLES ──────────────────────────────────────── */
.more-articles {
    grid-column: 1 / -1;
    margin-top: 5rem;
    padding-top: 3rem;
    border-top: 1px solid var(--rule);
}

.more-articles-head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    margin-bottom: 2.5rem;
}

.more-articles-head h3 {
    font-family: var(--serif);
    font-size: 1.6rem;
    font-weight: 700;
    color: var(--ink);
}
.more-articles-head a {
    font-family: var(--sans);
    font-size: .82rem;
    color: var(--ink-soft);
    text-decoration: none;
    letter-spacing: .04em;
}
.more-articles-head a:hover { color: var(--accent); }

.related-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 2rem;
}

.related-card {
    text-decoration: none;
    color: inherit;
    display: block;
}

.related-card-img {
    width: 100%;
    aspect-ratio: 16 / 9;
    object-fit: cover;
    border-radius: .5rem;
    display: block;
    background: var(--paper-warm) center / cover no-repeat;
    transition: opacity .3s var(--ease);
}
.related-card:hover .related-card-img { opacity: .88; }

.related-card-meta {
    font-family: var(--sans);
    font-size: .72rem;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: var(--accent);
    margin: .9rem 0 .35rem;
    font-weight: 600;
}

.related-card h4 {
    font-family: var(--serif);
    font-size: 1.08rem;
    font-weight: 700;
    color: var(--ink);
    line-height: 1.35;
    transition: color .2s;
}
.related-card:hover h4 { color: var(--accent); }

.related-card p {
    font-family: var(--sans);
    font-size: .82rem;
    color: var(--ink-muted);
    margin-top: .3rem;
}

/* ─── RESPONSIVE ─────────────────────────────────────────── */
@media (max-width: 900px) {
    .post-hero {
        grid-template-columns: 1fr;
        gap: 2rem;
    }
    .post-layout {
        grid-template-columns: 1fr;
        gap: 3rem;
    }
    .post-sidebar {
        position: static;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }
    .sidebar-section {
        margin-bottom: 0;
        padding-bottom: 0;
        border-bottom: none;
    }
    .more-articles { grid-column: 1; }
}

@media (max-width: 540px) {
    .post-sidebar { grid-template-columns: 1fr; }
}

/* ─── SCROLL ANIMATION ───────────────────────────────────── */
.fade-up {
    opacity: 1;
    transform: none;
    transition: opacity .55s var(--ease), transform .55s var(--ease);
}
.fade-up.visible {
    opacity: 1;
    transform: none;
}
</style>

<!-- Reading progress -->
<div id="reading-progress"></div>

<div class="post-root">

    <!-- Breadcrumb -->
    <nav class="post-breadcrumb" aria-label="Breadcrumb">
        <a href="/">Home</a>
        <span class="sep">›</span>
        <a href="{{ route('interview') }}">Blog</a>
        <span class="sep">›</span>
        <span>{{ $post->category ?? 'Career Advice' }}</span>
    </nav>

    <!-- Hero -->
    <header class="post-hero fade-up">
        <div class="post-hero-left">
            <div class="post-kicker">{{ $post->category ?? 'Career Advice' }}</div>
            <h1 class="post-title">{{ $post->title }}</h1>

            @if($post->excerpt)
            <p class="post-excerpt">{{ $post->excerpt }}</p>
            @endif

            <div class="post-meta-row">
                <time>{{ $post->published_at?->format('M d, Y') ?? $post->created_at->format('M d, Y') }}</time>
                <span class="dot">·</span>
                <span class="reading-time">{{ ceil(str_word_count(strip_tags($post->body)) / 200) }} min read</span>
            </div>
        </div>

        <div class="post-hero-right">
            @if($post->thumbnail)
            <img
                src="{{ asset('storage/' . $post->thumbnail) }}"
                alt="{{ $post->title }}"
                class="post-featured-image"
                loading="eager"
                width="800"
                height="600"
            >
            @endif
        </div>
    </header>

    <!-- Content layout -->
    <div class="post-layout">

        <!-- Main body -->
        <article class="post-body fade-up" id="post-content">
            {!! $post->body !!}
        </article>

        <!-- Sidebar -->
        <aside class="post-sidebar" aria-label="Article sidebar">

            <!-- Table of contents (auto-built) -->
            <div class="sidebar-section" id="toc-section">
                <p class="sidebar-label">In this article</p>
                <ul class="toc-list" id="toc-list"></ul>
            </div>

            <!-- Share -->
            <div class="sidebar-section">
                <p class="sidebar-label">Share</p>
                <div class="share-stack">
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener" class="share-link">
                        <svg viewBox="0 0 24 24"><path d="M22 5.9c-.8.4-1.6.6-2.5.8a4.2 4.2 0 0 0 1.8-2.3 8.3 8.3 0 0 1-2.6 1 4.2 4.2 0 0 0-7.2 3.8A12 12 0 0 1 3 4.8a4.2 4.2 0 0 0 1.3 5.6 4 4 0 0 1-1.9-.5v.1a4.2 4.2 0 0 0 3.4 4.1 4.2 4.2 0 0 1-1.9.1 4.2 4.2 0 0 0 3.9 2.9A8.4 8.4 0 0 1 2 19.5a11.8 11.8 0 0 0 6.4 1.9c7.7 0 12-6.4 12-12v-.6c.8-.6 1.5-1.3 2-2.1"/></svg>
                        Share on X
                    </a>
                    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="share-link">
                        <svg viewBox="0 0 24 24"><path d="M20.4 20.4h-3.6v-5.6c0-1.3-.5-2.2-1.7-2.2-.9 0-1.5.6-1.7 1.1-.1.2-.1.5-.1.9v5.8H9.7s.1-9.4 0-10.4h3.6v1.5c.5-.8 1.3-1.9 3.2-1.9 2.3 0 4 1.5 4 4.8v6zM6 8.4a2.1 2.1 0 1 1 0-4.2 2.1 2.1 0 0 1 0 4.2zM7.8 20.4H4.2V10h3.6v10.4z"/></svg>
                        Share on LinkedIn
                    </a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="share-link">
                        <svg viewBox="0 0 24 24"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9v-2.9h2.5V9.8c0-2.5 1.5-3.8 3.7-3.8 1.1 0 2.2.2 2.2.2v2.4h-1.3c-1.3 0-1.7.8-1.7 1.6v2h2.9l-.5 2.9h-2.4v7A10 10 0 0 0 22 12"/></svg>
                        Share on Facebook
                    </a>
                    <button class="share-link" id="copy-link-btn" style="cursor:pointer; border:none; background:transparent; width:100%;">
                        <svg viewBox="0 0 24 24"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71" style="stroke:currentColor;fill:none;stroke-width:2;stroke-linecap:round;"/></svg>
                        Copy link
                    </button>
                </div>
            </div>

        </aside>

        <!-- More articles full-width -->
        @if($popularPosts->count())
        <footer class="more-articles fade-up">
            <div class="more-articles-head">
                <h3>More from the blog</h3>
                <a href="{{ route('interview') }}">View all articles →</a>
            </div>

            <div class="related-grid">
                @foreach($popularPosts as $popular)
                <a href="{{ route('blog.show', $popular->slug) }}" class="related-card">
                    <div class="related-card-img"
                        style="background-image:url('{{ $popular->thumbnail ? asset('storage/' . $popular->thumbnail) : 'https://placehold.co/600x338/f2ede6/9a8f89?text=Cvbliss' }}')">
                    </div>
                    <p class="related-card-meta">{{ $popular->category ?? 'Career Advice' }}</p>
                    <h4>{{ $popular->title }}</h4>
                    <p>{{ $popular->published_at?->format('M d, Y') ?? $popular->created_at->format('M d, Y') }}</p>
                </a>
                @endforeach
            </div>
        </footer>
        @endif

    </div><!-- .post-layout -->

</div><!-- .post-root -->

<script>
/* ── Reading progress ─────────────────────────────────────── */
const progressBar = document.getElementById('reading-progress');
window.addEventListener('scroll', () => {
    const scrollTop = window.scrollY;
    const docH = document.body.scrollHeight - window.innerHeight;
    progressBar.style.width = (scrollTop / docH * 100) + '%';
}, { passive: true });

/* ── Fade-up on scroll ────────────────────────────────────── */
const fadeEls = document.querySelectorAll('.fade-up');
const io = new IntersectionObserver(entries => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); } });
}, { threshold: .08 });
fadeEls.forEach(el => io.observe(el));

/* ── Auto table of contents ───────────────────────────────── */
(function buildTOC() {
    const headings = document.querySelectorAll('#post-content h2, #post-content h3');
    const list = document.getElementById('toc-list');
    const section = document.getElementById('toc-section');
    if (!headings.length) { section.style.display = 'none'; return; }

    headings.forEach((h, i) => {
        if (!h.id) h.id = 'heading-' + i;
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.href = '#' + h.id;
        a.textContent = h.textContent;
        if (h.tagName === 'H3') a.style.paddingLeft = '1.5rem';
        li.appendChild(a);
        list.appendChild(li);
    });

    const tocLinks = list.querySelectorAll('a');
    const headingIo = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (e.isIntersecting) {
                tocLinks.forEach(l => l.classList.remove('active'));
                const match = list.querySelector('a[href="#' + e.target.id + '"]');
                if (match) match.classList.add('active');
            }
        });
    }, { rootMargin: '-10% 0px -80% 0px' });
    headings.forEach(h => headingIo.observe(h));
})();

/* ── FAQ accordion — UNIQUE per block, no cross-contamination ─
   Each .faq-block gets a unique namespace via data-faq-group.
   Each .faq-item inside it gets a data-faq-id scoped to its group.
   Clicking one ONLY toggles within its own group context.
─────────────────────────────────────────────────────────────── */
(function initFAQs() {
    document.querySelectorAll('.faq-block').forEach((block, groupIndex) => {
        const groupId = 'faq-group-' + groupIndex + '-' + Date.now();
        block.setAttribute('data-faq-group', groupId);

        block.querySelectorAll('.faq-item').forEach((item, itemIndex) => {
            const uniqueId = groupId + '--item-' + itemIndex;
            item.setAttribute('data-faq-id', uniqueId);

            const btn = item.querySelector('.faq-question');
            const answer = item.querySelector('.faq-answer');
            if (!btn || !answer) return;

            btn.setAttribute('aria-controls', uniqueId + '-answer');
            btn.setAttribute('aria-expanded', 'false');
            answer.setAttribute('id', uniqueId + '-answer');

            btn.addEventListener('click', () => {
                /* Only look inside THIS block — no global selectors */
                const isOpen = item.classList.contains('is-open');

                /* Close all siblings within THIS block only */
                block.querySelectorAll('[data-faq-id]').forEach(sibling => {
                    sibling.classList.remove('is-open');
                    const sibBtn = sibling.querySelector('.faq-question');
                    if (sibBtn) sibBtn.setAttribute('aria-expanded', 'false');
                });

                /* Toggle clicked item */
                if (!isOpen) {
                    item.classList.add('is-open');
                    btn.setAttribute('aria-expanded', 'true');
                }
            });
        });
    });
})();

/* ── Copy link ────────────────────────────────────────────── */
document.getElementById('copy-link-btn')?.addEventListener('click', () => {
    navigator.clipboard.writeText(window.location.href).then(() => {
        const btn = document.getElementById('copy-link-btn');
        const orig = btn.innerHTML;
        btn.innerHTML = btn.innerHTML.replace('Copy link', 'Copied!');
        setTimeout(() => { btn.innerHTML = orig; }, 2000);
    });
});
</script>

@endsection