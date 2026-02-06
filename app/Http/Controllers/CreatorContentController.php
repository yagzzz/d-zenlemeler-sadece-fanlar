<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Services\ContentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CreatorContentController extends Controller
{
    public function store(Request $request, ContentService $service)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'visibility' => ['required', 'in:public,registered_only,subscriber_only,ppv,tier_only'],
            'ppv_price_atomic' => ['nullable', 'integer', 'min:1', 'required_if:visibility,ppv'],
            'ppv_currency' => ['nullable', 'string', 'max:10'],
            'required_tier_id' => ['nullable', 'string', 'exists:tiers,id', 'required_if:visibility,tier_only'],
        ]);

        $content = $service->createContent($request->user(), $data);

        return response()->json([
            'id' => $content->id,
            'visibility' => $content->visibility,
            'is_published' => $content->is_published,
        ], Response::HTTP_CREATED);
    }

    public function update(Content $content, Request $request, ContentService $service)
    {
        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'body' => ['nullable', 'string'],
            'visibility' => ['sometimes', 'in:public,registered_only,subscriber_only,ppv,tier_only'],
            'ppv_price_atomic' => ['nullable', 'integer', 'min:1', 'required_if:visibility,ppv'],
            'ppv_currency' => ['nullable', 'string', 'max:10'],
            'required_tier_id' => ['nullable', 'string', 'exists:tiers,id', 'required_if:visibility,tier_only'],
        ]);

        $updated = $service->updateContent($request->user(), $content, $data);

        return response()->json([
            'id' => $updated->id,
            'visibility' => $updated->visibility,
            'is_published' => $updated->is_published,
        ]);
    }

    public function publish(Content $content, Request $request, ContentService $service)
    {
        $updated = $service->publishContent($request->user(), $content);

        return response()->json([
            'id' => $updated->id,
            'is_published' => $updated->is_published,
            'published_at' => optional($updated->published_at)->toISOString(),
        ]);
    }

    public function destroy(Content $content, Request $request)
    {
        if ($content->creator_id !== $request->user()->id) {
            abort(Response::HTTP_FORBIDDEN, 'Bu içeriği silme yetkiniz yok.');
        }

        $content->delete();

        return response()->json(['deleted' => true]);
    }
}
