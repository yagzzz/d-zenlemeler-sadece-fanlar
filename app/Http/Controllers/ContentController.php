<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Services\ContentPresenter;
use App\Services\Media\MediaService;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function show(Content $content, Request $request, ContentPresenter $presenter, MediaService $mediaService)
    {
        $payload = $presenter->present($content, $request->user());
        $locked = $payload['locked'] ?? true;

        $payload['media'] = $content->mediaAssets()
            ->orderBy('content_media.position')
            ->get()
            ->map(function ($asset) use ($locked, $mediaService, $request) {
                $viewData = $locked ? null : $mediaService->getSignedViewUrl($request->user(), $asset);

                return [
                    'id' => $asset->id,
                    'type' => $asset->type,
                    'mime_type' => $asset->mime_type,
                    'status' => $asset->status,
                    'position' => $asset->pivot?->position ?? 0,
                    'view_url' => $viewData['view_url'] ?? null,
                    'expires_at' => $viewData['expires_at'] ?? null,
                ];
            })
            ->values();

        return response()->json($payload);
    }
}
