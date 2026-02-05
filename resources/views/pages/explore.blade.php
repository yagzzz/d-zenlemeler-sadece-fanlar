@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-6 py-8" data-page="explore">
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Explore</p>
        <h1 class="text-3xl font-semibold">Keşfet</h1>
        <p class="mt-2 text-sm text-slate-400">Popüler creator'ları ve içerikleri keşfet.</p>
    </div>

    {{-- Search --}}
    <div class="mb-8">
        <div class="relative">
            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500">🔍</span>
            <input type="text" placeholder="Creator veya içerik ara…" class="w-full rounded-2xl border border-white/10 bg-white/5 py-3 pl-11 pr-4 text-sm text-white placeholder-slate-500 focus:border-fuchsia-400/50 focus:outline-none transition-colors" />
        </div>
    </div>

    {{-- Categories --}}
    <div class="mb-8 flex flex-wrap gap-2" data-testid="explore-categories">
        @foreach (['Tümü', 'Müzik', 'Sanat', 'Fitness', 'Eğitim', 'Yaşam', 'Teknoloji'] as $cat)
            <button class="rounded-full {{ $loop->first ? 'bg-white text-slate-900' : 'bg-white/10 text-slate-300 hover:bg-white/20' }} px-4 py-2 text-xs font-semibold transition-colors">{{ $cat }}</button>
        @endforeach
    </div>

    {{-- Featured Creators --}}
    <div class="mb-8">
        <h2 class="mb-4 text-lg font-semibold">Öne Çıkan Creator'lar</h2>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" data-testid="explore-creators">
            @php
                $creators = \App\Models\User::where('role', 'creator')
                    ->whereNotNull('creator_approved_at')
                    ->with('creatorProfile')
                    ->take(6)
                    ->get();
            @endphp
            @forelse ($creators as $creator)
                <a href="/c/{{ $creator->username }}" class="group rounded-2xl border border-white/10 bg-white/5 p-5 hover:bg-white/10 transition-colors">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-fuchsia-500 to-cyan-400 text-sm font-bold text-white">{{ strtoupper(substr($creator->name, 0, 1)) }}</div>
                        <div>
                            <p class="text-sm font-semibold group-hover:text-white transition-colors">{{ $creator->name }}</p>
                            <p class="text-xs text-slate-400">@{{ $creator->username }}</p>
                        </div>
                    </div>
                    @if ($creator->creatorProfile?->tagline)
                        <p class="mt-3 text-xs text-slate-400 line-clamp-2">{{ $creator->creatorProfile->tagline }}</p>
                    @endif
                </a>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-white/20 bg-white/5 p-8 text-center">
                    <p class="text-sm text-slate-400">Henüz creator bulunmuyor.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- Trending --}}
    <div>
        <h2 class="mb-4 text-lg font-semibold">Trend İçerikler</h2>
        <div class="grid gap-4 sm:grid-cols-2" data-testid="explore-trending">
            @php
                $contents = \App\Models\Content::where('is_published', true)
                    ->where('visibility', 'public')
                    ->with('creator')
                    ->latest('published_at')
                    ->take(4)
                    ->get();
            @endphp
            @forelse ($contents as $content)
                <div class="rounded-2xl border border-white/10 bg-white/5 p-5 hover:bg-white/10 transition-colors">
                    <div class="flex items-center gap-2 mb-2">
                        <div class="flex h-6 w-6 items-center justify-center rounded-full bg-white/10 text-xs font-bold">{{ strtoupper(substr($content->creator->name ?? '?', 0, 1)) }}</div>
                        <span class="text-xs text-slate-400">{{ $content->creator->username ?? 'anon' }}</span>
                    </div>
                    <p class="text-sm font-semibold">{{ $content->title }}</p>
                    <p class="mt-1 text-xs text-slate-400 line-clamp-2">{{ Str::limit($content->body, 100) }}</p>
                </div>
            @empty
                <div class="col-span-full rounded-2xl border border-dashed border-white/20 bg-white/5 p-6 text-center">
                    <p class="text-sm text-slate-400">Henüz trend içerik yok.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
