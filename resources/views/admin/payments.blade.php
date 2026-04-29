@extends('layouts.admin')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <h1 class="text-3xl font-bold text-gray-950 mb-6">Pricing Control</h1>
    <div class="space-y-4">
        @foreach($plans as $plan)
            <form method="POST" action="{{ route('admin.plans.update', $plan) }}" class="bg-white border border-gray-200 rounded-lg p-5 shadow-sm grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
                @csrf
                @method('PATCH')
                <div><label class="text-xs font-semibold text-gray-500">{{ $plan->name }}</label><input name="price_paise" type="number" value="{{ $plan->price_paise }}" class="mt-1 w-full rounded-md border-gray-300"></div>
                <div><label class="text-xs font-semibold text-gray-500">Resume Limit</label><input name="resume_limit" type="number" value="{{ $plan->resume_limit }}" class="mt-1 w-full rounded-md border-gray-300"></div>
                <div><label class="text-xs font-semibold text-gray-500">Cover Letter Limit</label><input name="cover_letter_limit" type="number" value="{{ $plan->cover_letter_limit }}" class="mt-1 w-full rounded-md border-gray-300"></div>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="ai_enabled" value="1" @checked($plan->ai_enabled)> AI</label>
                <button class="rounded-md bg-teal-700 px-4 py-2 text-sm font-semibold text-white">Save</button>
            </form>
        @endforeach
    </div>
</div>
@endsection
