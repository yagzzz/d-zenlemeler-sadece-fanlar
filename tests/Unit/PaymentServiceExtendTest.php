<?php

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use App\Services\Payments\MockPaymentSimulator;
use App\Services\Payments\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(Tests\TestCase::class, RefreshDatabase::class);

it('extends from existing ends_at when active', function () {
    config()->set('features.flags.payments_core', true);
    config()->set('features.flags.tiers', true);

    $payer = User::factory()->create();
    $creator = User::factory()->creator()->create();

    $tier = Tier::create([
        'creator_id' => $creator->id,
        'name' => 'Base',
        'description' => 'Starter',
        'tier_level' => 1,
        'price_atomic' => 1000,
        'currency' => 'XMR',
        'duration_days' => 10,
        'is_active' => true,
        'position' => 0,
    ]);

    $existing = Subscription::create([
        'user_id' => $payer->id,
        'creator_id' => $creator->id,
        'starts_at' => Carbon::now()->subDays(5),
        'ends_at' => Carbon::now()->addDays(5),
    ]);

    $service = app(PaymentService::class);
    $invoice = $service->createSubscriptionInvoice($payer, $tier);

    app(MockPaymentSimulator::class)->markPaid($invoice->payment_reference, $invoice->amount_atomic);
    $result = $service->verifyInvoiceAndApply($invoice->payment_reference);

    $updated = Subscription::findOrFail($existing->id);

    expect($result['status'])->toBe('paid');
    expect($updated->ends_at->greaterThan($existing->ends_at))->toBeTrue();
});
