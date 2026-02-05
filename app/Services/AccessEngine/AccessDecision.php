<?php

namespace App\Services\AccessEngine;

class AccessDecision
{
    public function __construct(
        public readonly bool $granted,
        public readonly ?string $reason = null
    ) {
    }
}
