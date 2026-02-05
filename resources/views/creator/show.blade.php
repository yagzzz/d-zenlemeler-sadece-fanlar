@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-6 py-8" data-page="creator" data-username="{{ $username }}" data-profile-endpoint="/api/creators/{{ $username }}/profile" data-contents-endpoint="/creators/{{ $username }}/contents" data-tiers-endpoint="{{ config('features.flags.tiers') ? '/creators/'.$username.'/tiers' : '' }}" data-analytics-endpoint="{{ config('features.flags.analytics_stub') ? '/api/creators/'.$username.'/analytics' : '' }}">
    @if (!config('features.flags.ui'))
        <div class="rounded-3xl border border-white/10 bg-white/5 p-6">
            <p class="text-lg font-semibold">Feature disabled</p>
            <p class="text-sm text-slate-400">UI özelliği kapalı. Lütfen daha sonra tekrar deneyin.</p>
        </div>
    @else
        <div class="relative overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-slate-900 via-slate-950 to-black p-6">
            <div class="absolute inset-0 bg-gradient-to-r from-fuchsia-500/20 via-transparent to-cyan-400/10"></div>
            <div class="relative flex flex-col gap-6">
                <div class="flex items-center gap-4">
                    <div id="creator-avatar" class="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-fuchsia-500 to-cyan-400 text-xl font-bold text-white">{{ strtoupper(substr($username, 0, 1)) }}</div>
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-slate-400">Creator</p>
                        <h1 id="creator-name" class="text-2xl font-semibold">{{ $username }}</h1>
                        <p id="creator-tagline" class="text-sm text-slate-300">Premium içerikler burada.</p>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button id="subscribe-cta" class="flex-1 rounded-2xl bg-white text-slate-900 py-3 text-sm font-semibold">Abone Ol</button>
                    <button id="tip-cta" class="flex-1 rounded-2xl border border-white/20 py-3 text-sm font-semibold">Tip gönder</button>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <div class="grid gap-4 md:grid-cols-3">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-widest text-slate-400">Tips</p>
                    <p id="creator-tips-total" class="mt-2 text-lg font-semibold">0 XMR</p>
                    <p id="creator-tips-count" class="text-xs text-slate-500">0 tip</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-widest text-slate-400">Subscribers</p>
                    <p id="creator-subscribers" class="mt-2 text-lg font-semibold">0</p>
                    <p class="text-xs text-slate-500">Active</p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-widest text-slate-400">Tierler</p>
                    <p class="mt-2 text-sm text-slate-300">Aşağıdan planları incele</p>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold">Tier’ler</h2>
                <button id="compare-tiers" class="text-xs text-slate-400">Karşılaştır</button>
            </div>
            <div id="creator-tiers" class="grid gap-4 md:grid-cols-2"></div>
        </div>

        <div class="mt-8">
            <h2 class="text-lg font-semibold mb-4">İçerikler</h2>
            <div id="creator-contents" class="space-y-6"></div>
        </div>
    @endif

    @include('components.content-card')
</div>
@endsection
