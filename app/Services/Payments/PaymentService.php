<?php

namespace App\Services\Payments;

use App\Models\Invoice;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function __construct(private PaymentGateway $gateway)
    {
    }

    public function createSubscriptionInvoice(User $payer, User $creator, int $durationDays, int $priceAtomic): Invoice
    {
        $expiresAt = Carbon::now()->addMinutes(30);
        $session = $this->gateway->createInvoice($priceAtomic, 'XMR', $expiresAt, [
            'invoice_type' => 'subscription',
            'duration_days' => $durationDays,
            'creator_id' => $creator->id,
        ]);

        $platformFee = (int) round($priceAtomic * 0.06);

        return Invoice::create([
            'payer_id' => $payer->id,
            'payee_id' => $creator->id,
            'invoice_type' => 'subscription',
            'status' => 'pending',
            'amount_atomic' => $priceAtomic,
            'currency' => 'XMR',
            'address' => $session->address,
            'payment_reference' => $session->reference,
            'expires_at' => $expiresAt,
            'metadata' => [
                'duration_days' => $durationDays,
                'platform_fee_atomic' => $platformFee,
            ],
        ]);
    }

    public function verifyInvoiceAndApply(string $reference): array
    {
        $invoice = Invoice::query()->where('payment_reference', $reference)->first();

        if (! $invoice) {
            throw (new ModelNotFoundException())->setModel(Invoice::class);
        }

        if ($invoice->status === 'paid') {
            $subscription = $this->getSubscription($invoice);

            return [
                'status' => 'paid',
                'subscription_ends_at' => $subscription?->ends_at,
            ];
        }

        if ($invoice->status !== 'pending') {
            return [
                'status' => $invoice->status,
                'subscription_ends_at' => null,
            ];
        }

        if ($invoice->expires_at->isPast()) {
            $invoice->update(['status' => 'expired']);

            return [
                'status' => 'expired',
                'subscription_ends_at' => null,
            ];
        }

        $status = $this->gateway->verify($reference);

        if (! $status->paid || $status->paidAmountAtomic < $invoice->amount_atomic) {
            return [
                'status' => 'pending',
                'subscription_ends_at' => null,
            ];
        }

        return DB::transaction(function () use ($invoice) {
            $invoice->refresh();

            if ($invoice->status === 'paid') {
                $subscription = $this->getSubscription($invoice);

                return [
                    'status' => 'paid',
                    'subscription_ends_at' => $subscription?->ends_at,
                ];
            }

            $invoice->update([
                'status' => 'paid',
                'paid_at' => Carbon::now(),
            ]);

            $subscription = $this->extendSubscription($invoice);

            return [
                'status' => 'paid',
                'subscription_ends_at' => $subscription->ends_at,
            ];
        });
    }

    private function extendSubscription(Invoice $invoice): Subscription
    {
        $durationDays = (int) ($invoice->metadata['duration_days'] ?? 0);
        $now = Carbon::now();

        $subscription = Subscription::query()->firstOrNew([
            'user_id' => $invoice->payer_id,
            'creator_id' => $invoice->payee_id,
        ]);

        if (! $subscription->exists || $subscription->ends_at === null || $subscription->ends_at->isPast()) {
            $subscription->starts_at = $now;
            $subscription->ends_at = $now->copy()->addDays($durationDays);
        } else {
            $subscription->ends_at = $subscription->ends_at->copy()->addDays($durationDays);
        }

        $subscription->last_invoice_id = $invoice->id;
        $subscription->save();

        return $subscription;
    }

    private function getSubscription(Invoice $invoice): ?Subscription
    {
        return Subscription::query()
            ->where('user_id', $invoice->payer_id)
            ->where('creator_id', $invoice->payee_id)
            ->first();
    }
}
