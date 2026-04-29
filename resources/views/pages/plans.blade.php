@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-bold text-gray-950">Plans</h1>
    @if(session('status'))<p class="mt-3 text-sm text-teal-700">{{ session('status') }}</p>@endif
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h2 class="text-2xl font-bold text-gray-950">{{ $plan->name }}</h2>
                <p class="mt-2 text-4xl font-bold">Rs. {{ number_format($plan->price_paise / 100) }}</p>
                <ul class="mt-5 space-y-2 text-sm text-gray-600">
                    <li>{{ is_null($plan->downloads_allowed) ? 'Unlimited' : $plan->downloads_allowed }} downloads</li>
                    <li>{{ $plan->duration_days }} days access</li>
                    <li>{{ $plan->ai_enabled ? 'AI features included' : 'Standard resume downloads' }}</li>
                </ul>
                @auth
                    <form method="POST" action="{{ route('plans.order', $plan) }}">
                        @csrf
                        <button class="mt-6 w-full rounded-md bg-teal-700 px-4 py-3 text-sm font-semibold text-white">
                            Pay with Razorpay
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="mt-6 block text-center rounded-md bg-teal-700 px-4 py-3 text-sm font-semibold text-white">Login / Register</a>
                @endauth
            </div>
        @endforeach
    </div>
</div>
@endsection
