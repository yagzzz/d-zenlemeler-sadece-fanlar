<?php

use App\Models\User;
use App\Services\AccessEngine\AccessRequest;
use App\Services\AccessEngine\DefaultAccessEngine;

uses(Tests\TestCase::class);

it('denies registered_only when user is not logged in', function () {
    config()->set('features.flags.access_engine', true);

    $engine = new DefaultAccessEngine();
    $decision = $engine->decide(new AccessRequest(null, 'registered_only'));

    expect($decision->granted)->toBeFalse();
    expect($decision->reason)->toBe('not_logged_in');
});

it('denies registered_only when email is not verified', function () {
    config()->set('features.flags.access_engine', true);

    $user = new User();
    $user->email_verified_at = null;

    $engine = new DefaultAccessEngine();
    $decision = $engine->decide(new AccessRequest($user, 'registered_only'));

    expect($decision->granted)->toBeFalse();
    expect($decision->reason)->toBe('not_verified');
});

it('grants registered_only when email is verified', function () {
    config()->set('features.flags.access_engine', true);

    $user = new User();
    $user->email_verified_at = now();

    $engine = new DefaultAccessEngine();
    $decision = $engine->decide(new AccessRequest($user, 'registered_only'));

    expect($decision->granted)->toBeTrue();
    expect($decision->reason)->toBeNull();
});
