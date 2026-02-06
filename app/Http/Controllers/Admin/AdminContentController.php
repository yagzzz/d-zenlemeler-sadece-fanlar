<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Content;
use Illuminate\Http\Request;

class AdminContentController extends Controller
{
    /**
     * GET /admin/contents — List all contents.
     */
    public function index(Request $request)
    {
        $query = Content::with('creator')->orderByDesc('created_at');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if ($request->query('status') === 'published') {
            $query->where('is_published', true);
        } elseif ($request->query('status') === 'draft') {
            $query->where('is_published', false);
        }

        $contents = $query->paginate(20)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['contents' => $contents]);
        }

        return view('admin.contents.index', compact('contents'));
    }

    /**
     * POST /admin/contents/{content}/takedown — Take down content.
     */
    public function takedown(Request $request, Content $content)
    {
        $content->update(['is_published' => false]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'İçerik kaldırıldı.']);
        }

        return redirect('/admin/contents')->with('success', 'İçerik kaldırıldı.');
    }

    /**
     * POST /admin/contents/{content}/restore — Restore content.
     */
    public function restore(Request $request, Content $content)
    {
        $content->update(['is_published' => true]);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'İçerik yayına alındı.']);
        }

        return redirect('/admin/contents')->with('success', 'İçerik yayına alındı.');
    }
}
