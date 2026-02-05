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
        $decision = $this->accessEngine->decide(new AccessRequest($user, $content->visibility, $content->creator_id, $content->id));
        $preview = $this->buildPreview($decision->granted, $decision->reason, $decision->tierHint);
        $locked = ! $decision->granted;

        return [
            'id' => $content->id,
            'creator_id' => $content->creator_id,
            'title' => $content->title,
            'body' => $decision->granted ? $content->body : null,
            'visibility' => $content->visibility,
            'is_published' => $content->is_published,
            'published_at' => optional($content->published_at)->toISOString(),
            'locked' => $locked,
            'lock_reason' => $locked ? $decision->reason : null,
            'cta' => $preview['cta'],
            'tier_hint' => $decision->tierHint,
            'access' => [
                'granted' => $decision->granted,
                'reason' => $decision->reason,
            ],
            'preview' => $preview,
        ];
    }

    private function buildPreview(bool $granted, ?string $reason, ?array $tierHint): array
    {
        if ($granted) {
            return ['type' => 'none', 'cta' => null];
        }

        $ctaType = match ($reason) {
            'not_logged_in', 'not_verified' => 'register',
            'subscription_required' => 'subscribe',
            'tier_required' => 'upgrade',
            'ppv_required' => 'buy_ppv',
            default => null,
        };

        $cta = $ctaType === null ? null : array_filter([
            'type' => $ctaType,
            'required_tier_level' => $tierHint['required_level'] ?? null,
            'required_tier_name' => $tierHint['required_name'] ?? null,
        ], fn ($value) => $value !== null);

        return ['type' => 'blur', 'cta' => $cta];
    }
}
