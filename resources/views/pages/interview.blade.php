@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-bold text-gray-950">Interview Tips</h1>
    <p class="mt-2 text-gray-600">Fresh articles from your content team.</p>
    <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($articles as $article)
            <article class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <p class="text-xs font-semibold uppercase text-teal-700">{{ $article->category ?? 'Preparation' }}</p>
                <h2 class="text-xl font-bold text-gray-950">{{ $article->title }}</h2>
                <p class="mt-2 text-gray-600">{{ $article->excerpt }}</p>
                <div class="mt-4 text-sm leading-6 text-gray-700">{{ $article->body }}</div>
            </article>
        @empty
            <p class="text-gray-500">No articles published yet.</p>
        @endforelse
    </div>
</div>
@endsection
