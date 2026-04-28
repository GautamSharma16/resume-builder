<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Purchase;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Razorpay\Api\Api;
use Throwable;

class SubscriptionController extends Controller
{
    public function plans()
    {
        return view('pages.plans', ['plans' => Plan::where('is_active', true)->get()]);
    }

    public function order(Request $request, Plan $plan)
    {
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $order = $api->order->create([
            'receipt' => 'plan_'.$plan->id.'_user_'.auth()->id(),
            'amount' => $plan->price_paise,
            'currency' => config('services.razorpay.currency', 'INR'),
            'payment_capture' => 1,
        ]);

        $purchase = Purchase::create([
            'user_id' => auth()->id(),
            'plan_id' => $plan->id,
            'amount_paise' => $plan->price_paise,
            'currency' => 'INR',
            'status' => 'created',
            'razorpay_order_id' => $order['id'],
        ]);

        return response()->json([
            'purchase_id' => $purchase->id,
            'order_id' => $order['id'],
            'key' => config('services.razorpay.key'),
            'amount' => $plan->price_paise,
            'currency' => 'INR',
        ]);
    }

    public function verify(Request $request, Purchase $purchase)
    {
        $validated = $request->validate([
            'razorpay_order_id' => ['required'],
            'razorpay_payment_id' => ['required'],
            'razorpay_signature' => ['required'],
        ]);

        try {
            (new Api(config('services.razorpay.key'), config('services.razorpay.secret')))
                ->utility
                ->verifyPaymentSignature($validated);
        } catch (Throwable) {
            abort(422, 'Payment verification failed.');
        }

        $purchase->update([
            'status' => 'paid',
            'razorpay_payment_id' => $validated['razorpay_payment_id'],
            'razorpay_signature' => $validated['razorpay_signature'],
            'paid_at' => now(),
        ]);

        Subscription::updateOrCreate(
            ['user_id' => $purchase->user_id],
            ['plan_id' => $purchase->plan_id, 'status' => 'active', 'starts_at' => now()]
        );

        return response()->json(['ok' => true]);
    }
}
