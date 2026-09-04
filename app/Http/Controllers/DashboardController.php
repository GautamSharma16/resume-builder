<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\CoverLetter;
use App\Models\Article;
use App\Models\Resume;
use App\Models\ResumeAnalysis;
use App\Models\Template;
use App\Models\User;
use App\Models\VisitorLog;
use App\Services\PendingDownloadService;
use App\Services\TemplateRenderService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(TemplateRenderService $renderer, PendingDownloadService $pendingDownloads)
    {
        $user = auth()->user();
        
        // Active Plan
        $activeSubscription = $user->activeSubscription()->with('plan')->first();
        
        // Recent Resumes (Take 4)
        $recentResumes = \App\Models\Resume::where('user_id', $user->id)
            ->with('template')
            ->latest()
            ->take(4)
            ->get();

        $recentResumePreviews = $recentResumes->mapWithKeys(function (Resume $resume) use ($renderer) {
            if (! $resume->template) {
                return [$resume->id => null];
            }

            $html = (string) $renderer->renderResume($resume->template, $resume->data ?? []);

            return [
                $resume->id => view('templates.rendered-document', ['html' => $html])->render(),
            ];
        });
            
        // Recent Cover Letters (Take 4)
        $recentCoverLetters = \App\Models\CoverLetter::where('user_id', $user->id)
            ->with('template')
            ->latest()
            ->take(4)
            ->get();

        $recentCoverLetterPreviews = $recentCoverLetters->mapWithKeys(function (CoverLetter $letter) use ($renderer) {
            if (! $letter->template) {
                return [$letter->id => null];
            }

            $html = (string) $renderer->renderCoverLetter($letter->template, $letter->data ?? []);

            return [
                $letter->id => view('templates.rendered-document', ['html' => $html])->render(),
            ];
        });

        $recentBlogs = Article::query()
            ->where('is_published', true)
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('dashboard', [
            'user' => $user,
            'activeSubscription' => $activeSubscription,
            'recentResumes' => $recentResumes,
            'recentResumePreviews' => $recentResumePreviews,
            'recentCoverLetters' => $recentCoverLetters,
            'recentCoverLetterPreviews' => $recentCoverLetterPreviews,
            'recentBlogs' => $recentBlogs,
            'totalResumes' => \App\Models\Resume::where('user_id', $user->id)->count(),
            'totalCoverLetters' => \App\Models\CoverLetter::where('user_id', $user->id)->count(),
            'pendingDownload' => $pendingDownloads->dashboardPayload(request(), $user),
        ]);
    }

    public function coverLetters(TemplateRenderService $renderer)
    {
        $letters = CoverLetter::query()
            ->where('user_id', auth()->id())
            ->with('template')
            ->latest()
            ->paginate(12);

        $previews = $letters->getCollection()->mapWithKeys(function (CoverLetter $letter) use ($renderer) {
            if (! $letter->template) {
                return [$letter->id => null];
            }

            $html = (string) $renderer->renderCoverLetter($letter->template, $letter->data ?? []);

            return [
                $letter->id => view('templates.rendered-document', ['html' => $html])->render(),
            ];
        });

        return view('cover-letter.index', [
            'letters' => $letters,
            'previews' => $previews,
        ]);
    }

    public function admin()
    {
        $user = auth()->user();
        $stats = $this->dashboardStats();

        if ($user->hasRole(['seo', 'article', 'article_writer'])) {
            return view('admin.seo-dashboard', $stats);
        }

        if ($user->hasRole(['developer', 'dev'])) {
            return view('admin.developer-dashboard', $stats);
        }

        return view('admin.dashboard', $stats);
    }

    public function getData()
    {
        return response()->json($this->dashboardStats());
    }

    public function visits()
    {
        $visits = VisitorLog::query()
            ->select('path')
            ->selectRaw('COUNT(*) as visits_count')
            ->selectRaw($this->uniqueVisitorSql().' as unique_visitors_count')
            ->selectRaw('MAX(COALESCE(last_visited_at, updated_at)) as last_visited_at')
            ->groupBy('path')
            ->orderByDesc('unique_visitors_count')
            ->paginate(15);

        return view('admin.visits', array_merge($this->visitorStats(), [
            'visits' => $visits,
        ]));
    }

    public function registrations(Request $request)
    {
        $timezone = config('app.timezone', 'Asia/Kolkata');
        $scope = $request->query('scope', 'all');
        $today = Carbon::today($timezone);
        $firstRegistration = User::query()->where('role', 'user')->min('created_at');
        $defaultFrom = $firstRegistration
            ? Carbon::parse($firstRegistration)->timezone($timezone)->toDateString()
            : $today->toDateString();
        $fromDate = $request->query('from', $scope === 'today' ? $today->toDateString() : $defaultFrom);
        $toDate = $request->query('to', $scope === 'today' ? $today->toDateString() : $today->toDateString());
        $from = Carbon::parse($fromDate, $timezone)->startOfDay();
        $to = Carbon::parse($toDate, $timezone)->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $registeredUsers = User::query()
            ->select(['id', 'name', 'email', 'created_at'])
            ->where('role', 'user')
            ->whereBetween('created_at', [$from, $to])
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $allUsersCount = User::where('role', 'user')->count();
        $todayUsersCount = User::where('role', 'user')
            ->whereBetween('created_at', [$today->copy()->startOfDay(), $today->copy()->endOfDay()])
            ->count();

        return view('admin.registrations', [
            'registeredUsers' => $registeredUsers,
            'allUsersCount' => $allUsersCount,
            'todayUsersCount' => $todayUsersCount,
            'selectedUsersCount' => $registeredUsers->total(),
            'scope' => $scope,
            'from' => $from,
            'to' => $to,
            'timezone' => $timezone,
        ]);
    }

    private function dashboardStats(): array
    {
        return array_merge($this->visitorStats(), [
            'totalUsers' => User::count(),
            'totalResumes' => Resume::count() + ResumeAnalysis::count(),
            'totalTemplates' => Template::count(),
            'totalPurchases' => Purchase::where('status', 'paid')->count(),
        ]);
    }

    private function visitorStats(): array
    {
        return [
            'totalVisitors' => (int) VisitorLog::query()
                ->selectRaw($this->uniqueVisitorSql().' as visitor_count')
                ->value('visitor_count'),
            'totalVisits' => VisitorLog::count(),
            'todayVisits' => VisitorLog::whereDate('last_visited_at', today())->count(),
            'todayVisitors' => (int) VisitorLog::whereDate('last_visited_at', today())
                ->selectRaw($this->uniqueVisitorSql().' as visitor_count')
                ->value('visitor_count'),
            'todayRegistrations' => User::where('role', 'user')->whereDate('created_at', today())->count(),
        ];
    }

    private function uniqueVisitorSql(): string
    {
        return 'COUNT(DISTINCT COALESCE(visitor_hash, ip_address))';
    }
}
