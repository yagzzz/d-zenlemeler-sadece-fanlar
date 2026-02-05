@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-4xl px-6 py-8" data-page="create">
    <div class="mb-6">
        <p class="text-xs uppercase tracking-[0.3em] text-slate-500">Create</p>
        <h1 class="text-3xl font-semibold">İçerik Oluştur</h1>
        <p class="mt-2 text-sm text-slate-400">Yeni içerik paylaş ve fan'larınla buluş.</p>
    </div>

    {{-- Creator Studio --}}
    <div class="space-y-6" data-testid="creator-studio">
        {{-- Content Type Selector --}}
        <div class="flex gap-3">
            <button class="flex-1 rounded-2xl bg-white text-slate-900 py-3 text-sm font-semibold">📝 Metin</button>
            <button class="flex-1 rounded-2xl bg-white/10 text-slate-300 hover:bg-white/20 py-3 text-sm font-semibold transition-colors">📷 Fotoğraf</button>
            <button class="flex-1 rounded-2xl bg-white/10 text-slate-300 hover:bg-white/20 py-3 text-sm font-semibold transition-colors">🎥 Video</button>
        </div>

        {{-- Compose Form --}}
        <form class="space-y-4" data-testid="create-form">
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400">Başlık</label>
                <input type="text" placeholder="İçerik başlığı…" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder-slate-500 focus:border-fuchsia-400/50 focus:outline-none transition-colors" />
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400">İçerik</label>
                <textarea rows="6" placeholder="İçeriğini yaz…" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white placeholder-slate-500 focus:border-fuchsia-400/50 focus:outline-none transition-colors"></textarea>
            </div>

            {{-- Media Upload --}}
            <div class="rounded-2xl border-2 border-dashed border-white/10 p-8 text-center hover:border-fuchsia-400/30 transition-colors cursor-pointer">
                <p class="text-2xl">📎</p>
                <p class="mt-2 text-sm text-slate-400">Medya eklemek için tıkla veya sürükle</p>
                <p class="mt-1 text-xs text-slate-500">JPG, PNG, MP4 — Maks 50MB</p>
            </div>

            {{-- Visibility --}}
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400">Görünürlük</label>
                <div class="mt-2 flex flex-wrap gap-2">
                    <button type="button" class="rounded-full bg-white text-slate-900 px-4 py-2 text-xs font-semibold">🌍 Herkese Açık</button>
                    <button type="button" class="rounded-full bg-white/10 text-slate-300 px-4 py-2 text-xs font-semibold hover:bg-white/20 transition-colors">👥 Kayıtlı Üyeler</button>
                    <button type="button" class="rounded-full bg-white/10 text-slate-300 px-4 py-2 text-xs font-semibold hover:bg-white/20 transition-colors">⭐ Aboneler</button>
                    <button type="button" class="rounded-full bg-white/10 text-slate-300 px-4 py-2 text-xs font-semibold hover:bg-white/20 transition-colors">🔒 PPV</button>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-2">
                <button type="button" class="flex-1 rounded-2xl border border-white/20 py-3 text-sm font-semibold text-slate-300 hover:bg-white/5 transition-colors">Taslak Kaydet</button>
                <button type="submit" class="flex-1 rounded-2xl bg-fuchsia-500 hover:bg-fuchsia-600 py-3 text-sm font-semibold text-white transition-colors">Yayınla</button>
            </div>
        </form>
    </div>
</div>
@endsection
