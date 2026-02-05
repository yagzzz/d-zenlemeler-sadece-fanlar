<?php

use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns only active tiers for creator username', function () {
    config()->set('features.flags.tiers', true);

    $creator = User::factory()->creator()->create([
        'username' => 'tiered-creator',
        'name' => 'Same Name',
    ]);

    Tier::create([
        'creator_id' => $creator->id,
        'name' => 'Active',
        'description' => null,
        'tier_level' => 1,
        'price_atomic' => 1000,
        'currency' => 'XMR',
        'duration_days' => 30,
        'is_active' => true,
        'position' => 1,
    ]);

    Tier::create([
        'creator_id' => $creator->id,
        'name' => 'Inactive',
        'description' => null,
        'tier_level' => 2,
        'price_atomic' => 2000,
        'currency' => 'XMR',
        'duration_days' => 30,
        'is_active' => false,
        'position' => 2,
    ]);

    $this->get('/creators/tiered-creator/tiers')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.name', 'Active');
});

it('does not resolve by display name', function () {
    config()->set('features.flags.tiers', true);

    $creator = User::factory()->creator()->create([
        'username' => 'unique-tier-username',
        'name' => 'Display Name',
    ]);

    Tier::create([
        'creator_id' => $creator->id,
        'name' => 'Active',
        'description' => null,
        'tier_level' => 1,
        'price_atomic' => 1000,
        'currency' => 'XMR',
        'duration_days' => 30,
        'is_active' => true,
        'position' => 1,
    ]);

    $this->get('/creators/Display Name/tiers')
        ->assertNotFound();
});
