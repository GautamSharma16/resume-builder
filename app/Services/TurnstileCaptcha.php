<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class TurnstileCaptcha
{
    public function enabled(): bool
    {
        return filled(config('services.turnstile.site_key')) && filled(config('services.turnstile.secret_key'));
    }

    public function siteKey(): ?string
    {
        return config('services.turnstile.site_key');
    }

    public function verify(?string $token, ?string $ip = null): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        if (! is_string($token) || trim($token) === '') {
            return false;
        }

        $response = Http::asForm()
            ->timeout((int) config('services.turnstile.timeout', 5))
            ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', array_filter([
                'secret' => config('services.turnstile.secret_key'),
                'response' => $token,
                'remoteip' => $ip,
            ]));

        return (bool) $response->json('success', false);
    }
}
