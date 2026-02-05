<?php

use App\Models\Content;
use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\User;
use App\Services\Payments\MockPaymentSimulator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates ppv invoice, records purchase, and remains idempotent', function () {
    config()->set('features.flags.ppv_core', true);
    config()->set('features.flags.payments_core', true);

    $payer = User::factory()->create();
    $creator = User::factory()->creator()->create();

    $content = Content::factory()->create([
        'creator_id' => $creator->id,
        'visibility' => 'ppv',
        'ppv_price_atomic' => 2500,
        'ppv_currency' => 'XMR',
        'is_published' => true,
    ]);

    $response = $this->actingAs($payer)
        ->post('/payments/ppv/invoice', [
            'content_id' => $content->id,
        ])
        ->assertCreated();

    $invoiceId = $response->json('invoice_id');
    $invoice = Invoice::findOrFail($invoiceId);

    app(MockPaymentSimulator::class)->markPaid($invoice->payment_reference, $invoice->amount_atomic);

    $verify = $this->actingAs($payer)
        ->post('/payments/verify', ['payment_reference' => $invoice->payment_reference])
        ->assertOk();

    $purchase = Purchase::where('user_id', $payer->id)
        ->where('content_id', $content->id)
        ->first();

    expect($purchase)->not->toBeNull();
    expect($verify->json('status'))->toBe('paid');
    expect($verify->json('purchase.content_id'))->toBe($content->id);
    expect($verify->json('purchase.unlocked'))->toBeTrue();

    $verifyAgain = $this->actingAs($payer)
        ->post('/payments/verify', ['payment_reference' => $invoice->payment_reference])
        ->assertOk();

    $purchaseCount = Purchase::where('user_id', $payer->id)
        ->where('content_id', $content->id)
        ->count();

    expect($verifyAgain->json('status'))->toBe('paid');
    expect($purchaseCount)->toBe(1);
});
