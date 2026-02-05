<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Services\Media\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CreatorMediaController extends Controller
{
    public function initiate(Request $request, MediaService $service)
    {
        $data = $request->validate([
            'type' => ['required', 'in:image,video'],
            'mime_type' => ['required', 'string', 'max:100'],
            'original_filename' => ['required', 'string', 'max:255'],
            'size_bytes' => ['nullable', 'integer', 'min:1'],
        ]);

        $result = $service->initiateUpload(
            $request->user(),
            $data['type'],
            $data['mime_type'],
            $data['original_filename'],
            $data['size_bytes'] ?? null
        );

        return response()->json($result, Response::HTTP_CREATED);
    }

    public function attach(Content $content, Request $request, MediaService $service)
    {
        $data = $request->validate([
            'media_asset_id' => ['required', 'integer', 'exists:media_assets,id'],
            'position' => ['nullable', 'integer', 'min:0'],
        ]);

        $service->attachToContent(
            $request->user(),
            $content,
            (int) $data['media_asset_id'],
            (int) ($data['position'] ?? 0)
        );

        return response()->json(['ok' => true]);
    }
}
