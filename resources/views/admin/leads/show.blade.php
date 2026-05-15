@extends('layouts.admin')

@section('content')
<div class="p-6 lg:p-8">
    <a href="{{ route('admin.leads.index') }}" class="text-sm font-semibold text-blue-600">Back to leads</a>
    <div class="mt-5 max-w-3xl rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 border-b border-slate-100 pb-5 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-950">{{ $lead->name }}</h1>
                <p class="mt-1 text-sm text-slate-500">{{ $lead->created_at?->format('M d, Y h:i A') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.leads.destroy', $lead) }}" onsubmit="return confirm('Delete this lead?')">
                @csrf
                @method('DELETE')
                <button class="rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50">Delete</button>
            </form>
        </div>

        <dl class="mt-5 grid gap-4 sm:grid-cols-2">
            <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Email</dt><dd class="mt-1 text-slate-900">{{ $lead->email }}</dd></div>
            <div><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Phone</dt><dd class="mt-1 text-slate-900">{{ $lead->mobile ?: 'Not provided' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Subject</dt><dd class="mt-1 text-slate-900">{{ $lead->subject ?: 'General inquiry' }}</dd></div>
            <div class="sm:col-span-2"><dt class="text-xs font-bold uppercase tracking-wider text-slate-400">Message</dt><dd class="mt-2 whitespace-pre-line rounded-lg bg-slate-50 p-4 leading-7 text-slate-800">{{ $lead->message }}</dd></div>
        </dl>
    </div>
</div>
@endsection
