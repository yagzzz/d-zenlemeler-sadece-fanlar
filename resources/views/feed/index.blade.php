@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-6 py-8" data-page="feed" data-feed-endpoint="/feed">
    <div class="flex items-center justify-between mb-6">
        <div>
            <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Feed</p>
            <h1 class="text-3xl font-semibold">Premium içerikler</h1>
        </div>
        <a href="/explore" class="rounded-full bg-white/10 px-4 py-2 text-sm font-medium text-slate-200 hover:bg-white/20 transition-colors">Keşfet</a>
    </div>

    @if (!config('features.flags.ui'))
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <p class="text-lg font-semibold">Feature disabled</p>
            <p class="text-sm text-slate-400">UI özelliği kapalı. Lütfen daha sonra tekrar deneyin.</p>
        </div>
    @else
        <div id="feed-skeleton" class="space-y-6">
            @for ($i = 0; $i < 3; $i++)
                <div class="skeleton-card"></div>
            @endfor
        </div>

        <div id="feed-empty" class="hidden">
            <div class="rounded-3xl border border-dashed border-white/20 bg-white/5 p-8 text-center" data-testid="empty-state">
                <p class="text-4xl">📭</p>
                <h2 class="mt-4 text-lg font-semibold">Henüz içerik yok</h2>
                <p class="mt-2 text-sm text-slate-400">Creator'lar içerik paylaştıkça burada görünecek.</p>
                <a href="/explore" class="mt-4 inline-block rounded-full bg-white/10 px-6 py-2 text-sm font-medium text-slate-200 hover:bg-white/20 transition-colors">Creator'ları Keşfet</a>
            </div>
        </div>

        <div id="feed-list" class="space-y-6"></div>
    @endif

    <script id="cta-labels" type="application/json">
        {"not_logged_in":"Ücretsiz Üye Ol","not_verified":"E-postanı doğrula","subscription_required":"Tier’leri Gör","tier_required":"Tier’leri Gör","ppv_required":"Satın Al"}
    </script>

    @include('components.content-card')
</div>
@endsection
