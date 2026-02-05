<?php

namespace App\Services\Payments;

use Illuminate\Support\Facades\Cache;

class MockPaymentSimulator
{
    public function markPaid(string $reference, int $amountAtomic): void
    {
        Cache::put($this->cacheKey($reference), [
            'paid' => true,
            'amount_atomic' => $amountAtomic,
        ], now()->addHours(1));
    }

    public function getStatus(string $reference): ?array
    {
        return Cache::get($this->cacheKey($reference));
    }

    private function cacheKey(string $reference): string
    {
        return "mock_payment_status:{$reference}";
    }
}
