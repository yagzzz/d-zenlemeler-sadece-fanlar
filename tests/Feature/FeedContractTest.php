<?php

use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns feed contract for guest users', function () {
    config()->set('features.flags.feed', true);
    config()->set('features.flags.access_engine', true);

    $creator = User::factory()->create();

    Content::factory()
        ->for($creator, 'creator')
        ->published()
        ->state([
            'visibility' => 'public',
            'published_at' => now()->subMinute(),
        ])
        ->create();

    Content::factory()
        ->for($creator, 'creator')
        ->published()
        ->state([
            'visibility' => 'registered_only',
            'published_at' => now(),
        ])
        ->create();

    $response = $this->get('/feed')->assertOk();

    $response->assertJsonCount(2, 'data');

    $data = $response->json('data');

    expect($data[0]['visibility'])->toBe('registered_only');
    expect($data[0]['access']['granted'])->toBeFalse();
    expect($data[0]['access']['reason'])->toBe('not_logged_in');
    expect($data[0]['preview'])->toBe([
        'type' => 'blur',
        'cta' => 'login',
    ]);
    expect($data[0]['body'])->toBeNull();

    expect($data[1]['visibility'])->toBe('public');
    expect($data[1]['access']['granted'])->toBeTrue();
    expect($data[1]['preview'])->toBe([
        'type' => 'none',
        'cta' => null,
    ]);
});
