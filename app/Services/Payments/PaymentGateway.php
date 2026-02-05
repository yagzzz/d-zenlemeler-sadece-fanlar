<?php

namespace App\Services\Payments;

interface PaymentGateway
{
    public function createInvoice(int $amountAtomic, string $currency, \DateTimeInterface $expiresAt, array $metadata): InvoiceSession;

    public function verify(string $reference): PaymentStatus;
}
