@extends('layouts.app')

@section('content')
@php
    $isAdminReset = strtolower((string) old('email', $email)) === ($adminResetEmail ?? null);
@endphp
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="grid grid-cols-1 lg:grid-cols-2 overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
        <section class="p-8 sm:p-10">
            <p class="text-sm font-semibold uppercase text-teal-700">Create new password</p>
            <h1 class="mt-2 text-3xl font-bold text-gray-950">Reset password</h1>
            <p class="mt-3 text-gray-600">Choose a strong password before returning to your dashboard.</p>
            @if($errors->any())<p class="mt-5 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</p>@endif
            <form method="POST" action="{{ route('password.store') }}" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                @if($isAdminReset)
                    <input type="hidden" name="email" value="{{ $adminResetEmail }}">
                    <input type="email" value="{{ $adminResetEmail }}" readonly class="w-full rounded-md border-gray-300 bg-gray-100 text-sm text-gray-700 focus:border-teal-600 focus:ring-teal-600" placeholder="Email">
                @else
                    <input name="email" type="email" value="{{ old('email', $email) }}" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600" placeholder="Email">
                @endif
                <input name="password" type="password" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600" placeholder="New password">
                <input name="password_confirmation" type="password" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600" placeholder="Confirm password">
                <button class="w-full rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-800">Reset password</button>
            </form>
        </section>
        <section class="bg-gray-950 p-8 sm:p-10 text-white">
            <h2 class="text-2xl font-bold">Password rules</h2>
            <ul class="mt-6 space-y-3 text-sm text-gray-300">
                <li>Minimum 8 characters.</li>
                <li>Use uppercase and lowercase letters.</li>
                <li>Include at least one number.</li>
            </ul>
        </section>
    </div>
</div>
@endsection
