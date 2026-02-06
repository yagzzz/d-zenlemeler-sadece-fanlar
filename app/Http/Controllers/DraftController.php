<?php

namespace App\Http\Controllers;

use App\Models\Content;
use Illuminate\Http\Request;

class DraftController extends Controller
{
    public function save(Request $request, Content $content)
    {
        if ($content->creator_id !== $request->user()->id) {
            return response()->json(['message' => 'Yetkisiz'], 403);
        }

        $validated = $request->validate([
            'draft_body' => 'required|string|max:50000',
        ]);

        $content->update([
            'draft_body' => $validated['draft_body'],
            'draft_saved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Taslak kaydedildi',
            'draft_saved_at' => $content->fresh()->draft_saved_at?->toISOString(),
        ]);
    }
}
