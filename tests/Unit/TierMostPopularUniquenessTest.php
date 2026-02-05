<?php

use App\Models\Tier;
use App\Models\User;
use App\Services\TierService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('ensures only one tier is most popular per creator', function () {
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
        'position' => 0,
        'is_most_popular' => true,
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
        'is_most_popular' => false,
    ]);

    $service = app(TierService::class);
    $service->updateTier($creator, $tier2, ['is_most_popular' => true]);

    $tier1->refresh();
    $tier2->refresh();

    expect($tier1->is_most_popular)->toBeFalse();
    expect($tier2->is_most_popular)->toBeTrue();
});
