@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6 flex items-start justify-between gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-950">New Article</h1>
            <p class="mt-1 text-sm text-gray-500">Draft your content and publish when ready.</p>
        </div>
        <a href="{{ route('admin.articles.index') }}" class="text-sm font-semibold text-gray-600 hover:text-gray-900">Back</a>
    </div>

    <form method="POST" action="{{ route('admin.articles.store') }}" enctype="multipart/form-data" class="bg-white border border-gray-200 rounded-2xl p-6 shadow-sm space-y-4">
        @csrf
        @include('admin.articles.form')
    </form>
</div>
@endsection
