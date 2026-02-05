<template id="content-card-template">
    <article class="content-card relative rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur" data-content-id="">
        <div class="flex items-center gap-3 mb-4">
            <div class="creator-avatar flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-fuchsia-500 to-cyan-400 text-sm font-bold text-white"></div>
            <div class="flex-1">
                <p class="creator-name text-sm font-semibold"></p>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500 content-visibility"></p>
            </div>
            <span class="content-badge hidden rounded-full bg-white/10 px-3 py-1 text-xs uppercase tracking-widest text-slate-200">🔒 Locked</span>
        </div>
        <h3 class="text-lg font-semibold content-title"></h3>
        <div class="mt-3 text-sm text-slate-300 leading-relaxed content-body"></div>
        <div class="mt-4 flex items-center justify-between border-t border-white/5 pt-4">
            <div class="text-xs text-slate-500 tips-summary"></div>
            <button class="cta-button hidden rounded-full bg-fuchsia-500 hover:bg-fuchsia-600 px-5 py-2 text-xs font-semibold text-white transition-colors">Action</button>
        </div>
        <div class="locked-overlay pointer-events-none absolute inset-0 hidden rounded-3xl bg-slate-950/70 backdrop-blur"></div>
    </article>
</template>
