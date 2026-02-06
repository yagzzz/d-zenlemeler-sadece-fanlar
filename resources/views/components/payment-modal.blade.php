<div id="payment-modal" class="modal-overlay hidden">
    <div class="modal-backdrop absolute inset-0 bg-black/60" data-payment-close></div>
    <div class="modal-sheet">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="text-bold" style="font-size:1.125rem;">Ödeme</h3>
            <button class="text-muted pointer-cursor" style="background:none;border:none;font-size:1.25rem;" data-payment-close>✕</button>
        </div>
        <div class="mt-4" id="payment-steps" style="display:flex;flex-direction:column;gap:1.5rem;">
            <div data-step="summary">
                <p class="text-muted" style="font-size:0.875rem;">Özet</p>
                <div class="mt-2" style="border-radius:1rem;border:1px solid rgba(255,255,255,0.1);padding:1rem;">
                    <p id="payment-summary" style="font-size:0.875rem;"></p>
                </div>
                <button id="payment-start" class="btn btn-primary btn-block mt-3" style="padding:0.75rem;">Ödemeyi Başlat</button>
            </div>
            <div data-step="waiting" class="hidden">
                <p class="text-muted" style="font-size:0.875rem;">Ödeme Bekleniyor</p>
                <div class="mt-2" style="border-radius:1rem;border:1px solid rgba(255,255,255,0.1);padding:1rem;display:flex;flex-direction:column;gap:0.5rem;">
                    <div class="text-muted" style="font-size:0.75rem;">Adres</div>
                    <div id="payment-address" style="font-size:0.875rem;word-break:break-all;"></div>
                    <div class="text-muted" style="font-size:0.75rem;">Tutar</div>
                    <div id="payment-amount" style="font-size:0.875rem;"></div>
                    <div class="text-muted" style="font-size:0.75rem;">Fatura</div>
                    <div id="payment-invoice" style="font-size:0.875rem;"></div>
                </div>
                <p id="payment-status" class="mt-2 text-muted" style="font-size:0.75rem;">Doğrulanıyor…</p>
            </div>
            <div data-step="success" class="hidden">
                <p style="font-size:0.875rem;color:#34d399;">Başarılı</p>
                <button id="payment-close-success" class="btn btn-block mt-3" style="padding:0.75rem;background:#34d399;color:#0f172a;border-radius:1rem;font-weight:600;">İçeriğe dön</button>
            </div>
        </div>
    </div>
</div>
