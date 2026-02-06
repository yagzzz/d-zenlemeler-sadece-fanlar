<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Panel — {{ settings('site_name', config('app.name', 'Sadece Fanlar')) }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    @php($hasViteManifest = is_file(public_path('build/manifest.json')))
    @php($hasViteHot = is_file(storage_path('framework/vite.hot')))
    @if ($hasViteHot || $hasViteManifest)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="/app.css">
        <script defer src="/app.js"></script>
    @endif
    <style>
        .admin-layout { display:flex; min-height:100vh; }
        .admin-sidebar { width:240px; background:#1a1a2e; color:#eee; padding:1rem; flex-shrink:0; }
        .admin-sidebar a { color:#ccc; text-decoration:none; display:block; padding:0.5rem 0.75rem; border-radius:6px; margin-bottom:2px; font-size:0.95rem; }
        .admin-sidebar a:hover, .admin-sidebar a.active { background:#16213e; color:#fff; }
        .admin-sidebar .brand { font-size:1.25rem; font-weight:700; color:#d946ef; margin-bottom:1.5rem; padding:0.5rem; }
        .admin-main { flex:1; padding:2rem; background:#f8f9fa; overflow-x:auto; }
        .admin-card { background:#fff; border-radius:8px; padding:1.5rem; margin-bottom:1rem; box-shadow:0 1px 3px rgba(0,0,0,.08); }
        .admin-table { width:100%; border-collapse:collapse; }
        .admin-table th, .admin-table td { padding:0.75rem; text-align:left; border-bottom:1px solid #e5e7eb; }
        .admin-table th { font-weight:600; color:#666; font-size:0.85rem; text-transform:uppercase; }
        .admin-table tr:hover { background:#f3f4f6; }
        .badge { display:inline-block; padding:0.25rem 0.5rem; border-radius:4px; font-size:0.8rem; font-weight:600; }
        .badge-success { background:#d1fae5; color:#065f46; }
        .badge-warning { background:#fef3c7; color:#92400e; }
        .badge-danger { background:#fee2e2; color:#991b1b; }
        .badge-info { background:#dbeafe; color:#1e40af; }
        .badge-secondary { background:#e5e7eb; color:#374151; }
        .btn-sm { padding:0.25rem 0.75rem; font-size:0.85rem; border-radius:4px; border:none; cursor:pointer; }
        .btn-primary { background:#d946ef; color:#fff; }
        .btn-primary:hover { background:#c026d3; }
        .btn-danger { background:#ef4444; color:#fff; }
        .btn-danger:hover { background:#dc2626; }
        .btn-success { background:#10b981; color:#fff; }
        .btn-success:hover { background:#059669; }
        .btn-outline { background:transparent; border:1px solid #d1d5db; color:#374151; }
        .btn-outline:hover { background:#f3f4f6; }
        .form-group { margin-bottom:1rem; }
        .form-group label { display:block; font-weight:600; margin-bottom:0.25rem; font-size:0.9rem; }
        .form-group input, .form-group textarea, .form-group select { width:100%; padding:0.5rem; border:1px solid #d1d5db; border-radius:6px; font-size:0.95rem; }
        .form-group textarea { min-height:80px; resize:vertical; }
        .form-group .hint { font-size:0.8rem; color:#888; margin-top:0.25rem; }
        .stat-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:1rem; margin-bottom:1.5rem; }
        .stat-card { background:#fff; border-radius:8px; padding:1.25rem; box-shadow:0 1px 3px rgba(0,0,0,.08); }
        .stat-card .stat-value { font-size:2rem; font-weight:700; color:#1a1a2e; }
        .stat-card .stat-label { color:#888; font-size:0.85rem; }
        .alert { padding:0.75rem 1rem; border-radius:6px; margin-bottom:1rem; }
        .alert-success { background:#d1fae5; color:#065f46; }
        .alert-error { background:#fee2e2; color:#991b1b; }
        .pagination-links { margin-top:1rem; }
        .search-bar { display:flex; gap:0.5rem; margin-bottom:1rem; }
        .search-bar input { flex:1; }
        .group-section { margin-bottom:2rem; }
        .group-section h3 { text-transform:capitalize; border-bottom:2px solid #d946ef; padding-bottom:0.5rem; margin-bottom:1rem; }
    </style>
</head>
<body>
    <div class="admin-layout">
        {{-- Sidebar --}}
        <aside class="admin-sidebar">
            <div class="brand">🛡️ Admin Panel</div>
            @php($currentPath = request()->path())
            <a href="/admin" class="{{ $currentPath === 'admin' ? 'active' : '' }}">📊 Dashboard</a>
            <a href="/admin/settings" class="{{ str_starts_with($currentPath, 'admin/settings') ? 'active' : '' }}">⚙️ Site Ayarları</a>
            <a href="/admin/users" class="{{ str_starts_with($currentPath, 'admin/users') ? 'active' : '' }}">👥 Kullanıcılar</a>
            <a href="/admin/contents" class="{{ str_starts_with($currentPath, 'admin/contents') ? 'active' : '' }}">📝 İçerikler</a>
            <a href="/admin/reports" class="{{ str_starts_with($currentPath, 'admin/reports') ? 'active' : '' }}">🚩 Raporlar</a>
            <a href="/admin/creator-applications" class="{{ str_starts_with($currentPath, 'admin/creator-applications') ? 'active' : '' }}">🎨 Creator Başvuruları</a>
            <hr style="border-color:#333; margin:1rem 0;">
            <a href="/">← Siteye Dön</a>
        </aside>

        {{-- Main content --}}
        <main class="admin-main">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert-error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</body>
</html>
