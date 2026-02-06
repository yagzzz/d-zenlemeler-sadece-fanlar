<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    /**
     * GET /admin/users — List all users.
     */
    public function index(Request $request)
    {
        $query = User::query()->orderByDesc('created_at');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        if ($role = $request->query('role')) {
            $query->where('role', $role);
        }

        $users = $query->paginate(20)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json(['users' => $users]);
        }

        return view('admin.users.index', compact('users'));
    }

    /**
     * GET /admin/users/{user}/edit — Edit user form.
     */
    public function edit(Request $request, User $user)
    {
        if ($request->wantsJson()) {
            return response()->json(['user' => $user]);
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * PUT /admin/users/{user} — Update user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => "required|email|unique:users,email,{$user->id}",
            'role'  => 'required|in:user,creator,admin',
        ]);

        $user->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Kullanıcı güncellendi.', 'user' => $user->fresh()]);
        }

        return redirect('/admin/users')->with('success', 'Kullanıcı güncellendi.');
    }

    /**
     * POST /admin/users/{user}/ban — Toggle ban (delete/restore).
     */
    public function ban(Request $request, User $user)
    {
        // Prevent self-ban
        if ($user->id === $request->user()->id) {
            if ($request->wantsJson()) {
                return response()->json(['message' => 'Kendinizi banlayamazsınız.'], 422);
            }
            return back()->withErrors(['error' => 'Kendinizi banlayamazsınız.']);
        }

        $user->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Kullanıcı banlandı.']);
        }

        return redirect('/admin/users')->with('success', 'Kullanıcı banlandı.');
    }
}
