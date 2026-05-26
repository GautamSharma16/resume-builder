<?php

namespace App\Http\Middleware;

use App\Models\CoverLetter;
use App\Models\Resume;
use App\Services\PendingDownloadService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureDownloadAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $document = $request->route('resume') ?: $request->route('coverLetter');
        $format = (string) ($request->route('format') ?: 'pdf');

        if ($document instanceof Resume) {
            $this->authorizeResume($request, $document);

            if (! $request->user()) {
                app(PendingDownloadService::class)->rememberResume($request, $document, $format);

                return redirect()->guest(route('login'));
            }

            if (! $document->is_paid && ! $request->user()->activeSubscription?->hasDownloadsRemaining()) {
                return redirect()->route('plans')->with('status', 'Choose a plan to unlock downloads.');
            }
        }

        if ($document instanceof CoverLetter) {
            $this->authorizeCoverLetter($request, $document);

            if (! $request->user()) {
                app(PendingDownloadService::class)->rememberCoverLetter($request, $document, $format);

                return redirect()->guest(route('login'));
            }

            if (! $document->is_paid && ! $request->user()->activeSubscription?->hasDownloadsRemaining()) {
                return redirect()->route('plans')->with('status', 'Choose a plan to unlock downloads.');
            }
        }

        return $next($request);
    }

    private function authorizeResume(Request $request, Resume $resume): void
    {
        if ($resume->user_id && $resume->user_id !== $request->user()?->id) {
            abort(403);
        }

        if (! $resume->user_id && $resume->session_id !== $request->session()->getId()) {
            abort(403);
        }
    }

    private function authorizeCoverLetter(Request $request, CoverLetter $coverLetter): void
    {
        if ($coverLetter->user_id && $coverLetter->user_id !== $request->user()?->id) {
            abort(403);
        }

        if (! $coverLetter->user_id && $coverLetter->session_id !== $request->session()->getId()) {
            abort(403);
        }
    }
}
