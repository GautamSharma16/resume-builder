@extends('layouts.admin')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-950 mb-6">Edit Template</h1>
    <form method="POST" action="{{ route('admin.templates.update', $template) }}" class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm space-y-4">
        @csrf
        @method('PATCH')
        @include('admin.templates.form')
    </form>
</div>
@endsection
