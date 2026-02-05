<?php

namespace App\Services\Creator;

use App\Models\Content;
use App\Models\CreatorProfile;
use App\Models\MediaAsset;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;

class CreatorProfileService
{
    public function getOrCreateProfile(User $creator): CreatorProfile
    {
        $this->authorizeCreator($creator);

        return CreatorProfile::firstOrCreate(
            ['user_id' => $creator->id],
            [
                'categories' => [],
                'tags' => [],
                'social_links' => [],
            ]
        );
    }

    public function updateProfile(User $creator, array $payload): CreatorProfile
    {
        $profile = $this->getOrCreateProfile($creator);

        $avatarId = $payload['avatar_media_id'] ?? null;
        $bannerId = $payload['banner_media_id'] ?? null;

        if ($avatarId !== null) {
            $this->validateImageAssetOwnership($creator, (int) $avatarId, 'avatar_media_id');
        }

        if ($bannerId !== null) {
            $this->validateImageAssetOwnership($creator, (int) $bannerId, 'banner_media_id');
        }

        $profile->update($payload);

        return $profile->refresh();
    }

    /**
     * @return array{creator: array, profile: array, monetization: array, stats: array}
     */
    public function publicProfile(string $username): array
    {
        $creator = User::query()
            ->where('role', 'creator')
            ->where('username', $username)
            ->firstOrFail();

        $profile = CreatorProfile::firstOrCreate(
            ['user_id' => $creator->id],
            [
                'categories' => [],
                'tags' => [],
                'social_links' => [],
            ]
        );

        $contentCount = Content::query()
            ->where('creator_id', $creator->id)
            ->count();

        return [
            'creator' => [
                'id' => $creator->id,
                'username' => $creator->username,
                'display_name' => $creator->name,
                'avatar_url' => null,
            ],
            'profile' => [
                'bio' => $profile->bio,
                'tagline' => $profile->tagline,
                'categories' => $profile->categories ?? [],
                'tags' => $profile->tags ?? [],
                'social_links' => $profile->social_links ?? [],
            ],
            'monetization' => [
                'default_subscription_price_atomic' => $profile->default_subscription_price_atomic,
                'currency' => $profile->currency,
                'allow_ppv' => $profile->allow_ppv,
                'allow_tips' => $profile->allow_tips,
                'allow_registered_only' => $profile->allow_registered_only,
            ],
            'stats' => [
                'content_count' => $contentCount,
            ],
        ];
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

    private function validateImageAssetOwnership(User $creator, int $assetId, string $field): void
    {
        $asset = MediaAsset::query()->findOrFail($assetId);

        if ($asset->creator_id !== $creator->id) {
            throw new AuthorizationException('Not allowed to use this media asset.');
        }

        if ($asset->type !== 'image') {
            throw ValidationException::withMessages([
                $field => 'Media asset must be an image.',
            ]);
        }
    }
}
