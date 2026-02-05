<?php

namespace App\Services\Payments;

class PaymentStatus
{
    public function __construct(
        public readonly bool $paid,
        public readonly int $paidAmountAtomic
    ) {}
}
