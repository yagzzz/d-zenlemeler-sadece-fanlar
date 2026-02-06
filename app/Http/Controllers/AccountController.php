<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeleteAccountRequest;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function destroy(DeleteAccountRequest $request)
    {
        $user = $request->user();

        // Logout BEFORE deleting — Auth::logout() cycles remember_token which
        // would re-insert the user if called after delete() sets exists=false.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

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

        return response()->json(['message' => 'Hesap silindi.', 'redirect' => '/login']);
    }
}
