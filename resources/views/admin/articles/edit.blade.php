@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-950">Edit Article</h1>
            <p class="mt-1 text-sm text-gray-500">
                Status:
                <span class="font-semibold {{ $article->is_published ? 'text-emerald-700' : 'text-gray-700' }}">
                    {{ $article->is_published ? 'Published' : 'Draft' }}
                </span>
            </p>
        </div>
        <a href="{{ route('admin.articles.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">Back</a>
    </div>

    <form method="POST" action="{{ route('admin.articles.update', $article) }}" enctype="multipart/form-data" class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
        @csrf
        @method('PATCH')
        @include('admin.articles.form')
    </form>
</div>
@endsection
