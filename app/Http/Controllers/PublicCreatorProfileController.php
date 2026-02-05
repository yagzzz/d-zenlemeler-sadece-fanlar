<?php

namespace App\Http\Controllers;

use App\Services\Creator\CreatorProfileService;
use Illuminate\Http\Response;

class PublicCreatorProfileController extends Controller
{
    public function show(string $username, CreatorProfileService $service)
    {
        $payload = $service->publicProfile($username);

        return response()->json($payload, Response::HTTP_OK);
    }
}
