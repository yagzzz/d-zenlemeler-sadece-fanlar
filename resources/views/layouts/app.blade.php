<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sadece Fanlar') }}</title>
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
</head>
<body class="d-flex flex-column" data-ui-enabled="{{ config('features.flags.ui') ? '1' : '0' }}" data-ui-polish-enabled="{{ config('features.flags.ui_polish') ? '1' : '0' }}" data-app-env="{{ app()->environment() }}">

    @php($currentPath = request()->path())

    <div class="flex-fill">
        <div class="container-xl overflow-hidden">
            <div class="row main-wrapper">
                {{-- ── Desktop Side Menu (col-2/col-md-3) ────────────── --}}
                <div class="col-2 col-md-3 pt-4 p-0 d-none d-md-block">
                    <div class="side-menu px-1 px-md-2 px-lg-3">
                        {{-- User details --}}
                        @auth
                        <div class="user-details mb-4 d-flex pointer-cursor flex-row">
                            <div class="ml-0 ml-md-2">
                                <div class="user-avatar rounded-circle d-flex align-items-center justify-content-center">
                                    {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                                </div>
                            </div>
                            <div class="d-none d-lg-block overflow-hidden">
                                <div class="pl-2 d-flex justify-content-center flex-column overflow-hidden">
                                    <div class="ml-2 d-flex flex-column overflow-hidden">
                                        <span class="text-bold text-truncate">{{ auth()->user()->name ?? 'Misafir' }}</span>
                                        <span class="text-muted"><span>@</span>{{ auth()->user()->username ?? '' }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @else
                        <div class="user-details mb-4 d-flex pointer-cursor flex-row">
                            <div class="ml-0 ml-md-2">
                                <div class="user-avatar rounded-circle d-flex align-items-center justify-content-center">😎</div>
                            </div>
                            <div class="d-none d-lg-block overflow-hidden">
                                <div class="pl-2 d-flex justify-content-center flex-column overflow-hidden">
                                    <div class="ml-2 d-flex flex-column overflow-hidden">
                                        <span class="text-bold text-truncate">Sadece Fanlar</span>
                                        <span class="text-muted"><a href="/login" style="color:#d946ef;">Giriş Yap</a></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endauth

                        {{-- Navigation --}}
                        <ul class="nav flex-column user-side-menu">
                            <li class="nav-item">
                                <a href="/" class="h-pill h-pill-primary nav-link {{ $currentPath === '/' ? 'active' : '' }} d-flex justify-content-between">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="icon-wrapper d-flex justify-content-center align-items-center">
                                            <svg class="icon-large" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M80 212v236a16 16 0 0016 16h96V328a24 24 0 0124-24h80a24 24 0 0124 24v136h96a16 16 0 0016-16V212" stroke-linecap="round" stroke-linejoin="round"/><path d="M480 256L266.89 52c-5-5.28-16.69-5.34-21.78 0L32 256" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </div>
                                        <span class="d-none d-lg-block ml-2 text-truncate side-menu-label">Ana Sayfa</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/notifications" class="nav-link h-pill h-pill-primary {{ str_starts_with($currentPath, 'notifications') ? 'active' : '' }} d-flex justify-content-between">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="icon-wrapper d-flex justify-content-center align-items-center position-relative">
                                            <svg class="icon-large" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M427.68 351.13C402 320 383.87 304 383.87 217.27 383.87 138 343.35 109.73 310 96c-4.43-1.82-8.6-6-9.95-10.55C294.2 65.54 277.8 48 256 48s-38.21 17.55-44 37.47c-1.35 4.6-5.52 8.71-9.95 10.53-33.39 13.75-73.87 41.92-73.87 121.27 0 86.75-18.18 102.77-44.12 133.92C71.82 366.47 81.61 384 104.43 384h303.14c22.52 0 32.59-17.55 20.11-32.87z" stroke-linecap="round" stroke-linejoin="round"/><path d="M320 384v16a64 64 0 01-128 0v-16" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            <div class="menu-notification-badge d-none" id="notif-badge-desktop">0</div>
                                        </div>
                                        <span class="d-none d-lg-block ml-2 text-truncate side-menu-label">Bildirimler</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/inbox" class="nav-link h-pill h-pill-primary {{ str_starts_with($currentPath, 'inbox') ? 'active' : '' }} d-flex justify-content-between">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="icon-wrapper d-flex justify-content-center align-items-center position-relative">
                                            <svg class="icon-large" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M408 64H104a56.16 56.16 0 00-56 56v192a56.16 56.16 0 0056 56h40v80l93.72-78.14a8 8 0 015.13-1.86H408a56.16 56.16 0 0056-56V120a56.16 56.16 0 00-56-56z" stroke-linejoin="round"/></svg>
                                            <div class="menu-notification-badge d-none" id="msg-badge-desktop">0</div>
                                        </div>
                                        <span class="d-none d-lg-block ml-2 text-truncate side-menu-label">Mesajlar</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/bookmarks" class="nav-link {{ str_starts_with($currentPath, 'bookmarks') ? 'active' : '' }} h-pill h-pill-primary d-flex justify-content-between">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="icon-wrapper d-flex justify-content-center align-items-center">
                                            <svg class="icon-large" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M352 48H160a48 48 0 00-48 48v368l144-128 144 128V96a48 48 0 00-48-48z" stroke-linejoin="round"/></svg>
                                        </div>
                                        <span class="d-none d-lg-block ml-2 text-truncate side-menu-label">Kaydedilenler</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/explore" class="nav-link {{ str_starts_with($currentPath, 'explore') ? 'active' : '' }} h-pill h-pill-primary d-flex justify-content-between">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="icon-wrapper d-flex justify-content-center align-items-center">
                                            <svg class="icon-large" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><circle cx="256" cy="256" r="208"/><path d="M200 153.4c55.45-25.36 126.87-7.4 152.1 48s-.1 127.46-55.45 152.82S169.78 361.6 144.55 306.17 144.55 178.77 200 153.4z"/></svg>
                                        </div>
                                        <span class="d-none d-lg-block ml-2 text-truncate side-menu-label">Keşfet</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/profile" class="nav-link {{ str_starts_with($currentPath, 'profile') ? 'active' : '' }} h-pill h-pill-primary d-flex justify-content-between">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="icon-wrapper d-flex justify-content-center align-items-center">
                                            <svg class="icon-large" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M344 144c-3.92 52.87-44 96-88 96s-84.15-43.12-88-96c-4-55 35-96 88-96s92 42 88 96z" stroke-linecap="round" stroke-linejoin="round"/><path d="M256 304c-87 0-175.3 48-191.64 138.6C62.39 453.52 68.57 464 80 464h352c11.44 0 17.62-10.48 15.65-21.4C431.3 352 343 304 256 304z" stroke-miterlimit="10"/></svg>
                                        </div>
                                        <span class="d-none d-lg-block ml-2 text-truncate side-menu-label">Profil</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link h-pill h-pill-primary text-muted d-flex justify-content-between open-menu">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="icon-wrapper d-flex justify-content-center align-items-center">
                                            <svg class="icon-large" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><circle cx="256" cy="256" r="26"/><circle cx="346" cy="256" r="26"/><circle cx="166" cy="256" r="26"/></svg>
                                        </div>
                                        <span class="d-none d-lg-block ml-2 text-truncate side-menu-label">Daha Fazla</span>
                                    </div>
                                </a>
                            </li>
                            @auth
                            <li class="nav-item mt-1">
                                <form method="POST" action="/logout">
                                    @csrf
                                    <button type="submit" class="nav-link h-pill h-pill-primary d-flex justify-content-between w-100" style="background:none;border:none;cursor:pointer;">
                                        <div class="d-flex justify-content-center align-items-center">
                                            <div class="icon-wrapper d-flex justify-content-center align-items-center">
                                                <svg class="icon-large" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M304 336v40a40 40 0 01-40 40H104a40 40 0 01-40-40V136a40 40 0 0140-40h152c22.09 0 48 17.91 48 40v40M368 336l80-80-80-80M176 256h256" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                            </div>
                                            <span class="d-none d-lg-block ml-2 text-truncate side-menu-label">Çıkış Yap</span>
                                        </div>
                                    </button>
                                </form>
                            </li>
                            @endauth
                            @guest
                            <li class="nav-item mt-1">
                                <a href="/login" class="nav-link h-pill h-pill-primary d-flex justify-content-between">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="icon-wrapper d-flex justify-content-center align-items-center">
                                            <svg class="icon-large" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M192 176v-40a40 40 0 0140-40h152a40 40 0 0140 40v240a40 40 0 01-40 40H240c-22.09 0-48-17.91-48-40v-40M96 256h256M304 176l80 80-80 80" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </div>
                                        <span class="d-none d-lg-block ml-2 text-truncate side-menu-label">Giriş Yap</span>
                                    </div>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="/register" class="nav-link h-pill h-pill-primary d-flex justify-content-between">
                                    <div class="d-flex justify-content-center align-items-center">
                                        <div class="icon-wrapper d-flex justify-content-center align-items-center">
                                            <svg class="icon-large" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M376 144c-3.92 52.87-44 96-88 96s-84.15-43.12-88-96c-4-55 35-96 88-96s92 42 88 96z" stroke-linecap="round" stroke-linejoin="round"/><path d="M288 304c-87 0-175.3 48-191.64 138.6-1.97 10.9 4.21 21.4 15.65 21.4h352c11.44 0 17.62-10.48 15.65-21.4C463.3 352 375 304 288 304z" stroke-miterlimit="10"/><path d="M88 176v112M144 232H32" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                        </div>
                                        <span class="d-none d-lg-block ml-2 text-truncate side-menu-label">Kayıt Ol</span>
                                    </div>
                                </a>
                            </li>
                            @endguest
                            {{-- New Post CTA --}}
                            @auth
                            <li class="nav-item mt-3">
                                <a role="button" class="btn btn-round btn-primary btn-block" href="/create">
                                    <span class="d-none d-lg-block text-truncate new-post-label">Yeni Gönderi</span>
                                    <span class="d-block d-lg-none d-flex align-items-center justify-content-center">
                                        <svg class="icon-medium" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M256 112v288M400 256H112" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </span>
                                </a>
                            </li>
                            @endauth
                        </ul>
                    </div>
                </div>

                {{-- ── Main Content Column (col-12/col-md-9) ─────────── --}}
                <div class="col-12 col-md-9 min-vh-100 border-left px-0 content-wrapper">
                    @yield('content')
                </div>
            </div>

            {{-- ── Mobile Bottom Nav ──────────────────────────────────── --}}
            <div class="d-block d-md-none fixed-bottom" data-nav="bottom">
                <div class="mobile-bottom-nav border-top z-index-3 py-1 neutral-bg">
                    <div class="d-flex justify-content-between w-100 py-2 px-2">
                        <a href="/" class="h-pill h-pill-primary nav-link d-flex justify-content-between px-3 {{ $currentPath === '/' ? 'active' : '' }}">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="icon-wrapper d-flex justify-content-center align-items-center">
                                    <svg class="icon-large" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M80 212v236a16 16 0 0016 16h96V328a24 24 0 0124-24h80a24 24 0 0124 24v136h96a16 16 0 0016-16V212" stroke-linecap="round" stroke-linejoin="round"/><path d="M480 256L266.89 52c-5-5.28-16.69-5.34-21.78 0L32 256" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                            </div>
                        </a>
                        <a href="/notifications" class="h-pill h-pill-primary nav-link d-flex justify-content-between px-3 {{ str_starts_with($currentPath, 'notifications') ? 'active' : '' }}">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="icon-wrapper d-flex justify-content-center align-items-center position-relative">
                                    <svg class="icon-large" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M427.68 351.13C402 320 383.87 304 383.87 217.27 383.87 138 343.35 109.73 310 96c-4.43-1.82-8.6-6-9.95-10.55C294.2 65.54 277.8 48 256 48s-38.21 17.55-44 37.47c-1.35 4.6-5.52 8.71-9.95 10.53-33.39 13.75-73.87 41.92-73.87 121.27 0 86.75-18.18 102.77-44.12 133.92C71.82 366.47 81.61 384 104.43 384h303.14c22.52 0 32.59-17.55 20.11-32.87z" stroke-linecap="round" stroke-linejoin="round"/><path d="M320 384v16a64 64 0 01-128 0v-16" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    <div class="menu-notification-badge d-none" id="notif-badge-mobile">0</div>
                                </div>
                            </div>
                        </a>
                        <a href="/create" class="h-pill h-pill-primary nav-link d-flex justify-content-between px-3 {{ str_starts_with($currentPath, 'create') ? 'active' : '' }}">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="icon-wrapper d-flex justify-content-center align-items-center">
                                    <svg class="icon-large" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M448 256c0-106-86-192-192-192S64 150 64 256s86 192 192 192 192-86 192-192z"/><path d="M256 176v160M336 256H176" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </div>
                            </div>
                        </a>
                        <a href="/inbox" class="h-pill h-pill-primary nav-link d-flex justify-content-between px-3 {{ str_starts_with($currentPath, 'inbox') ? 'active' : '' }}">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="icon-wrapper d-flex justify-content-center align-items-center position-relative">
                                    <svg class="icon-large" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><path d="M408 64H104a56.16 56.16 0 00-56 56v192a56.16 56.16 0 0056 56h40v80l93.72-78.14a8 8 0 015.13-1.86H408a56.16 56.16 0 0056-56V120a56.16 56.16 0 00-56-56z" stroke-linejoin="round"/></svg>
                                    <div class="menu-notification-badge d-none" id="msg-badge-mobile">0</div>
                                </div>
                            </div>
                        </a>
                        <a href="{{ auth()->check() ? '/profile' : '/login' }}" class="h-pill h-pill-primary nav-link d-flex justify-content-between px-3">
                            <div class="d-flex justify-content-center align-items-center">
                                <div class="icon-wrapper d-flex justify-content-center align-items-center">
                                    <div class="user-avatar rounded-circle w-32 d-flex align-items-center justify-content-center mobile-nav-avatar">
                                        @auth{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}@else😎@endauth
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Global Elements ───────────────────────────────────────────── --}}
    <div id="toast-root" class="toast-container top-right"></div>

    @include('components.tier-modal')
    @include('components.payment-modal')
    @include('components.tip-modal')
    @include('components.comment-modal')
</body>
</html>
