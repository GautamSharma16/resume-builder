@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-bold text-gray-950">Plans</h1>
    @if(session('status'))<p class="mt-3 text-sm text-teal-700">{{ session('status') }}</p>@endif
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
            <div class="bg-white border border-gray-200 rounded-lg p-6 shadow-sm">
                <h2 class="text-2xl font-bold text-gray-950">{{ $plan->name }}</h2>
                <p class="mt-2 text-4xl font-bold">₹{{ number_format($plan->price_paise / 100) }}</p>
                <ul class="mt-5 space-y-2 text-sm text-gray-600">
                    <li>{{ $plan->resume_limit ?: 'Unlimited' }} Resume{{ $plan->resume_limit === 1 ? '' : 's' }}</li>
                    <li>{{ $plan->cover_letter_limit ?: 'Unlimited' }} Cover Letters</li>
                    <li>{{ $plan->ai_enabled ? 'AI enabled' : 'No AI' }}</li>
                </ul>
                @auth
                    <button
                        class="buy-plan mt-6 w-full rounded-md bg-teal-700 px-4 py-3 text-sm font-semibold text-white"
                        data-order-url="{{ route('plans.order', $plan) }}"
                    >Buy Plan</button>
                @else
                    <a href="{{ route('login') }}" class="mt-6 block text-center rounded-md bg-teal-700 px-4 py-3 text-sm font-semibold text-white">Login to Buy</a>
                @endauth
            </div>
        @endforeach
    </div>
</div>
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
@push('scripts')
<script>
document.querySelectorAll('.buy-plan').forEach((button) => {
    button.addEventListener('click', async () => {
        button.disabled = true;
        button.textContent = 'Opening checkout...';
        try {
            const { data } = await axios.post(button.dataset.orderUrl);
            const checkout = new Razorpay({
                key: data.key,
                amount: data.amount,
                currency: data.currency,
                name: '{{ config('app.name', 'Cvbliss') }}',
                description: 'Subscription plan',
                order_id: data.order_id,
                handler: async (payment) => {
                    await axios.post(`/purchases/${data.purchase_id}/verify`, payment);
                    window.location.href = '{{ route('dashboard') }}';
                },
                modal: {
                    ondismiss: () => {
                        button.disabled = false;
                        button.textContent = 'Buy Plan';
                    }
                },
                theme: { color: '#0f766e' }
            });
            checkout.open();
        } catch (error) {
            button.disabled = false;
            button.textContent = 'Buy Plan';
            alert(error.response?.data?.message || 'Payment could not be started.');
        }
    });
});
</script>
@endpush
@endsection
