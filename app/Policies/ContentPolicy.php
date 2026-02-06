<?php

namespace App\Policies;

use App\Models\Content;
use App\Models\User;

class ContentPolicy
{
    public function update(User $user, Content $content): bool
    {
        return $user->id === $content->creator_id && $user->isCreator();
    }

    public function delete(User $user, Content $content): bool
    {
        return $user->id === $content->creator_id && $user->isCreator();
    }

    public function publish(User $user, Content $content): bool
    {
        return $user->id === $content->creator_id && $user->isCreator();
    }
}
