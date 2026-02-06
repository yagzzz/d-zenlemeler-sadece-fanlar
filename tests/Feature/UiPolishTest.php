<?php

use App\Models\CreatorApplication;
use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders feed and creator pages without vite manifest', function () {
    config()->set('features.flags.ui', true);

    $this->get('/')->assertOk();
    $this->get('/c/demo-creator')->assertOk();
});

it('returns creator analytics payload', function () {
    config()->set('features.flags.analytics_stub', true);

    $creator = User::factory()->creator()->create([
        'username' => 'demo-creator',
    ]);

    $invoice = Invoice::create([
        'payer_id' => User::factory()->create()->id,
        'payee_id' => $creator->id,
        'invoice_type' => 'tip',
        'status' => 'paid',
        'amount_atomic' => 3200,
        'currency' => 'XMR',
        'address' => 'tip-address',
        'payment_reference' => 'tip-ref-1',
        'expires_at' => now()->addMinutes(30),
        'paid_at' => now(),
        'metadata' => ['creator_id' => $creator->id],
    ]);

    Tip::create([
        'payer_id' => User::factory()->create()->id,
        'creator_id' => $creator->id,
        'content_id' => null,
        'invoice_id' => $invoice->id,
        'amount_atomic' => 3200,
        'currency' => 'XMR',
        'message' => null,
        'is_anonymous' => false,
    ]);

    Subscription::create([
        'user_id' => User::factory()->create()->id,
        'creator_id' => $creator->id,
        'tier_id' => null,
        'starts_at' => now(),
        'ends_at' => now()->addDay(),
    ]);

    $response = $this->actingAs($creator)->getJson('/api/creators/demo-creator/analytics');

    $response->assertOk();
    $response->assertJsonStructure([
        'tips_total_atomic',
        'tips_count',
        'active_subscribers',
    ]);
});

it('gates admin creator applications page', function () {
    config()->set('features.flags.creator_applications', true);

    $creator = User::factory()->create();
    $admin = User::factory()->admin()->create();

    CreatorApplication::create([
        'user_id' => $creator->id,
        'status' => 'pending',
        'application_text' => 'Please approve',
    ]);

    $this->actingAs($creator)
        ->get('/admin/creator-applications')
        ->assertForbidden();

    $this->actingAs($admin)
        ->get('/admin/creator-applications')
        ->assertOk()
        ->assertSee('Creator Başvuruları');
});
