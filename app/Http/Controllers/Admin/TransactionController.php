<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Purchase;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Purchase::with(['user', 'plan'])
            ->latest()
            ->paginate(20);

        $summary = [
            'total' => Purchase::count(),
            'paid' => Purchase::where('status', 'paid')->count(),
            'created' => Purchase::where('status', 'created')->count(),
            'revenue_paise' => Purchase::where('status', 'paid')->sum('amount_paise'),
        ];

        return view('admin.transactions', compact('transactions', 'summary'));
    }
}
