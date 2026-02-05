<?php

namespace App\Services;

use App\Models\Content;
use App\Models\Tip;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

class TipService
{
    public function listCreatorTips(User $creator, int $limit = 20)
    {
        $this->authorizeCreator($creator);

        return Tip::query()
            ->where('creator_id', $creator->id)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function aggregateContentTips(Content $content): array
    {
        $aggregate = Tip::query()
            ->where('content_id', $content->id)
            ->selectRaw('COUNT(*) as tip_count, COALESCE(SUM(amount_atomic), 0) as total_atomic')
            ->first();

        return [
            'count' => (int) ($aggregate->tip_count ?? 0),
            'total_atomic' => (int) ($aggregate->total_atomic ?? 0),
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
}
