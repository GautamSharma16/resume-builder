<?php

namespace App\Services;

use App\Models\CoverLetter;
use App\Models\Resume;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PendingDownloadService
{
    private const SESSION_KEY = 'pending_download';
    private const PLAN_PROMPTED_KEY = 'pending_download_plan_prompted';

    public function rememberResume(Request $request, Resume $resume, string $format = 'pdf'): string
    {
        $format = $this->format($format);
        $url = route('resume.download', [$resume, $format]);

        $request->session()->put('pending_resume_id', $resume->id);
        $request->session()->put(self::SESSION_KEY, [
            'type' => 'resume',
            'id' => $resume->id,
            'format' => $format,
            'url' => $url,
        ]);
        $request->session()->forget(self::PLAN_PROMPTED_KEY);
        $request->session()->put('url.intended', $url);

        return $url;
    }

    public function rememberCoverLetter(Request $request, CoverLetter $coverLetter, string $format = 'pdf'): string
    {
        $format = $this->format($format);
        $url = route('cover-letter.download', [$coverLetter, $format]);

        $request->session()->put('pending_cover_letter_id', $coverLetter->id);
        $request->session()->put(self::SESSION_KEY, [
            'type' => 'cover_letter',
            'id' => $coverLetter->id,
            'format' => $format,
            'url' => $url,
        ]);
        $request->session()->forget(self::PLAN_PROMPTED_KEY);
        $request->session()->put('url.intended', $url);

        return $url;
    }

    public function attachPendingDocuments(Request $request, User $user, ?string $sessionId = null): void
    {
        $sessionId ??= $request->session()->getId();

        if ($resumeId = $request->session()->get('pending_resume_id')) {
            Resume::query()
                ->whereKey($resumeId)
                ->whereNull('user_id')
                ->where('session_id', $sessionId)
                ->update([
                    'user_id' => $user->id,
                    'session_id' => null,
                ]);
        }

        if ($letterId = $request->session()->get('pending_cover_letter_id')) {
            CoverLetter::query()
                ->whereKey($letterId)
                ->whereNull('user_id')
                ->where('session_id', $sessionId)
                ->update([
                    'user_id' => $user->id,
                    'session_id' => null,
                ]);
        }
    }

    public function clear(Request $request): void
    {
        $request->session()->forget('url.intended');
        $request->session()->forget([
            self::SESSION_KEY,
            self::PLAN_PROMPTED_KEY,
            'pending_resume_id',
            'pending_cover_letter_id',
        ]);
    }

    public function hasPending(Request $request): bool
    {
        return (bool) $this->intendedUrl($request);
    }

    public function dashboardPayload(Request $request, User $user): array
    {
        $url = $this->intendedUrl($request);

        if (! $url) {
            return [
                'hasPending' => false,
                'canDownload' => false,
                'downloadUrl' => null,
                'plansUrl' => route('plans'),
            ];
        }

        $pending = $request->session()->get(self::SESSION_KEY, []);
        $type = is_array($pending) ? ($pending['type'] ?? null) : null;

        $canDownload = match ($type) {
            'cover_letter' => (bool) $user->activeSubscription?->isActive(),
            'resume' => (bool) $user->activeSubscription?->hasDownloadsRemaining(),
            default => false,
        };

        $shouldRedirectToPlans = ! $canDownload && ! $request->session()->pull(self::PLAN_PROMPTED_KEY, false);

        if (! $canDownload && $shouldRedirectToPlans) {
            $request->session()->put(self::PLAN_PROMPTED_KEY, true);
        }

        return [
            'hasPending' => true,
            'canDownload' => $canDownload,
            'downloadUrl' => $url,
            'plansUrl' => route('plans'),
            'shouldRedirectToPlans' => $shouldRedirectToPlans,
        ];
    }

    public function intendedUrl(Request $request): ?string
    {
        $pending = $request->session()->get(self::SESSION_KEY);
        $url = is_array($pending) ? ($pending['url'] ?? null) : null;

        return is_string($url) && $this->isLocalUrl($url) ? $url : null;
    }

    private function format(string $format): string
    {
        return in_array($format, ['pdf', 'doc', 'ppt'], true) ? $format : 'pdf';
    }

    private function isLocalUrl(string $url): bool
    {
        $appUrl = rtrim(url('/'), '/');

        return $url === $appUrl
            || Str::startsWith($url, $appUrl.'/')
            || (Str::startsWith($url, '/') && ! Str::startsWith($url, '//'));
    }
}
