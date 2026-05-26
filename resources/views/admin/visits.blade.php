@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 py-8">
        <div class="sm:flex sm:items-end sm:justify-between">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[0.24em] text-blue-600">Analytics</p>
                <h1 class="mt-3 text-3xl font-bold text-gray-900">Website Visits</h1>
                <p class="mt-2 text-sm text-gray-600">Unique page traffic grouped from visitor logs.</p>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="mt-4 inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800 sm:mt-0">Back to dashboard</a>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-2">
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-600">Total Visitors</p>
                <p class="mt-4 text-4xl font-black text-gray-950">{{ number_format($totalVisitors ?? 0) }}</p>
                <p class="mt-3 text-sm leading-6 text-gray-500">Overall unique visitors, counted once per visitor identity across all tracked pages.</p>
            </div>
            <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Today Visitors</p>
                <p class="mt-4 text-4xl font-black text-gray-950">{{ number_format($todayVisitors ?? 0) }}</p>
                <p class="mt-3 text-sm leading-6 text-gray-500">Unique visitors seen today only. Refreshes, quick repeats, assets and API calls are ignored.</p>
            </div>
        </div>

        <div class="mt-5 rounded-2xl border border-blue-100 bg-blue-50/70 p-5 text-sm leading-6 text-slate-600">
            <span class="font-semibold text-slate-900">How visitor counting works:</span>
            Cvbliss stores a persistent visitor cookie and a hashed visitor identity. A visitor creates only one row per page, so refreshes, repeated same-session visits, assets, API calls, and admin pages update the last visit timestamp instead of inflating totals.
        </div>

        <div class="mt-8 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">Page</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">Tracked Page Rows</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">Unique Visitors</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-700">Last Visit</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($visits as $visit)
                            <tr>
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">
                                    <span class="break-all">{{ $visit->path }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($visit->visits_count) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ number_format($visit->unique_visitors_count) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $visit->last_visited_at ? \Illuminate\Support\Carbon::parse($visit->last_visited_at)->format('d M Y, h:i A') : '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-sm text-gray-500">No visits have been recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($visits->hasPages())
            <div class="mt-6">
                {{ $visits->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
