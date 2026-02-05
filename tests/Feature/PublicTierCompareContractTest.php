<?php

use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns tier compare contract with sorted tiers and badges', function () {
    config()->set('features.flags.tier_ux', true);

    $creator = User::factory()->creator()->create([
        'username' => 'compare-creator',
        'name' => 'Compare Creator',
    ]);

    Tier::create([
        'creator_id' => $creator->id,
        'name' => 'Gold',
        'description' => 'Gold tier',
        'tier_level' => 2,
        'price_atomic' => 2000,
        'yearly_price_atomic' => 12000,
        'yearly_duration_days' => 365,
        'currency' => 'XMR',
        'duration_days' => 30,
        'is_active' => true,
        'position' => 2,
        'is_most_popular' => true,
    ]);

    Tier::create([
        'creator_id' => $creator->id,
        'name' => 'Bronze',
        'description' => 'Bronze tier',
        'tier_level' => 1,
        'price_atomic' => 1000,
        'currency' => 'XMR',
        'duration_days' => 30,
        'is_active' => true,
        'position' => 1,
        'is_most_popular' => false,
    ]);

    $response = $this->get('/api/creators/compare-creator/tiers/compare')
        ->assertOk()
        ->assertJsonStructure([
            'creator' => ['username', 'display_name', 'avatar_url'],
            'currency',
            'billing' => ['default', 'options'],
            'tiers',
            'actions' => ['subscribe' => ['endpoint'], 'upgrade' => ['endpoint']],
        ]);

    $tiers = $response->json('tiers');

    expect($tiers[0]['name'])->toBe('Bronze');
    expect($tiers[1]['badges'])->toBe(['En Popüler']);
});
