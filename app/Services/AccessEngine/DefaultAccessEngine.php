<?php

namespace App\Services\AccessEngine;

use App\Models\Content;
use App\Models\Purchase;
use App\Models\Subscription;
use App\Models\Tier;

class DefaultAccessEngine implements AccessEngine
{
    public function decide(AccessRequest $request): AccessDecision
    {
        if (! config('features.flags.access_engine')) {
            return new AccessDecision(true, 'feature_disabled');
        }

        return match ($request->visibility) {
            'public' => new AccessDecision(true),
            'registered_only' => $this->decideRegisteredOnly($request),
            'subscriber_only' => $this->decideSubscriberOnly($request),
            'tier_only' => $this->decideTierOnly($request),
            'ppv' => $this->decidePpv($request),
            default => new AccessDecision(false, 'invalid_visibility'),
        };
    }

    private function decideRegisteredOnly(AccessRequest $request): AccessDecision
    {
        if ($request->user === null) {
            return new AccessDecision(false, 'not_logged_in');
        }

        if ($request->user->email_verified_at === null) {
            return new AccessDecision(false, 'not_verified');
        }

        return new AccessDecision(true);
    }

    private function decideSubscriberOnly(AccessRequest $request): AccessDecision
    {
        if ($request->user === null) {
            return new AccessDecision(false, 'not_logged_in');
        }

        $creatorId = $request->creatorId ?? $this->resolveCreatorId($request);

        if ($creatorId === null) {
            return new AccessDecision(false, 'subscription_required');
        }

        $hasSubscription = Subscription::query()
            ->where('user_id', $request->user->id)
            ->where('creator_id', $creatorId)
            ->where('ends_at', '>', now())
            ->exists();

        if (! $hasSubscription) {
            return new AccessDecision(false, 'subscription_required');
        }

        return new AccessDecision(true);
    }

    private function decideTierOnly(AccessRequest $request): AccessDecision
    {
        if ($request->user === null) {
            return new AccessDecision(false, 'not_logged_in');
        }

        $content = $this->resolveContent($request);

        if (! $content) {
            return new AccessDecision(false, 'tier_required');
        }

        $subscription = Subscription::query()
            ->where('user_id', $request->user->id)
            ->where('creator_id', $content->creator_id)
            ->where('ends_at', '>', now())
            ->first();

        if (! $subscription) {
            return new AccessDecision(false, 'subscription_required');
        }

        $requiredTier = $this->resolveRequiredTier($content);

        if (! $requiredTier) {
            return new AccessDecision(false, 'tier_required');
        }

        $subscriptionTier = $subscription->tier_id
            ? Tier::query()->find($subscription->tier_id)
            : null;

        if (! $subscriptionTier || $subscriptionTier->tier_level < $requiredTier->tier_level) {
            return new AccessDecision(false, 'tier_required', [
                'required_level' => $requiredTier->tier_level,
                'required_name' => $requiredTier->name,
            ]);
        }

        return new AccessDecision(true);
    }

    private function decidePpv(AccessRequest $request): AccessDecision
    {
        if ($request->user === null || $request->contentId === null) {
            return $request->user === null
                ? new AccessDecision(false, 'not_logged_in')
                : new AccessDecision(false, 'ppv_required');
        }

        $hasPurchase = Purchase::query()
            ->where('user_id', $request->user->id)
            ->where('content_id', $request->contentId)
            ->where(function ($query) {
                $query->whereNull('access_expires_at')
                    ->orWhere('access_expires_at', '>', now());
            })
            ->exists();

        if (! $hasPurchase) {
            return new AccessDecision(false, 'ppv_required');
        }

        return new AccessDecision(true);
    }

    private function resolveCreatorId(AccessRequest $request): ?int
    {
        $content = $this->resolveContent($request);

        return $content?->creator_id;
    }

    private function resolveContent(AccessRequest $request): ?Content
    {
        if (! $request->contentId) {
            return null;
        }

        return Content::query()->find($request->contentId);
    }

    private function resolveRequiredTier(Content $content): ?Tier
    {
        if (! $content->required_tier_id) {
            return null;
        }

        return Tier::query()->find($content->required_tier_id);
    }
}
