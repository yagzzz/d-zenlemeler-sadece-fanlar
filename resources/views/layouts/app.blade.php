<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sadece Fanlar') }}</title>
    @php($hasViteManifest = is_file(public_path('build/manifest.json')))
    @php($hasViteHot = is_file(storage_path('framework/vite.hot')))
    @if ($hasViteHot || $hasViteManifest)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <link rel="stylesheet" href="/app.css">
        <script defer src="/app.js"></script>
    @endif
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased" data-ui-enabled="{{ config('features.flags.ui') ? '1' : '0' }}" data-ui-polish-enabled="{{ config('features.flags.ui_polish') ? '1' : '0' }}" data-app-env="{{ app()->environment() }}">

    {{-- ── Desktop Side Menu ─────────────────────────────────────────── --}}
    <aside class="side-menu">
        <a href="/" class="menu-brand">
            <div class="brand-icon">SF</div>
            <span class="brand-text">Sadece Fanlar</span>
        </a>

        @php($currentPath = request()->path())
        <nav class="flex-1 space-y-0.5">
            <a href="/"              class="menu-item {{ $currentPath === '/' ? 'active' : '' }}"><span class="menu-icon">🏠</span> Ana Sayfa</a>
            <a href="/explore"       class="menu-item {{ str_starts_with($currentPath, 'explore') ? 'active' : '' }}"><span class="menu-icon">🔍</span> Keşfet</a>
            <a href="/notifications" class="menu-item {{ str_starts_with($currentPath, 'notifications') ? 'active' : '' }}"><span class="menu-icon">🔔</span> Bildirimler</a>
            <a href="/inbox"         class="menu-item {{ str_starts_with($currentPath, 'inbox') ? 'active' : '' }}"><span class="menu-icon">✉️</span> Mesajlar</a>
            <a href="/bookmarks"     class="menu-item {{ str_starts_with($currentPath, 'bookmarks') ? 'active' : '' }}"><span class="menu-icon">🔖</span> Kaydedilenler</a>
            <a href="/create"        class="menu-item {{ str_starts_with($currentPath, 'create') ? 'active' : '' }}"><span class="menu-icon">➕</span> Oluştur</a>
            <a href="/profile"       class="menu-item {{ str_starts_with($currentPath, 'profile') ? 'active' : '' }}"><span class="menu-icon">👤</span> Profil</a>
        </nav>

        <div class="menu-footer">
            <div class="flex items-center gap-3 rounded-xl bg-white/5 p-3">
                <div class="h-9 w-9 rounded-full bg-gradient-to-br from-fuchsia-500 to-cyan-400 flex items-center justify-center text-xs font-bold text-white">
                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold truncate">{{ auth()->user()->name ?? 'Misafir' }}</p>
                    <p class="text-[10px] text-slate-500 truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
            </div>
        </div>
    </aside>

    {{-- ── Main Content ──────────────────────────────────────────────── --}}
    <div class="main-content">
        @yield('content')
    </div>

    {{-- ── Mobile Bottom Nav ─────────────────────────────────────────── --}}
    <nav class="bottom-nav" data-nav="bottom">
        <div class="nav-items">
            <a href="/"          class="nav-item {{ $currentPath === '/' ? 'active' : '' }}"><span class="nav-icon">🏠</span><span class="nav-label">Ana Sayfa</span></a>
            <a href="/explore"   class="nav-item {{ str_starts_with($currentPath, 'explore') ? 'active' : '' }}"><span class="nav-icon">🔍</span><span class="nav-label">Keşfet</span></a>
            <a href="/create"    class="nav-item {{ str_starts_with($currentPath, 'create') ? 'active' : '' }}"><span class="nav-icon">➕</span><span class="nav-label">Oluştur</span></a>
            <a href="/notifications" class="nav-item {{ str_starts_with($currentPath, 'notifications') ? 'active' : '' }}"><span class="nav-icon">🔔</span><span class="nav-label">Bildirim</span></a>
            <a href="/profile"   class="nav-item {{ str_starts_with($currentPath, 'profile') ? 'active' : '' }}"><span class="nav-icon">👤</span><span class="nav-label">Profil</span></a>
        </div>
    </nav>

    {{-- ── Global Elements ───────────────────────────────────────────── --}}
    <div id="toast-root" class="fixed top-4 right-4 z-50 space-y-3"></div>

    @include('components.tier-modal')
    @include('components.payment-modal')
    @include('components.tip-modal')
    @include('components.comment-modal')
</body>
</html>
