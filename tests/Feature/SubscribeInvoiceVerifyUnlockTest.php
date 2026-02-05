<?php

use App\Models\Content;
use App\Models\Invoice;
use App\Models\Tier;
use App\Models\User;
use App\Services\Payments\MockPaymentSimulator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('unlocks tier content after subscription invoice verify', function () {
    config()->set('features.flags.tier_ux', true);
    config()->set('features.flags.tiers', true);
    config()->set('features.flags.payments_core', true);
    config()->set('features.flags.feed', true);
    config()->set('features.flags.access_engine', true);

    $creator = User::factory()->creator()->create([
        'username' => 'tier-locker',
    ]);

    $tier2 = Tier::create([
        'creator_id' => $creator->id,
        'name' => 'Tier 2',
        'description' => null,
        'tier_level' => 2,
        'price_atomic' => 2000,
        'currency' => 'XMR',
        'duration_days' => 30,
        'is_active' => true,
        'position' => 0,
    ]);

    Content::factory()->for($creator, 'creator')->published()->create([
        'visibility' => 'tier_only',
        'required_tier_id' => $tier2->id,
    ]);

    $subscriber = User::factory()->create();

    $response = $this->actingAs($subscriber)
        ->post('/api/creators/tier-locker/subscribe/invoice', [
            'tier_id' => $tier2->id,
            'billing' => 'monthly',
        ])
        ->assertCreated();

    $invoice = Invoice::findOrFail($response->json('invoice_id'));

    app(MockPaymentSimulator::class)->markPaid($invoice->payment_reference, $invoice->amount_atomic);

    $this->actingAs($subscriber)
        ->post('/payments/verify', ['payment_reference' => $invoice->payment_reference])
        ->assertOk();

    $feed = $this->actingAs($subscriber)
        ->get('/feed')
        ->assertOk();

    $item = collect($feed->json('data'))->firstWhere('visibility', 'tier_only');

    expect($item['locked'])->toBeFalse();
});
