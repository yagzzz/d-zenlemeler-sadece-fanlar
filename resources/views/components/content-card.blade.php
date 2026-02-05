<template id="content-card-template">
    <article class="content-card relative rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur" data-content-id="">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs uppercase tracking-[0.3em] text-slate-500 content-visibility"></p>
                <h3 class="text-xl font-semibold content-title"></h3>
            </div>
            <span class="content-badge hidden rounded-full bg-white/10 px-3 py-1 text-xs uppercase tracking-widest text-slate-200">Locked</span>
        </div>
        <div class="mt-4 text-sm text-slate-300 content-body"></div>
        <div class="mt-4 flex items-center justify-between">
            <div class="text-xs text-slate-500 tips-summary"></div>
            <button class="cta-button hidden rounded-full bg-fuchsia-500 px-4 py-2 text-xs font-semibold text-white">Action</button>
        </div>
        <div class="locked-overlay pointer-events-none absolute inset-0 hidden rounded-3xl bg-slate-950/70 backdrop-blur"></div>
    </article>
</template>
