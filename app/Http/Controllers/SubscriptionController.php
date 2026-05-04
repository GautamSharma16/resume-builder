<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Payment;
use App\Models\Purchase;
use App\Services\PlanActivationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Razorpay\Api\Api;
use Throwable;

class SubscriptionController extends Controller
{
    public function plans()
    {
        return view('pages.plans', ['plans' => Plan::where('is_active', true)->orderBy('price_paise')->get()]);
    }

    public function checkout(Request $request, Plan $plan, PlanActivationService $plans)
    {
        return $this->redirectToPayment($request, $plan, $plans);
    }

    public function order(Request $request, Plan $plan, PlanActivationService $plans)
    {
        return $this->redirectToPayment($request, $plan, $plans);
    }

    private function redirectToPayment(Request $request, Plan $plan, PlanActivationService $plans)
    {
        abort_unless($plan->is_active, 404);

        if ($plan->price_paise <= 0) {
            $plans->activate($request->user(), $plan);

            return redirect()
                ->route('dashboard')
                ->with('status', "{$plan->name} plan activated. You can download {$this->downloadLabel($plan)}.");
        }

        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $reference = 'plan_'.$plan->slug.'_user_'.$request->user()->id.'_'.now()->timestamp;
        $contact = $this->normalizeRazorpayContact($request->user()->mobile);

        $customer = [
            'name' => $request->user()->name,
            'email' => $request->user()->email,
        ];

        if ($contact) {
            $customer['contact'] = $contact;
        }

        $link = $api->paymentLink->create([
            'amount' => $plan->price_paise,
            'currency' => config('services.razorpay.currency', 'INR'),
            'accept_partial' => false,
            'reference_id' => $reference,
            'description' => $plan->name.' Resume Builder Plan',
            'customer' => $customer,
            'notify' => [
                'sms' => false,
                'email' => true,
            ],
            'callback_url' => route('plans.callback'),
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

    private function normalizeRazorpayContact(?string $mobile): ?string
    {
        if (! $mobile) {
            return null;
        }

        $contact = preg_replace('/\D+/', '', $mobile);

        if (! $contact || preg_match('/^([0-9])\1+$/', $contact)) {
            return null;
        }

        if (strlen($contact) === 12 && str_starts_with($contact, '91')) {
            $contact = substr($contact, 2);
        }

        return strlen($contact) === 10 ? $contact : null;
    }

    private function downloadLabel(Plan $plan): string
    {
        return is_null($plan->downloads_allowed)
            ? 'unlimited resumes'
            : $plan->downloads_allowed.' resume'.($plan->downloads_allowed === 1 ? '' : 's');
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

    public function paymentLinkCallback(Request $request, PlanActivationService $plans)
    {
        $validated = $request->validate([
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_payment_link_id' => ['required', 'string'],
            'razorpay_payment_link_reference_id' => ['required', 'string'],
            'razorpay_payment_link_status' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $this->verifyPaymentLinkSignature($validated);

        $purchase = Purchase::with(['user', 'plan'])
            ->where('razorpay_payment_link_id', $validated['razorpay_payment_link_id'])
            ->where('razorpay_payment_link_reference_id', $validated['razorpay_payment_link_reference_id'])
            ->firstOrFail();

        if ($validated['razorpay_payment_link_status'] !== 'paid') {
            $purchase->update(['status' => $validated['razorpay_payment_link_status']]);

            return redirect()
                ->route('plans')
                ->with('status', 'Payment was not completed. Please try again.');
        }

        $alreadyProcessed = $purchase->status === 'paid'
            && Payment::where('razorpay_payment_id', $validated['razorpay_payment_id'])->exists();

        DB::transaction(function () use ($purchase, $plans, $validated, $alreadyProcessed) {
            $purchase->fill([
                'status' => 'paid',
                'razorpay_payment_id' => $validated['razorpay_payment_id'],
                'razorpay_signature' => $validated['razorpay_signature'],
                'paid_at' => now(),
            ])->save();

            Payment::firstOrCreate(
                ['razorpay_payment_id' => $validated['razorpay_payment_id']],
                [
                    'user_id' => $purchase->user_id,
                    'plan_id' => $purchase->plan_id,
                    'purchase_id' => $purchase->id,
                    'plan_slug' => $purchase->plan_slug,
                    'amount_paise' => $purchase->amount_paise,
                    'currency' => $purchase->currency,
                    'status' => 'paid',
                    'payload' => $validated,
                    'paid_at' => now(),
                ]
            );

            if (! $alreadyProcessed && $purchase->user && $purchase->plan) {
                $plans->activate($purchase->user, $purchase->plan);
            }
        });

        return redirect()
            ->route('dashboard')
            ->with('status', 'Payment successful. Your plan is active and downloads are unlocked.');
    }

    private function verifyPaymentLinkSignature(array $payload): void
    {
        $secret = (string) config('services.razorpay.secret');
        abort_if($secret === '', 500, 'Razorpay secret is not configured.');

        $signedPayload = implode('|', [
            $payload['razorpay_payment_link_id'],
            $payload['razorpay_payment_link_reference_id'],
            $payload['razorpay_payment_link_status'],
            $payload['razorpay_payment_id'],
        ]);

        $expected = hash_hmac('sha256', $signedPayload, $secret);

        abort_unless(hash_equals($expected, $payload['razorpay_signature']), 422, 'Payment verification failed.');
    }
}
