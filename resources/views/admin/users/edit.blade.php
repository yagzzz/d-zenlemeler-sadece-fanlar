@extends('admin.layout')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <h1>Kullanıcı Düzenle: {{ $user->name }}</h1>
    <a href="/admin/users" class="btn-sm btn-outline" style="text-decoration:none;">← Geri</a>
</div>

<div class="admin-card">
    <form method="POST" action="/admin/users/{{ $user->id }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">İsim</label>
            <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required>
        </div>

        <div class="form-group">
            <label for="email">E-posta</label>
            <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" required>
        </div>

        <div class="form-group">
            <label for="role">Rol</label>
            <select name="role" id="role">
                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                <option value="creator" {{ $user->role === 'creator' ? 'selected' : '' }}>Creator</option>
                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>

        <div class="form-group">
            <label>Kullanıcı Adı</label>
            <input type="text" value="{{ $user->username }}" disabled>
            <div class="hint">Kullanıcı adı bu formdan değiştirilemez.</div>
        </div>

        <div class="form-group">
            <label>Kayıt Tarihi</label>
            <input type="text" value="{{ $user->created_at->format('d.m.Y H:i') }}" disabled>
        </div>

        <button type="submit" class="btn-sm btn-primary" style="padding:0.5rem 1.5rem; font-size:1rem;">
            💾 Kaydet
        </button>
    </form>
</div>
@endsection
