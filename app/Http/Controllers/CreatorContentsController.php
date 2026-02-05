<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ContentFeedService;
use App\Services\ContentPresenter;
use Illuminate\Http\Request;

class CreatorContentsController extends Controller
{
    public function index(User $user, Request $request, ContentFeedService $feedService, ContentPresenter $presenter)
    {
        $page = max(1, (int) $request->query('page', 1));
        $perPage = (int) $request->query('per_page', 15);
        $perPage = min(max($perPage, 1), 50);

        $paginator = $feedService->getCreatorContents($user, $page, $perPage);
        $items = $paginator->getCollection()
            ->map(fn ($content) => $presenter->present($content, $request->user()))
            ->values();

        return response()->json([
            'data' => $items,
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }
}
