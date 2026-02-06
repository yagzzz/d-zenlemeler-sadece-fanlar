@extends('layouts.app')

@section('content')
<div data-page="creator" data-username="{{ $username }}" data-profile-endpoint="/creators/{{ $username }}/profile" data-contents-endpoint="/creators/{{ $username }}/contents" data-tiers-endpoint="{{ config('features.flags.tiers') ? '/creators/'.$username.'/tiers' : '' }}" data-analytics-endpoint="{{ config('features.flags.analytics_stub') ? '/api/creators/'.$username.'/analytics' : '' }}">
    @if (!config('features.flags.ui'))
        <div class="content-column"><div class="post-box p-4"><p class="text-bold">Feature disabled</p></div></div>
    @else
        {{-- Cover --}}
        <div class="profile-cover">
            <div style="position:absolute;inset:0;background:linear-gradient(to right,rgba(217,70,239,0.3),transparent,rgba(34,211,238,0.2));"></div>
        </div>

        <div class="content-column" style="margin-top:-1.5rem;">
            {{-- Avatar + Info --}}
            <div class="d-flex align-items-end gap-3 mb-3">
                <div id="creator-avatar" class="profile-avatar">{{ strtoupper(substr($username, 0, 1)) }}</div>
                <div style="flex:1;padding-bottom:0.25rem;">
                    <h1 id="creator-name" class="text-bold" style="font-size:1.25rem;">{{ $username }}</h1>
                    <p class="text-muted" style="font-size:0.875rem;">@{{ $username }}</p>
                </div>
            </div>

            <p id="creator-tagline" class="text-muted mb-3" style="font-size:0.875rem;">Premium içerikler burada.</p>

            {{-- Action Buttons --}}
            <div class="d-flex gap-3 mb-4">
                <button id="subscribe-cta" class="btn btn-primary" style="flex:1;padding:0.75rem;">Abone Ol</button>
                <button id="tip-cta" class="btn btn-outline" style="flex:1;padding:0.75rem;">💎 Tip Gönder</button>
            </div>

            {{-- Stats --}}
            <div class="d-flex gap-3 mb-4" style="display:grid;grid-template-columns:repeat(3,1fr);gap:0.75rem;">
                <div class="stat-card">
                    <p id="creator-tips-total" class="text-bold" style="font-size:1rem;">0 XMR</p>
                    <p id="creator-tips-count" class="text-muted" style="font-size:0.625rem;">0 tip</p>
                </div>
                <div class="stat-card">
                    <p id="creator-subscribers" class="text-bold" style="font-size:1rem;">0</p>
                    <p class="text-muted" style="font-size:0.625rem;">Abone</p>
                </div>
                <div class="stat-card">
                    <p id="creator-post-count" class="text-bold" style="font-size:1rem;">—</p>
                    <p class="text-muted" style="font-size:0.625rem;">İçerik</p>
                </div>
            </div>

            {{-- Tab Nav --}}
            <div class="d-flex gap-1 mb-4" style="border-bottom:1px solid rgba(255,255,255,0.06);">
                <button class="tab-btn active" data-tab="posts">Gönderiler</button>
                <button class="tab-btn" data-tab="tiers">Tier'ler</button>
            </div>

            {{-- Tab: Posts --}}
            <div id="tab-posts">
                <div id="creator-contents" class="posts-wrapper"></div>
            </div>

            {{-- Tab: Tiers --}}
            <div id="tab-tiers" class="hidden">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h2 class="text-bold" style="font-size:1rem;">Planlar</h2>
                    <button id="compare-tiers" class="btn-ghost" style="font-size:0.75rem;">Karşılaştır</button>
                </div>
                <div id="creator-tiers" style="display:grid;gap:1rem;"></div>
            </div>
        </div>
    @endif

    @include('components.content-card')
</div>
@endsection
