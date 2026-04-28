@extends('layouts.app')

@section('content')
<div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white border border-gray-200 rounded-lg p-6 sm:p-8 shadow-sm">
        <h1 class="text-3xl font-bold text-gray-950">Verify OTP</h1>
        <p class="mt-2 text-gray-600">Enter the 6-digit code sent to {{ $user->email }}.</p>
        @if(session('status'))<p class="mt-4 rounded-md bg-teal-50 p-3 text-sm text-teal-800">{{ session('status') }}</p>@endif
        @if($errors->any())<p class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</p>@endif
        <form method="POST" action="{{ route('otp.verify') }}" class="mt-6 space-y-4">
            @csrf
            <input name="otp" inputmode="numeric" maxlength="6" required class="w-full rounded-md border-gray-300 text-center text-2xl font-bold tracking-[0.4em] focus:border-teal-600 focus:ring-teal-600">
            <button class="w-full rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-800">Verify</button>
        </form>
        <form method="POST" action="{{ route('otp.resend') }}" class="mt-4">
            @csrf
            <button class="w-full rounded-md border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50">Resend OTP</button>
        </form>
        <p class="mt-3 text-xs text-gray-500">You can resend once every 30 seconds. Maximum 5 verification attempts per OTP.</p>
    </div>
</div>
@endsection
