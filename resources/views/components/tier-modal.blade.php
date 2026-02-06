<div id="tier-modal" class="modal-overlay hidden">
    <div class="modal-backdrop" data-tier-close></div>
    <div class="modal-sheet">
        <div class="d-flex justify-content-between align-items-center">
            <h3 class="text-bold" style="font-size:1.125rem;">Tier'leri Karşılaştır</h3>
            <button class="text-muted pointer-cursor" style="background:none;border:none;font-size:1.25rem;" data-tier-close>✕</button>
        </div>
        <div class="mt-3 d-flex align-items-center gap-2">
            <span class="billing-toggle active" data-billing="monthly">Aylık</span>
            <span class="billing-toggle" data-billing="yearly">Yıllık</span>
        </div>
        <div id="tier-compare-list" class="mt-4" style="display:flex;flex-direction:column;gap:1rem;"></div>
    </div>
</div>
