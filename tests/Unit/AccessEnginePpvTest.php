<?php

use App\Models\Content;
use App\Models\Purchase;
use App\Models\User;
use App\Services\AccessEngine\AccessRequest;
use App\Services\AccessEngine\DefaultAccessEngine;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('denies ppv access without purchase', function () {
    config()->set('features.flags.access_engine', true);

    $user = User::factory()->create();
    $content = Content::factory()->create([
        'visibility' => 'ppv',
    ]);

    $engine = app(DefaultAccessEngine::class);
    $decision = $engine->decide(new AccessRequest($user, 'ppv', $content->creator_id, $content->id));

    expect($decision->granted)->toBeFalse();
    expect($decision->reason)->toBe('ppv_required');
});

it('grants ppv access with purchase', function () {
    config()->set('features.flags.access_engine', true);

    $user = User::factory()->create();
    $content = Content::factory()->create([
        'visibility' => 'ppv',
    ]);

    Purchase::create([
        'user_id' => $user->id,
        'content_id' => $content->id,
        'invoice_id' => null,
        'purchased_at' => now(),
        'access_expires_at' => null,
    ]);

    $engine = app(DefaultAccessEngine::class);
    $decision = $engine->decide(new AccessRequest($user, 'ppv', $content->creator_id, $content->id));

    expect($decision->granted)->toBeTrue();
    expect($decision->reason)->toBeNull();
});
