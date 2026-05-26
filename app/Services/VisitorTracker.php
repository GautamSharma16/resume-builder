<?php

namespace App\Services;

use App\Models\VisitorLog;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;
use Throwable;

class VisitorTracker
{
    private const COOKIE_NAME = 'cvbliss_visitor_id';
    private const COOKIE_MINUTES = 60 * 24 * 365;

    public function track(Request $request): ?Cookie
    {
        $visitorId = $this->visitorId($request);
        $path = $this->normalizedPath($request);
        $now = now();

        $payload = [
            'visitor_id' => $visitorId,
            'session_id' => $visitorId,
            'visitor_hash' => $this->visitorHash($visitorId),
            'ip_address' => $request->ip(),
            'ip_hash' => $this->hash((string) $request->ip()),
            'path' => $path,
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
            'user_agent_hash' => $this->hash((string) $request->userAgent()),
            'device_hash' => $this->deviceHash($request),
            'last_visited_at' => $now,
        ];

        $cacheKey = 'visitor-log:'.$payload['visitor_hash'].':'.sha1($path);

        try {
            Cache::lock($cacheKey, 5)->block(2, fn () => $this->persist($payload, $now));
        } catch (Throwable) {
            $this->persist($payload, $now);
        }

        return cookie(
            self::COOKIE_NAME,
            $visitorId,
            self::COOKIE_MINUTES,
            config('session.path', '/'),
            config('session.domain'),
            (bool) config('session.secure'),
            true,
            false,
            config('session.same_site', 'lax')
        );
    }

    public function shouldTrack(Request $request): bool
    {
        $path = $request->path();

        return $request->isMethod('get')
            && ! $request->is('up')
            && ! $request->is('api/*')
            && ! $request->is('admin/*')
            && ! $request->ajax()
            && ! $request->expectsJson()
            && ! preg_match('/\.(?:css|js|map|json|png|jpe?g|gif|webp|svg|ico|woff2?|ttf|eot|pdf|docx?)$/i', $path);
    }

    private function persist(array $payload, $now): void
    {
        try {
            VisitorLog::query()->firstOrCreate(
                [
                    'visitor_hash' => $payload['visitor_hash'],
                    'path' => $payload['path'],
                ],
                $payload + ['first_visited_at' => $now, 'created_at' => $now, 'updated_at' => $now]
            );
        } catch (QueryException) {
            // A concurrent request inserted the same visitor/page row first.
        }

        VisitorLog::query()
            ->where('visitor_hash', $payload['visitor_hash'])
            ->where('path', $payload['path'])
            ->update([
                'visitor_id' => $payload['visitor_id'],
                'session_id' => $payload['session_id'],
                'ip_address' => $payload['ip_address'],
                'ip_hash' => $payload['ip_hash'],
                'user_agent' => $payload['user_agent'],
                'user_agent_hash' => $payload['user_agent_hash'],
                'device_hash' => $payload['device_hash'],
                'last_visited_at' => $payload['last_visited_at'],
                'updated_at' => $now,
            ]);
    }

    private function visitorId(Request $request): string
    {
        $visitorId = $request->cookies->get(self::COOKIE_NAME);

        if (is_string($visitorId) && Str::isUuid($visitorId)) {
            return $visitorId;
        }

        return (string) Str::uuid();
    }

    private function normalizedPath(Request $request): string
    {
        $path = '/'.trim($request->path(), '/');

        return $path === '/' ? '/' : Str::limit($path, 255, '');
    }

    private function visitorHash(string $visitorId): string
    {
        return $this->hash('visitor|'.$visitorId);
    }

    private function deviceHash(Request $request): string
    {
        return $this->hash(implode('|', [
            $request->ip(),
            $request->userAgent(),
            $request->header('accept-language'),
            $request->header('sec-ch-ua-platform'),
            $request->header('sec-ch-ua-mobile'),
        ]));
    }

    private function hash(string $value): string
    {
        return hash_hmac('sha256', $value, config('app.key'));
    }
}
