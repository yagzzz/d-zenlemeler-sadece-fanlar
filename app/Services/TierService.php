<?php

namespace App\Services;

use App\Models\Tier;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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

        $this->validateYearlyPrice($payload);

        $payload['creator_id'] = $creator->id;

        return DB::transaction(function () use ($creator, $payload) {
            if (! empty($payload['is_most_popular'])) {
                Tier::query()
                    ->where('creator_id', $creator->id)
                    ->update(['is_most_popular' => false]);
            }

            return Tier::create($payload);
        });
    }

    public function updateTier(User $creator, Tier $tier, array $payload): Tier
    {
        $this->authorizeCreator($creator);

        if ($tier->creator_id !== $creator->id) {
            throw new AuthorizationException('Not allowed to update this tier.');
        }

        $this->validateYearlyPrice($payload, $tier);

        return DB::transaction(function () use ($creator, $tier, $payload) {
            if (! empty($payload['is_most_popular'])) {
                Tier::query()
                    ->where('creator_id', $creator->id)
                    ->where('id', '!=', $tier->id)
                    ->update(['is_most_popular' => false]);
            }

            $tier->update($payload);

            return $tier->refresh();
        });
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

    private function validateYearlyPrice(array $payload, ?Tier $existingTier = null): void
    {
        if (! array_key_exists('yearly_price_atomic', $payload)) {
            return;
        }

        $yearly = $payload['yearly_price_atomic'];

        if ($yearly === null) {
            return;
        }

        $monthly = $payload['price_atomic'] ?? $existingTier?->price_atomic;

        if ($monthly === null) {
            return;
        }

        if ((int) $yearly < (int) $monthly * 6) {
            throw ValidationException::withMessages([
                'yearly_price_atomic' => 'Yearly price must be at least 6x monthly price.',
            ]);
        }
    }
}
