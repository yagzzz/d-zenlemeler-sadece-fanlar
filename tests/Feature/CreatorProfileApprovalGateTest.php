<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('blocks creator profile access when not approved', function () {
    config()->set('features.flags.creator_profile', true);

    $creator = User::factory()->create([
        'role' => 'creator',
        'creator_approved_at' => null,
        'name' => 'unapproved-creator',
    ]);

    $this->actingAs($creator)
        ->get('/creator/profile')
        ->assertForbidden();
});
