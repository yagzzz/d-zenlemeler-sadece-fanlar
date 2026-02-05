<?php

use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a creator to create content', function () {
    config()->set('features.flags.content_core', true);

    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/creator/content', [
            'title' => 'Hello World',
            'body' => 'Test content body',
            'visibility' => 'public',
        ])
        ->assertCreated()
        ->assertJson([
            'visibility' => 'public',
            'is_published' => false,
        ]);

    $content = Content::first();

    expect($content)->not->toBeNull();
    expect($content->creator_id)->toBe($user->id);
    expect($content->title)->toBe('Hello World');
});
