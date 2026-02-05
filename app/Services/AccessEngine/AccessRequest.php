<?php

namespace App\Services\AccessEngine;

use App\Models\User;

class AccessRequest
{
    public function __construct(
        public readonly ?User $user,
        public readonly string $visibility
    ) {
    }
}
