@extends('layouts.app')

@section('content')
<div class="content-column" data-page="bookmarks" data-bookmarks-endpoint="/api/bookmarks">
    <div class="mb-4">
        <h1 class="text-bold" style="font-size:1.25rem;">Kaydedilenler</h1>
        <p class="text-muted" style="font-size:0.875rem;">Kaydettiğin içeriklere hızlıca ulaş.</p>
    </div>

    <div id="bookmarks-skeleton">
        @for ($i = 0; $i < 2; $i++)
            <div class="skeleton-card mb-3"></div>
        @endfor
    </div>

    <div id="bookmarks-empty" class="hidden post-box p-6 text-center">
        <p style="font-size:2rem;">🔖</p>
        <h2 class="mt-2 text-bold" style="font-size:1rem;">Kayıtlı içerik yok</h2>
        <p class="mt-1 text-muted" style="font-size:0.875rem;">Beğendiğin içerikleri kaydet, sonra kolayca bul.</p>
        <a href="/explore" class="btn btn-primary mt-3" style="font-size:0.75rem;">Keşfet</a>
    </div>

    <div id="bookmarks-list" class="posts-wrapper"></div>

    @include('components.content-card')

    <script id="cta-labels" type="application/json">
        {"not_logged_in":"Ücretsiz Üye Ol","not_verified":"E-postanı doğrula","subscription_required":"Tier'leri Gör","tier_required":"Tier'leri Gör","ppv_required":"Satın Al"}
    </script>
</div>
@endsection
