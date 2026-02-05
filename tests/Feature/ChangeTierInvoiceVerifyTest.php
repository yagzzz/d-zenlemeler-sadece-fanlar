<?php

use App\Models\Content;
use App\Models\Invoice;
use App\Models\Tier;
use App\Models\User;
use App\Services\Payments\MockPaymentSimulator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('upgrades tier and unlocks content after change-tier invoice verify', function () {
    config()->set('features.flags.tier_ux', true);
    config()->set('features.flags.tiers', true);
    config()->set('features.flags.payments_core', true);
    config()->set('features.flags.feed', true);
    config()->set('features.flags.access_engine', true);

    $creator = User::factory()->creator()->create([
        'username' => 'tier-upgrade',
    ]);

    $tier1 = Tier::create([
        'creator_id' => $creator->id,
        'name' => 'Tier 1',
        'description' => null,
        'tier_level' => 1,
        'price_atomic' => 1000,
        'currency' => 'XMR',
        'duration_days' => 30,
        'is_active' => true,
        'position' => 0,
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
        'position' => 1,
    ]);

    Content::factory()->for($creator, 'creator')->published()->create([
        'visibility' => 'tier_only',
        'required_tier_id' => $tier2->id,
    ]);

    $subscriber = User::factory()->create();

    $subscribe = $this->actingAs($subscriber)
        ->post('/api/creators/tier-upgrade/subscribe/invoice', [
            'tier_id' => $tier1->id,
        ])
        ->assertCreated();

    $subscribeInvoice = Invoice::findOrFail($subscribe->json('invoice_id'));
    app(MockPaymentSimulator::class)->markPaid($subscribeInvoice->payment_reference, $subscribeInvoice->amount_atomic);

    $this->actingAs($subscriber)
        ->post('/payments/verify', ['payment_reference' => $subscribeInvoice->payment_reference])
        ->assertOk();

    $change = $this->actingAs($subscriber)
        ->post('/api/creators/tier-upgrade/subscription/change-tier/invoice', [
            'new_tier_id' => $tier2->id,
        ])
        ->assertCreated();

    $changeInvoice = Invoice::findOrFail($change->json('invoice_id'));
    app(MockPaymentSimulator::class)->markPaid($changeInvoice->payment_reference, $changeInvoice->amount_atomic);

    $this->actingAs($subscriber)
        ->post('/payments/verify', ['payment_reference' => $changeInvoice->payment_reference])
        ->assertOk();

    $feed = $this->actingAs($subscriber)
        ->get('/feed')
        ->assertOk();

    $item = collect($feed->json('data'))->firstWhere('visibility', 'tier_only');

    expect($item['locked'])->toBeFalse();
});
