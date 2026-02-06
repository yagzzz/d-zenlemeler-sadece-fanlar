@extends('layouts.app')

@section('content')
<div class="content-column" data-page="bookmarks" data-bookmarks-endpoint="/api/bookmarks">
    <div class="mb-6">
        <h1 class="text-xl font-bold">Kaydedilenler</h1>
        <p class="text-sm text-slate-400">Kaydettiğin içeriklere hızlıca ulaş.</p>
    </div>

    <div id="bookmarks-skeleton" class="space-y-4">
        @for ($i = 0; $i < 2; $i++)
            <div class="skeleton-card"></div>
        @endfor
    </div>

    <div id="bookmarks-empty" class="hidden post-box p-8 text-center">
        <p class="text-3xl">🔖</p>
        <h2 class="mt-3 text-base font-semibold">Kayıtlı içerik yok</h2>
        <p class="mt-1 text-sm text-slate-400">Beğendiğin içerikleri kaydet, sonra kolayca bul.</p>
        <a href="/explore" class="mt-4 inline-block btn-primary text-xs">Keşfet</a>
    </div>

    <div id="bookmarks-list" class="space-y-4"></div>

    @include('components.content-card')

    <script id="cta-labels" type="application/json">
        {"not_logged_in":"Ücretsiz Üye Ol","not_verified":"E-postanı doğrula","subscription_required":"Tier'leri Gör","tier_required":"Tier'leri Gör","ppv_required":"Satın Al"}
    </script>
</div>
@endsection
