@extends('layouts.app')

@section('content')


<style>
  :root {
    --ink:       var(--navy, #0b1221);
    --paper:     #ffffff;
    --accent:    var(--blue, #2563eb);
    --accent2:   var(--blue-dark, #1d4ed8);
    --muted:     var(--muted, #64748b);
    --card-bg:   var(--white, #ffffff);
    --border:    var(--border, rgba(0,0,0,0.07));
    --radius:    12px;
    --transition: 0.35s cubic-bezier(.4,0,.2,1);
  }

  /* ── Base ── */
  .blog-root {
    font-family: var(--font-body);
    background: var(--paper);
    min-height: 100vh;
    color: var(--ink);
  }

  /* ── Hero masthead ── */
  .blog-masthead {
    position: relative;
    background: linear-gradient(150deg, #ffffff 0%, #fafcff 50%, #f5f7ff 100%);
    overflow: hidden;
    padding: 80px 0 64px;
    border-bottom: 1px solid var(--border);
  }
  .blog-masthead::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at center, rgba(37,99,235,0.05) 0%, transparent 70%);
  }
  .blog-masthead::after {
    content: 'CAREER';
    position: absolute;
    right: -20px;
    bottom: -40px;
    font-family: var(--font-display);
    font-weight: 900;
    font-size: clamp(120px, 22vw, 260px);
    line-height: 1;
    color: rgba(37,99,235,0.03);
    pointer-events: none;
    user-select: none;
    letter-spacing: -4px;
  }
  .masthead-inner {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 24px;
    position: relative;
    z-index: 1;
  }
  .masthead-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 11px;
    font-weight: 500;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: var(--accent);
    margin-bottom: 20px;
  }
  .masthead-eyebrow span {
    width: 28px;
    height: 1px;
    background: var(--accent);
    display: inline-block;
  }
  .masthead-title {
    font-family: var(--font-display);
    font-weight: 900;
    font-size: clamp(40px, 6vw, 76px);
    line-height: 1.05;
    color: var(--navy);
    letter-spacing: -1px;
    max-width: 640px;
  }
  .masthead-title em {
    font-style: italic;
    background: linear-gradient(135deg, var(--blue), var(--accent2));
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
  }
  .masthead-sub {
    margin-top: 20px;
    font-size: 17px;
    color: var(--muted);
    font-weight: 400;
    max-width: 440px;
    line-height: 1.7;
  }
  .masthead-meta {
    display: flex;
    align-items: center;
    gap: 24px;
    margin-top: 36px;
  }
  .masthead-count {
    font-size: 13px;
    color: var(--muted);
    font-weight: 500;
  }
  .masthead-search {
    margin-left: auto;
    position: relative;
  }
  .masthead-search input {
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 100px;
    padding: 10px 44px 10px 18px;
    font-family: var(--font-body);
    font-size: 13px;
    color: var(--ink);
    outline: none;
    width: 240px;
    transition: var(--transition);
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
  }
  .masthead-search input::placeholder { color: var(--muted); }
  .masthead-search input:focus {
    background: var(--white);
    border-color: var(--blue);
    box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    width: 280px;
  }
  .masthead-search svg {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--muted);
  }

  /* ── Filter chips ── */
  .filter-bar {
    background: var(--card-bg);
    border-bottom: 1px solid var(--border);
    position: sticky;
    top: 0;
    z-index: 50;
    backdrop-filter: blur(8px);
  }
  .filter-inner {
    max-width: 1100px;
    margin: 0 auto;
    padding: 0 24px;
    display: flex;
    gap: 8px;
    overflow-x: auto;
    scrollbar-width: none;
    padding-top: 16px;
    padding-bottom: 16px;
    align-items: center;
  }
  .filter-inner::-webkit-scrollbar { display: none; }
  .chip {
    flex-shrink: 0;
    padding: 6px 16px;
    border-radius: 100px;
    font-size: 12px;
    font-weight: 500;
    letter-spacing: .04em;
    cursor: pointer;
    border: 1px solid var(--border);
    background: transparent;
    color: var(--muted);
    transition: var(--transition);
    font-family: var(--font-body);
  }
  .chip:hover, .chip.active {
    background: var(--ink);
    color: #fff;
    border-color: var(--ink);
  }
  .chip.active { font-weight: 600; }

  /* ── Grid layout ── */
  .blog-body {
    max-width: 1100px;
    margin: 0 auto;
    padding: 56px 24px 80px;
  }

  /* Featured (first card spans 2 cols) */
  .articles-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 24px;
  }

  /* ── Card base ── */
  .article-card {
    background: var(--card-bg);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: var(--transition);
    cursor: pointer;
    position: relative;
    animation: fadeUp .5s both;
  }
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
  }
  .article-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 40px rgba(0,0,0,.1);
    border-color: rgba(0,0,0,.15);
  }
  .article-card:nth-child(1)  { animation-delay: .05s; }
  .article-card:nth-child(2)  { animation-delay: .1s;  }
  .article-card:nth-child(3)  { animation-delay: .15s; }
  .article-card:nth-child(4)  { animation-delay: .2s;  }
  .article-card:nth-child(5)  { animation-delay: .25s; }
  .article-card:nth-child(6)  { animation-delay: .3s;  }

  /* Featured card */
  .article-card.featured {
    grid-column: span 7;
  }
  .article-card.featured .card-img-wrap {
    height: 340px;
  }
  .article-card.featured .card-title {
    font-size: 26px;
  }

  /* Regular cards */
  .article-card.regular {
    grid-column: span 5;
  }

  /* Small cards (3rd row+) */
  .article-card.small {
    grid-column: span 4;
    flex-direction: row;
    max-height: 180px;
  }
  .article-card.small .card-img-wrap {
    width: 140px;
    min-width: 140px;
    height: auto;
    flex-shrink: 0;
  }
  .article-card.small .card-body { padding: 18px; }
  .article-card.small .card-excerpt { display: none; }
  .article-card.small .card-title { font-size: 15px; }
  .article-card.small .card-footer { margin-top: auto; }

  /* ── Card image ── */
  .card-img-wrap {
    width: 100%;
    height: 220px;
    overflow: hidden;
    position: relative;
    background: #e8e4dd;
  }
  .card-img-wrap img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform .6s cubic-bezier(.4,0,.2,1);
  }
  .article-card:hover .card-img-wrap img {
    transform: scale(1.05);
  }
  /* Placeholder when no image */
  .card-img-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, #e8e4dd 0%, #d4cfc7 100%);
    font-family: var(--font-display);
    font-size: 48px;
    color: rgba(0,0,0,.12);
    letter-spacing: -2px;
    font-style: italic;
  }
  .card-reading-time {
    position: absolute;
    bottom: 12px;
    right: 12px;
    background: rgba(0,0,0,.65);
    backdrop-filter: blur(4px);
    color: #fff;
    font-size: 11px;
    font-weight: 500;
    padding: 4px 10px;
    border-radius: 100px;
    letter-spacing: .04em;
  }

  /* ── Card body ── */
  .card-body {
    padding: 24px;
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
    margin-bottom: 10px;
  }
  .card-category.alt { color: var(--accent2); }
  .card-title {
    font-family: var(--font-display);
    font-weight: 700;
    font-size: 20px;
    line-height: 1.3;
    color: var(--ink);
    margin: 0 0 10px;
    transition: color var(--transition);
  }
  .article-card:hover .card-title { color: var(--accent); }
  .card-excerpt {
    font-size: 14px;
    line-height: 1.7;
    color: var(--muted);
    font-weight: 300;
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
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid var(--border);
  }
  .card-author {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .card-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--accent), var(--accent2));
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
  }
  .card-author-info { line-height: 1.3; }
  .card-author-name { font-size: 12px; font-weight: 500; color: var(--ink); }
  .card-date { font-size: 11px; color: var(--muted); }
  .card-read-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--ink);
    text-decoration: none;
    letter-spacing: .04em;
    transition: var(--transition);
    border: none;
    background: none;
    cursor: pointer;
    font-family: var(--font-body);
  }
  .card-read-btn svg { transition: transform var(--transition); }
  .article-card:hover .card-read-btn svg { transform: translateX(4px); }

  /* ── Divider row ── */
  .section-divider {
    grid-column: span 12;
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 16px 0 8px;
  }
  .section-divider span {
    font-size: 11px;
    font-weight: 700;
    letter-spacing: .18em;
    text-transform: uppercase;
    color: var(--muted);
    white-space: nowrap;
  }
  .section-divider::before, .section-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border);
  }

  /* ── Empty state ── */
  .empty-state {
    grid-column: span 12;
    text-align: center;
    padding: 80px 24px;
  }
  .empty-state-icon {
    font-size: 56px;
    margin-bottom: 20px;
    opacity: .4;
  }
  .empty-state h3 {
    font-family: var(--font-display);
    font-size: 24px;
    color: var(--ink);
    margin-bottom: 8px;
  }
  .empty-state p { font-size: 15px; color: var(--muted); }

  /* ── Newsletter banner ── */
  .newsletter-banner {
    background: linear-gradient(135deg, var(--surface-2), #e0e7ff);
    margin: 0 0 64px;
    padding: 56px 24px;
    text-align: center;
    position: relative;
    overflow: hidden;
    border-radius: var(--radius);
    max-width: 1100px;
    margin: 0 auto 64px;
  }
  .newsletter-banner::before {
    content: '';
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse 60% 100% at 50% 120%, rgba(37,99,235,0.1) 0%, transparent 70%);
  }
  .newsletter-banner h2 {
    font-family: var(--font-display);
    font-size: clamp(28px, 4vw, 44px);
    color: var(--navy);
    font-weight: 700;
    position: relative;
    z-index: 1;
  }
  .newsletter-banner p {
    font-size: 16px;
    color: var(--muted);
    margin: 12px 0 28px;
    position: relative;
    z-index: 1;
  }
  .newsletter-form {
    display: flex;
    gap: 10px;
    max-width: 440px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
  }
  .newsletter-form input {
    flex: 1;
    background: var(--white);
    border: 1px solid var(--border);
    border-radius: 100px;
    padding: 13px 22px;
    font-family: var(--font-body);
    font-size: 14px;
    color: var(--ink);
    outline: none;
    box-shadow: 0 2px 10px rgba(0,0,0,0.02);
  }
  .newsletter-form input::placeholder { color: var(--muted); }
  .newsletter-form input:focus {
      border-color: var(--blue);
      box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
  }
  .newsletter-form button {
    background: linear-gradient(135deg, var(--blue), var(--blue-dark));
    color: #fff;
    border: none;
    padding: 13px 24px;
    border-radius: 100px;
    font-family: var(--font-body);
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .04em;
    cursor: pointer;
    transition: var(--transition);
    white-space: nowrap;
  }
  .newsletter-form button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 14px rgba(37,99,235,0.3);
  }

  /* ── Load more ── */
  .load-more-wrap {
    text-align: center;
    padding: 12px 0 40px;
  }
  .load-more-btn {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 14px 32px;
    border: 1.5px solid var(--ink);
    border-radius: 100px;
    font-family: var(--font-body);
    font-size: 13px;
    font-weight: 600;
    letter-spacing: .06em;
    text-transform: uppercase;
    color: var(--ink);
    background: transparent;
    cursor: pointer;
    transition: var(--transition);
  }
  .load-more-btn:hover {
    background: var(--ink);
    color: #fff;
  }

  /* ── Responsive ── */
  @media (max-width: 900px) {
    .article-card.featured { grid-column: span 12; }
    .article-card.regular  { grid-column: span 12; }
    .article-card.small    { grid-column: span 6; flex-direction: column; max-height: none; }
    .article-card.small .card-img-wrap { width: 100%; height: 180px; }
    .article-card.small .card-excerpt { display: block; }
    .masthead-search { display: none; }
  }
  @media (max-width: 600px) {
    .article-card.small { grid-column: span 12; }
    .blog-masthead { padding: 56px 0 48px; }
    .masthead-meta { flex-wrap: wrap; }
  }
</style>

<div class="blog-root">

  {{-- ── MASTHEAD ── --}}
  <section class="blog-masthead">
    <div class="masthead-inner">
      <div class="masthead-eyebrow">
        <span></span> The Journal
      </div>
      <h1 class="masthead-title">
        Ideas worth<br><em>reading.</em>
      </h1>
      <p class="masthead-sub">
        Fresh perspectives, expert insights, and stories that help you grow — curated by our content team.
      </p>
      <div class="masthead-meta">
        <span class="masthead-count">{{ $articles instanceof \Illuminate\Pagination\LengthAwarePaginator ? $articles->total() : $articles->count() }} articles published</span>
        <div class="masthead-search">
          <input type="text" placeholder="Search articles…" id="searchInput">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
          </svg>
        </div>
      </div>
    </div>
  </section>

  {{-- ── FILTER BAR ── --}}
  <div class="filter-bar">
    <div class="filter-inner" id="filterBar">
      <button class="chip active" data-cat="all">All</button>
      @php
        $cats = $articles->pluck('category')->filter()->unique()->values();
      @endphp
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
          {{-- Featured card --}}
          <article class="article-card featured"
                   data-cat="{{ Str::slug($article->category ?? '') }}"
                   onclick="window.location='{{ route('blog.show', $article->slug) }}'">
            <div class="card-img-wrap">
              @if($article->thumbnail ?? false)
                <img src="{{ asset('storage/' . $article->thumbnail) }}" alt="{{ $article->title }}" loading="eager">
              @else
                <div class="card-img-placeholder">{{ substr($article->title,0,1) }}</div>
              @endif
              <span class="card-reading-time">
                {{ ceil(str_word_count(strip_tags($article->body ?? '')) / 200) }} min read
              </span>
            </div>
            <div class="card-body">
              <span class="card-category">{{ $article->category ?? 'Featured' }}</span>
              <h2 class="card-title">{{ $article->title }}</h2>
              <p class="card-excerpt">{{ $article->excerpt }}</p>
              <div class="card-footer">
                <div class="card-author">
                  <div class="card-avatar">{{ strtoupper(substr($article->author ?? 'A', 0, 1)) }}</div>
                  <div class="card-author-info">
                    <div class="card-author-name">{{ $article->author ?? 'Editorial Team' }}</div>
                    <div class="card-date">{{ optional($article->published_at ?? $article->created_at)->format('M d, Y') }}</div>
                  </div>
                </div>
                <a href="{{ route('blog.show', $article->slug) }}" class="card-read-btn">
                  Read article
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
              </div>
            </div>
          </article>

        @elseif($i === 1)
          {{-- Second card (regular) --}}
          <article class="article-card regular"
                   data-cat="{{ Str::slug($article->category ?? '') }}"
                   onclick="window.location='{{ route('blog.show', $article->slug) }}'">
            <div class="card-img-wrap">
              @if($article->thumbnail ?? false)
                <img src="{{ asset('storage/' . $article->thumbnail) }}" alt="{{ $article->title }}" loading="lazy">
              @else
                <div class="card-img-placeholder">{{ substr($article->title,0,1) }}</div>
              @endif
              <span class="card-reading-time">
                {{ ceil(str_word_count(strip_tags($article->body ?? '')) / 200) }} min read
              </span>
            </div>
            <div class="card-body">
              <span class="card-category alt">{{ $article->category ?? 'Guide' }}</span>
              <h2 class="card-title">{{ $article->title }}</h2>
              <p class="card-excerpt">{{ $article->excerpt }}</p>
              <div class="card-footer">
                <div class="card-author">
                  <div class="card-avatar" style="background: linear-gradient(135deg,#2a6cc8,#5b8fd4)">
                    {{ strtoupper(substr($article->author ?? 'A', 0, 1)) }}
                  </div>
                  <div class="card-author-info">
                    <div class="card-author-name">{{ $article->author ?? 'Editorial Team' }}</div>
                    <div class="card-date">{{ optional($article->published_at ?? $article->created_at)->format('M d, Y') }}</div>
                  </div>
                </div>
                <a href="{{ route('blog.show', $article->slug) }}" class="card-read-btn">
                  Read
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
              </div>
            </div>
          </article>

        @else
          {{-- Small cards from index 2 onward --}}
          @if($i === 2)
            <div class="section-divider">
              <span>More articles</span>
            </div>
          @endif

          <article class="article-card small"
                   data-cat="{{ Str::slug($article->category ?? '') }}"
                   onclick="window.location='{{ route('blog.show', $article->slug) }}'">
            <div class="card-img-wrap">
              @if($article->thumbnail ?? false)
                <img src="{{ asset('storage/' . $article->thumbnail) }}" alt="{{ $article->title }}" loading="lazy">
              @else
                <div class="card-img-placeholder" style="font-size:28px">{{ substr($article->title,0,1) }}</div>
              @endif
            </div>
            <div class="card-body">
              <span class="card-category">{{ $article->category ?? 'Tips' }}</span>
              <h2 class="card-title">{{ $article->title }}</h2>
              <p class="card-excerpt">{{ $article->excerpt }}</p>
              <div class="card-footer">
                <div class="card-date">{{ optional($article->published_at ?? $article->created_at)->format('M d, Y') }}</div>
                <span style="font-size:11px;color:var(--muted)">
                  {{ ceil(str_word_count(strip_tags($article->body ?? '')) / 200) }} min
                </span>
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

    </div>{{-- /articles-grid --}}

    {{-- Load more (show only if paginated) --}}
    @if($articles instanceof \Illuminate\Pagination\LengthAwarePaginator && $articles->hasMorePages())
      <div class="load-more-wrap" style="margin-top:48px">
        <a href="{{ $articles->nextPageUrl() }}" class="load-more-btn">
          Load more articles
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
        </a>
      </div>
    @endif

  </div>{{-- /blog-body --}}

  {{-- ── NEWSLETTER ── --}}
  <div class="newsletter-banner">
    <h2>Get articles in your inbox.</h2>
    <p>No noise. Just the best stories, once a week.</p>
    <form class="newsletter-form" onsubmit="return false;">
      <input type="email" placeholder="your@email.com">
      <button type="submit">Subscribe</button>
    </form>
  </div>

</div>{{-- /blog-root --}}

<script>
  // ── Category filter ──
  document.querySelectorAll('.chip').forEach(chip => {
    chip.addEventListener('click', () => {
      document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');

      const cat = chip.dataset.cat;
      document.querySelectorAll('.article-card').forEach(card => {
        const match = cat === 'all' || card.dataset.cat === cat || card.dataset.cat === '';
        card.style.display = match ? '' : 'none';
      });
    });
  });

  // ── Search filter ──
  const searchInput = document.getElementById('searchInput');
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      const q = searchInput.value.toLowerCase();
      document.querySelectorAll('.article-card').forEach(card => {
        const text = card.textContent.toLowerCase();
        card.style.display = text.includes(q) ? '' : 'none';
      });
    });
  }
</script>

@endsection