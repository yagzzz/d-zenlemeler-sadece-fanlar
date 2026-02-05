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
            'subscriber_only' => new AccessDecision(false, 'not_implemented'),
            'ppv' => new AccessDecision(false, 'not_implemented'),
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
}
