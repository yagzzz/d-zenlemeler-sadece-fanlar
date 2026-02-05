<?php

use App\Models\Content;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('initiates upload, attaches media, and returns signed url for public content', function () {
    config()->set('features.flags.media_core', true);

    $creator = User::factory()->creator()->create();

    $content = Content::factory()->create([
        'creator_id' => $creator->id,
        'visibility' => 'public',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $initiate = $this->actingAs($creator)
        ->post('/creator/media/initiate', [
            'type' => 'image',
            'mime_type' => 'image/jpeg',
            'original_filename' => 'cover.jpg',
            'size_bytes' => 2048,
        ])
        ->assertCreated();

    $mediaAssetId = $initiate->json('media_asset_id');
    $asset = MediaAsset::findOrFail($mediaAssetId);

    expect($asset->status)->toBe('pending');

    $this->actingAs($creator)
        ->post("/creator/contents/{$content->id}/media/attach", [
            'media_asset_id' => $mediaAssetId,
            'position' => 0,
        ])
        ->assertOk();

    $asset->refresh();
    expect($asset->status)->toBe('ready');

    $this->get("/media/{$mediaAssetId}/url")
        ->assertOk()
        ->assertJsonStructure([
            'view_url',
            'expires_at',
        ]);
});
