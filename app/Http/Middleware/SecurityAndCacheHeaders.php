<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityAndCacheHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        if ($request->is('build/*') || $request->is('images/*') || $request->is('*.webp') || $request->is('*.png') || $request->is('*.jpg') || $request->is('*.jpeg') || $request->is('*.ico')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }

        return $response;
    }
}
