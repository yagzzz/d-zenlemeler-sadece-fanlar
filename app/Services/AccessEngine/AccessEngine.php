<?php

namespace App\Services\AccessEngine;

interface AccessEngine
{
    public function decide(AccessRequest $request): AccessDecision;
}
