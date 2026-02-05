<?php

namespace App\Services;

use App\Models\Content;
use App\Models\User;
use App\Services\AccessEngine\AccessEngine;
use App\Services\AccessEngine\AccessRequest;

class ContentPresenter
{
    public function __construct(private AccessEngine $accessEngine) {}

    public function present(Content $content, ?User $user): array
    {
        $decision = $this->accessEngine->decide(new AccessRequest($user, $content->visibility, $content->creator_id, $content->id));
        $preview = $this->buildPreview($decision->granted, $decision->reason, $decision->tierHint);
        $locked = ! $decision->granted;
        $tipsAggregate = $this->buildTipsAggregate($content);

        return [
            'id' => $content->id,
            'creator_id' => $content->creator_id,
            'creator_username' => $content->creator?->username,
            'title' => $content->title,
            'body' => $decision->granted ? $content->body : null,
            'visibility' => $content->visibility,
            'is_published' => $content->is_published,
            'published_at' => optional($content->published_at)->toISOString(),
            'locked' => $locked,
            'lock_reason' => $locked ? $decision->reason : null,
            'cta' => $preview['cta'],
            'tier_hint' => $decision->tierHint,
            'tips' => $tipsAggregate,
            'tip_cta' => [
                'type' => 'tip',
                'min_atomic' => (int) config('tips.min_atomic', 1000),
            ],
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
            'endpoint' => $ctaType === 'upgrade' ? '/api/creators/{username}/tiers/compare' : null,
        ], fn ($value) => $value !== null);

        return ['type' => 'blur', 'cta' => $cta];
    }

    private function buildTipsAggregate(Content $content): array
    {
        $aggregate = \App\Models\Tip::query()
            ->where('content_id', $content->id)
            ->selectRaw('COUNT(*) as tip_count, COALESCE(SUM(amount_atomic), 0) as total_atomic')
            ->first();

        return [
            'count' => (int) ($aggregate->tip_count ?? 0),
            'total_atomic' => (int) ($aggregate->total_atomic ?? 0),
        ];
    }
}
