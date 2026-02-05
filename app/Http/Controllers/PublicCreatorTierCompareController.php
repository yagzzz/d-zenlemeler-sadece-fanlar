<?php

namespace App\Http\Controllers;

use App\Models\Tier;
use App\Models\User;
use App\Services\TierService;
use Illuminate\Http\Response;

class PublicCreatorTierCompareController extends Controller
{
    public function show(string $username, TierService $service)
    {
        $creator = User::query()
            ->where('role', 'creator')
            ->where('username', $username)
            ->firstOrFail();

        $tiers = $service->listForCreatorPublic($username);

        return response()->json([
            'creator' => [
                'username' => $creator->username,
                'display_name' => $creator->name,
                'avatar_url' => null,
            ],
            'currency' => 'XMR',
            'billing' => [
                'default' => 'monthly',
                'options' => [
                    ['key' => 'monthly', 'label' => 'Aylık', 'duration_days' => 30],
                    ['key' => 'yearly', 'label' => 'Yıllık', 'duration_days' => 365, 'discount_badge' => 'İndirim'],
                ],
            ],
            'tiers' => $tiers->map(fn (Tier $tier) => [
                'id' => $tier->id,
                'name' => $tier->name,
                'description' => $tier->description,
                'level' => $tier->tier_level,
                'badges' => $tier->is_most_popular ? ['En Popüler'] : [],
                'price' => [
                    'monthly_atomic' => $tier->price_atomic,
                    'yearly_atomic' => $tier->yearly_price_atomic,
                ],
                'features' => [],
                'limits' => ['max_subscribers' => null],
                'ui' => [
                    'highlight' => (bool) $tier->is_most_popular,
                    'color' => null,
                ],
            ])->values(),
            'actions' => [
                'subscribe' => ['endpoint' => "/api/creators/{$username}/subscribe/invoice"],
                'upgrade' => ['endpoint' => "/api/creators/{$username}/subscription/change-tier/invoice"],
            ],
        ], Response::HTTP_OK);
    }
}
