<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use App\Services\ContentPresenter;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index(Request $request, ContentPresenter $presenter)
    {
        $bookmarks = Bookmark::where('user_id', $request->user()->id)
            ->with('content.creator')
            ->latest()
            ->paginate(20);

        $items = $bookmarks->getCollection()->map(
            fn (Bookmark $b) => $presenter->present($b->content, $request->user())
        )->values();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $bookmarks->currentPage(),
                'per_page' => $bookmarks->perPage(),
                'total' => $bookmarks->total(),
            ],
        ]);
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'content_id' => 'required|exists:contents,id',
        ]);

        $existing = Bookmark::where('user_id', $request->user()->id)
            ->where('content_id', $validated['content_id'])
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['status' => 'removed', 'active' => false]);
        }

        Bookmark::create([
            'user_id' => $request->user()->id,
            'content_id' => $validated['content_id'],
        ]);

        return response()->json(['status' => 'added', 'active' => true]);
    }
}
