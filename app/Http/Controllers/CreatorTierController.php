<?php

namespace App\Http\Controllers;

use App\Models\Tier;
use App\Services\TierService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CreatorTierController extends Controller
{
    public function index(Request $request, TierService $service)
    {
        $tiers = $service->listForCreatorOwner($request->user());

        return response()->json([
            'data' => $tiers->map(fn (Tier $tier) => $this->transformTier($tier))->values(),
        ]);
    }

    public function store(Request $request, TierService $service)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'tier_level' => ['required', 'integer', 'min:1'],
            'price_atomic' => ['required', 'integer', 'min:0'],
            'yearly_price_atomic' => ['nullable', 'integer', 'min:0'],
            'yearly_duration_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'currency' => ['nullable', 'string', 'max:10'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'position' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_most_popular' => ['nullable', 'boolean'],
        ]);

        $tier = $service->createTier($request->user(), $data);

        return response()->json([
            'tier' => $this->transformTier($tier),
        ], Response::HTTP_CREATED);
    }

    public function update(Tier $tier, Request $request, TierService $service)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'tier_level' => ['sometimes', 'integer', 'min:1'],
            'price_atomic' => ['sometimes', 'integer', 'min:0'],
            'yearly_price_atomic' => ['nullable', 'integer', 'min:0'],
            'yearly_duration_days' => ['nullable', 'integer', 'min:1', 'max:3650'],
            'currency' => ['nullable', 'string', 'max:10'],
            'duration_days' => ['sometimes', 'integer', 'min:1', 'max:3650'],
            'position' => ['sometimes', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
            'is_most_popular' => ['sometimes', 'boolean'],
        ]);

        $updated = $service->updateTier($request->user(), $tier, $data);

        return response()->json([
            'tier' => $this->transformTier($updated),
        ]);
    }

    public function destroy(Tier $tier, Request $request, TierService $service)
    {
        $archived = $service->archiveTier($request->user(), $tier);

        return response()->json([
            'tier' => $this->transformTier($archived),
        ]);
    }

    private function transformTier(Tier $tier): array
    {
        return [
            'id' => $tier->id,
            'creator_id' => $tier->creator_id,
            'name' => $tier->name,
            'description' => $tier->description,
            'tier_level' => $tier->tier_level,
            'price_atomic' => $tier->price_atomic,
            'currency' => $tier->currency,
            'duration_days' => $tier->duration_days,
            'is_active' => $tier->is_active,
            'position' => $tier->position,
        ];
    }
}
