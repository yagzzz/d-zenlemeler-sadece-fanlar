<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows approved creator to manage tiers and blocks others', function () {
    config()->set('features.flags.tiers', true);

    $creator = User::factory()->creator()->create();
    $other = User::factory()->creator()->create();

    $create = $this->actingAs($creator)
        ->post('/creator/tiers', [
            'name' => 'Gold',
            'description' => 'Gold tier',
            'tier_level' => 1,
            'price_atomic' => 2500,
            'currency' => 'XMR',
            'duration_days' => 30,
            'position' => 1,
        ])
        ->assertCreated();

    $tierId = $create->json('tier.id');

    $this->actingAs($creator)
        ->get('/creator/tiers')
        ->assertOk()
        ->assertJsonPath('data.0.id', $tierId);

    $this->actingAs($creator)
        ->patch("/creator/tiers/{$tierId}", [
            'name' => 'Platinum',
            'price_atomic' => 3000,
        ])
        ->assertOk()
        ->assertJsonPath('tier.name', 'Platinum');

    $this->actingAs($creator)
        ->delete("/creator/tiers/{$tierId}")
        ->assertOk()
        ->assertJsonPath('tier.is_active', false);

    $this->actingAs($other)
        ->patch("/creator/tiers/{$tierId}", [
            'name' => 'Hacked',
        ])
        ->assertForbidden();
});

it('blocks unapproved creator', function () {
    config()->set('features.flags.tiers', true);

    $creator = User::factory()->create([
        'role' => 'creator',
        'creator_approved_at' => null,
    ]);

    $this->actingAs($creator)
        ->get('/creator/tiers')
        ->assertForbidden();
});
