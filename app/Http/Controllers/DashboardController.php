<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Resume;
use App\Models\ResumeAnalysis;
use App\Models\Template;
use App\Models\User;
use App\Models\VisitorLog;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Active Plan
        $activeSubscription = $user->activeSubscription()->with('plan')->first();
        
        // Recent Resumes
        $recentResumes = \App\Models\Resume::where('user_id', $user->id)
            ->with('template')
            ->latest()
            ->take(6)
            ->get();
            
        // Recent Cover Letters
        $recentCoverLetters = \App\Models\CoverLetter::where('user_id', $user->id)
            ->with('template')
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard', [
            'user' => $user,
            'activeSubscription' => $activeSubscription,
            'recentResumes' => $recentResumes,
            'recentCoverLetters' => $recentCoverLetters,
            'totalResumes' => \App\Models\Resume::where('user_id', $user->id)->count(),
            'totalCoverLetters' => \App\Models\CoverLetter::where('user_id', $user->id)->count(),
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
            ->selectRaw('COUNT(DISTINCT COALESCE(session_id, ip_address)) as unique_visitors_count')
            ->selectRaw('MAX(created_at) as last_visited_at')
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
                ->selectRaw('COUNT(DISTINCT COALESCE(session_id, ip_address)) as visitor_count')
                ->value('visitor_count'),
            'totalVisits' => VisitorLog::count(),
            'todayVisits' => VisitorLog::whereDate('created_at', today())->count(),
            'todayVisitors' => (int) VisitorLog::whereDate('created_at', today())
                ->selectRaw('COUNT(DISTINCT COALESCE(session_id, ip_address)) as visitor_count')
                ->value('visitor_count'),
        ];
    }
}
