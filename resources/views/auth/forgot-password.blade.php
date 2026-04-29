@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-2 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <section class="p-8 sm:p-10">
            <p class="text-sm font-semibold uppercase text-teal-700">Account recovery</p>
            <h1 class="mt-2 text-3xl font-bold text-gray-950">Forgot password</h1>
            <p class="mt-3 text-gray-600">Enter your registered email and we will send a secure reset link if the account exists.</p>
            @if(session('status'))<p class="mt-5 rounded-md bg-teal-50 p-3 text-sm text-teal-800">{{ session('status') }}</p>@endif
            @if($errors->any())<p class="mt-5 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</p>@endif
            <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-semibold text-gray-800">Email address</label>
                    <input name="email" type="email" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600" placeholder="you@example.com">
                </div>
                <button class="w-full rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-800">Send reset link</button>
            </form>
            <a href="{{ route('login') }}" class="mt-5 inline-flex text-sm font-semibold text-teal-700">Back to login</a>
        </section>
        <section class="bg-gray-950 p-8 sm:p-10 text-white">
            <h2 class="text-2xl font-bold">Secure by default</h2>
            <div class="mt-6 space-y-4 text-sm text-gray-300">
                <p>Reset links are sent only to verified email addresses and expire automatically.</p>
                <p>Use a strong password with uppercase, lowercase, and numbers for your SaaS account.</p>
                <p>Staff accounts should reset from the same email used for Secure staff login.</p>
            </div>
        </section>
    </div>
</div>
@endsection
