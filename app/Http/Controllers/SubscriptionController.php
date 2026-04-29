<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Purchase;
use App\Services\PlanActivationService;
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
        abort_unless($plan->is_active, 404);

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $reference = 'plan_'.$plan->slug.'_user_'.$request->user()->id.'_'.now()->timestamp;

        $link = $api->paymentLink->create([
            'amount' => $plan->price_paise,
            'currency' => config('services.razorpay.currency', 'INR'),
            'accept_partial' => false,
            'reference_id' => $reference,
            'description' => $plan->name.' Resume Builder Plan',
            'customer' => [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'contact' => $request->user()->mobile,
            ],
            'notify' => [
                'sms' => false,
                'email' => true,
            ],
            'callback_url' => route('dashboard'),
            'callback_method' => 'get',
            'notes' => [
                'user_id' => (string) $request->user()->id,
                'plan' => $plan->slug,
            ],
        ]);

        $purchase = Purchase::create([
            'user_id' => $request->user()->id,
            'plan_id' => $plan->id,
            'plan_slug' => $plan->slug,
            'amount_paise' => $plan->price_paise,
            'currency' => config('services.razorpay.currency', 'INR'),
            'status' => 'created',
            'razorpay_payment_link_id' => $link['id'],
            'razorpay_payment_link_reference_id' => $reference,
            'razorpay_payment_link_url' => $link['short_url'],
            'notes' => [
                'user_id' => $request->user()->id,
                'plan' => $plan->slug,
            ],
        ]);

        return redirect()->away($purchase->razorpay_payment_link_url);
    }

    public function verify(Request $request, Purchase $purchase, PlanActivationService $plans)
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

        $plans->activate($purchase->user, $purchase->plan);

        return response()->json(['ok' => true]);
    }
}
