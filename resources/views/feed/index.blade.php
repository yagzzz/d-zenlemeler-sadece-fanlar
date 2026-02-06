@extends('layouts.app')

@section('content')
<div class="content-column" data-page="feed" data-feed-endpoint="/feed">
    {{-- Quick Compose --}}
    <div class="post-box mb-3">
        <div class="post-header">
            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <a href="/create" class="sf-input" style="display:block; color:#64748b;">Ne düşünüyorsun?</a>
        </div>
    </div>

    @if (!config('features.flags.ui'))
        <div class="post-box p-4">
            <p class="text-bold">Feature disabled</p>
            <p class="text-muted" style="font-size:0.8125rem;">UI özelliği kapalı.</p>
        </div>
    @else
        {{-- Skeleton --}}
        <div id="feed-skeleton">
            @for ($i = 0; $i < 3; $i++)
                <div class="skeleton-card mb-3"></div>
            @endfor
        </div>

        {{-- Empty State --}}
        <div id="feed-empty" class="hidden">
            <div class="post-box p-6 text-center" data-testid="empty-state">
                <p style="font-size:2rem;">📭</p>
                <h2 class="mt-2 text-bold" style="font-size:1rem;">Henüz içerik yok</h2>
                <p class="mt-1 text-muted" style="font-size:0.875rem;">Creator'lar içerik paylaştıkça burada görünecek.</p>
                <a href="/explore" class="btn btn-primary mt-3" style="font-size:0.75rem;">Creator'ları Keşfet</a>
            </div>
        </div>

        {{-- Posts list (JS renders cards here) --}}
        <div id="feed-list" class="posts-wrapper"></div>

        {{-- Loading spinner --}}
        <div id="feed-loading" class="hidden text-center py-3">
            <div class="spinner-border" style="width:2rem;height:2rem;border:3px solid rgba(255,255,255,0.1);border-top-color:#d946ef;border-radius:50%;animation:spin 0.8s linear infinite;display:inline-block;"></div>
        </div>
    @endif

    <script id="cta-labels" type="application/json">
        {"not_logged_in":"Ücretsiz Üye Ol","not_verified":"E-postanı doğrula","subscription_required":"Tier'leri Gör","tier_required":"Tier'leri Gör","ppv_required":"Satın Al"}
    </script>

    @include('components.content-card')
</div>
@endsection
