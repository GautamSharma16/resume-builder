@extends('layouts.app')

@section('title', $post->title . ' - Cvbliss Blog')

@section('content')

{{-- Same fonts as home page --}}
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,500;12..96,600;12..96,700;12..96,800&family=DM+Serif+Display:ital@0;1&display=swap" rel="stylesheet">

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

    .blog-post-root {
        max-width: 900px;
        margin: 0 auto;
        padding: 4rem 1.5rem;
    }

    .post-header {
        text-align: center;
        margin-bottom: 3rem;
    }

    .post-category {
        display: inline-block;
        background: var(--blue);
        color: white;
        padding: 0.25rem 1rem;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 1.5rem;
    }

    .post-title {
        font-family: var(--font-display);
        font-size: clamp(2.5rem, 6vw, 4rem);
        color: var(--navy);
        line-height: 1.1;
        margin-bottom: 1.5rem;
    }

    .post-meta {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1.5rem;
        color: var(--muted);
        font-size: 0.9rem;
    }

    .post-featured-image {
        width: 100%;
        height: 500px;
        background-size: cover;
        background-position: center;
        border-radius: 2rem;
        margin-bottom: 4rem;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.1);
    }

    .post-body {
        font-family: var(--font-body);
        font-size: 1.25rem;
        line-height: 1.8;
        color: var(--ink);
    }

    .post-body h2 {
        font-family: var(--font-display);
        font-size: 2.25rem;
        margin: 3rem 0 1.5rem;
        color: var(--navy);
    }

    .post-body p {
        margin-bottom: 2rem;
    }

    .post-footer {
        margin-top: 6rem;
        padding-top: 3rem;
        border-top: 1px solid var(--border);
    }

    .popular-posts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .popular-card {
        text-decoration: none;
        color: inherit;
    }

    .popular-card:hover h4 {
        color: var(--blue);
    }

    .popular-card h4 {
        font-family: var(--font-display);
        font-size: 1.25rem;
        margin-top: 1rem;
        transition: color 0.2s;
    }
</style>

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
        <div class="post-featured-image" style="background-image: url('{{ asset('storage/' . $post->thumbnail) }}')"></div>
    @endif

    <div class="post-body">
        {!! $post->body !!}
    </div>

    <footer class="post-footer">
        <h3 class="section-heading">More from our <em>Blog</em></h3>
        <div class="popular-posts-grid">
            @foreach($popularPosts as $popular)
                <a href="{{ route('blog.show', $popular->slug) }}" class="popular-card">
                    <div style="height: 200px; background-image: url('{{ $popular->thumbnail ? asset('storage/' . $popular->thumbnail) : 'https://placehold.co/600x400' }}'); background-size: cover; border-radius: 1rem;"></div>
                    <h4>{{ $popular->title }}</h4>
                </a>
            @endforeach
        </div>
    </footer>
</div>

@endsection
