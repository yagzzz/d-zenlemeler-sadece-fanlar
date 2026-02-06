<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\User;
use Illuminate\Http\Request;

class ExploreController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query('q');
        $filter = $request->query('filter', 'trending'); // trending, latest, creators

        $creatorsQuery = User::where('role', 'creator')
            ->whereNotNull('creator_approved_at')
            ->with('creatorProfile');

        if ($query) {
            $creatorsQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('username', 'like', "%{$query}%");
            });
        }

        $creators = $creatorsQuery->take(12)->get();

        $contentsQuery = Content::where('is_published', true)
            ->where('visibility', 'public')
            ->with('creator');

        if ($query) {
            $contentsQuery->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('body', 'like', "%{$query}%");
            });
        }

        // Add counts for trending scoring
        $contentsQuery->withCount(['reactions', 'comments', 'bookmarks']);

        if ($filter === 'latest') {
            $contentsQuery->latest('published_at');
        } else {
            // Trending: score = reactions + comments + bookmarks
            $contentsQuery->orderByRaw('(reactions_count + comments_count + bookmarks_count) DESC')
                ->latest('published_at');
        }

        $contents = $contentsQuery->take(20)->get();

        if ($request->expectsJson()) {
            return response()->json([
                'creators' => $creators->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'username' => $c->username,
                    'tagline' => $c->creatorProfile?->tagline,
                ]),
                'contents' => $contents->map(fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->title,
                    'body' => \Illuminate\Support\Str::limit($c->body, 100),
                    'creator_name' => $c->creator?->name,
                    'creator_username' => $c->creator?->username,
                    'reactions_count' => $c->reactions_count,
                    'comments_count' => $c->comments_count,
                    'bookmarks_count' => $c->bookmarks_count,
                    'trending_score' => $c->reactions_count + $c->comments_count + $c->bookmarks_count,
                ]),
                'filter' => $filter,
            ]);
        }

        return view('pages.explore', compact('creators', 'contents', 'filter'));
    }
}
