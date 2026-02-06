<?php

namespace App\Http\Controllers;

use App\Models\Reaction;
use Illuminate\Http\Request;

class ReactionController extends Controller
{
    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'reactable_type' => 'required|in:content,comment',
            'reactable_id' => 'required|integer',
            'type' => 'sometimes|string|in:like,love,fire',
        ]);

        $morphType = $validated['reactable_type'] === 'content'
            ? \App\Models\Content::class
            : \App\Models\Comment::class;

        $existing = Reaction::where('user_id', $request->user()->id)
            ->where('reactable_type', $morphType)
            ->where('reactable_id', $validated['reactable_id'])
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['status' => 'removed', 'active' => false]);
        }

        Reaction::create([
            'user_id' => $request->user()->id,
            'reactable_type' => $morphType,
            'reactable_id' => $validated['reactable_id'],
            'type' => $validated['type'] ?? 'like',
        ]);

        return response()->json(['status' => 'added', 'active' => true]);
    }
}
