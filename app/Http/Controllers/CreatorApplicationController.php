<?php

namespace App\Http\Controllers;

use App\Services\CreatorApplicationService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CreatorApplicationController extends Controller
{
    public function apply(Request $request, CreatorApplicationService $service)
    {
        $data = $request->validate([
            'application_text' => ['required', 'string', 'min:10'],
        ]);

        $application = $service->apply($request->user(), $data['application_text']);

        return response()->json([
            'id' => $application->id,
            'status' => $application->status,
        ], Response::HTTP_CREATED);
    }
}
