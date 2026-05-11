@extends('layouts.admin')

@section('title', 'Transactions - Admin')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Transactions</h1>
            <p class="text-sm text-gray-500">View all Razorpay payment-link attempts and completed payments</p>
        </div>
        <a href="{{ route('admin.transactions.export') }}" class="inline-flex items-center gap-2 bg-white border border-gray-200 px-4 py-2 rounded-xl text-sm font-semibold text-gray-700 hover:bg-gray-50 transition shadow-sm">
            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Download CSV
        </a>
    </div>

    <div class="grid gap-4 mb-6 md:grid-cols-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-500">Total</p>
            <p class="mt-2 text-2xl font-bold text-gray-950">{{ $summary['total'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-500">Paid</p>
            <p class="mt-2 text-2xl font-bold text-green-700">{{ $summary['paid'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-500">Pending</p>
            <p class="mt-2 text-2xl font-bold text-amber-700">{{ $summary['created'] }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase text-gray-500">Revenue</p>
            <p class="mt-2 text-2xl font-bold text-gray-950">Rs. {{ number_format($summary['revenue_paise'] / 100, 2) }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
        {{-- Desktop table --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Transaction ID</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($transactions as $transaction)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4 text-sm font-mono text-gray-600">
                            {{ $transaction->razorpay_payment_id ?: $transaction->razorpay_payment_link_id ?: 'Pending' }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                    {{ substr($transaction->user?->name ?? 'U', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ $transaction->user?->name ?? 'Guest / deleted user' }}</p>
                                    <p class="text-xs text-gray-500">{{ $transaction->user?->email ?? 'No email available' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                {{ $transaction->plan->name ?? 'Single Purchase' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm font-semibold text-gray-900">
                            Rs. {{ number_format($transaction->amount_paise / 100, 2) }}
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusClass = $transaction->status === 'paid'
                                    ? 'bg-green-100 text-green-800'
                                    : ($transaction->status === 'created' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700');
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                {{ ucfirst(str_replace('_', ' ', $transaction->status)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $transaction->created_at->format('M d, Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            No transactions found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="md:hidden divide-y divide-gray-100">
            @forelse($transactions as $transaction)
                @php
                    $statusClass = $transaction->status === 'paid'
                        ? 'bg-green-100 text-green-800'
                        : ($transaction->status === 'created' ? 'bg-amber-100 text-amber-800' : 'bg-gray-100 text-gray-700');
                    $txnId = $transaction->razorpay_payment_id ?: $transaction->razorpay_payment_link_id ?: 'Pending';
                @endphp
                <div class="p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-xs font-mono text-gray-500 truncate">{{ $txnId }}</div>
                            <div class="mt-2 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ substr($transaction->user?->name ?? 'U', 0, 1) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="text-sm font-semibold text-gray-900 truncate">{{ $transaction->user?->name ?? 'Guest / deleted user' }}</div>
                                    <div class="text-xs text-gray-500 truncate">{{ $transaction->user?->email ?? 'No email available' }}</div>
                                </div>
                            </div>
                            <div class="mt-3 flex flex-wrap gap-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                    {{ $transaction->plan->name ?? 'Single Purchase' }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
                                    {{ ucfirst(str_replace('_', ' ', $transaction->status)) }}
                                </span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                                    Rs. {{ number_format($transaction->amount_paise / 100, 2) }}
                                </span>
                            </div>
                            <div class="mt-2 text-xs text-gray-500">
                                {{ $transaction->created_at->format('M d, Y H:i') }}
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-gray-500">
                    No transactions found.
                </div>
            @endforelse
        </div>
        @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
