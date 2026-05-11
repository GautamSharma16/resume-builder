@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-950">Articles</h1>
            <p class="mt-1 text-sm text-gray-500">Create and publish content shown in the Interview section.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.articles.create') }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                New article
            </a>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                        Total: {{ $articles->count() }}
                    </span>
                </div>

                <div class="relative w-full md:max-w-sm">
                    <input id="article-search"
                           type="search"
                           placeholder="Search articles..."
                           class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2 pl-10 text-sm focus:border-teal-500 focus:ring-teal-500">
                    <svg class="pointer-events-none absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35m1.85-5.15a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        {{-- Desktop table --}}
        <div class="hidden md:block">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Article</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($articles as $article)
                            @php
                                $statusClass = $article->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700';
                            @endphp
                            <tr class="article-row hover:bg-gray-50 transition"
                                data-name="{{ strtolower(($article->title ?? '').' '.($article->category ?? '').' '.($article->excerpt ?? '')) }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 flex items-center justify-center text-xs text-gray-500 shrink-0">
                                            @if($article->thumbnail)
                                                <img src="{{ asset('storage/' . $article->thumbnail) }}" alt="Thumbnail" class="w-full h-full object-cover">
                                            @else
                                                No image
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="font-semibold text-gray-900 truncate">{{ $article->title }}</div>
                                            @if($article->excerpt)
                                                <div class="mt-0.5 text-sm text-gray-500 line-clamp-1">{{ $article->excerpt }}</div>
                                            @endif
                                            <div class="mt-1 text-xs text-gray-400">
                                                Updated: {{ optional($article->updated_at)->format('M d, Y') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $article->category ?: '—' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                                        {{ $article->is_published ? 'Published' : 'Draft' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.articles.edit', $article) }}"
                                           class="inline-flex items-center gap-1 rounded-lg bg-teal-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-800">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-14 text-center">
                                    <div class="text-sm font-semibold text-gray-800">No articles yet.</div>
                                    <div class="mt-1 text-sm text-gray-500">Create your first article to publish it in the Interview section.</div>
                                    <div class="mt-4">
                                        <a href="{{ route('admin.articles.create') }}"
                                           class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">
                                            New article
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse($articles as $article)
                @php
                    $statusClass = $article->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700';
                @endphp
                <div class="article-card p-4"
                     data-name="{{ strtolower(($article->title ?? '').' '.($article->category ?? '').' '.($article->excerpt ?? '')) }}">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 rounded-xl overflow-hidden bg-gray-100 border border-gray-200 flex items-center justify-center text-xs text-gray-500 shrink-0">
                            @if($article->thumbnail)
                                <img src="{{ asset('storage/' . $article->thumbnail) }}" alt="Thumbnail" class="w-full h-full object-cover">
                            @else
                                No image
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="font-semibold text-gray-900 truncate">{{ $article->title }}</div>
                            <div class="mt-1 flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                                    {{ $article->is_published ? 'Published' : 'Draft' }}
                                </span>
                                @if($article->category)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                        {{ $article->category }}
                                    </span>
                                @endif
                            </div>
                            @if($article->excerpt)
                                <div class="mt-2 text-sm text-gray-500 line-clamp-2">{{ $article->excerpt }}</div>
                            @endif
                            <div class="mt-3">
                                <a href="{{ route('admin.articles.edit', $article) }}"
                                   class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-800">
                                    Edit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center">
                    <div class="text-sm font-semibold text-gray-800">No articles yet.</div>
                    <div class="mt-1 text-sm text-gray-500">Create your first article.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
(() => {
    const input = document.getElementById('article-search');
    if (!input) return;

    function filter(q) {
        const query = String(q || '').trim().toLowerCase();
        document.querySelectorAll('.article-row, .article-card').forEach(el => {
            const hay = el.getAttribute('data-name') || '';
            el.style.display = hay.includes(query) ? '' : 'none';
        });
    }

    input.addEventListener('input', (e) => filter(e.target.value));
})();
</script>
@endsection
