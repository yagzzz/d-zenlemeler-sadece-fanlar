<div id="comment-modal" class="modal-overlay hidden">
    <div class="modal-backdrop" data-comment-close></div>
    <div class="modal-sheet">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="text-bold" style="font-size:1.125rem;">Yorumlar</h3>
            <button class="text-muted pointer-cursor" style="background:none;border:none;font-size:1.25rem;" data-comment-close>✕</button>
        </div>

        <div id="comments-list" style="max-height:15rem;overflow-y:auto;">
            <p class="text-muted py-3 text-center" style="font-size:0.75rem;" id="comments-empty">Henüz yorum yok. İlk yorumu sen yaz!</p>
        </div>

        <form id="comment-form" class="d-flex gap-2 mt-3">
            <input type="text" id="comment-input" placeholder="Yorum yaz…" class="sf-input" style="flex:1;" maxlength="2000" />
            <button type="submit" class="btn btn-primary" style="padding:0.625rem 1rem;">Gönder</button>
        </form>
    </div>
</div>
