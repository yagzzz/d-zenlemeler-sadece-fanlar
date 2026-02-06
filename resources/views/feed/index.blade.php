@extends('layouts.app')

@section('content')
<div class="content-column" data-page="feed" data-feed-endpoint="/feed">
    {{-- Quick Compose --}}
    <div class="post-box mb-4">
        <div class="p-4 flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-fuchsia-500 to-cyan-400 flex items-center justify-center text-xs font-bold text-white flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <a href="/create" class="flex-1 rounded-xl bg-white/5 border border-white/10 px-4 py-2.5 text-sm text-slate-500 hover:bg-white/10 transition-colors">Ne düşünüyorsun?</a>
        </div>
    </div>

    @if (!config('features.flags.ui'))
        <div class="post-box p-6">
            <p class="text-sm font-semibold">Feature disabled</p>
            <p class="text-xs text-slate-400">UI özelliği kapalı.</p>
        </div>
    @else
        <div id="feed-skeleton" class="space-y-4">
            @for ($i = 0; $i < 3; $i++)
                <div class="skeleton-card"></div>
            @endfor
        </div>

        <div id="feed-empty" class="hidden">
            <div class="post-box p-8 text-center" data-testid="empty-state">
                <p class="text-3xl">📭</p>
                <h2 class="mt-3 text-base font-semibold">Henüz içerik yok</h2>
                <p class="mt-1 text-sm text-slate-400">Creator'lar içerik paylaştıkça burada görünecek.</p>
                <a href="/explore" class="mt-4 inline-block btn-primary text-xs">Creator'ları Keşfet</a>
            </div>
        </div>

        <div id="feed-list" class="space-y-4"></div>
    @endif

    <script id="cta-labels" type="application/json">
        {"not_logged_in":"Ücretsiz Üye Ol","not_verified":"E-postanı doğrula","subscription_required":"Tier'leri Gör","tier_required":"Tier'leri Gör","ppv_required":"Satın Al"}
    </script>

    @include('components.content-card')
</div>
@endsection
