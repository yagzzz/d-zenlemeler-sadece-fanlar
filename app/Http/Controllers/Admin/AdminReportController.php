<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Report;
use Illuminate\Http\Request;

class AdminReportController extends Controller
{
    /**
     * GET /admin/reports — List all reports.
     */
    public function index(Request $request)
    {
        $query = Report::with(['reporter', 'reportable'])->orderByDesc('created_at');

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        } else {
            // Default: show pending first
            $query->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END");
        }

        $reports = $query->paginate(20)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['reports' => $reports]);
        }

        return view('admin.reports.index', compact('reports'));
    }

    /**
     * POST /admin/reports/{report}/resolve — Resolve a report.
     */
    public function resolve(Request $request, Report $report)
    {
        $report->update([
            'status'      => 'resolved',
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Rapor çözüldü.']);
        }

        return redirect('/admin/reports')->with('success', 'Rapor çözüldü.');
    }

    /**
     * POST /admin/reports/{report}/dismiss — Dismiss a report.
     */
    public function dismiss(Request $request, Report $report)
    {
        $report->update([
            'status'      => 'dismissed',
            'resolved_by' => $request->user()->id,
            'resolved_at' => now(),
        ]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Rapor reddedildi.']);
        }

        return redirect('/admin/reports')->with('success', 'Rapor reddedildi.');
    }
}
