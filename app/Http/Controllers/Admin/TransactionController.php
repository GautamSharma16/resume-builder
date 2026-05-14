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

    public function exportCsv()
    {
        $transactions = Purchase::with(['user', 'plan'])->latest()->get();
        $filename = "transactions-" . now()->format('Y-m-d-H-i') . ".csv";
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'User', 'Email', 'Plan', 'Amount (INR)', 'Status', 'Date', 'Razorpay ID'];

        $callback = function() use($transactions, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    $transaction->user?->name ?? 'Guest',
                    $transaction->user?->email ?? 'N/A',
                    $transaction->plan?->name ?? 'N/A',
                    number_format($transaction->amount_paise / 100, 2),
                    ucfirst($transaction->status),
                    ($transaction->paid_at ?? $transaction->created_at)?->format('Y-m-d H:i:s') ?? 'N/A',
                    $transaction->razorpay_payment_id ?: $transaction->razorpay_payment_link_id ?: 'Pending'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
