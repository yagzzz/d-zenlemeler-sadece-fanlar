<?php

namespace App\Services;

use App\Models\Content;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class ContentService
{
    public function createContent(User $creator, array $data): Content
    {
        $data['creator_id'] = $creator->id;

        return Content::create($data);
    }

    public function updateContent(User $creator, Content $content, array $data): Content
    {
        $this->authorizeCreator($creator, $content);

        $content->update($data);

        return $content;
    }

    public function publishContent(User $creator, Content $content): Content
    {
        $this->authorizeCreator($creator, $content);

        $content->forceFill([
            'is_published' => true,
            'published_at' => $content->published_at ?? now(),
        ])->save();

        return $content;
    }

    private function authorizeCreator(User $creator, Content $content): void
    {
        if ($content->creator_id !== $creator->id) {
            throw new AuthorizationException('Not allowed to manage this content.');
        }
    }
}
