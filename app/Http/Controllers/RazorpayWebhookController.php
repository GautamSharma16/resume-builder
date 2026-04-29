<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\Purchase;
use App\Models\User;
use App\Services\PlanActivationService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class RazorpayWebhookController extends Controller
{
    public function __invoke(Request $request, PlanActivationService $plans)
    {
        $this->verifySignature($request);

        $payload = $request->all();
        $event = (string) Arr::get($payload, 'event');

        if ($event !== 'payment.captured') {
            return response()->json(['ok' => true, 'ignored' => $event]);
        }

        $payment = Arr::get($payload, 'payload.payment.entity', []);
        $paymentId = (string) Arr::get($payment, 'id');
        $eventId = (string) Arr::get($payload, 'id');
        $notes = Arr::get($payment, 'notes', []);
        $userId = (int) Arr::get($notes, 'user_id');
        $planSlug = strtolower((string) Arr::get($notes, 'plan'));

        abort_if($paymentId === '' || $userId < 1 || $planSlug === '', 422, 'Invalid webhook payload.');
        if (Payment::where('razorpay_payment_id', $paymentId)->exists()) {
            return response()->json(['ok' => true, 'duplicate' => 'payment']);
        }

        if ($eventId !== '' && Payment::where('webhook_event_id', $eventId)->exists()) {
            return response()->json(['ok' => true, 'duplicate' => 'event']);
        }

        $user = User::findOrFail($userId);
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();

        DB::transaction(function () use ($plans, $user, $plan, $payment, $paymentId, $eventId, $notes) {
            $purchase = Purchase::where('user_id', $user->id)
                ->where('plan_slug', $plan->slug)
                ->where('status', 'created')
                ->latest()
                ->first();

            if (! $purchase) {
                $purchase = new Purchase([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'plan_slug' => $plan->slug,
                    'amount_paise' => (int) Arr::get($payment, 'amount', $plan->price_paise),
                    'currency' => (string) Arr::get($payment, 'currency', 'INR'),
                ]);
            }

            $purchase->fill([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_slug' => $plan->slug,
                'status' => 'paid',
                'razorpay_payment_id' => $paymentId,
                'webhook_event_id' => $eventId ?: null,
                'notes' => $notes,
                'paid_at' => now(),
            ])->save();

            Payment::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'purchase_id' => $purchase->id,
                'plan_slug' => $plan->slug,
                'amount_paise' => (int) Arr::get($payment, 'amount', $plan->price_paise),
                'currency' => (string) Arr::get($payment, 'currency', 'INR'),
                'status' => 'paid',
                'razorpay_payment_id' => $paymentId,
                'webhook_event_id' => $eventId ?: null,
                'payload' => $payment,
                'paid_at' => now(),
            ]);

            $plans->activate($user, $plan);
        });

        return response()->json(['ok' => true]);
    }

    private function verifySignature(Request $request): void
    {
        $secret = config('services.razorpay.webhook_secret');
        abort_if(! $secret, 500, 'Razorpay webhook secret is not configured.');

        $actual = (string) $request->header('X-Razorpay-Signature');
        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        abort_unless(hash_equals($expected, $actual), 403, 'Invalid Razorpay signature.');
    }
}
