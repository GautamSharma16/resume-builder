@extends('layouts.app')

@section('title', 'Page Not Found | Cvbliss')

@section('content')
<section class="bg-white">
    <div class="mx-auto flex min-h-[62vh] max-w-4xl flex-col items-center justify-center px-6 py-20 text-center">
        <p class="text-sm font-bold uppercase tracking-[0.24em] text-blue-600">404</p>
        <h1 class="mt-4 text-4xl font-bold text-slate-950 sm:text-5xl">Page not found</h1>
        <p class="mt-4 max-w-2xl text-base leading-7 text-slate-600">
            The page you requested may have moved, expired, or never existed.
        </p>
        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('home') }}" class="rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white shadow-lg shadow-blue-600/20 transition hover:bg-blue-700">Go Home</a>
            <a href="{{ route('resume-maker') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Resume Maker</a>
            <a href="{{ route('templates') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Templates</a>
            <a href="{{ route('contact') }}" class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-bold text-slate-700 transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-700">Contact Support</a>
        </div>
    </div>
</section>
@endsection
