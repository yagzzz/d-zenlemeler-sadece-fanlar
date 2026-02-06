<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePreferencesRequest;
use App\Http\Requests\UpdateUserSettingsRequest;
use App\Models\User;

class UserSettingsController extends Controller
{
    /**
     * Update profile (name, username).
     */
    public function update(UpdateUserSettingsRequest $request)
    {
        $user = $request->user();

        $user->update($request->only(['name', 'username']));

        return response()->json([
            'message' => 'Ayarlar güncellendi.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    /**
     * Update notification preferences.
     */
    public function updatePreferences(UpdatePreferencesRequest $request)
    {
        $user = $request->user();

        $current = $user->getNotificationPreferencesWithDefaults();

        // For JSON requests (API), only update keys that are explicitly sent.
        // For form submissions, treat missing keys as unchecked (false).
        $updated = [];
        foreach (array_keys(User::DEFAULT_NOTIFICATION_PREFERENCES) as $key) {
            if ($request->has($key)) {
                $updated[$key] = (bool) $request->input($key);
            } elseif (!$request->isJson()) {
                $updated[$key] = false;
            }
        }

        $merged = array_merge($current, $updated);

        $user->update(['notification_preferences' => $merged]);

        return response()->json([
            'message' => 'Bildirim tercihleri güncellendi.',
            'preferences' => $user->fresh()->getNotificationPreferencesWithDefaults(),
        ]);
    }
}
