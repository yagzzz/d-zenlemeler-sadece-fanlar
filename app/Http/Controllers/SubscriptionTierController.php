<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Tier;
use App\Models\User;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SubscriptionTierController extends Controller
{
    public function createInvoice(string $username, Request $request, PaymentService $service)
    {
        $data = $request->validate([
            'tier_id' => ['required', 'string', 'exists:tiers,id'],
            'billing' => ['nullable', 'in:monthly,yearly'],
        ]);

        $creator = User::query()
            ->where('role', 'creator')
            ->where('username', $username)
            ->firstOrFail();

        if ($creator->creator_approved_at === null) {
            return response()->json(['message' => 'Creator not approved.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $tier = Tier::query()->findOrFail($data['tier_id']);

        if ($tier->creator_id !== $creator->id || ! $tier->is_active) {
            return response()->json(['message' => 'Tier is inactive.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $invoice = $service->createSubscriptionInvoice(
            $request->user(),
            $tier,
            $data['billing'] ?? 'monthly'
        );

        return response()->json([
            'invoice_id' => $invoice->id,
            'address' => $invoice->address,
            'amount_atomic' => $invoice->amount_atomic,
            'currency' => $invoice->currency,
            'expires_at' => $invoice->expires_at?->toISOString(),
        ], Response::HTTP_CREATED);
    }

    public function changeTierInvoice(string $username, Request $request, PaymentService $service)
    {
        $data = $request->validate([
            'new_tier_id' => ['required', 'string', 'exists:tiers,id'],
            'billing' => ['nullable', 'in:monthly,yearly'],
        ]);

        $creator = User::query()
            ->where('role', 'creator')
            ->where('username', $username)
            ->firstOrFail();

        $newTier = Tier::query()->findOrFail($data['new_tier_id']);

        if ($newTier->creator_id !== $creator->id || ! $newTier->is_active) {
            return response()->json(['message' => 'Tier is inactive.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $subscription = Subscription::query()
            ->where('user_id', $request->user()->id)
            ->where('creator_id', $creator->id)
            ->where('ends_at', '>', now())
            ->first();

        if (! $subscription) {
            return $this->createInvoice($username, $request, $service);
        }

        $currentTier = $subscription->tier_id ? Tier::query()->find($subscription->tier_id) : null;
        $currentLevel = $currentTier?->tier_level ?? 0;

        if ($newTier->tier_level <= $currentLevel) {
            $subscription->update([
                'pending_tier_id' => $newTier->id,
                'pending_effective_at' => $subscription->ends_at,
            ]);

            return response()->json([
                'status' => 'scheduled',
                'effective_at' => $subscription->ends_at?->toISOString(),
            ], Response::HTTP_OK);
        }

        $invoice = $service->createSubscriptionInvoice(
            $request->user(),
            $newTier,
            $data['billing'] ?? 'monthly'
        );

        return response()->json([
            'invoice_id' => $invoice->id,
            'address' => $invoice->address,
            'amount_atomic' => $invoice->amount_atomic,
            'currency' => $invoice->currency,
            'expires_at' => $invoice->expires_at?->toISOString(),
        ], Response::HTTP_CREATED);
    }
}
