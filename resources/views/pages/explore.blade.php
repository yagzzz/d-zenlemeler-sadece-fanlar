@extends('layouts.app')

@section('content')
<div class="content-column" data-page="explore">
    <div class="mb-4">
        <h1 class="text-bold" style="font-size:1.25rem;">Keşfet</h1>
        <p class="text-muted" style="font-size:0.875rem;">Popüler creator'ları ve içerikleri keşfet.</p>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <form method="GET" action="/explore" style="position:relative;">
            <input type="hidden" name="filter" value="{{ $filter ?? 'trending' }}" />
            <svg style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:#64748b;" class="icon-small" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><circle cx="221" cy="221" r="165"/><path d="M430.9 430.9L338.1 338.1" stroke-linecap="round"/></svg>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Creator veya içerik ara…" class="sf-input" style="padding-left:3rem;" />
        </form>
    </div>

    {{-- Filter Tabs --}}
    <div class="mb-4 d-flex flex-wrap gap-2" data-testid="explore-filters">
        <a href="/explore?filter=trending{{ request('q') ? '&q='.urlencode(request('q')) : '' }}" class="pill {{ ($filter ?? 'trending') === 'trending' ? 'pill-active' : 'pill-default' }}" style="text-decoration:none;">🔥 Trend</a>
        <a href="/explore?filter=latest{{ request('q') ? '&q='.urlencode(request('q')) : '' }}" class="pill {{ ($filter ?? '') === 'latest' ? 'pill-active' : 'pill-default' }}" style="text-decoration:none;">🕐 En Yeni</a>
        <a href="/explore?filter=creators{{ request('q') ? '&q='.urlencode(request('q')) : '' }}" class="pill {{ ($filter ?? '') === 'creators' ? 'pill-active' : 'pill-default' }}" style="text-decoration:none;">👥 Creator'lar</a>
    </div>

    {{-- Featured Creators --}}
    @if (($filter ?? 'trending') !== 'latest')
    <div class="mb-6">
        <h2 class="mb-3 text-bold" style="font-size:1rem;">Öne Çıkan Creator'lar</h2>
        <div style="display:grid;gap:0.75rem;grid-template-columns:repeat(1,1fr);" data-testid="explore-creators">
            @forelse ($creators as $creator)
                <a href="/c/{{ $creator->username }}" class="creator-card" style="text-decoration:none;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="creator-avatar">{{ strtoupper(substr($creator->name, 0, 1)) }}</div>
                        <div style="min-width:0;">
                            <p class="text-bold" style="font-size:0.875rem;">{{ $creator->name }}</p>
                            <p class="text-muted" style="font-size:0.75rem;">@{{ $creator->username }}</p>
                        </div>
                    </div>
                    @if ($creator->creatorProfile?->tagline)
                        <p class="mt-2 text-muted line-clamp-1" style="font-size:0.75rem;">{{ $creator->creatorProfile->tagline }}</p>
                    @endif
                </a>
            @empty
                <div class="empty-state">
                    <p class="text-muted" style="font-size:0.875rem;">Henüz creator bulunmuyor.</p>
                </div>
            @endforelse
        </div>
    </div>
    @endif

    {{-- Trending / Latest Content --}}
    @if (($filter ?? 'trending') !== 'creators')
    <div>
        <h2 class="mb-3 text-bold" style="font-size:1rem;">
            {{ ($filter ?? 'trending') === 'latest' ? 'En Yeni İçerikler' : 'Trend İçerikler' }}
        </h2>
        <div style="display:grid;gap:0.75rem;" data-testid="explore-trending">
            @forelse ($contents as $content)
                <a href="/c/{{ $content->creator->username ?? '' }}" class="post-box p-4" style="text-decoration:none;display:block;" data-content-id="{{ $content->id }}">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,rgba(217,70,239,0.6),rgba(34,211,238,0.6));display:flex;align-items:center;justify-content:center;color:white;font-size:0.625rem;font-weight:700;">{{ strtoupper(substr($content->creator->name ?? '?', 0, 1)) }}</div>
                        <span class="text-muted" style="font-size:0.75rem;">{{ $content->creator->username ?? 'anon' }}</span>
                        @if (isset($content->reactions_count))
                        <span class="text-muted" style="font-size:0.625rem;margin-left:auto;">
                            ❤️ {{ $content->reactions_count }} · 💬 {{ $content->comments_count }} · 🔖 {{ $content->bookmarks_count }}
                        </span>
                        @endif
                    </div>
                    <p class="text-bold" style="font-size:0.875rem;">{{ $content->title }}</p>
                    <p class="mt-1 text-muted line-clamp-1" style="font-size:0.75rem;">{{ Str::limit($content->body, 100) }}</p>
                </a>
            @empty
                <div class="empty-state">
                    <p class="text-muted" style="font-size:0.875rem;">Henüz trend içerik yok.</p>
                </div>
            @endforelse
        </div>
    </div>
    @endif
</div>
@endsection
