<?php

use App\Models\Content;
use App\Models\Invoice;
use App\Models\Tip;
use App\Models\User;
use App\Services\Payments\MockPaymentSimulator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates and verifies a content tip invoice and aggregates tips', function () {
    config()->set('features.flags.tips', true);
    config()->set('features.flags.payments_core', true);

    $payer = User::factory()->create();
    $creator = User::factory()->creator()->create([
        'username' => 'tipper',
    ]);

    $content = Content::factory()->create([
        'creator_id' => $creator->id,
    ]);

    $response = $this->actingAs($payer)
        ->post('/api/creators/tipper/tips/invoice', [
            'amount_atomic' => 2000,
            'message' => 'Great post',
            'content_id' => $content->id,
        ])
        ->assertCreated();

    $invoiceId = $response->json('invoice_id');
    $invoice = Invoice::findOrFail($invoiceId);

    app(MockPaymentSimulator::class)->markPaid($invoice->payment_reference, $invoice->amount_atomic);

    $this->actingAs($payer)
        ->post("/api/invoices/{$invoice->id}/verify")
        ->assertOk()
        ->assertJsonPath('status', 'paid')
        ->assertJsonStructure(['tip' => ['id']]);

    $tip = Tip::query()->where('invoice_id', $invoice->id)->first();
    expect($tip)->not->toBeNull();

    $this->get("/api/contents/{$content->id}/tips")
        ->assertOk()
        ->assertJson([
            'count' => 1,
            'total_atomic' => 2000,
        ]);
});
