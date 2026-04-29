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
            'price_paise' => ['required', 'integer', 'min:0'],
            'downloads_allowed' => ['nullable', 'integer', 'min:1'],
            'duration_days' => ['required', 'integer', 'min:1'],
            'resume_limit' => ['nullable', 'integer', 'min:1'],
            'cover_letter_limit' => ['nullable', 'integer', 'min:1'],
            'ai_enabled' => ['nullable', 'boolean'],
        ]);

        $plan->update($validated + ['ai_enabled' => false]);

        return back()->with('status', 'Pricing updated.');
    }
}
