<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Resume;
use App\Models\User;
use App\Models\VisitorLog;

class DashboardController extends Controller
{
    public function admin()
    {
        return view('admin.dashboard', [
            'totalUsers' => User::count(),
            'totalResumes' => Resume::count(),
            'totalPurchases' => Purchase::where('status', 'paid')->count(),
            'totalVisitors' => VisitorLog::distinct('session_id')->count('session_id'),
        ]);
    }
}
