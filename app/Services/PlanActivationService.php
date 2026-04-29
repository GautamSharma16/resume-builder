<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PlanActivationService
{
    public function __construct(private readonly PlanCatalog $plans)
    {
    }

    public function activate(User $user, string|Plan $plan): Subscription
    {
        $plan = $plan instanceof Plan ? $plan : $this->plans->ensurePlan($plan);

        return DB::transaction(function () use ($user, $plan) {
            Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->update(['status' => 'expired']);

            return Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_slug' => $plan->slug,
                'status' => 'active',
                'downloads_allowed' => $plan->downloads_allowed,
                'downloads_used' => 0,
                'plan_started_at' => now(),
                'expiry_date' => now()->addDays($plan->duration_days),
                'starts_at' => now(),
                'ends_at' => now()->addDays($plan->duration_days),
            ]);
        });
    }

    public function consumeDownload(User $user): Subscription
    {
        return DB::transaction(function () use ($user) {
            $subscription = Subscription::where('user_id', $user->id)
                ->where('status', 'active')
                ->where('expiry_date', '>', now())
                ->latest('id')
                ->lockForUpdate()
                ->first();

            abort_if(! $subscription, 402, 'Choose a plan to unlock downloads.');
            abort_if(! $subscription->hasDownloadsRemaining(), 402, 'Your plan download limit is exhausted.');

            if (! is_null($subscription->downloads_allowed)) {
                $subscription->increment('downloads_used');
                $subscription->refresh();
            }

            return $subscription;
        });
    }
}
