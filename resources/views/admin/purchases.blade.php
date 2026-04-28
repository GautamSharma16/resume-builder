@extends('layouts.admin')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 md:px-8 py-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Purchases</h1>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Quantity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Total</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase tracking-wider">Date</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">Premium Templates</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">45</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">$450</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">Apr 10, 2026</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
