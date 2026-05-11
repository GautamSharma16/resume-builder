@extends('layouts.admin')

@section('title', 'Pricing Plans - Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Pricing Control</h1>
        <p class="mt-1 text-sm text-gray-500">Manage subscription plans and features.</p>
    </div>

    @if(session('status'))
    <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
        {{ session('status') }}
    </div>
    @endif

    <div class="grid grid-cols-1 gap-6">
        @foreach($plans as $plan)
        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="font-bold text-gray-900 text-lg">{{ $plan->name }} Plan</h3>
                    <p class="text-xs text-gray-500">Update price and limits for this plan.</p>
                </div>
                <span class="text-xs font-mono text-gray-400 uppercase tracking-widest">ID: {{ $plan->slug }}</span>
            </div>
            
            <form action="{{ route('admin.plans.update', $plan) }}" method="POST" class="p-6">
                @csrf
                @method('PATCH')
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Display Name</label>
                        <input type="text" name="name" value="{{ old('name', $plan->name) }}" required
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Price (in Rupees ₹)</label>
                        <input type="number" step="0.01" name="price_rupees" value="{{ old('price_rupees', $plan->price_paise / 100) }}" required
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Duration (Days)</label>
                        <input type="number" name="duration_days" value="{{ old('duration_days', $plan->duration_days) }}" required
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500 transition">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Resume Limit</label>
                        <input type="number" name="resume_limit" value="{{ old('resume_limit', $plan->resume_limit) }}"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500 transition">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Downloads Allowed</label>
                        <input type="number" name="downloads_allowed" value="{{ old('downloads_allowed', $plan->downloads_allowed) }}"
                            class="w-full rounded-xl border-gray-200 bg-gray-50 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-blue-500 transition">
                    </div>
                    <div class="flex items-end pb-2">
                        <div class="bg-blue-50 text-blue-700 px-4 py-2 rounded-xl text-sm font-medium flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            AI Features & Unlimited Cover Letters Included
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-100">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-2.5 rounded-xl shadow-sm hover:shadow transition-all transform hover:-translate-y-0.5">
                        Update {{ $plan->name }} Plan
                    </button>
                </div>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endsection
