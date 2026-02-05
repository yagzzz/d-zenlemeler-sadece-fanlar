<?php

use App\Models\Content;
use App\Models\Subscription;
use App\Models\User;
use App\Services\AccessEngine\AccessRequest;
use App\Services\AccessEngine\DefaultAccessEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('denies subscriber_only for anonymous users', function () {
    config()->set('features.flags.access_engine', true);

    $content = Content::factory()->create([
        'visibility' => 'subscriber_only',
    ]);

    $engine = app(DefaultAccessEngine::class);
    $decision = $engine->decide(new AccessRequest(null, 'subscriber_only', $content->creator_id, $content->id));

    expect($decision->granted)->toBeFalse();
    expect($decision->reason)->toBe('not_logged_in');
});

it('denies subscriber_only without active subscription', function () {
    config()->set('features.flags.access_engine', true);

    $user = User::factory()->create();
    $content = Content::factory()->create([
        'visibility' => 'subscriber_only',
    ]);

    $engine = app(DefaultAccessEngine::class);
    $decision = $engine->decide(new AccessRequest($user, 'subscriber_only', $content->creator_id, $content->id));

    expect($decision->granted)->toBeFalse();
    expect($decision->reason)->toBe('subscription_required');
});

it('grants subscriber_only with active subscription', function () {
    config()->set('features.flags.access_engine', true);

    $user = User::factory()->create();
    $creator = User::factory()->creator()->create();
    $content = Content::factory()->create([
        'creator_id' => $creator->id,
        'visibility' => 'subscriber_only',
    ]);

    Subscription::create([
        'user_id' => $user->id,
        'creator_id' => $creator->id,
        'starts_at' => Carbon::now()->subDay(),
        'ends_at' => Carbon::now()->addDays(7),
    ]);

    $engine = app(DefaultAccessEngine::class);
    $decision = $engine->decide(new AccessRequest($user, 'subscriber_only', $content->creator_id, $content->id));

    expect($decision->granted)->toBeTrue();
    expect($decision->reason)->toBeNull();
});
