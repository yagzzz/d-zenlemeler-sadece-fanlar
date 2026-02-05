<?php

use App\Models\CreatorApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows a user to apply and an admin to approve', function () {
    config()->set('features.flags.creator_applications', true);

    $user = User::factory()->create();
    $admin = User::factory()->admin()->create();

    $this->actingAs($user)
        ->post('/creator/apply', ['application_text' => 'I want to become a creator.'])
        ->assertCreated();

    $application = CreatorApplication::firstOrFail();

    $this->actingAs($admin)
        ->post("/admin/creator-applications/{$application->id}/approve")
        ->assertOk()
        ->assertJson([
            'id' => $application->id,
            'status' => 'approved',
        ]);

    $application->refresh();
    $user->refresh();

    expect($application->status)->toBe('approved');
    expect($user->role)->toBe('creator');
    expect($user->creator_approved_at)->not->toBeNull();
});
