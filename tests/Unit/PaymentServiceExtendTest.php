<?php

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use App\Services\Payments\MockPaymentSimulator;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

it('extends from existing ends_at when active', function () {
    config()->set('features.flags.payments_core', true);

    $payer = User::factory()->create();
    $creator = User::factory()->creator()->create();

    $existing = Subscription::create([
        'user_id' => $payer->id,
        'creator_id' => $creator->id,
        'starts_at' => Carbon::now()->subDays(5),
        'ends_at' => Carbon::now()->addDays(5),
    ]);

    $service = app(PaymentService::class);
    $invoice = $service->createSubscriptionInvoice($payer, $creator, 10, 1000);

    app(MockPaymentSimulator::class)->markPaid($invoice->payment_reference, $invoice->amount_atomic);
    $result = $service->verifyInvoiceAndApply($invoice->payment_reference);

    $updated = Subscription::findOrFail($existing->id);

    expect($result['status'])->toBe('paid');
    expect($updated->ends_at->greaterThan($existing->ends_at))->toBeTrue();
});
