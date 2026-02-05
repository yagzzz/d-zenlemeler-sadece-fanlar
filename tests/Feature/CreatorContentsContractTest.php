<?php

use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns creator contents contract for guests', function () {
    config()->set('features.flags.feed', true);
    config()->set('features.flags.access_engine', true);

    $creator = User::factory()->create();

    Content::factory()
        ->for($creator, 'creator')
        ->published()
        ->state([
            'visibility' => 'public',
            'published_at' => now(),
        ])
        ->create();

    $response = $this->get("/creators/{$creator->id}/contents")
        ->assertOk();

    $response->assertJsonCount(1, 'data');

    $item = $response->json('data.0');

    expect($item['creator_id'])->toBe($creator->id);
    expect($item['visibility'])->toBe('public');
    expect($item['access']['granted'])->toBeTrue();
    expect($item['preview'])->toBe([
        'type' => 'none',
        'cta' => null,
    ]);
    expect($item['tips'])->toBe([
        'count' => 0,
        'total_atomic' => 0,
    ]);
    expect($item['tip_cta'])->toBe([
        'type' => 'tip',
        'min_atomic' => (int) config('tips.min_atomic', 1000),
    ]);
});
