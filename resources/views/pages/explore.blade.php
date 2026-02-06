@extends('layouts.app')

@section('content')
<div class="content-column" data-page="explore">
    <div class="mb-6">
        <h1 class="text-xl font-bold">Keşfet</h1>
        <p class="text-sm text-slate-400">Popüler creator'ları ve içerikleri keşfet.</p>
    </div>

    {{-- Search --}}
    <div class="mb-6">
        <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">🔍</span>
            <input type="text" placeholder="Creator veya içerik ara…" class="sf-input pl-11" />
        </div>
    </div>

    {{-- Categories --}}
    <div class="mb-6 flex flex-wrap gap-2" data-testid="explore-categories">
        @foreach (['Tümü', 'Müzik', 'Sanat', 'Fitness', 'Eğitim', 'Yaşam', 'Teknoloji'] as $cat)
            <button class="pill {{ $loop->first ? 'pill-active' : 'pill-default' }}">{{ $cat }}</button>
        @endforeach
    </div>

    {{-- Featured Creators --}}
    <div class="mb-8">
        <h2 class="mb-3 text-base font-semibold">Öne Çıkan Creator'lar</h2>
        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3" data-testid="explore-creators">
            @php
                $creators = \App\Models\User::where('role', 'creator')
                    ->whereNotNull('creator_approved_at')
                    ->with('creatorProfile')
                    ->take(6)
                    ->get();
            @endphp
            @forelse ($creators as $creator)
                <a href="/c/{{ $creator->username }}" class="group rounded-xl border border-white/5 bg-white/5 p-4 hover:bg-white/10 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-br from-fuchsia-500 to-cyan-400 text-sm font-bold text-white">{{ strtoupper(substr($creator->name, 0, 1)) }}</div>
                        <div class="min-w-0">
                            <p class="text-sm font-semibold group-hover:text-white transition-colors truncate">{{ $creator->name }}</p>
                            <p class="text-xs text-slate-500">@{{ $creator->username }}</p>
                        </div>
                    </div>
                    @if ($creator->creatorProfile?->tagline)
                        <p class="mt-2 text-xs text-slate-400 line-clamp-2">{{ $creator->creatorProfile->tagline }}</p>
                    @endif
                </a>
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-white/10 bg-white/5 p-6 text-center">
                    <p class="text-sm text-slate-400">Henüz creator bulunmuyor.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Trending --}}
    <div>
        <h2 class="mb-3 text-base font-semibold">Trend İçerikler</h2>
        <div class="grid gap-3 sm:grid-cols-2" data-testid="explore-trending">
            @php
                $contents = \App\Models\Content::where('is_published', true)
                    ->where('visibility', 'public')
                    ->with('creator')
                    ->latest('published_at')
                    ->take(4)
                    ->get();
            @endphp
            @forelse ($contents as $content)
                <div class="rounded-xl border border-white/5 bg-white/5 p-4 hover:bg-white/10 transition-colors">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br from-fuchsia-500/60 to-cyan-400/60 text-[10px] font-bold text-white">{{ strtoupper(substr($content->creator->name ?? '?', 0, 1)) }}</div>
                        <a href="/c/{{ $content->creator->username ?? '' }}" class="text-xs text-slate-400 hover:text-white">{{ $content->creator->username ?? 'anon' }}</a>
                    </div>
                    <p class="text-sm font-semibold">{{ $content->title }}</p>
                    <p class="mt-1 text-xs text-slate-400 line-clamp-2">{{ Str::limit($content->body, 100) }}</p>
                </div>
            @empty
                <div class="col-span-full rounded-xl border border-dashed border-white/10 bg-white/5 p-6 text-center">
                    <p class="text-sm text-slate-400">Henüz trend içerik yok.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
