<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Tier;
use App\Models\User;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    public function createSubscriptionInvoice(Request $request, PaymentService $service)
    {
        $data = $request->validate([
            'tier_id' => ['required', 'string', 'exists:tiers,id'],
        ]);

        $tier = Tier::query()->findOrFail($data['tier_id']);
        $creator = User::query()->findOrFail($tier->creator_id);

        if (! $tier->is_active) {
            return response()->json(['message' => 'Tier is inactive.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($creator->creator_approved_at === null) {
            return response()->json(['message' => 'Creator not approved.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $invoice = $service->createSubscriptionInvoice($request->user(), $tier);

        return response()->json([
            'invoice_id' => $invoice->id,
            'address' => $invoice->address,
            'amount_atomic' => $invoice->amount_atomic,
            'currency' => $invoice->currency,
            'expires_at' => $invoice->expires_at?->toISOString(),
        ], Response::HTTP_CREATED);
    }

    public function verify(Request $request, PaymentService $service)
    {
        $data = $request->validate([
            'payment_reference' => ['required', 'string'],
        ]);

        $result = $service->verifyInvoiceAndApply($data['payment_reference']);

        return response()->json([
            'status' => $result['status'],
            'subscription_ends_at' => optional($result['subscription_ends_at'])->toISOString(),
            'purchase' => $result['purchase'] ?? null,
            'tip' => $result['tip'] ?? null,
        ]);
    }

    public function createPpvInvoice(Request $request, PaymentService $service)
    {
        $data = $request->validate([
            'content_id' => ['required', 'integer', 'exists:contents,id'],
        ]);

        $content = Content::query()->findOrFail($data['content_id']);

        $invoice = $service->createPpvInvoice($request->user(), $content);

        return response()->json([
            'invoice_id' => $invoice->id,
            'address' => $invoice->address,
            'amount_atomic' => $invoice->amount_atomic,
            'currency' => $invoice->currency,
            'expires_at' => $invoice->expires_at?->toISOString(),
        ], Response::HTTP_CREATED);
    }
}
