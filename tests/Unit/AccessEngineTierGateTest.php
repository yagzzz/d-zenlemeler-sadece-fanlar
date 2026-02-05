<?php

use App\Models\Content;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use App\Services\AccessEngine\AccessRequest;
use App\Services\AccessEngine\DefaultAccessEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('denies tier_only when subscription tier too low', function () {
    config()->set('features.flags.access_engine', true);

    $creator = User::factory()->creator()->create();
    $user = User::factory()->create();

    $tier1 = Tier::create([
        'creator_id' => $creator->id,
        'name' => 'Tier 1',
        'description' => null,
        'tier_level' => 1,
        'price_atomic' => 1000,
        'currency' => 'XMR',
        'duration_days' => 30,
        'is_active' => true,
        'position' => 0,
    ]);

    $tier2 = Tier::create([
        'creator_id' => $creator->id,
        'name' => 'Tier 2',
        'description' => null,
        'tier_level' => 2,
        'price_atomic' => 2000,
        'currency' => 'XMR',
        'duration_days' => 30,
        'is_active' => true,
        'position' => 1,
    ]);

    $content = Content::factory()->create([
        'creator_id' => $creator->id,
        'visibility' => 'tier_only',
        'required_tier_id' => $tier2->id,
    ]);

    Subscription::create([
        'user_id' => $user->id,
        'creator_id' => $creator->id,
        'tier_id' => $tier1->id,
        'starts_at' => Carbon::now()->subDay(),
        'ends_at' => Carbon::now()->addDays(7),
    ]);

    $engine = app(DefaultAccessEngine::class);
    $decision = $engine->decide(new AccessRequest($user, 'tier_only', $content->creator_id, $content->id));

    expect($decision->granted)->toBeFalse();
    expect($decision->reason)->toBe('tier_required');
});

it('grants tier_only when subscription tier meets requirement', function () {
    config()->set('features.flags.access_engine', true);

    $creator = User::factory()->creator()->create();
    $user = User::factory()->create();

    $tier2 = Tier::create([
        'creator_id' => $creator->id,
        'name' => 'Tier 2',
        'description' => null,
        'tier_level' => 2,
        'price_atomic' => 2000,
        'currency' => 'XMR',
        'duration_days' => 30,
        'is_active' => true,
        'position' => 1,
    ]);

    $tier3 = Tier::create([
        'creator_id' => $creator->id,
        'name' => 'Tier 3',
        'description' => null,
        'tier_level' => 3,
        'price_atomic' => 3000,
        'currency' => 'XMR',
        'duration_days' => 30,
        'is_active' => true,
        'position' => 2,
    ]);

    $content = Content::factory()->create([
        'creator_id' => $creator->id,
        'visibility' => 'tier_only',
        'required_tier_id' => $tier2->id,
    ]);

    Subscription::create([
        'user_id' => $user->id,
        'creator_id' => $creator->id,
        'tier_id' => $tier3->id,
        'starts_at' => Carbon::now()->subDay(),
        'ends_at' => Carbon::now()->addDays(7),
    ]);

    $engine = app(DefaultAccessEngine::class);
    $decision = $engine->decide(new AccessRequest($user, 'tier_only', $content->creator_id, $content->id));

    expect($decision->granted)->toBeTrue();
    expect($decision->reason)->toBeNull();
});
