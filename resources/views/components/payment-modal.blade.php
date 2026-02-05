<div id="payment-modal" class="fixed inset-0 z-50 hidden">
    <div class="modal-backdrop absolute inset-0 bg-black/60" data-payment-close></div>
    <div class="absolute bottom-0 left-0 right-0 mx-auto max-w-2xl rounded-t-3xl bg-slate-950 border-t border-white/10 p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Ödeme</h3>
            <button class="text-slate-400" data-payment-close>✕</button>
        </div>
        <div class="mt-6 space-y-6" id="payment-steps">
            <div data-step="summary">
                <p class="text-sm text-slate-400">Özet</p>
                <div class="mt-2 rounded-2xl border border-white/10 p-4">
                    <p id="payment-summary" class="text-sm"></p>
                </div>
                <button id="payment-start" class="mt-4 w-full rounded-2xl bg-white text-slate-900 py-3 text-sm font-semibold">Ödemeyi Başlat</button>
            </div>
            <div data-step="waiting" class="hidden">
                <p class="text-sm text-slate-400">Ödeme Bekleniyor</p>
                <div class="mt-2 rounded-2xl border border-white/10 p-4 space-y-2">
                    <div class="text-xs text-slate-400">Adres</div>
                    <div id="payment-address" class="text-sm break-all"></div>
                    <div class="text-xs text-slate-400">Tutar</div>
                    <div id="payment-amount" class="text-sm"></div>
                    <div class="text-xs text-slate-400">Fatura</div>
                    <div id="payment-invoice" class="text-sm"></div>
                </div>
                <p id="payment-status" class="mt-3 text-xs text-slate-400">Doğrulanıyor…</p>
            </div>
            <div data-step="success" class="hidden">
                <p class="text-sm text-emerald-400">Başarılı</p>
                <button id="payment-close-success" class="mt-4 w-full rounded-2xl bg-emerald-400 text-slate-900 py-3 text-sm font-semibold">İçeriğe dön</button>
            </div>
        </div>
    </div>
</div>
