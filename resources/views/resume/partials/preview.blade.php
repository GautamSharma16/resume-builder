<header class="border-b-2 border-gray-950 pb-4">
    <h1 class="text-3xl font-bold uppercase text-gray-950">{{ $resume['name'] ?? 'Your Name' }}</h1>
    <p class="mt-2 text-sm text-gray-600">{{ collect([$resume['email'] ?? null, $resume['mobile'] ?? $resume['contact'] ?? null, $resume['location'] ?? $resume['address'] ?? null])->filter()->join(' | ') }}</p>
    <p class="text-sm text-gray-600">{{ collect($resume['social_links'] ?? [])->filter()->join(' | ') }}</p>
</header>
<section class="mt-6"><h2 class="text-xs font-bold uppercase text-teal-700">Summary</h2><p class="mt-2 text-sm leading-6 text-gray-700">{{ $resume['summary'] ?? '' }}</p></section>
<section class="mt-6"><h2 class="text-xs font-bold uppercase text-teal-700">Skills</h2><p class="mt-2 text-sm text-gray-700">{{ implode(', ', $resume['skills'] ?? []) }}</p></section>
<section class="mt-6">
    <h2 class="text-xs font-bold uppercase text-teal-700">Experience</h2>
    @foreach(($resume['experience'] ?? []) as $item)
        <div class="mt-3">
            <h3 class="font-bold text-gray-950">{{ $item['role'] ?? '' }}</h3>
            <p class="text-sm text-gray-500">{{ $item['company'] ?? '' }}</p>
            <ul class="mt-2 list-disc pl-5 text-sm text-gray-700">@foreach(($item['points'] ?? []) as $point)<li>{{ $point }}</li>@endforeach</ul>
        </div>
    @endforeach
</section>
<section class="mt-6"><h2 class="text-xs font-bold uppercase text-teal-700">Education</h2><ul class="mt-2 list-disc pl-5 text-sm text-gray-700">@foreach(($resume['education'] ?? []) as $item)<li>{{ $item }}</li>@endforeach</ul></section>

<section class="mt-6"><h2 class="text-xs font-bold uppercase text-teal-700">Projects</h2><ul class="mt-2 list-disc pl-5 text-sm text-gray-700">@foreach(($resume['projects'] ?? []) as $item)<li>{{ $item }}</li>@endforeach</ul></section>
