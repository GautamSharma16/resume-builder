@extends('layouts.admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    @php
        $filterType = $filterType ?? request('type');
        $filterType = in_array($filterType, ['resume', 'cover_letter'], true) ? $filterType : null;
        $pageTitle = $filterType === 'cover_letter' ? 'Cover Letter Templates' : ($filterType === 'resume' ? 'Resume Templates' : 'Templates');
        $createUrl = $filterType ? route('admin.templates.create', ['type' => $filterType]) : route('admin.templates.create');
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-950">{{ $pageTitle }}</h1>
            <p class="mt-1 text-sm text-gray-500">
                Upload templates here. Users will see them in the template gallery and builders.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ $createUrl }}"
               class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-teal-800">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                </svg>
                New template
            </a>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">
                        Total: {{ $templates->count() }}
                    </span>
                    <div class="hidden sm:flex items-center gap-2">
                        <a href="{{ route('admin.templates.index') }}"
                           class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold border {{ $filterType === null ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                            All
                        </a>
                        <a href="{{ route('admin.templates.index', ['type' => 'resume']) }}"
                           class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold border {{ $filterType === 'resume' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                            Resume
                        </a>
                        <a href="{{ route('admin.templates.index', ['type' => 'cover_letter']) }}"
                           class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold border {{ $filterType === 'cover_letter' ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700 border-gray-200 hover:bg-gray-50' }}">
                            Cover letter
                        </a>
                    </div>
                </div>

                <div class="relative w-full md:max-w-sm">
                    <input id="template-search"
                           type="search"
                           placeholder="Search templates..."
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
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @forelse($templates as $template)
                            @php
                                $statusClass = $template->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700';
                                $typeClass = $template->type === 'cover_letter' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800';
                            @endphp
                            <tr class="template-row hover:bg-gray-50 transition"
                                data-name="{{ strtolower($template->name.' '.$template->category.' '.$template->type) }}">
                                <td class="px-6 py-4">
                                    <div class="font-semibold text-gray-900">{{ $template->name }}</div>
                                    <div class="mt-0.5 text-xs text-gray-500 font-mono">{{ $template->slug }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $typeClass }}">
                                        {{ $template->type === 'cover_letter' ? 'Cover letter' : 'Resume' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700">
                                    {{ $template->category }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.templates.preview', $template) }}"
                                           target="_blank"
                                           class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                            Preview
                                        </a>
                                        <a href="{{ route('admin.templates.edit', $template) }}"
                                           class="inline-flex items-center gap-1 rounded-lg bg-teal-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-800">
                                            Edit
                                        </a>
                                        <form action="{{ route('admin.templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this template?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-white px-3 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-14 text-center">
                                    <div class="text-sm font-semibold text-gray-800">No templates found.</div>
                                    <div class="mt-1 text-sm text-gray-500">Create your first template to make it available in the builders.</div>
                                    <div class="mt-4">
                                        <a href="{{ $createUrl }}"
                                           class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-teal-800">
                                            New template
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
            @forelse($templates as $template)
                @php
                    $statusClass = $template->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-gray-100 text-gray-700';
                    $typeClass = $template->type === 'cover_letter' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800';
                @endphp
                <div class="template-card p-4"
                     data-name="{{ strtolower($template->name.' '.$template->category.' '.$template->type) }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="font-semibold text-gray-900 truncate">{{ $template->name }}</div>
                            <div class="mt-1 flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $typeClass }}">
                                    {{ $template->type === 'cover_letter' ? 'Cover letter' : 'Resume' }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">
                                    {{ $template->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                Category: <span class="font-medium text-gray-700">{{ $template->category }}</span>
                            </div>
                        </div>
                        <div class="flex flex-col gap-2 shrink-0">
                            <a href="{{ route('admin.templates.preview', $template) }}" target="_blank"
                               class="inline-flex items-center justify-center rounded-lg border border-gray-200 bg-white px-3 py-2 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                                Preview
                            </a>
                            <a href="{{ route('admin.templates.edit', $template) }}"
                               class="inline-flex items-center justify-center rounded-lg bg-teal-700 px-3 py-2 text-xs font-semibold text-white hover:bg-teal-800">
                                Edit
                            </a>
                            <form action="{{ route('admin.templates.destroy', $template) }}" method="POST" onsubmit="return confirm('Are you sure?');" class="block w-full">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-semibold text-red-600 hover:bg-red-50">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-6 text-center">
                    <div class="text-sm font-semibold text-gray-800">No templates found.</div>
                    <div class="mt-1 text-sm text-gray-500">Create your first template.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
(() => {
    const input = document.getElementById('template-search');
    if (!input) return;

    function filter(q) {
        const query = String(q || '').trim().toLowerCase();
        document.querySelectorAll('.template-row, .template-card').forEach(el => {
            const hay = el.getAttribute('data-name') || '';
            el.style.display = hay.includes(query) ? '' : 'none';
        });
    }

    input.addEventListener('input', (e) => filter(e.target.value));
})();
</script>
@endsection
