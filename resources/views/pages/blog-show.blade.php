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
    background: linear-gradient(90deg, #2563eb, #7c3aed);
    width: 0%;
    z-index: 9999;
}

/* Layout */
.blog-post-root {
    max-width: 780px;
    margin: 0 auto;
    padding: 4rem 1.5rem;
}

/* Header */
.post-header {
    text-align: center;
    margin-bottom: 3rem;
}

.post-category {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: white;
    padding: 0.35rem 1rem;
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
    height: 420px;
    border-radius: 1.8rem;
    margin-bottom: 3rem;
    background-size: cover;
    background-position: center;
    transition: 0.4s ease;
}
.post-featured-image:hover {
    transform: scale(1.02);
}

/* Content */
.post-body {
    font-family: var(--font-body);
    font-size: 1.1rem;
    line-height: 1.9;
    color: var(--ink);
}

.post-body h2 {
    font-family: var(--font-display);
    font-size: 2rem;
    margin-top: 3rem;
}

.post-body h3 {
    margin-top: 2rem;
    font-weight: 600;
}

.post-body p {
    margin-bottom: 1.6rem;
}

.post-body ul {
    padding-left: 1.5rem;
    margin-bottom: 1.5rem;
}

.post-body li {
    margin-bottom: 0.5rem;
}

/* Quote */
.post-body blockquote {
    border-left: 4px solid var(--blue);
    padding: 1rem 1.5rem;
    background: #f1f5f9;
    border-radius: 0.5rem;
    font-style: italic;
    margin: 2rem 0;
}

/* Code */
.post-body pre {
    background: #0f172a;
    color: #e2e8f0;
    padding: 1rem;
    border-radius: 0.5rem;
    overflow-x: auto;
}

/* SHARE BAR */
.share-bar {
    position: fixed;
    left: 20px;
    top: 40%;
    display: flex;
    flex-direction: column;
    gap: 12px;
    z-index: 1000;
}

.share-btn {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    background: rgba(255,255,255,0.9);
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: 0.3s ease;
}

.share-btn svg {
    width: 18px;
    height: 18px;
    fill: var(--navy);
}

.share-btn:hover {
    transform: translateY(-3px) scale(1.1);
    background: var(--blue);
}

.share-btn:hover svg {
    fill: white;
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
    transform: translateY(-6px);
}

.popular-card h4 {
    font-family: var(--font-display);
    margin-top: 1rem;
}

/* Mobile fix */
@media(max-width: 768px) {
    .share-bar {
        display: none;
    }
}
</style>

<div class="reading-progress" id="progressBar"></div>

<!-- SHARE BAR -->
<div class="share-bar">
    <!-- Facebook -->
    <a href="https://www.facebook.com/sharer/sharer.php?u={{ url()->current() }}" target="_blank" class="share-btn">
        <svg viewBox="0 0 24 24">
            <path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9v-2.9h2.5V9.8c0-2.5 1.5-3.8 3.7-3.8 1.1 0 2.2.2 2.2.2v2.4h-1.3c-1.3 0-1.7.8-1.7 1.6v2h2.9l-.5 2.9h-2.4v7A10 10 0 0 0 22 12"/>
        </svg>
    </a>

    <!-- Twitter -->
    <a href="https://twitter.com/intent/tweet?url={{ url()->current() }}" target="_blank" class="share-btn">
        <svg viewBox="0 0 24 24">
            <path d="M22 5.9c-.8.4-1.6.6-2.5.8a4.2 4.2 0 0 0 1.8-2.3 8.3 8.3 0 0 1-2.6 1 4.2 4.2 0 0 0-7.2 3.8A12 12 0 0 1 3 4.8a4.2 4.2 0 0 0 1.3 5.6 4 4 0 0 1-1.9-.5v.1a4.2 4.2 0 0 0 3.4 4.1 4.2 4.2 0 0 1-1.9.1 4.2 4.2 0 0 0 3.9 2.9A8.4 8.4 0 0 1 2 19.5a11.8 11.8 0 0 0 6.4 1.9c7.7 0 12-6.4 12-12v-.6c.8-.6 1.5-1.3 2-2.1"/>
        </svg>
    </a>

    <!-- LinkedIn -->
    <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ url()->current() }}" target="_blank" class="share-btn">
        <svg viewBox="0 0 24 24">
            <path d="M20.4 20.4h-3.6v-5.6c0-1.3-.5-2.2-1.7-2.2-.9 0-1.5.6-1.7 1.1-.1.2-.1.5-.1.9v5.8H9.7s.1-9.4 0-10.4h3.6v1.5c.5-.8 1.3-1.9 3.2-1.9 2.3 0 4 1.5 4 4.8v6zM6 8.4a2.1 2.1 0 1 1 0-4.2 2.1 2.1 0 0 1 0 4.2zM7.8 20.4H4.2V10h3.6v10.4z"/>
        </svg>
    </a>
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
window.addEventListener('scroll', () => {
    let scrollTop = window.scrollY;
    let docHeight = document.body.scrollHeight - window.innerHeight;
    let progress = (scrollTop / docHeight) * 100;
    document.getElementById('progressBar').style.width = progress + "%";
});
</script>

@endsection