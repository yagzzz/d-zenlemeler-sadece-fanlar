<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Http\Response;

class CreatorAnalyticsController extends Controller
{
    public function show(string $username)
    {
        $creator = User::query()
            ->where('role', 'creator')
            ->where('username', $username)
            ->firstOrFail();

        $tipsAggregate = Tip::query()
            ->where('creator_id', $creator->id)
            ->selectRaw('COUNT(*) as tip_count, COALESCE(SUM(amount_atomic), 0) as total_atomic')
            ->first();

        $activeSubscribers = Subscription::query()
            ->where('creator_id', $creator->id)
            ->where('ends_at', '>', now())
            ->count();

        return response()->json([
            'tips_total_atomic' => (int) ($tipsAggregate->total_atomic ?? 0),
            'tips_count' => (int) ($tipsAggregate->tip_count ?? 0),
            'active_subscribers' => $activeSubscribers,
        ], Response::HTTP_OK);
    }
}
