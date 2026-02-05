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
    <div class="min-h-screen pb-24">
        @yield('content')
    </div>

    <nav class="fixed bottom-0 left-0 right-0 z-40 bg-slate-950/90 backdrop-blur border-t border-white/10" data-nav="bottom">
        <div class="mx-auto max-w-4xl px-6 py-3 flex items-center justify-between text-xs uppercase tracking-widest text-slate-400">
            <a href="/" class="flex flex-col items-center gap-1 text-slate-100">
                <span class="text-xl">🏠</span>
                <span>Home</span>
            </a>
            <a href="/explore" class="flex flex-col items-center gap-1 hover:text-slate-100 transition-colors">
                <span class="text-xl">✨</span>
                <span>Explore</span>
            </a>
            <a href="/create" class="flex flex-col items-center gap-1 hover:text-slate-100 transition-colors">
                <span class="text-xl">➕</span>
                <span>Create</span>
            </a>
            <a href="/inbox" class="flex flex-col items-center gap-1 hover:text-slate-100 transition-colors">
                <span class="text-xl">💬</span>
                <span>Inbox</span>
            </a>
            <a href="/profile" class="flex flex-col items-center gap-1 hover:text-slate-100 transition-colors">
                <span class="text-xl">👤</span>
                <span>Profile</span>
            </a>
        </div>
    </nav>

    <div id="toast-root" class="fixed top-4 right-4 z-50 space-y-3"></div>

    @include('components.tier-modal')
    @include('components.payment-modal')
    @include('components.tip-modal')
</body>
</html>
