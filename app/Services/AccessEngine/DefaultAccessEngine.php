<?php

namespace App\Services\AccessEngine;

use App\Models\Subscription;

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
            'ppv' => new AccessDecision(false, 'ppv_required'),
            default => new AccessDecision(false, 'invalid_visibility'),
        };
    }

    private function decideRegisteredOnly(AccessRequest $request): AccessDecision
    {
        if ($request->user === null) {
            return new AccessDecision(false, 'not_logged_in');
        }

        return new AccessDecision(true);
    }

    private function decideSubscriberOnly(AccessRequest $request): AccessDecision
    {
        if ($request->user === null || $request->creatorId === null) {
            return new AccessDecision(false, 'subscription_required');
        }

        $hasSubscription = Subscription::query()
            ->where('user_id', $request->user->id)
            ->where('creator_id', $request->creatorId)
            ->where('ends_at', '>', now())
            ->exists();

        if (! $hasSubscription) {
            return new AccessDecision(false, 'subscription_required');
        }

        return new AccessDecision(true);
    }
}
