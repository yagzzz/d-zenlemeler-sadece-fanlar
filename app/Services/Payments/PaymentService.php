<?php

namespace App\Services\Payments;

use App\Models\Content;
use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\Subscription;
use App\Models\Tier;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    public function __construct(private PaymentGateway $gateway) {}

    public function createSubscriptionInvoice(User $payer, Tier $tier, string $billing = 'monthly'): Invoice
    {
        if (! $tier->is_active) {
            throw ValidationException::withMessages([
                'tier_id' => 'Tier is inactive.',
            ]);
        }

        if ($billing === 'yearly' && ! $tier->yearly_price_atomic) {
            throw ValidationException::withMessages([
                'billing' => 'Yearly billing is not available for this tier.',
            ]);
        }

        $durationDays = $billing === 'yearly'
            ? (int) ($tier->yearly_duration_days ?? 365)
            : (int) $tier->duration_days;
        $priceAtomic = $billing === 'yearly'
            ? (int) $tier->yearly_price_atomic
            : (int) $tier->price_atomic;
        $creatorId = $tier->creator_id;
        $expiresAt = Carbon::now()->addMinutes(30);
        $session = $this->gateway->createInvoice($priceAtomic, 'XMR', $expiresAt, [
            'invoice_type' => 'subscription',
            'duration_days' => $durationDays,
            'creator_id' => $creatorId,
            'tier_id' => $tier->id,
        ]);

        $platformFee = (int) round($priceAtomic * 0.06);

        return Invoice::create([
            'payer_id' => $payer->id,
            'payee_id' => $creatorId,
            'invoice_type' => 'subscription',
            'status' => 'pending',
            'amount_atomic' => $priceAtomic,
            'currency' => $tier->currency ?? 'XMR',
            'address' => $session->address,
            'payment_reference' => $session->reference,
            'expires_at' => $expiresAt,
            'metadata' => [
                'duration_days' => $durationDays,
                'tier_id' => $tier->id,
                'creator_id' => $creatorId,
                'billing' => $billing,
                'platform_fee_atomic' => $platformFee,
            ],
        ]);
    }

    public function createPpvInvoice(User $payer, Content $content): Invoice
    {
        if ($content->visibility !== 'ppv') {
            throw ValidationException::withMessages([
                'content_id' => 'Content is not PPV.',
            ]);
        }

        if (! $content->ppv_price_atomic) {
            throw ValidationException::withMessages([
                'ppv_price_atomic' => 'PPV price is required.',
            ]);
        }

        $expiresAt = Carbon::now()->addMinutes(30);
        $session = $this->gateway->createInvoice(
            (int) $content->ppv_price_atomic,
            $content->ppv_currency ?? 'XMR',
            $expiresAt,
            ['invoice_type' => 'ppv', 'content_id' => $content->id]
        );

        return Invoice::create([
            'payer_id' => $payer->id,
            'payee_id' => $content->creator_id,
            'invoice_type' => 'ppv',
            'status' => 'pending',
            'amount_atomic' => (int) $content->ppv_price_atomic,
            'currency' => $content->ppv_currency ?? 'XMR',
            'address' => $session->address,
            'payment_reference' => $session->reference,
            'expires_at' => $expiresAt,
            'metadata' => [
                'content_id' => $content->id,
            ],
        ]);
    }

    public function createTipInvoice(
        User $payer,
        User $creator,
        int $amountAtomic,
        ?Content $content = null,
        ?string $message = null,
        bool $isAnonymous = false
    ): Invoice {
        $expiresAt = Carbon::now()->addMinutes(30);
        $session = $this->gateway->createInvoice($amountAtomic, 'XMR', $expiresAt, [
            'invoice_type' => 'tip',
            'creator_id' => $creator->id,
            'content_id' => $content?->id,
        ]);

        return Invoice::create([
            'payer_id' => $payer->id,
            'payee_id' => $creator->id,
            'invoice_type' => 'tip',
            'status' => 'pending',
            'amount_atomic' => $amountAtomic,
            'currency' => 'XMR',
            'address' => $session->address,
            'payment_reference' => $session->reference,
            'expires_at' => $expiresAt,
            'metadata' => [
                'creator_id' => $creator->id,
                'content_id' => $content?->id,
                'message' => $message,
                'is_anonymous' => $isAnonymous,
            ],
        ]);
    }

    public function verifyInvoiceAndApply(string $reference): array
    {
        $invoice = Invoice::query()->where('payment_reference', $reference)->first();

        if (! $invoice) {
            throw (new ModelNotFoundException)->setModel(Invoice::class);
        }

        if ($invoice->status === 'paid') {
            return $this->buildPaidResponse($invoice);
        }

        if ($invoice->status !== 'pending') {
            return $this->buildStatusResponse($invoice->status);
        }

        if ($invoice->expires_at->isPast()) {
            $invoice->update(['status' => 'expired']);

            return $this->buildStatusResponse('expired');
        }

        $status = $this->gateway->verify($reference);

        // Demo mode: auto-mark as paid if enabled
        $demoMode = false;
        try {
            $demoMode = (bool) settings('payments_demo_mode', false);
        } catch (\Throwable) {
            // settings() may not be available in all contexts
        }

        if ($demoMode && (! $status->paid || $status->paidAmountAtomic < $invoice->amount_atomic)) {
            $status = new PaymentStatus(true, $invoice->amount_atomic);
        }

        if (! $status->paid || $status->paidAmountAtomic < $invoice->amount_atomic) {
            return $this->buildStatusResponse('pending');
        }

        return DB::transaction(function () use ($invoice) {
            $invoice->refresh();

            if ($invoice->status === 'paid') {
                return $this->buildPaidResponse($invoice);
            }

            $invoice->update([
                'status' => 'paid',
                'paid_at' => Carbon::now(),
            ]);

            if ($invoice->invoice_type === 'ppv') {
                $purchase = $this->applyPpvPurchase($invoice);

                return [
                    'status' => 'paid',
                    'subscription_ends_at' => null,
                    'purchase' => $purchase
                        ? ['content_id' => $purchase->content_id, 'unlocked' => true]
                        : null,
                ];
            }

            if ($invoice->invoice_type === 'tip') {
                $tip = $this->applyTip($invoice);

                return [
                    'status' => 'paid',
                    'subscription_ends_at' => null,
                    'purchase' => null,
                    'tip' => $tip ? ['id' => $tip->id] : null,
                ];
            }

            $subscription = $this->extendSubscription($invoice);

            return [
                'status' => 'paid',
                'subscription_ends_at' => $subscription->ends_at,
                'purchase' => null,
                'tip' => null,
            ];
        });
    }

    private function buildPaidResponse(Invoice $invoice): array
    {
        if ($invoice->invoice_type === 'ppv') {
            $purchase = $this->getPurchase($invoice);

            return [
                'status' => 'paid',
                'subscription_ends_at' => null,
                'purchase' => $purchase
                    ? ['content_id' => $purchase->content_id, 'unlocked' => true]
                    : null,
            ];
        }

        if ($invoice->invoice_type === 'tip') {
            $tip = $this->getTip($invoice);

            return [
                'status' => 'paid',
                'subscription_ends_at' => null,
                'purchase' => null,
                'tip' => $tip ? ['id' => $tip->id] : null,
            ];
        }

        $subscription = $this->getSubscription($invoice);

        return [
            'status' => 'paid',
            'subscription_ends_at' => $subscription?->ends_at,
            'purchase' => null,
            'tip' => null,
        ];
    }

    private function buildStatusResponse(string $status): array
    {
        return [
            'status' => $status,
            'subscription_ends_at' => null,
            'purchase' => null,
            'tip' => null,
        ];
    }

    private function applyPpvPurchase(Invoice $invoice): ?Purchase
    {
        $contentId = $invoice->metadata['content_id'] ?? null;

        if (! $contentId) {
            return null;
        }

        $purchase = Purchase::query()
            ->where('user_id', $invoice->payer_id)
            ->where('content_id', $contentId)
            ->first();

        if ($purchase) {
            return $purchase;
        }

        return Purchase::create([
            'user_id' => $invoice->payer_id,
            'content_id' => $contentId,
            'invoice_id' => $invoice->id,
            'purchased_at' => Carbon::now(),
            'access_expires_at' => null,
        ]);
    }

    private function getPurchase(Invoice $invoice): ?Purchase
    {
        $contentId = $invoice->metadata['content_id'] ?? null;

        if (! $contentId) {
            return null;
        }

        return Purchase::query()
            ->where('user_id', $invoice->payer_id)
            ->where('content_id', $contentId)
            ->first();
    }

    private function extendSubscription(Invoice $invoice): Subscription
    {
        $durationDays = (int) ($invoice->metadata['duration_days'] ?? 0);
        $tierId = $invoice->metadata['tier_id'] ?? null;
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
        if ($tierId) {
            $subscription->tier_id = $tierId;
            $subscription->pending_tier_id = null;
            $subscription->pending_effective_at = null;
        }
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

    private function applyTip(Invoice $invoice): ?Tip
    {
        $tip = Tip::query()->where('invoice_id', $invoice->id)->first();

        if ($tip) {
            return $tip;
        }

        return Tip::create([
            'payer_id' => $invoice->payer_id,
            'creator_id' => $invoice->payee_id,
            'content_id' => $invoice->metadata['content_id'] ?? null,
            'invoice_id' => $invoice->id,
            'amount_atomic' => $invoice->amount_atomic,
            'currency' => $invoice->currency,
            'message' => $invoice->metadata['message'] ?? null,
            'is_anonymous' => (bool) ($invoice->metadata['is_anonymous'] ?? false),
        ]);
    }

    private function getTip(Invoice $invoice): ?Tip
    {
        return Tip::query()->where('invoice_id', $invoice->id)->first();
    }
}
