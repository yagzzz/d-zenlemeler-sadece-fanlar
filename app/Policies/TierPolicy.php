<?php

namespace App\Policies;

use App\Models\Tier;
use App\Models\User;

class TierPolicy
{
    public function update(User $user, Tier $tier): bool
    {
        return $user->id === $tier->creator_id && $user->isCreator();
    }

    public function delete(User $user, Tier $tier): bool
    {
        return $user->id === $tier->creator_id && $user->isCreator();
    }
}
