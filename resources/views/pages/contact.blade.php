@extends('layouts.app')

@section('content')

<div class="bg-[#f7f9fe] py-16">

    <div class="max-w-6xl mx-auto px-4">

        {{-- Heading --}}
        <div class="text-center max-w-2xl mx-auto mb-12">
            <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">
                Let’s talk 
            </h1>
            <p class="text-gray-600 mt-3 text-base">
                Whether you’re stuck, have feedback, or just want to say hi — we’re here to help.
            </p>
        </div>

        <div class="grid md:grid-cols-2 gap-10 items-start">

            {{-- LEFT: CONTACT FORM --}}
            <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">

                <h2 class="text-lg font-semibold text-gray-900 mb-6">
                    Send us a message
                </h2>

                <form class="space-y-5">

                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-1 block">
                            Your Name
                        </label>
                        <input type="text"
                            placeholder="e.g. Gaurav Kumar"
                            class="w-full border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 p-3 rounded-lg outline-none transition">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-1 block">
                            Email Address
                        </label>
                        <input type="email"
                            placeholder="you@example.com"
                            class="w-full border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 p-3 rounded-lg outline-none transition">
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-700 mb-1 block">
                            Message
                        </label>
                        <textarea rows="5"
                            placeholder="Tell us what you need help with..."
                            class="w-full border border-gray-200 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 p-3 rounded-lg outline-none transition"></textarea>
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700 transition shadow-sm">
                        Send Message
                    </button>

                    <p class="text-xs text-gray-500 text-center">
                        We usually reply within a few hours.
                    </p>

                </form>

            </div>

            {{-- RIGHT: CONTACT DETAILS --}}
            <div class="space-y-6">

                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <h3 class="font-semibold text-gray-900 mb-2">
                        Support
                    </h3>
                    <p class="text-gray-600 text-sm">
                        Need help with your resume or facing any issue?
                    </p>
                    <p class="mt-2 text-blue-600 font-medium">
                        support@cvbliss.in
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <h3 class="font-semibold text-gray-900 mb-2">
                        Call us
                    </h3>
                    <p class="text-gray-600 text-sm">
                        Available Mon–Sat, 10:30 AM – 6:30 PM
                    </p>
                    <p class="mt-2 text-blue-600 font-medium">
                        +91 9876543210
                    </p>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <h3 class="font-semibold text-gray-900 mb-2">
                        Location
                    </h3>
                    <p class="text-gray-600 text-sm">
                        Based in India, serving users globally 
                    </p>
                </div>

                {{-- Small trust note --}}
                <div class="text-sm text-gray-500">
                    We’re a small team building tools to help people land better jobs.
                    Your message goes directly to us — not a bot.
                </div>

            </div>

        </div>

    </div>

</div>

@endsection