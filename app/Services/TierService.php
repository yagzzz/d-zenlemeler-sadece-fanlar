<?php

namespace App\Services;

use App\Models\Tier;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class TierService
{
    public function listForCreatorPublic(string $username)
    {
        $creator = User::query()
            ->where('role', 'creator')
            ->where('username', $username)
            ->firstOrFail();

        return Tier::query()
            ->where('creator_id', $creator->id)
            ->where('is_active', true)
            ->orderBy('position')
            ->orderBy('tier_level')
            ->get();
    }

    public function listForCreatorOwner(User $creator)
    {
        $this->authorizeCreator($creator);

        return Tier::query()
            ->where('creator_id', $creator->id)
            ->orderBy('position')
            ->orderBy('tier_level')
            ->get();
    }

    public function createTier(User $creator, array $payload): Tier
    {
        $this->authorizeCreator($creator);

        $payload['creator_id'] = $creator->id;

        return Tier::create($payload);
    }

    public function updateTier(User $creator, Tier $tier, array $payload): Tier
    {
        $this->authorizeCreator($creator);

        if ($tier->creator_id !== $creator->id) {
            throw new AuthorizationException('Not allowed to update this tier.');
        }

        $tier->update($payload);

        return $tier->refresh();
    }

    public function archiveTier(User $creator, Tier $tier): Tier
    {
        $this->authorizeCreator($creator);

        if ($tier->creator_id !== $creator->id) {
            throw new AuthorizationException('Not allowed to archive this tier.');
        }

        $tier->update(['is_active' => false]);

        return $tier->refresh();
    }

    private function authorizeCreator(User $creator): void
    {
        if ($creator->role !== 'creator') {
            throw new AuthorizationException('Creator access required.');
        }

        if ($creator->creator_approved_at === null) {
            throw new AuthorizationException('Creator not approved.');
        }
    }
}
