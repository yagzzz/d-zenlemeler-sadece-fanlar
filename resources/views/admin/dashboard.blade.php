@extends('admin.layout')

@section('content')
<h1 style="margin-bottom:1.5rem;">Dashboard</h1>

<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_users'] }}</div>
        <div class="stat-label">Toplam Kullanıcı</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_creators'] }}</div>
        <div class="stat-label">Aktif Creator</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['total_contents'] }}</div>
        <div class="stat-label">Toplam İçerik</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['pending_reports'] }}</div>
        <div class="stat-label">Bekleyen Raporlar</div>
    </div>
    <div class="stat-card">
        <div class="stat-value">{{ $stats['pending_applications'] }}</div>
        <div class="stat-label">Creator Başvuruları</div>
    </div>
</div>

<div class="admin-card">
    <h3>Hızlı Bağlantılar</h3>
    <div style="display:flex; gap:0.75rem; flex-wrap:wrap; margin-top:1rem;">
        <a href="/admin/settings" class="btn-sm btn-primary" style="text-decoration:none;">⚙️ Site Ayarları</a>
        <a href="/admin/users" class="btn-sm btn-outline" style="text-decoration:none;">👥 Kullanıcılar</a>
        <a href="/admin/contents" class="btn-sm btn-outline" style="text-decoration:none;">📝 İçerikler</a>
        <a href="/admin/reports" class="btn-sm btn-outline" style="text-decoration:none;">🚩 Raporlar</a>
        <a href="/admin/creator-applications" class="btn-sm btn-outline" style="text-decoration:none;">🎨 Başvurular</a>
    </div>
</div>
@endsection
