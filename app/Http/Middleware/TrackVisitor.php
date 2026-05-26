<?php

namespace App\Http\Middleware;

use App\Services\VisitorTracker;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    public function __construct(private readonly VisitorTracker $tracker)
    {
    }

    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request) && $cookie = $this->tracker->track($request)) {
            $response->headers->setCookie($cookie);
        }

        return $response;
    }

    private function shouldTrack(Request $request): bool
    {
        return Schema::hasTable('visitor_logs') && $this->tracker->shouldTrack($request);
    }
}
