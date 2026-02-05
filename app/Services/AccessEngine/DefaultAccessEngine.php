<?php

namespace App\Services\AccessEngine;

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
            'subscriber_only' => new AccessDecision(false, 'subscription_required'),
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
}
