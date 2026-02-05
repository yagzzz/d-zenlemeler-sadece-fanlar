<?php

use App\Models\Content;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('applies subscriber and tier gating in feed contracts', function () {
    config()->set('features.flags.feed', true);
    config()->set('features.flags.access_engine', true);

    $creator = User::factory()->creator()->create();

    $tier1 = Tier::create([
        'creator_id' => $creator->id,
        'name' => 'Tier 1',
        'description' => null,
        'tier_level' => 1,
        'price_atomic' => 1000,
        'currency' => 'XMR',
        'duration_days' => 30,
        'is_active' => true,
        'position' => 1,
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
        'position' => 2,
    ]);

    Content::factory()->for($creator, 'creator')->published()->create([
        'visibility' => 'public',
        'published_at' => now()->subMinute(),
    ]);

    Content::factory()->for($creator, 'creator')->published()->create([
        'visibility' => 'subscriber_only',
        'published_at' => now()->subSeconds(30),
    ]);

    Content::factory()->for($creator, 'creator')->published()->create([
        'visibility' => 'tier_only',
        'required_tier_id' => $tier2->id,
        'published_at' => now(),
    ]);

    $anonResponse = $this->get('/feed')->assertOk();
    $anonItems = collect($anonResponse->json('data'));

    $subscriberItem = $anonItems->firstWhere('visibility', 'subscriber_only');
    $tierItem = $anonItems->firstWhere('visibility', 'tier_only');

    expect($subscriberItem['locked'])->toBeTrue();
    expect($subscriberItem['lock_reason'])->toBe('not_logged_in');
    expect($subscriberItem['cta']['type'])->toBe('register');

    expect($tierItem['locked'])->toBeTrue();
    expect($tierItem['lock_reason'])->toBe('not_logged_in');
    expect($tierItem['cta']['type'])->toBe('register');

    $subscriber = User::factory()->create();
    Subscription::create([
        'user_id' => $subscriber->id,
        'creator_id' => $creator->id,
        'tier_id' => $tier1->id,
        'starts_at' => Carbon::now()->subDay(),
        'ends_at' => Carbon::now()->addDays(7),
    ]);

    $tier1Response = $this->actingAs($subscriber)->get('/feed')->assertOk();
    $tier1Items = collect($tier1Response->json('data'));
    $subscriberItem = $tier1Items->firstWhere('visibility', 'subscriber_only');
    $tierItem = $tier1Items->firstWhere('visibility', 'tier_only');

    expect($subscriberItem['locked'])->toBeFalse();
    expect($tierItem['locked'])->toBeTrue();
    expect($tierItem['lock_reason'])->toBe('tier_required');
    expect($tierItem['cta']['type'])->toBe('upgrade');
    expect($tierItem['cta']['required_tier_level'])->toBe(2);

    $subscriber->refresh();
    Subscription::query()
        ->where('user_id', $subscriber->id)
        ->where('creator_id', $creator->id)
        ->update(['tier_id' => $tier2->id]);

    $tier2Response = $this->actingAs($subscriber)->get('/feed')->assertOk();
    $tier2Items = collect($tier2Response->json('data'));
    $tierItem = $tier2Items->firstWhere('visibility', 'tier_only');

    expect($tierItem['locked'])->toBeFalse();
});
