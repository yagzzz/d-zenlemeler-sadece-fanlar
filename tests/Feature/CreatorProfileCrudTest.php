<?php

use App\Models\CreatorProfile;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates and updates creator profile with media', function () {
    config()->set('features.flags.creator_profile', true);

    $creator = User::factory()->creator()->create([
        'name' => 'creator-one',
    ]);

    $this->actingAs($creator)
        ->get('/creator/profile')
        ->assertOk()
        ->assertJsonPath('profile.tags', []);

    $avatar = MediaAsset::create([
        'creator_id' => $creator->id,
        'type' => 'image',
        'status' => 'ready',
        'provider' => 'local',
        'object_key' => 'creator/'.$creator->id.'/avatar',
    ]);

    $banner = MediaAsset::create([
        'creator_id' => $creator->id,
        'type' => 'image',
        'status' => 'ready',
        'provider' => 'local',
        'object_key' => 'creator/'.$creator->id.'/banner',
    ]);

    $update = $this->actingAs($creator)
        ->put('/creator/profile', [
            'bio' => 'Hello world',
            'tagline' => 'Creator tagline',
            'categories' => ['art', 'music'],
            'tags' => ['indie'],
            'social_links' => [
                'instagram' => 'https://instagram.com/example',
            ],
            'wallet_xmr_address' => '44Affq5kSiGBoZ...',
            'default_subscription_price_atomic' => 1000,
            'allow_free_content' => true,
            'allow_registered_only' => true,
            'allow_ppv' => true,
            'allow_tips' => true,
            'avatar_media_id' => $avatar->id,
            'banner_media_id' => $banner->id,
        ])
        ->assertOk();

    $profile = CreatorProfile::where('user_id', $creator->id)->first();

    expect($profile)->not->toBeNull();
    expect($profile->avatar_media_id)->toBe($avatar->id);
    expect($profile->banner_media_id)->toBe($banner->id);
    expect($update->json('profile.bio'))->toBe('Hello world');
});
