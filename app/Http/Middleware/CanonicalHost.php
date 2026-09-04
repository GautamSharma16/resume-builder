<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalHost
{
    public function handle(Request $request, Closure $next): Response
    {
        $preferredHost = parse_url((string) config('app.url'), PHP_URL_HOST) ?: env('APP_CANONICAL_HOST');

        if (! $preferredHost || app()->environment('local', 'testing')) {
            return $next($request);
        }

        $host = $request->getHost();
        $isCvblissVariant = in_array($host, ['cvbliss.in', 'www.cvbliss.in'], true);

        if ($isCvblissVariant && $host !== $preferredHost) {
            $url = 'https://'.$preferredHost.$request->getRequestUri();

            return redirect()->away($url, 301);
        }

        return $next($request);
    }
}
