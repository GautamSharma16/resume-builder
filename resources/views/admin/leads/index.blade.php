@extends('layouts.admin')

@section('content')
<div class="p-6 lg:p-8">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-widest text-blue-600">Lead Management</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-950">Contact Leads</h1>
            <p class="mt-1 text-sm text-slate-500">Messages submitted from the public contact form.</p>
        </div>
        <form method="GET" class="flex w-full gap-2 sm:w-auto">
            <input name="q" value="{{ $search }}" placeholder="Search leads" class="min-w-0 flex-1 rounded-lg border-slate-300 text-sm focus:border-blue-600 focus:ring-blue-600 sm:w-72">
            <button class="rounded-lg bg-slate-950 px-4 py-2 text-sm font-semibold text-white">Search</button>
        </form>
    </div>

    @if(session('status'))
        <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">{{ session('status') }}</div>
    @endif

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Lead</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Subject</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-slate-500">Received</th>
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-slate-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($leads as $lead)
                        <tr>
                            <td class="px-4 py-4">
                                <div class="font-semibold text-slate-900">{{ $lead->name }}</div>
                                <div class="text-sm text-slate-500">{{ $lead->email }} @if($lead->mobile) · {{ $lead->mobile }} @endif</div>
                            </td>
                            <td class="px-4 py-4 text-sm text-slate-700">{{ $lead->subject ?: 'General inquiry' }}</td>
                            <td class="px-4 py-4 text-sm text-slate-500">{{ $lead->created_at?->format('M d, Y h:i A') }}</td>
                            <td class="px-4 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.leads.show', $lead) }}" class="rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">View</a>
                                    <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}" onsubmit="return confirm('Delete this lead?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-sm text-slate-500">No leads found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 px-4 py-3">{{ $leads->links() }}</div>
    </div>
</div>
@endsection
