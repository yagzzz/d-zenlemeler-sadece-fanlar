<?php

use App\Models\Content;
use App\Models\MediaAsset;
use App\Models\Purchase;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('denies guest from subscriber-only content show', function () {
    config()->set('features.flags.access_engine', true);
    config()->set('features.flags.content_show', true);

    $creator = User::factory()->creator()->create();
    $content = Content::factory()->create([
        'creator_id' => $creator->id,
        'visibility' => 'subscriber_only',
        'is_published' => true,
    ]);

    $response = $this->getJson("/api/contents/{$content->id}");

    $response->assertStatus(403);
    expect($response->json('granted'))->toBeFalse();
    expect($response->json('reason'))->toBe('not_logged_in');
});

it('allows active subscriber to read content body', function () {
    config()->set('features.flags.access_engine', true);
    config()->set('features.flags.content_show', true);

    $creator = User::factory()->creator()->create();
    $subscriber = User::factory()->create();

    $content = Content::factory()->create([
        'creator_id' => $creator->id,
        'visibility' => 'subscriber_only',
        'is_published' => true,
    ]);

    Subscription::create([
        'user_id' => $subscriber->id,
        'creator_id' => $creator->id,
        'tier_id' => null,
        'starts_at' => now(),
        'ends_at' => now()->addDay(),
    ]);

    $response = $this->actingAs($subscriber)->getJson("/api/contents/{$content->id}");

    $response->assertOk();
    expect($response->json('body'))->toBe($content->body);
});

it('allows tier subscriber to read tier-only content', function () {
    config()->set('features.flags.access_engine', true);
    config()->set('features.flags.content_show', true);

    $creator = User::factory()->creator()->create();
    $subscriber = User::factory()->create();

    $tier = Tier::create([
        'creator_id' => $creator->id,
        'name' => 'Gold',
        'description' => 'Gold tier',
        'tier_level' => 2,
        'price_atomic' => 5000,
        'currency' => 'XMR',
        'duration_days' => 30,
        'is_active' => true,
        'position' => 1,
        'is_most_popular' => false,
    ]);

    $content = Content::factory()->create([
        'creator_id' => $creator->id,
        'visibility' => 'tier_only',
        'required_tier_id' => $tier->id,
        'is_published' => true,
    ]);

    Subscription::create([
        'user_id' => $subscriber->id,
        'creator_id' => $creator->id,
        'tier_id' => $tier->id,
        'starts_at' => now(),
        'ends_at' => now()->addDay(),
    ]);

    $response = $this->actingAs($subscriber)->getJson("/api/contents/{$content->id}");

    $response->assertOk();
    expect($response->json('body'))->toBe($content->body);
});

it('requires ppv purchase before content body is visible', function () {
    config()->set('features.flags.access_engine', true);
    config()->set('features.flags.content_show', true);

    $creator = User::factory()->creator()->create();
    $buyer = User::factory()->create();

    $content = Content::factory()->create([
        'creator_id' => $creator->id,
        'visibility' => 'ppv',
        'ppv_price_atomic' => 2500,
        'ppv_currency' => 'XMR',
        'is_published' => true,
    ]);

    $response = $this->actingAs($buyer)->getJson("/api/contents/{$content->id}");

    $response->assertStatus(403);
    expect($response->json('reason'))->toBe('ppv_required');

    Purchase::create([
        'user_id' => $buyer->id,
        'content_id' => $content->id,
        'invoice_id' => null,
        'purchased_at' => now(),
        'access_expires_at' => null,
    ]);

    $response = $this->actingAs($buyer)->getJson("/api/contents/{$content->id}");

    $response->assertOk();
    expect($response->json('body'))->toBe($content->body);
});

it('denies media view url when content access is denied', function () {
    config()->set('features.flags.access_engine', true);
    config()->set('features.flags.media_core', true);

    $creator = User::factory()->creator()->create();
    $content = Content::factory()->create([
        'creator_id' => $creator->id,
        'visibility' => 'subscriber_only',
        'is_published' => true,
    ]);

    $asset = MediaAsset::create([
        'creator_id' => $creator->id,
        'content_id' => null,
        'type' => 'image',
        'status' => 'ready',
        'provider' => 'local',
        'bucket' => null,
        'object_key' => 'creator/'.$creator->id.'/asset.png',
        'original_filename' => 'asset.png',
        'mime_type' => 'image/png',
        'size_bytes' => 100,
    ]);

    $content->mediaAssets()->attach($asset->id, ['position' => 0]);

    $response = $this->getJson("/media/{$asset->id}/url");

    $response->assertStatus(403);
});

it('allows media view url when content access is granted', function () {
    config()->set('features.flags.access_engine', true);
    config()->set('features.flags.media_core', true);

    $creator = User::factory()->creator()->create();
    $subscriber = User::factory()->create();

    $content = Content::factory()->create([
        'creator_id' => $creator->id,
        'visibility' => 'subscriber_only',
        'is_published' => true,
    ]);

    Subscription::create([
        'user_id' => $subscriber->id,
        'creator_id' => $creator->id,
        'tier_id' => null,
        'starts_at' => now(),
        'ends_at' => now()->addDay(),
    ]);

    $asset = MediaAsset::create([
        'creator_id' => $creator->id,
        'content_id' => null,
        'type' => 'image',
        'status' => 'ready',
        'provider' => 'local',
        'bucket' => null,
        'object_key' => 'creator/'.$creator->id.'/asset.png',
        'original_filename' => 'asset.png',
        'mime_type' => 'image/png',
        'size_bytes' => 100,
    ]);

    $content->mediaAssets()->attach($asset->id, ['position' => 0]);

    $response = $this->actingAs($subscriber)->getJson("/media/{$asset->id}/url");

    $response->assertOk();
    expect($response->json('view_url'))->toBeString();
});
