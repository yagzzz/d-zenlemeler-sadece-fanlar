<?php

namespace App\Services;

use App\Models\CreatorApplication;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CreatorApplicationService
{
    public function apply(User $user, string $applicationText): CreatorApplication
    {
        $hasPending = CreatorApplication::query()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            throw ValidationException::withMessages([
                'application' => 'Pending application already exists.',
            ]);
        }

        $application = CreatorApplication::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'application_text' => $applicationText,
        ]);

        $user->forceFill([
            'creator_applied_at' => Carbon::now(),
            'creator_rejected_at' => null,
            'creator_rejection_reason' => null,
        ])->save();

        return $application;
    }

    public function listPending()
    {
        return CreatorApplication::query()
            ->where('status', 'pending')
            ->latest()
            ->get();
    }

    public function approve(CreatorApplication $application, User $admin): CreatorApplication
    {
        $now = Carbon::now();

        $application->forceFill([
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => $now,
            'rejection_reason' => null,
        ])->save();

        $application->user()->update([
            'role' => 'creator',
            'creator_approved_at' => $now,
            'creator_rejected_at' => null,
            'creator_rejection_reason' => null,
        ]);

        return $application;
    }

    public function reject(CreatorApplication $application, User $admin, string $reason): CreatorApplication
    {
        $now = Carbon::now();

        $application->forceFill([
            'status' => 'rejected',
            'reviewed_by' => $admin->id,
            'reviewed_at' => $now,
            'rejection_reason' => $reason,
        ])->save();

        $application->user()->update([
            'role' => 'user',
            'creator_rejected_at' => $now,
            'creator_rejection_reason' => $reason,
            'creator_approved_at' => null,
        ]);

        return $application;
    }
}
