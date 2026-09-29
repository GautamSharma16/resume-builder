@extends('layouts.app')

@section('title', 'Career Resources | Resume and Job Search')
@section('meta_description', 'Explore practical career resources for resumes, cover letters, ATS preparation, job searches, and interview readiness.')

@push('structured-data')
    @php
        $resourceSchema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'CollectionPage',
                    '@id' => route('resources'),
                    'name' => 'Career Resources',
                    'description' => 'Career resources for resumes, cover letters, job searches, and interviews.',
                    'url' => route('resources'),
                    'isPartOf' => ['@type' => 'WebSite', 'name' => 'CvBliss', 'url' => route('home')],
                ],
                [
                    '@type' => 'BreadcrumbList',
                    'itemListElement' => [
                        ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')],
                        ['@type' => 'ListItem', 'position' => 2, 'name' => 'Career Resources', 'item' => route('resources')],
                    ],
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($resourceSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
<main class="bg-white">
    <section class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:py-20">
        <p class="text-sm font-semibold uppercase tracking-wide text-blue-700">CvBliss Resource Hub</p>
        <h1 class="mt-3 max-w-3xl text-4xl font-bold text-slate-950 sm:text-5xl">Career resources for stronger job applications</h1>
        <p class="mt-5 max-w-3xl text-lg leading-8 text-slate-600">Use these focused resources to create a clearer resume, write a tailored cover letter, prepare for interviews, and make each job search step more intentional.</p>
    </section>

    <section class="border-y border-slate-200 bg-slate-50">
        <div class="mx-auto grid max-w-5xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-2">
            <article>
                <h2 class="text-2xl font-bold text-slate-950">Build an ATS-friendly resume</h2>
                <p class="mt-3 leading-7 text-slate-600">Choose a clear layout, focus your experience on measurable outcomes, and check that your most relevant skills are easy to find.</p>
                <div class="mt-5 flex flex-wrap gap-x-5 gap-y-3 text-sm font-semibold">
                    <a class="text-blue-700 underline" href="{{ route('resume.create') }}">Create a resume</a>
                    <a class="text-blue-700 underline" href="{{ route('templates') }}">Browse resume templates</a>
                    <a class="text-blue-700 underline" href="{{ route('enhance-cv') }}">Check ATS readiness</a>
                </div>
            </article>
            <article>
                <h2 class="text-2xl font-bold text-slate-950">Write a tailored cover letter</h2>
                <p class="mt-3 leading-7 text-slate-600">Connect your achievements to the employer's needs, use the job description thoughtfully, and keep the letter focused on the role.</p>
                <div class="mt-5 text-sm font-semibold">
                    <a class="text-blue-700 underline" href="{{ route('cover-letter') }}">Use the cover letter generator</a>
                </div>
            </article>
        </div>
    </section>

    <section id="job-search-tips" class="mx-auto max-w-5xl px-4 py-16 sm:px-6">
        <h2 class="text-3xl font-bold text-slate-950">Job search and interview guidance</h2>
        <p class="mt-4 max-w-3xl leading-7 text-slate-600">A better application is only one part of the process. Use practical advice to research roles, prepare thoughtful examples, and approach interviews with confidence.</p>
        <div class="mt-10 grid gap-8 md:grid-cols-3">
            <article>
                <h3 class="text-lg font-semibold text-slate-950">Job search tips</h3>
                <p class="mt-2 leading-7 text-slate-600">Prioritize roles where your recent experience and strengths clearly solve the employer's stated needs.</p>
            </article>
            <article>
                <h3 class="text-lg font-semibold text-slate-950">Interview preparation</h3>
                <p class="mt-2 leading-7 text-slate-600">Prepare concise examples that explain the situation, the action you took, and the result you achieved.</p>
            </article>
            <article>
                <h3 class="text-lg font-semibold text-slate-950">Career articles</h3>
                <p class="mt-2 leading-7 text-slate-600">Read current CvBliss articles for practical guidance that supports each stage of your job search.</p>
                <a class="mt-4 inline-block text-sm font-semibold text-blue-700 underline" href="{{ route('interview') }}">Visit the career blog</a>
            </article>
        </div>
        <p class="mt-10 text-sm text-slate-600">Need help with a CvBliss tool? <a class="font-semibold text-blue-700 underline" href="{{ route('contact') }}">Contact career support</a>.</p>
    </section>
</main>
@endsection
