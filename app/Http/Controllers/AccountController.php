<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ], [
            'password.required' => 'Şifrenizi onaylayın.',
            'password.current_password' => 'Şifre yanlış.',
        ]);

        $user = $request->user();

        DB::transaction(function () use ($user) {
            // Remove social data
            $user->comments()->delete();
            $user->bookmarks()->delete();
            $user->reactions()->delete();
            $user->notifications()->delete();

            // Remove content if creator
            if ($user->isCreator()) {
                $user->contents()->delete();
                $user->creatorProfile()?->delete();
                $user->tiers()->delete();
            }

            $user->delete();
        });

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json(['message' => 'Hesap silindi.', 'redirect' => '/login']);
    }
}
