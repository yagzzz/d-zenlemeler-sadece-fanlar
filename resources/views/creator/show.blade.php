@extends('layouts.app')

@section('content')
<div data-page="creator" data-username="{{ $username }}" data-profile-endpoint="/api/creators/{{ $username }}/profile" data-contents-endpoint="/creators/{{ $username }}/contents" data-tiers-endpoint="{{ config('features.flags.tiers') ? '/creators/'.$username.'/tiers' : '' }}" data-analytics-endpoint="{{ config('features.flags.analytics_stub') ? '/api/creators/'.$username.'/analytics' : '' }}">
    @if (!config('features.flags.ui'))
        <div class="content-column"><div class="post-box p-6"><p class="text-sm font-semibold">Feature disabled</p></div></div>
    @else
        {{-- Cover --}}
        <div class="profile-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-fuchsia-500/30 via-transparent to-cyan-400/20"></div>
        </div>

        <div class="content-column -mt-6">
            {{-- Avatar + Info --}}
            <div class="flex items-end gap-4 mb-4">
                <div id="creator-avatar" class="profile-avatar">{{ strtoupper(substr($username, 0, 1)) }}</div>
                <div class="flex-1 pb-1">
                    <h1 id="creator-name" class="text-xl font-bold">{{ $username }}</h1>
                    <p class="text-sm text-slate-400">@{{ $username }}</p>
                </div>
            </div>

            <p id="creator-tagline" class="text-sm text-slate-300 mb-4">Premium içerikler burada.</p>

            {{-- Action Buttons --}}
            <div class="flex gap-3 mb-6">
                <button id="subscribe-cta" class="btn-primary flex-1 py-3">Abone Ol</button>
                <button id="tip-cta" class="btn-outline flex-1 py-3">💎 Tip Gönder</button>
            </div>

            {{-- Stats --}}
            <div class="grid grid-cols-3 gap-3 mb-6">
                <div class="rounded-xl bg-white/5 border border-white/5 p-3 text-center">
                    <p id="creator-tips-total" class="text-base font-semibold">0 XMR</p>
                    <p id="creator-tips-count" class="text-[10px] text-slate-500">0 tip</p>
                </div>
                <div class="rounded-xl bg-white/5 border border-white/5 p-3 text-center">
                    <p id="creator-subscribers" class="text-base font-semibold">0</p>
                    <p class="text-[10px] text-slate-500">Abone</p>
                </div>
                <div class="rounded-xl bg-white/5 border border-white/5 p-3 text-center">
                    <p id="creator-post-count" class="text-base font-semibold">—</p>
                    <p class="text-[10px] text-slate-500">İçerik</p>
                </div>
            </div>

            {{-- Tab Nav --}}
            <div class="flex gap-1 mb-6 border-b border-white/5">
                <button class="tab-btn px-4 py-2.5 text-sm font-medium border-b-2 border-fuchsia-500 text-white" data-tab="posts">Gönderiler</button>
                <button class="tab-btn px-4 py-2.5 text-sm font-medium border-b-2 border-transparent text-slate-400 hover:text-white" data-tab="tiers">Tier'ler</button>
            </div>

            {{-- Tab: Posts --}}
            <div id="tab-posts" class="space-y-4">
                <div id="creator-contents" class="space-y-4"></div>
            </div>

            {{-- Tab: Tiers --}}
            <div id="tab-tiers" class="hidden">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-base font-semibold">Planlar</h2>
                    <button id="compare-tiers" class="text-xs text-slate-400 hover:text-white">Karşılaştır</button>
                </div>
                <div id="creator-tiers" class="grid gap-4 md:grid-cols-2"></div>
            </div>
        </div>
    @endif

    @include('components.content-card')
</div>
@endsection
