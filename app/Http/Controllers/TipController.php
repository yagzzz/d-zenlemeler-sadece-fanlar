<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Invoice;
use App\Models\User;
use App\Services\Payments\PaymentService;
use App\Services\TipService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TipController extends Controller
{
    public function createCreatorTipInvoice(string $username, Request $request, PaymentService $paymentService)
    {
        $data = $request->validate([
            'amount_atomic' => ['required', 'integer', 'min:' . config('tips.min_atomic')],
            'message' => ['nullable', 'string', 'max:280'],
            'is_anonymous' => ['nullable', 'boolean'],
            'content_id' => ['nullable', 'integer', 'exists:contents,id'],
        ]);

        $creator = User::query()
            ->where('role', 'creator')
            ->where('username', $username)
            ->firstOrFail();

        if ($creator->creator_approved_at === null) {
            return response()->json(['message' => 'Creator not approved.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $content = null;

        if (! empty($data['content_id'])) {
            $content = Content::query()->findOrFail($data['content_id']);

            if ($content->creator_id !== $creator->id) {
                return response()->json(['message' => 'Content does not belong to creator.'], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        $invoice = $paymentService->createTipInvoice(
            $request->user(),
            $creator,
            (int) $data['amount_atomic'],
            $content,
            $data['message'] ?? null,
            (bool) ($data['is_anonymous'] ?? false)
        );

        return response()->json([
            'invoice_id' => $invoice->id,
            'address' => $invoice->address,
            'amount_atomic' => $invoice->amount_atomic,
            'expires_at' => $invoice->expires_at?->toISOString(),
            'status' => $invoice->status,
        ], Response::HTTP_CREATED);
    }

    public function verifyInvoice(Invoice $invoice, PaymentService $paymentService)
    {
        $result = $paymentService->verifyInvoiceAndApply($invoice->payment_reference);

        return response()->json([
            'status' => $result['status'],
            'subscription_ends_at' => optional($result['subscription_ends_at'])->toISOString(),
            'tip' => $result['tip'] ?? null,
            'purchase' => $result['purchase'] ?? null,
        ]);
    }

    public function listCreatorTips(Request $request, TipService $service)
    {
        $creator = User::query()
            ->where('role', 'creator')
            ->where('username', $request->route('username'))
            ->firstOrFail();

        if ($request->user()->id !== $creator->id) {
            return response()->json(['message' => 'Not allowed.'], Response::HTTP_FORBIDDEN);
        }

        $limit = (int) $request->query('limit', 20);
        $limit = min(max($limit, 1), 100);

        $tips = $service->listCreatorTips($creator, $limit);

        return response()->json([
            'data' => $tips->map(function ($tip) {
                return [
                    'id' => $tip->id,
                    'amount_atomic' => $tip->amount_atomic,
                    'currency' => $tip->currency,
                    'message' => $tip->message,
                    'is_anonymous' => $tip->is_anonymous,
                    'content_id' => $tip->content_id,
                    'created_at' => $tip->created_at?->toISOString(),
                    'payer' => $tip->is_anonymous ? null : [
                        'id' => $tip->payer_id,
                    ],
                ];
            })->values(),
        ]);
    }

    public function contentTipAggregate(Content $content, TipService $service)
    {
        $aggregate = $service->aggregateContentTips($content);

        return response()->json($aggregate, Response::HTTP_OK);
    }
}
