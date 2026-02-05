<?php

use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use App\Services\Payments\MockPaymentSimulator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates subscription invoice for tier and extends by tier duration', function () {
    config()->set('features.flags.payments_core', true);
    config()->set('features.flags.tiers', true);

    $payer = User::factory()->create();
    $creator = User::factory()->creator()->create();

    $tier = Tier::create([
        'creator_id' => $creator->id,
        'name' => 'Pro',
        'description' => 'Pro tier',
        'tier_level' => 1,
        'price_atomic' => 1500,
        'currency' => 'XMR',
        'duration_days' => 45,
        'is_active' => true,
        'position' => 0,
    ]);

    $response = $this->actingAs($payer)
        ->post('/payments/subscription/invoice', [
            'tier_id' => $tier->id,
        ])
        ->assertCreated();

    $invoiceId = $response->json('invoice_id');
    $invoice = \App\Models\Invoice::findOrFail($invoiceId);

    app(MockPaymentSimulator::class)->markPaid($invoice->payment_reference, $invoice->amount_atomic);

    $verify = $this->actingAs($payer)
        ->post('/payments/verify', ['payment_reference' => $invoice->payment_reference])
        ->assertOk();

    $subscription = Subscription::where('user_id', $payer->id)
        ->where('creator_id', $creator->id)
        ->first();

    expect($verify->json('status'))->toBe('paid');
    expect($subscription)->not->toBeNull();
    expect($subscription->ends_at->greaterThan(now()->addDays(30)))->toBeTrue();
});
