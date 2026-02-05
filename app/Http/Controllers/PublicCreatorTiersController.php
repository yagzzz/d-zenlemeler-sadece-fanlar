<?php

namespace App\Http\Controllers;

use App\Models\Tier;
use App\Services\TierService;
use Illuminate\Http\Response;

class PublicCreatorTiersController extends Controller
{
    public function index(string $username, TierService $service)
    {
        $tiers = $service->listForCreatorPublic($username);

        return response()->json([
            'data' => $tiers->map(fn (Tier $tier) => [
                'id' => $tier->id,
                'name' => $tier->name,
                'description' => $tier->description,
                'tier_level' => $tier->tier_level,
                'price_atomic' => $tier->price_atomic,
                'currency' => $tier->currency,
                'duration_days' => $tier->duration_days,
                'position' => $tier->position,
            ])->values(),
        ], Response::HTTP_OK);
    }
}
