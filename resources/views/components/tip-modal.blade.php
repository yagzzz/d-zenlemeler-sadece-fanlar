<div id="tip-modal" class="fixed inset-0 z-50 hidden">
    <div class="modal-backdrop absolute inset-0 bg-black/60" data-tip-close></div>
    <div class="absolute bottom-0 left-0 right-0 mx-auto max-w-2xl rounded-t-3xl bg-slate-950 border-t border-white/10 p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Tip Gönder</h3>
            <button class="text-slate-400" data-tip-close>✕</button>
        </div>
        <form id="tip-form" class="mt-6 space-y-4">
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400">Tutar (atomic)</label>
                <input id="tip-amount" type="number" min="1000" value="2000" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white" />
            </div>
            <div>
                <label class="text-xs uppercase tracking-widest text-slate-400">Mesaj</label>
                <textarea id="tip-message" rows="3" class="mt-2 w-full rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-white" placeholder="Kısa bir not ekle"></textarea>
            </div>
            <button type="submit" class="w-full rounded-2xl bg-white text-slate-900 py-3 text-sm font-semibold">Tip Oluştur</button>
        </form>
    </div>
</div>
