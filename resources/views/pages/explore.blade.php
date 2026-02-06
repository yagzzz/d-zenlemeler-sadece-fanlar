@extends('layouts.app')

@section('content')
<div class="content-column" data-page="explore">
    <div class="mb-4">
        <h1 class="text-bold" style="font-size:1.25rem;">Keşfet</h1>
        <p class="text-muted" style="font-size:0.875rem;">Popüler creator'ları ve içerikleri keşfet.</p>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <div style="position:relative;">
            <svg style="position:absolute;left:1rem;top:50%;transform:translateY(-50%);color:#64748b;" class="icon-small" viewBox="0 0 512 512" fill="none" stroke="currentColor" stroke-width="32"><circle cx="221" cy="221" r="165"/><path d="M430.9 430.9L338.1 338.1" stroke-linecap="round"/></svg>
            <input type="text" placeholder="Creator veya içerik ara…" class="sf-input" style="padding-left:3rem;" />
        </div>
    </div>

    {{-- Categories --}}
    <div class="mb-4 d-flex flex-wrap gap-2" data-testid="explore-categories">
        @foreach (['Tümü', 'Müzik', 'Sanat', 'Fitness', 'Eğitim', 'Yaşam', 'Teknoloji'] as $cat)
            <button class="pill {{ $loop->first ? 'pill-active' : 'pill-default' }}">{{ $cat }}</button>
        @endforeach
    </div>

    {{-- Featured Creators --}}
    <div class="mb-6">
        <h2 class="mb-3 text-bold" style="font-size:1rem;">Öne Çıkan Creator'lar</h2>
        <div style="display:grid;gap:0.75rem;grid-template-columns:repeat(1,1fr);" data-testid="explore-creators">
            @php
                $creators = \App\Models\User::where('role', 'creator')
                    ->whereNotNull('creator_approved_at')
                    ->with('creatorProfile')
                    ->take(6)
                    ->get();
            @endphp
            @forelse ($creators as $creator)
                <a href="/c/{{ $creator->username }}" class="creator-card">
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

    {{-- Trending Content --}}
    <div>
        <h2 class="mb-3 text-bold" style="font-size:1rem;">Trend İçerikler</h2>
        <div style="display:grid;gap:0.75rem;" data-testid="explore-trending">
            @php
                $contents = \App\Models\Content::where('is_published', true)
                    ->where('visibility', 'public')
                    ->with('creator')
                    ->latest('published_at')
                    ->take(4)
                    ->get();
            @endphp
            @forelse ($contents as $content)
                <div class="post-box p-4">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div style="width:28px;height:28px;border-radius:50%;background:linear-gradient(135deg,rgba(217,70,239,0.6),rgba(34,211,238,0.6));display:flex;align-items:center;justify-content:center;color:white;font-size:0.625rem;font-weight:700;">{{ strtoupper(substr($content->creator->name ?? '?', 0, 1)) }}</div>
                        <a href="/c/{{ $content->creator->username ?? '' }}" class="text-muted" style="font-size:0.75rem;">{{ $content->creator->username ?? 'anon' }}</a>
                    </div>
                    <p class="text-bold" style="font-size:0.875rem;">{{ $content->title }}</p>
                    <p class="mt-1 text-muted line-clamp-1" style="font-size:0.75rem;">{{ Str::limit($content->body, 100) }}</p>
                </div>
            @empty
                <div class="empty-state">
                    <p class="text-muted" style="font-size:0.875rem;">Henüz trend içerik yok.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
