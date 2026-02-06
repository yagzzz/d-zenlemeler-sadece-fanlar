<div id="comment-modal" class="fixed inset-0 z-50 hidden">
    <div class="modal-backdrop absolute inset-0 bg-black/60" data-comment-close></div>
    <div class="modal-sheet">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold">Yorumlar</h3>
            <button class="text-slate-400 hover:text-white transition-colors" data-comment-close>✕</button>
        </div>

        <div id="comments-list" class="divide-y divide-white/5 max-h-60 overflow-y-auto">
            <p class="text-xs text-slate-500 py-4 text-center" id="comments-empty">Henüz yorum yok. İlk yorumu sen yaz!</p>
        </div>

        <form id="comment-form" class="mt-4 flex gap-2">
            <input type="text" id="comment-input" placeholder="Yorum yaz…" class="sf-input flex-1" maxlength="2000" />
            <button type="submit" class="btn-primary px-4">Gönder</button>
        </form>
    </div>
</div>
