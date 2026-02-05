<?php

use App\Models\Invoice;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('lists creator tips and hides anonymous payer', function () {
    config()->set('features.flags.tips', true);

    $creator = User::factory()->creator()->create([
        'username' => 'tip-creator',
    ]);

    $payer = User::factory()->create();

    $invoice = Invoice::create([
        'payer_id' => $payer->id,
        'payee_id' => $creator->id,
        'invoice_type' => 'tip',
        'status' => 'paid',
        'amount_atomic' => 1500,
        'currency' => 'XMR',
        'address' => 'address',
        'payment_reference' => 'ref-1',
        'expires_at' => now()->addMinutes(10),
        'paid_at' => now(),
        'metadata' => [],
    ]);

    Tip::create([
        'payer_id' => $payer->id,
        'creator_id' => $creator->id,
        'content_id' => null,
        'invoice_id' => $invoice->id,
        'amount_atomic' => 1500,
        'currency' => 'XMR',
        'message' => 'Nice',
        'is_anonymous' => true,
    ]);

    $response = $this->actingAs($creator)
        ->get('/api/creators/tip-creator/tips')
        ->assertOk();

    $item = $response->json('data.0');

    expect($item['is_anonymous'])->toBeTrue();
    expect($item['payer'])->toBeNull();
});
