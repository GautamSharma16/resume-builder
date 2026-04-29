@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-950">Admin Dashboard</h1>
    <div class="mt-8 grid grid-cols-1 md:grid-cols-4 gap-5">
        @foreach([
            'Total Users' => $totalUsers ?? 0,
            'Total Resumes' => $totalResumes ?? 0,
            'Total Purchases' => $totalPurchases ?? 0,
            'Total Visitors' => $totalVisitors ?? 0,
        ] as $label => $value)
            <div class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm">
                <p class="text-sm font-semibold text-gray-600">{{ $label }}</p>
                <p class="mt-2 text-3xl font-bold text-gray-950">{{ $value }}</p>
            </div>
        @endforeach
    </div>
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-5">
        @if(auth()->user()->hasRole(['admin','super_admin','developer','dev']))
            <a href="{{ route('admin.templates.index') }}" class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm font-semibold">Manage Templates</a>
            <a href="{{ route('admin.payments') }}" class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm font-semibold">Pricing Control</a>
        @endif
        @if(auth()->user()->hasRole(['admin','super_admin','seo','article','article_writer']))
            <a href="{{ route('admin.articles.index') }}" class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm font-semibold">Manage Articles</a>
        @endif
    </div>
</div>
@endsection
