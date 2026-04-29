<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('get') && ! $request->is('up') && Schema::hasTable('visitor_logs')) {
            VisitorLog::create([
                'session_id' => $request->session()->getId(),
                'ip_address' => $request->ip(),
                'path' => '/'.ltrim($request->path(), '/'),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
            ]);
        }

        return $next($request);
    }
}
