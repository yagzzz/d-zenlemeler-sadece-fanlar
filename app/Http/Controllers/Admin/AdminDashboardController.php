<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Report;
use App\Models\User;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total_users'    => User::count(),
            'total_creators' => User::where('role', 'creator')->whereNotNull('creator_approved_at')->count(),
            'total_contents' => Content::count(),
            'pending_reports' => Report::where('status', 'pending')->count(),
            'pending_applications' => User::whereNotNull('creator_applied_at')
                ->whereNull('creator_approved_at')
                ->whereNull('creator_rejected_at')
                ->count(),
        ];

        if ($request->wantsJson()) {
            return response()->json(['stats' => $stats]);
        }

        return view('admin.dashboard', compact('stats'));
    }
}
