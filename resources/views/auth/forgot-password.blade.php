@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white border border-gray-200 rounded-lg p-6 sm:p-8 shadow-sm">
        <h1 class="text-3xl font-bold text-gray-950">Forgot password</h1>
        <p class="mt-2 text-gray-600">Enter your email and we will send a password reset link.</p>
        @if(session('status'))<p class="mt-4 rounded-md bg-teal-50 p-3 text-sm text-teal-800">{{ session('status') }}</p>@endif
        @if($errors->any())<p class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</p>@endif
        <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
            @csrf
            <input name="email" type="email" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600" placeholder="you@example.com">
            <button class="w-full rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-800">Send reset link</button>
        </form>
    </div>
</div>
@endsection
