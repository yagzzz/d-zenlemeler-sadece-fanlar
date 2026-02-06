@extends('admin.layout')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <h1>Kullanıcılar</h1>
</div>

<div class="admin-card">
    <form method="GET" action="/admin/users" class="search-bar">
        <input type="text" name="search" placeholder="İsim, e-posta veya kullanıcı adı ara…" value="{{ request('search') }}">
        <select name="role" style="width:auto;">
            <option value="">Tüm Roller</option>
            <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
            <option value="creator" {{ request('role') === 'creator' ? 'selected' : '' }}>Creator</option>
            <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
        </select>
        <button type="submit" class="btn-sm btn-primary">🔍 Ara</button>
    </form>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>İsim</th>
                <th>Kullanıcı Adı</th>
                <th>E-posta</th>
                <th>Rol</th>
                <th>Kayıt Tarihi</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($users as $user)
            <tr>
                <td>{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ '@' . $user->username }}</td>
                <td>{{ $user->email }}</td>
                <td>
                    @switch($user->role)
                        @case('admin') <span class="badge badge-danger">Admin</span> @break
                        @case('creator') <span class="badge badge-success">Creator</span> @break
                        @default <span class="badge badge-secondary">User</span>
                    @endswitch
                </td>
                <td>{{ $user->created_at->format('d.m.Y') }}</td>
                <td>
                    <a href="/admin/users/{{ $user->id }}/edit" class="btn-sm btn-outline">Düzenle</a>
                    @if ($user->id !== auth()->id())
                    <form method="POST" action="/admin/users/{{ $user->id }}/ban" style="display:inline;" onsubmit="return confirm('Bu kullanıcıyı banlamak istediğinize emin misiniz?')">
                        @csrf
                        <button type="submit" class="btn-sm btn-danger">Banla</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#888;">Kullanıcı bulunamadı.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if ($users->hasPages())
    <div class="pagination-links">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
