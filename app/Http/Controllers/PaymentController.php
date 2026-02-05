<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\Payments\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    public function createSubscriptionInvoice(Request $request, PaymentService $service)
    {
        $data = $request->validate([
            'creator_id' => ['required', 'integer', 'exists:users,id'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:365'],
        ]);

        $creator = User::query()->findOrFail($data['creator_id']);

        if ($creator->creator_approved_at === null) {
            return response()->json(['message' => 'Creator not approved.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $amountAtomic = (int) config('payments.subscription_price_atomic_per_day') * $data['duration_days'];

        $invoice = $service->createSubscriptionInvoice(
            $request->user(),
            $creator,
            $data['duration_days'],
            $amountAtomic
        );

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
        ]);
    }
}
