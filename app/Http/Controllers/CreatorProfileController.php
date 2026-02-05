<?php

namespace App\Http\Controllers;

use App\Services\Creator\CreatorProfileService;
use Illuminate\Http\Request;

class CreatorProfileController extends Controller
{
    public function show(Request $request, CreatorProfileService $service)
    {
        $profile = $service->getOrCreateProfile($request->user());

        return response()->json([
            'profile' => $this->transformProfile($profile),
        ]);
    }

    public function update(Request $request, CreatorProfileService $service)
    {
        $data = $request->validate([
            'bio' => ['nullable', 'string', 'max:5000'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'categories' => ['nullable', 'array', 'max:10'],
            'categories.*' => ['string', 'max:32'],
            'tags' => ['nullable', 'array', 'max:20'],
            'tags.*' => ['string', 'max:24'],
            'social_links' => ['nullable', 'array'],
            'social_links.instagram' => ['nullable', 'url', 'max:200'],
            'social_links.x' => ['nullable', 'url', 'max:200'],
            'social_links.youtube' => ['nullable', 'url', 'max:200'],
            'social_links.tiktok' => ['nullable', 'url', 'max:200'],
            'social_links.website' => ['nullable', 'url', 'max:200'],
            'wallet_xmr_address' => ['nullable', 'string', 'max:200'],
            'default_subscription_price_atomic' => ['nullable', 'integer', 'min:0'],
            'allow_free_content' => ['nullable', 'boolean'],
            'allow_registered_only' => ['nullable', 'boolean'],
            'allow_ppv' => ['nullable', 'boolean'],
            'allow_tips' => ['nullable', 'boolean'],
            'avatar_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
            'banner_media_id' => ['nullable', 'integer', 'exists:media_assets,id'],
        ]);

        $profile = $service->updateProfile($request->user(), $data);

        return response()->json([
            'profile' => $this->transformProfile($profile),
        ]);
    }

    private function transformProfile($profile): array
    {
        return [
            'bio' => $profile->bio,
            'tagline' => $profile->tagline,
            'avatar_media_id' => $profile->avatar_media_id,
            'banner_media_id' => $profile->banner_media_id,
            'categories' => $profile->categories ?? [],
            'tags' => $profile->tags ?? [],
            'social_links' => $profile->social_links ?? [],
            'wallet_xmr_address' => $profile->wallet_xmr_address,
            'default_subscription_price_atomic' => $profile->default_subscription_price_atomic,
            'currency' => $profile->currency,
            'allow_free_content' => $profile->allow_free_content,
            'allow_registered_only' => $profile->allow_registered_only,
            'allow_ppv' => $profile->allow_ppv,
            'allow_tips' => $profile->allow_tips,
        ];
    }
}
