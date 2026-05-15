<?php

namespace App\Http\Middleware;

use App\Models\VisitorLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($this->shouldTrack($request)) {
            $visitorId = $request->cookies->get('cvbliss_visitor_id');

            if (! is_string($visitorId) || $visitorId === '') {
                $visitorId = (string) Str::uuid();
            }

            $path = '/'.ltrim($request->path(), '/');

            $visit = VisitorLog::query()
                ->where('session_id', $visitorId)
                ->where('path', $path)
                ->whereDate('created_at', today())
                ->first();

            if ($visit) {
                $visit->forceFill([
                    'ip_address' => $request->ip(),
                    'user_agent' => substr((string) $request->userAgent(), 0, 500),
                ])->save();
            } else {
                VisitorLog::create([
                    'session_id' => $visitorId,
                    'ip_address' => $request->ip(),
                    'path' => $path,
                    'user_agent' => substr((string) $request->userAgent(), 0, 500),
                ]);
            }

            $response->headers->setCookie(cookie(
                'cvbliss_visitor_id',
                $visitorId,
                60 * 24 * 365,
                config('session.path', '/'),
                config('session.domain'),
                (bool) config('session.secure'),
                true,
                false,
                config('session.same_site', 'lax')
            ));
        }

        return $response;
    }

    private function shouldTrack(Request $request): bool
    {
        $path = $request->path();

        return $request->isMethod('get')
            && ! $request->is('up')
            && ! $request->is('api/*')
            && ! $request->is('admin/dashboard-data')
            && ! $request->ajax()
            && ! $request->expectsJson()
            && ! preg_match('/\.(?:css|js|map|json|png|jpe?g|gif|webp|svg|ico|woff2?|ttf|eot|pdf|docx?)$/i', $path)
            && Schema::hasTable('visitor_logs');
    }
}
