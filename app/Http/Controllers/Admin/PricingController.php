<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;

class PricingController extends Controller
{
    public function index()
    {
        return view('admin.payments', ['plans' => Plan::orderBy('price_paise')->get()]);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price_rupees' => ['required', 'numeric', 'min:0'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'resume_limit' => ['nullable', 'integer', 'min:0'],
            'downloads_allowed' => ['nullable', 'integer', 'min:0'],
        ]);

        $plan->update([
            'name' => $validated['name'],
            'price_paise' => (int)($validated['price_rupees'] * 100),
            'duration_days' => $validated['duration_days'],
            'resume_limit' => $validated['resume_limit'],
            'downloads_allowed' => $request->downloads_allowed ?? $request->resume_limit,
            'ai_enabled' => true,
            'cover_letter_limit' => null, // Unlimited
        ]);

        return back()->with('status', 'Plan "' . $plan->name . '" updated successfully.');
    }
}
