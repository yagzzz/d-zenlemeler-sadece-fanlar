<div id="tip-modal" class="modal-overlay hidden">
    <div class="modal-backdrop absolute inset-0 bg-black/60" data-tip-close></div>
    <div class="modal-sheet">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="text-bold" style="font-size:1.125rem;">Tip Gönder</h3>
            <button class="text-muted pointer-cursor" style="background:none;border:none;font-size:1.25rem;" data-tip-close>✕</button>
        </div>
        <form id="tip-form" class="mt-4" style="display:flex;flex-direction:column;gap:1rem;">
            <div>
                <label class="text-muted" style="font-size:0.625rem;text-transform:uppercase;letter-spacing:0.1em;">Tutar (atomic)</label>
                <input id="tip-amount" type="number" min="1000" value="2000" class="sf-input mt-1" />
            </div>
            <div>
                <label class="text-muted" style="font-size:0.625rem;text-transform:uppercase;letter-spacing:0.1em;">Mesaj</label>
                <textarea id="tip-message" rows="3" class="sf-textarea mt-1" placeholder="Kısa bir not ekle"></textarea>
            </div>
            <button type="submit" class="btn btn-primary btn-block" style="padding:0.75rem;">Tip Oluştur</button>
        </form>
    </div>
</div>
