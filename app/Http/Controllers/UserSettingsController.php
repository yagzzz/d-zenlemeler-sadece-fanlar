<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateUserSettingsRequest;

class UserSettingsController extends Controller
{
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
}
