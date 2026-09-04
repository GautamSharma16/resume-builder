@extends('layouts.app')

@section('content')

<style>
  @import url('https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&family=Playfair+Display:ital,wght@0,700;0,900;1,700;1,900&display=swap');

  :root {
    --ink:        #0b1221;
    --paper:      #ffffff;
    --accent:     #2563eb;
    --accent2:    #1d4ed8;
    --muted:      #64748b;
    --muted-lt:   #94a3b8;
    --surface:    #f7f9fc;
    --border:     rgba(0,0,0,0.07);
    --border-md:  rgba(0,0,0,0.11);
    --radius-sm:  8px;
    --radius:     12px;
    --radius-lg:  18px;
    --t:          0.3s cubic-bezier(.4,0,.2,1);

    --font-body: 'DM Sans', sans-serif;
    --font-disp: 'Playfair Display', serif;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  .blog-root {
    font-family: var(--font-body);
    background: var(--paper);
    color: var(--ink);
    -webkit-font-smoothing: antialiased;
  }

  /* ════════════════════════════════════════
     MASTHEAD — compact, no wasted space
  ════════════════════════════════════════ */
  .blog-masthead {
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    padding: 48px 0 0;
    overflow: hidden;
    position: relative;
  }

  /* subtle grid texture */
  .blog-masthead::before {
    content: '';
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(var(--border) 1px, transparent 1px),
      linear-gradient(90deg, var(--border) 1px, transparent 1px);
    background-size: 40px 40px;
    opacity: 0.6;
    pointer-events: none;
  }

  .masthead-inner {
    max-width: 1406px;
    margin: 0 auto;
    padding: 0 28px;
    position: relative;
    z-index: 1;
  }

  /* top row: eyebrow + search side by side */
  .masthead-top-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 12px;
  }

  .masthead-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 10.5px;
    font-weight: 700;
    letter-spacing: .2em;
    text-transform: uppercase;
    color: var(--accent);
  }
  .masthead-eyebrow::before {
    content: '';
    width: 24px; height: 1.5px;
    background: var(--accent);
    display: inline-block;
  }

  /* search inline in masthead */
  .masthead-search {
    position: relative;
    flex-shrink: 0;
  }
  .masthead-search input {
    background: white;
    border: 1px solid var(--border-md);
    border-radius: 9999px;
    padding: 9px 40px 9px 16px;
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--ink);
    outline: none;
    width: 220px;
    transition: var(--t);
    box-shadow: 0 1px 6px rgba(0,0,0,0.04);
  }
  .masthead-search input::placeholder { color: var(--muted-lt); }
  .masthead-search input:focus {
    width: 260px;
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
  }
  .masthead-search-icon {
    position: absolute;
    right: 13px; top: 50%;
    transform: translateY(-50%);
    color: var(--muted-lt);
    pointer-events: none;
  }

  /* title block */
  .masthead-title-row {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 24px;
    flex-wrap: wrap;
    padding-bottom: 28px;
    border-bottom: 1px solid var(--border);
  }

  .masthead-title {
    font-family: var(--font-disp);
    font-weight: 900;
    font-size: clamp(36px, 5.5vw, 64px);
    line-height: 1.0;
    color: var(--ink);
    letter-spacing: -1.5px;
  }
  .masthead-title em {
    font-style: italic;
    color: var(--accent);
  }

  .masthead-right {
    flex-shrink: 0;
    text-align: right;
  }
  .masthead-sub {
    font-size: 14px;
    color: var(--muted);
    line-height: 1.65;
    max-width: 300px;
    margin-left: auto;
  }
  .masthead-count-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 12px;
    background: white;
    border: 1px solid var(--border-md);
    border-radius: 9999px;
    padding: 5px 14px;
    font-size: 12px;
    font-weight: 600;
    color: var(--muted);
  }
  .masthead-count-pill span {
    font-weight: 700;
    color: var(--ink);
  }

  /* ════════════════════════════════════════
     FILTER BAR
  ════════════════════════════════════════ */
  .filter-bar {
    background: white;
    border-bottom: 1px solid var(--border);
    
    top: 0;
    z-index: 50;
  }
  .filter-inner {
    max-width: 1120px;
    margin: 0 auto;
    padding: 0 28px;
    display: flex;
    gap: 6px;
    overflow-x: auto;
    scrollbar-width: none;
    padding-top: 12px;
    padding-bottom: 12px;
    align-items: center;
  }
  .filter-inner::-webkit-scrollbar { display: none; }

  .chip {
    flex-shrink: 0;
    padding: 5px 15px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid var(--border-md);
    background: transparent;
    color: var(--muted);
    transition: var(--t);
    font-family: var(--font-body);
    letter-spacing: 0.02em;
  }
  .chip:hover { background: var(--surface); color: var(--ink); }
  .chip.active {
    background: var(--ink);
    color: white;
    border-color: var(--ink);
    font-weight: 600;
  }

  /* ════════════════════════════════════════
     BLOG BODY
  ════════════════════════════════════════ */
  .blog-body {
    max-width: 1470px;
    margin: 0 auto;
    padding: 36px 28px 72px;
  }

  /* ════════════════════════════════════════
     ARTICLES GRID
  ════════════════════════════════════════ */
  .articles-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 18px;
  }

  /* ── Card base ── */
  .article-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: var(--radius-lg);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: var(--t);
    cursor: pointer;
    position: relative;
    animation: fadeUp .45s both;
  }
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .article-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 16px 40px rgba(0,0,0,0.09);
    border-color: var(--border-md);
  }
  .article-card:nth-child(1) { animation-delay: .04s; }
  .article-card:nth-child(2) { animation-delay: .09s; }
  .article-card:nth-child(3) { animation-delay: .14s; }
  .article-card:nth-child(4) { animation-delay: .19s; }
  .article-card:nth-child(5) { animation-delay: .24s; }
  .article-card:nth-child(6) { animation-delay: .29s; }

  /* Featured */
  .article-card.featured { grid-column: span 7; }
  .article-card.featured .card-img-wrap { aspect-ratio: 4 / 3; }
  .article-card.featured .card-title { font-size: 24px; }

  /* Second (sidebar) */
  .article-card.regular { grid-column: span 5; }
  .article-card.regular .card-img-wrap { aspect-ratio: 4 / 3; }

  /* Small cards */
  .article-card.small {
    grid-column: span 4;
    flex-direction: row;
  }
  .article-card.small .card-img-wrap {
    width: 130px;
    min-width: 130px;
    aspect-ratio: 4 / 3;
    height: auto;
  }
  .article-card.small .card-body { padding: 16px; }
  .article-card.small .card-excerpt { display: none; }
  .article-card.small .card-title { font-size: 14.5px; line-height: 1.4; }

  /* ── Card image ── */
  .card-img-wrap {
    width: 100%;
    overflow: hidden;
    position: relative;
    background: #e9ecf1;
    flex-shrink: 0;
  }
  .card-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .55s cubic-bezier(.4,0,.2,1);
    display: block;
  }
  .article-card:hover .card-img-wrap img { transform: scale(1.04); }

  .card-img-placeholder {
    width: 100%; height: 100%;
    min-height: 160px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
    font-family: var(--font-disp);
    font-size: 44px;
    color: rgba(0,0,0,0.1);
    font-style: italic;
  }
  .article-card.small .card-img-placeholder { font-size: 28px; min-height: 100%; }

  .card-reading-time {
    position: absolute;
    bottom: 10px; right: 10px;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(6px);
    color: white;
    font-size: 10.5px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 9999px;
    letter-spacing: .04em;
  }

  /* ── Card body ── */
  .card-body {
    padding: 20px;
    display: flex;
    flex-direction: column;
    flex: 1;
  }
  .card-category {
    display: inline-block;
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 8px;
  }
  .card-title {
    font-family: var(--font-disp);
    font-weight: 700;
    font-size: 18px;
    line-height: 1.3;
    color: var(--ink);
    margin-bottom: 8px;
    transition: color var(--t);
  }
  .article-card:hover .card-title { color: var(--accent); }

  .card-excerpt {
    font-size: 13.5px;
    line-height: 1.7;
    color: var(--muted);
    font-weight: 400;
    flex: 1;
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  .card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 16px;
    padding-top: 13px;
    border-top: 1px solid var(--border);
  }
  .card-author {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .card-avatar {
    width: 28px; height: 28px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: 700;
    color: white;
    flex-shrink: 0;
  }
  .card-author-name { font-size: 12px; font-weight: 600; color: var(--ink); line-height: 1.3; }
  .card-date { font-size: 11px; color: var(--muted-lt); }

  .card-read-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11.5px;
    font-weight: 700;
    color: var(--ink);
    text-decoration: none;
    letter-spacing: .05em;
    text-transform: uppercase;
    transition: var(--t);
    border: none;
    background: none;
    cursor: pointer;
    font-family: var(--font-body);
  }
  .card-read-btn svg { transition: transform var(--t); }
  .article-card:hover .card-read-btn { color: var(--accent); }
  .article-card:hover .card-read-btn svg { transform: translateX(3px); }

  /* ── Section divider ── */
  .section-divider {
    grid-column: span 12;
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 4px 0;
  }
  .section-divider span {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: var(--muted-lt);
    white-space: nowrap;
  }
  .section-divider::before, .section-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
  }

  /* ── Empty ── */
  .empty-state {
    grid-column: span 12;
    text-align: center;
    padding: 72px 24px;
  }
  .empty-state-icon { font-size: 48px; margin-bottom: 16px; opacity: .3; }
  .empty-state h3 { font-family: var(--font-disp); font-size: 22px; color: var(--ink); margin-bottom: 6px; }
  .empty-state p { font-size: 14px; color: var(--muted); }

  /* ── Load more ── */
  .load-more-wrap {
    text-align: center;
    padding: 8px 0 36px;
    margin-top: 32px;
  }
  .load-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 28px;
    border: 1.5px solid var(--border-md);
    border-radius: 9999px;
    font-family: var(--font-body);
    font-size: 12.5px;
    font-weight: 700;
    letter-spacing: .07em;
    text-transform: uppercase;
    color: var(--ink);
    background: transparent;
    cursor: pointer;
    transition: var(--t);
    text-decoration: none;
  }
  .load-more-btn:hover { background: var(--ink); color: white; border-color: var(--ink); }

  /* ════════════════════════════════════════
     NEWSLETTER — tighter, inline form
  ════════════════════════════════════════ */
  .newsletter-section {
    background: var(--surface);
    border-top: 1px solid var(--border);
    padding: 52px 28px;
  }
  .newsletter-inner {
    max-width: 1120px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 32px;
    flex-wrap: wrap;
  }
  .newsletter-text h2 {
    font-family: var(--font-disp);
    font-size: clamp(22px, 3vw, 32px);
    font-weight: 900;
    color: var(--ink);
    letter-spacing: -.5px;
    line-height: 1.1;
  }
  .newsletter-text p {
    margin-top: 6px;
    font-size: 14px;
    color: var(--muted);
    line-height: 1.6;
  }
  .newsletter-form {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
  }
  .newsletter-form input {
    background: white;
    border: 1px solid var(--border-md);
    border-radius: 9999px;
    padding: 11px 20px;
    font-family: var(--font-body);
    font-size: 13.5px;
    color: var(--ink);
    outline: none;
    width: 260px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
    transition: var(--t);
  }
  .newsletter-form input::placeholder { color: var(--muted-lt); }
  .newsletter-form input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
  .newsletter-form button {
    background: var(--ink);
    color: white;
    border: none;
    padding: 11px 22px;
    border-radius: 9999px;
    font-family: var(--font-body);
    font-size: 13px;
    font-weight: 700;
    letter-spacing: .04em;
    cursor: pointer;
    transition: var(--t);
    white-space: nowrap;
  }
  .newsletter-form button:hover {
    background: var(--accent);
    box-shadow: 0 4px 14px rgba(37,99,235,0.3);
    transform: translateY(-1px);
  }

  /* ════════════════════════════════════════
     RESPONSIVE
  ════════════════════════════════════════ */
  @media (max-width: 1000px) {
    .article-card.featured { grid-column: span 12; }
    .article-card.regular  { grid-column: span 12; }
    .article-card.small    { grid-column: span 6; }
    .article-card.small .card-img-wrap { width: 120px; min-width: 120px; }
  }
  @media (max-width: 680px) {
    .article-card.small { grid-column: span 12; }
    .masthead-title-row { flex-direction: column; align-items: flex-start; }
    .masthead-right { text-align: left; }
    .masthead-sub { margin-left: 0; }
    .newsletter-inner { flex-direction: column; }
    .newsletter-form { flex-direction: column; width: 100%; }
    .newsletter-form input { width: 100%; }
  }
</style>

<div class="blog-root">

  {{-- ── MASTHEAD ── --}}
  <section class="blog-masthead">
    <div class="masthead-inner">

      <div class="masthead-top-row">
        <div class="masthead-eyebrow">The Journal</div>
        <div class="masthead-search">
          <input type="text" placeholder="Search articles…" id="searchInput">
          <svg class="masthead-search-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
        </div>
      </div>

      <div class="masthead-title-row">
        <h1 class="masthead-title">Ideas worth<br><em>reading.</em></h1>
        <div class="masthead-right">
          <p class="masthead-sub">Fresh perspectives and expert insights to help you grow — curated by our team.</p>
          <div class="masthead-count-pill">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
            <span>{{ $articles instanceof \Illuminate\Pagination\LengthAwarePaginator ? $articles->total() : $articles->count() }}</span> articles
          </div>
        </div>
      </div>

    </div>
  </section>

  {{-- ── FILTER BAR ── --}}
  <div class="filter-bar">
    <div class="filter-inner" id="filterBar">
      <button class="chip active" data-cat="all">All</button>
      @php $cats = $articles->pluck('category')->filter()->unique()->values(); @endphp
      @foreach($cats as $cat)
        <button class="chip" data-cat="{{ Str::slug($cat) }}">{{ $cat }}</button>
      @endforeach
    </div>
  </div>

  {{-- ── ARTICLES GRID ── --}}
  <div class="blog-body">
    <div class="articles-grid" id="articlesGrid">

      @forelse($articles as $i => $article)

        @if($i === 0)
          <article class="article-card featured"
                   data-cat="{{ Str::slug($article->category ?? '') }}"
                   onclick="window.location='{{ route('blog.show', $article->slug) }}'">
            <div class="card-img-wrap">
              @if($article->thumbnail ?? false)
                <img src="{{ asset('storage/' . $article->thumbnail) }}" alt="{{ $article->title }}" loading="lazy" width="600" height="400">
              @else
                <div class="card-img-placeholder">{{ substr($article->title, 0, 1) }}</div>
              @endif
              <span class="card-reading-time">{{ ceil(str_word_count(strip_tags($article->body ?? '')) / 200) }} min read</span>
            </div>
            <div class="card-body">
              <span class="card-category">{{ $article->category ?? 'Featured' }}</span>
              <h2 class="card-title">{{ $article->title }}</h2>
              <p class="card-excerpt">{{ $article->excerpt }}</p>
              <div class="card-footer">
                <div class="card-author">
                  <div class="card-avatar">{{ strtoupper(substr($article->author ?? 'A', 0, 1)) }}</div>
                  <div>
                    <div class="card-author-name">{{ $article->author ?? 'Editorial Team' }}</div>
                    <div class="card-date">{{ optional($article->published_at ?? $article->created_at)->format('M d, Y') }}</div>
                  </div>
                </div>
                <a href="{{ route('blog.show', $article->slug) }}" class="card-read-btn">
                  Read <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
              </div>
            </div>
          </article>

        @elseif($i === 1)
          <article class="article-card regular"
                   data-cat="{{ Str::slug($article->category ?? '') }}"
                   onclick="window.location='{{ route('blog.show', $article->slug) }}'">
            <div class="card-img-wrap">
              @if($article->thumbnail ?? false)
                <img src="{{ asset('storage/' . $article->thumbnail) }}" alt="{{ $article->title }}" loading="lazy">
              @else
                <div class="card-img-placeholder">{{ substr($article->title, 0, 1) }}</div>
              @endif
              <span class="card-reading-time">{{ ceil(str_word_count(strip_tags($article->body ?? '')) / 200) }} min read</span>
            </div>
            <div class="card-body">
              <span class="card-category">{{ $article->category ?? 'Guide' }}</span>
              <h2 class="card-title">{{ $article->title }}</h2>
              <p class="card-excerpt">{{ $article->excerpt }}</p>
              <div class="card-footer">
                <div class="card-author">
                  <div class="card-avatar" style="background: linear-gradient(135deg,#3b82f6,#1d4ed8)">
                    {{ strtoupper(substr($article->author ?? 'A', 0, 1)) }}
                  </div>
                  <div>
                    <div class="card-author-name">{{ $article->author ?? 'Editorial Team' }}</div>
                    <div class="card-date">{{ optional($article->published_at ?? $article->created_at)->format('M d, Y') }}</div>
                  </div>
                </div>
                <a href="{{ route('blog.show', $article->slug) }}" class="card-read-btn">
                  Read <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
              </div>
            </div>
          </article>

        @else
          @if($i === 2)
            <div class="section-divider"><span>More articles</span></div>
          @endif

          <article class="article-card small"
                   data-cat="{{ Str::slug($article->category ?? '') }}"
                   onclick="window.location='{{ route('blog.show', $article->slug) }}'">
            <div class="card-img-wrap">
              @if($article->thumbnail ?? false)
                <img src="{{ asset('storage/' . $article->thumbnail) }}" alt="{{ $article->title }}" loading="lazy">
              @else
                <div class="card-img-placeholder">{{ substr($article->title, 0, 1) }}</div>
              @endif
            </div>
            <div class="card-body">
              <span class="card-category">{{ $article->category ?? 'Tips' }}</span>
              <h2 class="card-title">{{ $article->title }}</h2>
              <p class="card-excerpt">{{ $article->excerpt }}</p>
              <div class="card-footer">
                <div class="card-date">{{ optional($article->published_at ?? $article->created_at)->format('M d, Y') }}</div>
                <span style="font-size:11px;color:var(--muted-lt);">{{ ceil(str_word_count(strip_tags($article->body ?? '')) / 200) }} min</span>
              </div>
            </div>
          </article>
        @endif

      @empty
        <div class="empty-state">
          <div class="empty-state-icon">✦</div>
          <h3>Nothing here yet.</h3>
          <p>Check back soon — articles are on the way.</p>
        </div>
      @endforelse

    </div>

    @if($articles instanceof \Illuminate\Pagination\LengthAwarePaginator && $articles->hasMorePages())
      <div class="load-more-wrap">
        <a href="{{ $articles->nextPageUrl() }}" class="load-more-btn">
          Load more
          <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
        </a>
      </div>
    @endif
  </div>

  {{-- ── NEWSLETTER ── --}}
  <section class="newsletter-section">
    <div class="newsletter-inner">
      <div class="newsletter-text">
        <h2>Get articles in your inbox.</h2>
        <p>No noise. Just the best stories, once a week.</p>
      </div>
      <form class="newsletter-form" onsubmit="return false;">
        <input type="email" placeholder="your@email.com">
        <button type="submit">Subscribe →</button>
      </form>
    </div>
  </section>

</div>

<script>
  document.querySelectorAll('.chip').forEach(chip => {
    chip.addEventListener('click', () => {
      document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      const cat = chip.dataset.cat;
      document.querySelectorAll('.article-card').forEach(card => {
        card.style.display = (cat === 'all' || !card.dataset.cat || card.dataset.cat === cat) ? '' : 'none';
      });
    });
  });

  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const q = searchInput.value.toLowerCase();
      document.querySelectorAll('.article-card').forEach(card => {
        card.style.display = card.textContent.toLowerCase().includes(q) ? '' : 'none';
      });
    });
  }
</script>

@endsection