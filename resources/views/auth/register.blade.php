@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="bg-white border border-gray-200 rounded-lg p-6 sm:p-8 shadow-sm">
        <p class="text-sm font-semibold uppercase text-teal-700">Create account</p>
        <h1 class="mt-2 text-3xl font-bold text-gray-950">Start building better resumes</h1>
        @if($errors->any())<p class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</p>@endif

        <a href="{{ route('auth.google') }}" class="mt-6 flex w-full items-center justify-center gap-3 rounded-md border border-gray-300 px-4 py-3 text-sm font-semibold text-gray-800 hover:bg-gray-50">
            <span class="text-lg font-bold">G</span> Sign up with Google
        </a>

        <form method="POST" action="{{ route('register.store') }}" class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-800 mb-1">Name</label>
                <input name="name" value="{{ old('name') }}" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Email</label>
                <input name="email" type="email" value="{{ old('email') }}" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Mobile</label>
                <input name="mobile" value="{{ old('mobile') }}" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Password</label>
                <input name="password" type="password" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            </div>
            <div>
                <label class="block text-sm font-semibold text-gray-800 mb-1">Confirm Password</label>
                <input name="password_confirmation" type="password" required class="w-full rounded-md border-gray-300 text-sm focus:border-teal-600 focus:ring-teal-600">
            </div>
            <button class="md:col-span-2 rounded-md bg-teal-700 px-5 py-3 text-sm font-semibold text-white hover:bg-teal-800">Register and Send OTP</button>
        </form>

        <p class="mt-5 text-sm text-gray-600">Already registered? <a href="{{ route('login') }}" class="font-semibold text-teal-700">Login</a></p>
    </div>
</div>
@endsection
