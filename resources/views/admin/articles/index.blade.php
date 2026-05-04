@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-950">Articles</h1>
        <a href="{{ route('admin.articles.create') }}" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white">New Article</a>
    </div>
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <tbody class="divide-y divide-gray-100">
                @foreach($articles as $article)
                    <tr>
                        <td class="px-5 py-4">
                            @if($article->thumbnail)
                                <img src="{{ asset('storage/' . $article->thumbnail) }}" alt="Thumbnail" class="w-16 h-16 object-cover rounded">
                            @else
                                <div class="w-16 h-16 bg-gray-200 rounded flex items-center justify-center text-gray-500">No Image</div>
                            @endif
                        </td>
                        <td class="px-5 py-4 font-semibold">{{ $article->title }}</td>
                        <td class="px-5 py-4">{{ $article->is_published ? 'Published' : 'Draft' }}</td>
                        <td class="px-5 py-4"><a class="text-teal-700 font-semibold" href="{{ route('admin.articles.edit', $article) }}">Edit</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
