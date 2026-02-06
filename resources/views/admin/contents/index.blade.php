@extends('admin.layout')

@section('content')
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
    <h1>İçerikler</h1>
</div>

<div class="admin-card">
    <form method="GET" action="/admin/contents" class="search-bar">
        <input type="text" name="search" placeholder="Başlık veya içerik ara…" value="{{ request('search') }}">
        <select name="status" style="width:auto;">
            <option value="">Tüm Durumlar</option>
            <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Yayında</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Taslak</option>
        </select>
        <button type="submit" class="btn-sm btn-primary">🔍 Ara</button>
    </form>

    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Başlık</th>
                <th>Creator</th>
                <th>Durum</th>
                <th>Görünürlük</th>
                <th>Tarih</th>
                <th>İşlemler</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($contents as $content)
            <tr>
                <td>{{ $content->id }}</td>
                <td>{{ Str::limit($content->title ?? '(Başlıksız)', 50) }}</td>
                <td>{{ $content->creator?->name ?? '-' }}</td>
                <td>
                    @if ($content->is_published)
                        <span class="badge badge-success">Yayında</span>
                    @else
                        <span class="badge badge-warning">Taslak</span>
                    @endif
                </td>
                <td>
                    <span class="badge badge-info">{{ $content->visibility ?? 'public' }}</span>
                </td>
                <td>{{ $content->created_at->format('d.m.Y') }}</td>
                <td>
                    @if ($content->is_published)
                    <form method="POST" action="/admin/contents/{{ $content->id }}/takedown" style="display:inline;" onsubmit="return confirm('Bu içeriği kaldırmak istediğinize emin misiniz?')">
                        @csrf
                        <button type="submit" class="btn-sm btn-danger">Kaldır</button>
                    </form>
                    @else
                    <form method="POST" action="/admin/contents/{{ $content->id }}/restore" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-sm btn-success">Yayınla</button>
                    </form>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; color:#888;">İçerik bulunamadı.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if ($contents->hasPages())
    <div class="pagination-links">
        {{ $contents->links() }}
    </div>
    @endif
</div>
@endsection
