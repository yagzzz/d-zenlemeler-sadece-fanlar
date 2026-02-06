<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Content;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Content $content)
    {
        $comments = $content->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user', 'reactions'])
            ->withCount('reactions')
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $comments->getCollection()->map(fn (Comment $c) => $this->format($c)),
            'meta' => [
                'current_page' => $comments->currentPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
            ],
        ]);
    }

    public function store(Request $request, Content $content)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:2000',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = $content->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'parent_id' => $validated['parent_id'] ?? null,
        ]);

        $comment->load(['user', 'reactions']);

        return response()->json($this->format($comment), 201);
    }

    public function destroy(Request $request, Comment $comment)
    {
        if ($comment->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Yetkisiz'], 403);
        }

        $comment->delete();

        return response()->json(['message' => 'Silindi']);
    }

    private function format(Comment $comment): array
    {
        return [
            'id' => $comment->id,
            'user_id' => $comment->user_id,
            'username' => $comment->user?->username ?? $comment->user?->name ?? 'anon',
            'display_name' => $comment->user?->name ?? 'Anonim',
            'body' => $comment->body,
            'reactions_count' => $comment->reactions_count ?? $comment->reactions->count(),
            'created_at' => $comment->created_at?->toISOString(),
            'replies' => $comment->relationLoaded('replies')
                ? $comment->replies->map(fn (Comment $r) => $this->format($r))->values()
                : [],
        ];
    }
}
