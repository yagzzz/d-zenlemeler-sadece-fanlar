<?php

namespace App\Services\Payments;

class InvoiceSession
{
    public function __construct(
        public readonly string $address,
        public readonly string $reference
    ) {
    }
}
