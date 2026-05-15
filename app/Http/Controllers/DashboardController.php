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
use App\Services\TemplateRenderService;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(TemplateRenderService $renderer)
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
            ->selectRaw('MAX(updated_at) as last_visited_at')
            ->groupBy('path')
            ->orderByDesc('visits_count')
            ->paginate(15);

        return view('admin.visits', array_merge($this->visitorStats(), [
            'visits' => $visits,
        ]));
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
            'todayVisits' => VisitorLog::whereDate('created_at', today())->count(),
            'todayVisitors' => (int) VisitorLog::whereDate('created_at', today())
                ->selectRaw($this->uniqueVisitorSql().' as visitor_count')
                ->value('visitor_count'),
        ];
    }

    private function uniqueVisitorSql(): string
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            return "COUNT(DISTINCT COALESCE(session_id, ip_address || '|' || COALESCE(user_agent, '')))";
        }

        return "COUNT(DISTINCT COALESCE(session_id, CONCAT(ip_address, '|', COALESCE(user_agent, ''))))";
    }
}
