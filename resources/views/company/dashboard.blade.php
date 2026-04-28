@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-bold text-gray-950">Company Dashboard</h1>
    <p class="mt-2 text-gray-600">Welcome, {{ auth()->user()->name }}. Company hiring tools can be added here.</p>
</div>
@endsection
