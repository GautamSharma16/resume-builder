@extends('layouts.app')

@section('title', $post->title . ' - Cvbliss Blog')

@section('content')

<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@400;500;600;700;800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">

<style>
:root {
    --blue: #2563eb;
    --navy: #0b1221;
    --ink: #1e293b;
    --muted: #64748b;
    --surface: #f8fafc;
    --border: rgba(0,0,0,0.07);
    --font-display: 'DM Serif Display', serif;
    --font-body: 'Bricolage Grotesque', sans-serif;
}

/* Progress bar */
.reading-progress {
    position: fixed;
    top: 0;
    left: 0;
    height: 4px;
    background: var(--blue);
    width: 0%;
    z-index: 9999;
}

/* Layout */
.blog-post-root {
    max-width: 850px;
    margin: 0 auto;
    padding: 4rem 1.5rem;
}

/* Header */
.post-header {
    text-align: center;
    margin-bottom: 3rem;
    animation: fadeUp 0.6s ease;
}

.post-category {
    background: var(--blue);
    color: white;
    padding: 0.3rem 1rem;
    border-radius: 999px;
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 0.05em;
}

.post-title {
    font-family: var(--font-display);
    font-size: clamp(2.5rem, 6vw, 3.5rem);
    margin: 1.5rem 0;
    color: var(--navy);
}

.post-meta {
    color: var(--muted);
    font-size: 0.9rem;
}

/* Image */
.post-featured-image {
    width: 100%;
    height: 450px;
    border-radius: 2rem;
    margin-bottom: 3rem;
    background-size: cover;
    background-position: center;
    transition: transform 0.4s ease;
}
.post-featured-image:hover {
    transform: scale(1.02);
}

/* Content */
.post-body {
    font-family: var(--font-body);
    font-size: 1.15rem;
    line-height: 1.9;
    color: var(--ink);
}

.post-body h2 {
    font-family: var(--font-display);
    margin-top: 3rem;
    font-size: 2rem;
}

.post-body p {
    margin-bottom: 1.8rem;
}

/* Fade animation */
@keyframes fadeUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Share buttons */
.share-bar {
    position: fixed;
    left: 20px;
    top: 40%;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.share-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: 0.3s;
}
.share-btn:hover {
    transform: scale(1.1);
    background: var(--blue);
    color: white;
}

/* Footer */
.post-footer {
    margin-top: 5rem;
    border-top: 1px solid var(--border);
    padding-top: 3rem;
}

.popular-posts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 2rem;
}

.popular-card {
    text-decoration: none;
    color: inherit;
    transition: 0.3s;
}

.popular-card:hover {
    transform: translateY(-5px);
}

.popular-card h4 {
    font-family: var(--font-display);
    margin-top: 1rem;
}
</style>

<div class="reading-progress" id="progressBar"></div>

<div class="share-bar">
    <div class="share-btn">F</div>
    <div class="share-btn">T</div>
    <div class="share-btn">L</div>
</div>

<div class="blog-post-root">

<header class="post-header">
    <span class="post-category">{{ $post->category ?? 'Career Advice' }}</span>

    <h1 class="post-title">{{ $post->title }}</h1>

    <div class="post-meta">
        <span>{{ $post->published_at?->format('M d, Y') ?? $post->created_at->format('M d, Y') }}</span>
        <span>•</span>
        <span>{{ ceil(str_word_count(strip_tags($post->body)) / 200) }} min read</span>
    </div>
</header>

@if($post->thumbnail)
<div class="post-featured-image"
     style="background-image: url('{{ asset('storage/' . $post->thumbnail) }}')">
</div>
@endif

<div class="post-body">
    {!! $post->body !!}
</div>

<footer class="post-footer">
    <h3>More from our Blog</h3>

    <div class="popular-posts-grid">
        @foreach($popularPosts as $popular)
        <a href="{{ route('blog.show', $popular->slug) }}" class="popular-card">
            <div style="height:200px; border-radius:1rem;
                background-image:url('{{ $popular->thumbnail ? asset('storage/' . $popular->thumbnail) : 'https://placehold.co/600x400' }}');
                background-size:cover;">
            </div>
            <h4>{{ $popular->title }}</h4>
        </a>
        @endforeach
    </div>
</footer>

</div>

<script>
/* Reading progress */
window.addEventListener('scroll', () => {
    let scrollTop = window.scrollY;
    let docHeight = document.body.scrollHeight - window.innerHeight;
    let progress = (scrollTop / docHeight) * 100;
    document.getElementById('progressBar').style.width = progress + "%";
});
</script>

@endsection