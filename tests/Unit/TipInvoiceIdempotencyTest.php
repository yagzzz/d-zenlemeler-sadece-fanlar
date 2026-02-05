<?php

use App\Models\Content;
use App\Models\Tip;
use App\Models\User;
use App\Services\Payments\MockPaymentSimulator;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('creates only one tip when verifying twice', function () {
    config()->set('features.flags.payments_core', true);
    config()->set('features.flags.tips', true);

    $payer = User::factory()->create();
    $creator = User::factory()->creator()->create();
    $content = Content::factory()->create([
        'creator_id' => $creator->id,
    ]);

    $service = app(PaymentService::class);
    $invoice = $service->createTipInvoice($payer, $creator, 1500, $content, 'Nice!', true);

    app(MockPaymentSimulator::class)->markPaid($invoice->payment_reference, $invoice->amount_atomic);

    $service->verifyInvoiceAndApply($invoice->payment_reference);
    $service->verifyInvoiceAndApply($invoice->payment_reference);

    $count = Tip::query()->where('invoice_id', $invoice->id)->count();

    expect($count)->toBe(1);
});
