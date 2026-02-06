@extends('layouts.app')

@section('content')
<div class="content-column" data-page="create">
    <div class="mb-6">
        <h1 class="text-xl font-bold">İçerik Oluştur</h1>
        <p class="text-sm text-slate-400">Yeni içerik paylaş ve fan'larınla buluş.</p>
    </div>

    <div class="post-box" data-testid="creator-studio">
        <form id="create-form" class="p-4 space-y-4" data-testid="create-form">
            {{-- Title --}}
            <input type="text" name="title" placeholder="Başlık…" class="sf-input text-base font-semibold" required />

            {{-- Body --}}
            <textarea name="body" rows="5" placeholder="İçeriğini yaz…" class="sf-textarea" id="create-body"></textarea>

            {{-- Draft indicator --}}
            <p id="draft-status" class="text-xs text-slate-500 hidden">💾 Taslak kaydedildi</p>

            {{-- Media Upload Zone --}}
            <div id="upload-zone" class="rounded-xl border-2 border-dashed border-white/10 p-6 text-center hover:border-fuchsia-400/30 transition-colors cursor-pointer">
                <p class="text-2xl">📎</p>
                <p class="mt-2 text-sm text-slate-400">Medya eklemek için tıkla veya sürükle</p>
                <p class="mt-1 text-xs text-slate-500">JPG, PNG, MP4, MP3 — Maks 100MB</p>
                <input type="file" id="media-input" class="hidden" multiple accept="image/*,video/*,audio/*" />
            </div>
            <div id="upload-previews" class="grid grid-cols-3 gap-2"></div>

            {{-- Visibility --}}
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400 mb-2 block">Görünürlük</label>
                <div class="flex flex-wrap gap-2">
                    <button type="button" class="pill pill-active visibility-btn" data-visibility="public">🌍 Herkese Açık</button>
                    <button type="button" class="pill pill-default visibility-btn" data-visibility="registered_only">👥 Kayıtlı</button>
                    <button type="button" class="pill pill-default visibility-btn" data-visibility="subscriber_only">⭐ Aboneler</button>
                    <button type="button" class="pill pill-default visibility-btn" data-visibility="ppv">🔒 PPV</button>
                </div>
                <input type="hidden" name="visibility" value="public" id="visibility-input" />
            </div>

            {{-- PPV Price (shown only when PPV selected) --}}
            <div id="ppv-price-row" class="hidden">
                <label class="text-xs uppercase tracking-widest text-slate-400">PPV Fiyat (atomic XMR)</label>
                <input type="number" name="ppv_price_atomic" min="1000" value="3000" class="sf-input mt-1" />
            </div>

            {{-- Actions --}}
            <div class="flex gap-3 pt-2">
                <button type="button" id="save-draft-btn" class="btn-outline flex-1 py-3">Taslak Kaydet</button>
                <button type="submit" class="btn-primary flex-1 py-3">Yayınla</button>
            </div>
        </form>
    </div>
</div>
@endsection
