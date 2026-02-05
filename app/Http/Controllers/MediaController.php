<?php

namespace App\Http\Controllers;

use App\Models\MediaAsset;
use App\Services\Media\MediaService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MediaController extends Controller
{
    public function viewUrl(MediaAsset $mediaAsset, Request $request, MediaService $service)
    {
        try {
            $result = $service->getSignedViewUrl($request->user(), $mediaAsset);
        } catch (AuthorizationException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
                'reason' => $exception->getMessage(),
            ], Response::HTTP_FORBIDDEN);
        }

        return response()->json($result);
    }
}
