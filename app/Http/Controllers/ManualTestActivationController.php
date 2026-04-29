<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PlanActivationService;

class ManualTestActivationController extends Controller
{
    public function __invoke(int $userId, string $plan, PlanActivationService $plans)
    {
        abort_unless(app()->environment(['local', 'testing']), 403, 'Manual activation is only available locally.');

        $user = User::findOrFail($userId);
        $subscription = $plans->activate($user, strtolower($plan));

        return response()->json([
            'ok' => true,
            'user_id' => $user->id,
            'plan' => $subscription->plan_slug,
            'plan_started_at' => $subscription->plan_started_at,
            'expiry_date' => $subscription->expiry_date,
            'downloads_allowed' => $subscription->downloads_allowed,
            'downloads_used' => $subscription->downloads_used,
        ]);
    }
}
