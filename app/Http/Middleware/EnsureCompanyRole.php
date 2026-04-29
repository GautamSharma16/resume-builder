<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCompanyRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && auth()->user()->isCompany()) {
            return $next($request);
        }

        return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
    }
}
