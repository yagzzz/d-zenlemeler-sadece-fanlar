<?php

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payments\MockPaymentSimulator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates invoice, marks paid, and extends subscription idempotently', function () {
    config()->set('features.flags.payments_core', true);

    $payer = User::factory()->create();
    $creator = User::factory()->creator()->create();

    $response = $this->actingAs($payer)
        ->post('/payments/subscription/invoice', [
            'creator_id' => $creator->id,
            'duration_days' => 10,
        ])
        ->assertCreated();

    $invoiceId = $response->json('invoice_id');
    $invoice = Invoice::findOrFail($invoiceId);

    app(MockPaymentSimulator::class)->markPaid($invoice->payment_reference, $invoice->amount_atomic);

    $verify = $this->actingAs($payer)
        ->post('/payments/verify', ['payment_reference' => $invoice->payment_reference])
        ->assertOk();

    $subscription = Subscription::where('user_id', $payer->id)
        ->where('creator_id', $creator->id)
        ->first();

    expect($subscription)->not->toBeNull();
    expect($verify->json('status'))->toBe('paid');

    $verifyAgain = $this->actingAs($payer)
        ->post('/payments/verify', ['payment_reference' => $invoice->payment_reference])
        ->assertOk();

    $subscription->refresh();
    expect($verifyAgain->json('status'))->toBe('paid');
    expect($subscription->ends_at)->toEqual($subscription->ends_at);
});
