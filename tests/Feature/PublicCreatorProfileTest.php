<?php

use App\Models\Content;
use App\Models\CreatorProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns public creator profile contract', function () {
    config()->set('features.flags.creator_profile', true);

    $creator = User::factory()->creator()->create([
        'name' => 'creator-public',
        'username' => 'creator-public',
    ]);

    CreatorProfile::create([
        'user_id' => $creator->id,
        'bio' => 'Bio text',
        'tagline' => 'Tagline',
        'categories' => ['art'],
        'tags' => ['tag1'],
        'social_links' => ['website' => 'https://example.com'],
        'default_subscription_price_atomic' => 1234,
    ]);

    Content::factory()->count(2)->create([
        'creator_id' => $creator->id,
    ]);

    $this->get('/creators/creator-public/profile')
        ->assertOk()
        ->assertJsonStructure([
            'creator' => ['id', 'username', 'display_name', 'avatar_url'],
            'profile' => ['bio', 'tagline', 'categories', 'tags', 'social_links'],
            'monetization' => ['default_subscription_price_atomic', 'currency', 'allow_ppv', 'allow_tips', 'allow_registered_only'],
            'stats' => ['content_count'],
        ])
        ->assertJsonPath('creator.username', 'creator-public')
        ->assertJsonPath('stats.content_count', 2);
});

it('resolves public profile strictly by username', function () {
    config()->set('features.flags.creator_profile', true);

    $first = User::factory()->creator()->create([
        'name' => 'Same Display',
        'username' => 'unique-one',
    ]);

    $second = User::factory()->creator()->create([
        'name' => 'Same Display',
        'username' => 'unique-two',
    ]);

    $this->get('/creators/unique-two/profile')
        ->assertOk()
        ->assertJsonPath('creator.id', $second->id)
        ->assertJsonPath('creator.username', 'unique-two');

    $this->get('/creators/Same Display/profile')
        ->assertNotFound();
});
