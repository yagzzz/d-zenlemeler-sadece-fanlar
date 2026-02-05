<?php

namespace App\Services\Payments;

use Illuminate\Support\Str;

class MockMoneroGateway implements PaymentGateway
{
    public function __construct(private MockPaymentSimulator $simulator) {}

    public function createInvoice(int $amountAtomic, string $currency, \DateTimeInterface $expiresAt, array $metadata): InvoiceSession
    {
        $reference = (string) Str::uuid();
        $address = "xmr_mock_{$reference}";

        return new InvoiceSession($address, $reference);
    }

    public function verify(string $reference): PaymentStatus
    {
        $status = $this->simulator->getStatus($reference);

        if (! $status) {
            return new PaymentStatus(false, 0);
        }

        return new PaymentStatus((bool) $status['paid'], (int) $status['amount_atomic']);
    }
}
