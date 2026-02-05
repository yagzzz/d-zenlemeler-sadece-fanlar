<div id="tier-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/60" data-tier-close></div>
    <div class="absolute bottom-0 left-0 right-0 mx-auto max-w-2xl rounded-t-3xl bg-slate-950 border-t border-white/10 p-6">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold">Tier’leri Karşılaştır</h3>
            <button class="text-slate-400" data-tier-close>✕</button>
        </div>
        <div class="mt-4 flex items-center gap-2 text-xs text-slate-400">
            <span class="billing-toggle active" data-billing="monthly">Aylık</span>
            <span class="billing-toggle" data-billing="yearly">Yıllık</span>
        </div>
        <div id="tier-compare-list" class="mt-6 space-y-4"></div>
    </div>
</div>
