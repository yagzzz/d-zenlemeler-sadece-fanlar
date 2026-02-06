<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kayıt Ol — {{ config('app.name', 'Sadece Fanlar') }}</title>
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
<body class="d-flex flex-column" style="min-height:100vh;align-items:center;justify-content:center;">
    <div style="width:100%;max-width:400px;padding:2rem;">
        {{-- Logo --}}
        <div class="text-center mb-4">
            <span style="font-size:2.5rem;">😎</span>
            <h1 class="text-bold mt-2" style="font-size:1.5rem;">{{ config('app.name', 'Sadece Fanlar') }}</h1>
            <p class="text-muted mt-1" style="font-size:0.875rem;">Yeni hesap oluştur</p>
        </div>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="mb-3" style="background:rgba(244,63,94,0.15);border:1px solid rgba(244,63,94,0.3);border-radius:0.75rem;padding:0.75rem 1rem;">
                @foreach ($errors->all() as $error)
                    <p style="font-size:0.75rem;color:#fda4af;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="/register" style="display:flex;flex-direction:column;gap:1rem;">
            @csrf
            <div>
                <label class="text-muted" style="font-size:0.625rem;text-transform:uppercase;letter-spacing:0.1em;">İsim</label>
                <input type="text" name="name" value="{{ old('name') }}" class="sf-input mt-1" required autofocus />
            </div>
            <div>
                <label class="text-muted" style="font-size:0.625rem;text-transform:uppercase;letter-spacing:0.1em;">Kullanıcı Adı</label>
                <input type="text" name="username" value="{{ old('username') }}" class="sf-input mt-1" required />
            </div>
            <div>
                <label class="text-muted" style="font-size:0.625rem;text-transform:uppercase;letter-spacing:0.1em;">E-posta</label>
                <input type="email" name="email" value="{{ old('email') }}" class="sf-input mt-1" required />
            </div>
            <div>
                <label class="text-muted" style="font-size:0.625rem;text-transform:uppercase;letter-spacing:0.1em;">Şifre</label>
                <input type="password" name="password" class="sf-input mt-1" required />
            </div>
            <div>
                <label class="text-muted" style="font-size:0.625rem;text-transform:uppercase;letter-spacing:0.1em;">Şifre Tekrar</label>
                <input type="password" name="password_confirmation" class="sf-input mt-1" required />
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%;padding:0.75rem;font-size:0.875rem;">Kayıt Ol</button>
        </form>

        <p class="text-center mt-3" style="font-size:0.75rem;color:#94a3b8;">
            Zaten hesabın var mı? <a href="/login" style="color:#d946ef;text-decoration:underline;">Giriş Yap</a>
        </p>
    </div>
</body>
</html>
