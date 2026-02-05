<?php

namespace App\Services;

use App\Models\Content;
use App\Models\User;
use App\Services\AccessEngine\AccessEngine;
use App\Services\AccessEngine\AccessRequest;

class ContentPresenter
{
    public function __construct(private AccessEngine $accessEngine)
    {
    }

    public function present(Content $content, ?User $user): array
    {
        $decision = $this->accessEngine->decide(new AccessRequest($user, $content->visibility, $content->creator_id));
        $preview = $this->buildPreview($decision->granted, $decision->reason);

        return [
            'id' => $content->id,
            'creator_id' => $content->creator_id,
            'title' => $content->title,
            'body' => $decision->granted ? $content->body : null,
            'visibility' => $content->visibility,
            'is_published' => $content->is_published,
            'published_at' => optional($content->published_at)->toISOString(),
            'access' => [
                'granted' => $decision->granted,
                'reason' => $decision->reason,
            ],
            'preview' => $preview,
        ];
    }

    private function buildPreview(bool $granted, ?string $reason): array
    {
        if ($granted) {
            return ['type' => 'none', 'cta' => null];
        }

        $cta = match ($reason) {
            'not_logged_in' => 'login',
            'subscription_required' => 'subscribe',
            'ppv_required' => 'buy_ppv',
            default => null,
        };

        return ['type' => 'blur', 'cta' => $cta];
    }
}
