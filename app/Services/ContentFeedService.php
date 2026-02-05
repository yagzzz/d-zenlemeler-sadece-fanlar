<?php

namespace App\Services;

use App\Models\Content;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ContentFeedService
{
    public function getFeed(int $page, int $perPage): LengthAwarePaginator
    {
        return Content::query()
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function getCreatorContents(User $creator, int $page, int $perPage): LengthAwarePaginator
    {
        return Content::query()
            ->where('creator_id', $creator->id)
            ->where('is_published', true)
            ->orderByDesc('published_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
