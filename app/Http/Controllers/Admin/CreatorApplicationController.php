<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreatorApplication;
use App\Services\CreatorApplicationService;
use Illuminate\Http\Request;

class CreatorApplicationController extends Controller
{
    public function index(CreatorApplicationService $service)
    {
        return response()->json([
            'data' => $service->listPending(),
        ]);
    }

    public function approve(CreatorApplication $application, Request $request, CreatorApplicationService $service)
    {
        $updated = $service->approve($application, $request->user());

        return response()->json([
            'id' => $updated->id,
            'status' => $updated->status,
        ]);
    }

    public function reject(CreatorApplication $application, Request $request, CreatorApplicationService $service)
    {
        $data = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:3'],
        ]);

        $updated = $service->reject($application, $request->user(), $data['rejection_reason']);

        return response()->json([
            'id' => $updated->id,
            'status' => $updated->status,
        ]);
    }
}
