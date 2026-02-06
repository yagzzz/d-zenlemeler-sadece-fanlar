@extends('layouts.app')

@section('content')
<div class="content-column" data-page="create">
    <div class="mb-4">
        <h1 class="text-bold" style="font-size:1.25rem;">İçerik Oluştur</h1>
        <p class="text-muted" style="font-size:0.875rem;">Yeni içerik paylaş ve fan'larınla buluş.</p>
    </div>

    <div class="post-box" data-testid="creator-studio">
        <form id="create-form" class="p-4" data-testid="create-form" style="display:flex;flex-direction:column;gap:1rem;">
            {{-- Title --}}
            <input type="text" name="title" placeholder="Başlık…" class="sf-input text-bold" style="font-size:1rem;" required />

            {{-- Body --}}
            <textarea name="body" rows="5" placeholder="İçeriğini yaz…" class="sf-textarea" id="create-body"></textarea>

            {{-- Draft indicator --}}
            <p id="draft-status" class="hidden text-muted" style="font-size:0.75rem;">💾 Taslak kaydedildi</p>

            {{-- Media Upload Zone --}}
            <div id="upload-zone" class="upload-zone">
                <p style="font-size:1.5rem;">📎</p>
                <p class="mt-2 text-muted" style="font-size:0.875rem;">Medya eklemek için tıkla veya sürükle</p>
                <p class="mt-1 text-muted" style="font-size:0.75rem;">JPG, PNG, MP4, MP3 — Maks 100MB</p>
                <input type="file" id="media-input" class="hidden" multiple accept="image/*,video/*,audio/*" />
            </div>
            <div id="upload-previews" class="upload-previews"></div>

            {{-- Visibility --}}
            <div>
                <label class="text-muted mb-2" style="font-size:0.625rem;text-transform:uppercase;letter-spacing:0.1em;display:block;">Görünürlük</label>
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="pill pill-active visibility-btn" data-visibility="public">🌍 Herkese Açık</button>
                    <button type="button" class="pill pill-default visibility-btn" data-visibility="registered_only">👥 Kayıtlı</button>
                    <button type="button" class="pill pill-default visibility-btn" data-visibility="subscriber_only">⭐ Aboneler</button>
                    <button type="button" class="pill pill-default visibility-btn" data-visibility="ppv">🔒 PPV</button>
                </div>
                <input type="hidden" name="visibility" value="public" id="visibility-input" />
            </div>

            {{-- PPV Price (shown only when PPV selected) --}}
            <div id="ppv-price-row" class="hidden">
                <label class="text-muted" style="font-size:0.625rem;text-transform:uppercase;letter-spacing:0.1em;">PPV Fiyat (atomic XMR)</label>
                <input type="number" name="ppv_price_atomic" min="1000" value="3000" class="sf-input mt-1" />
            </div>

            {{-- Actions --}}
            <div class="d-flex gap-3 pt-2">
                <button type="button" id="save-draft-btn" class="btn btn-outline" style="flex:1;padding:0.75rem;">Taslak Kaydet</button>
                <button type="submit" class="btn btn-primary" style="flex:1;padding:0.75rem;">Yayınla</button>
            </div>
        </form>
    </div>
</div>
@endsection
