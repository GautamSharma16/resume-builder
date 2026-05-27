<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PendingDownloadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('google')
            ->stateless()
            ->redirect();
    }

    public function callback(Request $request)
    {
        $googleUser = Socialite::driver('google')
            ->stateless()
            ->user();

        $user = User::where('email', $googleUser->getEmail())->first();

        if ($user) {
            $user->forceFill([
                'google_id' => $user->google_id ?: $googleUser->getId(),
                'provider' => 'google',
                'email_verified_at' => $user->email_verified_at ?: now(),
            ])->save();
        } else {
            $user = User::create([
                'name' => $googleUser->getName()
                    ?: $googleUser->getNickname()
                    ?: 'Google User',

                'email' => $googleUser->getEmail(),
                'mobile' => null,
                'password' => Str::random(32),
                'role' => 'user',
                'google_id' => $googleUser->getId(),
                'provider' => 'google',
                'email_verified_at' => now(),
            ]);
        }

        $guestSessionId = $request->session()->getId();
        Auth::login($user);

        request()->session()->regenerate();
        app(PendingDownloadService::class)->attachPendingDocuments($request, $user, $guestSessionId);

        $intended = (string) $request->session()->get('url.intended', '');
        $intendedPath = parse_url($intended, PHP_URL_PATH) ?: '';

        if (app(PendingDownloadService::class)->hasPending($request) && ! str_starts_with($intendedPath, '/plans/')) {
            return redirect()->route('dashboard');
        }

        $plansPath = parse_url(route('plans'), PHP_URL_PATH) ?: '/plans';

        if ($user->activeSubscription?->hasDownloadsRemaining() && $intendedPath === $plansPath) {
            $request->session()->forget('url.intended');
            return redirect()->route('dashboard');
        }

        return redirect()->intended(route('dashboard'));
    }
}
