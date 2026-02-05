<?php

use App\Models\Content;
use App\Models\MediaAsset;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('denies media url for subscriber_only content and allows after subscription', function () {
    config()->set('features.flags.media_core', true);
    config()->set('features.flags.access_engine', true);

    $creator = User::factory()->creator()->create();

    $content = Content::factory()->create([
        'creator_id' => $creator->id,
        'visibility' => 'subscriber_only',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $initiate = $this->actingAs($creator)
        ->post('/creator/media/initiate', [
            'type' => 'video',
            'mime_type' => 'video/mp4',
            'original_filename' => 'clip.mp4',
        ])
        ->assertCreated();

    $mediaAssetId = $initiate->json('media_asset_id');
    $asset = MediaAsset::findOrFail($mediaAssetId);

    $this->actingAs($creator)
        ->post("/creator/contents/{$content->id}/media/attach", [
            'media_asset_id' => $asset->id,
        ])
        ->assertOk();

    $this->get("/media/{$asset->id}/url")
        ->assertForbidden()
        ->assertJson([
            'reason' => 'subscription_required',
        ]);

    $user = User::factory()->create();

    Subscription::create([
        'user_id' => $user->id,
        'creator_id' => $creator->id,
        'starts_at' => Carbon::now()->subDay(),
        'ends_at' => Carbon::now()->addDays(7),
    ]);

    $this->actingAs($user)
        ->get("/media/{$asset->id}/url")
        ->assertOk()
        ->assertJsonStructure([
            'view_url',
            'expires_at',
        ]);
});
